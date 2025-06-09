<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User-specific channels
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Ticket-specific channels
Broadcast::channel('tickets.{ticketUuid}', function ($user, $ticketUuid) {
    $ticket = Ticket::where('uuid', $ticketUuid)->first();

    if (! $ticket) {
        return false;
    }

    // Allow access if user is the creator, assigned agent, or admin
    if ($user->id === $ticket->creator_id) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'creator'];
    }

    if ($user->id === $ticket->assigned_to_id) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'assignee'];
    }

    if ($user->isAdmin()) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'admin'];
    }

    // Allow agents if they have access to the ticket's office
    if ($user->isAgent() && $user->offices->contains($ticket->office_id)) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'agent'];
    }

    return false;
});

// Office-specific channels (for agents and admins)
Broadcast::channel('office.{officeId}', function ($user, $officeId) {
    if ($user->isAdmin()) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'admin'];
    }

    if ($user->isAgent() && $user->offices->contains((int) $officeId)) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'agent'];
    }

    return false;
});
