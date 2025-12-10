<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking - Cari NPK</title>
    {{-- Asumsi mix('css/app.css') sudah memuat Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* small extra tweaks for tablet-centered layout */
        .tablet-wrap { max-width: 900px; margin: 1.5rem auto; }
        
        /* Tambahkan transisi halus untuk interaksi */
        .transition-all-200 { transition: all 200ms ease-in-out; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="tablet-wrap px-4">
    
    <div class="bg-white shadow-xl rounded-2xl p-8 transform hover:shadow-2xl transition-all-200">
        
        <header class="mb-6 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-800 flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Booking Rapat
            </h1>
            <p class="text-sm text-gray-500 mt-1">Cari dan pilih PIC booking tanpa perlu login.</p>
        </header>

        <div class="mb-6">
            <label for="searchInput" class="block text-sm font-semibold text-gray-700 mb-2">
                Cari NPK / Nama Karyawan
            </label>
            <div class="relative">
                <input 
                    id="searchInput" 
                    type="search" 
                    placeholder="Masukkan NPK atau nama lengkap" 
                    class="mt-1 block w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all-200" 
                    autofocus
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>

        <div id="results" class="space-y-3">
            <div class="text-gray-400 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                Ketikkan minimal 3 karakter untuk mulai mencari...
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-100">
            <div class="p-3 bg-indigo-50 rounded-lg flex items-start">
                <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <p class="ml-3 text-sm text-indigo-800">
                    <strong>Catatan:</strong> Data NPK yang Anda pilih akan disimpan sementara di sesi, lalu Anda akan diarahkan ke halaman detail booking.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Skrip JavaScript Tetap Sama --}}
<script>
    (function () {
        const searchInput = document.getElementById('searchInput');
        const resultsEl = document.getElementById('results');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const params = new URLSearchParams(window.location.search);
        const roomId = params.get('room_id');

        let debounceTimer = null;
        
        // Initial placeholder state
        resultsEl.innerHTML = '<div class="text-gray-400 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-200">Ketikkan minimal 3 karakter untuk mulai mencari...</div>';


        function renderResults(items) {
            resultsEl.innerHTML = '';
            if (!items || items.length === 0) {
                resultsEl.innerHTML = '<div class="text-gray-500 p-3 bg-red-50 rounded-lg border border-red-200 font-medium">❌ Tidak ada hasil yang ditemukan.</div>';
                return;
            }

            items.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                // Styling Hasil Pencarian yang Ditingkatkan
                btn.className = 'w-full text-left p-4 bg-white border border-gray-200 rounded-xl hover:bg-indigo-50 hover:border-indigo-300 transition-all-200 flex justify-between items-center group shadow-sm';
                
                btn.innerHTML = `
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm mr-4 flex-shrink-0">
                            ${escapeHtml(item.name.charAt(0).toUpperCase())}
                        </div>
                        <div>
                            <div class="font-bold text-gray-800">${escapeHtml(item.name)}</div>
                            <div class="text-xs text-gray-500">NPK: ${escapeHtml(item.npk)}</div>
                        </div>
                    </div>
                    <div class="text-sm text-indigo-600 font-semibold flex items-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        Pilih
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                `;
                
                btn.addEventListener('click', function () {
                    selectNpk(item.npk);
                });
                resultsEl.appendChild(btn);
            });
        }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"'\/]/g, function (c) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;','/':'\/'}[c]); });
        }

        function search(q) {
            if (!q || q.length < 3) {
                 resultsEl.innerHTML = '<div class="text-gray-400 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-200">Ketikkan minimal 3 karakter untuk mulai mencari...</div>';
                 return; 
            }
            
            // Tampilkan loading state
            resultsEl.innerHTML = '<div class="p-3 text-center text-indigo-600"><svg class="animate-spin h-5 w-5 mr-3 inline-block" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sedang mencari...</div>';


            fetch(`/user-booking/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    renderResults(data);
                })
                .catch(err => {
                    console.error(err);
                    resultsEl.innerHTML = '<div class="p-3 text-red-600 bg-red-100 rounded-lg border border-red-300">⚠️ Terjadi kesalahan saat mencari data. Coba lagi.</div>';
                });
        }

        function selectNpk(npk) {
            // ... (Fungsi selectNpk tetap sama)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/user-booking/select';
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrfToken;
            const npkInput = document.createElement('input');
            npkInput.type = 'hidden';
            npkInput.name = 'npk';
            npkInput.value = npk;
            form.appendChild(token);
            form.appendChild(npkInput);
            if (roomId) {
                const r = document.createElement('input');
                r.type = 'hidden';
                r.name = 'room_id';
                r.value = roomId;
                form.appendChild(r);
            }
            document.body.appendChild(form);
            form.submit();
        }

        searchInput.addEventListener('input', function (e) {
            const q = e.target.value.trim();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                search(q);
            }, 300);
        });

        // initial focus and optional pre-search if query present in URL
        const initialQ = params.get('q');
        if (initialQ) {
            searchInput.value = initialQ;
            search(initialQ);
        }
    })();
</script>
</body>
</html>