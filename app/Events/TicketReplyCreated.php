<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplyCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TicketReply $reply,
        public Ticket $ticket
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
            'reply' => [
                'id' => $this->reply->id,
                'message' => $this->reply->message,
                'is_internal' => $this->reply->is_internal,
                'created_at' => $this->reply->created_at->toISOString(),
                'user' => [
                    'id' => $this->reply->user->id,
                    'name' => $this->reply->user->name,
                    'role' => $this->reply->user->role,
                    'avatar_url' => $this->reply->user->avatar_url,
                ],
                'attachments' => $this->reply->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'size' => $attachment->size,
                ]),
            ],
            'ticket' => [
                'id' => $this->ticket->id,
                'uuid' => $this->ticket->uuid,
                'subject' => $this->ticket->subject,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.reply.created';
    }
}
