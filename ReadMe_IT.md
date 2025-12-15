# Technical Documentation - DOORS Application
**Version:** 1.0.0
**Framework:** Laravel 12.x
**PHP Version:** 8.2+

Dokumen ini ditujukan untuk Tim IT / DevOps sebagai panduan deployment, configuration, dan maintenance aplikasi DOORS.  

---

## 1. System Requirements

Pastikan server memenuhi spesifikasi berikut:
- **OS:** Linux (Ubuntu 22.04 LTS / 24.04 LTS recommended)
- **Web Server:** Nginx (Recommended) atau Apache
- **PHP:** Versi 8.2 atau lebih baru
  - Extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `pcntl` (untuk Reverb/Queue)
- **Database:** MySQL 8.0+ / MariaDB 10.x
- **Node.js:** Versi 18+ (Untuk build frontend assets)
- **Composer:** Versi 2.x

---

## 2. Deployment Steps

Langkah standar untuk fresh installation di server production:

1.  **Clone Repository**
    ```bash
        https://github.com/AbdiDzikry/doors4.git
    ```

2.  **Install Dependencies**
    ```bash
    composer install --optimize-autoloader --no-dev
    npm install
    npm run build
    ```

3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    php artisan storage:link
    ```
    *Edit file `.env` dan sesuaikan konfigurasi database dan API.*

4.  **Database Migration**
    ```bash
    php artisan migrate --force
    ```

5.  **Permissions**
    Pastikan web server (www-data) memiliki akses tulis ke folder storage:
    ```bash
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    ```

---

## 3. Environment Configuration (.env)

### A. Database
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doors_db
DB_USERNAME=doors_user
DB_PASSWORD=secret
```

### B. Employee API (CRITICAL)
Aplikasi melakukan sync data karyawan dari API eksternal.
**PENTING:** Hapus bypass SSL (`withoutVerifying()`) di `app/Services/EmployeeApiService.php` jika sertifikat API valid.

```ini
EMPLOYEE_API_KEY= pada env
EMPLOYEE_API_URL=https://msa-be.dharmagroup.co.id/api/data/company
```

### C. Laravel Reverb (Websockets)
Aplikasi menggunakan Reverb untuk update real-time. Sesuaikan dengan domain/port server.

```ini
REVERB_APP_ID=app_id
REVERB_APP_KEY=app_key
REVERB_APP_SECRET=app_secret
REVERB_HOST="doors.dharmagroup.co.id"
REVERB_PORT=8080
REVERB_SCHEME=https
```

---

## 4. Server Daemon Setup (Superior/Systemd)

Aplikasi membutuhkan 2 proses background agar berjalan optimal: **Reverb Server** (untuk realtime) dan **Queue Worker**. Gunakan **Supervisor** untuk mengelolanya.

### Konfigurasi Supervisor (`/etc/supervisor/conf.d/doors-worker.conf`)

```ini
[program:doors-reverb]
process_name=%(program_name)s
command=php /var/www/doors/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/doors/storage/logs/reverb.log

[program:doors-queue]
process_name=%(program_name)s
command=php /var/www/doors/artisan queue:work --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/doors/storage/logs/worker.log
```

Setelah file dibuat, jalankan:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 5. Scheduled Tasks (Cron Job)

Scheduler digunakan untuk **Sync Data Karyawan** secara otomatis setiap hari jam 02:00 Pagi.

Masukkan entri berikut ke crontab user web server (`www-data`):

```bash
# Buka crontab editor
crontab -u www-data -e
```

**Isi Crontab:**
```bash
* * * * * cd /var/www/doors && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Troubleshooting

### SSL Certificate Error saat Sync (cURL 60)
Jika terjadi error *"cURL error 60: SSL certificate problem"* saat sync di production:
1. Pastikan CA Certificates di server uptodate (`update-ca-certificates`).
2. Jika API menggunakan Self-Signed Certificate, tambahkan cert tersebut ke Trusted Store server aplikasi.
3. *Jalan pintas (tidak disarankan):* Gunakan `Http::withoutVerifying()` di kode service.

### Websocket Connection Error
Jika fitur real-time tidak jalan:
1. Cek status supervisor: `sudo supervisorctl status`.
2. Pastikan Port 8080 (atau port Reverb) dibuka di Firewall manager.
3. Jika menggunakan HTTPS (SSL), pastikan konfigurasi Nginx untuk WebSocket Proxy sudah benar (Upgrade Headers).

### Data Karyawan Tidak Update
1. Cek log sync terakhir di database (jika ada log table) atau via file log.
2. Coba jalankan manual: `php artisan sync:employees`.

---

## 7. Security & Encryption

Aplikasi DOORS dibangun dengan standar keamanan Laravel (Secure by Default):

1.  **Password Encryption:**
    Password user di-hash menggunakan **Bcrypt** (satu arah). Admin database tidak dapat membaca password user.

2.  **Protection:**
    - **SQL Injection:** Dicegah oleh Eloquent ORM.
    - **CSRF Protection:** Form dilindungi token anti-forgery.
    - **XSS Protection:** Blade template engine melakukan auto-escaping.

3.  **Data Transmission (SSL/HTTPS):**
    Di lingkungan Production, **WAJIB** memasang SSL Certificate agar komunikasi data (login credential, data karyawan) terenkripsi saat transit.

4.  **Sensitive Data:**
    API Key dan credential database **TIDAK** boleh di-hardcode di source code, melainkan harus via environment variable (`.env`).
