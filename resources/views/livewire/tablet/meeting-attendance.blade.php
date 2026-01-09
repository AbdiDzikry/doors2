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
        <div class="flex gap-2 w-full">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-id-card"></i>
                </span>
                <input type="text" 
                       wire:model="inputNpk" 
                       wire:keydown.enter="verifyNpk"
                       placeholder="NPK Organizer / PIC" 
                       class="w-full border-gray-300 rounded-lg pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all disabled:bg-gray-100 disabled:text-gray-400"
                       inputmode="numeric"
                       @if($status !== 'open') disabled @endif>
            </div>
            
            <button wire:click="verifyNpk" 
                    class="px-6 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2 
                    {{ $status == 'open' 
                        ? 'bg-green-600 hover:bg-green-700 active:bg-green-800 text-white' 
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                    @if($status !== 'open') disabled @endif>
                @if($status == 'open') 
                    Kelola <i class="fas fa-arrow-right"></i>
                @else
                    <i class="fas fa-ban"></i>
                @endif
            </button>
        </div>

        @error('inputNpk') <div class="text-red-600 text-xs font-bold mt-2 flex items-center animate-pulse"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
        @if($errorMsg) <div class="text-red-600 text-xs font-bold mt-2 flex items-center animate-pulse"><i class="fas fa-exclamation-circle mr-1"></i> {{ $errorMsg }}</div> @endif
    </div>

    <!-- State 2: Modal (Unlocked) -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="close"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Heroicon name: outline/check -->
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Kelola Kehadiran Peserta
                            </h3>
                            <div class="mt-4 max-h-60 overflow-y-auto border rounded-lg p-2 bg-gray-50">
                                @forelse($participantsList as $participant)
                                    <label class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer border-b last:border-0 border-gray-100">
                                        <input type="checkbox" 
                                               wire:model.defer="attendanceData.{{ $participant->id }}" 
                                               class="form-checkbox h-5 w-5 text-green-600 rounded focus:ring-green-500 border-gray-300 transition duration-150 ease-in-out">
                                        <div class="flex-1">
                                            <span class="text-gray-900 font-medium block">{{ $participant->name }}</span>
                                            <span class="text-gray-500 text-xs">{{ $participant->department ?? 'External' }}</span>
                                        </div>
                                        @if($attendanceData[$participant->id] ?? false)
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-bold">Hadir</span>
                                        @endif
                                    </label>
                                @empty
                                    <p class="text-gray-500 text-sm text-center py-4">Belum ada peserta terdaftar.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveAttendance" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Perubahan
                    </button>
                    <button type="button" wire:click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
