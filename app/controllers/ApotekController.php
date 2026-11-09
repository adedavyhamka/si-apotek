<?php
/**
 * ApotekController — CRUD apotek (soft-delete).
 */
declare(strict_types=1);

class ApotekController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = (string) ($_GET['filter'] ?? 'aktif');

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(a.nama_apotek LIKE :q OR a.alamat LIKE :q OR a.no_telp LIKE :q OR p.nama LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($filter === 'aktif') {
            $where[] = "a.status = 'aktif'";
        } elseif ($filter === 'nonaktif') {
            $where[] = "a.status = 'nonaktif'";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_apotek a
             JOIN tbl_pemilik_apotek p ON a.id_pemilik = p.id_pemilik
             $whereSql",
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            "SELECT a.*, p.nama AS nama_pemilik,
                    (SELECT COUNT(*) FROM tbl_apoteker apk WHERE apk.id_apotek = a.id_apotek) AS jumlah_apoteker,
                    (SELECT COUNT(*) FROM tbl_kasir k WHERE k.id_apotek = a.id_apotek) AS jumlah_kasir
             FROM tbl_apotek a
             JOIN tbl_pemilik_apotek p ON a.id_pemilik = p.id_pemilik
             $whereSql
             ORDER BY a.nama_apotek ASC
             LIMIT :lim OFFSET :off",
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('apotek/index', [
            'title'      => 'Data Apotek',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
            'filter'     => $filter,
        ]);
    }

    public function tambah(): void
    {
        $this->view('apotek/form', [
            'title'   => 'Tambah Apotek',
            'data'    => null,
            'pemilik' => $this->pemilikList(),
        ]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apotek');
        }
        csrf_check();

        $nama = req_str('nama_apotek');
        $alamat = req_str('alamat');
        $noTelp = req_str('no_telp');
        $idPemilik = req_int('id_pemilik');

        if ($nama === '' || $idPemilik < 1) {
            $this->backWithInput($_POST, 'apotek', 'tambah');
        }

        Database::execute(
            'INSERT INTO tbl_apotek (nama_apotek, alamat, no_telp, id_pemilik) VALUES (:n, :a, :t, :p)',
            [':n' => $nama, ':a' => $alamat, ':t' => $noTelp, ':p' => $idPemilik]
        );
        $this->flashRedirect('apotek', 'index', 'success', 'Apotek berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_apotek WHERE id_apotek = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('apotek', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('apotek/form', [
            'title'   => 'Edit Apotek',
            'data'    => $data,
            'pemilik' => $this->pemilikList(),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apotek');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $nama = req_str('nama_apotek');
        $alamat = req_str('alamat');
        $noTelp = req_str('no_telp');
        $idPemilik = req_int('id_pemilik');

        if ($nama === '' || $idPemilik < 1) {
            $this->backWithInput($_POST, 'apotek', 'edit', ['id' => $id]);
        }

        Database::execute(
            'UPDATE tbl_apotek SET nama_apotek = :n, alamat = :a, no_telp = :t, id_pemilik = :p WHERE id_apotek = :id',
            [':n' => $nama, ':a' => $alamat, ':t' => $noTelp, ':p' => $idPemilik, ':id' => $id]
        );
        $this->flashRedirect('apotek', 'index', 'success', 'Apotek berhasil diperbarui.');
    }

    /** Nonaktifkan (soft-delete). */
    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apotek');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_apotek SET status = 'nonaktif' WHERE id_apotek = :id", [':id' => $id]);
        $this->flashRedirect('apotek', 'index', 'success', 'Apotek dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('apotek');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_apotek SET status = 'aktif' WHERE id_apotek = :id", [':id' => $id]);
        $this->flashRedirect('apotek', 'index', 'success', 'Apotek diaktifkan kembali.');
    }

    /** Daftar pemilik aktif untuk dropdown (id_pemilik + nama). */
    private function pemilikList(): array
    {
        return Database::fetchAll(
            "SELECT id_pemilik, nama FROM tbl_pemilik_apotek WHERE status = 'aktif' ORDER BY nama ASC"
        );
    }
}
