<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class FAQ extends Model
{
    use HasFactory, Searchable;

    protected $table = 'f_a_q_s';

    protected $fillable = [
        'question',
        'answer',
        'office_id',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function usageTracking()
    {
        return $this->hasMany(FAQUsageTracking::class, 'faq_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('question');
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
            'question' => $this->question,
            'answer' => strip_tags($this->answer),
            'is_published' => $this->is_published,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }
}
