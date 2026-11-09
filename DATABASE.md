# DATABASE — SI-APOTEK

Dokumentasi struktur database Sistem Informasi Manajemen Apotek.

- **Nama database**: `db_si_apotek`
- **Engine**: InnoDB
- **Charset**: `utf8mb4` / collation `utf8mb4_unicode_ci`
- **File**: `database/schema.sql` (struktur) & `database/seed.sql` (data awal)

## Ringkasan Tabel

| # | Tabel | Keterangan | Tipe |
|---|---|---|---|
| 1 | `tbl_pemilik_apotek` | Pemilik apotek | Master |
| 2 | `tbl_apotek` | Data apotek / cabang | Master |
| 3 | `tbl_apoteker` | Apoteker (no SIPA) | Master |
| 4 | `tbl_kasir` | Kasir (shift) | Master |
| 5 | `tbl_pelanggan` | Pelanggan | Master |
| 6 | `tbl_obat` | Obat (stok per cabang) | Master |
| 7 | `tbl_resep` | Resep dokter | Transaksi |
| 8 | `tbl_transaksi_penjualan` | Header transaksi | Transaksi |
| 9 | `tbl_item_transaksi` | Detail item transaksi | Transaksi |
| 10 | `tbl_user` | Akun & role (autentikasi) | Pendukung |
| 11 | `tbl_stok_histori` | Riwayat mutasi stok | Pendukung |

## Relasi Antar Tabel (ERD)

```
tbl_pemilik_apotek 1 ────< N tbl_apotek
tbl_apotek         1 ────< N tbl_apoteker
tbl_apotek         1 ────< N tbl_kasir
tbl_apotek         1 ────< N tbl_obat
tbl_apotek         1 ────< N tbl_user (scope kasir/apoteker)
tbl_pelanggan      1 ────< N tbl_transaksi_penjualan
tbl_kasir          1 ────< N tbl_transaksi_penjualan
tbl_resep          1 ────< N tbl_transaksi_penjualan (boleh NULL)
tbl_transaksi_penjualan 1 ────< N tbl_item_transaksi
tbl_obat           1 ────< N tbl_item_transaksi
tbl_pelanggan      1 ────< N tbl_resep
tbl_apoteker       1 ────< N tbl_resep
tbl_obat           1 ────< N tbl_stok_histori
```

## Detail Kolom

### 1. `tbl_pemilik_apotek`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_pemilik` | INT UNSIGNED PK AI | Primary key |
| `nama` | VARCHAR(100) NOT NULL | Nama pemilik |
| `alamat` | VARCHAR(200) | Alamat |
| `no_telp` | VARCHAR(20) | Telepon |
| `status` | ENUM(aktif,nonaktif) | Soft-delete |

### 2. `tbl_apotek`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_apotek` | INT UNSIGNED PK AI | Primary key |
| `nama_apotek` | VARCHAR(100) NOT NULL | Nama apotek |
| `alamat` | VARCHAR(200) | Alamat |
| `no_telp` | VARCHAR(20) | Telepon |
| `id_pemilik` | INT UNSIGNED FK | → `tbl_pemilik_apotek` (RESTRICT / CASCADE) |
| `status` | ENUM | Soft-delete |

### 3. `tbl_apoteker`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_apoteker` | INT UNSIGNED PK AI | Primary key |
| `nama_apoteker` | VARCHAR(100) NOT NULL | Nama |
| `no_sipa` | VARCHAR(50) | Nomor SIPA |
| `no_telp` | VARCHAR(20) | Telepon |
| `id_apotek` | INT UNSIGNED FK | → `tbl_apotek` (RESTRICT / CASCADE) |
| `status` | ENUM | Soft-delete |

### 4. `tbl_kasir`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_kasir` | INT UNSIGNED PK AI | Primary key |
| `nama_kasir` | VARCHAR(100) NOT NULL | Nama |
| `shift` | VARCHAR(20) | Shift kerja |
| `no_telp` | VARCHAR(20) | Telepon |
| `id_apotek` | INT UNSIGNED FK | → `tbl_apotek` (RESTRICT / CASCADE) |
| `status` | ENUM | Soft-delete |

### 5. `tbl_pelanggan`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_pelanggan` | INT UNSIGNED PK AI | Primary key |
| `nama_pelanggan` | VARCHAR(100) NOT NULL | Nama |
| `alamat` | VARCHAR(200) | Alamat |
| `no_telp` | VARCHAR(20) | Telepon |
| `tanggal_daftar` | DATE | Tanggal registrasi |
| `status` | ENUM | Soft-delete |

### 6. `tbl_obat`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_obat` | INT UNSIGNED PK AI | Primary key |
| `nama_obat` | VARCHAR(100) NOT NULL | Nama obat |
| `kategori` | VARCHAR(50) | Kategori |
| `dosis` | VARCHAR(50) | Dosis |
| `harga` | DECIMAL(10,2) | Harga jual |
| `stok` | INT | Stok saat ini |
| `stok_minimum` | INT | Ambang peringatan stok |
| `expired_date` | DATE | Tanggal kedaluwarsa |
| `id_apotek` | INT UNSIGNED FK | → `tbl_apotek` (RESTRICT / CASCADE) |
| `status` | ENUM | Soft-delete |

