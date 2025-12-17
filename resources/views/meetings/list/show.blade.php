@extends('layouts.master')

@section('title', 'Meeting Details')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <a href="{{ route('meeting.meeting-lists.index') }}" class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Meeting List
            </a>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-800">Meeting Details</h1>
                    <span class="hidden md:inline text-gray-300">|</span>
                    <p class="text-sm text-gray-600">Topic: <span class="font-semibold">{{ $meeting->topic ?? 'N/A' }}</span></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bento Item 1: Meeting Info (Top Left, 2 Columns) -->
            <div class="lg:col-span-2 bg-white shadow-md rounded-xl p-6 h-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Topic</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $meeting->topic ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Room</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $meeting->room?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Start Time</h3>
                        <p class="mt-1 text-gray-900">{{ \Carbon\Carbon::parse($meeting->start_time)->format('l, d F Y, H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">End Time</h3>
                        <p class="mt-1 text-gray-900">{{ \Carbon\Carbon::parse($meeting->end_time)->format('l, d F Y, H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Status</h3>
                        @php
                                $statusClasses = match($meeting->calculated_status) {
                                    'scheduled' => 'bg-indigo-50 text-indigo-700',
                                    'ongoing' => 'bg-blue-100 text-blue-800 ring-2 ring-blue-500 ring-opacity-50',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                @if($meeting->calculated_status === 'ongoing')
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5 animate-pulse"></span>
                                @endif
                                {{ ucfirst($meeting->calculated_status) }}
                            </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Booked By</h3>
                        <p class="mt-1 text-gray-900">{{ $meeting->user?->name ?? 'N/A' }} ({{ $meeting->user?->email ?? 'N/A' }})</p>
                    </div>

                    @if ($meeting->recurringMeeting)
                    <div class="md:col-span-2 mt-4 pt-4 border-t border-gray-200">
                         <h3 class="text-sm font-medium text-gray-500">Recurring Meeting</h3>
                         <p class="mt-1 text-gray-900">
                             This is a <span class="font-semibold">{{ ucfirst($meeting->recurringMeeting->frequency) }}</span> meeting, ending on 
                             <span class="font-semibold">{{ \Carbon\Carbon::parse($meeting->recurringMeeting->ends_at)->format('d F Y') }}</span>.
                         </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Bento Item 2: Pantry Orders (Right Side, Vertical, Spans 2 Rows) -->
            <div class="lg:col-span-1 lg:row-span-2 bg-white shadow-md rounded-xl p-6 h-full flex flex-col">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">Pantry Orders</h2>
                <div class="flex-grow overflow-y-auto max-h-[600px] pr-2">
                    @if ($meeting->pantryOrders->isNotEmpty())
                        <ul class="divide-y divide-gray-200">
                            @foreach ($meeting->pantryOrders as $order)
                                <li class="py-3 flex justify-between items-start space-x-2">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-800">
                                            <span class="font-medium">{{ $order->pantryItem?->name ?? 'N/A' }}</span> 
                                            <span class="text-gray-500">x {{ $order->quantity }}</span>
                                        </div>
                                        @if($order->custom_items)
                                            <div class="text-xs text-amber-600 italic mt-1">
                                                <i class="far fa-sticky-note mr-1"></i> Note: {{ $order->custom_items }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium px-2 py-1 rounded-full whitespace-nowrap
                                        @switch($order->status)
                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                            @case('preparing') bg-blue-100 text-blue-800 @break
                                            @case('delivered') bg-green-100 text-green-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">No pantry orders for this meeting.</p>
                    @endif
                </div>
            </div>

            <!-- Bento Item 3: Participants (Bottom Left, 2 Columns) -->
            <div class="lg:col-span-2 bg-white shadow-md rounded-xl p-6" x-data="{ showAttendanceModal: false }">
                <div class="flex flex-wrap justify-between items-center mb-4 border-b border-gray-100 pb-2 gap-2">
                    <h2 class="text-lg font-semibold text-gray-800">Participants</h2>
                    <div class="flex items-center gap-2">
                        @if ($meeting->calculated_status !== 'cancelled')
                        <a href="{{ route('meeting.meetings.attendance.export', $meeting->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm flex items-center transition">
                            <i class="fas fa-file-excel mr-2"></i> Download Absensi
                        </a>
                        <button @click="showAttendanceModal = true" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm flex items-center transition">
                             <i class="far fa-id-card mr-2"></i> Record Attendance
                        </button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                    @if ($meeting->meetingParticipants->isNotEmpty())
                        @foreach ($meeting->meetingParticipants as $participant)
                            <div class="py-3 border-b border-gray-100 last:border-0 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                                @if ($participant->participant_type === 'App\Models\User') {{-- Internal Participant --}}
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-800 font-medium">{{ $participant->participant?->name ?? 'N/A' }}</span>
                                        <span class="text-xs text-gray-500">{{ $participant->participant?->email ?? '' }}</span>
                                    </div>
                                    <div class="flex flex-col sm:items-end space-y-1 mt-2 sm:mt-0">
                                        <span class="text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100 px-2 py-0.5 rounded-md">Internal</span>
                                        @if ($participant->attended_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                                <i class="fas fa-check-circle mr-1"></i> Hadir
                                                <span class="mx-1 opacity-50">|</span>
                                                {{ \Carbon\Carbon::parse($participant->attended_at)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                                <i class="fas fa-times-circle mr-1"></i> Belum Hadir
                                            </span>
                                        @endif
                                    </div>
                                @elseif ($participant->participant_type === 'App\Models\ExternalParticipant') {{-- External Participant --}}
                                    <div class="flex flex-col">
                                        <p class="text-sm text-gray-800">{{ $participant->participant?->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $participant->participant?->company ?? 'N/A' }}</p>
                                    </div>
                                    <span class="text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-md self-start sm:self-center">External</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500 col-span-2">No participants for this meeting.</p>
                    @endif
                </div>

                <!-- Attendance Modal -->
                <div x-show="showAttendanceModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="display: none;">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full" @click.away="showAttendanceModal = false">
                        <form action="{{ route('meeting.meetings.attendance.store', $meeting->id) }}" method="POST">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="far fa-id-badge text-green-600"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900">Record Attendance</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 mb-4">Please enter your NPK to confirm your attendance.</p>
                                            <label for="npk" class="block text-sm font-medium text-gray-700">NPK</label>
                                            <input type="text" name="npk" id="npk" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter NPK">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">Confirm</button>
                                <button type="button" @click="showAttendanceModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
