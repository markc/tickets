<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake notifications to prevent email sending issues in tests
        Notification::fake();

        // Seed necessary data
        $this->seed(\Database\Seeders\TicketStatusSeeder::class);
        $this->seed(\Database\Seeders\TicketPrioritySeeder::class);
        $this->seed(\Database\Seeders\OfficeSeeder::class);
    }

    public function test_customer_can_create_ticket()
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'customer']);
        $office = Office::first();
        $priority = TicketPriority::first();

        $response = $this->actingAs($user)
            ->post('/tickets', [
                'subject' => 'Test Ticket',
                'content' => 'This is a test ticket content',
                'office_id' => $office->id,
                'ticket_priority_id' => $priority->id,
                'attachments' => [
                    UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'subject' => 'Test Ticket',
            'content' => 'This is a test ticket content',
            'creator_id' => $user->id,
            'office_id' => $office->id,
            'ticket_priority_id' => $priority->id,
        ]);

        $ticket = Ticket::where('subject', 'Test Ticket')->first();
        $this->assertCount(1, $ticket->attachments);
        Storage::disk('public')->assertExists($ticket->attachments->first()->path);
    }

    public function test_customer_can_view_own_tickets()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $ownTicket = Ticket::factory()->create(['creator_id' => $customer->id]);
        $otherTicket = Ticket::factory()->create(['creator_id' => $otherCustomer->id]);

        $response = $this->actingAs($customer)->get('/tickets');

        $response->assertSuccessful();
        $response->assertSee($ownTicket->subject);
        $response->assertDontSee($otherTicket->subject);
    }

    public function test_customer_cannot_view_other_tickets()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['creator_id' => $otherCustomer->id]);

        $response = $this->actingAs($customer)->get("/tickets/{$ticket->uuid}");

        $response->assertForbidden();
    }

    public function test_agent_can_view_all_tickets()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['creator_id' => $customer->id]);
        
        // Associate agent with ticket's office so they can view it
        $agent->offices()->attach($ticket->office_id);

        $response = $this->actingAs($agent)->get("/tickets/{$ticket->uuid}");

        $response->assertSuccessful();
        $response->assertSee($ticket->subject);
    }

    public function test_customer_can_reply_to_own_ticket()
    {
        Storage::fake('public');

        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['creator_id' => $customer->id]);

        $response = $this->actingAs($customer)
            ->post("/tickets/{$ticket->uuid}/reply", [
                'content' => 'This is a reply',
                'attachments' => [
                    UploadedFile::fake()->create('reply.pdf', 100),
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'content' => 'This is a reply',
        ]);

        $reply = $ticket->replies()->first();
        $this->assertCount(1, $reply->attachments);
    }

    public function test_ticket_timeline_is_recorded()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // Customer creates ticket
        $this->actingAs($customer);
        $response = $this->post('/tickets', [
            'subject' => 'Timeline Test',
            'content' => 'Testing timeline',
            'office_id' => Office::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        $ticket = Ticket::where('subject', 'Timeline Test')->first();

        // Check creation timeline
        $this->assertDatabaseHas('ticket_timelines', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'entry' => 'Ticket created',
        ]);

        // Customer adds reply
        $this->post("/tickets/{$ticket->uuid}/reply", [
            'content' => 'Customer reply',
        ]);

        // Check reply timeline
        $this->assertDatabaseHas('ticket_timelines', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'entry' => 'Added a reply',
        ]);
    }

    public function test_search_functionality()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $ticket1 = Ticket::factory()->create([
            'creator_id' => $user->id,
            'subject' => 'Network connectivity issue',
        ]);
        $ticket2 = Ticket::factory()->create([
            'creator_id' => $user->id,
            'subject' => 'Billing question',
        ]);

        // Import to search index
        $ticket1->searchable();
        $ticket2->searchable();

        $response = $this->actingAs($user)->get('/search?q=network');

        $response->assertSuccessful();
        $response->assertSee('Network connectivity issue');
        $response->assertDontSee('Billing question');
    }

    public function test_ticket_validation()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)
            ->post('/tickets', [
                'subject' => '', // Empty subject
                'content' => 'Content',
                'office_id' => 999, // Invalid office
                'ticket_priority_id' => Office::first()->id,
            ]);

        $response->assertSessionHasErrors(['subject', 'office_id']);
    }
}
