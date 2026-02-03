@extends('layouts.master')

@section('content')
    <div class="space-y-6">

        {{-- Professional Header Card --}}
        <div
            class="bg-white p-6 rounded-3xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-400"></div> {{-- Blue
            accent for Tickets --}}

            <div class="flex flex-col md:flex-row justify-between items-center z-10 relative">
                <div>
                    <h3 class="text-gray-900 text-3xl font-black tracking-tight mb-1">Tiket Laporan AC</h3>
                    <div class="flex items-center gap-2 text-gray-500 font-medium">
                        <span
                            class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded text-xs font-bold tracking-wider">HELPDESK</span>
                        <span class="text-sm">Kelola laporan kerusakan & perbaikan.</span>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="mt-4 md:mt-0">
                    {{-- Maybe Export button later --}}
                </div>
            </div>
        </div>

        {{-- Status Tabs (As Cards) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Tab: New --}}
            <a href="{{ route('ga.tickets.index', ['tab' => 'new']) }}"
                class="group relative bg-white p-5 rounded-2xl border transition-all duration-200 {{ $statusGroup == 'new' ? 'border-blue-500 ring-1 ring-blue-500 shadow-md' : 'border-gray-100 hover:border-blue-300 shadow-sm' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1 group-hover:text-blue-600">
                            Laporan Masuk</p>
                        <h4 class="text-3xl font-black text-gray-800">{{ $countNew }}</h4>
                    </div>
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center {{ $statusGroup == 'new' ? 'bg-blue-500 text-white' : 'bg-blue-50 text-blue-500 group-hover:bg-blue-100' }}">
                        <i class="fas fa-inbox text-lg"></i>
                    </div>
                </div>
                @if($statusGroup == 'new')
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-blue-500 rounded-t-full">
                    </div>
                @endif
            </a>

            {{-- Tab: Process --}}
            <a href="{{ route('ga.tickets.index', ['tab' => 'process']) }}"
                class="group relative bg-white p-5 rounded-2xl border transition-all duration-200 {{ $statusGroup == 'process' ? 'border-yellow-500 ring-1 ring-yellow-500 shadow-md' : 'border-gray-100 hover:border-yellow-300 shadow-sm' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1 group-hover:text-yellow-600">
                            Sedang Proses</p>
                        <h4 class="text-3xl font-black text-gray-800">{{ $countProcess }}</h4>
                    </div>
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center {{ $statusGroup == 'process' ? 'bg-yellow-500 text-white' : 'bg-yellow-50 text-yellow-600 group-hover:bg-yellow-100' }}">
                        <i class="fas fa-tools text-lg"></i>
                    </div>
                </div>
                @if($statusGroup == 'process')
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-yellow-500 rounded-t-full">
                    </div>
                @endif
            </a>

            {{-- Tab: History --}}
            <a href="{{ route('ga.tickets.index', ['tab' => 'history']) }}"
                class="group relative bg-white p-5 rounded-2xl border transition-all duration-200 {{ $statusGroup == 'history' ? 'border-green-500 ring-1 ring-green-500 shadow-md' : 'border-gray-100 hover:border-green-300 shadow-sm' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1 group-hover:text-green-600">
                            Selesai / History</p>
                        <h4 class="text-3xl font-black text-gray-800">{{ $countHistory }}</h4>
                    </div>
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center {{ $statusGroup == 'history' ? 'bg-green-500 text-white' : 'bg-green-50 text-green-500 group-hover:bg-green-100' }}">
                        <i class="fas fa-history text-lg"></i>
                    </div>
                </div>
                @if($statusGroup == 'history')
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-green-500 rounded-t-full">
                    </div>
                @endif
            </a>
        </div>

        {{-- Ticket List Table --}}
        <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead
                    class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-5">Tiket Info</th>
                        <th class="px-6 py-5">Aset</th>
                        <th class="px-6 py-5">Laporan</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-6 py-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="text-xs font-mono font-bold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">#{{ substr($ticket->uuid, 0, 8) }}</span>
                                    <div class="text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="mt-1 font-bold text-gray-800 text-sm">{{ $ticket->reporter_name }}</div>
                                <div class="text-xs text-gray-500">{{ $ticket->reporter_nik }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->asset)
                                    <div class="flex items-center gap-3">
                                        @php
                                            $model = strtoupper($ticket->asset->model ?? '');
                                            $iconClass = 'far fa-snowflake';
                                            if (str_contains($model, 'CASSET'))
                                                $iconClass = 'fas fa-fan';
                                            elseif (str_contains($model, 'SPLIT'))
                                                $iconClass = 'fas fa-minus border-2 border-current rounded px-1';
                                        @endphp
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center text-sm">
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-700">{{ $ticket->asset->sku }}</div>
                                            <div class="text-xs text-gray-500">{{ $ticket->asset->location }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-red-500 text-xs italic">Asset Deleted</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block bg-red-50 text-red-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase mb-1">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->issue_category)) }}
                                </span>
                                <div class="text-sm text-gray-600 line-clamp-2 max-w-xs italic">
                                    "{{ $ticket->description ?? 'Tidak ada deskripsi' }}"
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusConf = match ($ticket->status) {
                                        'pending_validation' => ['color' => 'bg-blue-100 text-blue-700', 'label' => 'Perlu Validasi'],
                                        'open' => ['color' => 'bg-purple-100 text-purple-700', 'label' => 'Open'],
                                        'assigned' => ['color' => 'bg-yellow-100 text-yellow-700', 'label' => 'Assigned'],
                                        'in_progress' => ['color' => 'bg-orange-100 text-orange-700', 'label' => 'Dikerjakan'],
                                        'resolved' => ['color' => 'bg-green-100 text-green-700', 'label' => 'Selesai'],
                                        'closed' => ['color' => 'bg-gray-100 text-gray-700', 'label' => 'Closed'],
                                        'false_alarm' => ['color' => 'bg-red-100 text-red-700', 'label' => 'Ditolak/False'],
                                        default => ['color' => 'bg-gray-100 text-gray-700', 'label' => $ticket->status]
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusConf['color'] }}">
                                    {{ $statusConf['label'] }}
                                </span>
                                @if($ticket->technician_id)
                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-gray-500">
                                        <i class="fas fa-user-cog"></i> Tech Assigned
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('ga.tickets.show', $ticket->uuid) }}"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all shadow-sm">
                                    Detail <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-clipboard-check text-gray-300 text-2xl"></i>
                                    </div>
                                    <span class="font-bold text-gray-800">Tidak ada tiket laporan.</span>
                                    <p class="text-sm text-gray-400">Belum ada laporan kerusakan di kategori ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-6 border-t border-gray-100">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
@endsection