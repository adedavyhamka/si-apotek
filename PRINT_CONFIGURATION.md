# PRINT CONFIGURATION — SI-APOTEK

Konfigurasi mekanisme cetak & ekspor dokumen laporan.

## Prinsip

Aplikasi **tidak bergantung pada library PDF server-side** (agar kompatibel
dengan shared hosting InfinityFree). Cetak PDF memakai **print dialog browser**
(`window.print()` → "Save as PDF"), dengan CSS `@page` & `@media print` untuk
layout A4 yang presisi.

## File Terkait

| File | Fungsi |
|---|---|
| `app/assets/css/report.css` | Stylesheet laporan + aturan cetak (`@page`, `@media print`) |
| `app/views/layouts/print.php` | Layout khusus cetak (tanpa sidebar/topbar) |
| `app/views/laporan/cetak.php` | Isi dokumen laporan (kop, tabel, ringkasan, footer) |
| `app/views/partials/report_header.php` | Kop laporan (logo + identitas apotek) |

## Orientasi & Ukuran

```css
@page { size: A4 portrait; margin: 14mm 13mm; }
@page landscape { size: A4 landscape; }
.report-landscape { page: landscape; }
```

- **Portrait**: harian, terlaris, kasir, kedaluwarsa.
- **Landscape**: penjualan (7 kolom), stok (8 kolom).

Kelas `report-landscape` disisipkan pada `<body>` halaman cetak oleh
`print.php` berdasarkan `$report['landscape']`.

## Aturan Cetak (`@media print`)

- Menyembunyikan sidebar, topbar, tombol, alert (`.no-print`, dll).
- `thead { display: table-header-group; }` — header tabel berulang tiap halaman.
- `tr { page-break-inside: avoid; }` — baris tidak terpotong antar halaman.
- `print-color-adjust: exact` — warna header tabel tetap tercetak.
- Memaksa latar putih agar terbaca saat cetak hitam-putih.

## Tombol & Alur

| Tombol | Aksi |
|---|---|
| Cetak Laporan | Buka `cetak?…&autoprint=1` (tab baru) → auto `window.print()` |
| Download PDF | Buka `cetak?…` (tab baru) → pengguna pilih "Save as PDF" |
| Export Excel | `export?format=excel` → file `.xls` (HTML, MIME `vnd.ms-excel`) |
| Export CSV | `export?format=csv` → file `.csv` (BOM UTF-8) |

## Kop & Penutup

- **Kop**: logo kiri, identitas apotek (dari `tbl_apotek`) kanan, garis pemisah.
- **Meta**: judul, periode, tanggal cetak, pengguna pencetak.
- **Footer**: "Dokumen dihasilkan secara otomatis oleh SI-APOTEK" + waktu cetak.
- **Blok tanda tangan**: ruang kosong (tanpa nama/ttd palsu).

## Validasi Filter

- `dari <= sampai` (ditolak bila terbalik).
- Filter berlaku konsisten pada tabel, ringkasan, cetak, PDF, Excel, CSV.
- Filter apotek (`id_apotek`) diterapkan per konteks tabel.
