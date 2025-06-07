<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // Base query with role-based filtering
        $ticketsQuery = Ticket::query();
        if ($user->isAgent()) {
            $officeIds = $user->offices->pluck('id');
            $ticketsQuery->whereIn('office_id', $officeIds);
        }

        // Calculate statistics
        $totalTickets = $ticketsQuery->count();
        $assignedToMe = $user->assignedTickets()->count();
        $unassignedTickets = $ticketsQuery->whereNull('assigned_to_id')->count();
        $openTickets = $ticketsQuery->whereHas('status', function ($q) {
            $q->where('name', '!=', 'Closed');
        })->count();

        return [
            Stat::make('Total Tickets', $totalTickets)
                ->description($user->isAgent() ? 'In your offices' : 'System-wide')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Assigned to Me', $assignedToMe)
                ->description('Tickets you own')
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),

            Stat::make('Unassigned', $unassignedTickets)
                ->description('Need assignment')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Open Tickets', $openTickets)
                ->description('Active tickets')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
