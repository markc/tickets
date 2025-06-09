<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLA extends Model
{
    use HasFactory;

    protected $table = 's_l_a_s';

    protected $fillable = [
        'name',
        'description',
        'office_id',
        'ticket_priority_id',
        'response_time_minutes',
        'resolution_time_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function ticketPriority()
    {
        return $this->belongsTo(TicketPriority::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Calculate response due time from creation
     */
    public function calculateResponseDueAt(Carbon $createdAt): Carbon
    {
        return $this->calculateDueTime($createdAt, $this->response_time_minutes);
    }

    /**
     * Calculate resolution due time from creation
     */
    public function calculateResolutionDueAt(Carbon $createdAt): Carbon
    {
        return $this->calculateDueTime($createdAt, $this->resolution_time_minutes);
    }

    /**
     * Calculate due time considering business hours
     */
    private function calculateDueTime(Carbon $startTime, int $minutes): Carbon
    {
        $dueTime = $startTime->copy();
        $remainingMinutes = $minutes;

        while ($remainingMinutes > 0) {
            // If it's a weekend, move to next Monday
            if ($dueTime->isWeekend()) {
                $dueTime->next(Carbon::MONDAY)->setTime(9, 0, 0);
            }

            // If it's outside business hours (9 AM - 6 PM), move to next business hour
            if ($dueTime->hour < 9) {
                $dueTime->setTime(9, 0, 0);
            } elseif ($dueTime->hour >= 18) {
                $dueTime->addDay()->setTime(9, 0, 0);
                if ($dueTime->isWeekend()) {
                    $dueTime->next(Carbon::MONDAY);
                }
            }

            // Calculate minutes remaining in current business day
            $endOfBusinessDay = $dueTime->copy()->setTime(18, 0, 0);
            $minutesInDay = $dueTime->diffInMinutes($endOfBusinessDay);

            if ($remainingMinutes <= $minutesInDay) {
                // Fits within current business day
                $dueTime->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                // Move to next business day
                $remainingMinutes -= $minutesInDay;
                $dueTime->addDay()->setTime(9, 0, 0);
                if ($dueTime->isWeekend()) {
                    $dueTime->next(Carbon::MONDAY);
                }
            }
        }

        return $dueTime;
    }

    /**
     * Get formatted response time
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        return $this->formatMinutes($this->response_time_minutes);
    }

    /**
     * Get formatted resolution time
     */
    public function getFormattedResolutionTimeAttribute(): string
    {
        return $this->formatMinutes($this->resolution_time_minutes);
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h ".($mins > 0 ? "{$mins}m" : '');
        }

        return "{$mins}m";
    }
}
