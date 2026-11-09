# PROJECT PROGRESS — SI-APOTEK

Sistem Informasi Manajemen Apotek (SI-APOTEK) untuk Apotek Qilah Sehat.
Diperbarui: 2026-11-10.

## Ringkasan Status

| Tahap | Status | Keterangan |
|---|---|---|
| 1. Analisis | ✅ Selesai | Lingkungan siap, folder kosong, GitHub & MySQL terautentikasi |
| 2. Database | ✅ Selesai | 11 tabel + seed + FK & hash teruji |
| 3. Backend | ✅ Selesai | Auth, RBAC, CRUD 11 modul, PDO prepared statement |
| 4. Frontend | ✅ Selesai | Dashboard biru-putih, sidebar, Chart.js, responsif |
| 5. Integrasi | ✅ Selesai | Transaksi atomik + stok + laporan terhubung |
| 6. Testing | 🔄 Berjalan | Login/RBAC/transaksi/stok teruji; CRUD & mobile menunggu |
| 7. GitHub | ✅ Selesai | Repo `si-apotek` public di-push |
| 8. Deployment | ⬜ Belum | Menunggu akses panel InfinityFree |
| 9. Dokumentasi | ✅ Selesai | README, INSTALLATION, DATABASE, DEPLOYMENT, USER_GUIDE, CHANGELOG |

## Lingkungan Pengembangan

- **OS**: macOS 26.5.2 (arm64)
- **PHP**: 8.5.10 (Homebrew)
- **MySQL**: 26.7.0 (Homebrew), `mysqld` aktif, root tanpa password (lokal)
- **Git**: 2.50.1
- **GitHub CLI**: 2.103.3, login sebagai `adedavyhamka`

## Keputusan Teknis (log)

1. **Stack**: PHP Native (MVC sederhana) + PDO + MySQL — sesuai target hosting InfinityFree.
2. **Database**: `db_si_apotek`, engine InnoDB, charset utf8mb4.
3. **Role**: super_admin, pemilik, apoteker, kasir.
4. **Soft-delete**: master data menggunakan kolom `status` (aktif/nonaktif) agar histori transaksi tidak hilang.

## Lokasi

Proyek: `/Users/davy/DATA LOCAL D MAC/LIA/Intan/Aplikasi apoteker`
