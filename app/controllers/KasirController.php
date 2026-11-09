<?php
/**
 * KasirController — CRUD kasir (soft-delete), terhubung ke apotek.
 */
declare(strict_types=1);

class KasirController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = (string) ($_GET['filter'] ?? 'aktif');

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(k.nama_kasir LIKE :q OR k.no_telp LIKE :q OR a.nama_apotek LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($filter === 'aktif') {
            $where[] = "k.status = 'aktif'";
        } elseif ($filter === 'nonaktif') {
            $where[] = "k.status = 'nonaktif'";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) Database::fetch(
            "SELECT COUNT(*) AS c FROM tbl_kasir k JOIN tbl_apotek a ON a.id_apotek = k.id_apotek $whereSql",
            $params
        )['c'];

        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            "SELECT k.*, a.nama_apotek
             FROM tbl_kasir k
             JOIN tbl_apotek a ON a.id_apotek = k.id_apotek
             $whereSql
             ORDER BY k.nama_kasir ASC
             LIMIT :lim OFFSET :off",
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('kasir/index', [
            'title'      => 'Data Kasir',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
            'filter'     => $filter,
        ]);
    }

    public function tambah(): void
    {
        $this->view('kasir/form', [
            'title'  => 'Tambah Kasir',
            'data'   => null,
            'apotek' => $this->apotekList(),
        ]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('kasir');
        }
        csrf_check();

        $nama     = req_str('nama_kasir');
        $shift    = req_str('shift');
        $noTelp   = req_str('no_telp');
        $idApotek = req_int('id_apotek');

        if ($nama === '' || $idApotek <= 0) {
            $this->backWithInput($_POST, 'kasir', 'tambah');
        }

        Database::execute(
            'INSERT INTO tbl_kasir (nama_kasir, shift, no_telp, id_apotek) VALUES (:n, :s, :t, :a)',
            [':n' => $nama, ':s' => $shift, ':t' => $noTelp, ':a' => $idApotek]
        );
        $this->flashRedirect('kasir', 'index', 'success', 'Kasir berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_kasir WHERE id_kasir = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('kasir', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('kasir/form', [
            'title'  => 'Edit Kasir',
            'data'   => $data,
            'apotek' => $this->apotekList((int) $data['id_apotek']),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('kasir');
        }
        csrf_check();

        $id       = (int) ($_POST['id'] ?? 0);
        $nama     = req_str('nama_kasir');
        $shift    = req_str('shift');
        $noTelp   = req_str('no_telp');
        $idApotek = req_int('id_apotek');

        if ($nama === '' || $idApotek <= 0) {
            $this->backWithInput($_POST, 'kasir', 'edit', ['id' => $id]);
        }

        Database::execute(
            'UPDATE tbl_kasir SET nama_kasir = :n, shift = :s, no_telp = :t, id_apotek = :a WHERE id_kasir = :id',
            [':n' => $nama, ':s' => $shift, ':t' => $noTelp, ':a' => $idApotek, ':id' => $id]
        );
        $this->flashRedirect('kasir', 'index', 'success', 'Kasir berhasil diperbarui.');
    }

    /** Nonaktifkan (soft-delete). */
    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('kasir');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_kasir SET status = 'nonaktif' WHERE id_kasir = :id", [':id' => $id]);
        $this->flashRedirect('kasir', 'index', 'success', 'Kasir dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('kasir');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        Database::execute("UPDATE tbl_kasir SET status = 'aktif' WHERE id_kasir = :id", [':id' => $id]);
        $this->flashRedirect('kasir', 'index', 'success', 'Kasir diaktifkan kembali.');
    }

    /** Daftar apotek aktif untuk dropdown; sertakan apotek terpilih bila nonaktif. */
    private function apotekList(int $currentId = 0): array
    {
        $rows = Database::fetchAll(
            "SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE status = 'aktif' ORDER BY nama_apotek ASC"
        );
        if ($currentId > 0) {
            $found = false;
            foreach ($rows as $r) {
                if ((int) $r['id_apotek'] === $currentId) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $extra = Database::fetch(
                    'SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE id_apotek = :id',
                    [':id' => $currentId]
                );
                if ($extra) {
                    $rows[] = $extra;
                }
            }
        }
        return $rows;
    }
}
