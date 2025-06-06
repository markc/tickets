<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'subject',
        'content',
        'creator_id',
        'assigned_to_id',
        'office_id',
        'ticket_status_id',
        'ticket_priority_id',
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

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function timeline()
    {
        return $this->hasMany(TicketTimeline::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
