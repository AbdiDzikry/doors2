@extends('layouts.master')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex justify-between items-center bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('ga.tickets.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h3 class="text-gray-900 text-2xl font-bold tracking-tight">Detail Tiket</h3>
                    <span
                        class="bg-gray-100 text-gray-600 font-mono px-2 py-1 rounded text-sm font-bold">{{ $ticket->ticket_number }}</span>
                </div>
            </div>

            @php
                $statusConf = match ($ticket->status) {
                    'pending_validation' => ['color' => 'bg-blue-100 text-blue-700', 'label' => 'Perlu Validasi'],
                    'open' => ['color' => 'bg-purple-100 text-purple-700', 'label' => 'Open'],
                    'assigned' => ['color' => 'bg-yellow-100 text-yellow-700', 'label' => 'Assigned'],
                    'in_progress' => ['color' => 'bg-orange-100 text-orange-700', 'label' => 'Dikerjakan'],
                    'resolved' => ['color' => 'bg-green-100 text-green-700', 'label' => 'Selesai'],
                    'closed' => ['color' => 'bg-gray-100 text-gray-700', 'label' => 'Closed'],
                    'false_alarm' => ['color' => 'bg-red-100 text-red-700', 'label' => 'Ditolak'],
                    default => ['color' => 'bg-gray-100 text-gray-700', 'label' => $ticket->status]
                };
            @endphp
            <span class="px-4 py-2 rounded-xl font-bold {{ $statusConf['color'] }}">
                {{ $statusConf['label'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Issue Detail --}}
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Informasi Laporan</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Pelapor</span>
                            <div class="font-bold text-gray-800">{{ $ticket->reporter_name }}</div>
                            <div class="text-sm text-gray-500">NIK: {{ $ticket->reporter_nik ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Kategori
                                Masalah</span>
                            <span
                                class="inline-block bg-red-50 text-red-600 px-3 py-1 rounded-lg text-sm font-bold uppercase">
                                {{ ucfirst(str_replace('_', ' ', $ticket->issue_category)) }}
                            </span>
                        </div>
                        <div class="col-span-full">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Deskripsi
                                Masalah</span>
                            <div class="p-4 bg-gray-50 rounded-xl text-gray-700 italic border border-gray-100">
                                "{{ $ticket->description ?? 'Tidak ada deskripsi detail.' }}"
                            </div>
                        </div>
                        <div class="col-span-full">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Bukti
                                Foto</span>
                            @if($ticket->evidence_photo_path)
                                <img src="{{ asset('storage/' . $ticket->evidence_photo_path) }}"
                                    class="rounded-xl max-h-64 object-cover border border-gray-200">
                            @else
                                <div
                                    class="h-24 bg-gray-50 rounded-xl border border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-sm">
                                    <i class="fas fa-image mr-2"></i> Tidak ada foto dilampirkan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Asset Card --}}
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex justify-between items-center">
                        Aset Terkait
                        <a href="{{ route('ga.assets.index', ['search' => $ticket->asset->sku]) }}" target="_blank"
                            class="text-xs text-blue-500 hover:underline">Lihat Aset</a>
                    </h4>

                    @if($ticket->asset)
                        <div class="flex items-center gap-4 mb-4">
                            @php
                                $model = strtoupper($ticket->asset->model ?? '');
                                $iconClass = 'far fa-snowflake';
                                if (str_contains($model, 'CASSET'))
                                    $iconClass = 'fas fa-fan';
                                elseif (str_contains($model, 'SPLIT'))
                                    $iconClass = 'fas fa-minus border-2 border-current rounded px-1';
                            @endphp
                            <div
                                class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center shadow-sm">
                                <i class="{{ $iconClass }} text-2xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800">{{ $ticket->asset->name }}</div>
                                <div class="text-sm text-gray-500 font-mono">{{ $ticket->asset->sku }}</div>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-50">
                                <span class="text-gray-500">Lokasi</span>
                                <span class="font-bold text-gray-800">{{ $ticket->asset->location }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-50">
                                <span class="text-gray-500">Model</span>
                                <span class="font-bold text-gray-800">{{ $ticket->asset->model }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-500">Status Aset</span>
                                <span
                                    class="uppercase font-bold {{ $ticket->asset->status == 'good' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $ticket->asset->status }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-red-500 font-bold text-center py-4">Data Aset Terhapus</div>
                    @endif
                </div>

                {{-- Action Panel --}}
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100"
                    x-data="{ showAssignModal: false, showResolveModal: false }">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Tindakan Admin</h4>

                    @if($ticket->status == 'pending_validation')
                        <div class="space-y-3">
                            <form action="{{ route('ga.tickets.validate', $ticket->uuid) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin memvalidasi tiket ini? Status akan berubah menjadi OPEN.')"
                                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-200 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i> Validasi Laporan
                                </button>
                            </form>

                            <form action="{{ route('ga.tickets.reject', $ticket->uuid) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Tolak laporan ini as False Alarm?')"
                                    class="w-full py-3 bg-white border border-red-200 text-red-600 hover:bg-red-50 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i> Tolak Laporan
                                </button>
                            </form>
                        </div>
                    @elseif($ticket->status == 'open')
                        <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-100 text-yellow-800 text-sm mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Laporan tervalidasi. Silakan tugaskan teknisi.
                        </div>
                        <button @click="showAssignModal = true"
                            class="w-full py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-bold shadow-lg transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-user-plus"></i> Assign Teknisi
                        </button>
                    @elseif(in_array($ticket->status, ['assigned', 'in_progress']))
                        <div class="mb-4">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Teknisi
                                Bertugas</span>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                    {{ substr($ticket->technician->name ?? 'T', 0, 1) }}
                                </div>
                                <span class="font-bold text-gray-800">{{ $ticket->technician->name ?? 'Teknisi' }}</span>
                            </div>
                        </div>

                        <button @click="showResolveModal = true"
                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-lg shadow-green-200 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-check-double"></i> Selesaikan Tiket
                        </button>
                    @elseif($ticket->status == 'resolved')
                        <div class="p-4 bg-green-50 rounded-xl border border-green-100 text-green-800 text-sm mb-3">
                            <i class="fas fa-check-circle mr-1"></i> Tiket telah diselesaikan pada
                            {{ $ticket->resolved_at ? $ticket->resolved_at->format('d M Y H:i') : '-' }}.
                        </div>
                        <div class="mt-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Catatan
                                Penyelesaian</span>
                            <p class="text-gray-700 italic">{{ $ticket->resolution_notes }}</p>
                        </div>
                    @else
                        <div class="text-center text-gray-500 text-sm">
                            <i class="fas fa-lock mr-1"></i> Tidak ada tindakan tersedia (Status: {{ $ticket->status }}).
                        </div>
                    @endif

                    {{-- Assign Modal --}}
                    <div x-show="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div
                            class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity" aria-hidden="true"
                                @click="showAssignModal = false">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form action="{{ route('ga.tickets.assign', $ticket->uuid) }}" method="POST">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Pilih
                                            Teknisi</h3>
                                        <div class="mt-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Teknisi</label>
                                            <select name="technician_id"
                                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                @foreach($technicians as $tech)
                                                    <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                            Assign
                                        </button>
                                        <button type="button" @click="showAssignModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Resolve Modal --}}
                    <div x-show="showResolveModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div
                            class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity" aria-hidden="true"
                                @click="showResolveModal = false">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form action="{{ route('ga.tickets.resolve', $ticket->uuid) }}" method="POST">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900">Selesaikan Tiket</h3>
                                        <div class="mt-4 space-y-4">
                                            <div>
                                                <label class="block text-gray-700 text-sm font-bold mb-2">Catatan
                                                    Perbaikan</label>
                                                <textarea name="resolution_notes" rows="3"
                                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                    required placeholder="Jelaskan apa yang diperbaiki..."></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 text-sm font-bold mb-2">Biaya Perbaikan
                                                    (Estimasi)</label>
                                                <input type="number" name="repair_cost"
                                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                            Resolve Ticket
                                        </button>
                                        <button type="button" @click="showResolveModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection