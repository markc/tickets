<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public User $updatedBy,
        public array $changes = []
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
                'status' => $this->ticket->status->name,
                'priority' => $this->ticket->priority->name,
                'updated_at' => $this->ticket->updated_at->toISOString(),
            ],
            'updated_by' => [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
                'role' => $this->updatedBy->role,
            ],
            'changes' => $this->changes,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }
}
