<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketTimeline;
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
    }
}
