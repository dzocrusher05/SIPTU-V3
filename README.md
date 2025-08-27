<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
<h1 align="center">SIPTU.V3</h1>

SIPTU.V3 adalah aplikasi internal berbasis Laravel untuk mengelola modul:

- BMN (inventaris dan peminjaman)
- Pegawai
- LPJ
- MAK
- IT Assets
- KGB Updates
- Surat Tugas (dengan alur permintaan → persetujuan)

Arsitektur: Laravel 11 + Inertia + Vue 3 + Naive UI + Tailwind, bundling dengan Vite. Admin UI sepenuhnya SPA (Inertia). Lihat catatan pengembangan di `docs/project_notes`.

## Persyaratan

- PHP 8.3+
- Composer 2.5+
- Node.js 18+ (disarankan 20+) dan npm
- MySQL 8+ (atau MariaDB kompatibel)
- Git

## Instalasi Cepat

1) Clone repo dan masuk direktori

```bash
git clone https://github.com/dzocrusher05/SIPTU-V3.git
cd SIPTU-V3
```

2) Siapkan environment

```bash
# Salin env contoh → sesuaikan DB_* dan APP_URL
cp .env.example .env          # Mac/Linux
# Windows: copy .env.example .env

# Install dependency PHP
composer install

# Generate APP_KEY
php artisan key:generate

# Buat symlink storage (opsional, untuk akses file publik)
php artisan storage:link
```

3) Install dependency front-end

```bash
npm install
```

4) Migrasi database

```bash
# Pastikan env DB_* sudah benar
php artisan migrate
```

5) Jalankan aplikasi (mode dev)

```bash
# Terminal 1: backend
php artisan serve

# Terminal 2: front-end dev server
npm run dev
```

Buka http://127.0.0.1:8000. Auth bawaan tersedia; jika belum ada user, gunakan menu Register untuk membuat akun pertama.

## Build Produksi

```bash
# Bundle aset produksi
npm run build

# Jalankan di server web yang mengarah ke folder public/
# (Nginx/Apache/PHP-FPM sesuai standar deployment Laravel)
```

## Rute & Modul Utama

- Admin SPA: seluruh halaman admin berada pada rute `spa/*`.
- Peminjaman BMN: mendukung tanggal mulai/sampai, lokasi tujuan, dan aksi Approve/Return.
- Surat Tugas: kolom `status` untuk alur permintaan → persetujuan, `nomor_st` dan `tanggal_st` bersifat nullable.

## Catatan Pengembangan

Riwayat perubahan dan keputusan desain dicatat di `docs/project_notes`.

## Troubleshooting

- Gagal build SCSS (sass-embedded): paket dev `sass-embedded` terpasang; jika tidak diperlukan Anda bisa menghapusnya dari `devDependencies` lalu `npm install` ulang.
- Perubahan skema: jalankan `php artisan migrate` setelah menarik perubahan yang memodifikasi migration.

## Lisensi

Proyek aplikasi ini menggunakan Laravel dan dependensi open source. Hak cipta dan lisensi kode aplikasi mengikuti ketentuan pemilik repositori ini.
