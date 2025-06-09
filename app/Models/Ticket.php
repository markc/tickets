<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Ticket extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'uuid',
        'subject',
        'content',
        'creator_id',
        'assigned_to_id',
        'office_id',
        'ticket_status_id',
        'ticket_priority_id',
        'sla_id',
        'sla_response_due_at',
        'sla_resolution_due_at',
        'first_response_at',
        'resolved_at',
        'sla_response_breached',
        'sla_resolution_breached',
        'merged_into_id',
        'merged_at',
        'merged_by_id',
        'merge_reason',
        'is_merged',
    ];

    protected $casts = [
        'sla_response_due_at' => 'datetime',
        'sla_resolution_due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'merged_at' => 'datetime',
        'sla_response_breached' => 'boolean',
        'sla_resolution_breached' => 'boolean',
        'is_merged' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $ticket->uuid = (string) Str::uuid();
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'ticket_priority_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    /**
     * Public replies visible to customers
     */
    public function publicReplies()
    {
        return $this->hasMany(TicketReply::class)->public();
    }

    /**
     * Internal notes visible only to agents
     */
    public function internalNotes()
    {
        return $this->hasMany(TicketReply::class)->internal();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function timeline()
    {
        return $this->hasMany(TicketTimeline::class);
    }

    public function sla()
    {
        return $this->belongsTo(SLA::class);
    }

    public function mergedInto()
    {
        return $this->belongsTo(Ticket::class, 'merged_into_id', 'uuid');
    }

    public function mergedTickets()
    {
        return $this->hasMany(Ticket::class, 'merged_into_id', 'uuid');
    }

    public function mergedBy()
    {
        return $this->belongsTo(User::class, 'merged_by_id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Check if response SLA is breached
     */
    public function isResponseSlaBreached(): bool
    {
        if (! $this->sla_response_due_at || $this->first_response_at) {
            return false;
        }

        return now() > $this->sla_response_due_at;
    }

    /**
     * Check if resolution SLA is breached
     */
    public function isResolutionSlaBreached(): bool
    {
        if (! $this->sla_resolution_due_at || $this->resolved_at) {
            return false;
        }

        return now() > $this->sla_resolution_due_at;
    }

    /**
     * Get time remaining for response SLA
     */
    public function getResponseTimeRemaining(): ?string
    {
        if (! $this->sla_response_due_at || $this->first_response_at) {
            return null;
        }

        $diff = now()->diffInMinutes($this->sla_response_due_at, false);

        if ($diff < 0) {
            return 'Overdue by '.abs($diff).' minutes';
        }

        return $diff.' minutes remaining';
    }

    /**
     * Get time remaining for resolution SLA
     */
    public function getResolutionTimeRemaining(): ?string
    {
        if (! $this->sla_resolution_due_at || $this->resolved_at) {
            return null;
        }

        $diff = now()->diffInMinutes($this->sla_resolution_due_at, false);

        if ($diff < 0) {
            return 'Overdue by '.abs($diff).' minutes';
        }

        return $diff.' minutes remaining';
    }

    /**
     * Scope to exclude merged tickets
     */
    public function scopeNotMerged($query)
    {
        return $query->where('is_merged', false);
    }

    /**
     * Scope to get only merged tickets
     */
    public function scopeMerged($query)
    {
        return $query->where('is_merged', true);
    }

    /**
     * Check if this ticket can be merged into another
     */
    public function canBeMergedInto(Ticket $targetTicket): bool
    {
        // Cannot merge into itself
        if ($this->uuid === $targetTicket->uuid) {
            return false;
        }

        // Cannot merge if already merged
        if ($this->is_merged || $targetTicket->is_merged) {
            return false;
        }

        // Cannot merge if tickets are in different offices (unless admin override)
        if ($this->office_id !== $targetTicket->office_id) {
            return false;
        }

        return true;
    }

    /**
     * Get all tickets that were merged into this one
     */
    public function getAllMergedTickets()
    {
        return $this->mergedTickets()->with(['creator', 'replies.user', 'timeline.user'])->get();
    }

    /**
     * Check if this ticket has any merged tickets
     */
    public function hasMergedTickets(): bool
    {
        return $this->mergedTickets()->exists();
    }

    /**
     * Get the count of merged tickets
     */
    public function getMergedTicketsCount(): int
    {
        return $this->mergedTickets()->count();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'subject' => $this->subject,
            'content' => strip_tags($this->content),
            'creator_name' => $this->creator?->name,
            'creator_email' => $this->creator?->email,
            'assigned_to_name' => $this->assignedTo?->name,
            'office_name' => $this->office?->name,
            'status_name' => $this->status?->name,
            'priority_name' => $this->priority?->name,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status?->name !== 'Deleted';
    }
}
