<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'is_internal',
        'user_id',
        'ticket_id',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Scope for public replies only (visible to customers)
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal notes only (visible to agents)
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Check if reply is an internal note
     */
    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    /**
     * Check if reply is public (visible to customers)
     */
    public function isPublic(): bool
    {
        return ! $this->is_internal;
    }
}
