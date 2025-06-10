<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = safe_auth_user();

        if (! $user) {
            abort(401, 'User not authenticated');
        }

        $stats = [];

        if ($user->isCustomer()) {
            $stats = $this->getCustomerStats($user);
        } else {
            $stats = $this->getAgentAdminStats($user);
        }

        return view('dashboard', compact('stats'));
    }

    private function getCustomerStats($user)
    {
        $tickets = $user->createdTickets();

        return [
            'total_tickets' => $tickets->count(),
            'open_tickets' => $tickets->whereHas('status', function ($q) {
                $q->where('name', '!=', 'Closed');
            })->count(),
            'recent_tickets' => $tickets->with(['status', 'priority', 'office'])
                ->latest()
                ->take(5)
                ->get(),
            'by_status' => $tickets->select('ticket_status_id', DB::raw('count(*) as count'))
                ->with('status')
                ->groupBy('ticket_status_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->status->name => $item->count];
                }),
        ];
    }

    private function getAgentAdminStats($user)
    {
        $ticketsQuery = Ticket::query();

        if ($user->isAgent()) {
            $officeIds = $user->offices->pluck('id');
            $ticketsQuery->whereIn('office_id', $officeIds);
        }

        $totalTickets = $ticketsQuery->count();
        $assignedToMe = $user->assignedTickets()->count();

        $statusStats = TicketStatus::withCount(['tickets' => function ($q) use ($user) {
            if ($user->isAgent()) {
                $officeIds = $user->offices->pluck('id');
                $q->whereIn('office_id', $officeIds);
            }
        }])->get();

        $priorityStats = TicketPriority::withCount(['tickets' => function ($q) use ($user) {
            if ($user->isAgent()) {
                $officeIds = $user->offices->pluck('id');
                $q->whereIn('office_id', $officeIds);
            }
        }])->get();

        $recentTickets = $ticketsQuery->with(['status', 'priority', 'office', 'creator', 'assignedTo'])
            ->latest()
            ->take(10)
            ->get();

        return [
            'total_tickets' => $totalTickets,
            'assigned_to_me' => $assignedToMe,
            'unassigned_tickets' => $ticketsQuery->whereNull('assigned_to_id')->count(),
            'status_stats' => $statusStats,
            'priority_stats' => $priorityStats,
            'recent_tickets' => $recentTickets,
        ];
    }
}
