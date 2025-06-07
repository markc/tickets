<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketTimeline;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('changeStatus')
                ->label('Change Status')
                ->icon('heroicon-o-check-circle')
                ->form([
                    Forms\Components\Select::make('ticket_status_id')
                        ->label('New Status')
                        ->options(TicketStatus::pluck('name', 'id'))
                        ->default($this->record->ticket_status_id)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $oldStatus = $this->record->status->name;
                    $this->record->update(['ticket_status_id' => $data['ticket_status_id']]);
                    $newStatus = TicketStatus::find($data['ticket_status_id'])->name;

                    TicketTimeline::create([
                        'ticket_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'entry' => "Status changed from {$oldStatus} to {$newStatus}",
                    ]);

                    Notification::make()
                        ->title('Status updated')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('changePriority')
                ->label('Change Priority')
                ->icon('heroicon-o-exclamation-circle')
                ->form([
                    Forms\Components\Select::make('ticket_priority_id')
                        ->label('New Priority')
                        ->options(TicketPriority::pluck('name', 'id'))
                        ->default($this->record->ticket_priority_id)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $oldPriority = $this->record->priority->name;
                    $this->record->update(['ticket_priority_id' => $data['ticket_priority_id']]);
                    $newPriority = TicketPriority::find($data['ticket_priority_id'])->name;

                    TicketTimeline::create([
                        'ticket_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'entry' => "Priority changed from {$oldPriority} to {$newPriority}",
                    ]);

                    Notification::make()
                        ->title('Priority updated')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('assignTicket')
                ->label('Assign Ticket')
                ->icon('heroicon-o-user')
                ->form([
                    Forms\Components\Select::make('assigned_to_id')
                        ->label('Assign To')
                        ->options(function () {
                            return User::whereHas('offices', function ($query) {
                                $query->where('offices.id', $this->record->office_id);
                            })->where('role', 'agent')->pluck('name', 'id');
                        })
                        ->default($this->record->assigned_to_id)
                        ->searchable(),
                ])
                ->action(function (array $data): void {
                    $oldAssignee = $this->record->assignedTo?->name ?? 'Unassigned';
                    $this->record->update(['assigned_to_id' => $data['assigned_to_id']]);
                    $newAssignee = User::find($data['assigned_to_id'])?->name ?? 'Unassigned';

                    TicketTimeline::create([
                        'ticket_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'entry' => "Ticket assigned from {$oldAssignee} to {$newAssignee}",
                    ]);

                    Notification::make()
                        ->title('Ticket assigned')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\EditAction::make()
                ->successRedirectUrl($this->getResource()::getUrl('index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Ticket Details')
                    ->schema([
                        TextEntry::make('uuid')
                            ->label('Ticket ID')
                            ->copyable(),
                        TextEntry::make('subject'),
                        TextEntry::make('content')
                            ->columnSpanFull()
                            ->markdown(),
                        TextEntry::make('creator.name')
                            ->label('Created By'),
                        TextEntry::make('creator.email')
                            ->label('Creator Email'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('office.name')
                            ->badge(),
                        TextEntry::make('status.name')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Open' => 'info',
                                'In Progress' => 'warning',
                                'On Hold' => 'warning',
                                'Closed' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('priority.name')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Low' => 'gray',
                                'Medium' => 'info',
                                'High' => 'warning',
                                'Urgent' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('assignedTo.name')
                            ->label('Assigned To')
                            ->placeholder('Unassigned'),
                    ])
                    ->columns(3),

                Section::make('Timeline')
                    ->schema([
                        TextEntry::make('timeline_display')
                            ->label('')
                            ->formatStateUsing(function () {
                                $timeline = collect();

                                $this->record->timeline()->with('user')->get()->each(function ($entry) use (&$timeline) {
                                    $timeline->push([
                                        'type' => 'timeline',
                                        'created_at' => $entry->created_at,
                                        'content' => $entry->entry,
                                        'user' => $entry->user,
                                    ]);
                                });

                                $this->record->replies()->with('user')->get()->each(function ($reply) use (&$timeline) {
                                    if (! $reply->is_internal_note || auth()->user()->role !== 'customer') {
                                        $timeline->push([
                                            'type' => 'reply',
                                            'created_at' => $reply->created_at,
                                            'content' => $reply->content,
                                            'user' => $reply->user,
                                            'is_internal_note' => $reply->is_internal_note,
                                        ]);
                                    }
                                });

                                $timeline = $timeline->sortBy('created_at');

                                $output = '';
                                foreach ($timeline as $item) {
                                    $user = $item['user']?->name ?? 'System';
                                    $time = $item['created_at']->diffForHumans();
                                    $content = $item['content'];
                                    $isInternal = $item['is_internal_note'] ?? false;

                                    $badge = $isInternal ? '<span class="badge badge-warning">Internal Note</span>' : '';

                                    $output .= "<div class='mb-4 p-3 border rounded'>";
                                    $output .= "<div class='font-medium'>{$user} {$badge}</div>";
                                    $output .= "<div class='text-sm text-gray-500'>{$time}</div>";
                                    $output .= "<div class='mt-2'>{$content}</div>";
                                    $output .= '</div>';
                                }

                                return $output;
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
