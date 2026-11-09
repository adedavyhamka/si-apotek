<?php
/**
 * ResepController — CRUD resep dokter.
 */
declare(strict_types=1);

class ResepController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(r.no_resep LIKE :q OR r.nama_pasien LIKE :q OR r.nama_dokter LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $total = (int) Database::fetch(
            'SELECT COUNT(*) AS c FROM tbl_resep r WHERE ' . implode(' AND ', $where),
            $params
        )['c'];
        $pagination = paginate($total, self::PER_PAGE);

        $rows = Database::fetchAll(
            'SELECT r.*, p.nama_pelanggan, ap.nama_apoteker
             FROM tbl_resep r
             LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = r.id_pelanggan
             LEFT JOIN tbl_apoteker ap ON ap.id_apoteker = r.id_apoteker
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY r.tanggal_resep DESC, r.id_resep DESC
             LIMIT :lim OFFSET :off',
            $params + [':lim' => $pagination['perPage'], ':off' => $pagination['offset']]
        );

        $this->view('resep/index', [
            'title'      => 'Data Resep',
            'rows'       => $rows,
            'pagination' => $pagination,
            'q'          => $q,
        ]);
    }

    public function tambah(): void
    {
        $this->view('resep/form', [
            'title'       => 'Tambah Resep',
            'data'        => null,
            'pelanggans'  => $this->pelangganOptions(),
            'apotekers'   => $this->apotekerOptions(),
        ]);
    }

    public function simpan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('resep');
        }
        csrf_check();

        $noResep = req_str('no_resep');
        $dokter = req_str('nama_dokter');
        $pasien = req_str('nama_pasien');
        $tanggal = req_str('tanggal_resep') ?: date('Y-m-d');
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
        $idPelanggan = req_int('id_pelanggan') ?: null;
        $idApoteker = req_int('id_apoteker') ?: null;

        if ($noResep === '') {
            $this->backWithInput($_POST, 'resep', 'tambah');
        }

        Database::execute(
            'INSERT INTO tbl_resep (no_resep, nama_dokter, nama_pasien, tanggal_resep, deskripsi, id_pelanggan, id_apoteker)
             VALUES (:no, :d, :p, :t, :desk, :pel, :apt)',
            [
                ':no' => $noResep, ':d' => $dokter, ':p' => $pasien, ':t' => $tanggal,
                ':desk' => $deskripsi, ':pel' => $idPelanggan, ':apt' => $idApoteker,
            ]
        );
        $this->flashRedirect('resep', 'index', 'success', 'Resep berhasil ditambahkan.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $data = Database::fetch('SELECT * FROM tbl_resep WHERE id_resep = :id', [':id' => $id]);
        if (!$data) {
            $this->flashRedirect('resep', 'index', 'error', 'Data tidak ditemukan.');
        }
        $this->view('resep/form', [
            'title'      => 'Edit Resep',
            'data'       => $data,
            'pelanggans' => $this->pelangganOptions(),
            'apotekers'  => $this->apotekerOptions(),
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('resep');
        }
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);
        $noResep = req_str('no_resep');
        $dokter = req_str('nama_dokter');
        $pasien = req_str('nama_pasien');
        $tanggal = req_str('tanggal_resep') ?: date('Y-m-d');
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
        $idPelanggan = req_int('id_pelanggan') ?: null;
        $idApoteker = req_int('id_apoteker') ?: null;

        Database::execute(
            'UPDATE tbl_resep SET no_resep = :no, nama_dokter = :d, nama_pasien = :p,
                    tanggal_resep = :t, deskripsi = :desk, id_pelanggan = :pel, id_apoteker = :apt
             WHERE id_resep = :id',
            [
                ':no' => $noResep, ':d' => $dokter, ':p' => $pasien, ':t' => $tanggal,
                ':desk' => $deskripsi, ':pel' => $idPelanggan, ':apt' => $idApoteker, ':id' => $id,
            ]
        );
        $this->flashRedirect('resep', 'index', 'success', 'Resep berhasil diperbarui.');
    }

    public function hapus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('resep');
        }
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        // Transaksi yang memakai resep ini akan tetap ada (id_resep di-set NULL).
        Database::execute('DELETE FROM tbl_resep WHERE id_resep = :id', [':id' => $id]);
        $this->flashRedirect('resep', 'index', 'success', 'Resep dihapus.');
    }

    private function pelangganOptions(): array
    {
        return Database::fetchAll("SELECT id_pelanggan, nama_pelanggan FROM tbl_pelanggan WHERE status='aktif' ORDER BY nama_pelanggan");
    }

    private function apotekerOptions(): array
    {
        return Database::fetchAll("SELECT id_apoteker, nama_apoteker FROM tbl_apoteker WHERE status='aktif' ORDER BY nama_apoteker");
    }
}
