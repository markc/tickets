<?php

namespace App\Services;

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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailTicketService
{
    public function __construct(
        private TicketAssignmentService $assignmentService
    ) {}

    public function createTicketFromEmail(
        string $fromEmail,
        string $fromName,
        string $subject,
        string $content,
        array $attachments = []
    ): ?Ticket {
        try {
            $user = $this->findOrCreateUser($fromEmail, $fromName);
            $defaultOffice = $this->getDefaultOffice();
            $defaultStatus = TicketStatus::where('is_default', true)->first();
            $defaultPriority = $this->getDefaultPriority();

            $ticket = Ticket::create([
                'uuid' => Str::uuid(),
                'subject' => $this->cleanSubject($subject),
                'content' => $content,
                'creator_id' => $user->id,
                'office_id' => $defaultOffice->id,
                'ticket_status_id' => $defaultStatus->id,
                'ticket_priority_id' => $defaultPriority->id,
            ]);

            $this->processAttachments($attachments, $ticket);

            TicketTimeline::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'created',
                'description' => 'Ticket created via email',
            ]);

            $this->assignmentService->autoAssignTicket($ticket);

            $this->sendCreatedNotifications($ticket);

            Log::info('Email ticket created', [
                'ticket_id' => $ticket->id,
                'ticket_uuid' => $ticket->uuid,
                'from_email' => $fromEmail,
                'subject' => $subject,
            ]);

            return $ticket;

        } catch (\Exception $e) {
            Log::error('Failed to create ticket from email', [
                'from_email' => $fromEmail,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function createReplyFromEmail(
        string $ticketUuid,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $content,
        array $attachments = []
    ): ?TicketReply {
        try {
            $ticket = Ticket::where('uuid', $ticketUuid)->first();

            if (! $ticket) {
                Log::warning('Attempted to reply to non-existent ticket', [
                    'ticket_uuid' => $ticketUuid,
                    'from_email' => $fromEmail,
                ]);

                return null;
            }

            $user = $this->findOrCreateUser($fromEmail, $fromName);

            if (! $this->canReplyToTicket($user, $ticket)) {
                Log::warning('User not authorized to reply to ticket', [
                    'ticket_uuid' => $ticketUuid,
                    'from_email' => $fromEmail,
                    'ticket_creator' => $ticket->creator->email,
                ]);

                return null;
            }

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $content,
            ]);

            $this->processAttachments($attachments, $reply);

            TicketTimeline::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'replied',
                'description' => 'Reply added via email',
            ]);

            $this->reopenTicketIfClosed($ticket, $user);
            $this->sendReplyNotifications($ticket, $reply);

            Log::info('Email reply created', [
                'ticket_id' => $ticket->id,
                'ticket_uuid' => $ticketUuid,
                'reply_id' => $reply->id,
                'from_email' => $fromEmail,
            ]);

            return $reply;

        } catch (\Exception $e) {
            Log::error('Failed to create reply from email', [
                'ticket_uuid' => $ticketUuid,
                'from_email' => $fromEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function findOrCreateUser(string $email, string $name): User
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            if (empty($user->name) || $user->name === $user->email) {
                $user->update(['name' => $name]);
            }

            return $user;
        }

        return User::create([
            'name' => $name ?: $email,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }

    private function processAttachments(array $attachments, $model): void
    {
        foreach ($attachments as $attachmentData) {
            try {
                if (! $this->isValidAttachment($attachmentData)) {
                    continue;
                }

                $filename = $this->sanitizeFilename($attachmentData['filename']);
                $path = 'email-attachments/'.date('Y/m/d/').uniqid().'_'.$filename;

                Storage::disk('public')->put($path, $attachmentData['content']);

                Attachment::create([
                    'attachable_type' => get_class($model),
                    'attachable_id' => $model->id,
                    'filename' => $filename,
                    'path' => $path,
                    'size' => $attachmentData['size'],
                    'mime_type' => $attachmentData['mime_type'],
                ]);

            } catch (\Exception $e) {
                Log::warning('Failed to process email attachment', [
                    'filename' => $attachmentData['filename'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function isValidAttachment(array $attachmentData): bool
    {
        if (empty($attachmentData['filename']) || empty($attachmentData['content'])) {
            return false;
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($attachmentData['size'] > $maxSize) {
            return false;
        }

        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'text/csv',
            'application/zip', 'application/x-zip-compressed',
        ];

        return in_array($attachmentData['mime_type'], $allowedTypes);
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);

        return trim($filename, '_');
    }

    private function canReplyToTicket(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return true;
        }

        return $ticket->creator_id === $user->id;
    }

    private function reopenTicketIfClosed(Ticket $ticket, User $user): void
    {
        $closedStatus = TicketStatus::where('name', 'Closed')->first();
        $openStatus = TicketStatus::where('is_default', true)->first();

        if ($closedStatus && $openStatus && $ticket->ticket_status_id === $closedStatus->id && $user->isCustomer()) {
            $ticket->update(['ticket_status_id' => $openStatus->id]);

            TicketTimeline::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'reopened',
                'description' => 'Ticket reopened via email reply',
            ]);
        }
    }

    private function getDefaultOffice(): Office
    {
        return Office::where('name', 'General Support')
            ->orWhere('name', 'Customer Support')
            ->first() ?: Office::first();
    }

    private function getDefaultPriority(): TicketPriority
    {
        return TicketPriority::where('name', 'Medium')
            ->orWhere('sort_order', 2)
            ->first() ?: TicketPriority::orderBy('sort_order')->first();
    }

    private function cleanSubject(string $subject): string
    {
        $subject = preg_replace('/^(Re:|Fwd?:|FW:)\s*/i', '', $subject);
        $subject = preg_replace('/\[Ticket:\s*#\d+\]\s*/', '', $subject);

        return trim($subject) ?: 'No Subject';
    }

    private function sendCreatedNotifications(Ticket $ticket): void
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('Failed to send created notifications', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendReplyNotifications(Ticket $ticket, TicketReply $reply): void
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('Failed to send reply notifications', [
                'ticket_id' => $ticket->id,
                'reply_id' => $reply->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
