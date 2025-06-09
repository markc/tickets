<?php

namespace Tests\Feature;

use App\Models\FAQ;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
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

    public function test_search_requires_authentication()
    {
        $response = $this->get('/search?q=test');
        $response->assertRedirect('/login');
    }

    public function test_search_validation()
    {
        $user = User::factory()->create();

        // Empty query
        $response = $this->actingAs($user)->get('/search');
        $response->assertSessionHasErrors(['q']);

        // Query too long
        $response = $this->actingAs($user)->get('/search?q='.str_repeat('a', 256));
        $response->assertSessionHasErrors(['q']);

        // Invalid type
        $response = $this->actingAs($user)->get('/search?q=test&type=invalid');
        $response->assertSessionHasErrors(['type']);
    }

    public function test_customer_can_search_own_tickets()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        // Create tickets
        $ownTicket = Ticket::factory()->create([
            'creator_id' => $customer->id,
            'subject' => 'Network connectivity problem',
        ]);
        $otherTicket = Ticket::factory()->create([
            'creator_id' => $otherCustomer->id,
            'subject' => 'Network issue for another user',
        ]);

        // Make searchable
        $ownTicket->searchable();
        $otherTicket->searchable();

        $response = $this->actingAs($customer)->get('/search?q=network');

        $response->assertSuccessful();
        $response->assertSee('Network connectivity problem');
        $response->assertDontSee('Network issue for another user');
    }

    public function test_admin_can_search_all_tickets()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);

        $ticket1 = Ticket::factory()->create([
            'creator_id' => $customer1->id,
            'subject' => 'Server error report',
        ]);
        $ticket2 = Ticket::factory()->create([
            'creator_id' => $customer2->id,
            'subject' => 'Server maintenance request',
        ]);

        $ticket1->searchable();
        $ticket2->searchable();

        $response = $this->actingAs($admin)->get('/search?q=server');

        $response->assertSuccessful();
        $response->assertSee('Server error report');
        $response->assertSee('Server maintenance request');
    }

    public function test_search_faqs()
    {
        $user = User::factory()->create();
        $office = Office::first();

        $faq1 = FAQ::create([
            'question' => 'How to reset password?',
            'answer' => 'Click on forgot password link',
            'office_id' => $office->id,
            'is_published' => true,
        ]);
        $faq2 = FAQ::create([
            'question' => 'How to contact support?',
            'answer' => 'Email us at support@tikm.com',
            'office_id' => $office->id,
            'is_published' => true,
        ]);
        $faq3 = FAQ::create([
            'question' => 'Draft FAQ about passwords',
            'answer' => 'This is not published',
            'office_id' => $office->id,
            'is_published' => false,
        ]);

        $faq1->searchable();
        $faq2->searchable();
        $faq3->searchable();

        $response = $this->actingAs($user)->get('/search?q=password&type=faqs');

        $response->assertSuccessful();
        $response->assertSee('How to reset password?');
        $response->assertDontSee('Draft FAQ about passwords'); // Unpublished
        $response->assertDontSee('How to contact support?'); // Different topic
    }

    public function test_search_type_filtering()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $office = Office::first();

        // Create searchable content
        $ticket = Ticket::factory()->create([
            'creator_id' => $user->id,
            'subject' => 'Login problem needs help',
        ]);
        $faq = FAQ::create([
            'question' => 'Login troubleshooting guide',
            'answer' => 'Steps to fix login issues',
            'office_id' => $office->id,
            'is_published' => true,
        ]);

        $ticket->searchable();
        $faq->searchable();

        // Search all
        $response = $this->actingAs($user)->get('/search?q=login&type=all');
        $response->assertSee('Login problem needs help');
        $response->assertSee('Login troubleshooting guide');

        // Search tickets only
        $response = $this->actingAs($user)->get('/search?q=login&type=tickets');
        $response->assertSee('Login problem needs help');
        $response->assertDontSee('Login troubleshooting guide');

        // Search FAQs only
        $response = $this->actingAs($user)->get('/search?q=login&type=faqs');
        $response->assertDontSee('Login problem needs help');
        $response->assertSee('Login troubleshooting guide');
    }

    public function test_search_pagination()
    {
        $user = User::factory()->create(['role' => 'customer']);

        // Create 15 tickets with similar subject
        for ($i = 1; $i <= 15; $i++) {
            $ticket = Ticket::factory()->create([
                'creator_id' => $user->id,
                'subject' => "Technical issue number $i",
            ]);
            $ticket->searchable();
        }

        $response = $this->actingAs($user)->get('/search?q=technical');

        $response->assertSuccessful();
        // Should see first 10 results
        $response->assertSee('Technical issue number 1');
        $response->assertSee('Technical issue number 10');
        // Should not see 11-15 on first page
        $response->assertDontSee('Technical issue number 11');

        // Check pagination links exist
        $response->assertSee('Next');
    }

    public function test_search_highlights_in_results()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $ticket = Ticket::factory()->create([
            'creator_id' => $user->id,
            'subject' => 'Email delivery problem',
            'content' => 'Emails are not being delivered to customers',
        ]);
        $ticket->searchable();

        $response = $this->actingAs($user)->get('/search?q=email');

        $response->assertSuccessful();
        $response->assertSee('Email delivery problem');
        $response->assertSee('Emails are not being delivered');
    }
}
