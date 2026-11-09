# INSTALLATION — SI-APOTEK

Panduan instalasi aplikasi di komputer lokal (macOS/Linux/Windows).

## Prasyarat

- PHP 8.0 atau lebih baru (dengan ekstensi `pdo_mysql`).
- MySQL / MariaDB 5.7+.
- (Opsional) Git.

## Langkah Instalasi

### 1. Siapkan Database

Buat database dan import struktur + data awal:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

Atau via phpMyAdmin: buka database → tab **Import** → pilih `schema.sql`,
lalu ulangi untuk `seed.sql`.

### 2. Konfigurasi Koneksi Database

Edit `app/config/database.php`. Default-nya untuk lokal:

```php
return [
    'host'    => 'localhost',
    'name'    => 'db_si_apotek',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];
```

Sesuaikan `host`, `user`, `pass`, `name` dengan server Anda.
Anda juga bisa memakai environment variable `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

### 3. Jalankan Aplikasi

**Cara 1 — PHP built-in server (development):**

```bash
cd app
php -S localhost:8080
```

Buka `http://localhost:8080`.

**Cara 2 — Apache/XAMPP:** salin isi folder `app/` ke document root (htdocs).

### 4. Login

Gunakan akun default (lihat README). Segera ganti password melalui menu
**Profil & Password**.

## Troubleshooting

| Masalah | Solusi |
|---|---|
| "Terjadi kesalahan pada koneksi database" | Periksa kredensial di `config/database.php` & pastikan MySQL berjalan |
| Halaman putih / error | Cek log `app/storage/logs/error.log` |
| CSRF tidak valid | Muat ulang halaman lalu coba lagi |
| Tidak bisa login | Pastikan user berstatus `aktif` di `tbl_user` |
