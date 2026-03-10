# SIMELATI

Sistem Informasi Manajemen SD Plus Melati.

## Stack
- Laravel 11
- PHP 8.2
- MySQL
- Blade + Bootstrap 5
- CSV Export

## Fitur Utama
- GPS Attendance Guru (check-in / check-out dengan validasi radius)
- Jurnal Mengajar terpisah dari check-in guru
- Manajemen Siswa
- Portal Izin Siswa untuk Orang Tua
- Auto Izin di Absensi Kelas
- Absensi Kelas per sesi
- Auto isi kolom siswa tidak hadir di Jurnal Mengajar dari data absensi kelas
- KPI Guru bulanan + ranking + export CSV
- Laporan admin

## Akun Seeder
- Admin: `admin@sdplusmelati.local` / `password`
- Teacher (semua password: `password`):
  - `nurul-hana-hidayah@sdplusmelati.local`
  - `samsiah@sdplusmelati.local`
  - `risa-nur-sofitri@sdplusmelati.local`
  - `rohadatul-aisy@sdplusmelati.local`
  - `tyas-dwi-fitriyanti@sdplusmelati.local`
  - `linda-herlianah@sdplusmelati.local`
  - `humairah-h@sdplusmelati.local`
  - `risma-pebriana@sdplusmelati.local`
  - `dilla@sdplusmelati.local`
  - `nurlinda-tm@sdplusmelati.local`
  - `syaifuddin@sdplusmelati.local`
  - `hery-isriyadi@sdplusmelati.local`
  - `nadia-anjelika@sdplusmelati.local`
  - `miki-sandi@sdplusmelati.local`
- Parent: `parent1@sdplusmelati.local` / `password`

## Setup
```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve