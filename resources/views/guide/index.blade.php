@extends('layouts.master')
@section('title', 'User Guide')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">User Guide / Panduan Penggunaan</h1>
            <p class="mt-2 text-lg text-gray-500">Panduan lengkap langkah demi langkah untuk menggunakan aplikasi DOORS.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sidebar Navigation (Sticky) -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-8">
                    <h3 class="font-bold text-gray-900 mb-4 px-2">Daftar Isi</h3>
                    <nav class="space-y-1">
                        <a href="#dashboard" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors">
                            <i class="fas fa-home w-5 mr-1"></i> Dashboard & Navigasi
                        </a>
                        <a href="#meeting-management" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors">
                            <i class="fas fa-sitemap w-5 mr-1"></i> Meeting Management
                        </a>
                        <a href="#booking" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors">
                            <i class="fas fa-calendar-plus w-5 mr-1"></i> Membuat Booking
                        </a>
                        <a href="#managing" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors">
                            <i class="fas fa-edit w-5 mr-1"></i> Mengelola Meeting
                        </a>
                        <a href="#attendance" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors">
                            <i class="fas fa-file-alt w-5 mr-1"></i> Absensi & Laporan
                        </a>
                        <a href="#pantry" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors">
                            <i class="fas fa-coffee w-5 mr-1"></i> Pesanan Pantry
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Content -->
            <div class="md:col-span-2 space-y-10">
                
                <!-- Section 1: Dashboard -->
                 <section id="dashboard" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center">
                        <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600 mr-3">
                            <i class="fas fa-home text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">1. Dashboard & Navigasi</h2>
                    </div>
                    <div class="p-6 prose prose-indigo max-w-none">
                        <p class="text-gray-600">Halaman Dashboard adalah pusat aktivitas Anda. Di sini Anda dapat melihat:</p>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2 list-none pl-0">
                            <li class="border rounded-lg p-3 bg-gray-50">
                                <h4 class="font-bold text-indigo-900 text-sm mb-1"><i class="fas fa-star text-yellow-400 mr-1"></i> Up Next</h4>
                                <p class="text-xs text-gray-600">Meeting yang akan segera dimulai agar Anda bersiap tepat waktu.</p>
                            </li>
                            <li class="border rounded-lg p-3 bg-gray-50">
                                <h4 class="font-bold text-indigo-900 text-sm mb-1"><i class="fas fa-search text-blue-400 mr-1"></i> Find Room</h4>
                                <p class="text-xs text-gray-600">Menu <strong>Room List</strong> untuk mencari ruangan kosong berdasarkan kapasitas dan fasilitas.</p>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Section: Meeting Management (New) -->
                <section id="meeting-management" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-purple-50 px-6 py-4 border-b border-purple-100 flex items-center">
                        <div class="bg-purple-100 p-2 rounded-lg text-purple-600 mr-3">
                            <i class="fas fa-sitemap text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">2. Menu Meeting Management</h2>
                    </div>
                    <div class="p-6 prose prose-purple max-w-none">
                        <p class="text-gray-600 mb-4">Menu utama aplikasi ada di sidebar kiri dengan nama <strong>Meeting Management</strong>. Menu ini memiliki 3 sub-menu utama:</p>
                        
                        <div class="space-y-4">
                            <!-- Submenu 1 -->
                            <div class="flex items-start">
                                <div class="bg-purple-50 rounded-lg p-3 mr-4 flex-shrink-0 w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-base">Room Reservation</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Pintu masuk untuk membooking ruangan. Di sini Anda bisa melihat tampilan Grid semua ruangan, status ketersediaan (Available/Booked), dan tombol <strong>Book Now</strong>.
                                    </p>
                                </div>
                            </div>

                            <!-- Submenu 2 -->
                            <div class="flex items-start">
                                <div class="bg-purple-50 rounded-lg p-3 mr-4 flex-shrink-0 w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-list-ul text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-base">Meeting List</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Daftar riwayat meeting Anda. Terbagi menjadi tab:
                                    </p>
                                    <ul class="list-disc pl-5 mt-1 text-sm text-gray-600">
                                        <li><strong>My Meetings:</strong> Meeting yang Anda buat (Organizer).</li>
                                        <li><strong>Invitations:</strong> Meeting di mana Anda diundang sebagai peserta.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Submenu 3 -->
                            <div class="flex items-start">
                                <div class="bg-purple-50 rounded-lg p-3 mr-4 flex-shrink-0 w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-chart-pie text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-base">Analytics</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Dashboard statistik penggunaan ruangan. Anda bisa melihat grafik Peak Hours (Jam Sibuk), ruangan paling laris, dan total durasi meeting per bulan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Booking -->
                <section id="booking" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex items-center">
                        <div class="bg-green-100 p-2 rounded-lg text-green-600 mr-3">
                            <i class="fas fa-calendar-plus text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">2. Membuat Booking Baru</h2>
                    </div>
                    <div class="p-6 prose prose-green max-w-none">
                        <div class="flex items-start mb-4">
                            <div class="bg-green-100 rounded-full p-2 mr-3 flex-shrink-0">
                                <span class="font-bold text-green-700 text-sm">Step 1</span>
                            </div>
                            <div class="mt-1">
                                <p class="text-gray-900 font-bold text-sm">Buka Menu Room Reservation</p>
                                <p class="text-gray-600 text-xs mt-1">
                                    Navigasi ke <strong>Meeting Management</strong> &rarr; <strong>Room Reservation</strong>.
                                    Di sini Anda dapat melihat status ketersediaan semua ruangan.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start mb-4">
                            <div class="bg-green-100 rounded-full p-2 mr-3 flex-shrink-0">
                                <span class="font-bold text-green-700 text-sm">Step 2</span>
                            </div>
                            <div class="mt-1">
                                <p class="text-gray-900 font-bold text-sm">Pilih Ruangan</p>
                                <p class="text-gray-600 text-xs mt-1">
                                    Cari ruangan yang 'Available' pada jam yang diinginkan, lalu klik tombol <strong>Book Now</strong>.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 rounded-full p-2 mr-3 flex-shrink-0">
                                <span class="font-bold text-green-700 text-sm">Step 3</span>
                            </div>
                            <div class="mt-1">
                                <p class="text-gray-900 font-bold text-sm">Lengkapi Detail Meeting</p>
                                <ul class="list-disc pl-5 mt-1 space-y-1 text-gray-600 text-xs">
                                    <li>Isi <strong>Meeting Details</strong> (Topik, Deskripsi).</li>
                                    <li>Tambahkan <strong>Participants</strong> (Internal/Eksternal).</li>
                                    <li>Pesan <strong>Pantry</strong> (Snack/Minuman) jika perlu.</li>
                                    <li>Klik tombol <strong>Book Meeting</strong> untuk menyimpan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Managing -->
                <section id="managing" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center">
                        <div class="bg-blue-100 p-2 rounded-lg text-blue-600 mr-3">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">3. Mengelola Meeting</h2>
                    </div>
                    <div class="p-6 prose prose-blue max-w-none">
                        <p class="text-gray-600 mb-4">Akses daftar meeting Anda di menu <strong>Meeting List > My Meetings</strong>.</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-start border-l-4 border-blue-400 pl-4 bg-blue-50/50 p-2 rounded-r-lg">
                                <i class="far fa-edit text-blue-600 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-gray-900 text-sm">Edit Meeting</strong>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Hanya tersedia jika status meeting masih <strong>Scheduled</strong>. Jika meeting sudah dimulai (Ongoing) atau selesai, Anda tidak dapat mengubah data.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start border-l-4 border-red-400 pl-4 bg-red-50/50 p-2 rounded-r-lg">
                                <i class="far fa-trash-alt text-red-600 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-gray-900 text-sm">Cancel Meeting</strong>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Membatalkan meeting akan otomatis menghapus jadwal dan mengembalikan stok pantry yang dipesan. Tombol ini tersedia kapan saja bagi pemilik meeting.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start border-l-4 border-gray-400 pl-4 bg-gray-50/50 p-2 rounded-r-lg">
                                <i class="far fa-eye text-gray-600 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-gray-900 text-sm">View Details</strong>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Melihat detail lengkap, status absensi, dan status pesanan pantry.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start border-l-4 border-indigo-400 pl-4 bg-indigo-50/50 p-2 rounded-r-lg">
                                <i class="far fa-file-excel text-indigo-600 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-gray-900 text-sm">Download Absensi</strong>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Unduh rekap kehadiran meeting dalam format Excel (.xlsx). Tombol ini tersedia di halaman daftar meeting (icon Excel) atau di dalam detail meeting.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Attendance -->
                <section id="attendance" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-teal-50 px-6 py-4 border-b border-teal-100 flex items-center">
                        <div class="bg-teal-100 p-2 rounded-lg text-teal-600 mr-3">
                            <i class="fas fa-file-alt text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">4. Absensi & Laporan</h2>
                    </div>
                    <div class="p-6 prose prose-teal max-w-none">
                        <p class="text-gray-600 mb-4">Sistem DOORS mendukung pencatatan kehadiran digital.</p>
                        <ul class="list-disc pl-5 space-y-3 text-gray-700">
                            <li>
                                <strong>Record Attendance (Catat Kehadiran):</strong><br>
                                Di halaman detail meeting, klik tombol hijau <span class="text-green-600 font-bold"><i class="far fa-id-card"></i> Record Attendance</span>. Masukkan NPK peserta untuk mencatat waktu kehadiran secara real-time.
                            </li>
                            <li>
                                <strong>Status Kehadiran:</strong><br>
                                Daftar peserta akan menampilkan badge <span class="text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded text-xs">Hadir | 09:00</span> jika sudah absen, atau <span class="text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded text-xs">Belum Hadir</span> jika belum.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Section 5: Pantry -->
                <section id="pantry" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex items-center">
                        <div class="bg-orange-100 p-2 rounded-lg text-orange-600 mr-3">
                            <i class="fas fa-mug-hot text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">5. Pesanan Pantry</h2>
                    </div>
                    <div class="p-6 prose prose-orange max-w-none">
                        <p class="text-gray-600 mb-4">Fitur ini terintegrasi langsung dengan booking ruangan.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="block text-2xl mb-2 text-yellow-500"><i class="fas fa-clock"></i></span>
                                <h4 class="font-bold text-gray-800 text-sm">Menunggu</h4>
                                <p class="text-xs text-gray-500 mt-1">Pesanan baru dibuat (Pending)</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="block text-2xl mb-2 text-blue-500"><i class="fas fa-fire-alt"></i></span>
                                <h4 class="font-bold text-gray-800 text-sm">Disiapkan</h4>
                                <p class="text-xs text-gray-500 mt-1">Pantry sedang menyiapkan (Preparing)</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="block text-2xl mb-2 text-green-500"><i class="fas fa-check-circle"></i></span>
                                <h4 class="font-bold text-gray-800 text-sm">Diantar</h4>
                                <p class="text-xs text-gray-500 mt-1">Pesanan selesai diantar (Delivered)</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4 text-center"><em>*Status pesanan diperbarui secara real-time oleh petugas Pantry/Resepsionis.</em></p>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>
@endsection
