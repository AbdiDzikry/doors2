@extends('layouts.master')

@section('title', 'Meeting List')

@section('content')
    @push('styles')
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Hide native date picker icon but keep functionality */
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }
    </style>
    @endpush

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: '{{ $activeTab }}' }">
            
            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Meeting Schedule</h1>
                    <p class="mt-2 text-sm text-gray-500">Manage and track all your scheduled room reservations.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                     <a href="{{ route('meeting.bookings.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all">
                        <i class="fas fa-plus mr-2"></i> New Booking
                    </a>
                </div>
            </div>

            <!-- Modern Tabs -->
            <div class="mb-8">
                <div class="border-b border-gray-200 overflow-x-auto no-scrollbar">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'meeting-list']) }}"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 group flex items-center"
                           :class="{ 'border-green-500 text-green-600': activeTab === 'meeting-list', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'meeting-list' }">
                           <i class="fas fa-list-ul mr-2" :class="{ 'text-green-500': activeTab === 'meeting-list', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'meeting-list' }"></i>
                            All Meetings
                        </a>
                        <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'my-meetings']) }}"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 group flex items-center"
                           :class="{ 'border-green-500 text-green-600': activeTab === 'my-meetings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'my-meetings' }">
                           <i class="fas fa-user-clock mr-2" :class="{ 'text-green-500': activeTab === 'my-meetings', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'my-meetings' }"></i>
                            My Meetings
                        </a>
                        <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'my-recurring-meetings']) }}"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 group flex items-center"
                           :class="{ 'border-green-500 text-green-600': activeTab === 'my-recurring-meetings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'my-recurring-meetings' }">
                           <i class="fas fa-sync-alt mr-2" :class="{ 'text-green-500': activeTab === 'my-recurring-meetings', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'my-recurring-meetings' }"></i>
                            Recurring
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Tab Content -->
            <div x-show="activeTab === 'meeting-list'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                
                <!-- Filter Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 mb-4">
                    <form action="{{ route('meeting.meeting-lists.index') }}" method="GET" x-data="{ 
                        filter: '{{ request('filter', 'day') }}',
                        updateFilter() {
                            if (this.filter !== 'custom') {
                                document.getElementById('start_date').value = '';
                                document.getElementById('end_date').value = '';
                            }
                        }
                    }">
                        <input type="hidden" name="tab" value="meeting-list">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <!-- Date Range -->
                            <div class="md:col-span-5 grid grid-cols-2 gap-3" x-show="filter === 'custom'" x-cloak x-transition>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Start Date</label>
                                    <div class="relative group">
                                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                                            class="block w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-3 pr-10 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-gray-700 cursor-pointer"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': '{{ request('start_date') }}'.length > 0 }">
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                                            <i class="fas fa-calendar-alt text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">End Date</label>
                                    <div class="relative group">
                                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                                            class="block w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-3 pr-10 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-gray-700 cursor-pointer"
                                              :class="{ 'border-green-500 ring-1 ring-green-500': '{{ request('end_date') }}'.length > 0 }">
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                                            <i class="fas fa-calendar-alt text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Filter & Search -->
                            <div class="md:col-span-4 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Period</label>
                                    <div class="relative" x-data="{ 
                                        open: false, 
                                        options: {
                                            'custom': 'Custom Range', 
                                            'day': 'Today', 
                                            'week': 'This Week', 
                                            'month': 'This Month',
                                            'year': 'This Year',
                                            'all': 'All Time'
                                        },
                                        get activeLabel() { return this.options[this.filter] } 
                                    }" @click.away="open = false">
                                        <!-- Hidden Native Input for Form Submission -->
                                        <input type="hidden" name="filter" x-model="filter">

                                        <!-- Custom Trigger Button -->
                                        <button type="button" @click="open = !open" 
                                            class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-3 pr-8 py-1.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-xs transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="activeLabel"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>

                                        <!-- Custom Dropdown List -->
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-xs border border-green-500/30"
                                            style="display: none;">
                                            <template x-for="(label, value) in options" :key="value">
                                                <div @click="filter = value; updateFilter(); open = false"
                                                     class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                     :class="{ 'text-green-900 bg-green-50': filter === value, 'text-gray-900 hover:bg-green-50 hover:text-green-700': filter !== value }">
                                                    <span class="block truncate font-medium" :class="{ 'font-semibold': filter === value, 'font-normal': filter !== value }" x-text="label"></span>
                                                    
                                                    <!-- Checkmark for selected item -->
                                                    <span x-show="filter === value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Search</label>
                                     <input type="text" name="search" value="{{ request('search') }}" placeholder="Topic, Room..." class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500 text-xs py-1.5 bg-gray-50">
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="md:col-span-3 flex gap-2">
                                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-1.5 px-3 text-xs rounded-lg shadow-sm transition-colors">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'meeting-list']) }}" class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors flex items-center justify-center">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @foreach (['topic' => 'Topic', 'room.name' => 'Room', 'start_time' => 'Date & Time', 'user.name' => 'Booked By'] as $column => $title)
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ $title }}
                                        </th>
                                    @endforeach
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-4"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($meetings as $meeting)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                        <!-- Topic -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ ucwords(strtolower($meeting->topic)) }}</div>
                                            @if($meeting->calculated_status === 'cancelled')
                                                <span class="text-xs text-red-500 italic mt-1 block">Cancelled</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Room -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                                {{ $meeting->room?->name ?? 'N/A' }}
                                            </div>
                                        </td>

                                        <!-- Date & Time -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('d-m-Y') }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Booked By -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-xs uppercase">
                                                    {{ substr($meeting->user?->name ?? 'U', 0, 2) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $meeting->user?->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                            $statusClasses = match($meeting->calculated_status) {
                                                    'scheduled' => 'bg-indigo-50 text-indigo-700',
                                                    'ongoing' => 'bg-blue-100 text-blue-800 ring-2 ring-blue-500 ring-opacity-50',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                                $statusLabel = ucfirst($meeting->calculated_status);
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                                @if($meeting->calculated_status === 'ongoing')
                                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5 animate-pulse"></span>
                                                @endif
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-3">
                                                <a href="{{ route('meeting.meeting-lists.show', $meeting->id) }}" class="text-gray-400 hover:text-green-600 transition-colors" title="View Details">
                                                    <i class="far fa-eye text-lg"></i>
                                                </a>

                                                @if($meeting->calculated_status !== 'cancelled')
                                                    @if(Auth::user()->hasAnyRole(['Admin', 'Super Admin']) || (Auth::id() === $meeting->user_id && !in_array($meeting->calculated_status, ['ongoing', 'completed'])))
                                                        <a href="{{ route('meeting.meeting-lists.edit', $meeting->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Meeting">
                                                            <i class="far fa-edit text-lg"></i>
                                                        </a>
                                                    @endif

                                                    @if(Auth::user()->hasAnyRole(['Admin', 'Super Admin']) || Auth::id() === $meeting->user_id)
                                                        <form action="{{ route('meeting.meeting-lists.destroy', $meeting->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this meeting?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Cancel Meeting">
                                                                <i class="far fa-trash-alt text-lg"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                                <p class="text-base font-medium text-gray-900">No meetings found</p>
                                                <p class="text-sm">Try adjusting your filters or search terms.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Other Tabs Content Placeholders (Keep existing includes/logic if complex, or simple placeholders if handled by Livewire elsewhere) -->
            <div x-show="activeTab === 'my-meetings'">
               @include('meetings.list.partials.my-meetings')
            </div>

            <div x-show="activeTab === 'my-recurring-meetings'">
                 @livewire('meeting.recurring-meetings-list')
            </div>

        </div>
    </div>
@endsection