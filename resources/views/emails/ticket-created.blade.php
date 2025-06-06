@component('mail::message')
# New Support Ticket Created

Hello {{ $ticket->creator->name }},

A new support ticket has been created and assigned number **{{ substr($ticket->uuid, 0, 8) }}**.

**Subject:** {{ $ticket->subject }}  
**Priority:** {{ $ticket->priority->name }}  
**Department:** {{ $ticket->office->name }}  
@if($ticket->assignedTo)
**Assigned to:** {{ $ticket->assignedTo->name }}  
@endif

**Description:**
{{ $ticket->content }}

@if($ticket->attachments->count() > 0)
**Attachments:**
@foreach($ticket->attachments as $attachment)
- {{ $attachment->filename }} ({{ number_format($attachment->size / 1024, 1) }} KB)
@endforeach
@endif

@component('mail::button', ['url' => route('tickets.show', $ticket->uuid)])
View Ticket
@endcomponent

You can reply directly to this email to add comments to your ticket. Our support team will respond as soon as possible.

@if($isCustomer)
**Need help faster?** Check our [FAQ page]({{ route('faq.index') }}) for answers to common questions.
@endif

Thanks,<br>
{{ config('app.name') }} Support Team
@endcomponent