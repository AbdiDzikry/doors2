<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
    @push('styles')
    <style>
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
    <div class="container mx-auto px-6 py-8">

        <form wire:submit.prevent="submitForm" method="POST">
            @csrf

            {{-- Error Display Block (Livewire handles this differently, but keeping for now) --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
                    <strong class="font-bold">Oops! Something went wrong.</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Room Information Section -->
            <div class="mb-8">
                <a href="{{ route('meeting.room-reservations.index') }}" class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 mb-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back to Room Selection
                </a>
                <h3 class="text-gray-800 text-3xl font-bold">Create Booking</h3>
                @if ($selectedRoom)
                    <div class="flex items-center">
                        <p class="text-sm text-gray-600">You are booking for room: <span class="font-semibold">{{ $selectedRoom->name }}</span></p>
                        @if ($current_meeting)
                            <span class="ml-4 px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">In Use until {{ $current_meeting->end_time->format('H:i T') }}</span>
                        @endif
                    </div>
                    <input type="hidden" name="room_id" value="{{ $selectedRoom->id }}">
                @else
                    <p class="text-sm text-gray-600">Select a room and fill in the meeting details.</p>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Meeting Details Card -->
                    <div class="bg-white rounded-lg shadow-md p-6" 
                         x-data="{ 
                             startTime: '{{ $start_time }}', 
                             duration: {{ $duration }},
                             calculateEndTime() {
                                 if (!this.startTime || !this.duration) return '';
                                 const startDate = new Date(this.startTime);
                                 const newEndDate = new Date(startDate.getTime() + this.duration * 60000);
                                 return newEndDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                             }
                         }">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">1. Meeting Details</h4>
                        
                        @if (!$selectedRoom)
                            <div class="mb-4">
                                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-1">Room <span class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ 
                                    open: false, 
                                    selected: @entangle('room_id'),
                                    get label() { 
                                        if (!this.selected) return '-- Select a Room --';
                                        // We need a way to look up the name. Since we can't easily pass the full array to JS without bloating, 
                                        // we'll rely on a hidden map or just update the UI text via Alpine when clicking an option.
                                        // Better yet, let's just use the selected text content logic if possible, or a simpler approach:
                                        // We will store the selected name in a separate Alpine var, initialized from PHP.
                                        return this.selectedName;
                                    },
                                    selectedName: '{{ $rooms->firstWhere('id', $room_id)?->name ?? '-- Select a Room --' }}'
                                }" @click.away="open = false">
                                    <button type="button" @click="open = !open" 
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="selectedName"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                        </span>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                        style="display: none;">
                                        @foreach ($rooms as $room)
                                            <div @click="selected = '{{ $room->id }}'; selectedName = '{{ addslashes($room->name) }}'; open = false; $wire.set('room_id', '{{ $room->id }}')"
                                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150 group"
                                                 :class="{ 'bg-green-50': selected == '{{ $room->id }}', 'hover:bg-green-50': selected != '{{ $room->id }}' }">
                                                <span class="block truncate font-medium" :class="{ 'font-bold text-green-900': selected == '{{ $room->id }}', 'text-gray-900 group-hover:text-green-700': selected != '{{ $room->id }}' }">
                                                    {{ $room->name }} (Cap: {{ $room->capacity }})
                                                </span>
                                                <span x-show="selected == '{{ $room->id }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                    <i class="fas fa-check text-xs"></i>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('room_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-1">Topic <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="topic" id="topic" 
                                class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-4 py-2.5 sm:text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 placeholder-gray-400 @error('topic') border-red-500 @enderror" 
                                placeholder="e.g., Quarterly Review">
                            @error('topic') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-4" x-data="{ 
                                date: '{{ \Carbon\Carbon::parse($start_time)->format('Y-m-d') }}',
                                hour: '{{ \Carbon\Carbon::parse($start_time)->format('H') }}',
                                minute: '{{ \Carbon\Carbon::parse($start_time)->format('i') }}',
                                updateStartTime() {
                                    const selectedMinute = Math.round(this.minute / 15) * 15;
                                    const formattedMinute = String(selectedMinute).padStart(2, '0');
                                    const newStartTime = `${this.date}T${this.hour}:${formattedMinute}`;
                                    @this.set('start_time', newStartTime);
                                    this.startTime = newStartTime; 
                                }
                            }">
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <input type="date" x-model="date" @change="updateStartTime()" id="start_date" 
                                        class="block w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 sm:text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 cursor-pointer @error('start_time') border-red-500 @enderror">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                </div>
                                @error('start_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                <label for="start_hour" class="block text-sm font-medium text-gray-700 mt-4 mb-1">Start Time <span class="text-red-500">*</span></label>
                                <div class="flex space-x-2">
                                    <!-- Hour Dropdown -->
                                    <div class="w-1/2 relative" x-data="{ open: false }" @click.away="open = false">
                                        <button type="button" @click="open = !open" 
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-8 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="hour"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">
                                            @foreach (range(7, 18) as $h)
                                                @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                <div @click="hour = '{{ $val }}'; updateStartTime(); open = false"
                                                     class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                     :class="{ 'text-green-900 bg-green-50': hour == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': hour != '{{ $val }}' }">
                                                    <span class="block truncate font-medium" :class="{ 'font-semibold': hour == '{{ $val }}' }">{{ $val }}</span>
                                                    <span x-show="hour == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Minute Dropdown -->
                                    <div class="w-1/2 relative" x-data="{ open: false }" @click.away="open = false">
                                        <button type="button" @click="open = !open" 
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-8 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="minute"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">
                                            @foreach (['00', '15', '30', '45'] as $m)
                                                <div @click="minute = '{{ $m }}'; updateStartTime(); open = false"
                                                     class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                     :class="{ 'text-green-900 bg-green-50': minute == '{{ $m }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': minute != '{{ $m }}' }">
                                                    <span class="block truncate font-medium" :class="{ 'font-semibold': minute == '{{ $m }}' }">{{ $m }}</span>
                                                    <span x-show="minute == '{{ $m }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration <span class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ 
                                    open: false, 
                                    duration: @entangle('duration').live,
                                    options: {
                                        '15': '15 Minutes', '30': '30 Minutes', '45': '45 Minutes',
                                        '60': '1 Hour', '75': '1 Hour 15 Minutes', '90': '1 Hour 30 Minutes', '105': '1 Hour 45 Minutes', '120': '2 Hours',
                                        '150': '2.5 Hours', '180': '3 Hours', '210': '3.5 Hours', '240': '4 Hours', '270': '4.5 Hours', '300': '5 Hours', '330': '5.5 Hours', '360': '6 Hours'
                                    },
                                    get label() { return this.options[this.duration] || 'Select Duration' }
                                }" @click.away="open = false">
                                    <button type="button" @click="open = !open" 
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="label"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                        </span>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                        style="display: none;">
                                        
                                        <!-- Rapat Cepat -->
                                        <div class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50">Rapat Cepat</div>
                                        @foreach (['15' => '15 Minutes', '30' => '30 Minutes', '45' => '45 Minutes'] as $val => $text)
                                            <div @click="duration = '{{ $val }}'; open = false" class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150" :class="{ 'text-green-900 bg-green-50': duration == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': duration != '{{ $val }}' }">
                                                <span class="block truncate font-medium" :class="{ 'font-semibold': duration == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="duration == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600"><i class="fas fa-check text-xs"></i></span>
                                            </div>
                                        @endforeach

                                        <!-- Rapat Standar -->
                                        <div class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-t border-gray-100">Rapat Standar</div>
                                        @foreach (['60' => '1 Hour', '75' => '1 Hour 15 Minutes', '90' => '1 Hour 30 Minutes', '105' => '1 Hour 45 Minutes', '120' => '2 Hours'] as $val => $text)
                                            <div @click="duration = '{{ $val }}'; open = false" class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150" :class="{ 'text-green-900 bg-green-50': duration == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': duration != '{{ $val }}' }">
                                                <span class="block truncate font-medium" :class="{ 'font-semibold': duration == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="duration == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600"><i class="fas fa-check text-xs"></i></span>
                                            </div>
                                        @endforeach

                                        <!-- Sesi Panjang -->
                                        <div class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-t border-gray-100">Sesi Panjang</div>
                                        @foreach (['150' => '2.5 Hours', '180' => '3 Hours', '210' => '3.5 Hours', '240' => '4 Hours', '270' => '4.5 Hours', '300' => '5 Hours', '330' => '5.5 Hours', '360' => '6 Hours'] as $val => $text)
                                            <div @click="duration = '{{ $val }}'; open = false" class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150" :class="{ 'text-green-900 bg-green-50': duration == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': duration != '{{ $val }}' }">
                                                <span class="block truncate font-medium" :class="{ 'font-semibold': duration == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="duration == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600"><i class="fas fa-check text-xs"></i></span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Suggested End Time: <span x-text="calculateEndTime()" class="font-semibold"></span>
                                </div>
                                @error('duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="priority_guest_id" class="block text-sm font-medium text-gray-700 mb-1">Priority Guest (Optional)</label>
                            <div class="relative" x-data="{ 
                                open: false, 
                                selected: @entangle('priority_guest_id'),
                                selectedName: '{{ $priorityGuests->firstWhere('id', $priority_guest_id) ? ($priorityGuests->firstWhere('id', $priority_guest_id)->name . ' - ' . $priorityGuests->firstWhere('id', $priority_guest_id)->title) : '-- No Priority Guest --' }}'
                            }" @click.away="open = false">
                                <button type="button" @click="open = !open" 
                                    class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                    :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                    <span class="block truncate" x-text="selectedName"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                    </span>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                    style="display: none;">
                                    <div @click="selected = ''; selectedName = '-- No Priority Guest --'; open = false; $wire.set('priority_guest_id', null)"
                                         class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                         :class="{ 'text-green-900 bg-green-50': !selected, 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected }">
                                        <span class="block truncate font-medium" :class="{ 'font-semibold': !selected }">-- No Priority Guest --</span>
                                        <span x-show="!selected" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                            <i class="fas fa-check text-xs"></i>
                                        </span>
                                    </div>
                                    @foreach ($priorityGuests as $guest)
                                        <div @click="selected = '{{ $guest->id }}'; selectedName = '{{ addslashes($guest->name . ' - ' . $guest->title) }}'; open = false; $wire.set('priority_guest_id', '{{ $guest->id }}')"
                                             class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                             :class="{ 'text-green-900 bg-green-50': selected == '{{ $guest->id }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $guest->id }}' }">
                                            <span class="block truncate font-medium" :class="{ 'font-semibold': selected == '{{ $guest->id }}' }">{{ $guest->name }} - {{ $guest->title }}</span>
                                            <span x-show="selected == '{{ $guest->id }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                <i class="fas fa-check text-xs"></i>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('priority_guest_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Recurring Meeting Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="recurring" class="form-checkbox h-5 w-5 text-green-600 rounded focus:ring-green-500">
                            <span class="ml-3 text-lg font-semibold text-gray-800">Make this a Recurring Meeting</span>
                        </label>

                        <div x-show="$wire.recurring" x-transition class="mt-4 pt-4 border-t">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="frequency" class="block text-sm font-medium text-gray-700 mb-1">Repeat</label>
                                    <div class="relative" x-data="{ 
                                        open: false, 
                                        selected: @entangle('frequency'),
                                        options: { 'daily': 'Daily', 'weekly': 'Weekly', 'monthly': 'Monthly' }, 
                                        get label() { return this.options[this.selected] || 'Daily' }
                                    }" @click.away="open = false">
                                        <button type="button" @click="open = !open" 
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="label"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">
                                            @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $val => $text)
                                                <div @click="selected = '{{ $val }}'; open = false"
                                                     class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                     :class="{ 'text-green-900 bg-green-50': selected == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $val }}' }">
                                                    <span class="block truncate font-medium" :class="{ 'font-semibold': selected == '{{ $val }}' }">{{ $text }}</span>
                                                    <span x-show="selected == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                    <div class="relative group">
                                        <input type="date" wire:model="ends_at" id="ends_at" 
                                            class="block w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 sm:text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 cursor-pointer">
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Participants Card -->
                    <div class="bg-white rounded-lg shadow-md" x-data="{ activeTab: 'internal' }">
                        <div class="p-6">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">2. Add Participants</h4>
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                                    <button type="button" @click="activeTab = 'internal'" :class="{'border-green-500 text-green-600': activeTab === 'internal', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'internal'}" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                        Internal
                                    </button>
                                    <button type="button" @click="activeTab = 'external'" :class="{'border-green-500 text-green-600': activeTab === 'external', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'external'}" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                        External
                                    </button>
                                </nav>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-b-lg">
                            <div x-show="activeTab === 'internal'">
                                @livewire('meeting.search-internal-participants', ['initialParticipants' => $internalParticipants])
                            </div>
                            <div x-show="activeTab === 'external'" style="display: none;">
                                @livewire('meeting.search-external-participants', ['initialParticipants' => $externalParticipants])
                            </div>
                        </div>
                    </div>

                    <!-- Pantry Card -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">3. Add Pantry Orders (Optional)</h4>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-b-lg">
                            @livewire('meeting.select-pantry-items', ['initialPantryItems' => $pantryOrders])
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Room/Booker Info Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        @if ($selectedRoom)
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Room Info</h4>
                            <img class="w-full h-40 object-cover rounded-md mb-4" src="{{ $selectedRoom->image_path ? route('master.rooms.image', ['filename' => basename($selectedRoom->image_path)]) : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" style="background:#f0f0f0;font-family:sans-serif;font-size:30px;color:#888;text-anchor:middle"><text x="50%" y="50%" dominant-baseline="middle">No Image for ' . htmlspecialchars($selectedRoom->name) . '</text></svg>' }}" alt="{{ $selectedRoom->name }}">
                            <p class="font-bold text-lg">{{ $selectedRoom->name }}</p>
                            <p class="text-sm text-gray-600">Capacity: {{ $selectedRoom->capacity }} people</p>
                            @php
                                $facilities = !empty($selectedRoom->facilities) ? array_map('trim', explode(',', $selectedRoom->facilities)) : [];
                            @endphp
                            @if (!empty($facilities))
                                <div class="mt-2">
                                    <h5 class="text-sm font-semibold text-gray-700">Facilities:</h5>
                                    <div class="flex flex-wrap items-center mt-1 gap-x-4 gap-y-2">
                                        @foreach ($facilities as $facility)
                                            <span class="text-sm text-gray-600">{{ $facility }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Status Chip --}}
                            <div class="mt-4">
                                @if ($selectedRoom->status === 'under_maintenance')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Under Maintenance</span>
                                @elseif ($current_meeting)
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">In Use until {{ $current_meeting->end_time?->format('H:i T') ?? 'N/A' }}</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Available</span>
                                @endif
                            </div>
                        @else
                             <h4 class="text-xl font-semibold text-gray-800 mb-4">Room Info</h4>
                             <p class="text-gray-600">Please select a room from the dropdown in the "Meeting Details" section.</p>
                        @endif
                        
                        <div class="border-t mt-4 pt-4">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Booker Info</h4>
                            <p class="text-sm text-gray-700"><span class="font-medium">Name:</span> {{ Auth::user()->name }}</p>
                            <p class="text-sm text-gray-700"><span class="font-medium">NPK:</span> {{ Auth::user()->npk ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-700"><span class="font-medium">Department:</span> {{ Auth::user()->department ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Room Schedule Card -->
                    @if($room_id)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-calendar-check mr-2 text-green-600"></i> Room Schedule
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Upcoming meetings in this room</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($this->roomMeetings as $meeting)
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                                    <div>{{ \Carbon\Carbon::parse($meeting->start_time)->format('d M') }}</div>
                                                    <div class="font-medium text-gray-900">
                                                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-xs text-gray-900">
                                                    <div class="truncate max-w-[150px]" title="{{ $meeting->topic }}">
                                                        {{ $meeting->topic }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                                    {{ $meeting->user->name }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $meeting->calculated_status === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                        {{ $meeting->calculated_status === 'ongoing' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $meeting->calculated_status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                                        {{ $meeting->calculated_status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                        {{ ucfirst($meeting->calculated_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                                    No upcoming meetings scheduled.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif


                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <button type="button" wire:click.prevent="submitForm" wire:loading.attr="disabled" wire:target="submitForm" class="inline-flex items-center justify-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 text-lg relative">
                    <span wire:loading.remove wire:target="submitForm">Book Meeting</span>
                    <span wire:loading wire:target="submitForm" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</main>