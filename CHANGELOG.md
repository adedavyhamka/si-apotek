# CHANGELOG — SI-APOTEK

Semua perubahan penting pada proyek ini dicatat di sini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

### TAHAP 2 — Database (2026-11-10)
- **Added**: `database/schema.sql` — 11 tabel (9 inti + `tbl_user` + `tbl_stok_histori`), engine InnoDB, utf8mb4.
- **Added**: `database/seed.sql` — data awal (4 user, 1 pemilik, 1 apotek, 2 apoteker, 2 kasir, 3 pelanggan, 10 obat, 2 resep).
- **Added**: `DATABASE.md` — dokumentasi lengkap struktur & relasi.
- **Added**: `tests/db_test.php` — tes koneksi PDO & validasi hash password.
- **Verified**: FK integrity (RESTRICT & invalid insert ditolak), password bcrypt valid.

### TAHAP 1 — Analisis (2026-11-10)
- **Added**: File pelacak proyek (`PROJECT_PROGRESS.md`, `TASKS.md`, `CHANGELOG.md`).
- **Added**: Log inspeksi awal (`_log_inspeksi.txt`).
- **Setup**: GitHub CLI terpasang (2.103.3) & login akun `adedavyhamka` via device flow.
- **Setup**: Reset password root MySQL lokal ke kosong (untuk development).
- **Verifikasi**: PHP 8.5.10, MySQL 26.7.0 aktif, Git 2.50.1 siap.
- **Kesimpulan**: Folder proyek kosong dari source code → aplikasi dibangun dari awal.
