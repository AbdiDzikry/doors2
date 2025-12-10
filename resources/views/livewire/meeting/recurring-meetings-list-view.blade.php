<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">My Recurring Meetings</h1>
            <p class="mt-1 text-sm text-gray-600">
                A list view of your recurring meetings. Confirm or cancel upcoming occurrences here.
            </p>
        </div>
        <a href="{{ route('meeting.bookings.create') }}" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-plus mr-2"></i>
            Create New Meeting
        </a>
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