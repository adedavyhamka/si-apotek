# SI-APOTEK — Sistem Informasi Manajemen Apotek

Aplikasi web **Sistem Informasi Manajemen Apotek** untuk Apotek Qilah Sehat.
Dibangun dengan **PHP Native (MVC sederhana) + MySQL (PDO)** — ringan, aman,
dan siap di-deploy ke shared hosting (InfinityFree).

## Fitur Utama

- **Autentikasi & Hak Akses (RBAC)**: 4 role — Super Admin, Pemilik, Apoteker, Kasir.
- **Dashboard**: penjualan hari ini, transaksi, pelanggan, obat, peringatan stok
  & kedaluwarsa, grafik harian/bulanan (Chart.js), transaksi terbaru.
- **Data Master**: pemilik, apotek (cabang), apoteker (SIPA), kasir (shift),
  obat (stok per cabang), pelanggan.
- **Transaksi Penjualan (POS)**: pencarian obat, keranjang, checkout **atomik**
  (pengurangan stok + pencatatan riwayat), cetak struk, riwayat.
- **Manajemen Resep**: input resep dokter, relasi pelanggan & apoteker.
- **Manajemen Stok**: stok masuk, riwayat mutasi, peringatan stok minimum & kedaluwarsa.
- **Laporan**: penjualan harian/periode, obat terlaris, stok, kedaluwarsa,
  per kasir; export **CSV (Excel)** & cetak **PDF** (via browser).

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8+ (Native, MVC sederhana), PDO |
| Database | MySQL / MariaDB (InnoDB, utf8mb4) |
| Frontend | HTML5, CSS3, Bootstrap 5, Chart.js, Bootstrap Icons |
| Keamanan | `password_hash` (bcrypt), prepared statements, CSRF token, XSS escaping, RBAC, session regeneration, pembatasan percobaan login |

## Struktur Proyek

```
.
├── app/                     # Web root (upload ke htdocs saat deploy)
│   ├── index.php            # Front controller (routing ?m=modul&a=aksi)
│   ├── config/              # config.php, database.php, access.php (RBAC)
│   ├── core/                # Database (PDO), Auth, helpers, View
│   ├── controllers/         # 1 controller per modul
│   ├── views/               # layout, per modul, partials
│   └── assets/              # css, js
├── database/                # schema.sql, seed.sql
├── tests/                   # skrip pengujian
├── docs (DATABASE.md, dst.)
```

## Instalasi Cepat (lokal)

```bash
# 1. Buat database & import
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql

# 2. Jalankan server PHP
cd app && php -S localhost:8080
```

Buka `http://localhost:8080` lalu login:

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | Super Admin |
| `pemilik` | `pemilik123` | Pemilik |
| `apoteker` | `apoteker123` | Apoteker |
| `kasir` | `kasir123` | Kasir |

> ⚠️ Ganti semua password default sebelum dipakai produksi.

## Dokumentasi

- [INSTALLATION.md](INSTALLATION.md) — panduan instalasi lengkap.
- [DATABASE.md](DATABASE.md) — struktur database & relasi.
- [DEPLOYMENT.md](DEPLOYMENT.md) — deploy ke InfinityFree.
- [USER_GUIDE.md](USER_GUIDE.md) — panduan penggunaan.
- [CHANGELOG.md](CHANGELOG.md) — riwayat perubahan.

## Lisensi

Proyek internal Universitas LIA — Apotek Qilah Sehat.
