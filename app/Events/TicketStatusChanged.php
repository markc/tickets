<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketStatus $oldStatus,
        public TicketStatus $newStatus,
        public User $changedBy
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tickets.' . $this->ticket->uuid),
            new PrivateChannel('user.' . $this->ticket->creator_id),
            ...$this->ticket->assignedTo ? [new PrivateChannel('user.' . $this->ticket->assigned_to_id)] : [],
            new PrivateChannel('office.' . $this->ticket->office_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id' => $this->ticket->id,
                'uuid' => $this->ticket->uuid,
                'subject' => $this->ticket->subject,
                'updated_at' => $this->ticket->updated_at->toISOString(),
            ],
            'status_change' => [
                'from' => [
                    'id' => $this->oldStatus->id,
                    'name' => $this->oldStatus->name,
                    'color' => $this->oldStatus->color,
                ],
                'to' => [
                    'id' => $this->newStatus->id,
                    'name' => $this->newStatus->name,
                    'color' => $this->newStatus->color,
                ],
            ],
            'changed_by' => [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
                'role' => $this->changedBy->role,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.status.changed';
    }
}
