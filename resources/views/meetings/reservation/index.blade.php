@extends('layouts.master')

@section('title', 'Room Reservation')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Hero Section -->
        <!-- Search & Filter Bar -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2 mb-2">
            <div class="bg-white rounded-2xl shadow-md p-3 border border-gray-100">
                <form action="{{ route('meeting.room-reservations.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-5">
                            <label for="search" class="block text-xs font-semibold text-gray-700 mb-0.5">Search Room</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-xs"></i>
                                </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Try 'Conference Room'..." class="block w-full pl-9 pr-3 py-1.5 border-gray-200 rounded-xl focus:ring-green-500 focus:border-green-500 shadow-sm text-xs bg-gray-50/50">
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label for="status" class="block text-xs font-semibold text-gray-700 mb-0.5">Availability</label>
                            <div class="relative" x-data="{ 
                                    open: false, 
                                    options: {
                                        '': 'All Statuses',
                                        'available': 'Available Now',
                                        'in_use': 'In Use', 
                                        'under_maintenance': 'Maintenance'
                                    },
                                    filter: '{{ request('status') }}',
                                    get activeLabel() { return this.options[this.filter] || 'All Statuses' } 
                                }" @click.away="open = false">
                                    <input type="hidden" name="status" x-model="filter">
                                    <button type="button" @click="open = !open" 
                                        class="relative w-full bg-gray-50/50 border border-gray-200 rounded-xl shadow-sm pl-3 pr-8 py-1.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-xs transition-all duration-200"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="activeLabel"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                        </span>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-xs border border-green-500/30"
                                        style="display: none;">
                                        <template x-for="(label, value) in options" :key="value">
                                            <div @click="filter = value; open = false;"
                                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                 :class="{ 'text-green-900 bg-green-50': filter == value, 'text-gray-900 hover:bg-green-50 hover:text-green-700': filter != value }">
                                                <span class="block truncate font-medium" :class="{ 'font-semibold': filter == value, 'font-normal': filter != value }" x-text="label"></span>
                                                <span x-show="filter == value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                    <i class="fas fa-check text-xs"></i>
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                        </div>
                        <div class="md:col-span-3 flex space-x-2">
                            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-3 rounded-xl shadow-lg hover:shadow-green-500/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center text-xs">
                                <i class="fas fa-search mr-1.5"></i> Find Room
                            </button>
                            @if(request()->anyFilled(['search', 'status']))
                            <a href="{{ route('meeting.room-reservations.index') }}" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold transition-colors duration-200 flex items-center justify-center text-xs">
                                <i class="fas fa-undo"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Grid -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($rooms as $room)
                    <div class="group relative bg-white rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
                        <!-- Image Section -->
                        <div class="relative h-48 overflow-hidden">
                            <img class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 will-change-transform" 
                                 loading="lazy"
                                 decoding="async"
                                 src="{{ $room->image_path ? route('master.rooms.image', ['filename' => basename($room->image_path)]) : 'https://placehold.co/600x400/e2e8f0/94a3b8?text=No+Image' }}" 
                                 alt="{{ $room->name }}">
                            
                            <!-- Status Overlay -->
                            <div class="absolute top-4 right-4" id="room-status-{{ $room->id }}">
                                @if ($room->status === 'under_maintenance')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-900/80 text-white backdrop-blur-sm border border-gray-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span> Maintenance
                                    </span>
                                @elseif ($room->is_in_use)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/90 text-orange-700 backdrop-blur-sm shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span> In Use
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/90 text-green-700 backdrop-blur-sm shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 shadow-[0_0_8px_theme('colors.green.400')]"></span> Available
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Overlay Gradient on Hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>

                        <!-- Content Section -->
                        <div class="p-4 flex-grow flex flex-col">
                            <div class="mb-3">
                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-700 transition-colors">
                                    {{ $room->name }}
                                </h3>
                                <div class="flex items-center mt-1.5 text-gray-500 text-xs">
                                    <i class="fas fa-users w-4 text-center mr-1.5 text-green-500/70"></i>
                                    <span>Capacity: <strong>{{ $room->capacity }}</strong></span>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-xs mb-4 flex-grow line-clamp-2">
                                {{ $room->description ?: 'No description available for this room.' }}
                            </p>

                            <!-- Facilities -->
                            @php
                                $facilities = !empty($room->facilities) ? array_map('trim', explode(',', $room->facilities)) : [];
                            @endphp
                            @if (!empty($facilities))
                                <div class="mb-6 flex flex-wrap gap-2">
                                    @foreach (array_slice($facilities, 0, 4) as $facility)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ $facility }}
                                        </span>
                                    @endforeach
                                    @if(count($facilities) > 4)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-50 text-gray-400">
                                            +{{ count($facilities) - 4 }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Action Button -->
                            <div class="mt-auto" id="room-btn-{{ $room->id }}">
                                @if ($room->status !== 'under_maintenance')
                                    <a href="{{ route('meeting.bookings.create', ['room_id' => $room->id]) }}" class="block w-full text-center bg-gray-50 hover:bg-green-600 text-gray-700 hover:text-white font-bold py-2.5 px-4 rounded-xl border border-gray-200 hover:border-transparent transition-all duration-300 group-hover:shadow-md text-sm after:absolute after:inset-0 after:z-10">
                                        Book Now <i class="fas fa-arrow-right ml-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1"></i>
                                    </a>
                                @else
                                    <button disabled class="block w-full text-center bg-gray-100 text-gray-400 font-bold py-2.5 px-4 rounded-xl cursor-not-allowed text-sm">
                                        Unavailable
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-20 px-4">
                        <div class="bg-gray-100 rounded-full p-6 mb-4">
                            <i class="fas fa-search h-12 w-12 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No rooms found</h3>
                        <p class="mt-1 text-gray-500">Try adjusting your search or filters to find what you're looking for.</p>
                        <a href="{{ route('meeting.room-reservations.index') }}" class="mt-6 text-green-600 hover:text-green-700 font-medium">
                            Clear all filters
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @push('scripts')
    <script type="module">
        console.log('Listening for room updates...');
        Echo.channel('rooms')
            .listen('RoomStatusUpdated', (e) => {
                console.log('Room update received:', e);
                const statusContainer = document.getElementById(`room-status-${e.roomId}`);
                if (statusContainer) {
                    let html = '';
                    if (e.status === 'under_maintenance') {
                        html = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-900/80 text-white backdrop-blur-sm border border-gray-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span> Maintenance
                                </span>`;
                    } else if (e.status === 'in_use') {
                        html = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/90 text-orange-700 backdrop-blur-sm shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span> In Use
                                </span>`;
                    } else {
                        html = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/90 text-green-700 backdrop-blur-sm shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 shadow-[0_0_8px_theme('colors.green.400')]"></span> Available
                                </span>`;
                    }
                    statusContainer.innerHTML = html;
                    
                    // Also update the button state if needed
                    const btnContainer = document.getElementById(`room-btn-${e.roomId}`);
                    if (btnContainer) {
                        if (e.status === 'under_maintenance') {
                             btnContainer.innerHTML = `<button disabled class="block w-full text-center bg-gray-100 text-gray-400 font-bold py-2.5 px-4 rounded-xl cursor-not-allowed text-sm">Unavailable</button>`;
                        } else {
                            // If it was maintenance and now is not, we might need to restore the link. 
                            // However, simplified logic: just reload or simple check.
                            // For In Use vs Available, the button is "Book Now" for both (usually).
                            // Wait, the view disables button only for Maintenance.
                            // So we only need to handle maintenance toggle.
                             if (statusContainer.innerHTML.includes('Maintenance') && e.status !== 'under_maintenance') {
                                 // Ideally we reconstruct the link, but it's simpler to just let them refresh for maintenance changes strictly?
                                 // Or reconstruct it:
                                 btnContainer.innerHTML = `<a href="/meeting/bookings/create?room_id=${e.roomId}" class="block w-full text-center bg-gray-50 hover:bg-green-600 text-gray-700 hover:text-white font-bold py-2.5 px-4 rounded-xl border border-gray-200 hover:border-transparent transition-all duration-300 group-hover:shadow-md text-sm after:absolute after:inset-0 after:z-10">
                                        Book Now <i class="fas fa-arrow-right ml-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1"></i>
                                    </a>`;
                             }
                        }
                    }
                }
            });
    </script>
    @endpush
@endsection

