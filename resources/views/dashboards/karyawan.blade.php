@extends('layouts.master')
@section('title', 'My Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Banner -->
        <div class="bg-white rounded-xl shadow-lg border-l-4 border-green-500 p-6 md:p-8 mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
                    Welcome Back, {{ Auth::user()->name }}!
                </h1>
                <p class="text-lg text-gray-500">
                    Here's a quick overview of your schedule for today, <span class="font-medium text-green-600">{{ now()->format('l, d M Y') }}</span>.
                </p>
            </div>
            <div class="hidden lg:block relative">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-green-50 rounded-full opacity-50 z-0"></div>
                <i class="far fa-smile-beam text-6xl text-green-500 relative z-10"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Priority Items -->
            <div class="lg:col-span-1 space-y-8">
                
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-bolt text-green-500 mr-2"></i> Quick Actions
                        </h2>
                    </div>
                    <div class="p-5 grid grid-cols-1 gap-3">
                        <a href="{{ route('meeting.bookings.create') }}" class="w-full group flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-100 hover:bg-green-100 hover:border-green-200 transition-all duration-200 cursor-pointer text-green-900">
                            <div class="flex items-center">
                                <span class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center mr-3 shadow-sm group-hover:scale-110 transition-transform">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span class="font-semibold">Book a Room</span>
                            </div>
                            <i class="fas fa-chevron-right text-green-300 group-hover:text-green-500"></i>
                        </a>

                        <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'my-meetings']) }}" class="w-full group flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 cursor-pointer text-gray-700">
                            <div class="flex items-center">
                                <span class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center mr-3 group-hover:bg-white border border-transparent group-hover:border-gray-200 transition-colors">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                                <span class="font-medium">My Meetings</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 group-hover:text-gray-400"></i>
                        </a>
                    </div>
                </div>

                <!-- Next Meeting Spotlight -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-green-100 rounded-bl-full opacity-50 z-0"></div>
                    
                    <div class="p-6 relative z-10">
                        <h2 class="text-sm font-bold text-green-600 uppercase tracking-wider mb-4 flex items-center">
                            <i class="fas fa-star mr-2"></i> Up Next
                        </h2>

                        @if ($nextMeeting)
                            <div class="flex flex-col h-full justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2 leading-tight">
                                        {{ ucwords(strtolower($nextMeeting->topic)) }}
                                    </h3>
                                    
                                    <div class="space-y-3 mt-4">
                                        <div class="flex items-start text-sm text-gray-600">
                                            <i class="far fa-clock w-5 mt-0.5 text-gray-400"></i>
                                            <div>
                                                <span class="font-semibold text-gray-900">{{ $nextMeeting->start_time->format('H:i') }} - {{ $nextMeeting->end_time->format('H:i') }}</span>
                                                <div class="text-xs text-gray-500">{{ $nextMeeting->start_time->format('l, d M Y') }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                                            <span class="bg-gray-100 text-gray-800 py-0.5 px-2 rounded text-xs font-semibold border border-gray-200">
                                                {{ $nextMeeting->room?->name ?? 'N/A' }}
                                            </span>
                                        </div>

                                        @php
                                            $statusClasses = match($nextMeeting->calculated_status) {
                                                'scheduled' => 'bg-blue-100 text-blue-800',
                                                'ongoing' => 'bg-green-100 text-green-800 ring-2 ring-green-500 ring-opacity-50',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <div class="flex items-center text-sm mt-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                                @if($nextMeeting->calculated_status === 'ongoing')
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                                @endif
                                                {{ ucfirst($nextMeeting->calculated_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-100">
                                    <a href="{{ route('meeting.meeting-lists.show', $nextMeeting) }}" class="flex items-center justify-center w-full px-4 py-2 bg-green-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none transition-colors">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 mb-4">
                                    <i class="fas fa-mug-hot text-2xl text-green-300"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">No Upcoming Meetings</h3>
                                <p class="text-sm text-gray-500 mt-1">You're all clear for now!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Meeting Schedule Table -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden flex flex-col h-full">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h2 class="text-lg font-bold text-gray-900">Upcoming Schedule</h2>
                        <a href="{{ route('meeting.meeting-lists.index') }}" class="text-sm font-medium text-green-600 hover:text-green-800 transition-colors">
                            View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                    
                    <div class="flex-1 overflow-x-auto">
                        @if($otherMeetings->isNotEmpty())
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Topic</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Action</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($otherMeetings as $meeting)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $meeting->start_time->format('d-m-Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">{{ ucwords(strtolower($meeting->topic)) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600">
                                                <i class="fas fa-map-marker-alt text-gray-400 mr-1.5"></i> {{ $meeting->room?->name ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             @php
                                                $statusClasses = match($meeting->calculated_status) {
                                                    'scheduled' => 'bg-blue-100 text-blue-800',
                                                    'ongoing' => 'bg-green-100 text-green-800',
                                                    'completed' => 'bg-gray-100 text-gray-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClasses }}">
                                                {{ ucfirst($meeting->calculated_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('meeting.meeting-lists.show', $meeting) }}" class="text-gray-400 hover:text-green-600 transition-colors">
                                                <i class="far fa-eye text-lg"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 text-center h-full">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="far fa-calendar-check text-2xl text-gray-300"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">All Caught Up</h3>
                                <p class="text-sm text-gray-500 max-w-xs mx-auto">You don't have any other upcoming meetings scheduled at the moment.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection