<?php

use App\Mail\TicketCreatedMail;
use App\Models\EmailTemplate;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->office = Office::factory()->create();
    $this->status = TicketStatus::factory()->create(['name' => 'Open']);
    $this->priority = TicketPriority::factory()->create(['name' => 'Medium']);
    $this->templateService = new EmailTemplateService;
});

test('can create email template', function () {
    $template = EmailTemplate::create([
        'name' => 'test_template',
        'subject' => 'Test Subject: {{ticket_id}}',
        'content' => 'Hello {{customer_name}}, your ticket {{ticket_id}} has been created.',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
        'is_default' => true,
        'language' => 'en',
        'description' => 'Test template',
    ]);

    expect($template)->toBeInstanceOf(EmailTemplate::class);
    expect($template->name)->toBe('test_template');
    expect($template->is_active)->toBeTrue();
    expect($template->is_default)->toBeTrue();
});

test('template variable replacement works', function () {
    $template = EmailTemplate::create([
        'name' => 'variable_test',
        'subject' => 'Ticket {{ticket_id}} for {{customer_name}}',
        'content' => 'Hello {{customer_name}}, your ticket {{ticket_id}} about "{{ticket_subject}}" is {{ticket_status}}.',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
    ]);

    $variables = [
        'ticket_id' => 'ABC123',
        'customer_name' => 'John Doe',
        'ticket_subject' => 'Login Problem',
        'ticket_status' => 'Open',
    ];

    $processedSubject = $template->processSubject($variables);
    $processedContent = $template->processContent($variables);

    expect($processedSubject)->toBe('Ticket ABC123 for John Doe');
    expect($processedContent)->toContain('Hello John Doe');
    expect($processedContent)->toContain('ticket ABC123');
    expect($processedContent)->toContain('"Login Problem"');
    expect($processedContent)->toContain('is Open');
});

test('can find default template', function () {
    // Create multiple templates with same name but different languages
    EmailTemplate::create([
        'name' => 'ticket_created_test',
        'subject' => 'Regular Template',
        'content' => 'Regular content',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
        'is_default' => false,
        'language' => 'en',
    ]);

    $defaultTemplate = EmailTemplate::create([
        'name' => 'ticket_created_test',
        'subject' => 'Default Template',
        'content' => 'Default content',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
        'is_default' => true,
        'language' => 'es', // Different language to avoid unique constraint
    ]);

    $found = EmailTemplate::findDefaultTemplate('ticket_created_test', 'es');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($defaultTemplate->id);
    expect($found->subject)->toBe('Default Template');
});

test('template validation methods work', function () {
    $template = EmailTemplate::create([
        'name' => 'validation_test',
        'subject' => 'Hello {{customer_name}}',
        'content' => 'Your ticket {{ticket_id}} has status {{ticket_status}} and {{unknown_variable}}.',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
    ]);

    $usedVariables = $template->getUsedVariables();
    $invalidVariables = $template->validateVariables();

    expect($usedVariables)->toContain('customer_name');
    expect($usedVariables)->toContain('ticket_id');
    expect($usedVariables)->toContain('ticket_status');
    expect($usedVariables)->toContain('unknown_variable');

    // Should return the invalid variable
    expect($invalidVariables)->toContain('unknown_variable');
    expect($invalidVariables)->not->toContain('customer_name');
});

test('template preview generation works', function () {
    $template = EmailTemplate::create([
        'name' => 'preview_test',
        'subject' => 'Ticket {{ticket_id}} from {{customer_name}}',
        'content' => 'Hello {{customer_name}}, your ticket about {{ticket_subject}} is now {{ticket_status}}.',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
    ]);

    $preview = $template->getPreview();

    expect($preview)->toHaveKey('subject');
    expect($preview)->toHaveKey('content');
    expect($preview['subject'])->toContain('ABCD1234');
    expect($preview['subject'])->toContain('John Doe');
    expect($preview['content'])->toContain('Hello John Doe');
    expect($preview['content'])->toContain('Login Issue');
});

