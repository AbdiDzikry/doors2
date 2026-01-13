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
*Dibuat oleh sulthan :)  - Doors App Project*
