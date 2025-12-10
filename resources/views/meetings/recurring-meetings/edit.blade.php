@extends('layouts.master')

@section('title', 'Edit Recurring Meeting')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Recurring Meeting</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form action="{{ route('meeting.recurring-meetings.update', $recurringMeeting->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="frequency" class="block text-gray-700 text-sm font-bold mb-2">Frequency:</label>
                <select name="frequency" id="frequency" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="daily" {{ $recurringMeeting->frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $recurringMeeting->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $recurringMeeting->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="ends_at" class="block text-gray-700 text-sm font-bold mb-2">Ends At:</label>
                <input type="date" name="ends_at" id="ends_at" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ \Carbon\Carbon::parse($recurringMeeting->ends_at)->format('Y-m-d') }}">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
                <a href="{{ route('meeting.recurring-meetings.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Cancel</a>
            </div>
        </form>
    </div>
@endsection