test('email template service processes ticket created', function () {
    // Create default template
    EmailTemplate::create([
        'name' => 'ticket_created',
        'subject' => '[Ticket #{{ticket_id}}] {{ticket_subject}}',
        'content' => 'Hello {{customer_name}}, your ticket {{ticket_id}} has been created.',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
        'is_default' => true,
    ]);

    $ticket = Ticket::factory()->create([
        'subject' => 'Test Issue',
        'content' => 'Test description',
        'creator_id' => $this->customer->id,
        'office_id' => $this->office->id,
        'ticket_status_id' => $this->status->id,
        'ticket_priority_id' => $this->priority->id,
    ]);

    $result = $this->templateService->processTicketCreatedEmail($ticket, true);

    expect($result)->not->toBeNull();
    expect($result)->toHaveKey('subject');
    expect($result)->toHaveKey('content');
    expect($result['subject'])->toContain('[Ticket #'.substr($ticket->uuid, 0, 8).']');
    expect($result['subject'])->toContain('Test Issue');
    expect($result['content'])->toContain($this->customer->name);
    expect($result['content'])->toContain(substr($ticket->uuid, 0, 8));
});

test('mail classes use templates', function () {
    Mail::fake();

    // Create default template
    EmailTemplate::create([
        'name' => 'ticket_created',
        'subject' => 'Custom Subject: {{ticket_id}}',
        'content' => 'Custom content for {{customer_name}}',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
        'is_default' => true,
    ]);

    $ticket = Ticket::factory()->create([
        'creator_id' => $this->customer->id,
        'office_id' => $this->office->id,
        'ticket_status_id' => $this->status->id,
        'ticket_priority_id' => $this->priority->id,
    ]);

    $mail = new TicketCreatedMail($ticket, true);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Custom Subject:');
    expect($envelope->subject)->toContain(substr($ticket->uuid, 0, 8));
});

test('template service creates default templates', function () {
    $this->templateService->createDefaultTemplates();

    $this->assertDatabaseHas('email_templates', ['name' => 'ticket_created']);
    $this->assertDatabaseHas('email_templates', ['name' => 'ticket_reply']);
    $this->assertDatabaseHas('email_templates', ['name' => 'ticket_status_changed']);
    $this->assertDatabaseHas('email_templates', ['name' => 'ticket_assigned']);

    // Check that all default templates are marked as default and active
    $defaultTemplates = EmailTemplate::where('is_default', true)->get();
    expect($defaultTemplates->count())->toBeGreaterThanOrEqual(4);

    foreach ($defaultTemplates as $template) {
        expect($template->is_active)->toBeTrue();
        expect($template->category)->toBe('ticket');
    }
});

test('scopes work correctly', function () {
    EmailTemplate::create([
        'name' => 'active_template',
        'subject' => 'Active',
        'content' => 'Active content',
        'type' => 'markdown',
        'category' => 'ticket',
        'is_active' => true,
    ]);

    EmailTemplate::create([
        'name' => 'inactive_template',
        'subject' => 'Inactive',
        'content' => 'Inactive content',
        'type' => 'markdown',
        'category' => 'general',
        'is_active' => false,
    ]);

    $activeTemplates = EmailTemplate::active()->get();
    $ticketTemplates = EmailTemplate::category('ticket')->get();
    $englishTemplates = EmailTemplate::language('en')->get();

    expect($activeTemplates->count())->toBe(1);
    expect($activeTemplates->first()->name)->toBe('active_template');

    expect($ticketTemplates->count())->toBe(1);
    expect($ticketTemplates->first()->name)->toBe('active_template');

    expect($englishTemplates->count())->toBe(2);
});

test('fallback when no template found', function () {
    $ticket = Ticket::factory()->create([
        'creator_id' => $this->customer->id,
        'office_id' => $this->office->id,
        'ticket_status_id' => $this->status->id,
        'ticket_priority_id' => $this->priority->id,
    ]);

    // No templates exist, should return null
    $result = $this->templateService->processTicketCreatedEmail($ticket, true);
    expect($result)->toBeNull();

    // Mail class should still work with fallback
    $mail = new TicketCreatedMail($ticket, true);
    $envelope = $mail->envelope();
    $content = $mail->content();

    expect($envelope->subject)->toContain('[Ticket #');
    expect($content->markdown)->toBe('emails.ticket-created');
});

test('template categories and types are available', function () {
    $categories = EmailTemplate::getCategories();
    $types = EmailTemplate::getTypes();
    $variables = EmailTemplate::getAvailableVariables();

    expect($categories)->toHaveKey('ticket');
    expect($categories)->toHaveKey('general');

    expect($types)->toHaveKey('markdown');
    expect($types)->toHaveKey('html');
    expect($types)->toHaveKey('plain');

    expect($variables)->toHaveKey('customer_name');
    expect($variables)->toHaveKey('ticket_id');
    expect($variables)->toHaveKey('agent_name');
});
