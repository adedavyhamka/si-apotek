<?php
/**
 * LaporanController — 6 jenis laporan profesional + cetak & export.
 *
 * Jenis laporan:
 *   penjualan   : LAPORAN PENJUALAN APOTEK (detail transaksi) — LANDSCAPE
 *   harian      : LAPORAN PENJUALAN HARIAN (ringkas per tanggal)
 *   terlaris    : LAPORAN OBAT TERLARIS
 *   kasir       : LAPORAN KINERJA TRANSAKSI KASIR
 *   stok        : LAPORAN PERSEDIAAN OBAT — LANDSCAPE
 *   kedaluwarsa : LAPORAN OBAT KEDALUWARSA
 */
declare(strict_types=1);

class LaporanController extends BaseController
{
    private const JENIS_LABEL = [
        'penjualan'   => 'LAPORAN PENJUALAN APOTEK',
        'harian'      => 'LAPORAN PENJUALAN HARIAN',
        'terlaris'    => 'LAPORAN OBAT TERLARIS',
        'kasir'       => 'LAPORAN KINERJA TRANSAKSI KASIR',
        'stok'        => 'LAPORAN PERSEDIAAN OBAT',
        'kedaluwarsa' => 'LAPORAN OBAT KEDALUWARSA',
    ];

    private function parseFilter(): array
    {
        $jenis = (string) ($_GET['jenis'] ?? 'penjualan');
        if (!isset(self::JENIS_LABEL[$jenis])) {
            $jenis = 'penjualan';
        }
        $dari = trim((string) ($_GET['dari'] ?? ''));
        $sampai = trim((string) ($_GET['sampai'] ?? ''));
        $idApotek = (int) ($_GET['id_apotek'] ?? 0);
        return [
            'jenis'     => $jenis,
            'dari'      => $dari !== '' ? $dari : date('Y-m-01'),
            'sampai'    => $sampai !== '' ? $sampai : date('Y-m-d'),
            'id_apotek' => $idApotek > 0 ? $idApotek : null,
        ];
    }

    private function validateRange(string $dari, string $sampai): ?string
    {
        $d = strtotime($dari);
        $s = strtotime($sampai);
        if ($d === false || $s === false) {
            return 'Format tanggal tidak valid.';
        }
        if ($d > $s) {
            return 'Tanggal awal tidak boleh melebihi tanggal akhir.';
        }
        return null;
    }

    private function apotekInfo(?int $idApotek): ?array
    {
        if ($idApotek === null) {
            return null;
        }
        return Database::fetch(
            'SELECT nama_apotek, alamat, no_telp FROM tbl_apotek WHERE id_apotek = :id',
            [':id' => $idApotek]
        ) ?: null;
    }

    private function apotekList(): array
    {
        return Database::fetchAll("SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE status='aktif' ORDER BY nama_apotek");
    }

