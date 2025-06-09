<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\EmailTemplateService;
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

    private ?array $processedTemplate = null;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
        public bool $isCustomer = false
    ) {
        // Process the email template on construction
        $templateService = new EmailTemplateService;
        $this->processedTemplate = $templateService->processTicketReplyEmail($this->ticket, $this->reply, $this->isCustomer);
    }

    public function envelope(): Envelope
    {
        $supportDomain = config('mail.support_domain', 'localhost');
        $replyToAddress = "support+{$this->ticket->uuid}@{$supportDomain}";

        // Use processed template subject if available, otherwise fallback to default
        $subject = $this->processedTemplate
            ? $this->processedTemplate['subject']
            : '[Ticket #'.substr($this->ticket->uuid, 0, 8).'] '.$this->ticket->subject;

        return new Envelope(
            subject: $subject,
            replyTo: [
                new Address($replyToAddress, config('app.name').' Support'),
            ],
        );
    }

    public function content(): Content
    {
        // If we have a processed template, use it
        if ($this->processedTemplate) {
            if ($this->processedTemplate['type'] === 'html') {
                return new Content(
                    htmlString: $this->processedTemplate['content'],
                );
            } elseif ($this->processedTemplate['type'] === 'plain') {
                return new Content(
                    textString: $this->processedTemplate['content'],
                );
            } else {
                // Default to markdown
                return new Content(
                    markdownString: $this->processedTemplate['content'],
                );
            }
        }

        // Fallback to existing template
        return new Content(
            markdown: 'emails.ticket-reply',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
