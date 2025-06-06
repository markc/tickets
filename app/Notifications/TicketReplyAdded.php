<?php

namespace App\Notifications;

use App\Mail\TicketReplyMail;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketReplyAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public TicketReply $reply) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        $isCustomer = $notifiable->isCustomer();

        return new TicketReplyMail($this->ticket, $this->reply, $isCustomer);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_uuid' => $this->ticket->uuid,
            'reply_id' => $this->reply->id,
            'sender' => $this->reply->user->name,
        ];
    }
}
