<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary data
        TicketStatus::create(['name' => 'Open', 'color' => '#3b82f6']);
        TicketPriority::create(['name' => 'Medium', 'color' => '#f59e0b']);
        Office::create(['name' => 'Technical Support']);
    }

    public function test_agent_can_create_internal_note()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Assign agent to office
        $agent->offices()->attach($office->id);

        $ticket = Ticket::create([
            'subject' => 'Test Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.reply', $ticket->uuid), [
                'content' => 'This is an internal note',
                'is_internal' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'This is an internal note',
            'is_internal' => true,
        ]);
    }

    public function test_customer_cannot_create_internal_note()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        $ticket = Ticket::create([
            'subject' => 'Test Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        $this->actingAs($customer)
            ->post(route('tickets.reply', $ticket->uuid), [
                'content' => 'This should not be internal',
                'is_internal' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Should be saved as public reply, not internal
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'content' => 'This should not be internal',
            'is_internal' => false,
        ]);
    }

    public function test_customer_cannot_see_internal_notes()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Assign agent to office
        $agent->offices()->attach($office->id);

        $ticket = Ticket::create([
            'subject' => 'Test Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        // Create public reply
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Public reply',
            'is_internal' => false,
        ]);

        // Create internal note
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Internal note',
            'is_internal' => true,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('tickets.show', $ticket->uuid));

        $response->assertSee('Public reply')
            ->assertDontSee('Internal note');
    }

    public function test_agent_can_see_internal_notes()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Assign agent to office
        $agent->offices()->attach($office->id);

        $ticket = Ticket::create([
            'subject' => 'Test Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        // Create internal note
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Internal note',
            'is_internal' => true,
        ]);

        $response = $this->actingAs($agent)
            ->get(route('tickets.show', $ticket->uuid));

        $response->assertSee('Internal note')
            ->assertSee('Internal Note'); // Badge text
    }

    public function test_internal_note_scope_methods()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Assign agent to office
        $agent->offices()->attach($office->id);

        $ticket = Ticket::create([
            'subject' => 'Test Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        $publicReply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Public reply',
            'is_internal' => false,
        ]);

        $internalNote = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Internal note',
            'is_internal' => true,
        ]);

        // Test scope methods
        $this->assertEquals(1, $ticket->publicReplies()->count());
        $this->assertEquals(1, $ticket->internalNotes()->count());
        $this->assertEquals(2, $ticket->replies()->count());

        // Test helper methods
        $this->assertTrue($internalNote->isInternal());
        $this->assertFalse($internalNote->isPublic());
        $this->assertTrue($publicReply->isPublic());
        $this->assertFalse($publicReply->isInternal());
    }
}
