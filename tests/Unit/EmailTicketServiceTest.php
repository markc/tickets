<?php

namespace Tests\Unit;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Services\EmailTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailTicketServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailTicketService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmailTicketService(new \App\Services\TicketAssignmentService);

        // Create necessary data
        $this->seed(\Database\Seeders\TicketStatusSeeder::class);
        $this->seed(\Database\Seeders\TicketPrioritySeeder::class);
        $this->seed(\Database\Seeders\OfficeSeeder::class);
    }

    public function test_create_ticket_from_email()
    {
        Notification::fake();

        $emailData = [
            'from' => 'test@example.com',
            'from_name' => 'Test User',
            'subject' => 'Test Email Ticket',
            'body' => 'This is a test email body',
            'to' => 'support@tikm.com',
            'attachments' => [],
        ];

        $ticket = $this->service->createTicketFromEmail(
            $emailData['from'],
            $emailData['from_name'],
            $emailData['subject'],
            $emailData['body'],
            $emailData['attachments']
        );

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals('Test Email Ticket', $ticket->subject);
        $this->assertEquals('This is a test email body', $ticket->content);
        $this->assertEquals('test@example.com', $ticket->creator->email);
        $this->assertEquals('Test User', $ticket->creator->name);

        Notification::assertSentTo(
            $ticket->creator,
            TicketCreated::class
        );
    }

    public function test_create_reply_from_email()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'creator_id' => $user->id,
            'uuid' => 'test-uuid-123',
        ]);

        $emailData = [
            'from' => $user->email,
            'from_name' => $user->name,
            'subject' => 'Re: Test Reply',
            'body' => 'This is a reply to the ticket',
            'to' => 'support+test-uuid-123@tikm.com',
            'attachments' => [],
        ];

        $reply = $this->service->createReplyFromEmail(
            $ticket->uuid,
            $emailData['from'],
            $emailData['from_name'],
            $emailData['subject'],
            $emailData['body'],
            $emailData['attachments']
        );

        $this->assertNotNull($reply);
        $this->assertEquals('This is a reply to the ticket', $reply->content);
        $this->assertEquals($user->id, $reply->user_id);
        $this->assertEquals($ticket->id, $reply->ticket_id);
    }

    public function test_extract_uuid_from_email()
    {
        $email1 = 'support+550e8400-e29b-41d4-a716-446655440000@tikm.com';
        $email2 = 'support@tikm.com';

        $uuid1 = $this->service->extractUuidFromEmail($email1);
        $uuid2 = $this->service->extractUuidFromEmail($email2);

        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $uuid1);
        $this->assertNull($uuid2);
    }

    public function test_find_or_create_user()
    {
        $email = 'newuser@example.com';
        $name = 'New User';

        $user = $this->service->findOrCreateUser($email, $name);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($name, $user->name);
        $this->assertEquals('customer', $user->role);

        // Test finding existing user
        $existingUser = $this->service->findOrCreateUser($email, 'Different Name');
        $this->assertEquals($user->id, $existingUser->id);
        $this->assertEquals($name, $existingUser->name); // Should keep original name
    }

    public function test_determine_office_from_email()
    {
        $billing = Office::where('name', 'Billing')->first();
        $technical = Office::where('name', 'Technical Support')->first();
        $general = Office::where('name', 'General Support')->first();

        $office1 = $this->service->determineOfficeFromEmail('billing@tikm.com', 'Invoice question');
        $office2 = $this->service->determineOfficeFromEmail('support@tikm.com', 'Server error help');
        $office3 = $this->service->determineOfficeFromEmail('support@tikm.com', 'General inquiry');

        $this->assertEquals($billing->id, $office1->id);
        $this->assertEquals($technical->id, $office2->id);
        $this->assertEquals($general->id, $office3->id);
    }
}