### 7. `tbl_resep`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_resep` | INT UNSIGNED PK AI | Primary key |
| `no_resep` | VARCHAR(50) NOT NULL | Nomor resep |
| `nama_dokter` | VARCHAR(100) | Nama dokter |
| `nama_pasien` | VARCHAR(100) | Nama pasien |
| `tanggal_resep` | DATE | Tanggal resep |
| `deskripsi` | TEXT | Isi resep |
| `id_pelanggan` | INT UNSIGNED FK NULL | → `tbl_pelanggan` |
| `id_apoteker` | INT UNSIGNED FK NULL | → `tbl_apoteker` |

### 8. `tbl_transaksi_penjualan`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_transaksi` | INT UNSIGNED PK AI | Primary key |
| `no_transaksi` | VARCHAR(30) UNIQUE | Nomor struk |
| `tanggal` | DATETIME | Waktu transaksi |
| `total` | DECIMAL(12,2) | Total pembayaran |
| `metode_pembayaran` | VARCHAR(50) | Tunai/Qris/Transfer/dll |
| `id_kasir` | INT UNSIGNED FK | → `tbl_kasir` (RESTRICT) |
| `id_pelanggan` | INT UNSIGNED FK NULL | → `tbl_pelanggan` |
| `id_resep` | INT UNSIGNED FK NULL | → `tbl_resep` (SET NULL) |

### 9. `tbl_item_transaksi`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_item` | INT UNSIGNED PK AI | Primary key |
| `jumlah` | INT | Jumlah beli |
| `harga_satuan` | DECIMAL(10,2) | Harga saat transaksi (snapshot) |
| `subtotal` | DECIMAL(10,2) | `jumlah × harga_satuan` |
| `id_transaksi` | INT UNSIGNED FK | → `tbl_transaksi_penjualan` (CASCADE) |
| `id_obat` | INT UNSIGNED FK | → `tbl_obat` (RESTRICT) |

### 10. `tbl_user` (pendukung — autentikasi)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_user` | INT UNSIGNED PK AI | Primary key |
| `username` | VARCHAR(50) UNIQUE | Login |
| `password` | VARCHAR(255) | Hash bcrypt |
| `nama_lengkap` | VARCHAR(100) | Nama tampilan |
| `role` | ENUM(super_admin,pemilik,apoteker,kasir) | Hak akses |
| `id_apotek` | INT UNSIGNED FK NULL | Scope cabang |
| `status` | ENUM | Soft-delete |
| `created_at` / `updated_at` | DATETIME | Audit |

### 11. `tbl_stok_histori` (pendukung — mutasi stok)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_histori` | INT UNSIGNED PK AI | Primary key |
| `id_obat` | INT UNSIGNED FK | → `tbl_obat` (CASCADE) |
| `jenis` | ENUM(masuk,keluar) | Arah mutasi |
| `jumlah` | INT | Jumlah berubah |
| `stok_sebelum` | INT | Stok sebelum |
| `stok_sesudah` | INT | Stok sesudah |
| `keterangan` | VARCHAR(255) | Catatan |
| `tanggal` | DATETIME | Waktu mutasi |
| `id_user` | INT UNSIGNED FK NULL | → `tbl_user` (SET NULL) |

## Aturan Foreign Key

| Relasi | ON DELETE | ON UPDATE |
|---|---|---|
| apotek → pemilik | RESTRICT | CASCADE |
| apoteker/kasir/obat → apotek | RESTRICT | CASCADE |
| transaksi → kasir | RESTRICT | CASCADE |
| transaksi → pelanggan | RESTRICT | CASCADE |
| transaksi → resep | SET NULL | CASCADE |
| item → transaksi | CASCADE | CASCADE |
| item → obat | RESTRICT | CASCADE |
| resep → pelanggan/apoteker | RESTRICT | CASCADE |
| user → apotek | SET NULL | CASCADE |
| stok_histori → obat | CASCADE | CASCADE |
| stok_histori → user | SET NULL | CASCADE |

**Prinsip**: `RESTRICT` mencegah penghapusan master data yang masih dipakai
histori; penonaktifan dilakukan lewat kolom `status` (soft-delete) sehingga
histori transaksi tidak pernah hilang.

## Deviasi dari ERD (alasan)

1. **`status` (soft-delete)** pada tabel master — mendukung fitur "nonaktifkan"
   tanpa menghapus histori transaksi.
2. **`tbl_obat.id_apotek`** — mendukung stok per cabang (multi-apotek).
3. **`tbl_obat.stok_minimum`** — fitur peringatan stok menipis.
4. **`tbl_item_transaksi.harga_satuan`** — snapshot harga saat transaksi agar
   laporan historis tidak berubah bila harga obat diedit.
5. **`tbl_transaksi_penjualan.no_transaksi`** — nomor struk unik untuk cetak.
6. **`tbl_resep.id_pelanggan` / `id_apoteker` nullable** — pasien walk-in tetap
   bisa dicatat lewat `nama_pasien`.
7. **`tbl_user` & `tbl_stok_histori`** — tabel pendukung autentikasi dan
   riwayat stok (di luar 9 tabel ERD).

## Cara Import

### Lokal (MySQL/CLI)
```bash
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql
```

### InfinityFree (phpMyAdmin)
1. Buka phpMyAdmin → pilih database `db_si_apotek`.
2. Tab **Import** → pilih `database/schema.sql` → Go.
3. Ulangi untuk `database/seed.sql`.

## Akun Default (Seed)

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | super_admin |
| `pemilik` | `pemilik123` | pemilik |
| `apoteker` | `apoteker123` | apoteker |
| `kasir` | `kasir123` | kasir |

> ⚠️ **Ganti semua password ini setelah login pertama**, terutama sebelum
> dipakai produksi.
