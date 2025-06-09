<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketMergeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketMergeController extends Controller
{
    public function __construct(
        private TicketMergeService $ticketMergeService
    ) {}

    /**
     * Show merge interface for a ticket
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('merge', $ticket);

        // Get suggested merge targets
        $suggestions = $this->ticketMergeService->getSuggestedMergeTargets($ticket);

        // Get recently viewed tickets in the same office
        $recentTickets = Ticket::notMerged()
            ->where('office_id', $ticket->office_id)
            ->where('uuid', '!=', $ticket->uuid)
            ->with(['creator', 'status', 'priority'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('tickets.merge', compact('ticket', 'suggestions', 'recentTickets'));
    }

    /**
     * Search for potential merge targets
     */
    public function search(Request $request, Ticket $ticket)
    {
        $this->authorize('merge', $ticket);

        $request->validate([
            'q' => 'required|string|max:255',
        ]);

        $query = $request->input('q');

        $results = Ticket::notMerged()
            ->where('office_id', $ticket->office_id)
            ->where('uuid', '!=', $ticket->uuid)
            ->where(function ($q) use ($query) {
                $q->where('subject', 'LIKE', "%{$query}%")
                    ->orWhere('uuid', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->with(['creator', 'status', 'priority'])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        // Add similarity scores
        $resultsWithScores = $results->map(function ($result) use ($ticket) {
            return [
                'ticket' => $result,
                'similarity_score' => $this->ticketMergeService->calculateSimilarityScore($ticket, $result),
                'reason' => 'Search result',
            ];
        })->sortByDesc('similarity_score');

        return response()->json([
            'results' => $resultsWithScores->values(),
        ]);
    }

    /**
     * Execute the merge
     */
    public function merge(Request $request, Ticket $sourceTicket)
    {
        $this->authorize('merge', $sourceTicket);

        $request->validate([
            'target_ticket_uuid' => 'required|string|exists:tickets,uuid',
            'reason' => 'nullable|string|max:1000',
        ]);

        $targetTicket = Ticket::where('uuid', $request->target_ticket_uuid)->firstOrFail();

        // Verify user can merge these specific tickets
        if (! $this->ticketMergeService->canUserMergeTickets(Auth::user(), $sourceTicket, $targetTicket)) {
            abort(403, 'You do not have permission to merge these tickets');
        }

        // Verify tickets can be merged
        if (! $sourceTicket->canBeMergedInto($targetTicket)) {
            return back()->withErrors(['merge' => 'These tickets cannot be merged. They may be in different offices or already merged.']);
        }

        try {
            $this->ticketMergeService->mergeTickets(
                $sourceTicket,
                $targetTicket,
                Auth::user(),
                $request->reason
            );

            return redirect()
                ->route('tickets.show', $targetTicket)
                ->with('success', "Ticket #{$sourceTicket->uuid} has been successfully merged into this ticket.");

        } catch (\Exception $e) {
            return back()->withErrors(['merge' => 'Failed to merge tickets: '.$e->getMessage()]);
        }
    }

    /**
     * Get ticket details for merge preview
     */
    public function preview(Ticket $ticket, Request $request)
    {
        $this->authorize('merge', $ticket);

        $request->validate([
            'target_uuid' => 'required|string|exists:tickets,uuid',
        ]);

        $targetTicket = Ticket::where('uuid', $request->target_uuid)
            ->with(['creator', 'status', 'priority', 'replies.user', 'attachments'])
            ->firstOrFail();

        // Check if merge is possible
        $canMerge = $ticket->canBeMergedInto($targetTicket) &&
                   $this->ticketMergeService->canUserMergeTickets(Auth::user(), $ticket, $targetTicket);

        $similarityScore = $this->ticketMergeService->calculateSimilarityScore($ticket, $targetTicket);

        return response()->json([
            'target_ticket' => [
                'uuid' => $targetTicket->uuid,
                'subject' => $targetTicket->subject,
                'content' => $targetTicket->content,
                'creator' => $targetTicket->creator->name,
                'status' => $targetTicket->status->name,
                'priority' => $targetTicket->priority->name,
                'created_at' => $targetTicket->created_at->format('M j, Y g:i A'),
                'replies_count' => $targetTicket->replies->count(),
                'attachments_count' => $targetTicket->attachments->count(),
            ],
            'can_merge' => $canMerge,
            'similarity_score' => $similarityScore,
            'warnings' => $this->getMergeWarnings($ticket, $targetTicket),
        ]);
    }

    /**
     * Get warnings about the merge
     */
    private function getMergeWarnings(Ticket $sourceTicket, Ticket $targetTicket): array
    {
        $warnings = [];

        // Different creators
        if ($sourceTicket->creator_id !== $targetTicket->creator_id) {
            $warnings[] = 'Tickets are from different customers';
        }

        // Different priorities
        if ($sourceTicket->ticket_priority_id !== $targetTicket->ticket_priority_id) {
            $warnings[] = 'Tickets have different priorities';
        }

        // Different assigned agents
        if ($sourceTicket->assigned_to_id !== $targetTicket->assigned_to_id) {
            $warnings[] = 'Tickets are assigned to different agents';
        }

        // Large age difference
        $daysDiff = abs($sourceTicket->created_at->diffInDays($targetTicket->created_at));
        if ($daysDiff > 30) {
            $warnings[] = "Tickets were created {$daysDiff} days apart";
        }

        // Target ticket is closed/resolved
        if ($targetTicket->status->is_closed) {
            $warnings[] = 'Target ticket is already closed';
        }

        return $warnings;
    }
}