    /**
     * Muat data laporan.
     * Return: ['judul','landscape','apotek','columns','rows','ringkasan']
     * columns = [ ['key'=>..,'label'=>..], ... ]  (tanpa kolom "No").
     */
    private function loadReport(string $jenis, string $dari, string $sampai, ?int $idApotek): array
    {
        $apotek = $this->apotekInfo($idApotek);
        $params = [':dari' => $dari, ':sampai' => $sampai];
        $apotekParams = [];
        $trxApotek = $idApotek !== null ? 'AND k.id_apotek = :apotek' : '';
        $obatApotek = $idApotek !== null ? 'AND o.id_apotek = :apotek' : '';
        if ($idApotek !== null) {
            $params[':apotek'] = $idApotek;
            $apotekParams = [':apotek' => $idApotek];
        }

        switch ($jenis) {
            case 'penjualan':
                $rows = Database::fetchAll(
                    "SELECT DATE(t.tanggal) AS tanggal, t.no_transaksi, t.total,
                            t.metode_pembayaran, p.nama_pelanggan, k.nama_kasir
                     FROM tbl_transaksi_penjualan t
                     LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
                     JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai $trxApotek
                     ORDER BY t.tanggal ASC",
                    $params
                );
                $sum = Database::fetch(
                    "SELECT COALESCE(SUM(t.total),0) AS total, COUNT(*) AS jml_trx
                     FROM tbl_transaksi_penjualan t
                     JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai $trxApotek",
                    $params
                );
                $jmlItem = (int) Database::fetch(
                    "SELECT COALESCE(SUM(i.jumlah),0) AS c
                     FROM tbl_item_transaksi i
                     JOIN tbl_transaksi_penjualan t ON t.id_transaksi = i.id_transaksi
                     JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai $trxApotek",
                    $params
                )['c'];
                $jmlTrx = (int) $sum['jml_trx'];
                $rata = $jmlTrx > 0 ? ((float) $sum['total'] / $jmlTrx) : 0;
                return [
                    'judul'     => self::JENIS_LABEL[$jenis],
                    'landscape' => true,
                    'apotek'    => $apotek,
                    'columns'   => [
                        ['key' => 'tanggal', 'label' => 'Tanggal'],
                        ['key' => 'no_transaksi', 'label' => 'ID Transaksi'],
                        ['key' => 'nama_pelanggan', 'label' => 'Nama Pelanggan'],
                        ['key' => 'nama_kasir', 'label' => 'Nama Kasir'],
                        ['key' => 'metode_pembayaran', 'label' => 'Metode Pembayaran'],
                        ['key' => 'total', 'label' => 'Total Transaksi'],
                    ],
                    'rows'      => $rows,
                    'ringkasan' => [
                        ['label' => 'Total Keseluruhan Penjualan', 'value' => format_uang($sum['total'])],
                        ['label' => 'Jumlah Transaksi', 'value' => (string) $jmlTrx],
                        ['label' => 'Jumlah Item Terjual', 'value' => (string) $jmlItem],
                        ['label' => 'Rata-rata Nilai Transaksi', 'value' => format_uang($rata)],
                    ],
                ];

            case 'harian':
                $rows = Database::fetchAll(
                    "SELECT DATE(t.tanggal) AS tanggal, COUNT(*) AS jml_transaksi, SUM(t.total) AS total
                     FROM tbl_transaksi_penjualan t
                     JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai $trxApotek
                     GROUP BY DATE(t.tanggal) ORDER BY tanggal ASC",
                    $params
                );
                $total = 0.0;
                foreach ($rows as $r) {
                    $total += (float) $r['total'];
                }
                return [
                    'judul'     => self::JENIS_LABEL[$jenis],
                    'landscape' => false,
                    'apotek'    => $apotek,
                    'columns'   => [
                        ['key' => 'tanggal', 'label' => 'Tanggal'],
                        ['key' => 'jml_transaksi', 'label' => 'Jumlah Transaksi'],
                        ['key' => 'total', 'label' => 'Total Penjualan'],
                    ],
                    'rows'      => $rows,
                    'ringkasan' => [
                        ['label' => 'Total Akumulasi Penjualan', 'value' => format_uang($total)],
                        ['label' => 'Jumlah Hari Aktif', 'value' => (string) count($rows)],
                    ],
                ];

            case 'terlaris':
                $rows = Database::fetchAll(
                    "SELECT o.nama_obat, o.kategori, o.harga,
                            SUM(i.jumlah) AS jumlah_terjual, SUM(i.subtotal) AS total_nilai
                     FROM tbl_item_transaksi i
                     JOIN tbl_obat o ON o.id_obat = i.id_obat
                     JOIN tbl_transaksi_penjualan t ON t.id_transaksi = i.id_transaksi
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai $obatApotek
                     GROUP BY i.id_obat, o.nama_obat, o.kategori, o.harga
                     ORDER BY jumlah_terjual DESC",
                    $params
                );
                return [
                    'judul'     => self::JENIS_LABEL[$jenis],
                    'landscape' => false,
                    'apotek'    => $apotek,
                    'columns'   => [
                        ['key' => 'nama_obat', 'label' => 'Nama Obat'],
                        ['key' => 'kategori', 'label' => 'Kategori'],
                        ['key' => 'jumlah_terjual', 'label' => 'Jumlah Terjual'],
                        ['key' => 'harga', 'label' => 'Harga'],
                        ['key' => 'total_nilai', 'label' => 'Total Nilai Penjualan'],
                    ],
                    'rows'      => $rows,
                    'ringkasan' => [
                        ['label' => 'Jumlah Jenis Obat Terjual', 'value' => (string) count($rows)],
                    ],
                ];

            case 'kasir':
                $rows = Database::fetchAll(
                    "SELECT k.nama_kasir, COUNT(*) AS jml_transaksi, SUM(t.total) AS total,
                            ROUND(AVG(t.total), 2) AS rata_rata
                     FROM tbl_transaksi_penjualan t
                     JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
                     WHERE DATE(t.tanggal) BETWEEN :dari AND :sampai $trxApotek
                     GROUP BY k.id_kasir, k.nama_kasir
                     ORDER BY total DESC",
                    $params
                );
                return [
                    'judul'     => self::JENIS_LABEL[$jenis],
                    'landscape' => false,
                    'apotek'    => $apotek,
                    'columns'   => [
                        ['key' => 'nama_kasir', 'label' => 'Nama Kasir'],
                        ['key' => 'jml_transaksi', 'label' => 'Jumlah Transaksi'],
                        ['key' => 'total', 'label' => 'Total Penjualan'],
                        ['key' => 'rata_rata', 'label' => 'Rata-rata Transaksi'],
                    ],
                    'rows'      => $rows,
                    'ringkasan' => [
                        ['label' => 'Jumlah Kasir', 'value' => (string) count($rows)],
                    ],
                ];

            case 'stok':
                $rows = Database::fetchAll(
                    "SELECT o.nama_obat, o.kategori, o.stok, o.stok_minimum, o.harga,
                            (o.stok * o.harga) AS nilai_persediaan, a.nama_apotek
                     FROM tbl_obat o JOIN tbl_apotek a ON a.id_apotek = o.id_apotek
                     WHERE o.status = 'aktif' $obatApotek
                     ORDER BY o.stok ASC, o.nama_obat ASC",
                    $apotekParams
                );
                $totalJenis = count($rows);
                $totalUnit = 0;
                $stokMin = 0;
                $perluTambah = 0;
                foreach ($rows as $r) {
                    $totalUnit += (int) $r['stok'];
                    if ((int) $r['stok'] <= (int) $r['stok_minimum']) {
                        $perluTambah++;
                    }
                    if ((int) $r['stok'] === (int) $r['stok_minimum']) {
                        $stokMin++;
                    }
                }
                return [
                    'judul'     => self::JENIS_LABEL[$jenis],
                    'landscape' => true,
                    'apotek'    => $apotek,
                    'columns'   => [
                        ['key' => 'nama_obat', 'label' => 'Nama Obat'],
                        ['key' => 'kategori', 'label' => 'Kategori'],
                        ['key' => 'stok', 'label' => 'Stok Tersedia'],
                        ['key' => 'stok_minimum', 'label' => 'Stok Minimum'],
                        ['key' => 'harga', 'label' => 'Harga'],
                        ['key' => 'nilai_persediaan', 'label' => 'Nilai Persediaan'],
                        ['key' => 'nama_apotek', 'label' => 'Nama Apotek'],
                    ],
                    'rows'      => $rows,
                    'ringkasan' => [
                        ['label' => 'Total Jenis Obat', 'value' => (string) $totalJenis],
                        ['label' => 'Total Unit Stok', 'value' => (string) $totalUnit],
                        ['label' => 'Obat pada Stok Minimum', 'value' => (string) $stokMin],
                        ['label' => 'Obat yang Perlu Segera Ditambah', 'value' => (string) $perluTambah],
                    ],
                ];

            case 'kedaluwarsa':
            default:
                $rows = Database::fetchAll(
                    "SELECT o.nama_obat, o.kategori, o.stok, o.expired_date,
                            DATEDIFF(o.expired_date, CURDATE()) AS sisa_hari, a.nama_apotek
                     FROM tbl_obat o JOIN tbl_apotek a ON a.id_apotek = o.id_apotek
                     WHERE o.status = 'aktif' AND o.expired_date IS NOT NULL
                       AND o.expired_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                       $obatApotek
                     ORDER BY o.expired_date ASC",
                    $apotekParams
                );
                return [
                    'judul'     => self::JENIS_LABEL['kedaluwarsa'],
                    'landscape' => false,
                    'apotek'    => $apotek,
                    'columns'   => [
                        ['key' => 'nama_obat', 'label' => 'Nama Obat'],
                        ['key' => 'kategori', 'label' => 'Kategori'],
                        ['key' => 'stok', 'label' => 'Jumlah Stok'],
                        ['key' => 'expired_date', 'label' => 'Tanggal Kedaluwarsa'],
                        ['key' => 'sisa_hari', 'label' => 'Status'],
                        ['key' => 'nama_apotek', 'label' => 'Nama Apotek'],
                    ],
                    'rows'      => $rows,
                    'ringkasan' => [
                        ['label' => 'Jumlah Obat (Kedaluwarsa / Mendekati)', 'value' => (string) count($rows)],
                    ],
                ];
        }
    }

