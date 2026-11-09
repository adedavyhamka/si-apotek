# USER GUIDE — SI-APOTEK

Panduan penggunaan aplikasi per peran pengguna.

## Peran (Role) & Hak Akses

| Modul | Super Admin | Pemilik | Apoteker | Kasir |
|---|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Pemilik | ✅ | ✅ | ❌ | ❌ |
| Apotek | ✅ | ✅ | ❌ | ❌ |
| Apoteker | ✅ | ✅ | ❌ | ❌ |
| Kasir | ✅ | ✅ | ❌ | ❌ |
| Obat | ✅ | ✅ | ✅ | ❌ |
| Pelanggan | ✅ | ✅ | ✅ | ✅ |
| Resep | ✅ | ✅ | ✅ | ❌ |
| Transaksi (POS) | ✅ | ✅ | ❌ | ✅ |
| Laporan | ✅ | ✅ | ❌ | ❌ |
| Pengguna | ✅ | ❌ | ❌ | ❌ |
| Profil & Password | ✅ | ✅ | ✅ | ✅ |

## Login & Keamanan

1. Buka halaman aplikasi → isi **username** & **password** → **Masuk**.
2. Ganti password pertama kali lewat **Profil & Password**.
3. Setelah 5× percobaan login gagal, akun terkunci 15 menit.

## Dashboard

Menampilkan ringkasan: penjualan hari ini, transaksi hari ini, total pelanggan,
total obat, obat hampir habis, obat kedaluwarsa, grafik penjualan, dan transaksi
terbaru.

## Data Master

Semua modul data master memiliki pola sama: **Daftar → Tambah → Edit →
Nonaktifkan/Aktifkan**. Tombol ✏️ edit, ❌ nonaktifkan (soft-delete), ✅ aktifkan.

- **Pemilik**: tambah/edit data pemilik apotek.
- **Apotek**: tambah apotek/cabang, pilih pemiliknya.
- **Apoteker**: data apoteker + nomor SIPA, pilih apotek penempatan.
- **Kasir**: data kasir + shift, pilih apotek penempatan.
- **Obat**: nama, kategori, dosis, harga, stok minimum, kedaluwarsa, apotek.
  - **Stok Masuk** (tombol ⬇️) untuk menambah stok.
  - **Riwayat** (tombol 🕐) untuk melihat mutasi stok.
  - Stok **tidak** diubah lewat form edit — kelola lewat Stok Masuk.
- **Pelanggan**: data pelanggan + riwayat transaksi (jumlah transaksi di daftar).

## Transaksi Penjualan (Kasir)

1. Buka menu **Penjualan**.
2. Cari obat di kolom kiri → tekan **+** untuk menambah ke keranjang.
3. Di keranjang: ubah jumlah / hapus item.
4. Pilih **metode pembayaran**, **pelanggan** (opsional), **resep** (opsional).
5. Tekan **Proses Pembayaran** → stok otomatis berkurang → muncul **struk**.
6. Tekan **Cetak** untuk mencetak struk.

> Stok divalidasi sebelum & saat checkout (atomik) — transaksi gagal jika stok
> tidak cukup, tanpa mengubah data.

## Resep

Input resep dokter: nomor resep, dokter, pasien, tanggal, deskripsi, dan relasi
ke pelanggan & apoteker yang melayani.

## Laporan

- Pilih rentang tanggal → **Tampilkan**.
- Lihat: penjualan harian, obat terlaris, transaksi per kasir, laporan stok,
  obat kedaluwarsa.
- **Export** ke CSV (Excel) untuk penjualan/obat terlaris/stok/kasir.
- **Cetak / PDF** menggunakan dialog cetak browser (save as PDF).

## Manajemen Pengguna (Super Admin)

- Tambah pengguna dengan role tertentu.
- Hubungkan pengguna role `kasir` ke data kasir, role `apoteker` ke data apoteker
  (agar bisa melakukan transaksi / tercatat sebagai pelayan resep).
- Nonaktifkan akun tanpa menghapusnya.

## Best Practice

- Gunakan akun `kasir` untuk operasional harian, `apoteker` untuk kelola stok.
- Pantau **Obat Hampir Habis** & **Obat Kedaluwarsa** di dashboard.
- Ekspor laporan berkala untuk arsip.
