<?php

use App\Models\FAQ;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use App\Services\KnowledgeBaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->agent = User::factory()->create(['role' => 'agent']);
    $this->customer = User::factory()->create(['role' => 'customer']);

    $this->office = Office::factory()->create(['name' => 'Technical Support']);
    
    // Associate agent with office so they can view tickets
    $this->agent->offices()->attach($this->office);

    $this->ticket = Ticket::factory()->create([
        'creator_id' => $this->customer->id,
        'office_id' => $this->office->id,
        'subject' => 'Password reset issue',
        'content' => 'I cannot reset my password using the forgot password link',
    ]);

    $this->faq1 = FAQ::factory()->create([
        'question' => 'How to reset your password?',
        'answer' => 'To reset your password, click on the "Forgot Password" link on the login page and follow the instructions.',
        'office_id' => $this->office->id,
        'is_published' => true,
    ]);

    $this->faq2 = FAQ::factory()->create([
        'question' => 'How to change your password?',
        'answer' => 'To change your password, go to your profile settings and update your credentials.',
        'office_id' => null, // Global FAQ
        'is_published' => true,
    ]);

    $this->unpublishedFaq = FAQ::factory()->create([
        'question' => 'Internal FAQ',
        'answer' => 'This is an internal FAQ that should not be visible to customers.',
        'office_id' => $this->office->id,
        'is_published' => false,
    ]);

    // Make FAQs searchable for tests
    $this->faq1->searchable();
    $this->faq2->searchable();
    $this->unpublishedFaq->searchable();
});

test('knowledge base service extracts keywords correctly', function () {
    $service = new KnowledgeBaseService;

    $method = new ReflectionMethod($service, 'extractKeywords');
    $method->setAccessible(true);

    $keywords = $method->invoke($service, 'I cannot reset my password using the forgot password link');

    expect($keywords)->toContain('reset', 'password', 'forgot', 'link');
    expect($keywords)->not->toContain('the', 'and', 'cannot'); // Stop words should be filtered
});

test('admin can get faq suggestions for ticket', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(route('api.knowledge-base.suggestions', $this->ticket));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'question',
                    'answer',
                    'office',
                    'excerpt',
                ],
            ],
            'ticket_id',
            'total',
        ]);

    // Should suggest password reset FAQ
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect(collect($data)->pluck('question'))->toContain('How to reset your password?');
});

test('agent can get faq suggestions for ticket', function () {
    $this->actingAs($this->agent);

    $response = $this->getJson(route('api.knowledge-base.suggestions', $this->ticket));

    $response->assertOk();
});

test('customer cannot get faq suggestions for other tickets', function () {
    $otherCustomer = User::factory()->create(['role' => 'customer']);
    $otherTicket = Ticket::factory()->create([
        'creator_id' => $otherCustomer->id,
        'office_id' => $this->office->id,
    ]);

    $this->actingAs($this->customer);

    $response = $this->getJson(route('api.knowledge-base.suggestions', $otherTicket));

    $response->assertForbidden();
});

test('users can search faqs', function () {
    $this->actingAs($this->agent);

    $response = $this->getJson(route('api.knowledge-base.search', ['q' => 'password']));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'question',
                    'answer',
                    'office',
                    'excerpt',
                ],
            ],
            'query',
            'total',
        ]);

    $data = $response->json('data');
    expect(collect($data)->pluck('question'))->toContain('How to reset your password?');
});

test('customers only see published faqs in search', function () {
    $this->actingAs($this->customer);

    $response = $this->getJson(route('api.knowledge-base.search', ['q' => 'FAQ']));

    $response->assertOk();

    $data = $response->json('data');
    expect(collect($data)->pluck('question'))->not->toContain('Internal FAQ');
});

test('admin can get trending faqs', function () {
    // Create some usage tracking
    DB::table('faq_usage_tracking')->insert([
        [
            'faq_id' => $this->faq1->id,
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->agent->id,
            'context' => 'reply_insertion',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'faq_id' => $this->faq1->id,
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->agent->id,
            'context' => 'reply_insertion',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($this->admin);

    $response = $this->getJson(route('api.knowledge-base.trending'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'question',
                    'answer',
                    'office',
                    'usage_count',
                    'excerpt',
                ],
            ],
            'total',
        ]);
});

test('agent can format faq for insertion', function () {
    $this->actingAs($this->agent);

    $response = $this->postJson(route('api.knowledge-base.faq.format', $this->faq1), [
        'format' => 'markdown',
        'ticket_id' => $this->ticket->uuid,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'question',
                'formatted_content',
                'format',
            ],
        ]);

    $formattedContent = $response->json('data.formatted_content');
    expect($formattedContent)->toContain('## How to reset your password?');
    expect($formattedContent)->toContain('*Reference: FAQ #'.$this->faq1->id.'*');
});

