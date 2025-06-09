<?php

namespace App\Http\Controllers;

use App\Models\CannedResponse;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CannedResponseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CannedResponse::class);

        $query = CannedResponse::with('user')
            ->accessibleByUser(auth()->user())
            ->active();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('content', 'like', '%'.$request->search.'%');
            });
        }

        $responses = $query->orderBy('usage_count', 'desc')
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $responses->map(function ($response) {
                return [
                    'id' => $response->id,
                    'title' => $response->title,
                    'content' => $response->content,
                    'category' => $response->category,
                    'usage_count' => $response->usage_count,
                    'is_public' => $response->is_public,
                    'created_by' => $response->user->name,
                ];
            }),
            'categories' => CannedResponse::getCommonCategories(),
        ]);
    }

    public function show(CannedResponse $cannedResponse): JsonResponse
    {
        $this->authorize('view', $cannedResponse);

        return response()->json([
            'data' => [
                'id' => $cannedResponse->id,
                'title' => $cannedResponse->title,
                'content' => $cannedResponse->content,
                'category' => $cannedResponse->category,
                'variables' => CannedResponse::getAvailableVariables(),
            ],
        ]);
    }

    public function preview(Request $request, CannedResponse $cannedResponse): JsonResponse
    {
        $this->authorize('view', $cannedResponse);

        $request->validate([
            'ticket_id' => 'nullable|exists:tickets,uuid',
        ]);

        $variables = [];

        if ($request->filled('ticket_id')) {
            $ticket = Ticket::with(['creator', 'assignedTo'])->where('uuid', $request->ticket_id)->first();
            if ($ticket) {
                $variables = [
                    'customer_name' => $ticket->creator->name,
                    'customer_email' => $ticket->creator->email,
                    'ticket_id' => $ticket->uuid,
                    'ticket_subject' => $ticket->subject,
                    'agent_name' => auth()->user()->name,
                ];
            }
        } else {
            $variables = [
                'customer_name' => 'John Doe',
                'customer_email' => 'customer@example.com',
                'ticket_id' => 'TICKET-123',
                'ticket_subject' => 'Sample Ticket Subject',
                'agent_name' => auth()->user()->name,
            ];
        }

        $processedContent = $cannedResponse->replaceVariables($variables);

        return response()->json([
            'data' => [
                'original_content' => $cannedResponse->content,
                'processed_content' => $processedContent,
                'variables_used' => $variables,
            ],
        ]);
    }

    public function use(Request $request, CannedResponse $cannedResponse): JsonResponse
    {
        $this->authorize('view', $cannedResponse);

        $request->validate([
            'ticket_id' => 'nullable|exists:tickets,uuid',
        ]);

        $variables = [];

        if ($request->filled('ticket_id')) {
            $ticket = Ticket::with(['creator', 'assignedTo'])->where('uuid', $request->ticket_id)->first();
            if ($ticket) {
                $variables = [
                    'customer_name' => $ticket->creator->name,
                    'customer_email' => $ticket->creator->email,
                    'ticket_id' => $ticket->uuid,
                    'ticket_subject' => $ticket->subject,
                    'agent_name' => auth()->user()->name,
                ];
            }
        }

        $processedContent = $cannedResponse->replaceVariables($variables);

        // Track usage
        $cannedResponse->incrementUsage();

        return response()->json([
            'data' => [
                'content' => $processedContent,
                'title' => $cannedResponse->title,
            ],
        ]);
    }
}
