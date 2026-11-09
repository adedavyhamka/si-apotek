# CHANGELOG — SI-APOTEK

Semua perubahan penting pada proyek ini dicatat di sini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

### TAHAP 7 — GitHub (2026-11-10)
- **Added**: `.gitignore` (kecualikan log, backup, kredensial, dokumen referensi asli).
- **Added**: repository `si-apotek` (public) di GitHub → https://github.com/adedavyhamka/si-apotek
- **Commit**: commit awal lengkap (source code + SQL + dokumentasi).

### TAHAP 3–6 — Backend, Frontend, Integrasi, Testing (2026-11-10)
- **Added**: 14 controller + 40+ view (MVC sederhana, PDO prepared statement).
- **Added**: Auth (bcrypt, session regenerate, lockout 5×/15 menit), RBAC 4 role, CSRF.
- **Added**: Modul lengkap — dashboard (Chart.js), pemilik, apotek, apoteker, kasir, obat (+stok masuk & riwayat), pelanggan, resep, transaksi POS (checkout atomik), laporan (CSV/PDF), user, profil.
- **Added**: Dokumentasi `README.md`, `INSTALLATION.md`, `DEPLOYMENT.md`, `USER_GUIDE.md`.
- **Verified**: lint 53 file PHP (0 error), smoke test 12 modul (200 OK), transaksi atomik (stok 50→49 + riwayat keluar), RBAC (kasir ditolak pemilik/user/obat).

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
