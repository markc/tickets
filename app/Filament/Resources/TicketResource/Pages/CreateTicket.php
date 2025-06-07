<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketTimeline;
use App\Models\User;
use App\Notifications\TicketCreated;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        TicketTimeline::create([
            'ticket_id' => $this->record->id,
            'user_id' => auth()->id(),
            'entry' => 'Ticket created by '.auth()->user()->name,
        ]);

        // Send email notifications
        $this->sendTicketCreatedNotifications();
    }

    protected function sendTicketCreatedNotifications(): void
    {
        $ticket = $this->record;
        
        // Notify the ticket creator (customer perspective)
        $ticket->creator->notify(new TicketCreated($ticket));
        
        // Notify assigned agent if exists
        if ($ticket->assigned_to_id) {
            $ticket->assignedTo->notify(new TicketCreated($ticket));
        }
        
        // Notify all agents in the same office
        $agentsInOffice = User::where('role', 'agent')
            ->whereHas('offices', function ($query) use ($ticket) {
                $query->where('offices.id', $ticket->office_id);
            })
            ->where('id', '!=', $ticket->assigned_to_id) // Don't double-notify assigned agent
            ->get();
            
        foreach ($agentsInOffice as $agent) {
            $agent->notify(new TicketCreated($ticket));
        }
        
        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketCreated($ticket));
        }
    }
}
