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

            {{-- Enhanced Error Display Block (Floating) --}}
            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                    x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed top-24 right-4 z-50 w-full max-w-sm overflow-hidden bg-white rounded-lg shadow-lg border-l-4 border-red-500">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <h3 class="text-sm font-medium text-gray-900">
                                    Pemesanan Gagal
                                </h3>
                                <div class="mt-1 text-sm text-gray-500">
                                    <p>Mohon periksa kembali input Anda:</p>
                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button @click="show = false"
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Tutup</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Room Information Section -->
            <div class="mb-8">
                <a href="{{ route('meeting.room-reservations.index') }}"
                    class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 mb-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Kembali ke Pemilihan Ruangan
                </a>
                <h3 class="text-gray-800 text-3xl font-bold">
                    {{ $isEditMode ? 'Edit Pertemuan' : 'Buat Pesanan Baru' }}
                </h3>
                @if ($selectedRoom)
                    <div class="flex items-center">
                        <p class="text-sm text-gray-600">Anda memesan ruangan: <span
                                class="font-semibold">{{ $selectedRoom->name }}</span></p>
                    </div>
                    <input type="hidden" name="room_id" value="{{ $selectedRoom->id }}">
                @else
                    <p class="text-sm text-gray-600">Pilih ruangan dan isi detail pertemuan.</p>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Meeting Details Card -->
                    <div class="bg-white rounded-lg shadow-md p-6" x-data="{ 
                             startTime: '{{ $start_time }}', 
                             duration: {{ $duration }},
                             calculateEndTime() {
                                 if (!this.startTime || !this.duration) return '';
                                 const startDate = new Date(this.startTime);
                                 const newEndDate = new Date(startDate.getTime() + this.duration * 60000);
                                 return newEndDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                             }
                         }">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">1. Detail Pertemuan</h4>

                        @if (!$selectedRoom)
                            <div class="mb-4" id="tour-room-selection">
                                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-1">Ruangan <span
                                        class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ 
                                                    open    : false, 
                                                    sele    cted: @entangle('room_id'),
                                                    get     label() { 
                                                        if (    !this.selected) return '-- Pilih Ruangan --';
                                                        // W    e need a way to look up the name. Since we can't easily pass the full array to JS without bloating, 
                                                        // w    e'll rely on a hidden map or just update the UI text via Alpine when clicking an option.
                                                        // B    etter yet, let's just use the selected text content logic if possible, or a simpler approach:
                                                        // W    e will store the selected name in a separate Alpine var, initialized from PHP.
                                                        retu    rn this.selectedName;
                                                    },    
                                                    sele    ctedName: '{{ $rooms->firstWhere('id', $room_id)?->name ?? '-- Pilih Ruangan --' }}'
                                                }" @ click.away="open = false">
                                    <button type="button" @click="open = !open"
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="selectedName"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                :class="{ 'transform rotate-180': open }"></i>
                                        </span>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                        style="display: none;">
                                        @foreach ($rooms as $room)
                                            <div @click="selected = '{{ $room->id }}'; selectedName = '{{ addslashes($room->name) }}'; open = false; $wire.set('room_id', '{{ $room->id }}')"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150 group"
                                                :class="{ 'bg-green-50': selected == '{{ $room->id }}', 'hover:bg-green-50': selected != '{{ $room->id }}' }">
                                                <span class="block truncate font-medium"
                                                    :class="{ 'font-bold text-green-900': selected == '{{ $room->id }}', 'text-gray-900 group-hover:text-green-700': selected != '{{ $room->id }}' }">
                                                    {{ $room->name }} (Cap: {{ $room->capacity }})
                                                </span>
                                                <span x-show="selected == '{{ $room->id }}'"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
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
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-1">Topik <span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model="topic" id="topic"
                                class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-4 py-2.5 sm:text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 placeholder-gray-400 @error('topic') border-red-500 @enderror"
                                placeholder="cth., Rapat Mingguan">
                            @error('topic') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="tour-datetime" x-data="{ 
                                date: '{{ \Carbon\Carbon::parse($start_time)->format('Y-m-d') }}',
                                hour: '{{ \Carbon\Carbon::parse($start_time)->format('H') }}',
                                minute: '{{ \Carbon\Carbon::parse($start_time)->format('i') }}',
                                duration: @entangle('duration').live,
                                occupiedSlots: @entangle('occupiedSlots').live,
                                isTimeBlocked(h, m) {
                                    const time = parseInt(h) * 60 + parseInt(m);
                                    return this.occupiedSlots.some(slot => time >= slot.start_minutes && time < slot.end_minutes);
                                },
                                isHourFull(h) {
                                    return ['00', '15', '30', '45'].every(m => this.isTimeBlocked(h, m));
                                },
                                isPastTime(h, m) {
                                    const now = new Date();
                                    const year = now.getFullYear();
                                    const month = String(now.getMonth() + 1).padStart(2, '0');
                                    const day = String(now.getDate()).padStart(2, '0');
                                    const today = `${year}-${month}-${day}`;

                                    if (this.date !== today) return false;
                                    
                                    const currentHour = now.getHours();
                                    const currentMinute = now.getMinutes();
                                    
                                    if (parseInt(h) < currentHour) return true;
                                    if (parseInt(h) === currentHour && parseInt(m) < currentMinute) return true;
                                    return false;
                                },
                                isHourInPast(h) {
                                    const now = new Date();
                                    const year = now.getFullYear();
                                    const month = String(now.getMonth() + 1).padStart(2, '0');
                                    const day = String(now.getDate()).padStart(2, '0');
                                    const today = `${year}-${month}-${day}`;
                                    
                                    if (this.date !== today) return false;
                                    
                                    const currentHour = now.getHours();
                                    if (parseInt(h) < currentHour) return true;
                                    if (parseInt(h) === currentHour) {
                                        return 45 < now.getMinutes();
                                    }
                                    return false;
                                },
                                updateStartTime() {
                                    const selectedMinute = Math.round(this.minute / 15) * 15;
                                    const formattedMinute = String(selectedMinute).padStart(2, '0');
                                    const newStartTime = `${this.date}T${this.hour}:${formattedMinute}`;
                                    @this.set('start_time', newStartTime);
                                    this.startTime = newStartTime; 
                                },
                                calculateEndTime() {
                                    if (!this.hour || !this.minute || !this.duration) return '';
                                    let totalMinutes = parseInt(this.hour) * 60 + parseInt(this.minute) + parseInt(this.duration);
                                    let endH = Math.floor(totalMinutes / 60) % 24;
                                    let endM = totalMinutes % 60;
                                    return `${String(endH).padStart(2, '0')}:${String(endM).padStart(2, '0')}`;
                                }
                            }">
                            <div class="mb-4">
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                    Mulai
                                    <span class="text-red-500">*</span></label>
                                <!-- Start Date Picker -->
                                <div class="relative group" x-data="datePicker({ value: date })"
                                    @input="date = $event.detail; updateStartTime()">
                                    <input type="hidden" x-model="value">
                                    <button type="button" @click="open = !open"
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-8 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200 text-gray-700"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="formattedDate || 'Pilih Tanggal'"></span>
                                        <span
                                            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                                            <i class="fas fa-calendar-alt text-xs"></i>
                                        </span>
                                    </button>

                                    <!-- Calendar Dropdown -->
                                    <div x-show="open" @click.away="open = false"
                                        class="absolute z-10 mt-1 w-64 bg-white shadow-lg rounded-xl p-4 text-sm border border-green-500/30"
                                        style="display: none;">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <span x-text="months[month]"
                                                    class="text-base font-bold text-gray-800"></span>
                                                <span x-text="year"
                                                    class="ml-1 text-base text-gray-600 font-normal"></span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button type="button"
                                                    class="transition-colors hover:bg-gray-100 rounded-lg p-1"
                                                    @click="prevMonth">
                                                    <i class="fas fa-arrow-up text-gray-600"></i>
                                                </button>
                                                <button type="button"
                                                    class="transition-colors hover:bg-gray-100 rounded-lg p-1"
                                                    @click="nextMonth">
                                                    <i class="fas fa-arrow-down text-gray-600"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-7 mb-2">
                                            <template x-for="(day, index) in days" :key="index">
                                                <div class="px-0.5">
                                                    <div x-text="day"
                                                        class="text-xs font-medium text-center text-gray-800"></div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="grid grid-cols-7">
                                            <template x-for="blank in blankdays">
                                                <div class="text-center border p-1 border-transparent text-sm"></div>
                                            </template>
                                            <template x-for="(date, dateIndex) in no_of_days" :key="dateIndex">
                                                <div class="px-0.5 mb-1">
                                                    <div @click="getDateValue(date)" x-text="date"
                                                        class="cursor-pointer text-center text-sm rounded-lg leading-7 transition-colors duration-150 ease-in-out"
                                                        :class="{ 'bg-green-500 text-white': isSelected(date), 'text-gray-700 hover:bg-green-100': !isSelected(date), 'bg-green-100': isToday(date) && !isSelected(date) }">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                                            <button type="button" @click="value = ''; open = false"
                                                class="text-xs text-green-500 hover:text-green-700">Hapus</button>
                                            <button type="button" @click="init(); open = false"
                                                class="text-xs text-green-500 hover:text-green-700">Hari Ini</button>
                                        </div>
                                    </div>
                                </div>
                                @error('start_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                <label for="start_hour" class="block text-sm font-medium text-gray-700 mt-4 mb-1">Waktu
                                    Mulai
                                    <span class="text-red-500">*</span></label>
                                <div class="flex space-x-2">
                                    <!-- Hour Dropdown -->
                                    <div class="w-1/2 relative" x-data="{ open: false }" @click.away="open = false">
                                        <button type="button" @click="open = !open"
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-8 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="hour"></span>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                    :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">
                                            @foreach (range(7, 18) as $h)
                                                @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                <div @click="if(!isHourFull('{{ $val }}') && !isHourInPast('{{ $val }}')) { hour = '{{ $val }}'; updateStartTime(); open = false; }"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                    :class="{ 
                                                                        'tex    t-green-900 bg-green-50': hour == '{{ $val }}', 
                                                                        'tex    t-gray-900 hover:bg-green-50 hover:text-green-700': hour != '{{ $val }}' && !isHourFull('{{ $val }}') && !isHourInPast('{{ $val }}'),
                                                                        'tex    t-gray-400 cursor-not-allowed bg-gray-50': isHourFull('{{ $val }}') || isHourInPast('{{ $val }}')
                                                                     }">
                                                    <span class="block truncate font-medium"
                                                        :class="{ 'font-semibold': hour == '{{ $val }}' }">{{ $val }}</span>
                                                    <span x-show="hour == '{{ $val }}'"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                    <span x-show="isHourFull('{{ $val }}') || isHourInPast('{{ $val }}')"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-red-400">
                                                        <i class="fas fa-ban text-xs"></i>
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
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                    :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">
                                            @foreach (['00', '15', '30', '45'] as $m)
                                                <div @click="if(!isTimeBlocked(hour, '{{ $m }}') && !isPastTime(hour, '{{ $m }}')) { minute = '{{ $m }}'; updateStartTime(); open = false; }"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                    :class="{ 
                                                                        'tex    t-green-900 bg-green-50': minute == '{{ $m }}', 
                                                                        'tex    t-gray-900 hover:bg-green-50 hover:text-green-700': minute != '{{ $m }}' && !isTimeBlocked(hour, '{{ $m }}') && !isPastTime(hour, '{{ $m }}'),
                                                                        'tex    t-gray-400 cursor-not-allowed bg-gray-50': isTimeBlocked(hour, '{{ $m }}') || isPastTime(hour, '{{ $m }}')
                                                                     }">
                                                    <span class="block truncate font-medium"
                                                        :class="{ 'font-semibold': minute == '{{ $m }}' }">{{ $m }}</span>
                                                    <span x-show="minute == '{{ $m }}'"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                    <span
                                                        x-show="isTimeBlocked(hour, '{{ $m }}') || isPastTime(hour, '{{ $m }}')"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-red-400">
                                                        <i class="fas fa-ban text-xs"></i>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durasi
                                    <span class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ 
                                    open: false, 
                                    options: {
                                        '15': '15 Menit', '30': '30 Menit', '45': '45 Menit',
                                        '60': '1 Jam', '75': '1 Jam 15 Menit', '90': '1.5 Jam', '105': '1 Jam 45 Menit', '120': '2 Jam',
                                        '150': '2.5 Jam', '180': '3 Jam', '210': '3.5 Jam', '240': '4 Jam', '270': '4.5 Jam', '300': '5 Jam', '330': '5.5 Jam', '360': '6 Jam'
                                    },
                                    get label() { return this.options[this.duration] || 'Pilih Durasi' }
                                }" @click.away="open = false">
                                    <button type="button" @click="open = !open"
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="label"></span>
                                        <span
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                :class="{ 'transform rotate-180': open }"></i>
                                        </span>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                        style="display: none;">

                                        <!-- Rapat Cepat -->
                                        <div
                                            class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50">
                                            Rapat Cepat</div>
                                        @foreach (['15' => '15 Menit', '30' => '30 Menit', '45' => '45 Menit'] as $val => $text)
                                            <div @click="duration = '{{ $val }}'; open = false"
                                                class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                                :class="{ 'text-green-900 bg-green-50': duration == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': duration != '{{ $val }}' }">
                                                <span class="block truncate font-medium"
                                                    :class="{ 'font-semibold': duration == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="duration == '{{ $val }}'"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600"><i
                                                        class="fas fa-check text-xs"></i></span>
                                            </div>
                                        @endforeach

                                        <!-- Rapat Standar -->
                                        <div
                                            class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-t border-gray-100">
                                            Rapat Standar</div>
                                        @foreach (['60' => '1 Jam', '75' => '1 Jam 15 Menit', '90' => '1.5 Jam', '105' => '1 Jam 45 Menit', '120' => '2 Jam'] as $val => $text)
                                            <div @click="duration = '{{ $val }}'; open = false"
                                                class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                                :class="{ 'text-green-900 bg-green-50': duration == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': duration != '{{ $val }}' }">
                                                <span class="block truncate font-medium"
                                                    :class="{ 'font-semibold': duration == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="duration == '{{ $val }}'"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600"><i
                                                        class="fas fa-check text-xs"></i></span>
                                            </div>
                                        @endforeach

                                        <!-- Sesi Panjang -->
                                        <div
                                            class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-t border-gray-100">
                                            Sesi Panjang</div>
                                        @foreach (['150' => '2.5 Jam', '180' => '3 Jam', '210' => '3.5 Jam', '240' => '4 Jam', '270' => '4.5 Jam', '300' => '5 Jam', '330' => '5.5 Jam', '360' => '6 Jam'] as $val => $text)
                                            <div @click="duration = '{{ $val }}'; open = false"
                                                class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                                :class="{ 'text-green-900 bg-green-50': duration == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': duration != '{{ $val }}' }">
                                                <span class="block truncate font-medium"
                                                    :class="{ 'font-semibold': duration == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="duration == '{{ $val }}'"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600"><i
                                                        class="fas fa-check text-xs"></i></span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Estimasi Waktu Selesai: <span x-text="calculateEndTime()"
                                        class="font-semibold"></span>
                                </div>
                                @error('duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="priority_guest_id" class="block text-sm font-medium text-gray-700 mb-1">Tamu
                                Prioritas (Opsional)</label>
                            <div class="relative" x-data="{ 
                                open: false, 
                                selected: @entangle('priority_guest_id'),
                                selectedName: '{{ $priorityGuests->firstWhere('id', $priority_guest_id) ? ($priorityGuests->firstWhere('id', $priority_guest_id)->name . ' - ' . $priorityGuests->firstWhere('id', $priority_guest_id)->title) : '-- Tidak Ada Tamu Prioritas --' }}'
                            }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                    :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                    <span class="block truncate" x-text="selectedName"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                            :class="{ 'transform rotate-180': open }"></i>
                                    </span>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                    style="display: none;">
                                    <div @click="selected = ''; selectedName = '-- No Priority Guest --'; open = false; $wire.set('priority_guest_id', null)"
                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                        :class="{ 'text-green-900 bg-green-50': !selected, 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected }">
                                        <span class="block truncate font-medium"
                                            :class="{ 'font-semibold': !selected }">-- Tidak Ada Tamu Prioritas
                                            --</span>
                                        <span x-show="!selected"
                                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                            <i class="fas fa-check text-xs"></i>
                                        </span>
                                    </div>
                                    @foreach ($priorityGuests as $guest)
                                        <div @click="selected = '{{ $guest->id }}'; selectedName = '{{ addslashes($guest->name . ' - ' . $guest->title) }}'; open = false; $wire.set('priority_guest_id', '{{ $guest->id }}')"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                            :class="{ 'text-green-900 bg-green-50': selected == '{{ $guest->id }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $guest->id }}' }">
                                            <span class="block truncate font-medium"
                                                :class="{ 'font-semibold': selected == '{{ $guest->id }}' }">{{ $guest->name }}
                                                - {{ $guest->title }}</span>
                                            <span x-show="selected == '{{ $guest->id }}'"
                                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                <i class="fas fa-check text-xs"></i>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('priority_guest_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Recurring Meeting Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="recurring"
                                class="form-checkbox h-5 w-5 text-green-600 rounded focus:ring-green-500">
                            <span class="ml-3 text-lg font-semibold text-gray-800">Buat Pertemuan Berulang</span>
                        </label>

                        <div x-show="$wire.recurring" x-transition class="mt-4 pt-4 border-t">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="frequency"
                                        class="block text-sm font-medium text-gray-700 mb-1">Ulangi</label>
                                    <div class="relative" x-data="{ 
                                        open: false, 
                                        selected: @entangle('frequency'),
                                        options: { 'daily': 'Harian', 'weekly': 'Mingguan' }, 
                                        get label() { return this.options[this.selected] || 'Harian' }
                                    }" @click.away="open = false">
                                        <button type="button" @click="open = !open"
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="label"></span>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                    :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">
                                            @foreach (['daily' => 'Harian', 'weekly' => 'Mingguan'] as $val => $text)
                                                <div @click="selected = '{{ $val }}'; open = false"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                    :class="{ 'text-green-900 bg-green-50': selected == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $val }}' }">
                                                    <span class="block truncate font-medium"
                                                        :class="{ 'font-semibold': selected == '{{ $val }}' }">{{ $text }}</span>
                                                    <span x-show="selected == '{{ $val }}'"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                        Berakhir</label>
                                    <div class="relative group" x-data="datePicker({ value: @entangle('ends_at') })">
                                        <input type="hidden" x-model="value" id="ends_at">

                                        <button type="button" @click="open = !open"
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-8 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200 text-gray-700"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate"
                                                x-text="formattedDate || 'Pilih Tanggal'"></span>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                                                <i class="fas fa-calendar-alt text-xs"></i>
                                            </span>
                                        </button>

                                        <!-- Calendar Dropdown -->
                                        <div x-show="open" @click.away="open = false"
                                            class="absolute z-10 mt-1 w-64 bg-white shadow-lg rounded-xl p-4 text-sm border border-green-500/30 bottom-0 left-0 lg:bottom-auto lg:top-full"
                                            style="display: none;">
                                            <div class="flex items-center justify-between mb-4">
                                                <div>
                                                    <span x-text="months[month]"
                                                        class="text-base font-bold text-gray-800"></span>
                                                    <span x-text="year"
                                                        class="ml-1 text-base text-gray-600 font-normal"></span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <button type="button"
                                                        class="transition-colors hover:bg-gray-100 rounded-lg p-1"
                                                        @click="prevMonth">
                                                        <i class="fas fa-arrow-up text-gray-600"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="transition-colors hover:bg-gray-100 rounded-lg p-1"
                                                        @click="nextMonth">
                                                        <i class="fas fa-arrow-down text-gray-600"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-7 mb-2">
                                                <template x-for="(day, index) in days" :key="index">
                                                    <div class="px-0.5">
                                                        <div x-text="day"
                                                            class="text-xs font-medium text-center text-gray-800"></div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="grid grid-cols-7">
                                                <template x-for="blank in blankdays">
                                                    <div class="text-center border p-1 border-transparent text-sm">
                                                    </div>
                                                </template>
                                                <template x-for="(date, dateIndex) in no_of_days" :key="dateIndex">
                                                    <div class="px-0.5 mb-1">
                                                        <div @click="getDateValue(date)" x-text="date"
                                                            class="cursor-pointer text-center text-sm rounded-lg leading-7 transition-colors duration-150 ease-in-out"
                                                            :class="{ 'bg-green-500 text-white': isSelected(date), 'text-gray-700 hover:bg-green-100': !isSelected(date), 'bg-green-100': isToday(date) && !isSelected(date) }">
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                                                <button type="button" @click="value = ''; open = false"
                                                    class="text-xs text-green-500 hover:text-green-700">Hapus</button>
                                                <button type="button" @click="init(); open = false"
                                                    class="text-xs text-green-500 hover:text-green-700">Hari
                                                    Ini</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Participants Card -->
                    <div class="bg-white rounded-lg shadow-md" id="tour-participants"
                        x-data="{ activeTab: 'internal' }">
                        <div class="p-6">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">2. Tambahkan Peserta</h4>
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                                    <button type="button" @click="activeTab = 'internal'"
                                        :class="{'border-green-500 text-green-600': activeTab === 'internal', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'internal'}"
                                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                        Internal
                                    </button>
                                    <button type="button" @click="activeTab = 'external'"
                                        :class="{'border-green-500 text-green-600': activeTab === 'external', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'external'}"
                                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                        Eksternal
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

                    <!-- Room Schedule Card (Moved) -->
                    @if($room_id)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-calendar-check mr-2 text-green-600"></i> Jadwal Ruangan
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Pertemuan mendatang di ruangan ini</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Waktu</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Topik</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Oleh</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($this->roomMeetings as $meeting)
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                                    <div>{{ \Carbon\Carbon::parse($meeting->start_time)->format('d M') }}</div>
                                                    <div class="font-medium text-gray-900">
                                                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
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
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                                        {{ $meeting->calculated_status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                                                                        {{ $meeting->calculated_status === 'ongoing' ? 'bg-green-100 text-green-800' : '' }}
                                                                                        {{ $meeting->calculated_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                                                        {{ $meeting->calculated_status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                        {{ ucfirst($meeting->calculated_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                                    Tidak ada pertemuan terjadwal.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Room/Booker Info Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        @if ($selectedRoom)
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Info Ruangan</h4>
                            <img class="w-full h-40 object-cover rounded-md mb-4"
                                src="{{ $selectedRoom->image_path ? route('master.rooms.image', ['filename' => basename($selectedRoom->image_path)]) : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" style="background:#f0f0f0;font-family:sans-serif;font-size:30px;color:#888;text-anchor:middle"><text x="50%" y="50%" dominant-baseline="middle">No Image for ' . htmlspecialchars($selectedRoom->name) . '</text></svg>' }}"
                                alt="{{ $selectedRoom->name }}">
                            <p class="font-bold text-lg">{{ $selectedRoom->name }}</p>
                            <p class="text-sm text-gray-600">Kapasitas: {{ $selectedRoom->capacity }} orang</p>
                            @php
                                $facilities = !empty($selectedRoom->facilities) ? array_map('trim', explode(',', $selectedRoom->facilities)) : [];
                            @endphp
                            @if (!empty($facilities))
                                <div class="mt-2">
                                    <h5 class="text-sm font-semibold text-gray-700">Fasilitas:</h5>
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
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Dalam
                                        Perbaikan</span>
                                @elseif ($current_meeting)
                                    <span
                                        class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">Digunakan
                                        sampai {{ $current_meeting->end_time?->format('H:i T') ?? 'N/A' }}</span>
                                @else
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Tersedia</span>
                                @endif
                            </div>
                        @else
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Info Ruangan</h4>
                            <p class="text-gray-600">Silakan pilih ruangan dari menu dropdown di bagian "Detail Pertemuan".
                            </p>
                        @endif

                        <div class="border-t mt-4 pt-4">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Booker Info</h4>

                            @if($isEditMode && Auth::user()->hasRole('Super Admin'))
                                {{-- Super Admin can change organizer in edit mode --}}
                                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-xs text-yellow-800 mb-2">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <strong>Super Admin:</strong> Anda dapat mengubah penyelenggara pertemuan di bawah
                                        ini
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <label for="organizer_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Penyelenggara Pertemuan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative" x-data="{ 
                                                        open    : false, 
                                                        sele    cted: @entangle('organizer_user_id'),
                                                        sele    ctedName: '{{ $allUsers->firstWhere('id', $organizer_user_id)?->name ?? Auth::user()->name }}',
                                                        sear    chQuery: '',
                                                        get     filteredUsers() {
                                                            if (    !this.searchQuery) return @js($allUsers->toArray());
                                                            cons    t query = this.searchQuery.toLowerCase();
                                                            retu    rn @js($allUsers->toArray()).filter(user => 
                                                                user    .name.toLowerCase().includes(query) || 
                                                                (use    r.npk && user.npk.toLowerCase().includes(query)) ||
                                                                (use    r.department && user.department.toLowerCase().includes(query))
                                                            );    
                                                        }    
                                                    }" @ click.away="open = false">
                                        <button type="button" @click="open = !open"
                                            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                            :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                            <span class="block truncate" x-text="selectedName"></span>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                    :class="{ 'transform rotate-180': open }"></i>
                                            </span>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-xl text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm border border-green-500/30"
                                            style="display: none;">

                                            {{-- Search Input --}}
                                            <div class="sticky top-0 bg-white p-2 border-b border-gray-200 rounded-t-xl">
                                                <div class="relative">
                                                    <input type="text" x-model="searchQuery" @click.stop
                                                        placeholder="Cari berdasarkan nama, NPK, atau departemen..."
                                                        class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                                                    <i
                                                        class="fas fa-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                                                </div>
                                            </div>

                                            {{-- User List --}}
                                            <div class="max-h-60 overflow-auto py-1">
                                                <template x-for="user in filteredUsers" :key="user.id">
                                                    <div @click="selected = user.id; selectedName = user.name; open = false; searchQuery = ''; $wire.set('organizer_user_id', user.id)"
                                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                        :class="{ 'text-green-900 bg-green-50': selected == user.id, 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != user.id }">
                                                        <div>
                                                            <span class="block truncate font-medium"
                                                                :class="{ 'font-semibold': selected == user.id }"
                                                                x-text="user.name"></span>
                                                            <span class="block text-xs text-gray-500">
                                                                NPK: <span x-text="user.npk || 'N/A'"></span> | <span
                                                                    x-text="user.department || 'N/A'"></span>
                                                            </span>
                                                        </div>
                                                        <span x-show="selected == user.id"
                                                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </span>
                                                    </div>
                                                </template>

                                                {{-- No Results Message --}}
                                                <div x-show="filteredUsers.length === 0"
                                                    class="py-4 text-center text-sm text-gray-500">
                                                    <i class="fas fa-search text-gray-400 mb-2"></i>
                                                    <p>Tidak ada pengguna yang cocok dengan "<span
                                                            x-text="searchQuery"></span>"</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-700"><span class="font-medium">NPK Penyelenggara:</span>
                                    {{ $allUsers->firstWhere('id', $organizer_user_id)?->npk ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">Departemen:</span>
                                    {{ $allUsers->firstWhere('id', $organizer_user_id)?->department ?? 'N/A' }}</p>
                            @else
                                {{-- Regular user or create mode: show current user info --}}
                                <p class="text-sm text-gray-700"><span class="font-medium">Nama:</span>
                                    {{ Auth::user()->name }}</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">NPK:</span>
                                    {{ Auth::user()->npk ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">Departemen:</span>
                                    {{ Auth::user()->department ?? 'N/A' }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Room Schedule Card -->
                    <!-- Pantry Card (Moved) -->
                    <div class="bg-white rounded-lg shadow-md" id="tour-pantry">
                        <div class="p-6">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Pesanan Pantry</h4>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-b-lg">
                            @livewire('meeting.select-pantry-items', ['initialPantryItems' => $pantryOrders])
                        </div>
                    </div>


                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8">
                {{-- Error block moved to top --}}
                <div class="flex justify-end">
                    <button type="button" id="tour-submit" wire:click.prevent="submitForm" wire:loading.attr="disabled"
                        wire:target="submitForm"
                        class="inline-flex items-center justify-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 text-lg relative">
                        <span wire:loading.remove wire:target="submitForm">
                            {{ $isEditMode ? 'Perbarui Pertemuan' : 'Buat Pertemuan' }}
                        </span>
                        <span wire:loading wire:target="submitForm" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Sedang Memproses...
                        </span>
                    </button>
                </div>
        </form>
    </div>
</main>