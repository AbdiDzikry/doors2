<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">Konfirmasi Kehadiran</h2>
        <p class="text-gray-600 text-sm">Mohon pastikan data diri Anda benar.</p>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
        <div class="mb-2">
            <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Nama Tamu</span>
            <div class="text-lg font-bold text-gray-900">
                @if($participant->participant)
                    {{ $participant->participant->name }}
                @else
                    {{ $participant->email ?? 'Tamu External' }}
                @endif
            </div>
            <div class="text-sm text-gray-600">
                {{ $participant->participant->department ?? 'External Guest' }}
            </div>
        </div>

        <hr class="my-3 border-gray-200">

        <div class="mb-2">
            <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Meeting</span>
            <div class="font-medium text-gray-900">{{ $meeting->topic }}</div>
        </div>

        <div class="mb-2">
            <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Ruangan</span>
            <div class="font-medium text-gray-900">{{ $meeting->room->name ?? 'TBA' }}</div>
        </div>
        
        <div class="mb-2">
            <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Waktu</span>
            <div class="font-medium text-gray-900">
                {{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}
            </div>
        </div>
        
        <div class="mb-2">
            <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Organizer</span>
            <div class="font-medium text-gray-900">{{ $meeting->organizer->name ?? '-' }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('kiosk.attend') }}">
        @csrf
        <input type="hidden" name="participant_id" value="{{ $participant->id }}">
        <input type="hidden" name="code" value="{{ $participant->checkin_code }}">

        <div class="flex flex-col gap-3">
            <x-primary-button class="w-full justify-center py-3 text-lg bg-green-600 hover:bg-green-700">
                {{ __('Ya, Check-In Sekarang') }}
            </x-primary-button>
            
            <a href="{{ route('kiosk.index') }}" class="w-full text-center py-3 text-gray-600 hover:text-gray-800 text-sm underline">
                Bukan saya, kembali
            </a>
        </div>
    </form>
</x-guest-layout>
