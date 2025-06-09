<?php

namespace App\Http\Controllers\Api;

use App\Events\TicketStatusChanged;
use App\Events\TicketUpdated;
use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'office_id' => 'nullable|integer|exists:offices,id',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $query = Ticket::with(['creator', 'assignedTo', 'office', 'status', 'priority']);

        // Apply authorization filters
        if ($user->isCustomer()) {
            $query->where('creator_id', $user->id);
        } elseif ($user->isAgent()) {
            $query->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('assigned_to_id', $user->id)
                    ->orWhereIn('office_id', $user->offices->pluck('id'));
            });
        }
        // Admins can see all tickets

        // Apply filters
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->status.'%');
            });
        }

        if ($request->filled('priority')) {
            $query->whereHas('priority', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->priority.'%');
            });
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%'.$request->search.'%')
                    ->orWhere('content', 'like', '%'.$request->search.'%')
                    ->orWhere('uuid', 'like', '%'.$request->search.'%');
            });
        }

        $perPage = $request->get('per_page', 15);
        $tickets = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'office_id' => 'required|integer|exists:offices,id',
            'priority_id' => 'nullable|integer|exists:ticket_priorities,id',
        ]);

        $user = $request->user();

        // Get default status and priority
        $defaultStatus = TicketStatus::where('is_default', true)->first()
                        ?? TicketStatus::first();
        $defaultPriority = TicketPriority::where('is_default', true)->first()
                          ?? TicketPriority::first();

        $ticket = Ticket::create([
            'uuid' => Str::uuid(),
            'subject' => $request->subject,
            'content' => $request->content,
            'creator_id' => $user->id,
            'office_id' => $request->office_id,
            'ticket_status_id' => $defaultStatus->id,
            'ticket_priority_id' => $request->priority_id ?? $defaultPriority->id,
        ]);

        $ticket->load(['creator', 'assignedTo', 'office', 'status', 'priority']);

        return response()->json([
            'data' => $ticket,
            'message' => 'Ticket created successfully',
        ], 201);
    }

    /**
     * Display the specified ticket
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $ticket = Ticket::with([
            'creator',
            'assignedTo',
            'office',
            'status',
            'priority',
            'replies.user',
            'replies.attachments',
            'attachments',
            'timeline.user',
        ])->where('uuid', $uuid)->firstOrFail();

        if (! Gate::allows('view', $ticket)) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        // Filter replies based on user role
        if ($request->user()->isCustomer()) {
            $ticket->setRelation('replies', $ticket->publicReplies);
        }

        return response()->json([
            'data' => $ticket,
        ]);
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        if (! Gate::allows('update', $ticket)) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $rules = [];

        // Customers can only update subject and content if ticket is not closed
        if ($request->user()->isCustomer()) {
            $rules = [
                'subject' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
            ];
        } else {
            // Agents and admins can update more fields
            $rules = [
                'subject' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'office_id' => 'sometimes|integer|exists:offices,id',
                'ticket_status_id' => 'sometimes|integer|exists:ticket_statuses,id',
                'ticket_priority_id' => 'sometimes|integer|exists:ticket_priorities,id',
                'assigned_to_id' => 'sometimes|nullable|integer|exists:users,id',
            ];
        }

        $request->validate($rules);

        // Get original status for status change event
        $originalStatus = $ticket->status;

        // Track changes for the event
        $changes = array_intersect_key($request->all(), array_flip(array_keys($rules)));

        $ticket->update($request->only(array_keys($rules)));
        $ticket->load(['creator', 'assignedTo', 'office', 'status', 'priority']);

        // Dispatch status change event if status was updated
        if (isset($changes['ticket_status_id']) && $originalStatus->id !== $ticket->ticket_status_id) {
            event(new TicketStatusChanged($ticket, $originalStatus, $ticket->status, $request->user()));
        }

        // Dispatch general update event
        event(new TicketUpdated($ticket, $request->user(), $changes));

        return response()->json([
            'data' => $ticket,
            'message' => 'Ticket updated successfully',
        ]);
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(string $uuid): JsonResponse
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        if (! Gate::allows('delete', $ticket)) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully',
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Ticket::query();

        // Apply authorization filters
        if ($user->isCustomer()) {
            $query->where('creator_id', $user->id);
        } elseif ($user->isAgent()) {
            $query->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('assigned_to_id', $user->id)
                    ->orWhereIn('office_id', $user->offices->pluck('id'));
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->whereHas('status', function ($q) {
                $q->where('name', '!=', 'Closed');
            })->count(),
            'closed' => (clone $query)->whereHas('status', function ($q) {
                $q->where('name', 'Closed');
            })->count(),
            'assigned_to_me' => $user->isCustomer() ? 0 : (clone $query)->where('assigned_to_id', $user->id)->count(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get form data for creating/editing tickets
     */
    public function formData(): JsonResponse
    {
        return response()->json([
            'data' => [
                'offices' => Office::select(['id', 'name'])->get(),
                'statuses' => TicketStatus::select(['id', 'name', 'color'])->get(),
                'priorities' => TicketPriority::select(['id', 'name', 'color'])->get(),
            ],
        ]);
    }
}
