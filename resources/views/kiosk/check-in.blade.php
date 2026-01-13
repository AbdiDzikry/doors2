<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-800">Self Check-In</h2>
        <p class="text-gray-600 text-sm">Masukkan kode unik yang tertera pada undangan Anda.</p>
    </div>

    <!-- Error/Success Messages -->
    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('kiosk.verify') }}">
        @csrf

        <!-- Code Input -->
        <div class="mt-4">
            <x-input-label for="code" :value="__('Kode Check-In')" />
            <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-widest uppercase" 
                            type="text" 
                            name="code" 
                            :value="old('code')" 
                            required autofocus autocomplete="off"
                            placeholder="ABCD12" 
                            maxlength="6" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="w-full justify-center py-3 text-lg">
                {{ __('Proses Check-In') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
