# Panduan Integrasi Sinkronisasi API Karyawan (Untuk Tim IT)

Dokumen ini menjelaskan langkah-langkah yang diperlukan agar fitur sinkronisasi data karyawan (Employee API) berjalan otomatis dan stabil di lingkungan **Staging** maupun **Production**.

---

## 1. Konfigurasi Environment (`.env`)
Pastikan variabel berikut sudah dikonfigurasi dengan benar di server:

```env
# URL API Employee (Sesuaikan jika staging/production berbeda)
EMPLOYEE_API_URL="https://msa-be.dharmagroup.co.id/api/data/company"
EMPLOYEE_API_KEY="AIzaSy... (Gunakan API Key yang Valid)"
```

---

## 2. Pengaturan Penjadwalan (Cron Job)
Aplikasi menggunakan fitu *Laravel Task Scheduler*. Agar fungsi `->daily()` berjalan otomatis, Tim IT perlu mendaftarkan perintah berikut ke dalam **Crontab** server:

```bash
# Tambahkan baris ini ke dalam crontab (perintah: crontab -e)
* * * * * cd /var/www/doors-app && php artisan schedule:run >> /dev/null 2>&1
```
> [!NOTE]
> Pastikan `/var/www/doors-app` diganti dengan path absolut lokasi aplikasi di server. Perintah ini akan mengecek setiap menit apakah ada tugas (seperti sync harian) yang perlu dijalankan.

---

## 3. Verifikasi & Testing Manual
Tim IT dapat melakukan pengujian manual untuk memastikan koneksi ke API tidak terhalang firewall/proxy:

### A. Tes Koneksi & Sinkronisasi
Jalankan perintah ini di root folder aplikasi:
```bash
php artisan sync:employees
```
*   **Berhasil**: Muncul pesan "Employee sync completed. Synced: X, Errors: Y".
*   **Gagal**: Periksa koneksi internet server atau validitas API Key.

### B. Tes Paksa (Bypass Cache)
Secara default, sync dibatasi sekali setiap 5 menit. Untuk mengetes berulang kali, gunakan flag `--force`:
```bash
php artisan sync:employees --force
```

---

## 4. Monitoring & Troubleshooting
Jika terjadi kendala data tidak masuk, silakan cek bagian berikut:

1.  **Log Aplikasi**: Periksa file `storage/logs/laravel.log`. Cari pesan dengan prefix `[Employee Sync Error]`.
2.  **Koneksi SSL**: Aplikasi saat ini menggunakan `withoutVerifying()` untuk HTTP request. Jika standar keamanan production mewajibkan verifikasi SSL, pastikan CA Certificate server sudah terupdate.
3.  **Database**: Pastikan user database memiliki akses untuk `UPDATE` dan `INSERT` pada tabel `users`.

---

## 5. Ringkasan Fitur
Sistem ini menggunakan metode **Hybrid Sync**:
1.  **On-Demand**: Sync otomatis saat karyawan baru (yang belum ada di DB) mencoba login.
2.  **Scheduled**: Sync massal otomatis setiap hari (Daily).
3.  **Manual**: Tombol "Sync API" di dashboard User Management (Hanya Super Admin).

---

# [NEW] Update Deployment: Migrasi ke Pusher (Jan 2026)

Untuk mengaktifkan fitur Real-time Update pada tablet di Production, **SANGAT PENTING** untuk menjalankan langkah berikut. Tidak cukup hanya update `.env` saja.

## 1. Update Source Code
Pastikan kode terbaru sudah ditarik karena ada perubahan pada `resources/js/bootstrap.js` dan `config/broadcasting.php`.
```bash
git pull origin main
```

## 2. Update .env
Tambahkan konfigurasi Pusher berikut. **HAPUS** atau **COMMENT** konfigurasi Reverb yang lama jika ada.

```env
BROADCAST_CONNECTION=pusher

# Ganti dengan kredensial asli
PUSHER_APP_ID=2104705
PUSHER_APP_KEY=97fa7011df4675532d7f
PUSHER_APP_SECRET=cf1171fd16b7f2dbb348
PUSHER_APP_CLUSTER=ap1

# Pastikan settingan ini KOSONG untuk Pusher Cloud
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

# Client Config
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## 3. Build Ulang Assets (CRITICAL)
Karena konfigurasi Pusher ditanam di file Javascript (`bootstrap.js`), Anda **HARUS** melakukan build ulang aset frontend agar perubahan `.env` terbaca oleh browser.
```bash
npm install
npm run build
```

## 4. Clear Cache Config
Karena ada perubahan logika di `config/broadcasting.php` untuk menangani host kosong:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 5. Restart Queue (Opsional)
Jika menggunakan Supervisor untuk antrian, restart worker agar mengambil kode baru.
```bash
php artisan queue:restart
```

## 6. Setup Task Scheduler (Opsional)
Fitur Tablet sudah memiliki **Auto-Refresh (Client Side)** setiap 2 menit, jadi Cron Job di server tidak lagi wajib untuk update status ruangan.
Namun, jika Anda ingin menggunakan fitur maintenance otomatis lainnya, Anda tetap bisa menambahkan cron entry:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