    public function index(): void
    {
        $f = $this->parseFilter();
        $error = $this->validateRange($f['dari'], $f['sampai']);

        $report = null;
        if ($error === null) {
            $report = $this->loadReport($f['jenis'], $f['dari'], $f['sampai'], $f['id_apotek']);
        }

        $this->view('laporan/index', [
            'title'      => 'Laporan',
            'jenis'      => $f['jenis'],
            'dari'       => $f['dari'],
            'sampai'     => $f['sampai'],
            'id_apotek'  => $f['id_apotek'],
            'apoteks'    => $this->apotekList(),
            'jenisLabel' => self::JENIS_LABEL,
            'error'      => $error,
            'report'     => $report,
            'pengguna'   => current_user_name(),
        ]);
    }

    public function cetak(): void
    {
        $f = $this->parseFilter();
        $error = $this->validateRange($f['dari'], $f['sampai']);
        if ($error !== null) {
            $this->flashRedirect('laporan', 'index', 'error', $error);
        }

        $report = $this->loadReport($f['jenis'], $f['dari'], $f['sampai'], $f['id_apotek']);

        $this->view('laporan/cetak', [
            'title'    => $report['judul'],
            'jenis'    => $f['jenis'],
            'dari'     => $f['dari'],
            'sampai'   => $f['sampai'],
            'report'   => $report,
            'pengguna' => current_user_name(),
        ], 'print');
    }

