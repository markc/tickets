<?php

namespace Tests\Unit;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TicketAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TicketAssignmentService;

        // Create test data
        $this->seed(\Database\Seeders\OfficeSeeder::class);
        $this->seed(\Database\Seeders\TicketStatusSeeder::class);
        $this->seed(\Database\Seeders\TicketPrioritySeeder::class);
    }

    public function test_auto_assign_ticket_with_round_robin()
    {
        $office = Office::first();

        // Create 3 agents for the office
        $agents = User::factory()->count(3)->create(['role' => 'agent']);
        foreach ($agents as $agent) {
            $agent->offices()->attach($office->id);
        }

        // Create and assign 3 tickets
        $tickets = [];
        for ($i = 0; $i < 3; $i++) {
            $ticket = Ticket::factory()->create([
                'office_id' => $office->id,
                'assigned_to_id' => null,
            ]);

            $assignedAgent = $this->service->autoAssignTicket($ticket);
            $tickets[] = ['ticket' => $ticket, 'agent' => $assignedAgent];
        }

        // Verify round-robin assignment
        $this->assertEquals($agents[0]->id, $tickets[0]['agent']->id);
        $this->assertEquals($agents[1]->id, $tickets[1]['agent']->id);
        $this->assertEquals($agents[2]->id, $tickets[2]['agent']->id);

        // Create another ticket - should go back to first agent
        $ticket4 = Ticket::factory()->create([
            'office_id' => $office->id,
            'assigned_to_id' => null,
        ]);
        $assignedAgent4 = $this->service->autoAssignTicket($ticket4);
        $this->assertEquals($agents[0]->id, $assignedAgent4->id);
    }

    public function test_assign_ticket_to_specific_agent()
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $assigner = User::factory()->create(['role' => 'admin']);

        $this->service->reassignTicket($ticket, $agent);
        $this->assertEquals($agent->id, $ticket->fresh()->assigned_to_id);

        // Check timeline entry
        $timelineEntry = $ticket->timeline()->latest()->first();
        $this->assertNotNull($timelineEntry);
        $this->assertEquals('assigned', $timelineEntry->action);
        $this->assertEquals($assigner->id, $timelineEntry->user_id);
    }

    public function test_reassign_ticket()
    {
        $oldAgent = User::factory()->create(['role' => 'agent']);
        $newAgent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create(['assigned_to_id' => $oldAgent->id]);
        $assigner = User::factory()->create(['role' => 'admin']);

        $this->service->reassignTicket($ticket, $newAgent);
        $this->assertEquals($newAgent->id, $ticket->fresh()->assigned_to_id);

        // Check timeline entries
        $timelineEntries = $ticket->timeline()->orderBy('id', 'desc')->limit(2)->get();
        $this->assertCount(2, $timelineEntries);
        $this->assertEquals('unassigned', $timelineEntries[1]->action);
        $this->assertEquals('assigned', $timelineEntries[0]->action);
    }

    public function test_get_next_agent_for_office()
    {
        $office = Office::first();
        $agents = User::factory()->count(2)->create(['role' => 'agent']);
        foreach ($agents as $agent) {
            $agent->offices()->attach($office->id);
        }

        // Clear cache to ensure clean state
        Cache::forget("last_assigned_agent_office_{$office->id}");

        $agent1 = $this->service->getNextAgentForOffice($office->id);
        $agent2 = $this->service->getNextAgentForOffice($office->id);
        $agent3 = $this->service->getNextAgentForOffice($office->id);

        $this->assertEquals($agents[0]->id, $agent1->id);
        $this->assertEquals($agents[1]->id, $agent2->id);
        $this->assertEquals($agents[0]->id, $agent3->id); // Back to first agent
    }

    public function test_no_agents_available_returns_null()
    {
        $office = Office::first();
        $ticket = Ticket::factory()->create(['office_id' => $office->id]);

        $result = $this->service->autoAssignTicket($ticket);

        $this->assertNull($result);
        $this->assertNull($ticket->fresh()->assigned_to_id);
    }

    public function test_get_agent_workload()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $openStatus = \App\Models\TicketStatus::where('name', 'Open')->first();
        $closedStatus = \App\Models\TicketStatus::where('name', 'Closed')->first();

        // Create tickets
        Ticket::factory()->count(3)->create([
            'assigned_to_id' => $agent->id,
            'ticket_status_id' => $openStatus->id,
        ]);
        Ticket::factory()->count(2)->create([
            'assigned_to_id' => $agent->id,
            'ticket_status_id' => $closedStatus->id,
        ]);

        $workload = $this->service->getAgentWorkload($agent);

        $this->assertEquals(5, $workload['total_assigned']);
        $this->assertEquals(3, $workload['open_tickets']);
        $this->assertArrayHasKey('high_priority', $workload);
    }
}