test('agent can format faq as plain text', function () {
    $this->actingAs($this->agent);

    $response = $this->postJson(route('api.knowledge-base.faq.format', $this->faq1), [
        'format' => 'plain',
        'ticket_id' => $this->ticket->uuid,
    ]);

    $response->assertOk();

    $formattedContent = $response->json('data.formatted_content');
    expect($formattedContent)->toContain('--- FAQ Reference ---');
    expect($formattedContent)->toContain('Q: How to reset your password?');
    expect($formattedContent)->toContain('Reference: FAQ #'.$this->faq1->id);
});

test('agent can track faq usage', function () {
    $this->actingAs($this->agent);

    $response = $this->postJson(route('api.knowledge-base.faq.track-usage', $this->faq1), [
        'ticket_id' => $this->ticket->uuid,
        'context' => 'reply_insertion',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'FAQ usage tracked successfully',
        ]);

    // Verify usage was tracked in database
    $this->assertDatabaseHas('faq_usage_tracking', [
        'faq_id' => $this->faq1->id,
        'ticket_id' => $this->ticket->id,
        'user_id' => $this->agent->id,
        'context' => 'reply_insertion',
    ]);
});

test('admin can get faq analytics', function () {
    // Create some usage data
    DB::table('faq_usage_tracking')->insert([
        'faq_id' => $this->faq1->id,
        'ticket_id' => $this->ticket->id,
        'user_id' => $this->agent->id,
        'context' => 'reply_insertion',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($this->admin);

    $response = $this->getJson(route('api.knowledge-base.analytics'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_faqs',
                'total_usage',
                'top_faqs',
                'usage_by_office',
                'daily_usage',
                'effectiveness_rate',
            ],
            'period_days',
        ]);

    $data = $response->json('data');
    expect($data['total_faqs'])->toBeGreaterThan(0);
    expect($data['total_usage'])->toBeGreaterThan(0);
});

test('customer cannot access faq analytics', function () {
    $this->actingAs($this->customer);

    $response = $this->getJson(route('api.knowledge-base.analytics'));

    $response->assertForbidden();
});

test('agent cannot access faq analytics', function () {
    $this->actingAs($this->agent);

    $response = $this->getJson(route('api.knowledge-base.analytics'));

    $response->assertForbidden();
});

test('users can view published faq details', function () {
    $this->actingAs($this->agent);

    $response = $this->getJson(route('api.knowledge-base.faq.show', $this->faq1));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'question',
                'answer',
                'office',
                'is_published',
                'usage_count_30_days',
                'created_at',
                'updated_at',
            ],
        ]);
});

test('customers cannot view unpublished faqs', function () {
    $this->actingAs($this->customer);

    $response = $this->getJson(route('api.knowledge-base.faq.show', $this->unpublishedFaq));

    $response->assertNotFound();
});

test('knowledge base service calculates relevance scores correctly', function () {
    $service = new KnowledgeBaseService;

    $suggestions = $service->getSuggestedFAQs($this->ticket, 10);

    expect($suggestions)->not->toBeEmpty();

    // Password reset FAQ should be suggested for password-related ticket
    $passwordFaq = $suggestions->firstWhere('id', $this->faq1->id);
    expect($passwordFaq)->not->toBeNull();
});

test('knowledge base service tracks usage correctly', function () {
    $service = new KnowledgeBaseService;

    $service->trackFAQUsage($this->faq1, $this->ticket, $this->agent, 'reply_insertion');

    $this->assertDatabaseHas('faq_usage_tracking', [
        'faq_id' => $this->faq1->id,
        'ticket_id' => $this->ticket->id,
        'user_id' => $this->agent->id,
        'context' => 'reply_insertion',
    ]);
});

test('knowledge base service filters faqs by office correctly', function () {
    $service = new KnowledgeBaseService;

    $results = $service->searchFAQs('password', $this->office->id, 10);

    // Should include office-specific FAQ and global FAQs
    expect($results->pluck('office_id'))->toContain($this->office->id, null);
});

test('knowledge base service gets trending faqs correctly', function () {
    // Create usage data
    DB::table('faq_usage_tracking')->insert([
        [
            'faq_id' => $this->faq1->id,
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->agent->id,
            'context' => 'reply_insertion',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'faq_id' => $this->faq1->id,
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->agent->id,
            'context' => 'reply_insertion',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $service = new KnowledgeBaseService;

    $trending = $service->getTrendingFAQs($this->office->id, 5);

    expect($trending)->not->toBeEmpty();
    expect($trending->first()->usage_count)->toBeGreaterThan(0);
});
