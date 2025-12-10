@extends('layouts.master')

@section('title', 'Recurring Meeting Details')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Recurring Meeting Details</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <p class="text-gray-700 text-base">Frequency: {{ $recurringMeeting->frequency }}</p>
        <p class="text-gray-700 text-base">Ends At: {{ $recurringMeeting->ends_at }}</p>
        <!-- TODO: Display associated meetings in the series -->
    </div>

    <a href="{{ route('meeting.recurring-meetings.edit', $recurringMeeting->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
    <form action="{{ route('meeting.recurring-meetings.destroy', $recurringMeeting->id) }}" method="POST" class="inline-block">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
    <a href="{{ route('meeting.recurring-meetings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to List</a>
@endsection