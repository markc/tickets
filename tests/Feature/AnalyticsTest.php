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

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary data
        TicketStatus::create(['name' => 'Open', 'color' => '#3b82f6']);
        TicketStatus::create(['name' => 'Closed', 'color' => '#10b981']);
        TicketPriority::create(['name' => 'Medium', 'color' => '#f59e0b']);
        TicketPriority::create(['name' => 'High', 'color' => '#ef4444']);
        Office::create(['name' => 'Technical Support', 'is_internal' => false]);
    }

    public function test_customers_cannot_access_analytics()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('analytics.dashboard'))
            ->assertStatus(403);
    }

    public function test_agents_can_access_analytics()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $this->actingAs($agent)
            ->get(route('analytics.dashboard'))
            ->assertOk()
            ->assertViewIs('analytics.dashboard')
            ->assertViewHas('analytics');
    }

    public function test_admins_can_access_analytics()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('analytics.dashboard'))
            ->assertOk()
            ->assertViewIs('analytics.dashboard')
            ->assertViewHas('analytics');
    }

    public function test_analytics_dashboard_contains_overview_metrics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Create test tickets
        $ticket1 = Ticket::create([
            'subject' => 'Test Ticket 1',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::where('name', 'Open')->first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        $ticket2 = Ticket::create([
            'subject' => 'Test Ticket 2',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::where('name', 'Closed')->first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('analytics.dashboard'));

        $response->assertOk();

        $analytics = $response->viewData('analytics');

        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('overview', $analytics);
        $this->assertArrayHasKey('tickets', $analytics);
        $this->assertArrayHasKey('agents', $analytics);
        $this->assertArrayHasKey('offices', $analytics);
        $this->assertArrayHasKey('sla', $analytics);
        $this->assertArrayHasKey('trends', $analytics);

        // Check overview metrics
        $overview = $analytics['overview'];
        $this->assertEquals(2, $overview['total_tickets']);
        $this->assertEquals(2, $overview['new_tickets']);
        $this->assertEquals(1, $overview['resolved_tickets']);
        $this->assertEquals(1, $overview['open_tickets']);
    }

    public function test_analytics_navigation_link_visible_to_agents()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->get('/dashboard');

        $response->assertSee('Analytics');
        $response->assertSee(route('analytics.dashboard'));
    }

    public function test_analytics_navigation_link_hidden_from_customers()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertDontSee('Analytics');
    }

    public function test_date_range_filtering_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('analytics.dashboard', ['date_range' => '7']));

        $response->assertOk();

        $dateRange = $response->viewData('dateRange');
        $this->assertEquals('7', $dateRange);
    }

    public function test_ticket_metrics_include_status_and_priority_breakdown()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Create tickets with different statuses and priorities
        Ticket::create([
            'subject' => 'Open Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::where('name', 'Open')->first()->id,
            'ticket_priority_id' => TicketPriority::where('name', 'High')->first()->id,
        ]);

        Ticket::create([
            'subject' => 'Closed Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::where('name', 'Closed')->first()->id,
            'ticket_priority_id' => TicketPriority::where('name', 'Medium')->first()->id,
        ]);

        $response = $this->actingAs($admin)->get(route('analytics.dashboard'));

        $analytics = $response->viewData('analytics');
        $tickets = $analytics['tickets'];

        $this->assertArrayHasKey('by_status', $tickets);
        $this->assertArrayHasKey('by_priority', $tickets);
        $this->assertArrayHasKey('top_customers', $tickets);

        // Check that we have status breakdown
        $this->assertNotEmpty($tickets['by_status']);
        $this->assertNotEmpty($tickets['by_priority']);
    }

    public function test_agent_performance_metrics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Assign agent to office
        $agent->offices()->attach($office->id);

        // Create ticket assigned to agent
        $ticket = Ticket::create([
            'subject' => 'Agent Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'assigned_to_id' => $agent->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::where('name', 'Closed')->first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
            'resolved_at' => now(),
        ]);

        // Create reply from agent
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'content' => 'Agent response',
            'is_internal' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('analytics.dashboard'));

        $analytics = $response->viewData('analytics');
        $agents = $analytics['agents'];

        $this->assertNotEmpty($agents);

        $agentMetrics = $agents->first();
        $this->assertEquals($agent->name, $agentMetrics['agent']);
        $this->assertEquals(1, $agentMetrics['total_assigned']);
        $this->assertEquals(1, $agentMetrics['resolved_tickets']);
        $this->assertEquals(1, $agentMetrics['replies_sent']);
    }
}
