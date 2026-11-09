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
// Development: tampilkan error. PRODUKSI: ubah display_errors menjadi '0'.
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

// ---- Sesi ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // aktifkan saat HTTPS tersedia
    ]);
    session_start();
}
