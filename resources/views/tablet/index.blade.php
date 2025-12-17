<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Doors') }} - Tablet Mode</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50">
    <div class="min-h-screen p-6 sm:p-10">
        <!-- Header -->
        <header class="max-w-7xl mx-auto flex justify-between items-center mb-10">
            <div>
                <x-application-logo class="w-16 h-16 fill-current text-gray-800" />
            </div>
            <div class="text-right">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Tablet Display Mode</h1>
                <p class="text-gray-500 text-sm mt-1">Pilih Ruangan untuk memulai Kiosk</p>
            </div>
        </header>

        <!-- Main Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($rooms as $room)
                <a href="{{ route('tablet.show', $room->id) }}" class="group block h-full">
                    <div class="bg-white rounded-3xl shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden transform group-hover:-translate-y-2 border border-gray-100 flex flex-col h-full">
                        <div class="h-48 bg-gray-200 relative overflow-hidden">
                            <img src="{{ $room->image_path ? route('master.rooms.image', basename($room->image_path)) : 'https://placehold.co/600x400/e2e8f0/64748b?text=' . urlencode($room->name) }}" 
                                 alt="{{ $room->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                            
                            <div class="absolute bottom-4 left-4 text-white">
                                <span class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold border border-white/30">
                                    Lantai {{ $room->floor }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6 flex-grow flex flex-col justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 group-hover:text-[#089244] transition-colors mb-2 leading-tight">
                                    {{ $room->name }}
                                </h2>
                                <p class="text-gray-500 text-sm">Kapasitas {{ $room->capacity }} Orang</p>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">TAP TO START</span>
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-[#089244] group-hover:text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($rooms->isEmpty())
            <div class="max-w-2xl mx-auto text-center py-20">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Belum ada ruangan</h3>
                <p class="mt-1 text-gray-500">Silakan tambahkan data ruangan melalui menu Master Data.</p>
            </div>
        @endif

        <!-- Footer Actions -->
        <div class="max-w-7xl mx-auto mt-12 text-center border-t border-gray-200 pt-8">
            <form method="POST" action="{{ route('logout') }}" class="inline-block">
                @csrf
                <button type="submit" class="flex items-center text-sm text-red-600 hover:text-red-800 hover:bg-red-50 px-4 py-2 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar dari Mode Tablet
                </button>
            </form>
        </div>
    </div>
</body>
</html>
