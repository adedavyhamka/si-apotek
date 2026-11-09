<?php
/**
 * Tes koneksi database & validasi hash password (TAHAP 2).
 * Jalankan: php tests/db_test.php
 */
declare(strict_types=1);

$host = 'localhost';
$db   = 'db_si_apotek';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    echo "Koneksi PDO: OK\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Koneksi PDO GAGAL: " . $e->getMessage() . "\n");
    exit(1);
}

// Ambil hash password user 'admin' dari database
$stmt = $pdo->prepare("SELECT username, password, role FROM tbl_user WHERE username = :u");
$stmt->execute([':u' => 'admin']);
$row = $stmt->fetch();

if (!$row) {
    fwrite(STDERR, "User 'admin' tidak ditemukan di tabel tbl_user\n");
    exit(1);
}

$okTrue  = password_verify('admin123', $row['password']);
$okFalse = password_verify('password-salah', $row['password']);

printf("User: %s | Role: %s\n", $row['username'], $row['role']);
printf("password_verify('admin123')           => %s (harap: true)\n", $okTrue ? 'true' : 'false');
printf("password_verify('password-salah')     => %s (harap: false)\n", $okFalse ? 'true' : 'false');

$pass = $okTrue && !$okFalse;
echo $pass ? "HASIL: PASS\n" : "HASIL: FAIL\n";
exit($pass ? 0 : 1);
