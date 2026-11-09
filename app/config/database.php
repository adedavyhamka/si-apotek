<?php
/**
 * Konfigurasi koneksi database.
 *
 * Nilai default adalah untuk development lokal.
 * Untuk produksi (InfinityFree), ganti nilai host/user/pass sesuai
 * panel hosting, atau set environment variable DB_HOST/DB_NAME/DB_USER/DB_PASS.
 * JANGAN commit kredensial produksi ke repository publik.
 */
declare(strict_types=1);

return [
    'host'    => getenv('DB_HOST') ?: 'localhost',
    'name'    => getenv('DB_NAME') ?: 'db_si_apotek',
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
