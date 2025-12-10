<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
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
                                <label for="room_id" class="block text-sm font-medium text-gray-700">Room <span class="text-red-500">*</span></label>
                                <select wire:model="room_id" id="room_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('room_id') border-red-500 @enderror">
                                    <option value="">-- Select a Room --</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }} (Capacity: {{ $room->capacity }})</option>
                                    @endforeach
                                </select>
                                @error('room_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="topic" class="block text-sm font-medium text-gray-700">Topic <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="topic" id="topic" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('topic') border-red-500 @enderror" placeholder="e.g., Quarterly Review">
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
                                    this.startTime = newStartTime; // Update Alpine's startTime for calculation
                                }
                            }">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date <span class="text-red-500">*</span></label>
                                <input type="date" x-model="date" @change="updateStartTime()" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('start_time') border-red-500 @enderror">
                                @error('start_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                <label for="start_hour" class="block text-sm font-medium text-gray-700 mt-2">Start Time <span class="text-red-500">*</span></label>
                                <div class="flex space-x-2 mt-1">
                                    <select x-model="hour" @change="updateStartTime()" id="start_hour" class="block w-1/2 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('start_time') border-red-500 @enderror">
                                        @foreach (range(7, 18) as $h)
                                            <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endforeach
                                    </select>
                                    <select x-model="minute" @change="updateStartTime()" id="start_minute" class="block w-1/2 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('start_time') border-red-500 @enderror">
                                        <option value="00">00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="duration" class="block text-sm font-medium text-gray-700">Duration <span class="text-red-500">*</span></label>
                                <select wire:model.live="duration" x-model="duration" id="duration" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('duration') border-red-500 @enderror">
                                    <optgroup label="Rapat Cepat">
                                        <option value="15">15 Minutes</option>
                                        <option value="30">30 Minutes</option>
                                        <option value="45">45 Minutes</option>
                                    </optgroup>
                                    <optgroup label="Rapat Standar">
                                        <option value="60">1 Hour</option>
                                        <option value="75">1 Hour 15 Minutes</option>
                                        <option value="90">1 Hour 30 Minutes</option>
                                        <option value="105">1 Hour 45 Minutes</option>
                                        <option value="120">2 Hours</option>
                                    </optgroup>
                                    <optgroup label="Sesi Panjang">
                                        <option value="150">2.5 Hours</option>
                                        <option value="180">3 Hours</option>
                                        <option value="210">3.5 Hours</option>
                                        <option value="240">4 Hours</option>
                                        <option value="270">4.5 Hours</option>
                                        <option value="300">5 Hours</option>
                                        <option value="330">5.5 Hours</option>
                                        <option value="360">6 Hours</option>
                                    </optgroup>
                                </select>
                                <div class="mt-1 text-xs text-gray-500">
                                    Suggested End Time: <span x-text="calculateEndTime()" class="font-semibold"></span>
                                </div>
                                @error('duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="priority_guest_id" class="block text-sm font-medium text-gray-700">Priority Guest (Optional)</label>
                            <select wire:model="priority_guest_id" id="priority_guest_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('priority_guest_id') border-red-500 @enderror">
                                <option value="">-- No Priority Guest --</option>
                                @foreach ($priorityGuests as $guest)
                                    <option value="{{ $guest->id }}">{{ $guest->name }} - {{ $guest->title }}</option>
                                @endforeach
                            </select>
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
                                    <label for="frequency" class="block text-sm font-medium text-gray-700">Repeat</label>
                                    <select wire:model="frequency" id="frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="ends_at" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input type="date" wire:model="ends_at" id="ends_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
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
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 italic">
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