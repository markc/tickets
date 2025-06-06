@component('mail::message')
# New Reply on Your Support Ticket

Hello,

A new reply has been added to support ticket **{{ substr($ticket->uuid, 0, 8) }}**.

**Subject:** {{ $ticket->subject }}  
**Reply from:** {{ $reply->user->name }}@if($reply->user->isAgent() || $reply->user->isAdmin()) ({{ $reply->user->isAdmin() ? 'Support Team' : 'Support Agent' }})@endif  
**Date:** {{ $reply->created_at->format('M j, Y \a\t g:i A') }}

---

**Message:**

{{ $reply->message }}

@if($reply->attachments->count() > 0)
**Attachments:**
@foreach($reply->attachments as $attachment)
- {{ $attachment->filename }} ({{ number_format($attachment->size / 1024, 1) }} KB)
@endforeach
@endif

---

@component('mail::button', ['url' => route('tickets.show', $ticket->uuid)])
View Full Conversation
@endcomponent

You can reply directly to this email to continue the conversation. All replies will be added to your support ticket.

**Current Status:** {{ $ticket->status->name }}  
**Priority:** {{ $ticket->priority->name }}

Thanks,<br>
{{ config('app.name') }} Support Team

---
*Ticket #{{ substr($ticket->uuid, 0, 8) }} • {{ $ticket->office->name }}*
@endcomponent