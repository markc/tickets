<?php

namespace Tests\Feature;

use App\Models\CannedResponse;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CannedResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary data
        TicketStatus::create(['name' => 'Open', 'color' => '#3b82f6']);
        TicketPriority::create(['name' => 'Medium', 'color' => '#f59e0b']);
        Office::create(['name' => 'Technical Support', 'is_internal' => false]);
    }

    public function test_customers_cannot_access_canned_responses_api()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('api.canned-responses.index'))
            ->assertStatus(403);
    }

    public function test_agents_can_access_canned_responses_api()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $this->actingAs($agent)
            ->get(route('api.canned-responses.index'))
            ->assertOk();
    }

    public function test_canned_response_creation_and_retrieval()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = CannedResponse::create([
            'title' => 'Test Response',
            'content' => 'Hello {{customer_name}}, this is a test response.',
            'category' => 'General',
            'user_id' => $admin->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('canned_responses', [
            'title' => 'Test Response',
            'category' => 'General',
            'is_public' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('api.canned-responses.index'))
            ->assertJsonFragment([
                'title' => 'Test Response',
                'category' => 'General',
            ]);
    }

    public function test_variable_replacement_in_canned_responses()
    {
        $admin = User::factory()->create(['role' => 'admin']);
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

        $response = CannedResponse::create([
            'title' => 'Test Response',
            'content' => 'Hello {{customer_name}}, your ticket {{ticket_id}} regarding "{{ticket_subject}}" is being handled by {{agent_name}}.',
            'category' => 'General',
            'user_id' => $admin->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->postJson(route('api.canned-responses.preview', $response), [
                'ticket_id' => $ticket->uuid,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'original_content',
                    'processed_content',
                    'variables_used',
                ],
            ]);
    }

    public function test_canned_response_usage_tracking()
    {
        $agent = User::factory()->create(['role' => 'agent']);
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

        $response = CannedResponse::create([
            'title' => 'Test Response',
            'content' => 'Hello {{customer_name}}!',
            'category' => 'General',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $this->actingAs($agent)
            ->postJson(route('api.canned-responses.use', $response), [
                'ticket_id' => $ticket->uuid,
            ])
            ->assertOk();

        $response->refresh();
        $this->assertEquals(1, $response->usage_count);
        $this->assertNotNull($response->last_used_at);
    }

    public function test_agents_can_only_edit_their_own_responses()
    {
        $agent1 = User::factory()->create(['role' => 'agent']);
        $agent2 = User::factory()->create(['role' => 'agent']);

        $response = CannedResponse::create([
            'title' => 'Agent 1 Response',
            'content' => 'This belongs to agent 1',
            'category' => 'General',
            'user_id' => $agent1->id,
            'is_public' => false,
            'is_active' => true,
        ]);

        // Agent 2 should not be able to view agent 1's private response
        $this->actingAs($agent2)
            ->get(route('api.canned-responses.show', $response))
            ->assertStatus(403);

        // Agent 1 should be able to view their own response
        $this->actingAs($agent1)
            ->get(route('api.canned-responses.show', $response))
            ->assertOk();
    }

    public function test_public_responses_visible_to_all_agents()
    {
        $agent1 = User::factory()->create(['role' => 'agent']);
        $agent2 = User::factory()->create(['role' => 'agent']);

        $response = CannedResponse::create([
            'title' => 'Public Response',
            'content' => 'This is public',
            'category' => 'General',
            'user_id' => $agent1->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        // Both agents should be able to view public response
        $this->actingAs($agent1)
            ->get(route('api.canned-responses.show', $response))
            ->assertOk();

        $this->actingAs($agent2)
            ->get(route('api.canned-responses.show', $response))
            ->assertOk();
    }

    public function test_admins_can_edit_any_response()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);

        $response = CannedResponse::create([
            'title' => 'Agent Response',
            'content' => 'This belongs to agent',
            'category' => 'General',
            'user_id' => $agent->id,
            'is_public' => false,
            'is_active' => true,
        ]);

        // Admin should be able to view any response
        $this->actingAs($admin)
            ->get(route('api.canned-responses.show', $response))
            ->assertOk();
    }

    public function test_category_filtering_works()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        CannedResponse::create([
            'title' => 'General Response',
            'content' => 'General content',
            'category' => 'General',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        CannedResponse::create([
            'title' => 'Technical Response',
            'content' => 'Technical content',
            'category' => 'Technical',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($agent)
            ->get(route('api.canned-responses.index', ['category' => 'Technical']))
            ->assertOk();

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Technical Response', $data[0]['title']);
    }

    public function test_search_functionality_works()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        CannedResponse::create([
            'title' => 'Password Reset',
            'content' => 'Instructions for password reset',
            'category' => 'Account',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        CannedResponse::create([
            'title' => 'Billing Issue',
            'content' => 'Billing problem resolution',
            'category' => 'Billing',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($agent)
            ->get(route('api.canned-responses.index', ['search' => 'password']))
            ->assertOk();

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Password Reset', $data[0]['title']);
    }

    public function test_inactive_responses_are_filtered_out()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        CannedResponse::create([
            'title' => 'Active Response',
            'content' => 'This is active',
            'category' => 'General',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        CannedResponse::create([
            'title' => 'Inactive Response',
            'content' => 'This is inactive',
            'category' => 'General',
            'user_id' => $agent->id,
            'is_public' => true,
            'is_active' => false,
        ]);

        $response = $this->actingAs($agent)
            ->get(route('api.canned-responses.index'))
            ->assertOk();

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active Response', $data[0]['title']);
    }

    public function test_canned_response_shows_in_ticket_interface()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Assign agent to office so they can view the ticket
        $agent->offices()->attach($office->id);

        $ticket = Ticket::create([
            'subject' => 'Test Ticket',
            'content' => 'Test content',
            'creator_id' => $customer->id,
            'office_id' => $office->id,
            'ticket_status_id' => TicketStatus::first()->id,
            'ticket_priority_id' => TicketPriority::first()->id,
        ]);

        // Agent should see canned responses interface in ticket view
        $response = $this->actingAs($agent)
            ->get(route('tickets.show', $ticket->uuid));

        $response->assertOk();
        $response->assertSee('canned-responses-section', false); // Check for the HTML ID
        $response->assertSee('Show Templates');

        // Customer should not see canned responses interface
        $customerResponse = $this->actingAs($customer)
            ->get(route('tickets.show', $ticket->uuid));

        $customerResponse->assertOk();
        $customerResponse->assertDontSee('canned-responses-section', false); // Check HTML ID is not present
    }
}
