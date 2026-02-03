@extends('layouts.master')

@section('content')
    <div class="space-y-6">

        {{-- Professional Header Card --}}
        <div class="bg-white p-6 rounded-3xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-gray-100 flex flex-col md:flex-row justify-between items-center relative overflow-hidden">
            {{-- Green Accent Line top --}}
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-green-400"></div>

            <div class="z-10">
                <h3 class="text-gray-900 text-3xl font-black tracking-tight mb-1">Data Aset AC</h3>
                <div class="flex items-center gap-2 text-gray-500 font-medium">
                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-bold tracking-wider">GA SYSTEM</span>
                    <span class="text-sm">General Affair Asset Management</span>
                </div>
            </div>

            <div class="flex items-center gap-8 mt-6 md:mt-0 z-10">
                {{-- Stats --}}
                <div class="text-right hidden md:block">
                    <span class="block text-4xl font-black text-gray-800 leading-none">{{ $totalAssets }}</span>
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">TOTAL UNIT</span>
                </div>

                {{-- Divider --}}
                <div class="h-10 w-px bg-gray-200 hidden md:block"></div>

                {{-- View Toggle --}}
                <div class="flex bg-gray-50 p-1.5 rounded-xl border border-gray-100">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="w-10 h-10 flex items-center justify-center rounded-lg transition-all {{ $viewMode == 'list' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-gray-400 hover:text-gray-600' }}">
                        <i class="fas fa-list"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'visual']) }}" class="w-10 h-10 flex items-center justify-center rounded-lg transition-all {{ $viewMode == 'visual' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-gray-400 hover:text-gray-600' }}">
                        <i class="fas fa-th-large"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Bar (Custom Dropdowns) --}}
        <div class="bg-white px-6 py-4 rounded-[20px] shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center">

            <form action="{{ route('ga.assets.index') }}" method="GET" id="filterForm" class="contents w-full">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                {{-- Search --}}
                <div class="relative flex-grow w-full md:w-auto group">
                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full pl-10 pr-4 py-2.5 bg-transparent border-0 ring-1 ring-gray-200 focus:ring-2 focus:ring-primary rounded-xl text-gray-700 placeholder-gray-400 text-sm transition-all"
                           placeholder="Cari SKU / Aset...">
                </div>

                {{-- Custom Location Dropdown --}}
                <div class="relative w-full md:w-56" x-data="{ open: false, selected: '{{ request('location') }}' }">
                    <button @click="open = !open" type="button" class="w-full bg-white px-4 py-2.5 rounded-xl border-0 ring-1 ring-gray-200 hover:ring-gray-300 focus:ring-2 focus:ring-primary text-left flex items-center justify-between transition-all group">
                        <span class="flex items-center gap-2 text-sm text-gray-700 truncate">
                            <i class="fas fa-map-marker-alt text-gray-400 group-hover:text-primary transition-colors"></i>
                            <span x-text="selected ? selected : 'Semua Lokasi'"></span>
                        </span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    <input type="hidden" name="location" :value="selected">

                    <div x-show="open" @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute z-50 mt-2 w-full bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 py-1 focus:outline-none max-h-60 overflow-auto">

                        <div @click="selected = ''; open = false; document.getElementById('filterForm').submit()" class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary flex items-center gap-2">
                             <span>📍 Semua Lokasi</span>
                        </div>
                        @foreach ($locations as $loc)
                            <div @click="selected = '{{ $loc }}'; open = false; document.getElementById('filterForm').submit()" 
                                 class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary flex items-center gap-2 {{ request('location') == $loc ? 'bg-primary/5 text-primary font-bold' : '' }}">
                                {{ $loc }}
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Custom Status Dropdown --}}
                <div class="relative w-full md:w-56" x-data="{ open: false, selected: '{{ request('status') }}' }">
                    <button @click="open = !open" type="button" class="w-full bg-white px-4 py-2.5 rounded-xl border-0 ring-1 ring-gray-200 hover:ring-gray-300 focus:ring-2 focus:ring-primary text-left flex items-center justify-between transition-all group">
                        <span class="flex items-center gap-2 text-sm text-gray-700 truncate">
                            <i class="fas fa-bolt text-gray-400 group-hover:text-primary transition-colors"></i>
                            <span x-text="selected ? (selected.charAt(0).toUpperCase() + selected.slice(1).replace('_', ' ')) : 'Semua Status'"></span>
                        </span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    <input type="hidden" name="status" :value="selected">

                    <div x-show="open" @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute z-50 mt-2 w-full bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 py-1 focus:outline-none">

                        <div @click="selected = ''; open = false; document.getElementById('filterForm').submit()" class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary flex items-center gap-2">
                             <span>⚡ Semua Status</span>
                        </div>
                        @foreach ($statuses as $stat)
                            <div @click="selected = '{{ $stat }}'; open = false; document.getElementById('filterForm').submit()" 
                                 class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary flex items-center gap-2 {{ request('status') == $stat ? 'bg-primary/5 text-primary font-bold' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $stat)) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </form>

            <div class="ml-auto border-l border-gray-200 pl-4 hidden md:flex items-center gap-4 text-xs font-semibold text-gray-500">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm"></span> Normal</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm"></span> Rusak</div>
            </div>
        </div>

        {{-- Content --}}
        @if($viewMode == 'visual')
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-20">
                @forelse ($assets as $location => $items)
                    <div class="bg-white rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow duration-300 border border-gray-100">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-50 pb-4">
                            <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                {{ $location }}
                            </h4>
                            <span class="bg-gray-100 text-gray-600 text-[10px] font-extrabold px-3 py-1 rounded-full">{{ $items->count() }} UNIT</span>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            @foreach ($items as $asset)
                                @php
                                    $theme = match ($asset->status) {
                                        'good' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'icon' => 'text-green-600', 'ring' => 'ring-green-100'],
                                        'broken' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => 'text-red-600', 'ring' => 'ring-red-100'],
                                        'needs_repair' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'icon' => 'text-yellow-600', 'ring' => 'ring-yellow-100'],
                                        'maintenance' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'icon' => 'text-yellow-600', 'ring' => 'ring-yellow-100'],
                                        default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'icon' => 'text-gray-400', 'ring' => 'ring-gray-100']
                                    };

                                    // Icon Logic
                                    $model = strtoupper($asset->model ?? '');
                                    if (str_contains($model, 'CASSET')) {
                                        $iconClass = 'fas fa-fan';
                                        $labelModel = 'Cassette';
                                    } elseif (str_contains($model, 'SPLIT')) {
                                        $iconClass = 'fas fa-minus text-xl border-2 border-current rounded px-1';
                                        $labelModel = 'Split Wall';
                                    } else {
                                        $iconClass = 'far fa-snowflake';
                                        $labelModel = $asset->model;
                                    }
                                @endphp
                                <a href="{{ route('ga.report.show', $asset->uuid) }}" target="_blank" 
                                   class="group flex flex-col items-center justify-center p-3 rounded-2xl {{ $theme['bg'] }} ring-1 {{ $theme['ring'] }} hover:ring-2 hover:ring-primary/20 hover:shadow-lg transition-all cursor-pointer relative overflow-hidden h-28">

                                    <div class="mb-3 {{ $theme['icon'] }} transform group-hover:scale-110 transition-transform duration-300 flex items-center justify-center h-10 w-10">
                                        <i class="{{ $iconClass }}"></i>
                                    </div>

                                    <span class="text-[11px] font-bold {{ $theme['text'] }} truncate max-w-full tracking-tight">{{ $asset->sku }}</span>

                                    {{-- Hover Tooltip --}}
                                    <div class="absolute inset-x-0 bottom-0 bg-white/90 backdrop-blur text-gray-700 text-[10px] font-bold text-center py-1 opacity-0 group-hover:opacity-100 transition-all translate-y-full group-hover:translate-y-0">
                                        {{ $labelModel }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-4">
                            <i class="fas fa-search text-gray-300 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Aset tidak ditemukan</h3>
                        <p class="text-gray-500 text-sm">Coba ubah filter pencarian Anda.</p>
                        <a href="{{ route('ga.assets.index') }}" class="mt-4 inline-block text-primary text-sm font-bold hover:underline">Reset Semua Filter</a>
                    </div>
                @endforelse
            </div>
        @else
            {{-- List Mode (Table) --}}
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-5">Info Aset</th>
                            <th class="px-6 py-5">Lokasi</th>
                            <th class="px-6 py-5">Status Unit</th>
                            <th class="px-6 py-5 text-right">Menu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($assets as $asset)
                            @php
                                $model = strtoupper($asset->model ?? '');
                                if (str_contains($model, 'CASSET')) {
                                    $iconClass = 'fas fa-fan';
                                } elseif (str_contains($model, 'SPLIT')) {
                                    $iconClass = 'fas fa-minus border-2 border-current rounded px-1';
                                } else {
                                    $iconClass = 'far fa-snowflake';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-primary/5 text-primary flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:text-white transition-colors duration-300 shadow-sm">
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm group-hover:text-primary transition-colors">{{ $asset->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono mt-0.5 bg-gray-100 px-1.5 py-0.5 rounded inline-block">{{ $asset->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-white border border-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm flex items-center w-fit gap-2">
                                        <i class="fas fa-map-marker-alt text-gray-300"></i>
                                        {{ $asset->location }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClass = match ($asset->status) {
                                            'good' => 'bg-green-50 text-green-700 ring-1 ring-green-600/20',
                                            'broken' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
                                            'needs_repair' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
                                            default => 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20'
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('ga.assets.qr', $asset->uuid) }}" target="_blank" 
                                       class="text-gray-400 hover:text-white hover:bg-primary bg-white border border-gray-200 h-9 w-9 rounded-xl inline-flex items-center justify-center transition-all shadow-sm hover:shadow-md hover:border-primary">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-6 border-t border-gray-100">
                    {{ $assets->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection