<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'search_params',
        'user_id',
        'is_public',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'search_params' => 'array',
        'is_public' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAccessibleByUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
                ->orWhere('user_id', $user->id);
        });
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function getSearchUrl(): string
    {
        $params = $this->search_params;
        $params['saved_search'] = $this->id;

        return route('search').'?'.http_build_query($params);
    }

    public function getFormattedFilters(): array
    {
        $params = $this->search_params;
        $formatted = [];

        if (! empty($params['q'])) {
            $formatted['Search'] = $params['q'];
        }

        if (! empty($params['type']) && $params['type'] !== 'all') {
            $formatted['Type'] = ucfirst($params['type']);
        }

        if (! empty($params['status'])) {
            $formatted['Status'] = is_array($params['status'])
                ? implode(', ', $params['status'])
                : $params['status'];
        }

        if (! empty($params['priority'])) {
            $formatted['Priority'] = is_array($params['priority'])
                ? implode(', ', $params['priority'])
                : $params['priority'];
        }

        if (! empty($params['date_from']) || ! empty($params['date_to'])) {
            $dateRange = '';
            if (! empty($params['date_from'])) {
                $dateRange = 'From '.$params['date_from'];
            }
            if (! empty($params['date_to'])) {
                $dateRange .= ($dateRange ? ' to ' : 'Until ').$params['date_to'];
            }
            $formatted['Date Range'] = $dateRange;
        }

        return $formatted;
    }
}
