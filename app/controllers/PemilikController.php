<?php
/**
 * PemilikController — CRUD pemilik apotek (soft-delete).
 */
declare(strict_types=1);

class PemilikController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = (string) ($_GET['filter'] ?? 'aktif');

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(nama LIKE :q OR no_telp LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($filter === 'aktif') {
            $where[] = "status = 'aktif'";
        } elseif ($filter === 'nonaktif') {
            $where[] = "status = 'nonaktif'";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_pemilik_apotek $whereSql",
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            "SELECT p.*, (SELECT COUNT(*) FROM tbl_apotek a WHERE a.id_pemilik = p.id_pemilik) AS jumlah_apotek
             FROM tbl_pemilik_apotek p
             $whereSql
             ORDER BY p.nama ASC
             LIMIT :lim OFFSET :off",
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('pemilik/index', [
            'title'      => 'Data Pemilik Apotek',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
            'filter'     => $filter,
        ]);
    }

    public function tambah(): void
    {
        $this->view('pemilik/form', ['title' => 'Tambah Pemilik', 'data' => null]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pemilik');
        }
        csrf_check();

        $nama = req_str('nama');
        $alamat = req_str('alamat');
        $noTelp = req_str('no_telp');

        if ($nama === '') {
            $this->backWithInput($_POST, 'pemilik', 'tambah');
        }

        Database::execute(
            'INSERT INTO tbl_pemilik_apotek (nama, alamat, no_telp) VALUES (:n, :a, :t)',
            [':n' => $nama, ':a' => $alamat, ':t' => $noTelp]
        );
        $this->flashRedirect('pemilik', 'index', 'success', 'Pemilik berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_pemilik_apotek WHERE id_pemilik = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('pemilik', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('pemilik/form', ['title' => 'Edit Pemilik', 'data' => $data]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pemilik');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $nama = req_str('nama');
        $alamat = req_str('alamat');
        $noTelp = req_str('no_telp');

        if ($nama === '') {
            $this->backWithInput($_POST, 'pemilik', 'edit', ['id' => $id]);
        }

        Database::execute(
            'UPDATE tbl_pemilik_apotek SET nama = :n, alamat = :a, no_telp = :t WHERE id_pemilik = :id',
            [':n' => $nama, ':a' => $alamat, ':t' => $noTelp, ':id' => $id]
        );
        $this->flashRedirect('pemilik', 'index', 'success', 'Pemilik berhasil diperbarui.');
    }

    /** Nonaktifkan (soft-delete). */
    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pemilik');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_pemilik_apotek SET status = 'nonaktif' WHERE id_pemilik = :id", [':id' => $id]);
        $this->flashRedirect('pemilik', 'index', 'success', 'Pemilik dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('pemilik');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_pemilik_apotek SET status = 'aktif' WHERE id_pemilik = :id", [':id' => $id]);
        $this->flashRedirect('pemilik', 'index', 'success', 'Pemilik diaktifkan kembali.');
    }
}
