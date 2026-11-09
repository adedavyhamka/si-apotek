<?php
/**
 * LaporanController — laporan penjualan, stok, obat terlaris, kasir.
 * Export: CSV (Excel) & cetak (PDF via browser).
 */
declare(strict_types=1);

class LaporanController extends BaseController
{
    public function index(): void
    {
        $dari = trim((string) ($_GET['dari'] ?? date('Y-m-01')));
        $sampai = trim((string) ($_GET['sampai'] ?? date('Y-m-d')));

        // Ringkasan periode
        $ringkasan = Database::fetch(
            'SELECT COALESCE(SUM(total),0) AS total_penjualan,
                    COUNT(*) AS total_transaksi
             FROM tbl_transaksi_penjualan
             WHERE DATE(tanggal) BETWEEN :dari AND :sampai',
            [':dari' => $dari, ':sampai' => $sampai]
        );

        // Penjualan harian
        $harian = Database::fetchAll(
            'SELECT DATE(tanggal) AS tgl, COUNT(*) AS jumlah_transaksi, SUM(total) AS total
             FROM tbl_transaksi_penjualan
             WHERE DATE(tanggal) BETWEEN :dari AND :sampai
             GROUP BY DATE(tanggal) ORDER BY tgl ASC',
            [':dari' => $dari, ':sampai' => $sampai]
        );

        // Obat terlaris
        $terlaris = Database::fetchAll(
            'SELECT o.nama_obat, SUM(i.jumlah) AS total_qty, SUM(i.subtotal) AS total_nilai
             FROM tbl_item_transaksi i
             JOIN tbl_obat o ON o.id_obat = i.id_obat
             JOIN tbl_transaksi_penjualan t ON t.id_transaksi = i.id_transaksi
             WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai
             GROUP BY i.id_obat, o.nama_obat
             ORDER BY total_qty DESC LIMIT 10',
            [':dari' => $dari, ':sampai' => $sampai]
        );

        // Transaksi per kasir
        $perKasir = Database::fetchAll(
            'SELECT k.nama_kasir, COUNT(*) AS jumlah_transaksi, SUM(t.total) AS total
             FROM tbl_transaksi_penjualan t
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai
             GROUP BY k.id_kasir, k.nama_kasir ORDER BY total DESC',
            [':dari' => $dari, ':sampai' => $sampai]
        );

        // Laporan stok
        $stok = Database::fetchAll(
            'SELECT o.nama_obat, o.kategori, o.stok, o.stok_minimum, o.expired_date, a.nama_apotek
             FROM tbl_obat o JOIN tbl_apotek a ON a.id_apotek = o.id_apotek
             WHERE o.status = :s ORDER BY o.stok ASC',
            [':s' => 'aktif']
        );

        // Obat kedaluwarsa
        $kedaluwarsa = Database::fetchAll(
            'SELECT o.nama_obat, o.stok, o.expired_date, a.nama_apotek
             FROM tbl_obat o JOIN tbl_apotek a ON a.id_apotek = o.id_apotek
             WHERE o.status = :s AND o.expired_date IS NOT NULL AND o.expired_date <= CURDATE()
             ORDER BY o.expired_date ASC',
            [':s' => 'aktif']
        );

        $this->view('laporan/index', [
            'title'      => 'Laporan',
            'dari'       => $dari,
            'sampai'     => $sampai,
            'ringkasan'  => $ringkasan,
            'harian'     => $harian,
            'terlaris'   => $terlaris,
            'perKasir'   => $perKasir,
            'stok'       => $stok,
            'kedaluwarsa'=> $kedaluwarsa,
        ]);
    }

    /** Export CSV (kompatibel Excel). Jenis: penjualan|terlaris|stok|kasir. */
    public function export(): void
    {
        $jenis = (string) ($_GET['jenis'] ?? 'penjualan');
        $dari = trim((string) ($_GET['dari'] ?? date('Y-m-01')));
        $sampai = trim((string) ($_GET['sampai'] ?? date('Y-m-d')));

        $filename = 'laporan_' . $jenis . '_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 agar Excel terbaca benar

        // Helper fputcsv dengan parameter escape eksplisit (hindari deprecation PHP 8.4+)
        $put = static function (array $row) use ($out): void {
            fputcsv($out, $row, ',', '"', '');
        };

        switch ($jenis) {
            case 'terlaris':
                $put( ['No', 'Nama Obat', 'Total Qty', 'Total Nilai (Rp)']);
                $rows = Database::fetchAll(
                    'SELECT o.nama_obat, SUM(i.jumlah) AS total_qty, SUM(i.subtotal) AS total_nilai
                     FROM tbl_item_transaksi i
                     JOIN tbl_obat o ON o.id_obat = i.id_obat
                     JOIN tbl_transaksi_penjualan t ON t.id_transaksi = i.id_transaksi
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai
                     GROUP BY i.id_obat, o.nama_obat ORDER BY total_qty DESC LIMIT 100',
                    [':dari' => $dari, ':sampai' => $sampai]
                );
                $no = 1;
                foreach ($rows as $r) {
                    $put( [$no++, $r['nama_obat'], $r['total_qty'], $r['total_nilai']]);
                }
                break;

            case 'stok':
                $put( ['Nama Obat', 'Kategori', 'Stok', 'Stok Minimum', 'Kedaluwarsa', 'Apotek']);
                $rows = Database::fetchAll(
                    "SELECT o.nama_obat, o.kategori, o.stok, o.stok_minimum, o.expired_date, a.nama_apotek
                     FROM tbl_obat o JOIN tbl_apotek a ON a.id_apotek = o.id_apotek WHERE o.status = 'aktif' ORDER BY o.stok ASC"
                );
                foreach ($rows as $r) {
                    $put( [$r['nama_obat'], $r['kategori'], $r['stok'], $r['stok_minimum'], $r['expired_date'], $r['nama_apotek']]);
                }
                break;

            case 'kasir':
                $put( ['Kasir', 'Jumlah Transaksi', 'Total (Rp)']);
                $rows = Database::fetchAll(
                    'SELECT k.nama_kasir, COUNT(*) AS jumlah_transaksi, SUM(t.total) AS total
                     FROM tbl_transaksi_penjualan t JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai
                     GROUP BY k.id_kasir, k.nama_kasir ORDER BY total DESC',
                    [':dari' => $dari, ':sampai' => $sampai]
                );
                foreach ($rows as $r) {
                    $put( [$r['nama_kasir'], $r['jumlah_transaksi'], $r['total']]);
                }
                break;

            case 'penjualan':
            default:
                $put( ['Tanggal', 'Jumlah Transaksi', 'Total (Rp)']);
                $rows = Database::fetchAll(
                    'SELECT DATE(tanggal) AS tgl, COUNT(*) AS jumlah_transaksi, SUM(total) AS total
                     FROM tbl_transaksi_penjualan WHERE DATE(tanggal) BETWEEN :dari AND :sampai
                     GROUP BY DATE(tanggal) ORDER BY tgl ASC',
                    [':dari' => $dari, ':sampai' => $sampai]
                );
                foreach ($rows as $r) {
                    $put( [$r['tgl'], $r['jumlah_transaksi'], $r['total']]);
                }
                break;
        }

        fclose($out);
        exit;
    }
}
