<?php
/**
 * AuthController — login, logout.
 */
declare(strict_types=1);

class AuthController extends BaseController
{
    public function login(): void
    {
        if (Auth::check()) {
            redirect_route('dashboard');
        }
        $this->view('auth/login', ['title' => 'Masuk'], 'auth');
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_route('auth', 'login');
        }
        csrf_check();

        $username = req_str('username');
        $password = (string) ($_POST['password'] ?? '');

        $_SESSION['old'] = ['username' => $username];

        $remaining = Auth::lockoutRemaining($username);
        if ($remaining > 0) {
            flash_set('error', 'Terlalu banyak percobaan gagal. Coba lagi dalam '
                . ceil($remaining / 60) . ' menit.');
            redirect_route('auth', 'login');
        }

        if ($username === '' || $password === '') {
            flash_set('error', 'Username dan password wajib diisi.');
            redirect_route('auth', 'login');
        }

        if (Auth::attempt($username, $password)) {
            flash_set('success', 'Selamat datang, ' . current_user_name() . '!');
            redirect_route('dashboard');
        }

        flash_set('error', 'Username atau password salah.');
        redirect_route('auth', 'login');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect_route('auth', 'login');
    }
}
