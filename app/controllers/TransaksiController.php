<?php
/**
 * TransaksiController — Point of Sale (POS): keranjang, checkout atomik,
 * pengurangan stok, struk, riwayat.
 */
declare(strict_types=1);

class TransaksiController extends BaseController
{
    private const PER_PAGE = 10;

    /** Inisialisasi keranjang di sesi. */
    private function cart(): array
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        return $_SESSION['cart'];
    }

    private function apotekScopeWhere(string $alias = 'o'): array
    {
        $role = current_role();
        if ($role === 'kasir') {
            $aid = user_apotek_id();
            if ($aid !== null) {
                return ["{$alias}.id_apotek = :aid", [':aid' => $aid]];
            }
        }
        return ['1=1', []];
    }

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        [$scopeSql, $params] = $this->apotekScopeWhere();

        $where = [$scopeSql, "o.status = 'aktif'"];
        if ($q !== '') {
            $where[] = '(o.nama_obat LIKE :q OR o.kategori LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $obats = Database::fetchAll(
            'SELECT o.id_obat, o.nama_obat, o.kategori, o.harga, o.stok, o.expired_date
             FROM tbl_obat o WHERE ' . implode(' AND ', $where) . '
             ORDER BY o.nama_obat ASC LIMIT 50',
            $params
        );

        $cart = $this->cart();
        $total = 0;
        foreach ($cart as $it) {
            $total += $it['harga'] * $it['jumlah'];
        }

        $pelanggans = Database::fetchAll("SELECT id_pelanggan, nama_pelanggan FROM tbl_pelanggan WHERE status='aktif' ORDER BY nama_pelanggan");
        $reseps = Database::fetchAll("SELECT id_resep, no_resep, nama_pasien FROM tbl_resep ORDER BY id_resep DESC LIMIT 50");
        $kasirs = Database::fetchAll("SELECT id_kasir, nama_kasir FROM tbl_kasir WHERE status='aktif' ORDER BY nama_kasir");

        $this->view('transaksi/index', [
            'title'      => 'Transaksi Penjualan',
            'obats'      => $obats,
            'cart'       => $cart,
            'total'      => $total,
            'q'          => $q,
            'pelanggans' => $pelanggans,
            'reseps'     => $reseps,
            'kasirs'     => $kasirs,
        ]);
    }

    public function tambahItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('transaksi');
        }
        csrf_check();

        $idObat = (int) ($_POST['id_obat'] ?? 0);
        $obat = Database::fetch(
            "SELECT id_obat, nama_obat, harga, stok FROM tbl_obat WHERE id_obat = :id AND status = 'aktif'",
            [':id' => $idObat]
        );

        if (!$obat) {
            $this->flashRedirect('transaksi', 'index', 'error', 'Obat tidak ditemukan atau nonaktif.');
        }

        $cart = $this->cart();
        $ada = $cart[$idObat]['jumlah'] ?? 0;

        if ($ada + 1 > (int) $obat['stok']) {
            $this->flashRedirect('transaksi', 'index', 'error', "Stok '{$obat['nama_obat']}' tidak mencukupi (sisa " . (int) $obat['stok'] . ').');
        }

        if (isset($cart[$idObat])) {
            $cart[$idObat]['jumlah']++;
        } else {
            $cart[$idObat] = [
                'id_obat'   => (int) $obat['id_obat'],
                'nama_obat' => $obat['nama_obat'],
                'harga'     => (float) $obat['harga'],
                'jumlah'    => 1,
            ];
        }
        $_SESSION['cart'] = $cart;
        redirect_route('transaksi', 'index');
    }

    public function ubahItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('transaksi');
        }
        csrf_check();

        $idObat = (int) ($_POST['id_obat'] ?? 0);
        $jumlah = req_int('jumlah');
        $cart = $this->cart();

        if (!isset($cart[$idObat])) {
            redirect_route('transaksi');
        }

        $obat = Database::fetch('SELECT stok FROM tbl_obat WHERE id_obat = :id', [':id' => $idObat]);
        $stok = $obat ? (int) $obat['stok'] : 0;

        if ($jumlah <= 0) {
            unset($cart[$idObat]);
        } elseif ($jumlah > $stok) {
            $this->flashRedirect('transaksi', 'index', 'error', "Jumlah melebihi stok (sisa {$stok}).");
        } else {
            $cart[$idObat]['jumlah'] = $jumlah;
        }
        $_SESSION['cart'] = $cart;
        redirect_route('transaksi', 'index');
    }

    public function hapusItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('transaksi');
        }
        csrf_check();
        $idObat = (int) ($_POST['id_obat'] ?? 0);
        $cart = $this->cart();
        unset($cart[$idObat]);
        $_SESSION['cart'] = $cart;
        redirect_route('transaksi', 'index');
    }

    public function kosongkan(): void
    {
        $_SESSION['cart'] = [];
        redirect_route('transaksi', 'index');
    }

    /** Proses pembayaran — atomik (transaksi + stok). */
    public function checkout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('transaksi');
        }
        csrf_check();

        $cart = $this->cart();
        if (empty($cart)) {
            $this->flashRedirect('transaksi', 'index', 'error', 'Keranjang masih kosong.');
        }

        $metode = req_str('metode_pembayaran');
        if ($metode === '') {
            $metode = 'Tunai';
        }
        $idPelanggan = req_int('id_pelanggan') ?: null;
        $idResep = req_int('id_resep') ?: null;
        $idKasir = $this->resolveKasirId();

        if ($idKasir === null) {
            $this->flashRedirect('transaksi', 'index', 'error', 'Tidak ada kasir aktif. Hubungi administrator.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $total = 0;
            $items = [];

            // Validasi & kunci stok
            foreach ($cart as $it) {
                $obat = Database::fetch(
                    'SELECT id_obat, nama_obat, harga, stok FROM tbl_obat WHERE id_obat = :id AND status = :s FOR UPDATE',
                    [':id' => $it['id_obat'], ':s' => 'aktif']
                );
                if (!$obat) {
                    throw new RuntimeException("Obat tidak ditemukan: " . $it['nama_obat']);
                }
                if ((int) $obat['stok'] < $it['jumlah']) {
                    throw new RuntimeException("Stok tidak mencukupi untuk {$obat['nama_obat']} (sisa {$obat['stok']}).");
                }
                $harga = (float) $obat['harga'];
                $subtotal = $harga * $it['jumlah'];
                $total += $subtotal;
                $items[] = [
                    'id_obat'   => (int) $obat['id_obat'],
                    'nama'      => $obat['nama_obat'],
                    'harga'     => $harga,
                    'jumlah'    => (int) $it['jumlah'],
                    'subtotal'  => $subtotal,
                    'stok_sebelum' => (int) $obat['stok'],
                ];
            }

            $noTransaksi = $this->generateNoTransaksi();

            Database::execute(
                'INSERT INTO tbl_transaksi_penjualan (no_transaksi, total, metode_pembayaran, id_kasir, id_pelanggan, id_resep)
                 VALUES (:no, :total, :met, :kasir, :pel, :resep)',
                [
                    ':no' => $noTransaksi, ':total' => $total, ':met' => $metode,
                    ':kasir' => $idKasir, ':pel' => $idPelanggan, ':resep' => $idResep,
                ]
            );
            $idTransaksi = (int) Database::lastInsertId();

            foreach ($items as $it) {
                Database::execute(
                    'INSERT INTO tbl_item_transaksi (jumlah, harga_satuan, subtotal, id_transaksi, id_obat)
                     VALUES (:j, :h, :s, :t, :o)',
                    [
                        ':j' => $it['jumlah'], ':h' => $it['harga'], ':s' => $it['subtotal'],
                        ':t' => $idTransaksi, ':o' => $it['id_obat'],
                    ]
                );

                $stokSesudah = $it['stok_sebelum'] - $it['jumlah'];
                Database::execute(
                    'UPDATE tbl_obat SET stok = :s WHERE id_obat = :id',
                    [':s' => $stokSesudah, ':id' => $it['id_obat']]
                );
                Database::execute(
                    'INSERT INTO tbl_stok_histori (id_obat, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, id_user)
                     VALUES (:o, :j, :jlh, :sb, :ss, :ket, :u)',
                    [
                        ':o' => $it['id_obat'], ':j' => 'keluar', ':jlh' => $it['jumlah'],
                        ':sb' => $it['stok_sebelum'], ':ss' => $stokSesudah,
                        ':ket' => 'Penjualan ' . $noTransaksi, ':u' => (int) ($_SESSION['user_id'] ?? 0) ?: null,
                    ]
                );
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[Checkout] ' . $e->getMessage());
            $this->flashRedirect('transaksi', 'index', 'error', 'Transaksi gagal: ' . $e->getMessage());
        }

        $_SESSION['cart'] = [];
        $this->flashRedirect('transaksi', 'struk', 'success', 'Transaksi berhasil disimpan.', ['id' => $idTransaksi]);
    }

    public function struk(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $trx = $this->transaksiDetail($id);
        if (!$trx) {
            $this->flashRedirect('transaksi', 'riwayat', 'error', 'Transaksi tidak ditemukan.');
        }
        $this->view('transaksi/struk', ['title' => 'Struk Penjualan', 'trx' => $trx]);
    }

    public function riwayat(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $dari = trim((string) ($_GET['dari'] ?? ''));
        $sampai = trim((string) ($_GET['sampai'] ?? ''));

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(t.no_transaksi LIKE :q OR p.nama_pelanggan LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($dari !== '') {
            $where[] = 'DATE(t.tanggal) >= :dari';
            $params[':dari'] = $dari;
        }
        if ($sampai !== '') {
            $where[] = 'DATE(t.tanggal) <= :sampai';
            $params[':sampai'] = $sampai;
        }

        $total = (int) Database::fetch(
            'SELECT COUNT(*) AS c FROM tbl_transaksi_penjualan t
             LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
             WHERE ' . implode(' AND ', $where),
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            'SELECT t.*, p.nama_pelanggan, k.nama_kasir,
                    (SELECT COUNT(*) FROM tbl_item_transaksi i WHERE i.id_transaksi = t.id_transaksi) AS jumlah_item
             FROM tbl_transaksi_penjualan t
             LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY t.tanggal DESC
             LIMIT :lim OFFSET :off',
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('transaksi/riwayat', [
            'title'      => 'Riwayat Transaksi',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
            'dari'       => $dari,
            'sampai'     => $sampai,
        ]);
    }

    public function detail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $trx = $this->transaksiDetail($id);
        if (!$trx) {
            $this->flashRedirect('transaksi', 'riwayat', 'error', 'Transaksi tidak ditemukan.');
        }
        $this->view('transaksi/detail', ['title' => 'Detail Transaksi', 'trx' => $trx]);
    }

    private function transaksiDetail(int $id): ?array
    {
        $trx = Database::fetch(
            'SELECT t.*, p.nama_pelanggan, k.nama_kasir, r.no_resep, r.nama_pasien,
                    a.nama_apotek, a.alamat AS apotek_alamat, a.no_telp AS apotek_telp
             FROM tbl_transaksi_penjualan t
             LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
             JOIN tbl_kasir k ON k.id_kasir = t.id_kasir
             JOIN tbl_apotek a ON a.id_apotek = k.id_apotek
             LEFT JOIN tbl_resep r ON r.id_resep = t.id_resep
             WHERE t.id_transaksi = :id',
            [':id' => $id]
        );
        if (!$trx) {
            return null;
        }
        $trx['items'] = Database::fetchAll(
            'SELECT i.*, o.nama_obat FROM tbl_item_transaksi i
             JOIN tbl_obat o ON o.id_obat = i.id_obat
             WHERE i.id_transaksi = :id',
            [':id' => $id]
        );
        return $trx;
    }

    private function resolveKasirId(): ?int
    {
        $u = current_user();
        if (!empty($u['id_kasir'])) {
            return (int) $u['id_kasir'];
        }
        $post = req_int('id_kasir');
        if ($post > 0) {
            return $post;
        }
        $k = Database::fetch("SELECT id_kasir FROM tbl_kasir WHERE status = 'aktif' ORDER BY id_kasir LIMIT 1");
        return $k ? (int) $k['id_kasir'] : null;
    }

    private function generateNoTransaksi(): string
    {
        return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
    }
}
