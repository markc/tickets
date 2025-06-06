<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
        public bool $isCustomer = false
    ) {}

    public function envelope(): Envelope
    {
        $supportDomain = config('mail.support_domain', 'localhost');
        $replyToAddress = "support+{$this->ticket->uuid}@{$supportDomain}";

        return new Envelope(
            subject: '[Ticket #'.substr($this->ticket->uuid, 0, 8).'] '.$this->ticket->subject,
            replyTo: [
                new Address($replyToAddress, config('app.name').' Support'),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-reply',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
