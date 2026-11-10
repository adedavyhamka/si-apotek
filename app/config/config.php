<?php
/**
 * Konfigurasi utama aplikasi SI-APOTEK.
 */
declare(strict_types=1);

define('APP_NAME', 'SI-APOTEK');
define('APP_VERSION', '1.0.0');

// Zona waktu Indonesia (WIB) — konsisten untuk tanggal transaksi & laporan
date_default_timezone_set('Asia/Jakarta');

// BASE_PATH = folder app/ (tempat index.php berada)
define('BASE_PATH', dirname(__DIR__));

// ---- Error handling ----
// PRODUKSI: display_errors '0' (jangan tampilkan detail error ke pengguna),
// cukup dicatat ke log. Ubah jadi '1' hanya saat debugging lokal.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

// ---- Sesi ----
// Shared hosting (InfinityFree) sering punya session.save_path default yang
// salah (/php_sessions) sehingga memicu error "ps_files_cleanup_dir ... Permission denied".
// Solusi: pakai direktori writable milik aplikasi.
$sessionDir = BASE_PATH . '/storage/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0770, true);
}
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    session_save_path($sessionDir);
}

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $secure, // aktif otomatis saat HTTPS
    ]);
    session_start();
}
