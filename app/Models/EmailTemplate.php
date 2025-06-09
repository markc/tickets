<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'type',
        'category',
        'is_active',
        'is_default',
        'variables',
        'language',
        'description',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Available template categories
     */
    public static function getCategories(): array
    {
        return [
            'ticket' => 'Ticket Notifications',
            'general' => 'General Communications',
            'account' => 'Account Related',
            'system' => 'System Notifications',
            'marketing' => 'Marketing Communications',
        ];
    }

    /**
     * Available template types
     */
    public static function getTypes(): array
    {
        return [
            'markdown' => 'Markdown',
            'html' => 'HTML',
            'plain' => 'Plain Text',
        ];
    }

    /**
     * Available variable placeholders
     */
    public static function getAvailableVariables(): array
    {
        return [
            'customer_name' => 'Customer full name',
            'customer_email' => 'Customer email address',
            'ticket_id' => 'Ticket UUID/reference number',
            'ticket_subject' => 'Ticket subject line',
            'ticket_content' => 'Ticket description/content',
            'ticket_status' => 'Current ticket status',
            'ticket_priority' => 'Ticket priority level',
            'ticket_url' => 'Direct link to view ticket',
            'agent_name' => 'Assigned agent name',
            'agent_email' => 'Assigned agent email',
            'office_name' => 'Department/office name',
            'company_name' => 'Company name from configuration',
            'current_date' => 'Current date (formatted)',
            'current_time' => 'Current time (formatted)',
            'reply_content' => 'Reply message content (for reply templates)',
            'reply_author' => 'Name of person who replied',
        ];
    }

    /**
     * Get the user who created this template
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the user who last updated this template
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Scope to get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get templates by language
     */
    public function scopeLanguage($query, string $language = 'en')
    {
        return $query->where('language', $language);
    }

    /**
     * Find the default template for a specific name and language
     */
    public static function findDefaultTemplate(string $name, string $language = 'en'): ?self
    {
        return static::active()
            ->where('name', $name)
            ->language($language)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Process template content with variable replacement
     */
    public function processContent(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $content = str_replace($placeholder, (string) $value, $content);
        }

        return $content;
    }

    /**
     * Process template subject with variable replacement
     */
    public function processSubject(array $variables = []): string
    {
        $subject = $this->subject;

        foreach ($variables as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $subject = str_replace($placeholder, (string) $value, $subject);
        }

        return $subject;
    }

    /**
     * Get all variable placeholders used in this template
     */
    public function getUsedVariables(): array
    {
        $content = $this->subject.' '.$this->content;
        preg_match_all('/\{\{(\w+)\}\}/', $content, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Validate that all used variables are available
     */
    public function validateVariables(): array
    {
        $usedVariables = $this->getUsedVariables();
        $availableVariables = array_keys(static::getAvailableVariables());

        return array_diff($usedVariables, $availableVariables);
    }

    /**
     * Get a preview of the template with sample data
     */
    public function getPreview(): array
    {
        $sampleData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john.doe@example.com',
            'ticket_id' => 'ABCD1234',
            'ticket_subject' => 'Login Issue',
            'ticket_content' => 'I cannot log into my account. Please help.',
            'ticket_status' => 'Open',
            'ticket_priority' => 'Medium',
            'ticket_url' => url('/tickets/sample'),
            'agent_name' => 'Support Agent',
            'agent_email' => 'support@company.com',
            'office_name' => 'Technical Support',
            'company_name' => config('app.name', 'Company'),
            'current_date' => now()->format('F j, Y'),
            'current_time' => now()->format('g:i A'),
            'reply_content' => 'Thank you for contacting us. We are looking into your issue.',
            'reply_author' => 'Support Agent',
        ];

        return [
            'subject' => $this->processSubject($sampleData),
            'content' => $this->processContent($sampleData),
        ];
    }
}
