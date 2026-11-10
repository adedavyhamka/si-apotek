# REPORT TEMPLATE — SI-APOTEK

Dokumentasi template laporan resmi dan jenis laporan yang didukung.

## Struktur Dokumen Laporan

Setiap dokumen laporan memiliki struktur seragam:

```
+--------------------------------------------------------------+
| KOP:  [Logo]  Nama Apotek / Alamat / Telepon   [Judul Laporan] |
|       ----------------------------------------------------     |
| J U D U L   L A P O R A N  (tengah)                           |
| Periode: DD/MM/YYYY s/d DD/MM/YYYY                            |
| Tanggal Cetak: ...   Dicetak oleh: ...                        |
+--------------------------------------------------------------+
| TABEL DATA (header hijau, zebra striping)                    |
+--------------------------------------------------------------+
| RINGKASAN (total, rata-rata, dst)                            |
| BLOK TANDA TANGAN (kosong)                                   |
| FOOTER: SI-APOTEK + waktu cetak                              |
+--------------------------------------------------------------+
```

## Jenis Laporan

### 1. LAPORAN PENJUALAN APOTEK (`jenis=penjualan`)
- **Orientasi**: Landscape (7 kolom).
- **Kolom**: No, Tanggal, ID Transaksi, Nama Pelanggan, Nama Kasir, Metode Pembayaran, Total Transaksi.
- **Ringkasan**: total penjualan, jumlah transaksi, jumlah item terjual, rata-rata nilai transaksi.
- **Sumber**: `tbl_transaksi_penjualan` + `tbl_item_transaksi` (agregasi item terpisah agar tidak menggandakan total).

### 2. LAPORAN PENJUALAN HARIAN (`jenis=harian`)
- **Orientasi**: Portrait.
- **Kolom**: No, Tanggal, Jumlah Transaksi, Total Penjualan.
- **Ringkasan**: total akumulasi penjualan, jumlah hari aktif.
- **Sumber**: `tbl_transaksi_penjualan` di-GROUP BY tanggal.

### 3. LAPORAN OBAT TERLARIS (`jenis=terlaris`)
- **Orientasi**: Portrait.
- **Kolom**: No, Nama Obat, Kategori, Jumlah Terjual, Harga, Total Nilai Penjualan.
- **Urutan**: jumlah terjual terbanyak.
- **Sumber**: `tbl_item_transaksi` (detail transaksi) + `tbl_obat`.

### 4. LAPORAN KINERJA TRANSAKSI KASIR (`jenis=kasir`)
- **Orientasi**: Portrait.
- **Kolom**: No, Nama Kasir, Jumlah Transaksi, Total Penjualan, Rata-rata Transaksi.
- **Sumber**: `tbl_transaksi_penjualan` + `tbl_kasir`.

### 5. LAPORAN PERSEDIAAN OBAT (`jenis=stok`)
- **Orientasi**: Landscape (8 kolom).
- **Kolom**: No, Nama Obat, Kategori, Stok Tersedia, Stok Minimum, Harga, Nilai Persediaan, Nama Apotek.
- **Nilai persediaan** = `stok × harga`.
- **Ringkasan**: total jenis obat, total unit stok, obat pada stok minimum, obat perlu segera ditambah.
- **Sumber**: `tbl_obat` + `tbl_apotek`.

### 6. LAPORAN OBAT KEDALUWARSA (`jenis=kedaluwarsa`)
- **Orientasi**: Portrait.
- **Kolom**: No, Nama Obat, Kategori, Jumlah Stok, Tanggal Kedaluwarsa, Status, Nama Apotek.
- **Status** (`sisa_hari` = `DATEDIFF(expired_date, CURDATE())`):
  - `< 0` → **Kedaluwarsa** (merah).
  - `0–30 hari` → **Akan kedaluwarsa ≤ 30 hari** (kuning).
  - `31–90 hari` → **Akan kedaluwarsa ≤ 90 hari** (abu).
- **Sumber**: `tbl_obat` (expired ≤ +90 hari dari tanggal aktual).

## Identitas Apotek pada Kop

- Diambil dari `tbl_apotek` (nama, alamat, telepon) — **bukan teks statis**.
- Bila filter apotek spesifik dipilih → tampilkan identitas apotek tsb.
- Bila "Semua Apotek" → tampilkan identitas generik "SI-APOTEK".
- Alamat/telepon kosong → tidak ditampilkan (tidak dikarang).

## Cara Menambah Jenis Laporan Baru

1. Tambah label di `LaporanController::JENIS_LABEL`.
2. Tambah `case` baru di `loadReport()` (kolom + ringkasan + orientasi).
3. Tidak perlu ubah view — view & export bersifat generik (berbasis `columns`).

## Format Data

- **Rupiah**: `format_uang()` → `Rp 3.500`.
- **Tanggal**: `format_tanggal()` → `DD/MM/YYYY`.
- **Angka**: rata kanan.
- **Status kedaluwarsa**: badge berwarna.
