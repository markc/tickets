<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQUsageTracking extends Model
{
    protected $table = 'faq_usage_tracking';

    protected $fillable = [
        'faq_id',
        'ticket_id',
        'user_id',
        'context',
    ];

    public function faq()
    {
        return $this->belongsTo(FAQ::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
