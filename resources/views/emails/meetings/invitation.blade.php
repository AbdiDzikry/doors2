<x-mail::message>
# Meeting Invitation

You are invited to a meeting with the following details:

**Topic:** {{ $meeting->topic }}
**Room:** {{ $meeting->room->name }}
**Start Time:** {{ $meeting->start_time->format('M d, Y H:i A') }}
**End Time:** {{ $meeting->end_time->format('M d, Y H:i A') }}

@if($meeting->priorityGuest)
**Priority Guest:** {{ $meeting->priorityGuest->name }}
@endif

<x-mail::button :url="route('meeting.meeting-lists.show', $meeting)">
View Meeting Details
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>