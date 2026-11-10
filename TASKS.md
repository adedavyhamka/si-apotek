# TASKS — SI-APOTEK

Daftar tugas pembangunan Sistem Informasi Manajemen Apotek (SI-APOTEK).
Status: `[ ]` belum, `[~]` berjalan, `[x]` selesai.

## TAHAP 1 — ANALISIS
- [x] Periksa folder proyek
- [x] Periksa source code tersedia (kosong → bangun dari awal)
- [x] Pelajari repository referensi (belajar-vibe-coding)
- [x] Periksa lingkungan PHP, MySQL, Git, GitHub CLI
- [x] Setup autentikasi GitHub (device flow)
- [x] Setup akses MySQL lokal (root tanpa password)
- [x] Buat dokumen analisis (ringkasan inspeksi → `_log_inspeksi.txt`)

## TAHAP 2 — DATABASE
- [x] ERD final (9 tabel inti + 2 tabel pendukung: tbl_user, tbl_stok_histori)
- [x] `database/schema.sql` (semua tabel, relasi, indeks)
- [x] `database/seed.sql` (data awal: user, master, contoh)
- [x] Buat database `db_si_apotek` di MySQL lokal
- [x] Import schema + seed
- [x] Uji integritas foreign key (RESTRICT & invalid FK teruji)
- [x] `DATABASE.md`

## TAHAP 3 — BACKEND
- [x] Struktur aplikasi PHP (MVC sederhana)
- [x] Konfigurasi database (PDO)
- [x] Autentikasi (login, logout, ganti password, session)
- [x] Middleware RBAC (4 role)
- [x] CRUD seluruh modul (pemilik, apotek, apoteker, kasir, obat, pelanggan, resep, transaksi, laporan, user, profil)

## TAHAP 4 — FRONTEND
- [x] Layout dashboard (sidebar, topbar)
- [x] Halaman login
- [x] Dashboard + kartu statistik + Chart.js
- [x] Halaman data master (pemilik, apotek, apoteker, kasir, obat, pelanggan)
- [x] Halaman transaksi & resep
- [x] Halaman laporan
- [x] Halaman pengaturan akun
- [x] Responsif (desktop/tablet/mobile)

## TAHAP 5 — INTEGRASI
- [x] Hubungkan modul dengan MySQL
- [x] Transaksi ↔ item transaksi
- [x] Resep ↔ pelanggan & apoteker
- [x] Stok ↔ transaksi (pengurangan atomik)
- [x] Laporan ↔ data transaksi

## TAHAP 6 — TESTING
- [x] Login/logout & hak akses per role
- [~] CRUD setiap tabel (smoke test semua modul 200 OK)
- [x] Transaksi penjualan (total, stok, validasi)
- [x] Cetak struk
- [~] Laporan
- [~] Keamanan (XSS, CSRF, SQL injection — CSRF & prepared statement terpasang)
- [~] Tampilan mobile (perlu uji manual)

## TAHAP 7 — GITHUB
- [x] Buat repo `si-apotek`
- [x] `.gitignore`
- [x] `README.md`
- [x] Commit bertahap (commit awal lengkap)
- [x] Push ke GitHub (https://github.com/adedavyhamka/si-apotek)

## TAHAP 8 — DEPLOYMENT (InfinityFree)
- [x] Siapkan paket produksi
- [x] Konfigurasi DB hosting (sql303.infinityfree.com)
- [x] Import SQL (via installer dari dalam hosting — MySQL tidak bisa diakses eksternal)
- [x] Upload source code ke htdocs (57 file via FTP)
- [x] Konfigurasi produksi (database.php)
- [x] SSL/HTTPS (redirect otomatis ke https)
- [x] Uji via si-apotek.gt.tc (login, dashboard, transaksi, stok 8→7, struk)

## TAHAP 9 — DOKUMENTASI
- [x] `README.md`
- [x] `INSTALLATION.md`
- [x] `DATABASE.md`
- [x] `DEPLOYMENT.md`
- [x] `USER_GUIDE.md`
- [x] `CHANGELOG.md`
