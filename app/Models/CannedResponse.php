<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'variables',
        'user_id',
        'is_public',
        'is_active',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeAccessibleByUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
                ->orWhere('user_id', $user->id);
        });
    }

    public function scopeByCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }

        return $query;
    }

    public function replaceVariables(array $data): string
    {
        $content = $this->content;

        // Replace common variables
        $variables = [
            '{{customer_name}}' => $data['customer_name'] ?? '',
            '{{customer_email}}' => $data['customer_email'] ?? '',
            '{{ticket_id}}' => $data['ticket_id'] ?? '',
            '{{ticket_subject}}' => $data['ticket_subject'] ?? '',
            '{{agent_name}}' => $data['agent_name'] ?? '',
            '{{company_name}}' => config('app.name', 'TIKM Support'),
            '{{current_date}}' => now()->format('F j, Y'),
            '{{current_time}}' => now()->format('g:i A'),
        ];

        foreach ($variables as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public static function getAvailableVariables(): array
    {
        return [
            '{{customer_name}}' => 'Customer\'s full name',
            '{{customer_email}}' => 'Customer\'s email address',
            '{{ticket_id}}' => 'Ticket ID/number',
            '{{ticket_subject}}' => 'Ticket subject line',
            '{{agent_name}}' => 'Agent\'s full name',
            '{{company_name}}' => 'Company name',
            '{{current_date}}' => 'Current date',
            '{{current_time}}' => 'Current time',
        ];
    }

    public static function getCommonCategories(): array
    {
        return [
            'General' => 'General responses',
            'Technical' => 'Technical support',
            'Billing' => 'Billing and payment issues',
            'Account' => 'Account management',
            'Shipping' => 'Shipping and delivery',
            'Returns' => 'Returns and refunds',
            'Escalation' => 'Escalation responses',
            'Closing' => 'Ticket closing responses',
        ];
    }
}
