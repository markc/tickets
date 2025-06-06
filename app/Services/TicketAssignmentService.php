<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketTimeline;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TicketAssignmentService
{
    public function autoAssignTicket(Ticket $ticket): void
    {
        $office = $ticket->office;

        $availableAgents = $office->users()
            ->where('role', 'agent')
            ->get();

        if ($availableAgents->isEmpty()) {
            return;
        }

        $selectedAgent = $this->selectAgentRoundRobin($office->id, $availableAgents);

        if ($selectedAgent) {
            $this->assignTicketToAgent($ticket, $selectedAgent);
        }
    }

    private function selectAgentRoundRobin(int $officeId, $agents): ?User
    {
        if ($agents->isEmpty()) {
            return null;
        }

        $cacheKey = "last_assigned_agent_office_{$officeId}";
        $lastAssignedAgentId = Cache::get($cacheKey);

        if (! $lastAssignedAgentId) {
            $selectedAgent = $agents->first();
        } else {
            $currentIndex = $agents->search(function ($agent) use ($lastAssignedAgentId) {
                return $agent->id == $lastAssignedAgentId;
            });

            if ($currentIndex === false) {
                $selectedAgent = $agents->first();
            } else {
                $nextIndex = ($currentIndex + 1) % $agents->count();
                $selectedAgent = $agents[$nextIndex];
            }
        }

        Cache::put($cacheKey, $selectedAgent->id, now()->addDays(30));

        return $selectedAgent;
    }

    private function assignTicketToAgent(Ticket $ticket, User $agent): void
    {
        $ticket->update(['assigned_to_id' => $agent->id]);

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'action' => 'assigned',
            'description' => "Ticket automatically assigned to {$agent->name}",
        ]);
    }

    public function reassignTicket(Ticket $ticket, User $newAgent): void
    {
        $oldAgent = $ticket->assignedTo;

        $ticket->update(['assigned_to_id' => $newAgent->id]);

        $description = $oldAgent
            ? "Ticket reassigned from {$oldAgent->name} to {$newAgent->name}"
            : "Ticket assigned to {$newAgent->name}";

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => 'reassigned',
            'description' => $description,
        ]);
    }

    public function unassignTicket(Ticket $ticket): void
    {
        $oldAgent = $ticket->assignedTo;

        $ticket->update(['assigned_to_id' => null]);

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => 'unassigned',
            'description' => $oldAgent ? "Ticket unassigned from {$oldAgent->name}" : 'Ticket unassigned',
        ]);
    }

    public function getAgentWorkload(User $agent): array
    {
        $assignedTickets = $agent->assignedTickets();

        return [
            'total_assigned' => $assignedTickets->count(),
            'open_tickets' => $assignedTickets->whereHas('status', function ($q) {
                $q->where('name', '!=', 'Closed');
            })->count(),
            'high_priority' => $assignedTickets->whereHas('priority', function ($q) {
                $q->where('name', 'High');
            })->count(),
        ];
    }
}
