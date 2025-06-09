<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Ticket;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KnowledgeBaseController extends Controller
{
    private KnowledgeBaseService $knowledgeBaseService;

    public function __construct(KnowledgeBaseService $knowledgeBaseService)
    {
        $this->knowledgeBaseService = $knowledgeBaseService;
    }

    /**
     * Get FAQ suggestions for a ticket
     */
    public function getSuggestions(Request $request, Ticket $ticket): JsonResponse
    {
        // Check if user can view this ticket
        if (! Gate::allows('view', $ticket)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $limit = $request->get('limit', 5);
        $suggestions = $this->knowledgeBaseService->getSuggestedFAQs($ticket, $limit);

        return response()->json([
            'data' => $suggestions->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'office' => $faq->office?->name,
                    'excerpt' => \Str::limit(strip_tags($faq->answer), 150),
                ];
            }),
            'ticket_id' => $ticket->uuid,
            'total' => $suggestions->count(),
        ]);
    }

    /**
     * Search FAQs with optional filtering
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|max:255',
            'office_id' => 'nullable|integer|exists:offices,id',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->get('q');
        $officeId = $request->get('office_id');
        $limit = $request->get('limit', 10);

        // For customers, only show FAQs from their ticket's office or global FAQs
        if (auth()->user()->isCustomer()) {
            // Get user's recent ticket office or no filtering
            $userOfficeId = auth()->user()->tickets()
                ->latest()
                ->first()?->office_id;
            $officeId = $userOfficeId;
        }

        $results = $this->knowledgeBaseService->searchFAQs($query, $officeId, $limit);

        return response()->json([
            'data' => $results->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'office' => $faq->office?->name,
                    'excerpt' => \Str::limit(strip_tags($faq->answer), 150),
                ];
            }),
            'query' => $query,
            'total' => $results->count(),
        ]);
    }

    /**
     * Get trending/popular FAQs
     */
    public function trending(Request $request): JsonResponse
    {
        $request->validate([
            'office_id' => 'nullable|integer|exists:offices,id',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $officeId = $request->get('office_id');
        $limit = $request->get('limit', 5);

        // For customers, filter by their office
        if (auth()->user()->isCustomer()) {
            $userOfficeId = auth()->user()->tickets()
                ->latest()
                ->first()?->office_id;
            $officeId = $userOfficeId;
        }

        $trending = $this->knowledgeBaseService->getTrendingFAQs($officeId, $limit);

        return response()->json([
            'data' => $trending->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'office' => $faq->office?->name,
                    'usage_count' => $faq->usage_count ?? 0,
                    'excerpt' => \Str::limit(strip_tags($faq->answer), 150),
                ];
            }),
            'total' => $trending->count(),
        ]);
    }

    /**
     * Get formatted FAQ content for insertion
     */
    public function format(Request $request, FAQ $faq): JsonResponse
    {
        $request->validate([
            'format' => 'nullable|in:markdown,html,plain',
            'ticket_id' => 'required|uuid|exists:tickets,uuid',
        ]);

        $format = $request->get('format', 'markdown');
        $ticketUuid = $request->get('ticket_id');
        $ticket = Ticket::where('uuid', $ticketUuid)->firstOrFail();

        // Check if user can view this ticket
        if (! Gate::allows('view', $ticket)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $formattedContent = $this->knowledgeBaseService->formatFAQForInsertion($faq, $format);

        return response()->json([
            'data' => [
                'id' => $faq->id,
                'question' => $faq->question,
                'formatted_content' => $formattedContent,
                'format' => $format,
            ],
        ]);
    }

    /**
     * Track FAQ usage when inserted into ticket reply
     */
    public function trackUsage(Request $request, FAQ $faq): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|uuid|exists:tickets,uuid',
            'context' => 'nullable|in:reply_insertion,suggestion_view,search_result',
        ]);

        $ticketUuid = $request->get('ticket_id');
        $context = $request->get('context', 'reply_insertion');
        $ticket = Ticket::where('uuid', $ticketUuid)->firstOrFail();

        // Check if user can view this ticket
        if (! Gate::allows('view', $ticket)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->knowledgeBaseService->trackFAQUsage($faq, $ticket, auth()->user(), $context);

        return response()->json([
            'success' => true,
            'message' => 'FAQ usage tracked successfully',
        ]);
    }

    /**
     * Get FAQ analytics (admin only)
     */
    public function analytics(Request $request): JsonResponse
    {
        // Only admins can access analytics
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = $request->get('days', 30);
        $analytics = $this->knowledgeBaseService->getFAQAnalytics($days);

        return response()->json([
            'data' => $analytics,
            'period_days' => $days,
        ]);
    }

    /**
     * Get FAQ details with usage statistics
     */
    public function show(FAQ $faq): JsonResponse
    {
        // Check if FAQ is published or user is admin/agent
        if (! $faq->is_published && auth()->user()->isCustomer()) {
            return response()->json(['error' => 'FAQ not found'], 404);
        }

        $usageCount = \DB::table('faq_usage_tracking')
            ->where('faq_id', $faq->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'data' => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'office' => $faq->office?->name,
                'is_published' => $faq->is_published,
                'sort_order' => $faq->sort_order,
                'usage_count_30_days' => $usageCount,
                'created_at' => $faq->created_at,
                'updated_at' => $faq->updated_at,
            ],
        ]);
    }
}
