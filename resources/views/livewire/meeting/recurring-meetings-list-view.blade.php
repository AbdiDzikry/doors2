<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">My Recurring Meetings</h1>
            <p class="mt-1 text-sm text-gray-600">
                A list view of your recurring meetings. Filter by date to see upcoming occurrences.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-end sm:items-center gap-4 w-full sm:w-auto">
             <!-- Date Inputs (Custom) -->
             <div class="flex gap-2" x-show="$wire.filter === 'custom'" x-transition x-cloak>
                <div>
                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Start</label>
                     <input type="date" wire:model.live="startDate" class="block w-full bg-white border border-gray-300 rounded-md shadow-sm text-xs py-1.5 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">End</label>
                     <input type="date" wire:model.live="endDate" class="block w-full bg-white border border-gray-300 rounded-md shadow-sm text-xs py-1.5 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Filter Dropdown -->
             <div class="relative w-full sm:w-auto" x-data="{ open: false }">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5 sm:hidden">Period</label>
                <button type="button" @click="open = !open" @click.away="open = false"
                    class="relative w-full sm:w-40 bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <span class="block truncate">
                        @if($filter == 'day') Today
                        @elseif($filter == 'week') This Week
                        @elseif($filter == 'month') This Month
                        @elseif($filter == 'year') This Year
                        @elseif($filter == 'all') All Time
                        @elseif($filter == 'custom') Custom Range
                        @endif
                    </span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                    style="display: none;">
                    
                    @foreach(['day' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time', 'custom' => 'Custom Range'] as $val => $label)
                        <div wire:click="$set('filter', '{{ $val }}')" @click="open = false"
                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-green-50 hover:text-green-900 {{ $filter === $val ? 'bg-green-50 text-green-900 font-semibold' : 'text-gray-900' }}">
                            <span class="block truncate">{{ $label }}</span>
                            @if($filter === $val)
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    @forelse ($recurringMeetings as $series)
        <div x-data="{ isOpen: false }" class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-4 border-b border-gray-200 cursor-pointer flex justify-between items-center" x-on:click="isOpen = !isOpen">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $series->topic }}</h2>
                    <p class="text-sm text-gray-600">Repeats {{ $series->recurring_type }} until {{ $series->recurring_end_date->format('d M Y') }} in {{ $series->room->name }}</p>
                </div>
                <svg :class="{ 'rotate-180': isOpen }" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <div x-show="isOpen" x-collapse>
                <div class="divide-y divide-gray-200">
                    @foreach ($series->children->sortBy('start_time') as $meeting)
                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <p class="text-gray-900 font-medium">{{ $meeting->start_time->format('d M Y, H:i') }} - {{ $meeting->end_time->format('H:i') }}</p>
                                <p class="text-sm text-gray-600">Room: {{ $meeting->room->name }}</p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($meeting->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($meeting->confirmation_status === 'pending_confirmation') bg-amber-100 text-amber-800
                                    @else bg-green-100 text-green-800 @endif">
                                    @if($meeting->status === 'cancelled')
                                        Cancelled
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $meeting->confirmation_status)) }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                @if ($meeting->confirmation_status === 'pending_confirmation' && $meeting->status !== 'cancelled')
                                    <button wire:click="confirmMeeting({{ $meeting->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Confirm
                                    </button>
                                    <button wire:click="cancelMeeting({{ $meeting->id }})" class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </button>
                                @else
                                    <span class="text-sm text-gray-500">Action taken</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
            <p>No recurring meetings found. Start by creating a new one!</p>
            <a href="{{ route('meeting.bookings.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Create New Meeting
            </a>
        </div>
    @endforelse

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-lg shadow-md mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Legend</h3>
        <div class="flex items-center space-x-4 text-sm">
            <div class="flex items-center">
                <span class="w-4 h-4 rounded-full bg-green-500 mr-2"></span>
                <span>Confirmed</span>
            </div>
            <div class="flex items-center">
                <span class="w-4 h-4 rounded-full bg-amber-500 mr-2"></span>
                <span>Pending Confirmation</span>
            </div>
            <div class="flex items-center">
                <span class="w-4 h-4 rounded-full bg-red-500 mr-2"></span>
                <span>Cancelled</span>
            </div>
        </div>
    </div>
</div>