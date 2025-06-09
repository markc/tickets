<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $totalTickets = Ticket::count();
        $newTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();
        $resolvedTickets = Ticket::whereBetween('updated_at', [$startDate, $endDate])
            ->whereHas('status', function ($query) {
                $query->where('name', 'Closed');
            })->count();

        $openTickets = Ticket::whereHas('status', function ($query) {
            $query->whereIn('name', ['Open', 'In Progress', 'On Hold']);
        })->count();

        $avgResponseTime = $this->getAverageResponseTime($startDate, $endDate);
        $slaCompliance = $this->getSlaCompliance($startDate, $endDate);

        return [
            Stat::make('Total Tickets', number_format($totalTickets))
                ->description('All time')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('New Tickets (30d)', number_format($newTickets))
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success'),

            Stat::make('Resolved (30d)', number_format($resolvedTickets))
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Open Tickets', number_format($openTickets))
                ->description('Currently open')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Avg Response Time', $avgResponseTime)
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('SLA Compliance', $slaCompliance.'%')
                ->description('Response SLA (30d)')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($slaCompliance >= 90 ? 'success' : ($slaCompliance >= 70 ? 'warning' : 'danger')),
        ];
    }

    private function getAverageResponseTime($startDate, $endDate): string
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

    private function getSlaCompliance($startDate, $endDate): float
    {
        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->get();

        if ($tickets->isEmpty()) {
            return 100;
        }

        $totalTickets = $tickets->count();
        $responseBreaches = $tickets->filter(function ($ticket) {
            return $ticket->isResponseSlaBreached();
        })->count();

        return round((($totalTickets - $responseBreaches) / $totalTickets) * 100, 1);
    }

    private function formatDuration($minutes): string
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
