<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Ensure only admins and agents can access analytics
        if (auth()->user()->isCustomer()) {
            abort(403, 'Access denied');
        }

        $dateRange = $request->get('date_range', '30'); // Default to 30 days
        $startDate = Carbon::now()->subDays($dateRange);
        $endDate = Carbon::now();

        $analytics = [
            'overview' => $this->getOverviewMetrics($startDate, $endDate),
            'tickets' => $this->getTicketMetrics($startDate, $endDate),
            'agents' => $this->getAgentMetrics($startDate, $endDate),
            'offices' => $this->getOfficeMetrics($startDate, $endDate),
            'sla' => $this->getSlaMetrics($startDate, $endDate),
            'trends' => $this->getTrendData($startDate, $endDate),
        ];

        return view('analytics.dashboard', compact('analytics', 'dateRange'));
    }

    private function getOverviewMetrics($startDate, $endDate)
    {
        $totalTickets = Ticket::count();
        $newTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();
        $resolvedTickets = Ticket::whereBetween('updated_at', [$startDate, $endDate])
            ->whereHas('status', function ($query) {
                $query->where('name', 'Closed');
            })->count();

        $avgResponseTime = $this->getAverageResponseTime($startDate, $endDate);
        $avgResolutionTime = $this->getAverageResolutionTime($startDate, $endDate);

        return [
            'total_tickets' => $totalTickets,
            'new_tickets' => $newTickets,
            'resolved_tickets' => $resolvedTickets,
            'open_tickets' => Ticket::whereHas('status', function ($query) {
                $query->whereIn('name', ['Open', 'In Progress', 'On Hold']);
            })->count(),
            'avg_response_time' => $avgResponseTime,
            'avg_resolution_time' => $avgResolutionTime,
            'resolution_rate' => $newTickets > 0 ? round(($resolvedTickets / $newTickets) * 100, 1) : 0,
        ];
    }

    private function getTicketMetrics($startDate, $endDate)
    {
        // Tickets by status
        $ticketsByStatus = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->select('ticket_status_id', DB::raw('count(*) as count'))
            ->with('status')
            ->groupBy('ticket_status_id')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status->name,
                    'count' => $item->count,
                    'color' => $item->status->color,
                ];
            });

        // Tickets by priority
        $ticketsByPriority = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->select('ticket_priority_id', DB::raw('count(*) as count'))
            ->with('priority')
            ->groupBy('ticket_priority_id')
            ->get()
            ->map(function ($item) {
                return [
                    'priority' => $item->priority->name,
                    'count' => $item->count,
                    'color' => $item->priority->color,
                ];
            });

        // Top customers by ticket count
        $topCustomers = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->select('creator_id', DB::raw('count(*) as ticket_count'))
            ->with('creator')
            ->groupBy('creator_id')
            ->orderByDesc('ticket_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'customer' => $item->creator->name,
                    'email' => $item->creator->email,
                    'ticket_count' => $item->ticket_count,
                ];
            });

        return [
            'by_status' => $ticketsByStatus,
            'by_priority' => $ticketsByPriority,
            'top_customers' => $topCustomers,
        ];
    }

    private function getAgentMetrics($startDate, $endDate)
    {
        // Agent performance metrics
        $agents = User::where('role', 'agent')
            ->withCount(['assignedTickets as total_assigned'])
            ->get()
            ->map(function ($agent) use ($startDate, $endDate) {
                $resolvedCount = $agent->assignedTickets()
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->whereHas('status', function ($query) {
                        $query->where('name', 'Closed');
                    })->count();

                $repliesCount = TicketReply::where('user_id', $agent->id)
                    ->where('is_internal', false)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $avgResponseTime = $this->getAgentAverageResponseTime($agent->id, $startDate, $endDate);

                return [
                    'agent' => $agent->name,
                    'email' => $agent->email,
                    'total_assigned' => $agent->total_assigned,
                    'resolved_tickets' => $resolvedCount,
                    'replies_sent' => $repliesCount,
                    'avg_response_time' => $avgResponseTime,
                    'resolution_rate' => $agent->total_assigned > 0 ?
                        round(($resolvedCount / $agent->total_assigned) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('resolved_tickets');

        return $agents->values();
    }

    private function getOfficeMetrics($startDate, $endDate)
    {
        return Office::where('is_internal', false)
            ->withCount(['tickets as total_tickets'])
            ->get()
            ->map(function ($office) use ($startDate, $endDate) {
                $newTickets = $office->tickets()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $resolvedTickets = $office->tickets()
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->whereHas('status', function ($query) {
                        $query->where('name', 'Closed');
                    })->count();

                $avgResolutionTime = $this->getOfficeAverageResolutionTime($office->id, $startDate, $endDate);

                return [
                    'office' => $office->name,
                    'total_tickets' => $office->total_tickets,
                    'new_tickets' => $newTickets,
                    'resolved_tickets' => $resolvedTickets,
                    'avg_resolution_time' => $avgResolutionTime,
                    'resolution_rate' => $newTickets > 0 ?
                        round(($resolvedTickets / $newTickets) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('new_tickets')
            ->values();
    }

    private function getSlaMetrics($startDate, $endDate)
    {
        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->get();

        $totalTickets = $tickets->count();
        $responseBreaches = $tickets->filter(function ($ticket) {
            return $ticket->isResponseSlaBreached();
        })->count();

        $resolutionBreaches = $tickets->filter(function ($ticket) {
            return $ticket->isResolutionSlaBreached();
        })->count();

        return [
            'total_tickets' => $totalTickets,
            'response_breaches' => $responseBreaches,
            'resolution_breaches' => $resolutionBreaches,
            'response_compliance' => $totalTickets > 0 ?
                round((($totalTickets - $responseBreaches) / $totalTickets) * 100, 1) : 100,
            'resolution_compliance' => $totalTickets > 0 ?
                round((($totalTickets - $resolutionBreaches) / $totalTickets) * 100, 1) : 100,
        ];
    }

    private function getTrendData($startDate, $endDate)
    {
        // Daily ticket creation trend
        $dailyTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing dates with 0
        $period = Carbon::parse($startDate)->daysUntil($endDate);
        $trendData = [];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $trendData[] = [
                'date' => $date->format('M j'),
                'tickets' => $dailyTickets[$dateKey] ?? 0,
            ];
        }

        return $trendData;
    }

    private function getAverageResponseTime($startDate, $endDate)
    {
        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('first_response_at')
            ->get();

        if ($tickets->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->first_response_at);
        });

        $avgMinutes = $totalMinutes / $tickets->count();

        return $this->formatDuration($avgMinutes);
    }

    private function getAverageResolutionTime($startDate, $endDate)
    {
        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->get();

        if ($tickets->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->resolved_at);
        });

        $avgMinutes = $totalMinutes / $tickets->count();

        return $this->formatDuration($avgMinutes);
    }

    private function getAgentAverageResponseTime($agentId, $startDate, $endDate)
    {
        $responses = TicketReply::where('user_id', $agentId)
            ->where('is_internal', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('ticket')
            ->get();

        if ($responses->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $responses->sum(function ($reply) {
            return $reply->ticket->created_at->diffInMinutes($reply->created_at);
        });

        $avgMinutes = $totalMinutes / $responses->count();

        return $this->formatDuration($avgMinutes);
    }

    private function getOfficeAverageResolutionTime($officeId, $startDate, $endDate)
    {
        $tickets = Ticket::where('office_id', $officeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->get();

        if ($tickets->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->resolved_at);
        });

        $avgMinutes = $totalMinutes / $tickets->count();

        return $this->formatDuration($avgMinutes);
    }

    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return round($minutes).'m';
        } elseif ($minutes < 1440) { // Less than 24 hours
            $hours = floor($minutes / 60);
            $mins = round($minutes % 60);

            return $hours.'h '.$mins.'m';
        } else { // Days
            $days = floor($minutes / 1440);
            $hours = floor(($minutes % 1440) / 60);

            return $days.'d '.$hours.'h';
        }
    }
}
