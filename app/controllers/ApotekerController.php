<?php
/**
 * ApotekerController — CRUD apoteker (soft-delete).
 */
declare(strict_types=1);

class ApotekerController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = (string) ($_GET['filter'] ?? 'aktif');

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(ap.nama_apoteker LIKE :q OR ap.no_sipa LIKE :q OR ap.no_telp LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($filter === 'aktif') {
            $where[] = "ap.status = 'aktif'";
        } elseif ($filter === 'nonaktif') {
            $where[] = "ap.status = 'nonaktif'";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_apoteker ap $whereSql",
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            "SELECT ap.*, a.nama_apotek
             FROM tbl_apoteker ap
             LEFT JOIN tbl_apotek a ON a.id_apotek = ap.id_apotek
             $whereSql
             ORDER BY ap.nama_apoteker ASC
             LIMIT :lim OFFSET :off",
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('apoteker/index', [
            'title'      => 'Data Apoteker',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
            'filter'     => $filter,
        ]);
    }

    public function tambah(): void
    {
        $apotekList = Database::fetchAll(
            "SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE status = 'aktif' ORDER BY nama_apotek ASC"
        );
        $this->view('apoteker/form', ['title' => 'Tambah Apoteker', 'data' => null, 'apotekList' => $apotekList]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apoteker');
        }
        csrf_check();

        $nama = req_str('nama_apoteker');
        $noSipa = req_str('no_sipa');
        $noTelp = req_str('no_telp');
        $idApotek = req_int('id_apotek');

        if ($nama === '' || $idApotek <= 0) {
            $this->backWithInput($_POST, 'apoteker', 'tambah');
        }

        Database::execute(
            'INSERT INTO tbl_apoteker (nama_apoteker, no_sipa, no_telp, id_apotek) VALUES (:n, :s, :t, :a)',
            [':n' => $nama, ':s' => $noSipa, ':t' => $noTelp, ':a' => $idApotek]
        );
        $this->flashRedirect('apoteker', 'index', 'success', 'Apoteker berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_apoteker WHERE id_apoteker = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('apoteker', 'index', 'error', 'Data tidak ditemukan.');
        }
        $apotekList = Database::fetchAll(
            "SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE status = 'aktif' ORDER BY nama_apotek ASC"
        );
        $this->view('apoteker/form', ['title' => 'Edit Apoteker', 'data' => $data, 'apotekList' => $apotekList]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apoteker');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $nama = req_str('nama_apoteker');
        $noSipa = req_str('no_sipa');
        $noTelp = req_str('no_telp');
        $idApotek = req_int('id_apotek');

        if ($nama === '' || $idApotek <= 0) {
            $this->backWithInput($_POST, 'apoteker', 'edit', ['id' => $id]);
        }

        Database::execute(
            'UPDATE tbl_apoteker SET nama_apoteker = :n, no_sipa = :s, no_telp = :t, id_apotek = :a WHERE id_apoteker = :id',
            [':n' => $nama, ':s' => $noSipa, ':t' => $noTelp, ':a' => $idApotek, ':id' => $id]
        );
        $this->flashRedirect('apoteker', 'index', 'success', 'Apoteker berhasil diperbarui.');
    }

    /** Nonaktifkan (soft-delete). */
    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apoteker');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_apoteker SET status = 'nonaktif' WHERE id_apoteker = :id", [':id' => $id]);
        $this->flashRedirect('apoteker', 'index', 'success', 'Apoteker dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apoteker');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_apoteker SET status = 'aktif' WHERE id_apoteker = :id", [':id' => $id]);
        $this->flashRedirect('apoteker', 'index', 'success', 'Apoteker diaktifkan kembali.');
    }
}
