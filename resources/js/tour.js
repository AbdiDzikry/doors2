import { driver } from "driver.js";
import "driver.js/dist/driver.css";
import axios from 'axios';

// Helper to check if user is admin based on page content
// (e.g. presence of Division filter which only Admins see)
const isAdminView = () => !!document.querySelector('input[name="division"]');

// Tour Configuration for different pages
const getTourConfig = (path) => {
    // 1. Dashboard (Main)
    if (path === '/dashboard' || (path.includes('/dashboard') && !path.includes('/receptionist') && !path.includes('/meeting'))) {
        return {
            key: 'tour_seen_dashboard',
            useGlobalStatus: true,
            steps: [
                {
                    popover: {
                        title: '👋 Selamat Datang di Doors!',
                        description: 'Aplikasi ini memudahkan Anda untuk melakukan reservasi ruangan meeting dan melihat jadwal terkini.',
                        side: "center", align: 'center'
                    }
                },
                {
                    element: '#nav-dashboard',
                    popover: {
                        title: '📊 Dashboard Utama',
                        description: 'Ringkasan aktivitas Anda hari ini, meeting yang akan datang, dan status ruangan.',
                        side: "right", align: 'start'
                    }
                },
                {
                    element: '#nav-meeting-mgmt',
                    popover: {
                        title: '📅 Manajemen Meeting',
                        description: 'Menu untuk <b>Booking Ruangan</b>, melihat <b>Daftar Meeting</b>, dan <b>Analytics</b>.',
                        side: "right", align: 'start'
                    }
                },
                {
                    element: '#nav-profile-dropdown',
                    popover: {
                        title: '👤 Profil Anda',
                        description: 'Akses menu Profil atau Logout dari sini.',
                        side: "left", align: 'start'
                    }
                },
                {
                    popover: {
                        title: '🎉 Selesai!',
                        description: 'Anda siap menggunakan Doors. Selamat bekerja!',
                        side: "center", align: 'center'
                    }
                },
            ]
        };
    }

    // 2. Receptionist Dashboard
    if (path.includes('/dashboard/receptionist')) {
        return {
            key: 'tour_seen_receptionist',
            useGlobalStatus: false,
            steps: [
                {
                    popover: {
                        title: '🛎️ Dashboard Resepsionis',
                        description: 'Kelola pesanan konsumsi dan pantau status ruangan dari sini.',
                        side: "center", align: 'center'
                    }
                }
            ]
        };
    }

    // 3. Booking Creation Page
    if (path.includes('/meeting/bookings/create')) {
        return {
            key: 'tour_seen_booking_create',
            useGlobalStatus: false,
            steps: [
                {
                    element: '#tour-room-selection',
                    popover: {
                        title: '1. Pilih Ruangan',
                        description: 'Pilih ruangan yang tersedia. Cek kapasitas dan fasilitas di panel detail.',
                        side: "bottom", align: 'start'
                    }
                },
                {
                    element: '#tour-datetime',
                    popover: {
                        title: '2. Waktu & Tanggal',
                        description: 'Tentukan kapan meeting akan berlangsung. Sistem akan mengecek ketersediaan.',
                        side: "top", align: 'start'
                    }
                },
                {
                    element: '#tour-participants',
                    popover: {
                        title: '3. Tambah Peserta',
                        description: 'Undang rekan kerja atau tamu eksternal. Undangan akan dikirim via email.',
                        side: "top", align: 'start'
                    }
                },
                {
                    element: '#tour-pantry',
                    popover: {
                        title: '4. Pesan Konsumsi',
                        description: 'Pesan snack atau minuman dari Pantry jika diperlukan.',
                        side: "top", align: 'start'
                    }
                },
                {
                    element: '#tour-submit',
                    popover: {
                        title: '✅ Kirim Booking',
                        description: 'Klik tombol ini untuk menyelesaikan reservasi Anda.',
                        side: "top", align: 'end'
                    }
                }
            ]
        };
    }

    // 4. Room Reservations List
    if (path.includes('/meeting/room-reservations')) {
        const params = new URLSearchParams(window.location.search);
        const isTimeline = params.get('view') === 'timeline';

        if (isTimeline) {
            return {
                key: 'tour_seen_room_res_timeline',
                useGlobalStatus: false,
                steps: [
                    {
                        element: '#tour-search-filter',
                        popover: {
                            title: '🔍 Filter & Navigasi',
                            description: 'Ganti tanggal atau cari ruangan tertentu lewat panel ini.',
                            side: "bottom", align: 'start'
                        }
                    },
                    {
                        element: '#tour-timeline-header',
                        popover: {
                            title: '⏰ Garis Waktu',
                            description: 'Panel atas menunjukkan jam (07:00 - 19:00). Garis merah menandakan waktu saat ini.',
                            side: "bottom", align: 'start'
                        }
                    },
                    {
                        element: '#tour-timeline-rooms',
                        popover: {
                            title: '🏠 Daftar Ruangan',
                            description: 'Daftar ruangan ada di sisi kiri dan akan tetap terlihat (sticky) saat Anda menggeser timeline.',
                            side: "right", align: 'start'
                        }
                    },
                    {
                        element: '#tour-timeline-grid',
                        popover: {
                            title: '🖱️ Klik untuk Booking',
                            description: 'Klik di area kosong pada baris ruangan yang diinginkan untuk langsung membuat reservasi di jam tersebut.',
                            side: "top", align: 'center'
                        }
                    },
                    {
                        element: '#tour-timeline-legend',
                        popover: {
                            title: '🎨 Status Warna',
                            description: 'Pahami arti warna kotak meeting: <br>🟨 Kuning (Scheduled)<br>🟩 Hijau (Completed)<br>⬜ Putih (Available)',
                            side: "bottom", align: 'end'
                        }
                    }
                ]
            };
        }

        // Default: Grid View
        return {
            key: 'tour_seen_room_res',
            useGlobalStatus: false,
            steps: [
                {
                    element: '#tour-search-filter',
                    popover: {
                        title: '🔍 Pencarian & Filter',
                        description: 'Cari ruangan berdasarkan nama, atau filter berdasarkan status ketersediaan (Available, In Use, Maintenance).',
                        side: "bottom", align: 'start'
                    }
                },
                {
                    element: '#tour-room-card',
                    popover: {
                        title: '🏠 Kartu Ruangan',
                        description: 'Setiap kartu menampilkan foto ruangan, kapasitas, dan status terkini. Klik "Book Now" untuk reservasi.',
                        side: "top", align: 'start'
                    }
                }
            ]
        };
    }

    // 5. Analytics (Dynamic Content)
    if (path.includes('/meeting/analytics')) {
        const admin = isAdminView();
        return {
            key: 'tour_seen_analytics',
            useGlobalStatus: false,
            steps: [
                {
                    element: '#tour-analytics-filter',
                    popover: {
                        title: admin ? '📅 Filter Data Global' : '📅 Filter Data Saya',
                        description: admin
                            ? 'Sebagai Admin, Anda bisa memfilter data berdasarkan <b>Divisi</b> atau <b>Departemen</b> untuk melihat tren organisasi.'
                            : 'Lihat ringkasan aktivitas meeting Anda dalam periode waktu tertentu (Mingguan, Bulanan, atau Custom).',
                        side: "bottom", align: 'start'
                    }
                },
                {
                    element: '#tour-busy-hours',
                    popover: {
                        title: admin ? '📊 Jam Sibuk Kantor' : '📊 Jam Sibuk Anda',
                        description: admin
                            ? 'Lihat kapan waktu penggunaan ruangan paling tinggi di seluruh kantor.'
                            : 'Grafik ini menunjukkan pola waktu meeting Anda dalam sehari. Berguna untuk melihat produktivitas atau beban kerja harian Anda.',
                        side: "top", align: 'start'
                    }
                },
                {
                    element: '#tour-meeting-status',
                    popover: {
                        title: '📈 Status Meeting',
                        description: 'Proporsi status meeting: Berapa yang Selesai, Terjadwal, atau Dibatalkan.',
                        side: "top", align: 'start'
                    }
                }
            ]
        };
    }

    // 6. Meeting Lists
    if (path.includes('/meeting/meeting-lists')) {
        const params = new URLSearchParams(window.location.search);
        const isMyMeetings = params.get('tab') === 'my-meetings';

        const filterId = isMyMeetings ? '#tour-my-list-filter' : '#tour-list-filter';
        const tableId = isMyMeetings ? '#tour-my-list-table' : '#tour-list-table';

        return {
            key: 'tour_seen_meeting_list',
            useGlobalStatus: false,
            steps: [
                {
                    element: '#tour-new-booking-btn',
                    popover: {
                        title: '➕ Booking Baru',
                        description: 'Shortcut cepat untuk membuat reservasi ruangan baru.',
                        side: "bottom", align: 'end'
                    }
                },
                {
                    element: '#tour-tabs',
                    popover: {
                        title: '📑 Navigasi Tab',
                        description: 'Pilih <b>All Meetings</b> untuk jadwal publik, atau <b>My Meetings</b> untuk jadwal pribadi Anda.',
                        side: "bottom", align: 'start'
                    }
                },
                {
                    element: filterId,
                    popover: {
                        title: '🔍 Filter Lanjutan',
                        description: 'Cari meeting spesifik menggunakan filter Tanggal, Status, atau Kata Kunci.',
                        side: "bottom", align: 'start'
                    }
                },
                {
                    element: tableId,
                    popover: {
                        title: '📋 Daftar & Aksi',
                        description: 'Lihat detail meeting di sini. Gunakan tombol aksi di sebelah kanan untuk Edit, Cancel, atau Download Absensi.',
                        side: "top", align: 'center'
                    }
                }
            ]
        };
    }

    return null;
};

