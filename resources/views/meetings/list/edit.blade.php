@extends('layouts.master')

@section('title', 'Edit Meeting')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <a href="{{ route('meeting.meeting-lists.show', $meeting) }}" class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Meeting Details
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Edit Meeting: {{ $meeting->topic }}</h1>
            <p class="text-sm text-gray-600">Modify the details of this meeting.</p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('meeting.meeting-lists.update', $meeting) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="topic" class="block text-sm font-medium text-gray-700">Meeting Topic</label>
                        <input type="text" name="topic" id="topic" value="{{ old('topic', $meeting->topic) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        @error('topic')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="room_id" class="block text-sm font-medium text-gray-700">Room</label>
                        {{-- This would typically be a dropdown of available rooms --}}
                        <input type="text" name="room_id" id="room_id" value="{{ old('room_id', $meeting->room->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" disabled>
                        <p class="text-xs text-gray-500 mt-1">Room selection not yet implemented for edit.</p>
                        @error('room_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                        <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $meeting->start_time->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        @error('start_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                        <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $meeting->end_time->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        @error('end_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Add more fields as needed, e.g., participants, pantry orders --}}
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Update Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
