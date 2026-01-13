<div>
    <!-- State 1: Security Gate (Locked) -->
    <!-- State 1: Security Gate (Locked) -->
    <div class="{{ $status == 'open' ? 'bg-green-50 border-green-500' : ($status == 'waiting' ? 'bg-blue-50 border-blue-500' : 'bg-red-50 border-red-500') }} border-l-4 p-5 rounded-r-xl mb-6 shadow-sm transition-colors duration-300">
        
        <!-- Header & Status -->
        <div class="mb-4">
            <h3 class="{{ $status == 'open' ? 'text-green-800' : ($status == 'waiting' ? 'text-blue-800' : 'text-red-800') }} font-extrabold text-lg flex items-center gap-2">
                @if($status == 'open') <i class="fas fa-user-check"></i> Absensi Peserta
                @elseif($status == 'waiting') <i class="fas fa-clock"></i> Absensi Belum Dibuka
                @else <i class="fas fa-lock"></i> Absensi Ditutup @endif
            </h3>
            
            <p class="text-gray-600 text-sm mt-2 leading-relaxed">
                @if($status === 'waiting')
                    Menu ini akan aktif saat meeting dimulai.<br>
                    <span class="text-xs text-blue-500 font-medium">Organizer/PIC dapat melakukan scan setelah waktu mulai.</span>
                @elseif($status === 'closed')
                    Batas waktu absensi telah berakhir.<br>
                    <span class="text-xs text-red-500 font-medium font-bold">Max 30 menit setelah meeting selesai.</span>
                @else
                    Silakan masukkan NPK untuk membuka daftar hadir.<br>
                    <span class="text-xs text-green-600 font-bold">Batas Akhir: 30 menit setelah meeting selesai.</span>
                @endif
            </p>
        </div>
        
        <!-- Input Action -->
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
                    // Check if Organizer is also a participant (Pivot) OR check organizer_attended logic (if we had column on meeting)
                    // Currently we rely on Pivot. Finding pivot for organizer:
                    $organizerPivot = $participantsList->first(function($p) use ($meeting) {
                        return $p->participant_type === 'App\Models\User' && $p->participant_id === $meeting->user_id; 
                    });
                    $isOrganizerAttended = $organizerPivot && $organizerPivot->attended_at;
                @endphp
                <span class="text-xs px-2 py-1 rounded-full font-bold {{ $isOrganizerAttended ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $isOrganizerAttended ? 'Hadir' : 'Belum Hadir' }}
                </span>
            </li>
            
            <!-- Other Participants -->
            @foreach($participantsList as $mp)
                @if($mp->participant_id == $meeting->user_id && $mp->participant_type == 'App\Models\User') @continue @endif
                <li class="px-4 py-3 text-sm flex justify-between items-center bg-white hover:bg-gray-50 transition-colors">
                    <span class="font-medium text-gray-700">
                        {{ $mp->name }}
                        @if($mp->is_pic)
                            <span class="text-xs font-normal text-blue-500 ml-1">(PIC)</span>
                        @endif
                    </span>
                    <span class="text-xs px-2 py-1 rounded-full font-bold {{ $mp->attended_at ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $mp->attended_at ? 'Hadir' : 'Belum Hadir' }}
                    </span>
                </li>
            @endforeach
            
            @if($participantsList->count() == 0)
                <li class="px-4 py-3 text-sm text-gray-500 italic text-center">Tidak ada peserta tambahan.</li>
            @endif
        </ul>
    </div>

    <!-- Right: Check-In Form -->
    <div class="flex flex-col h-full justify-between">
        @if ($status === 'waiting')
            <div class="text-center py-8">
                <i class="fas fa-clock text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 font-bold">Absensi Belum Dibuka</p>
                <p class="text-xs text-gray-400 mt-1">Akan dibuka saat meeting dimulai.</p>
            </div>
        @elseif ($status === 'closed')
            <div class="text-center py-8">
                <i class="fas fa-lock text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 font-bold">Absensi Ditutup</p>
                <p class="text-xs text-gray-400 mt-1">Meeting sudah selesai.</p>
            </div>
        @else
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Check-In Peserta
                </label>
                
                <form wire:submit="verifyNpk" class="relative">
                    <input type="text" 
                        wire:model="inputNpk"
                        class="w-full text-center text-xl font-bold tracking-widest border-2 border-gray-200 rounded-xl py-3 focus:ring-green-500 focus:border-green-500 transition-colors uppercase placeholder-gray-300"
                        placeholder="NPK"
                        autofocus
                        inputmode="numeric"
                    >
                    <button type="submit" class="absolute right-2 top-2 bottom-2 bg-[#089244] hover:bg-[#067a39] text-white px-4 rounded-lg font-bold shadow-md transition-all">
                        Check-In
                    </button>
                    <!-- Loading Indicator -->
                    <div wire:loading wire:target="verifyNpk" class="absolute inset-y-0 right-14 flex items-center pr-2">
                        <svg class="animate-spin h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </form>

                @error('inputNpk') 
                    <p class="text-red-500 text-xs mt-2 font-bold animate-pulse">{{ $message }}</p> 
                @enderror
                
                @if($errorMsg)
                    <p class="text-red-500 text-xs mt-2 font-bold">{{ $errorMsg }}</p>
                @endif
                
                <p class="text-[10px] text-gray-400 mt-2 text-center">
                    Masukkan NPK Anda untuk konfirmasi kehadiran.
                </p>
            </div>
        @endif

        @if($meeting->status == 'scheduled' || $meeting->status == 'ongoing')
            <form method="POST" action="{{ route('tablet.cancel', $meeting->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan meeting ini?');" class="mt-auto pt-4 border-t border-gray-100">
                @csrf
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-400">
                        Ingin membatalkan meeting? <br>Hanya Organizer / PIC yang dapat membatalkan.
                    </div>
                    <div class="flex gap-2">
                            <input type="text" name="npk" required placeholder="NPK Organizer / PIC" class="w-32 border-gray-300 rounded-lg text-xs py-1 px-2">
                            <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 font-bold py-1 px-3 rounded-lg text-xs border border-red-200 transition-colors">
                            Batalkan
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
    
    <!-- Toast Script (Optional if using global listener) -->
    <script>
        document.addEventListener('livewire:initialized', () => {
             Livewire.on('attendance-saved', (event) => {
                 // Play sound or show toast
                 // Assuming separate toast handler in layout, but we can do a quick visual cue here if needed.
                 // The global layout usually handles flash messages or we use SweetAlert.
             });
        });
    </script>
</div>
    </div>

    <!-- Modal Removed -->

    <!-- Inline Alert Success -->
    <div x-data="{ show: false, message: '' }" 
         @attendance-saved.window="show = true; message = $event.detail; setTimeout(() => show = false, 3000)" 
         x-show="show" 
         x-transition 
         class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-xl z-50 font-bold flex items-center"
         style="display: none;">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span x-text="message"></span>
    </div>
</div>
