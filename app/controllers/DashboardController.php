<?php
/**
 * DashboardController — statistik & grafik halaman utama.
 */
declare(strict_types=1);

class DashboardController extends BaseController
{
    public function index(): void
    {
        $role = current_role();
        $aid = user_apotek_id();
        $scoped = in_array($role, ['kasir', 'apoteker'], true) && $aid !== null;

        $obatScope = $scoped ? 'AND o.id_apotek = :aid' : '';
        $trxScope = $scoped ? 'AND k.id_apotek = :aid' : '';
        $params = $scoped ? [':aid' => $aid] : [];

        // 1. Penjualan hari ini
        $penjualanHariIni = Database::fetch(
            "SELECT COALESCE(SUM(t.total),0) AS total
             FROM tbl_transaksi_penjualan t
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             WHERE DATE(t.tanggal) = CURDATE() $trxScope",
            $params
        )['total'];

        // 2. Transaksi hari ini
        $transaksiHariIni = Database::fetch(
            "SELECT COUNT(*) AS c
             FROM tbl_transaksi_penjualan t
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             WHERE DATE(t.tanggal) = CURDATE() $trxScope",
            $params
        )['c'];

        // 3. Total pelanggan
        $totalPelanggan = Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_pelanggan WHERE status = 'aktif'"
        )['c'];

        // 4. Total obat
        $totalObat = Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_obat o WHERE o.status = 'aktif' $obatScope",
            $params
        )['c'];

        // 5. Obat hampir habis (stok <= minimum, masih > 0)
        $obatHampirHabis = Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_obat o
             WHERE o.status = 'aktif' AND o.stok > 0 AND o.stok <= o.stok_minimum $obatScope",
            $params
        )['c'];

        // 6. Obat kedaluwarsa (expired <= hari ini)
        $obatKedaluwarsa = Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_obat o
             WHERE o.status = 'aktif' AND o.expired_date IS NOT NULL AND o.expired_date <= CURDATE() $obatScope",
            $params
        )['c'];

        // 7. Grafik penjualan 7 hari terakhir
        $harian = Database::fetchAll(
            "SELECT DATE(t.tanggal) AS d, COALESCE(SUM(t.total),0) AS total
             FROM tbl_transaksi_penjualan t
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             WHERE t.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) $trxScope
             GROUP BY DATE(t.tanggal) ORDER BY d ASC",
            $params
        );

        // 8. Grafik penjualan 6 bulan terakhir
        $bulanan = Database::fetchAll(
            "SELECT DATE_FORMAT(t.tanggal,'%Y-%m') AS m, COALESCE(SUM(t.total),0) AS total
             FROM tbl_transaksi_penjualan t
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             WHERE t.tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) $trxScope
             GROUP BY m ORDER BY m ASC",
            $params
        );

        // 9. Transaksi terbaru
        $terbaru = Database::fetchAll(
            "SELECT t.no_transaksi, t.tanggal, t.total, t.metode_pembayaran,
                    p.nama_pelanggan, ks.nama_kasir
             FROM tbl_transaksi_penjualan t
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             JOIN tbl_kasir ks ON ks.id_kasir = t.id_kasir
             LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
             WHERE 1=1 $trxScope
             ORDER BY t.tanggal DESC LIMIT 8",
            $params
        );

        // Daftar obat hampir habis (untuk tampilan detail)
        $listHampirHabis = Database::fetchAll(
            "SELECT o.nama_obat, o.stok, o.stok_minimum, o.expired_date
             FROM tbl_obat o
             WHERE o.status='aktif' AND o.stok > 0 AND o.stok <= o.stok_minimum $obatScope
             ORDER BY o.stok ASC LIMIT 5",
            $params
        );

        $this->view('dashboard/index', [
            'title'             => 'Dashboard',
            'penjualanHariIni'  => (float) $penjualanHariIni,
            'transaksiHariIni'  => (int) $transaksiHariIni,
            'totalPelanggan'    => (int) $totalPelanggan,
            'totalObat'         => (int) $totalObat,
            'obatHampirHabis'   => (int) $obatHampirHabis,
            'obatKedaluwarsa'   => (int) $obatKedaluwarsa,
            'harian'            => $harian,
            'bulanan'           => $bulanan,
            'terbaru'           => $terbaru,
            'listHampirHabis'   => $listHampirHabis,
        ]);
    }
}
