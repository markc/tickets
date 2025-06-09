<?php

use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->agent = User::factory()->create(['role' => 'agent']);
    $this->admin = User::factory()->create(['role' => 'admin']);

    $this->office = Office::factory()->create();
    $this->status = TicketStatus::factory()->create();
    $this->priority = TicketPriority::factory()->create();

    $this->ticket = Ticket::factory()->create([
        'creator_id' => $this->customer->id,
        'office_id' => $this->office->id,
        'ticket_status_id' => $this->status->id,
        'ticket_priority_id' => $this->priority->id,
    ]);
});

test('customer can get their tickets via API', function () {
    Sanctum::actingAs($this->customer);

    $response = $this->getJson('/api/tickets');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'uuid',
                    'subject',
                    'content',
                    'creator',
                    'office',
                    'status',
                    'priority',
                    'created_at',
                    'updated_at',
                ],
            ],
            'pagination',
        ]);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.creator.id'))->toBe($this->customer->id);
});

test('customer can only see their own tickets', function () {
    $otherCustomer = User::factory()->create(['role' => 'customer']);
    $otherTicket = Ticket::factory()->create(['creator_id' => $otherCustomer->id]);

    Sanctum::actingAs($this->customer);

    $response = $this->getJson('/api/tickets');

    $response->assertOk();

    $ticketIds = collect($response->json('data'))->pluck('id');
    expect($ticketIds)->toContain($this->ticket->id);
    expect($ticketIds)->not->toContain($otherTicket->id);
});

test('customer can create ticket via API', function () {
    Sanctum::actingAs($this->customer);

    $ticketData = [
        'subject' => 'Test API Ticket',
        'content' => 'This is a test ticket created via API',
        'office_id' => $this->office->id,
        'priority_id' => $this->priority->id,
    ];

    $response = $this->postJson('/api/tickets', $ticketData);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'uuid',
                'subject',
                'content',
                'creator',
                'office',
                'status',
                'priority',
            ],
            'message',
        ]);

    expect($response->json('data.subject'))->toBe('Test API Ticket');
    expect($response->json('data.creator.id'))->toBe($this->customer->id);

    $this->assertDatabaseHas('tickets', [
        'subject' => 'Test API Ticket',
        'creator_id' => $this->customer->id,
    ]);
});

test('customer can view specific ticket via API', function () {
    Sanctum::actingAs($this->customer);

    $response = $this->getJson("/api/tickets/{$this->ticket->uuid}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'uuid',
                'subject',
                'content',
                'creator',
                'office',
                'status',
                'priority',
                'replies',
                'attachments',
                'timeline',
            ],
        ]);

    expect($response->json('data.uuid'))->toBe($this->ticket->uuid);
});

test('customer cannot view other customer tickets', function () {
    $otherCustomer = User::factory()->create(['role' => 'customer']);
    $otherTicket = Ticket::factory()->create(['creator_id' => $otherCustomer->id]);

    Sanctum::actingAs($this->customer);

    $response = $this->getJson("/api/tickets/{$otherTicket->uuid}");

    $response->assertForbidden();
});

test('agent can see tickets from their offices', function () {
    $this->agent->offices()->attach($this->office);

    Sanctum::actingAs($this->agent);

    $response = $this->getJson('/api/tickets');

    $response->assertOk();

    $ticketIds = collect($response->json('data'))->pluck('id');
    expect($ticketIds)->toContain($this->ticket->id);
});

test('admin can see all tickets', function () {
    $otherTicket = Ticket::factory()->create();

    Sanctum::actingAs($this->admin);

    $response = $this->getJson('/api/tickets');

    $response->assertOk();

    $ticketIds = collect($response->json('data'))->pluck('id');
    expect($ticketIds)->toContain($this->ticket->id);
    expect($ticketIds)->toContain($otherTicket->id);
});

test('user can get ticket statistics', function () {
    Sanctum::actingAs($this->customer);

    $response = $this->getJson('/api/tickets/stats');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total',
                'open',
                'closed',
                'assigned_to_me',
            ],
        ]);

    expect($response->json('data.total'))->toBeGreaterThan(0);
});

test('user can get form data for tickets', function () {
    Sanctum::actingAs($this->customer);

    $response = $this->getJson('/api/tickets/form-data');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'offices',
                'statuses',
                'priorities',
            ],
        ]);

    expect($response->json('data.offices'))->not->toBeEmpty();
});

test('API validates ticket creation data', function () {
    Sanctum::actingAs($this->customer);

    $response = $this->postJson('/api/tickets', [
        'subject' => '', // Empty subject
        'content' => '', // Empty content
        'office_id' => 999, // Non-existent office
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['subject', 'content', 'office_id']);
});

test('API supports ticket filtering and search', function () {
    $ticket2 = Ticket::factory()->create([
        'creator_id' => $this->customer->id,
        'subject' => 'Different Subject',
        'office_id' => $this->office->id,
    ]);

    Sanctum::actingAs($this->customer);

    // Test search functionality
    $response = $this->getJson('/api/tickets?search='.urlencode($this->ticket->subject));

    $response->assertOk();

    $subjects = collect($response->json('data'))->pluck('subject');
    expect($subjects)->toContain($this->ticket->subject);
    expect($subjects)->not->toContain($ticket2->subject);
});

test('API supports pagination', function () {
    // Create additional tickets
    Ticket::factory()->count(20)->create(['creator_id' => $this->customer->id]);

    Sanctum::actingAs($this->customer);

    $response = $this->getJson('/api/tickets?per_page=5');

    $response->assertOk();

    expect($response->json('data'))->toHaveCount(5);
    expect($response->json('pagination.per_page'))->toBe(5);
    expect($response->json('pagination.total'))->toBe(21); // 20 + 1 from beforeEach
});

test('customer can update their own ticket', function () {
    Sanctum::actingAs($this->customer);

    $response = $this->putJson("/api/tickets/{$this->ticket->uuid}", [
        'subject' => 'Updated Subject',
        'content' => 'Updated content',
    ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'subject' => 'Updated Subject',
            ],
            'message' => 'Ticket updated successfully',
        ]);

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'subject' => 'Updated Subject',
    ]);
});

test('agent can update more ticket fields than customer', function () {
    $newStatus = TicketStatus::factory()->create();

    $this->agent->offices()->attach($this->office);

    Sanctum::actingAs($this->agent);

    $response = $this->putJson("/api/tickets/{$this->ticket->uuid}", [
        'ticket_status_id' => $newStatus->id,
        'assigned_to_id' => $this->agent->id,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'ticket_status_id' => $newStatus->id,
        'assigned_to_id' => $this->agent->id,
    ]);
});