window.startTour = function () {
    const path = window.location.pathname;

    // Get dynamic config
    const currentConfig = getTourConfig(path);

    if (!currentConfig || !currentConfig.steps) return;

    // Check LocalStorage
    if (localStorage.getItem(currentConfig.key) === 'true') {
        return;
    }

    const activeSteps = currentConfig.steps.filter(step => {
        if (step.element) {
            return document.querySelector(step.element);
        }
        return true;
    });

    if (activeSteps.length === 0) return;

    let dontShowAgain = false;

    const driverObj = driver({
        showProgress: true,
        allowClose: true,
        popoverClass: 'driverjs-theme',
        animate: true,
        nextBtnText: 'Lanjut →',
        prevBtnText: '← Kembali',
        doneBtnText: 'Selesai',
        steps: activeSteps,
        onPopoverRender: (popover, { config, state }) => {
            const footer = popover.wrapper.querySelector('.driver-popover-footer');

            // 1. Add "Skip" Button (if not last step)
            if (!driverObj.isLastStep()) {
                if (footer && !footer.querySelector('.driver-skip-btn')) {
                    const skipBtn = document.createElement('button');
                    skipBtn.innerText = 'Lewati';
                    skipBtn.className = 'driver-skip-btn';
                    skipBtn.style.cssText = `
                        color: #9ca3af; 
                        font-size: 0.75rem; 
                        text-decoration: underline; 
                        background: none; 
                        border: none; 
                        padding: 0; 
                        cursor: pointer; 
                        margin-right: auto; 
                        font-weight: 500;
                    `;

                    skipBtn.onmouseenter = () => skipBtn.style.color = '#6b7280';
                    skipBtn.onmouseleave = () => skipBtn.style.color = '#9ca3af';

                    skipBtn.addEventListener('click', () => {
                        markTourAsSeen(currentConfig);
                        driverObj.destroy();
                    });

                    footer.prepend(skipBtn);
                }
            }

            // 2. Add "Don't Show Again" Checkbox (Only on Last Step)
            if (driverObj.isLastStep()) {
                const existingCheckbox = popover.wrapper.querySelector('.driver-popover-footer-checkbox');

                if (footer && !existingCheckbox) {
                    const checkboxContainer = document.createElement('div');
                    checkboxContainer.className = 'driver-popover-footer-checkbox';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = 'driver-dont-show-again';
                    checkbox.checked = true;

                    const label = document.createElement('label');
                    label.htmlFor = 'driver-dont-show-again';
                    label.innerText = 'Jangan tampilkan tips halaman ini lagi';

                    checkbox.addEventListener('change', (e) => {
                        dontShowAgain = e.target.checked;
                    });

                    dontShowAgain = true;

                    checkboxContainer.appendChild(checkbox);
                    checkboxContainer.appendChild(label);
                    popover.wrapper.appendChild(checkboxContainer);
                }
            }
        },
        onDestroyed: () => {
            if (dontShowAgain) {
                markTourAsSeen(currentConfig);
            }
        }
    });

    driverObj.drive();
};

function markTourAsSeen(config) {
    localStorage.setItem(config.key, 'true');
    console.log(`Marked tour ${config.key} as seen locally.`);

    if (config.useGlobalStatus) {
        axios.post('/tour/mark-as-seen')
            .then(response => {
                console.log('Global Tour marked as seen in DB');
            })
            .catch(error => {
                console.error('Failed to mark global tour as seen', error);
            });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => window.startTour(), 1000);
    });
} else {
    setTimeout(() => window.startTour(), 1000);
}
