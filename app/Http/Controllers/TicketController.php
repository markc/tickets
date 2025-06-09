<?php

namespace App\Http\Controllers;

use App\Events\TicketReplyCreated;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Attachment;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\TicketTimeline;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplyAdded;
use App\Services\TicketAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()->createdTickets()
            ->with(['status', 'priority', 'office', 'assignedTo'])
            ->latest()
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $offices = Office::all();
        $priorities = TicketPriority::orderBy('sort_order')->get();

        return view('tickets.create', compact('offices', 'priorities'));
    }

    public function store(StoreTicketRequest $request)
    {
        $validated = $request->validated();

        $defaultStatus = TicketStatus::where('is_default', true)->first()
            ?? TicketStatus::where('name', 'Open')->first()
            ?? TicketStatus::first();

        $ticket = Ticket::create([
            'uuid' => Str::uuid(),
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'creator_id' => auth()->id(),
            'office_id' => $validated['office_id'],
            'ticket_priority_id' => $validated['ticket_priority_id'],
            'ticket_status_id' => $defaultStatus->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');

                Attachment::create([
                    'attachable_type' => Ticket::class,
                    'attachable_id' => $ticket->id,
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'entry' => 'Ticket created',
        ]);

        $assignmentService = new TicketAssignmentService;
        $assignmentService->autoAssignTicket($ticket);

        $this->sendTicketCreatedNotifications($ticket);

        return redirect()->route('tickets.show', $ticket->uuid)
            ->with('success', 'Ticket created successfully!');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        // Load relationships based on user role
        if (auth()->user()->isCustomer()) {
            // Customers only see public replies
            $ticket->load([
                'status',
                'priority',
                'office',
                'creator',
                'assignedTo',
                'publicReplies.user',
                'publicReplies.attachments',
                'attachments',
                'timeline.user',
            ]);
        } else {
            // Agents and admins see all replies including internal notes
            $ticket->load([
                'status',
                'priority',
                'office',
                'creator',
                'assignedTo',
                'replies.user',
                'replies.attachments',
                'attachments',
                'timeline.user',
            ]);
        }

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $validated = $request->validate([
            'content' => 'required|string',
            'is_internal' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
        ]);

        // Only agents and admins can create internal notes
        $isInternal = $validated['is_internal'] ?? false;
        if ($isInternal && auth()->user()->isCustomer()) {
            $isInternal = false;
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'is_internal' => $isInternal,
        ]);

        // Update first response time if this is the first agent/admin response
        if (! $isInternal && ! auth()->user()->isCustomer() && ! $ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        // Mark ticket as resolved if status is set to closed
        if ($ticket->status->name === 'Closed' && ! $ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');

                Attachment::create([
                    'attachable_type' => TicketReply::class,
                    'attachable_id' => $reply->id,
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'entry' => $isInternal ? 'Added an internal note' : 'Added a reply',
        ]);

        // Only send notifications for public replies, not internal notes
        if (! $isInternal) {
            $this->sendReplyNotifications($ticket, $reply);
        }

        // Dispatch real-time event
        $reply->load(['user', 'attachments']);
        event(new TicketReplyCreated($reply, $ticket));

        return back()->with('success', 'Reply added successfully!');
    }

    private function sendTicketCreatedNotifications(Ticket $ticket)
    {
        $ticket->load(['creator', 'office', 'priority']);

        $ticket->creator->notify(new TicketCreated($ticket));

        $officeAgents = $ticket->office->users()->where('role', 'agent')->get();
        $admins = User::where('role', 'admin')->get();

        foreach ($officeAgents as $agent) {
            $agent->notify(new TicketCreated($ticket));
        }

        foreach ($admins as $admin) {
            $admin->notify(new TicketCreated($ticket));
        }
    }

    private function sendReplyNotifications(Ticket $ticket, TicketReply $reply)
    {
        $ticket->load(['creator', 'office', 'assignedTo']);
        $reply->load('user');

        $notifyUsers = collect();

        if ($reply->user_id !== $ticket->creator_id) {
            $notifyUsers->push($ticket->creator);
        }

        if ($ticket->assignedTo && $reply->user_id !== $ticket->assignedTo->id) {
            $notifyUsers->push($ticket->assignedTo);
        }

        if (! $reply->user->isCustomer()) {
            $officeAgents = $ticket->office->users()
                ->where('role', 'agent')
                ->where('id', '!=', $reply->user_id)
                ->get();

            foreach ($officeAgents as $agent) {
                $notifyUsers->push($agent);
            }

            $admins = User::where('role', 'admin')
                ->where('id', '!=', $reply->user_id)
                ->get();

            foreach ($admins as $admin) {
                $notifyUsers->push($admin);
            }
        }

        foreach ($notifyUsers->unique('id') as $user) {
            $user->notify(new TicketReplyAdded($ticket, $reply));
        }
    }
}
