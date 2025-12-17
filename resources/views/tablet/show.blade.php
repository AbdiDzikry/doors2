<x-tablet-layout>
    <div class="h-screen bg-gray-100 flex flex-col overflow-hidden" 
         x-data="{
            tab: 'list', // list | booking
            
            // Clock Logic
            currentTime: '--:--',
            initClock() {
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
            },
            updateClock() {
                let now = new Date();
                let h = String(now.getHours()).padStart(2, '0');
                let m = String(now.getMinutes()).padStart(2, '0');
                this.currentTime = `${h}:${m}`;
            },

            // Booking Form Logic
            date: '{{ now()->format("Y-m-d") }}',
            hour: '{{ now()->format("H") }}',
            minute: '00',
            duration: '60',
            initBooking() {
                // Round Start Time
                let now = new Date();
                let m = now.getMinutes();
                let h = now.getHours();
                if (m < 15) { this.minute = '15'; }
                else if (m < 30) { this.minute = '30'; }
                else if (m < 45) { this.minute = '45'; }
                else { this.minute = '00'; this.hour = String(h + 1).padStart(2, '0'); }
                this.hour = String(this.hour).padStart(2, '0');
            },
            updateHiddenInputs() {
                $refs.dateInput.value = this.date;
                $refs.hourInput.value = this.hour;
                $refs.minuteInput.value = this.minute;
                $refs.durationInput.value = this.duration;
            },
            calculateEndTime() {
                let totalMin = (parseInt(this.hour) * 60) + parseInt(this.minute) + parseInt(this.duration);
                let h = Math.floor(totalMin / 60) % 24;
                let m = totalMin % 60;
                return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
            },

            // Modal / Dropdown Logic
            // modalOpen: false, // Deprecated
            // selectedMeeting: null, // Deprecated
            expandedMeeting: null,
            toggleMeeting(id) {
                if (this.expandedMeeting === id) {
                    this.expandedMeeting = null;
                } else {
                    this.expandedMeeting = id;
                }
            },
            openModal(meeting) { // Legacy: keeping briefly for safety until full replacement
                this.selectedMeeting = meeting;
                this.modalOpen = true;
            }
         }"
         x-init="initClock(); initBooking(); $watch('date', () => updateHiddenInputs()); $watch('hour', () => updateHiddenInputs()); $watch('minute', () => updateHiddenInputs()); $watch('duration', () => updateHiddenInputs());">
         
        <!-- Top Bar -->
        <div class="bg-white shadow-md px-8 py-4 flex justify-between items-center z-20 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <x-application-logo class="block h-10 w-auto fill-current text-[#089244]" />
                <h1 class="text-2xl font-extrabold text-[#089244] tracking-tight">
                    Doors <span class="text-gray-400 font-medium text-lg">Panel</span>
                </h1>
            </div>
            
            <!-- Tabs -->
            <div class="flex bg-gray-100 p-1.5 rounded-xl shadow-inner">
                <button @click="tab = 'list'" 
                    class="px-8 py-2.5 rounded-lg font-bold text-sm transition-all duration-300 flex items-center gap-2"
                    :class="tab === 'list' ? 'bg-white text-[#089244] shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'">
                    <i class="fas fa-list-ul"></i> Jadwal Hari Ini
                </button>
                <button @click="tab = 'booking'" 
                    class="px-8 py-2.5 rounded-lg font-bold text-sm transition-all duration-300 flex items-center gap-2"
                    :class="tab === 'booking' ? 'bg-white text-[#089244] shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'">
                    <i class="fas fa-calendar-plus"></i> Booking Ruangan
                </button>
            </div>

            <div class="text-right">
                <div class="text-3xl font-black text-gray-800 tracking-tight leading-none" x-text="currentTime">--:--</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="flex-1 flex overflow-hidden p-6 gap-6 relative">
            
            <!-- LEFT COLUMN (Dynamic Content) -->
            <div class="flex-1 overflow-hidden flex flex-col relative">
                
                <!-- TAB 1: LIST MEETING -->
                <div x-show="tab === 'list'" x-transition 
                     class="flex-1 overflow-y-auto pr-2 space-y-4">
                    
                    <!-- Status Banner -->
                    @if($currentMeeting)
                        <div class="bg-red-600 text-white p-6 rounded-xl shadow-md flex items-center justify-between animate-pulse mb-6">
                            <div>
                                <h2 class="text-3xl font-bold uppercase tracking-wider">Sedang Dipakai</h2>
                                <p class="text-lg opacity-90 mt-1">Hingga {{ \Carbon\Carbon::parse($currentMeeting->end_time)->format('H:i') }} • {{ $currentMeeting->user->name }}</p>
                            </div>
                            <svg class="w-16 h-16 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        </div>
                    @else
                        <div class="bg-[#089244] text-white p-6 rounded-2xl shadow-lg flex items-center justify-between mb-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-white/10 opacity-0 animate-pulse"></div> <!-- Subtle sheen -->
                            <div class="relative z-10">
                                <h2 class="text-3xl font-extrabold uppercase tracking-wide flex items-center gap-3">
                                    <i class="fas fa-check-circle"></i> Ruangan Tersedia
                                </h2>
                                <p class="text-lg font-medium opacity-90 mt-1">Silakan booking untuk menggunakan.</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($todaysMeetings->isEmpty())
                        <div class="h-full flex flex-col items-center justify-center text-gray-400 mt-10">
                            <span class="text-6xl mb-4 opacity-50">🍵</span>
                            <h3 class="text-xl font-bold text-gray-600">Tidak ada jadwal meeting hari ini</h3>
                            <button @click="tab = 'booking'" class="mt-4 text-[#089244] font-bold hover:underline">Buat Booking Baru &rarr;</button>
                        </div>
                    @else
                        <!-- Table View (Matching Main Booking Form) -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                                    <span class="text-[#089244] mr-2"><i class="fas fa-calendar-alt"></i></span> Jadwal Ruangan
                                </h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50/80">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Topik</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Oleh</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($todaysMeetings as $meeting)
                                            <!-- Main Row -->
                                            <tr class="hover:bg-gray-50/80 transition-colors group cursor-pointer" @click="toggleMeeting({{ $meeting->id }})">
                                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 font-bold">
                                                    {{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}
                                                </td>
                                                <td class="px-6 py-5 text-sm text-gray-800 font-medium">
                                                    <div class="truncate max-w-[200px]" title="{{ $meeting->topic }}">
                                                        {{ $meeting->topic }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600 font-bold">
                                                            {{ substr($meeting->user->name, 0, 1) }}
                                                        </div>
                                                        <span class="truncate max-w-[120px]">{{ $meeting->user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5 whitespace-nowrap text-xs">
                                                    @if($meeting->calculated_status == 'ongoing')
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-[#089244] border border-green-200">
                                                            Ongoing
                                                        </span>
                                                    @elseif($meeting->calculated_status == 'completed')
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                                            Selesai
                                                        </span>
                                                    @else
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-50 text-blue-600 border border-blue-200">
                                                            Scheduled
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                                    <button class="text-gray-400 hover:text-[#089244] transition-colors focus:outline-none p-2 rounded-full hover:bg-green-50">
                                                        <svg class="w-5 h-5 transform transition-transform duration-200" 
                                                             :class="{'rotate-180': expandedMeeting === {{ $meeting->id }}}"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Expanded Detail Row (Dropdown) -->
                                            <tr x-show="expandedMeeting === {{ $meeting->id }}" x-transition.opacity class="bg-gray-50/50">
                                                <td colspan="5" class="p-0 border-b border-gray-200">
                                                    <div class="p-6 m-4 bg-white rounded-xl shadow-sm border border-gray-200">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                                            <!-- Left: Participants List -->
                                                            <div>
                                                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center">
                                                                    <i class="fas fa-users mr-2"></i> Daftar Peserta
                                                                </h4>
                                                                <ul class="border border-gray-100 rounded-lg overflow-hidden divide-y divide-gray-100 max-h-60 overflow-y-auto">
                                                                    <!-- Organizer -->
                                                                    <li class="px-4 py-3 text-sm flex justify-between items-center bg-blue-50/50">
                                                                        <div class="flex items-center">
                                                                            <span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span>
                                                                            <span class="font-bold text-gray-800">{{ $meeting->user->name }} <span class="text-xs font-normal text-gray-500">(Organizer)</span></span>
                                                                        </div>
                                                                        @php
                                                                            $organizerParticipant = $meeting->meetingParticipants->where('participant_id', $meeting->user_id)->where('participant_type', 'App\Models\User')->first();
                                                                            $isOrganizerAttended = $organizerParticipant && $organizerParticipant->attended_at;
                                                                        @endphp
                                                                        <span class="text-xs px-2 py-1 rounded-full font-bold {{ $isOrganizerAttended ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                                            {{ $isOrganizerAttended ? 'Hadir' : 'Belum Hadir' }}
                                                                        </span>
                                                                    </li>
                                                                    
                                                                    <!-- Other Participants -->
                                                                    @foreach($meeting->meetingParticipants as $mp)
                                                                        @if($mp->participant_id == $meeting->user_id && $mp->participant_type == 'App\Models\User') @continue @endif
                                                                        <li class="px-4 py-3 text-sm flex justify-between items-center bg-white hover:bg-gray-50">
                                                                            <span class="font-medium text-gray-700">{{ $mp->participant ? $mp->participant->name : 'External Guest' }}</span>
                                                                            <span class="text-xs px-2 py-1 rounded-full font-bold {{ $mp->attended_at ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                                                {{ $mp->attended_at ? 'Hadir' : 'Belum Hadir' }}
                                                                            </span>
                                                                        </li>
                                                                    @endforeach
                                                                    
                                                                    @if($meeting->meetingParticipants->count() == 0)
                                                                        <li class="px-4 py-3 text-sm text-gray-500 italic text-center">Tidak ada peserta tambahan.</li>
                                                                    @endif
                                                                </ul>
                                                            </div>

                                                            <!-- Right: Action Form -->
                                                            <div class="flex flex-col h-full justify-between">
                                                                <form method="POST" action="{{ route('tablet.check-in', $meeting->id) }}">
                                                                    @csrf
                                                                    <div class="mb-4">
                                                                        <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Kehadiran (NPK)</label>
                                                                        <div class="flex gap-2">
                                                                            <input type="text" name="npk" required placeholder="Masukkan NPK Anda..." autofocus
                                                                                class="flex-1 border-gray-300 rounded-lg shadow-sm focus:ring-[#089244] focus:border-[#089244] text-lg px-4 py-2">
                                                                            <button type="submit" class="bg-[#089244] hover:bg-[#067a39] text-white font-bold py-2 px-6 rounded-lg shadow-sm transition-colors flex items-center">
                                                                                <i class="fas fa-check mr-2"></i> Absen
                                                                            </button>
                                                                        </div>
                                                                        <p class="text-xs text-gray-400 mt-2">Masukkan NPK Organizer atau Peserta untuk konfirmasi kehadiran.</p>
                                                                    </div>
                                                                </form>

                                                                @if($meeting->status == 'scheduled' || $meeting->status == 'ongoing')
                                                                    <form method="POST" action="{{ route('tablet.cancel', $meeting->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan meeting ini?');" class="mt-auto pt-4 border-t border-gray-100">
                                                                        @csrf
                                                                        <div class="flex items-center justify-between">
                                                                            <div class="text-xs text-gray-400">
                                                                                Ingin membatalkan meeting? <br>Hanya Organizer yang dapat membatalkan.
                                                                            </div>
                                                                            <div class="flex gap-2">
                                                                                 <input type="text" name="npk" required placeholder="NPK Organizer" class="w-32 border-gray-300 rounded-lg text-xs py-1 px-2">
                                                                                 <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 font-bold py-1 px-3 rounded-lg text-xs border border-red-200 transition-colors">
                                                                                    Batalkan
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: BOOKING FORM -->
                <div x-show="tab === 'booking'" style="display: none;" x-transition
                     class="flex-1 overflow-y-auto pr-2">
                    <form action="{{ route('tablet.book', $room->id) }}" method="POST" class="space-y-6"
                        x-data="{
                            internalParticipants: [],
                            externalParticipants: [],
                            updateParticipants() {
                                $refs.internalInput.value = JSON.stringify(this.internalParticipants);
                                $refs.externalInput.value = JSON.stringify(this.externalParticipants);
                            }
                        }"
                        @internal-participants-updated.window="internalParticipants = $event.detail[0]; updateParticipants()"
                        @external-participants-updated.window="externalParticipants = $event.detail[0]; updateParticipants()">
                        @csrf
                        <input type="hidden" name="start_date" x-ref="dateInput">
                        <input type="hidden" name="start_hour" x-ref="hourInput">
                        <input type="hidden" name="start_minute" x-ref="minuteInput">
                        <input type="hidden" name="duration" x-ref="durationInput">
                        <input type="hidden" name="internal_participants" x-ref="internalInput">
                        <input type="hidden" name="external_participants" x-ref="externalInput">
                        
                        <!-- 0. Booker Identification (NPK) -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#089244]">
                            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <span class="bg-green-100 text-[#089244] w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                                Identitas Pemesan
                            </h2>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Masukkan NPK Anda <span class="text-red-500">*</span></label>
                                <input type="text" name="npk" required placeholder="Contoh: 12345678"
                                    class="w-full text-lg border-gray-300 rounded-lg shadow-sm focus:ring-[#089244] focus:border-[#089244] py-3">
                            </div>
                        </div>

                        <!-- 1. Meeting Details -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">2</span>
                                Detail Meeting
                            </h2>
                            
                            <div class="space-y-6">
                                <!-- Topic -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Topik Meeting <span class="text-red-500">*</span></label>
                                    <input type="text" name="topic" required placeholder="Contoh: Weekly Sync"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                     x-data="{
                                        date: '{{ now()->format('Y-m-d') }}',
                                        duration: '60',
                                        hour: '{{ $defaultHour ?? now()->format('H') }}',
                                        minute: '{{ $defaultMinute ?? (now()->format('i') < 30 ? '00' : '30') }}',
                                        calculateEndTime() {
                                            let h = parseInt(this.hour);
                                            let m = parseInt(this.minute);
                                            let d = parseInt(this.duration);
                                            
                                            let totalMinutes = (h * 60) + m + d;
                                            let endH = Math.floor(totalMinutes / 60);
                                            let endM = totalMinutes % 60;
                                            
                                            // Handle day overflow
                                            if (endH >= 24) endH = endH - 24;

                                            return String(endH).padStart(2, '0') + ':' + String(endM).padStart(2, '0');
                                        },
                                        updateHiddenInputs() { 
                                            $refs.dateInput.value = this.date;
                                            $refs.hourInput.value = this.hour;
                                            $refs.minuteInput.value = this.minute;
                                            $refs.durationInput.value = this.duration;
                                        }
                                     }"
                                     x-init="updateHiddenInputs()"
                                     x-effect="updateHiddenInputs()">
                                    <!-- Date & Duration Column -->
                                    <div class="space-y-4">
                                        <!-- Date -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                            <div class="relative">
                                                <input type="date" x-model="date" 
                                                    class="block w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-gray-900 font-medium focus:outline-none focus:ring-1 focus:ring-[#089244] focus:border-[#089244] cursor-pointer">
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Duration -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi</label>
                                            <div class="relative" x-data="{ 
                                                open: false, 
                                                options: {
                                                    '15': '15 Menit', '30': '30 Menit', '45': '45 Menit',
                                                    '60': '1 Jam', '75': '1 Jam 15 Menit', '90': '1 Jam 30 Menit', '105': '1 Jam 45 Menit', '120': '2 Jam',
                                                    '150': '2.5 Jam', '180': '3 Jam', '210': '3.5 Jam', '240': '4 Jam', '270': '4.5 Jam', '300': '5 Jam', '330': '5.5 Jam', '360': '6 Jam'
                                                }
                                            }" @click.away="open = false">
                                                <button type="button" @click="open = !open" 
                                                    class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left focus:ring-1 focus:ring-[#089244] focus:border-[#089244]"
                                                    :class="{ 'border-[#089244] ring-1 ring-[#089244]': open }">
                                                    <span class="block truncate" x-text="options[duration] || 'Pilih Durasi'"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="open" class="absolute z-20 mt-1 w-full bg-white shadow-xl max-h-60 rounded-xl py-1 overflow-auto border border-[#089244]/30" style="display: none;">
                                                    <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase bg-gray-50">Rapat Cepat</div>
                                                    @foreach ([15, 30, 45] as $val)
                                                        <div @click="duration = '{{ $val }}'; open = false" class="cursor-pointer py-2 pl-4 hover:bg-green-50 hover:text-[#089244]">{{ $val }} Menit</div>
                                                    @endforeach
                                                    <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase bg-gray-50 border-t">Rapat Standar</div>
                                                    @foreach ([60, 90, 120] as $val)
                                                        <div @click="duration = '{{ $val }}'; open = false" class="cursor-pointer py-2 pl-4 hover:bg-green-50 hover:text-[#089244]">{{ $val }} Menit</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Start Time Column (Split Dropdown) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-3">
                                            <!-- Hour -->
                                            <div class="relative w-1/2" x-data="{ open: false }" @click.away="open = false">
                                                <button type="button" @click="open = !open" 
                                                    class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm px-4 py-3 text-center focus:outline-none focus:ring-1 focus:ring-[#089244] focus:border-[#089244]"
                                                    :class="{ 'border-[#089244] ring-1 ring-[#089244]': open }">
                                                    <span class="block text-lg font-bold text-gray-800" x-text="hour"></span>
                                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                                </button>
                                                <div x-show="open" class="absolute z-20 mt-1 w-full bg-white shadow-xl max-h-48 rounded-xl py-1 overflow-auto border border-[#089244]/30">
                                                    @foreach(range(7, 18) as $h)
                                                        @php $val = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                                                        <div @click="hour = '{{ $val }}'; open = false" class="cursor-pointer py-2 text-center hover:bg-green-50 hover:text-[#089244] font-bold text-gray-700 transition-colors">
                                                            {{ $val }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <span class="text-2xl font-bold text-gray-300">:</span>

                                            <!-- Minute -->
                                            <div class="relative w-1/2" x-data="{ open: false }" @click.away="open = false">
                                                <button type="button" @click="open = !open" 
                                                    class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm px-4 py-3 text-center focus:outline-none focus:ring-1 focus:ring-[#089244] focus:border-[#089244]"
                                                    :class="{ 'border-[#089244] ring-1 ring-[#089244]': open }">
                                                    <span class="block text-lg font-bold text-gray-800" x-text="minute"></span>
                                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                                </button>
                                                <div x-show="open" class="absolute z-20 mt-1 w-full bg-white shadow-xl max-h-48 rounded-xl py-1 overflow-auto border border-[#089244]/30">
                                                    @foreach(['00', '15', '30', '45'] as $m)
                                                        <div @click="minute = '{{ $m }}'; open = false" class="cursor-pointer py-2 text-center hover:bg-green-50 hover:text-[#089244] font-bold text-gray-700 transition-colors">
                                                            {{ $m }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-3 font-medium text-center md:text-left">
                                            Selesai: <span class="font-bold text-[#089244]" x-text="calculateEndTime()"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Participants -->
                        <div class="bg-white rounded-xl shadow-sm p-6" 
                             x-data="{ activePartTab: 'internal', internal: [], external: [] }"
                             @internal-participants-updated.window="internal = $event.detail"
                             @external-participants-updated.window="external = $event.detail">
                            
                            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <span class="bg-purple-100 text-purple-700 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">3</span>
                                Peserta Tambahan (Opsional)
                            </h2>

                            <!-- Hidden Inputs for Form Submission -->
                            <!-- Hidden Inputs for Form Submission -->
                            <input type="hidden" name="internal_participants" :value="JSON.stringify(internal)">
                            <input type="hidden" name="external_participants" :value="JSON.stringify(external)">

                            <!-- Tab Nav -->
                            <div class="border-b border-gray-200 mb-4">
                                <nav class="-mb-px flex space-x-6">
                                    <button type="button" @click="activePartTab = 'internal'" 
                                        :class="{'border-[#089244] text-[#089244]': activePartTab === 'internal', 'border-transparent text-gray-500 hover:text-gray-700': activePartTab !== 'internal'}" 
                                        class="whitespace-nowrap pb-2 px-1 border-b-2 font-medium text-sm transition-colors">
                                        Internal (Karyawan)
                                    </button>
                                    <!-- <button type="button" @click="activePartTab = 'external'" 
                                        :class="{'border-[#089244] text-[#089244]': activePartTab === 'external', 'border-transparent text-gray-500 hover:text-gray-700': activePartTab !== 'external'}" 
                                        class="whitespace-nowrap pb-2 px-1 border-b-2 font-medium text-sm transition-colors">
                                        Eksternal (Tamu)
                                    </button> -->
                                </nav>
                            </div>

                            <!-- Internal -->
                            <div x-show="activePartTab === 'internal'">
                                @livewire('meeting.search-internal-participants')
                            </div>

                            <!-- External -->
                            <div x-show="activePartTab === 'external'" style="display: none;">
                                @livewire('meeting.search-external-participants')
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-[#089244] hover:bg-[#067a39] active:bg-[#05602e] text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 text-lg uppercase tracking-wide flex justify-center items-center">
                            Booking Ruangan
                        </button>
                    </form>
                </div>

            </div>

            <!-- RIGHT COLUMN: Room Info & Status -->
            <div class="w-80 flex flex-col gap-6 shrink-0 z-0">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden sticky top-0">
                    <img src="{{ $room->image_path ? route('master.rooms.image', basename($room->image_path)) : 'https://placehold.co/400x250/e2e8f0/64748b?text=Room' }}" 
                        class="w-full h-40 object-cover">
                    <div class="p-5">
                        <h2 class="text-xl font-bold text-gray-900 leading-tight mb-1">{{ $room->name }}</h2>
                        <p class="text-sm text-gray-500 mb-4">Lantai {{ $room->floor }} • Cap: {{ $room->capacity }}</p>
                        
                        @if($currentMeeting)
                            <div class="bg-red-50 text-red-700 px-3 py-2 rounded-lg text-sm font-bold flex items-center mb-2">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                                DIPAKAI
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($currentMeeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentMeeting->end_time)->format('H:i') }}<br>
                                {{ $currentMeeting->user->name }}
                            </p>
                        @else
                            <div class="bg-green-50 text-green-700 px-3 py-2 rounded-lg text-sm font-bold flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                TERSEDIA
                            </div>
                        @endif

                        @if($room->facilities)
                        <div class="mt-4 border-t pt-4">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Fasilitas</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $room->facilities) as $facility)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ trim($facility) }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>


        <!-- Success/Error Alerts (Existing) -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-6 right-6 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center z-50">
                <span class="font-bold mr-2">Berhasil!</span> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-6 right-6 bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center z-50">
                <span class="font-bold mr-2">Gagal!</span> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div x-data="{ show: true }" x-show="show" class="fixed bottom-20 right-6 bg-red-50 text-red-700 px-6 py-4 rounded-xl shadow-2xl z-50 border border-red-200">
                <div class="flex items-start">
                    <span class="text-2xl mr-3">⚠️</span>
                    <div>
                        <h4 class="font-bold text-lg mb-1">Periksa Inputan Anda</h4>
                        <ul class="list-disc list-inside text-sm font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button @click="show = false" class="mt-3 text-xs font-bold uppercase tracking-wider text-red-500 hover:text-red-700">Tutup</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-tablet-layout>
