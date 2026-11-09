<?php
/**
 * ObatController — CRUD obat + stok masuk + riwayat mutasi.
 * Apoteker hanya melihat/mengelola obat di apoteknya sendiri.
 */
declare(strict_types=1);

class ObatController extends BaseController
{
    private const PER_PAGE = 10;

    /** Scope apotek untuk role apoteker. Return [whereSql, params]. */
    private function scope(): array
    {
        if (current_role() === 'apoteker') {
            $aid = user_apotek_id();
            if ($aid !== null) {
                return ['o.id_apotek = :aid', [':aid' => $aid]];
            }
        }
        return ['1=1', []];
    }

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $kategori = trim((string) ($_GET['kategori'] ?? ''));
        $filter = (string) ($_GET['filter'] ?? 'aktif');

        [$scopeSql, $params] = $this->scope();

        $where = [$scopeSql];
        if ($q !== '') {
            $where[] = '(o.nama_obat LIKE :q OR o.kategori LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($kategori !== '') {
            $where[] = 'o.kategori = :kat';
            $params[':kat'] = $kategori;
        }
        if ($filter === 'aktif') {
            $where[] = "o.status = 'aktif'";
        } elseif ($filter === 'nonaktif') {
            $where[] = "o.status = 'nonaktif'";
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_obat o WHERE $whereSql",
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            "SELECT o.*, a.nama_apotek
             FROM tbl_obat o
             JOIN tbl_apotek a ON a.id_apotek = o.id_apotek
             WHERE $whereSql
             ORDER BY o.nama_obat ASC
             LIMIT :lim OFFSET :off",
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $kategoriList = Database::fetchAll(
            "SELECT DISTINCT kategori FROM tbl_obat WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori"
        );

        $this->view('obat/index', [
            'title'        => 'Data Obat',
            'rows'         => $rows,
            'pagination'   => $pagination,
            'q'            => $q,
            'kategori'     => $kategori,
            'filter'       => $filter,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function tambah(): void
    {
        $apoteks = $this->apotekOptions();
        $this->view('obat/form', ['title' => 'Tambah Obat', 'data' => null, 'apoteks' => $apoteks]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('obat');
        }
        csrf_check();

        $nama = req_str('nama_obat');
        $kategori = req_str('kategori');
        $dosis = req_str('dosis');
        $harga = req_float('harga');
        $stok = max(0, req_int('stok'));
        $stokMin = max(0, req_int('stok_minimum'));
        $expired = req_str('expired_date') ?: null;
        $idApotek = $this->resolveApotekId();

        if ($nama === '' || $idApotek === null) {
            $this->backWithInput($_POST, 'obat', 'tambah');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::execute(
                'INSERT INTO tbl_obat (nama_obat, kategori, dosis, harga, stok, stok_minimum, expired_date, id_apotek)
                 VALUES (:n, :k, :d, :h, :s, :sm, :e, :a)',
                [
                    ':n' => $nama, ':k' => $kategori, ':d' => $dosis, ':h' => $harga,
                    ':s' => $stok, ':sm' => $stokMin, ':e' => $expired, ':a' => $idApotek,
                ]
            );
            $idObat = (int) Database::lastInsertId();

            if ($stok > 0) {
                Database::execute(
                    'INSERT INTO tbl_stok_histori (id_obat, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, id_user)
                     VALUES (:o, :j, :jlh, 0, :s, :ket, :u)',
                    [
                        ':o' => $idObat, ':j' => 'masuk', ':jlh' => $stok, ':s' => $stok,
                        ':ket' => 'Stok awal', ':u' => (int) ($_SESSION['user_id'] ?? 0) ?: null,
                    ]
                );
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[Obat] ' . $e->getMessage());
            $this->flashRedirect('obat', 'index', 'error', 'Gagal menyimpan obat.');
        }

        $this->flashRedirect('obat', 'index', 'success', 'Obat berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_obat WHERE id_obat = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('obat', 'index', 'error', 'Data tidak ditemukan.');
        }
        $apoteks = $this->apotekOptions();
        $this->view('obat/form', ['title' => 'Edit Obat', 'data' => $data, 'apoteks' => $apoteks]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('obat');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $nama = req_str('nama_obat');
        $kategori = req_str('kategori');
        $dosis = req_str('dosis');
        $harga = req_float('harga');
        $stokMin = max(0, req_int('stok_minimum'));
        $expired = req_str('expired_date') ?: null;
        $idApotek = $this->resolveApotekId();

        if ($nama === '' || $idApotek === null) {
            $this->backWithInput($_POST, 'obat', 'edit', ['id' => $id]);
        }

        // Stok TIDAK diubah lewat edit; dikelola via stok masuk / transaksi.
        Database::execute(
            'UPDATE tbl_obat
             SET nama_obat = :n, kategori = :k, dosis = :d, harga = :h, stok_minimum = :sm,
                 expired_date = :e, id_apotek = :a
             WHERE id_obat = :id',
            [
                ':n' => $nama, ':k' => $kategori, ':d' => $dosis, ':h' => $harga,
                ':sm' => $stokMin, ':e' => $expired, ':a' => $idApotek, ':id' => $id,
            ]
        );
        $this->flashRedirect('obat', 'index', 'success', 'Obat berhasil diperbarui.');
    }

    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('obat');
        }
        csrf_check();
        Database::execute("UPDATE tbl_obat SET status = 'nonaktif' WHERE id_obat = :id", [':id' => (int) ($_POST['id'] ?? 0)]);
        $this->flashRedirect('obat', 'index', 'success', 'Obat dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('obat');
        }
        csrf_check();
        Database::execute("UPDATE tbl_obat SET status = 'aktif' WHERE id_obat = :id", [':id' => (int) ($_POST['id'] ?? 0)]);
        $this->flashRedirect('obat', 'index', 'success', 'Obat diaktifkan kembali.');
    }

    public function stokMasuk(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $obat = Database::fetch('SELECT * FROM tbl_obat WHERE id_obat = :id', [':id' => $id]);
        if (!$obat) {
            $this->flashRedirect('obat', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('obat/stok_masuk', ['title' => 'Stok Masuk', 'obat' => $obat]);
    }

    public function prosesStokMasuk(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('obat');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $jumlah = req_int('jumlah');
        $ket = req_str('keterangan');

        if ($jumlah <= 0) {
            $this->flashRedirect('obat', 'stokMasuk', 'error', 'Jumlah stok masuk minimal 1.', ['id' => $id]);
        }

        $obat = Database::fetch('SELECT * FROM tbl_obat WHERE id_obat = :id', [':id' => $id]);
        if (!$obat) {
            $this->flashRedirect('obat', 'index', 'error', 'Data tidak ditemukan.');
        }

        $sebelum = (int) $obat['stok'];
        $sesudah = $sebelum + $jumlah;

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::execute('UPDATE tbl_obat SET stok = :s WHERE id_obat = :id', [':s' => $sesudah, ':id' => $id]);
            Database::execute(
                'INSERT INTO tbl_stok_histori (id_obat, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, id_user)
                 VALUES (:o, :j, :jlh, :sb, :ss, :ket, :u)',
                [
                    ':o' => $id, ':j' => 'masuk', ':jlh' => $jumlah, ':sb' => $sebelum, ':ss' => $sesudah,
                    ':ket' => $ket !== '' ? $ket : 'Penambahan stok', ':u' => (int) ($_SESSION['user_id'] ?? 0) ?: null,
                ]
            );
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[StokMasuk] ' . $e->getMessage());
            $this->flashRedirect('obat', 'index', 'error', 'Gagal menambah stok.');
        }

        $this->flashRedirect('obat', 'index', 'success', 'Stok obat berhasil ditambah (' . $jumlah . ' unit).');
    }

    public function riwayat(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $obat = Database::fetch('SELECT * FROM tbl_obat WHERE id_obat = :id', [':id' => $id]);
        if (!$obat) {
            $this->flashRedirect('obat', 'index', 'error', 'Data tidak ditemukan.');
        }
        $riwayat = Database::fetchAll(
            'SELECT h.*, u.username FROM tbl_stok_histori h
             LEFT JOIN tbl_user u ON u.id_user = h.id_user
             WHERE h.id_obat = :id ORDER BY h.tanggal DESC, h.id_histori DESC LIMIT 100',
            [':id' => $id]
        );
        $this->view('obat/riwayat', ['title' => 'Riwayat Stok', 'obat' => $obat, 'riwayat' => $riwayat]);
    }

    /** Dropdown apotek (apoteker terkunci ke apoteknya). */
    private function apotekOptions(): array
    {
        if (current_role() === 'apoteker') {
            $aid = user_apotek_id();
            return $aid !== null ? Database::fetchAll('SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE id_apotek = :id', [':id' => $aid]) : [];
        }
        return Database::fetchAll("SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE status = 'aktif' ORDER BY nama_apotek");
    }

    private function resolveApotekId(): ?int
    {
        if (current_role() === 'apoteker') {
            return user_apotek_id();
        }
        $id = req_int('id_apotek');
        return $id > 0 ? $id : null;
    }
}
