<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketTimeline;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketMergeService
{
    /**
     * Merge a source ticket into a target ticket
     */
    public function mergeTickets(Ticket $sourceTicket, Ticket $targetTicket, User $mergedBy, ?string $reason = null): bool
    {
        // Validate merge is possible
        if (! $sourceTicket->canBeMergedInto($targetTicket)) {
            throw new \InvalidArgumentException('These tickets cannot be merged');
        }

        try {
            DB::beginTransaction();

            // Move all replies from source to target
            $this->moveReplies($sourceTicket, $targetTicket);

            // Move all attachments from source to target
            $this->moveAttachments($sourceTicket, $targetTicket);

            // Move timeline entries from source to target
            $this->moveTimelineEntries($sourceTicket, $targetTicket);

            // Update the source ticket as merged
            $sourceTicket->update([
                'is_merged' => true,
                'merged_into_id' => $targetTicket->uuid,
                'merged_at' => now(),
                'merged_by_id' => $mergedBy->id,
                'merge_reason' => $reason,
            ]);

            // Add timeline entry to target ticket about the merge
            $this->addMergeTimelineEntry($targetTicket, $sourceTicket, $mergedBy, $reason);

            // Update target ticket's updated_at timestamp
            $targetTicket->touch();

            DB::commit();

            Log::info('Tickets merged successfully', [
                'source_ticket' => $sourceTicket->uuid,
                'target_ticket' => $targetTicket->uuid,
                'merged_by' => $mergedBy->id,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to merge tickets', [
                'source_ticket' => $sourceTicket->uuid,
                'target_ticket' => $targetTicket->uuid,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Move all replies from source ticket to target ticket
     */
    private function moveReplies(Ticket $sourceTicket, Ticket $targetTicket): void
    {
        $sourceTicket->replies()->update(['ticket_id' => $targetTicket->id]);
    }

    /**
     * Move all attachments from source ticket to target ticket
     */
    private function moveAttachments(Ticket $sourceTicket, Ticket $targetTicket): void
    {
        $sourceTicket->attachments()->update([
            'attachable_id' => $targetTicket->id,
        ]);
    }

    /**
     * Move timeline entries from source to target, adding context
     */
    private function moveTimelineEntries(Ticket $sourceTicket, Ticket $targetTicket): void
    {
        $timelineEntries = $sourceTicket->timeline()->get();

        foreach ($timelineEntries as $entry) {
            TicketTimeline::create([
                'ticket_id' => $targetTicket->id,
                'user_id' => $entry->user_id,
                'entry' => $entry->entry." (Merged from ticket #{$sourceTicket->uuid})",
                'created_at' => $entry->created_at,
                'updated_at' => $entry->updated_at,
            ]);
        }

        // Delete original timeline entries to avoid duplication
        $sourceTicket->timeline()->delete();
    }

    /**
     * Add a timeline entry about the merge
     */
    private function addMergeTimelineEntry(Ticket $targetTicket, Ticket $sourceTicket, User $mergedBy, ?string $reason = null): void
    {
        $description = "Ticket #{$sourceTicket->uuid} was merged into this ticket";
        if ($reason) {
            $description .= ". Reason: {$reason}";
        }

        TicketTimeline::create([
            'ticket_id' => $targetTicket->id,
            'user_id' => $mergedBy->id,
            'entry' => $description,
        ]);
    }

    /**
     * Get suggested tickets for merging based on similarity
     */
    public function getSuggestedMergeTargets(Ticket $ticket, int $limit = 5): array
    {
        $suggestions = [];

        // Find tickets with similar subjects in the same office
        $similarSubjects = Ticket::notMerged()
            ->where('office_id', $ticket->office_id)
            ->where('uuid', '!=', $ticket->uuid)
            ->where('subject', 'LIKE', '%'.$this->extractKeywords($ticket->subject).'%')
            ->with(['creator', 'status', 'priority'])
            ->limit($limit)
            ->get();

        foreach ($similarSubjects as $similarTicket) {
            $suggestions[] = [
                'ticket' => $similarTicket,
                'similarity_score' => $this->calculateSimilarityScore($ticket, $similarTicket),
                'reason' => 'Similar subject',
            ];
        }

        // Find tickets from the same creator with similar content
        $sameCreatorTickets = Ticket::notMerged()
            ->where('office_id', $ticket->office_id)
            ->where('creator_id', $ticket->creator_id)
            ->where('uuid', '!=', $ticket->uuid)
            ->where('created_at', '>=', now()->subDays(30)) // Within last 30 days
            ->with(['creator', 'status', 'priority'])
            ->limit($limit)
            ->get();

        foreach ($sameCreatorTickets as $sameCreatorTicket) {
            $suggestions[] = [
                'ticket' => $sameCreatorTicket,
                'similarity_score' => $this->calculateSimilarityScore($ticket, $sameCreatorTicket),
                'reason' => 'Same customer, recent ticket',
            ];
        }

        // Sort by similarity score and remove duplicates
        $suggestions = collect($suggestions)
            ->unique(function ($item) {
                return $item['ticket']->uuid;
            })
            ->sortByDesc('similarity_score')
            ->take($limit)
            ->values()
            ->toArray();

        return $suggestions;
    }

    /**
     * Extract keywords from a subject line
     */
    private function extractKeywords(string $subject): string
    {
        // Remove common words and extract meaningful keywords
        $commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'about', 'issue', 'problem', 'help', 'need'];

        $words = preg_split('/\s+/', strtolower($subject));
        $keywords = array_filter($words, function ($word) use ($commonWords) {
            return strlen($word) > 2 && ! in_array($word, $commonWords);
        });

        return implode(' ', array_slice($keywords, 0, 3)); // Return first 3 keywords
    }

    /**
     * Calculate similarity score between two tickets
     */
    public function calculateSimilarityScore(Ticket $ticket1, Ticket $ticket2): float
    {
        $score = 0;

        // Subject similarity (0-40 points)
        $subjectSimilarity = $this->stringSimilarity($ticket1->subject, $ticket2->subject);
        $score += $subjectSimilarity * 40;

        // Same creator (20 points)
        if ($ticket1->creator_id === $ticket2->creator_id) {
            $score += 20;
        }

        // Same priority (10 points)
        if ($ticket1->ticket_priority_id === $ticket2->ticket_priority_id) {
            $score += 10;
        }

        // Recent creation (0-20 points based on how recent)
        $daysDiff = abs($ticket1->created_at->diffInDays($ticket2->created_at));
        $recencyScore = max(0, 20 - ($daysDiff * 2)); // Lose 2 points per day
        $score += $recencyScore;

        // Content similarity (0-10 points)
        if ($ticket1->content && $ticket2->content) {
            $contentSimilarity = $this->stringSimilarity($ticket1->content, $ticket2->content);
            $score += $contentSimilarity * 10;
        }

        return round($score, 2);
    }

    /**
     * Calculate string similarity percentage
     */
    private function stringSimilarity(string $str1, string $str2): float
    {
        similar_text(strtolower($str1), strtolower($str2), $percent);

        return $percent / 100;
    }

    /**
     * Validate merge permissions
     */
    public function canUserMergeTickets(User $user, Ticket $sourceTicket, Ticket $targetTicket): bool
    {
        // Admins can merge any tickets
        if ($user->isAdmin()) {
            return true;
        }

        // Agents can merge tickets in their offices
        if ($user->isAgent()) {
            $userOfficeIds = $user->offices->pluck('id')->toArray();

            return in_array($sourceTicket->office_id, $userOfficeIds) &&
                   in_array($targetTicket->office_id, $userOfficeIds);
        }

        // Customers cannot merge tickets
        return false;
    }
}
