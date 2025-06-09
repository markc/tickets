<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Services\TicketMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketMergeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $agent;

    private User $customer;

    private Office $office;

    private TicketStatus $status;

    private TicketPriority $priority;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->agent = User::factory()->create(['role' => 'agent']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->office = Office::factory()->create();
        $this->status = TicketStatus::factory()->create(['name' => 'Open']);
        $this->priority = TicketPriority::factory()->create(['name' => 'Medium']);

        // Associate agent with office
        $this->agent->offices()->attach($this->office);
    }

    public function test_admin_can_access_merge_interface()
    {
        $ticket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('tickets.merge.show', $ticket));

        $response->assertStatus(200);
        $response->assertSee('Merge Ticket');
        $response->assertSee($ticket->uuid);
    }

    public function test_agent_can_access_merge_interface_for_office_tickets()
    {
        $ticket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->agent)
            ->get(route('tickets.merge.show', $ticket));

        $response->assertStatus(200);
        $response->assertSee('Merge Ticket');
    }

    public function test_agent_cannot_access_merge_interface_for_other_office_tickets()
    {
        $otherOffice = Office::factory()->create();
        $ticket = Ticket::factory()->create([
            'office_id' => $otherOffice->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->agent)
            ->get(route('tickets.merge.show', $ticket));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_merge_interface()
    {
        $ticket = Ticket::factory()->create([
            'creator_id' => $this->customer->id,
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('tickets.merge.show', $ticket));

        $response->assertStatus(403);
    }

    public function test_successful_ticket_merge()
    {
        $sourceTicket = Ticket::factory()->create([
            'subject' => 'Source Ticket',
            'content' => 'Source content',
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
            'is_merged' => false,
        ]);

        $targetTicket = Ticket::factory()->create([
            'subject' => 'Target Ticket',
            'content' => 'Target content',
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
            'is_merged' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('tickets.merge', $sourceTicket), [
                'target_ticket_uuid' => $targetTicket->uuid,
                'reason' => 'Duplicate issue',
            ]);

        $response->assertRedirect(route('tickets.show', $targetTicket));
        $response->assertSessionHas('success');

        // Verify source ticket is marked as merged
        $sourceTicket->refresh();
        $this->assertTrue($sourceTicket->is_merged);
        $this->assertEquals($targetTicket->uuid, $sourceTicket->merged_into_id);
        $this->assertEquals($this->admin->id, $sourceTicket->merged_by_id);
        $this->assertEquals('Duplicate issue', $sourceTicket->merge_reason);
        $this->assertNotNull($sourceTicket->merged_at);
    }

    public function test_cannot_merge_tickets_from_different_offices()
    {
        $otherOffice = Office::factory()->create();

        $sourceTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $targetTicket = Ticket::factory()->create([
            'office_id' => $otherOffice->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('tickets.merge', $sourceTicket), [
                'target_ticket_uuid' => $targetTicket->uuid,
                'reason' => 'Test merge',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['merge']);

        // Verify tickets were not merged
        $sourceTicket->refresh();
        $this->assertFalse($sourceTicket->is_merged);
    }

    public function test_cannot_merge_already_merged_ticket()
    {
        $sourceTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
            'is_merged' => true,
        ]);

        $targetTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('tickets.merge', $sourceTicket), [
                'target_ticket_uuid' => $targetTicket->uuid,
                'reason' => 'Test merge',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['merge']);
    }

    public function test_search_for_merge_targets()
    {
        $sourceTicket = Ticket::factory()->create([
            'subject' => 'Login Issue',
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $targetTicket = Ticket::factory()->create([
            'subject' => 'Cannot Login to Account',
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('tickets.merge.search', $sourceTicket).'?q=login');

        $response->assertStatus(200);
        $response->assertJsonFragment(['uuid' => $targetTicket->uuid]);
    }

    public function test_merge_preview_functionality()
    {
        $sourceTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $targetTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('tickets.merge.preview', $sourceTicket).'?target_uuid='.$targetTicket->uuid);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'target_ticket',
            'can_merge',
            'similarity_score',
            'warnings',
        ]);
    }

    public function test_ticket_merge_service_suggestions()
    {
        $ticket = Ticket::factory()->create([
            'subject' => 'Email delivery problem',
            'content' => 'Emails are not being delivered',
            'creator_id' => $this->customer->id,
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $similarTicket = Ticket::factory()->create([
            'subject' => 'Email not working',
            'content' => 'Cannot receive emails',
            'creator_id' => $this->customer->id,
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $service = new TicketMergeService;
        $suggestions = $service->getSuggestedMergeTargets($ticket);

        $this->assertCount(1, $suggestions);
        $this->assertEquals($similarTicket->uuid, $suggestions[0]['ticket']->uuid);
        $this->assertGreaterThan(0, $suggestions[0]['similarity_score']);
    }

    public function test_ticket_relationships_work_correctly()
    {
        $sourceTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $targetTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        // Merge the tickets
        $service = new TicketMergeService;
        $service->mergeTickets($sourceTicket, $targetTicket, $this->admin, 'Test merge');

        // Test relationships
        $sourceTicket->refresh();
        $targetTicket->refresh();

        $this->assertEquals($targetTicket->uuid, $sourceTicket->mergedInto->uuid);
        $this->assertTrue($targetTicket->mergedTickets->contains($sourceTicket));
        $this->assertEquals($this->admin->id, $sourceTicket->mergedBy->id);
    }

    public function test_merge_button_visible_to_authorized_users()
    {
        $ticket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        // Admin should see merge button
        $response = $this->actingAs($this->admin)
            ->get(route('tickets.show', $ticket));
        $response->assertSee('Merge Ticket');

        // Agent should see merge button for their office
        $response = $this->actingAs($this->agent)
            ->get(route('tickets.show', $ticket));
        $response->assertSee('Merge Ticket');

        // Customer should not see merge button
        $response = $this->actingAs($this->customer)
            ->get(route('tickets.show', $ticket));
        $response->assertDontSee('Merge Ticket');
    }

    public function test_merged_ticket_indicator_displays()
    {
        $sourceTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
            'is_merged' => true,
            'merged_at' => now(),
            'merged_by_id' => $this->admin->id,
            'merge_reason' => 'Duplicate issue',
        ]);

        $targetTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
        ]);

        $sourceTicket->update(['merged_into_id' => $targetTicket->uuid]);

        $response = $this->actingAs($this->admin)
            ->get(route('tickets.show', $sourceTicket));

        $response->assertSee('This ticket has been merged');
        $response->assertSee('Duplicate issue');
        $response->assertSee($this->admin->name);
    }

    public function test_scope_methods_work_correctly()
    {
        $normalTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
            'is_merged' => false,
        ]);

        $mergedTicket = Ticket::factory()->create([
            'office_id' => $this->office->id,
            'ticket_status_id' => $this->status->id,
            'ticket_priority_id' => $this->priority->id,
            'is_merged' => true,
        ]);

        // Test notMerged scope
        $notMergedTickets = Ticket::notMerged()->get();
        $this->assertTrue($notMergedTickets->contains($normalTicket));
        $this->assertFalse($notMergedTickets->contains($mergedTicket));

        // Test merged scope
        $mergedTickets = Ticket::merged()->get();
        $this->assertFalse($mergedTickets->contains($normalTicket));
        $this->assertTrue($mergedTickets->contains($mergedTicket));
    }
}
