<?php
/**
 * UserController — manajemen pengguna & role (khusus super_admin).
 */
declare(strict_types=1);

class UserController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.username LIKE :q OR u.nama_lengkap LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $total = (int) Database::fetch(
            'SELECT COUNT(*) AS c FROM tbl_user u WHERE ' . implode(' AND ', $where),
            $params
        )['c'];
        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            'SELECT u.*, a.nama_apotek
             FROM tbl_user u
             LEFT JOIN tbl_apotek a ON a.id_apotek = u.id_apotek
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY u.id_user ASC
             LIMIT :lim OFFSET :off',
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('user/index', [
            'title'      => 'Manajemen Pengguna',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
        ]);
    }

    public function tambah(): void
    {
        $this->view('user/form', [
            'title'     => 'Tambah Pengguna',
            'data'      => null,
            'apoteks'   => $this->apotekOptions(),
            'kasirs'    => $this->kasirOptions(),
            'apotekers' => $this->apotekerOptions(),
        ]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('user');
        }
        csrf_check();

        $username = req_str('username');
        $password = (string) ($_POST['password'] ?? '');
        $nama = req_str('nama_lengkap');
        $role = req_str('role');
        $idApotek = req_int('id_apotek') ?: null;
        $idKasir = req_int('id_kasir') ?: null;
        $idApoteker = req_int('id_apoteker') ?: null;

        if ($username === '' || $password === '' || $nama === '' || !in_array($role, ['super_admin', 'pemilik', 'apoteker', 'kasir'], true)) {
            $this->backWithInput($_POST, 'user', 'tambah');
        }

        $ada = Database::fetch('SELECT id_user FROM tbl_user WHERE username = :u', [':u' => $username]);
        if ($ada) {
            $this->flashRedirect('user', 'tambah', 'error', 'Username sudah digunakan.');
        }

        if (strlen($password) < 6) {
            $this->flashRedirect('user', 'tambah', 'error', 'Password minimal 6 karakter.');
        }

        if ($role === 'kasir') {
            $idApoteker = null;
        } elseif ($role === 'apoteker') {
            $idKasir = null;
        } else {
            $idKasir = $idApoteker = null;
        }

        Database::execute(
            'INSERT INTO tbl_user (username, password, nama_lengkap, role, id_apotek, id_kasir, id_apoteker)
             VALUES (:u, :p, :n, :r, :a, :k, :ap)',
            [
                ':u' => $username, ':p' => password_hash($password, PASSWORD_BCRYPT), ':n' => $nama,
                ':r' => $role, ':a' => $idApotek, ':k' => $idKasir, ':ap' => $idApoteker,
            ]
        );
        $this->flashRedirect('user', 'index', 'success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_user WHERE id_user = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('user', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('user/form', [
            'title'     => 'Edit Pengguna',
            'data'      => $data,
            'apoteks'   => $this->apotekOptions(),
            'kasirs'    => $this->kasirOptions(),
            'apotekers' => $this->apotekerOptions(),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('user');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $nama = req_str('nama_lengkap');
        $role = req_str('role');
        $idApotek = req_int('id_apotek') ?: null;
        $idKasir = req_int('id_kasir') ?: null;
        $idApoteker = req_int('id_apoteker') ?: null;
        $password = (string) ($_POST['password'] ?? '');

        if ($role === 'kasir') {
            $idApoteker = null;
        } elseif ($role === 'apoteker') {
            $idKasir = null;
        } else {
            $idKasir = $idApoteker = null;
        }

        if ($password !== '') {
            if (strlen($password) < 6) {
                $this->flashRedirect('user', 'edit', 'error', 'Password minimal 6 karakter.', ['id' => $id]);
            }
            Database::execute(
                'UPDATE tbl_user SET nama_lengkap = :n, role = :r, id_apotek = :a, id_kasir = :k, id_apoteker = :ap, password = :p WHERE id_user = :id',
                [
                    ':n' => $nama, ':r' => $role, ':a' => $idApotek, ':k' => $idKasir,
                    ':ap' => $idApoteker, ':p' => password_hash($password, PASSWORD_BCRYPT), ':id' => $id,
                ]
            );
        } else {
            Database::execute(
                'UPDATE tbl_user SET nama_lengkap = :n, role = :r, id_apotek = :a, id_kasir = :k, id_apoteker = :ap WHERE id_user = :id',
                [':n' => $nama, ':r' => $role, ':a' => $idApotek, ':k' => $idKasir, ':ap' => $idApoteker, ':id' => $id]
            );
        }
        $this->flashRedirect('user', 'index', 'success', 'Pengguna berhasil diperbarui.');
    }

    public function nonaktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('user');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            $this->flashRedirect('user', 'index', 'error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        Database::execute("UPDATE tbl_user SET status = 'nonaktif' WHERE id_user = :id", [':id' => $id]);
        $this->flashRedirect('user', 'index', 'success', 'Pengguna dinonaktifkan.');
    }

    public function aktifkan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('user');
        }
        csrf_check();
        Database::execute("UPDATE tbl_user SET status = 'aktif' WHERE id_user = :id", [':id' => (int) ($_POST['id'] ?? 0)]);
        $this->flashRedirect('user', 'index', 'success', 'Pengguna diaktifkan kembali.');
    }

    private function apotekOptions(): array
    {
        return Database::fetchAll("SELECT id_apotek, nama_apotek FROM tbl_apotek WHERE status='aktif' ORDER BY nama_apotek");
    }

    private function kasirOptions(): array
    {
        return Database::fetchAll("SELECT id_kasir, nama_kasir FROM tbl_kasir WHERE status='aktif' ORDER BY nama_kasir");
    }

    private function apotekerOptions(): array
    {
        return Database::fetchAll("SELECT id_apoteker, nama_apoteker FROM tbl_apoteker WHERE status='aktif' ORDER BY nama_apoteker");
    }
}
