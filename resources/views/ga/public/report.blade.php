<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lapor Kerusakan AC - {{ config('app.name', 'Doors') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        {{-- Logo / Branding --}}
        <div class="w-full sm:max-w-md mt-6 px-6 py-4">
            <div class="flex flex-col items-center">
                {{-- <x-application-logo class="w-20 h-20 fill-current text-gray-500" /> --}}
                <h1 class="text-2xl font-bold text-gray-800">Lapor Kerusakan AC</h1>
                <p class="text-sm text-gray-500 mt-1">General Affair Service System</p>
            </div>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg border border-gray-100">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded relative"
                    role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <a href="" class="block mt-2 text-sm underline text-green-700">Lapor lagi</a>
                </div>
            @else
                {{-- Asset Info Card --}}
                <div class="bg-blue-50 border border-blue-100 rounded-md p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Detail Aset</h3>
                            <div class="mt-1 text-sm text-blue-700">
                                <p><strong>Unit:</strong> {{ $asset->name }}</p>
                                <p><strong>Lokasi:</strong> {{ $asset->location }}</p>
                                <p class="text-xs mt-1 text-blue-500">SKU: {{ $asset->sku }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reporting Form --}}
                <form method="POST" action="{{ route('ga.report.store', $asset->uuid) }}">
                    @csrf

                    {{-- Nama Pelapor --}}
                    <div>
                        <x-input-label for="reporter_name" :value="__('Nama Pelapor')" />
                        <x-text-input id="reporter_name" class="block mt-1 w-full" type="text" name="reporter_name"
                            :value="old('reporter_name')" required autofocus />
                        <x-input-error :messages="$errors->get('reporter_name')" class="mt-2" />
                    </div>

                    {{-- NIK --}}
                    <div class="mt-4">
                        <x-input-label for="reporter_nik" :value="__('NIK / NPK')" />
                        <x-text-input id="reporter_nik" class="block mt-1 w-full" type="text" name="reporter_nik"
                            :value="old('reporter_nik')" required />
                        <x-input-error :messages="$errors->get('reporter_nik')" class="mt-2" />
                    </div>

                    {{-- Issue Category --}}
                    <div class="mt-4">
                        <x-input-label for="issue_category" :value="__('Kategori Masalah')" />
                        <select id="issue_category" name="issue_category"
                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Pilih Masalah --</option>
                            <option value="not_cold">AC Tidak Dingin / Panas</option>
                            <option value="leaking">Bocor Air / Menetes</option>
                            <option value="noisy">Suara Berisik / Aneh</option>
                            <option value="smell">Bau Tidak Sedap</option>
                            <option value="dead">Mati Total</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <x-input-error :messages="$errors->get('issue_category')" class="mt-2" />
                    </div>

                    {{-- Description --}}
                    <div class="mt-4">
                        <x-input-label for="description" :value="__('Keterangan Tambahan')" />
                        <textarea id="description" name="description" rows="3"
                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-primary-button class="w-full justify-center py-3">
                            {{ __('Kirim Laporan') }}
                        </x-primary-button>
                    </div>
                </form>
            @endif
        </div>

        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} PT Dharma Polimetal Tbk. <br>GA Asset Management System v1.0
        </div>
    </div>
</body>

</html>