<?php

namespace Tests\Feature;

use App\Models\SavedSearch;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $agent;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->agent = User::factory()->create(['role' => 'agent']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_basic_search_functionality()
    {
        $ticket = Ticket::factory()->create([
            'subject' => 'Test Search Subject',
            'content' => 'This is a test ticket for search functionality',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'test']));

        $response->assertStatus(200);
        $response->assertSee('Test Search Subject');
    }

    public function test_advanced_search_with_filters()
    {
        $status = TicketStatus::factory()->create(['name' => 'Open']);
        $priority = TicketPriority::factory()->create(['name' => 'High']);

        $ticket = Ticket::factory()->create([
            'subject' => 'Filtered Test Ticket',
            'status_id' => $status->id,
            'priority_id' => $priority->id,
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('search', [
                'q' => 'test',
                'status' => ['Open'],
                'priority' => ['High'],
                'date_from' => now()->subDays(7)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertSee('Filtered Test Ticket');
    }

    public function test_save_search_functionality()
    {
        $searchData = [
            'name' => 'My Saved Search',
            'description' => 'Test search description',
            'search_params' => [
                'q' => 'test',
                'status' => ['Open'],
                'priority' => ['High'],
            ],
            'is_public' => false,
        ];

        $response = $this->actingAs($this->agent)
            ->postJson(route('search.save'), $searchData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('saved_searches', [
            'name' => 'My Saved Search',
            'user_id' => $this->agent->id,
        ]);
    }

    public function test_delete_saved_search()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->agent->id,
            'name' => 'Test Search',
        ]);

        $response = $this->actingAs($this->agent)
            ->deleteJson(route('search.saved.delete', $savedSearch));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('saved_searches', [
            'id' => $savedSearch->id,
        ]);
    }

    public function test_public_saved_search_visibility()
    {
        $publicSearch = SavedSearch::factory()->create([
            'user_id' => $this->admin->id,
            'name' => 'Public Search',
            'is_public' => true,
        ]);

        $privateSearch = SavedSearch::factory()->create([
            'user_id' => $this->admin->id,
            'name' => 'Private Search',
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->agent)
            ->getJson(route('api.search.saved'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Public Search']);
        $response->assertJsonMissing(['name' => 'Private Search']);
    }

    public function test_search_authorization()
    {
        $customerTicket = Ticket::factory()->create([
            'creator_id' => $this->customer->id,
            'subject' => 'Customer Only Ticket',
        ]);

        $otherTicket = Ticket::factory()->create([
            'creator_id' => $this->admin->id,
            'subject' => 'Admin Ticket',
        ]);

        // Customer should only see their own tickets
        $response = $this->actingAs($this->customer)
            ->get(route('search', ['q' => 'ticket']));

        $response->assertStatus(200);
        $response->assertSee('Customer Only Ticket');
        $response->assertDontSee('Admin Ticket');
    }

    public function test_saved_search_usage_tracking()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->agent->id,
            'usage_count' => 0,
        ]);

        $this->actingAs($this->agent)
            ->get(route('search', [
                'q' => 'test',
                'saved_search' => $savedSearch->id,
            ]));

        $savedSearch->refresh();
        $this->assertEquals(1, $savedSearch->usage_count);
        $this->assertNotNull($savedSearch->last_used_at);
    }

    public function test_filter_options_based_on_user_role()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('search', ['q' => 'test']));

        $response->assertStatus(200);
        // Customers should not see assignee filters
        $response->assertDontSee('Assigned To');

        $response = $this->actingAs($this->agent)
            ->get(route('search', ['q' => 'test']));

        $response->assertStatus(200);
        // Agents should see assignee filters
        $response->assertSee('Assigned To');
    }

    public function test_search_form_validation()
    {
        $response = $this->actingAs($this->agent)
            ->get(route('search', ['q' => '']));

        $response->assertSessionHasErrors(['q']);

        $response = $this->actingAs($this->agent)
            ->get(route('search', [
                'q' => 'test',
                'date_from' => '2024-01-01',
                'date_to' => '2023-12-31', // Invalid: to date before from date
            ]));

        $response->assertSessionHasErrors(['date_to']);
    }

    public function test_saved_search_permissions()
    {
        $otherUserSearch = SavedSearch::factory()->create([
            'user_id' => $this->admin->id,
            'is_public' => false,
        ]);

        // Agent should not be able to delete admin's private search
        $response = $this->actingAs($this->agent)
            ->deleteJson(route('search.saved.delete', $otherUserSearch));

        $response->assertStatus(403);
    }

    public function test_search_url_generation()
    {
        $savedSearch = SavedSearch::factory()->create([
            'search_params' => [
                'q' => 'test query',
                'status' => ['Open'],
                'priority' => ['High'],
            ],
        ]);

        $url = $savedSearch->getSearchUrl();

        $this->assertStringContainsString('q=test%20query', $url);
        $this->assertStringContainsString('saved_search='.$savedSearch->id, $url);
    }
}
