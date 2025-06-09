<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary data
        $this->seed(\Database\Seeders\TicketStatusSeeder::class);
        $this->seed(\Database\Seeders\TicketPrioritySeeder::class);
        $this->seed(\Database\Seeders\OfficeSeeder::class);
    }

    public function test_process_new_ticket_email()
    {
        Notification::fake();

        $emailContent = $this->getTestEmail(
            'john@example.com',
            'John Doe',
            'support@tikm.com',
            'Help with login issue',
            'I cannot login to my account. Please help.'
        );

        // Simulate piping email to artisan command
        $this->artisan('ticket:process-email')
            ->expectsOutput('Email processed successfully')
            ->assertExitCode(0);

        // Verify ticket was created
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Help with login issue',
            'content' => 'I cannot login to my account. Please help.',
        ]);

        $ticket = Ticket::where('subject', 'Help with login issue')->first();
        $this->assertEquals('john@example.com', $ticket->creator->email);
        $this->assertEquals('John Doe', $ticket->creator->name);

        // Verify notification was sent
        Notification::assertSentTo(
            $ticket->creator,
            TicketCreated::class
        );
    }

    public function test_process_reply_email()
    {
        Notification::fake();

        // Create existing ticket
        $user = User::factory()->create(['email' => 'jane@example.com']);
        $ticket = Ticket::factory()->create([
            'creator_id' => $user->id,
            'uuid' => 'test-uuid-12345',
        ]);

        $emailContent = $this->getTestEmail(
            'jane@example.com',
            'Jane Doe',
            'support+test-uuid-12345@tikm.com',
            'Re: Original Subject',
            'This is my reply to the ticket.'
        );

        // Simulate piping email to artisan command
        $this->artisan('ticket:process-email')
            ->expectsOutput('Email processed successfully')
            ->assertExitCode(0);

        // Verify reply was created
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => 'This is my reply to the ticket.',
        ]);

        // Verify timeline entry
        $this->assertDatabaseHas('ticket_timelines', [
            'ticket_id' => $ticket->id,
            'action' => 'replied',
            'user_id' => $user->id,
        ]);
    }

    public function test_process_email_with_attachments()
    {
        $emailContent = $this->getTestEmailWithAttachment(
            'attach@example.com',
            'Attachment Test',
            'support@tikm.com',
            'Ticket with attachment',
            'Please see attached file.'
        );

        $this->artisan('ticket:process-email')
            ->expectsOutput('Email processed successfully')
            ->assertExitCode(0);

        $ticket = Ticket::where('subject', 'Ticket with attachment')->first();
        $this->assertNotNull($ticket);
        $this->assertCount(1, $ticket->attachments);
        $this->assertEquals('test-document.pdf', $ticket->attachments->first()->file_name);
    }

    public function test_reject_spam_email()
    {
        $emailContent = $this->getTestEmail(
            'noreply@spammer.com',
            'Spammer',
            'support@tikm.com',
            'Buy cheap products',
            'Click here for amazing deals!'
        );

        $this->artisan('ticket:process-email')
            ->expectsOutput('Email rejected: Invalid sender')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('tickets', [
            'subject' => 'Buy cheap products',
        ]);
    }

    public function test_handle_invalid_uuid_in_reply()
    {
        $emailContent = $this->getTestEmail(
            'user@example.com',
            'User',
            'support+invalid-uuid@tikm.com',
            'Re: Something',
            'Reply to non-existent ticket'
        );

        $this->artisan('ticket:process-email')
            ->expectsOutput('Ticket not found, creating new ticket instead')
            ->assertExitCode(0);

        // Should create new ticket instead
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Re: Something',
            'content' => 'Reply to non-existent ticket',
        ]);
    }

    private function getTestEmail($from, $fromName, $to, $subject, $body)
    {
        return <<<EMAIL
From: $fromName <$from>
To: $to
Subject: $subject
Date: Mon, 1 Jan 2024 12:00:00 +0000
Message-ID: <test@example.com>
Content-Type: text/plain; charset=UTF-8

$body
EMAIL;
    }

    private function getTestEmailWithAttachment($from, $fromName, $to, $subject, $body)
    {
        $boundary = '----=_Part_1234567890';

        return <<<EMAIL
From: $fromName <$from>
To: $to
Subject: $subject
Date: Mon, 1 Jan 2024 12:00:00 +0000
Message-ID: <test@example.com>
Content-Type: multipart/mixed; boundary="$boundary"

--$boundary
Content-Type: text/plain; charset=UTF-8

$body

--$boundary
Content-Type: application/pdf
Content-Disposition: attachment; filename="test-document.pdf"
Content-Transfer-Encoding: base64

JVBERi0xLjQKJeLjz9MKCj4+CnN0YXJ0eHJlZgoxMTYKJSVFT0YK

--$boundary--
EMAIL;
    }
}