    public function export(): void
    {
        $f = $this->parseFilter();
        $format = ($_GET['format'] ?? 'csv') === 'excel' ? 'excel' : 'csv';
        $error = $this->validateRange($f['dari'], $f['sampai']);
        if ($error !== null) {
            flash_set('error', $error);
            redirect_route('laporan', 'index', $f);
        }

        $report = $this->loadReport($f['jenis'], $f['dari'], $f['sampai'], $f['id_apotek']);
        $format === 'excel' ? $this->exportExcel($report, $f) : $this->exportCsv($report, $f);
    }

    private function exportCsv(array $report, array $f): void
    {
        $filename = strtolower(str_replace(' ', '-', $report['judul'])) . '_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        $put = static function (array $row) use ($out): void {
            fputcsv($out, $row, ',', '"', '');
        };

        $put([$report['judul']]);
        if ($report['apotek']) {
            $put([$report['apotek']['nama_apotek'] . ($report['apotek']['alamat'] ? ' — ' . $report['apotek']['alamat'] : '') . ($report['apotek']['no_telp'] ? ' — ' . $report['apotek']['no_telp'] : '')]);
        }
        $put(['Periode: ' . format_tanggal($f['dari']) . ' s/d ' . format_tanggal($f['sampai'])]);
        $put([]);

        $labels = ['No'];
        foreach ($report['columns'] as $c) {
            $labels[] = $c['label'];
        }
        $put($labels);

        $no = 1;
        foreach ($report['rows'] as $r) {
            $line = [$no++];
            foreach ($report['columns'] as $c) {
                $line[] = (string) ($r[$c['key']] ?? '');
            }
            $put($line);
        }
        $put([]);
        foreach ($report['ringkasan'] as $s) {
            $put([$s['label'], $s['value']]);
        }
        fclose($out);
        exit;
    }

    private function exportExcel(array $report, array $f): void
    {
        $filename = strtolower(str_replace(' ', '-', $report['judul'])) . '_' . date('Ymd') . '.xls';
        $colspan = count($report['columns']) + 1;
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<html><head><meta charset="utf-8"></head><body>';
        echo '<table border="1">';
        echo '<tr><td colspan="' . $colspan . '" style="font-weight:bold;font-size:14px;">' . e($report['judul']) . '</td></tr>';
        if ($report['apotek']) {
            echo '<tr><td colspan="' . $colspan . '">' . e($report['apotek']['nama_apotek'])
                . ($report['apotek']['alamat'] ? ' — ' . e($report['apotek']['alamat']) : '')
                . ($report['apotek']['no_telp'] ? ' — ' . e($report['apotek']['no_telp']) : '') . '</td></tr>';
        }
        echo '<tr><td colspan="' . $colspan . '">Periode: ' . format_tanggal($f['dari']) . ' s/d ' . format_tanggal($f['sampai']) . '</td></tr>';
        echo '</table><table border="1">';

        echo '<tr><th style="background:#16834B;color:#fff;">No</th>';
        foreach ($report['columns'] as $c) {
            echo '<th style="background:#16834B;color:#fff;">' . e($c['label']) . '</th>';
        }
        echo '</tr>';

        $no = 1;
        foreach ($report['rows'] as $r) {
            echo '<tr><td>' . $no++ . '</td>';
            foreach ($report['columns'] as $c) {
                $v = $r[$c['key']] ?? '';
                echo '<td>' . e(report_cell_text($c['key'], $v)) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        echo '<table border="1"><tr><td colspan="' . $colspan . '"><b>Ringkasan</b></td></tr>';
        foreach ($report['ringkasan'] as $s) {
            echo '<tr><td>' . e($s['label']) . '</td><td colspan="' . ($colspan - 1) . '">' . e($s['value']) . '</td></tr>';
        }
        echo '</table></body></html>';
        exit;
    }
}
