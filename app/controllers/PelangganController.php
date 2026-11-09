<?php
/**
 * PelangganController — CRUD pelanggan (soft-delete).
 */
declare(strict_types=1);

class PelangganController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = (string) ($_GET['filter'] ?? 'aktif');

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(nama_pelanggan LIKE :q OR no_telp LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($filter === 'aktif') {
            $where[] = "status = 'aktif'";
        } elseif ($filter === 'nonaktif') {
            $where[] = "status = 'nonaktif'";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_pelanggan $whereSql",
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            "SELECT p.*, (SELECT COUNT(*) FROM tbl_transaksi_penjualan t WHERE t.id_pelanggan = p.id_pelanggan) AS jumlah_transaksi
             FROM tbl_pelanggan p
             $whereSql
             ORDER BY p.nama_pelanggan ASC
             LIMIT :lim OFFSET :off",
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('pelanggan/index', [
            'title'      => 'Data Pelanggan',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
            'filter'     => $filter,
        ]);
    }

    public function tambah(): void
    {
        $this->view('pelanggan/form', ['title' => 'Tambah Pelanggan', 'data' => null]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pelanggan');
        }
        csrf_check();

        $nama = req_str('nama_pelanggan');
        $alamat = req_str('alamat');
        $noTelp = req_str('no_telp');
        $tglDaftar = req_str('tanggal_daftar');

        if ($nama === '') {
            $this->backWithInput($_POST, 'pelanggan', 'tambah');
        }

        if ($tglDaftar === '') {
            Database::execute(
                'INSERT INTO tbl_pelanggan (nama_pelanggan, alamat, no_telp) VALUES (:n, :a, :t)',
                [':n' => $nama, ':a' => $alamat, ':t' => $noTelp]
            );
        } else {
            Database::execute(
                'INSERT INTO tbl_pelanggan (nama_pelanggan, alamat, no_telp, tanggal_daftar) VALUES (:n, :a, :t, :td)',
                [':n' => $nama, ':a' => $alamat, ':t' => $noTelp, ':td' => $tglDaftar]
            );
        }
        $this->flashRedirect('pelanggan', 'index', 'success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_pelanggan WHERE id_pelanggan = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('pelanggan', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('pelanggan/form', ['title' => 'Edit Pelanggan', 'data' => $data]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pelanggan');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $nama = req_str('nama_pelanggan');
        $alamat = req_str('alamat');
        $noTelp = req_str('no_telp');
        $tglDaftar = req_str('tanggal_daftar');

        if ($nama === '') {
            $this->backWithInput($_POST, 'pelanggan', 'edit', ['id' => $id]);
        }

        if ($tglDaftar === '') {
            Database::execute(
                'UPDATE tbl_pelanggan SET nama_pelanggan = :n, alamat = :a, no_telp = :t, tanggal_daftar = NULL WHERE id_pelanggan = :id',
                [':n' => $nama, ':a' => $alamat, ':t' => $noTelp, ':id' => $id]
            );
        } else {
            Database::execute(
                'UPDATE tbl_pelanggan SET nama_pelanggan = :n, alamat = :a, no_telp = :t, tanggal_daftar = :td WHERE id_pelanggan = :id',
                [':n' => $nama, ':a' => $alamat, ':t' => $noTelp, ':td' => $tglDaftar, ':id' => $id]
            );
        }
        $this->flashRedirect('pelanggan', 'index', 'success', 'Pelanggan berhasil diperbarui.');
    }

    /** Nonaktifkan (soft-delete). */
    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pelanggan');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_pelanggan SET status = 'nonaktif' WHERE id_pelanggan = :id", [':id' => $id]);
        $this->flashRedirect('pelanggan', 'index', 'success', 'Pelanggan dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pelanggan');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_pelanggan SET status = 'aktif' WHERE id_pelanggan = :id", [':id' => $id]);
        $this->flashRedirect('pelanggan', 'index', 'success', 'Pelanggan diaktifkan kembali.');
    }
}
