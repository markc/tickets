<?php

namespace App\Services;

use App\Models\SLA;
use App\Models\Ticket;
use App\Models\TicketTimeline;
use Illuminate\Support\Facades\Log;

class SLAService
{
    /**
     * Apply SLA to a ticket when created
     */
    public function applyToTicket(Ticket $ticket): void
    {
        $sla = $this->findApplicableSLA($ticket);

        if (! $sla) {
            Log::info('No SLA found for ticket', [
                'ticket_id' => $ticket->id,
                'office_id' => $ticket->office_id,
                'priority_id' => $ticket->ticket_priority_id,
            ]);

            return;
        }

        $createdAt = $ticket->created_at;

        $ticket->update([
            'sla_id' => $sla->id,
            'sla_response_due_at' => $sla->calculateResponseDueAt($createdAt),
            'sla_resolution_due_at' => $sla->calculateResolutionDueAt($createdAt),
        ]);

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'action' => 'sla_applied',
            'description' => "SLA '{$sla->name}' applied to ticket",
        ]);

        Log::info('SLA applied to ticket', [
            'ticket_id' => $ticket->id,
            'sla_id' => $sla->id,
            'response_due' => $ticket->sla_response_due_at,
            'resolution_due' => $ticket->sla_resolution_due_at,
        ]);
    }

    /**
     * Mark first response timestamp
     */
    public function markFirstResponse(Ticket $ticket, int $userId): void
    {
        if ($ticket->first_response_at) {
            return; // Already has first response
        }

        $firstResponseAt = now();
        $responseBreached = $ticket->sla_response_due_at &&
                           $firstResponseAt > $ticket->sla_response_due_at;

        $ticket->update([
            'first_response_at' => $firstResponseAt,
            'sla_response_breached' => $responseBreached,
        ]);

        $status = $responseBreached ? 'breached' : 'met';

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'action' => 'first_response',
            'description' => "First response provided - SLA response {$status}",
        ]);

        Log::info('First response marked', [
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'response_time' => $firstResponseAt,
            'sla_breached' => $responseBreached,
        ]);
    }

    /**
     * Mark ticket as resolved
     */
    public function markResolved(Ticket $ticket, int $userId): void
    {
        if ($ticket->resolved_at) {
            return; // Already resolved
        }

        $resolvedAt = now();
        $resolutionBreached = $ticket->sla_resolution_due_at &&
                             $resolvedAt > $ticket->sla_resolution_due_at;

        $ticket->update([
            'resolved_at' => $resolvedAt,
            'sla_resolution_breached' => $resolutionBreached,
        ]);

        $status = $resolutionBreached ? 'breached' : 'met';

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'action' => 'resolved',
            'description' => "Ticket resolved - SLA resolution {$status}",
        ]);

        Log::info('Ticket resolved', [
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'resolved_time' => $resolvedAt,
            'sla_breached' => $resolutionBreached,
        ]);
    }

    /**
     * Check for SLA breaches and update flags
     */
    public function checkBreaches(): array
    {
        $breaches = [
            'response' => [],
            'resolution' => [],
        ];

        // Check response SLA breaches
        $responseBreaches = Ticket::whereNotNull('sla_response_due_at')
            ->whereNull('first_response_at')
            ->where('sla_response_due_at', '<', now())
            ->where('sla_response_breached', false)
            ->get();

        foreach ($responseBreaches as $ticket) {
            $ticket->update(['sla_response_breached' => true]);

            TicketTimeline::create([
                'ticket_id' => $ticket->id,
                'user_id' => null,
                'action' => 'sla_breach',
                'description' => 'Response SLA breached',
            ]);

            $breaches['response'][] = $ticket;
        }

        // Check resolution SLA breaches
        $resolutionBreaches = Ticket::whereNotNull('sla_resolution_due_at')
            ->whereNull('resolved_at')
            ->where('sla_resolution_due_at', '<', now())
            ->where('sla_resolution_breached', false)
            ->get();

        foreach ($resolutionBreaches as $ticket) {
            $ticket->update(['sla_resolution_breached' => true]);

            TicketTimeline::create([
                'ticket_id' => $ticket->id,
                'user_id' => null,
                'action' => 'sla_breach',
                'description' => 'Resolution SLA breached',
            ]);

            $breaches['resolution'][] = $ticket;
        }

        return $breaches;
    }

    /**
     * Get SLA performance metrics
     */
    public function getPerformanceMetrics(int $days = 30): array
    {
        $since = now()->subDays($days);

        $totalTickets = Ticket::whereNotNull('sla_id')
            ->where('created_at', '>=', $since)
            ->count();

        $responseMetrics = Ticket::whereNotNull('sla_id')
            ->whereNotNull('first_response_at')
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_responded,
                SUM(CASE WHEN sla_response_breached = 0 THEN 1 ELSE 0 END) as response_met,
                SUM(CASE WHEN sla_response_breached = 1 THEN 1 ELSE 0 END) as response_breached
            ')
            ->first();

        $resolutionMetrics = Ticket::whereNotNull('sla_id')
            ->whereNotNull('resolved_at')
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_resolved,
                SUM(CASE WHEN sla_resolution_breached = 0 THEN 1 ELSE 0 END) as resolution_met,
                SUM(CASE WHEN sla_resolution_breached = 1 THEN 1 ELSE 0 END) as resolution_breached
            ')
            ->first();

        return [
            'total_tickets' => $totalTickets,
            'response_rate' => $responseMetrics->total_responded > 0
                ? round(($responseMetrics->response_met / $responseMetrics->total_responded) * 100, 2)
                : 0,
            'resolution_rate' => $resolutionMetrics->total_resolved > 0
                ? round(($resolutionMetrics->resolution_met / $resolutionMetrics->total_resolved) * 100, 2)
                : 0,
            'response_metrics' => $responseMetrics,
            'resolution_metrics' => $resolutionMetrics,
        ];
    }

    /**
     * Find applicable SLA for a ticket
     */
    private function findApplicableSLA(Ticket $ticket): ?SLA
    {
        return SLA::where('office_id', $ticket->office_id)
            ->where('ticket_priority_id', $ticket->ticket_priority_id)
            ->where('is_active', true)
            ->first();
    }
}
