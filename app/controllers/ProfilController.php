<?php
/**
 * ProfilController — lihat profil & ganti password.
 */
declare(strict_types=1);

class ProfilController extends BaseController
{
    public function index(): void
    {
        $user = Auth::user();
        $this->view('profil/index', ['title' => 'Profil & Password', 'user' => $user]);
    }

    public function gantiPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('profil');
        }
        csrf_check();

        $lama = (string) ($_POST['password_lama'] ?? '');
        $baru = (string) ($_POST['password_baru'] ?? '');
        $konfirmasi = (string) ($_POST['password_konfirmasi'] ?? '');

        $user = Auth::user();
        if (!$user) {
            redirect_route('auth', 'login');
        }

        if (!password_verify($lama, $user['password'])) {
            $this->flashRedirect('profil', 'index', 'error', 'Password lama salah.');
        }
        if (strlen($baru) < 6) {
            $this->flashRedirect('profil', 'index', 'error', 'Password baru minimal 6 karakter.');
        }
        if ($baru !== $konfirmasi) {
            $this->flashRedirect('profil', 'index', 'error', 'Konfirmasi password tidak cocok.');
        }

        Database::execute(
            'UPDATE tbl_user SET password = :p WHERE id_user = :id',
            [':p' => password_hash($baru, PASSWORD_BCRYPT), ':id' => (int) $user['id_user']]
        );

        // Perbarui sesi
        $_SESSION['user']['password'] = password_hash($baru, PASSWORD_BCRYPT);

        $this->flashRedirect('profil', 'index', 'success', 'Password berhasil diubah.');
    }
}
