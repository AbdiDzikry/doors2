<!DOCTYPE html>
<html>
<head>
    <title>Meeting Confirmation Reminder</title>
</head>
<body>
    <h1>Meeting Confirmation Reminder</h1>
    <p>
        This is a reminder to confirm your upcoming meeting:
    </p>
    <p>
        <strong>Topic:</strong> {{ $meeting->topic }}<br>
        <strong>Room:</strong> {{ $meeting->room->name }}<br>
        <strong>Date:</strong> {{ $meeting->start_time->format('d M Y') }}<br>
        <strong>Time:</strong> {{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}
    </p>
    <p>
        Please confirm or cancel your meeting by visiting the link below:
    </p>
    <a href="{{ route('meeting.recurring-meetings.index') }}">My Recurring Meetings</a>
</body>
</html>
