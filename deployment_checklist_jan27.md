# 🚀 Deployment Checklist (27 Januari 2026)

Dokumen ini berisi rangkuman langkah-langkah yang harus dilakukan saat deployment ke server nanti.

## 1. Setup Pusher (Real-time Broadcast)
Fitur ini menggantikan Reverb untuk auto-refresh yang lebih stabil.
*Notes: Langkah ini mungkin sudah dilakukan sebagian, tapi pastikan lagi.*

- [ ] **Dapatkan Credentials** dari Dashboard Pusher.com (App ID, Key, Secret, Cluster).
- [ ] **Update `.env`** di server:
  ```env
  BROADCAST_CONNECTION=pusher
  PUSHER_APP_ID=...
  PUSHER_APP_KEY=...
  PUSHER_APP_SECRET=...
  PUSHER_APP_CLUSTER=...
  ```
- [ ] **Install & Build** Dependencies:
  ```bash
  composer install
  npm install
  npm run build
  ```
- [ ] **Clear Cache**:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  ```

## 2. Migrasi Data Meeting (Cleaning & Insert)
Fitur ini akan membersihkan data lama (Jan-Mar) dan memasukkan 58 data meeting yang sudah divalidasi.

- [ ] **Pull Code Terbaru**:
  ```bash
  git pull origin master
  ```
- [ ] **Jalankan Seeder** (Recommended Method):
  ```bash
  php artisan db:seed --class=LegacyMeetingsJanMar2026Seeder
  ```
  *(Opsi Backup: Jika seeder gagal, gunakan file SQL `migration_meetings_jan_mar_2026.sql` via database manager)*

## 3. Verifikasi Akhir
- [ ] Cek tampilan Tablet/TV, pastikan data meeting sudah muncul.
- [ ] Cek fitur Auto-Refresh apakah berjalan semestinya.

---
**Status**: Siap Deploy.
**Files Penting**:
- `database/seeders/LegacyMeetingsJanMar2026Seeder.php`
- `migration_meetings_jan_mar_2026.sql`
