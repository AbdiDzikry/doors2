@extends('layouts.master')

@section('title', 'Room Reservation')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Hero Section -->
        <!-- Search & Filter Bar -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 mb-4">
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                <form action="{{ route('meeting.room-reservations.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-5">
                            <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Room</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Try 'Conference Room' or 'Projector'..." class="block w-full pl-10 pr-3 py-3 border-gray-200 rounded-xl focus:ring-green-500 focus:border-green-500 shadow-sm text-sm bg-gray-50/50">
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Availability</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-filter text-gray-400"></i>
                                </div>
                                <select name="status" id="status" class="block w-full pl-10 pr-10 py-3 border-gray-200 rounded-xl focus:ring-green-500 focus:border-green-500 shadow-sm text-sm bg-gray-50/50 appearance-none">
                                    <option value="">All Statuses</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available Now</option>
                                    <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                                    <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="md:col-span-3 flex space-x-2">
                            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-green-500/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center">
                                <i class="fas fa-search mr-2"></i> Find Room
                            </button>
                            @if(request()->anyFilled(['search', 'status']))
                            <a href="{{ route('meeting.room-reservations.index') }}" class="px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold transition-colors duration-200 flex items-center justify-center">
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
                        <div class="relative h-64 overflow-hidden">
                            <img class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 will-change-transform" 
                                 loading="lazy"
                                 decoding="async"
                                 src="{{ $room->image_path ? route('master.rooms.image', ['filename' => basename($room->image_path)]) : 'https://placehold.co/600x400/e2e8f0/94a3b8?text=No+Image' }}" 
                                 alt="{{ $room->name }}">
                            
                            <!-- Status Overlay -->
                            <div class="absolute top-4 right-4">
                                @if ($room->status === 'under_maintenance')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-900/80 text-white backdrop-blur-sm border border-gray-700">
                                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2 animate-pulse"></span> Maintenance
                                    </span>
                                @elseif ($room->is_in_use)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/90 text-orange-700 backdrop-blur-sm shadow-sm">
                                        <span class="w-2 h-2 rounded-full bg-orange-500 mr-2"></span> In Use
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/90 text-green-700 backdrop-blur-sm shadow-sm">
                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2 shadow-[0_0_8px_theme('colors.green.400')]"></span> Available
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Overlay Gradient on Hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>

                        <!-- Content Section -->
                        <div class="p-6 flex-grow flex flex-col">
                            <div class="mb-4">
                                <h3 class="text-2xl font-bold text-gray-800 group-hover:text-green-700 transition-colors">
                                    {{ $room->name }}
                                </h3>
                                <div class="flex items-center mt-2 text-gray-500 text-sm">
                                    <i class="fas fa-users w-5 text-center mr-2 text-green-500/70"></i>
                                    <span>Capacity: <strong>{{ $room->capacity }}</strong> People</span>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-6 flex-grow line-clamp-2">
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
                            <div class="mt-auto">
                                @if ($room->status !== 'under_maintenance')
                                    <a href="{{ route('meeting.bookings.create', ['room_id' => $room->id]) }}" class="block w-full text-center bg-gray-50 hover:bg-green-600 text-gray-700 hover:text-white font-bold py-3 px-4 rounded-xl border border-gray-200 hover:border-transparent transition-all duration-300 group-hover:shadow-md">
                                        Book Now <i class="fas fa-arrow-right ml-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1"></i>
                                    </a>
                                @else
                                    <button disabled class="block w-full text-center bg-gray-100 text-gray-400 font-bold py-3 px-4 rounded-xl cursor-not-allowed">
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
@endsection

