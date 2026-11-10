<?php
/**
 * Helper global — escaping, redirect, flash, CSRF, auth.
 */
declare(strict_types=1);

/* ---------- Output escaping (anti-XSS) ---------- */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/* ---------- Redirect & URL ---------- */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function url(string $query = ''): string
{
    return 'index.php' . ($query !== '' ? '?' . $query : '');
}

function redirect_route(string $module, string $action = 'index', array $extra = []): never
{
    $q = http_build_query(array_merge(['m' => $module, 'a' => $action], $extra));
    redirect('index.php?' . $q);
}

/* ---------- Flash messages ---------- */
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][$type] = $msg;
}

function flash_has(string $type): bool
{
    return isset($_SESSION['flash'][$type]);
}

function flash_get(string $type): ?string
{
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

/** @return array<string, string> */
function flash_all(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['_token'] ?? '';
    $valid = isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
    if (!$valid) {
        http_response_code(419);
        die('Token keamanan (CSRF) tidak valid. Silakan muat ulang halaman lalu coba lagi.');
    }
}

/* ---------- Auth (membaca session; logika ada di class Auth) ---------- */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** @return array<string, mixed>|null */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function current_user_name(): string
{
    return $_SESSION['user']['nama_lengkap'] ?? ($_SESSION['user']['username'] ?? '');
}

/** ID apotek scope user saat ini (untuk kasir/apoteker). */
function user_apotek_id(): ?int
{
    $v = $_SESSION['user']['id_apotek'] ?? null;
    return $v !== null ? (int) $v : null;
}

/* ---------- Input lama (repopulate form) ---------- */
function old(string $key, string $default = ''): string
{
    $v = $_SESSION['old'][$key] ?? $default;
    return e((string) $v);
}

function old_clear(): void
{
    unset($_SESSION['old']);
}

/* ---------- Validasi dasar ---------- */
function req_str(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function req_int(string $key): int
{
    return (int) ($_POST[$key] ?? 0);
}

function req_float(string $key): float
{
    return (float) str_replace(',', '.', (string) ($_POST[$key] ?? 0));
}

/* ---------- Format angka (IDR) ---------- */
function format_uang(float|int|string $v): string
{
    return 'Rp ' . number_format((float) $v, 0, ',', '.');
}

function format_tanggal(?string $d): string
{
    if (!$d) {
        return '-';
    }
    $ts = strtotime($d);
    return $ts === false ? $d : date('d/m/Y', $ts);
}

function format_datetime(?string $d): string
{
    if (!$d) {
        return '-';
    }
    $ts = strtotime($d);
    return $ts === false ? $d : date('d/m/Y H:i', $ts);
}

/* ---------- Format sel laporan ---------- */

/** Kunci kolom yang bernilai rupiah. */
function report_is_rupiah(string $key): bool
{
    return in_array($key, ['total', 'total_nilai', 'rata_rata', 'harga', 'nilai_persediaan', 'total_penjualan'], true);
}

/** Kunci kolom yang berupa angka bulat (rata kanan). */
function report_is_angka(string $key): bool
{
    return in_array($key, ['stok', 'stok_minimum', 'jml_transaksi', 'jumlah_terjual', 'jml_item'], true);
}

/** Teks polos (untuk Excel/CSV) dari nilai kolom. */
function report_cell_text(string $key, mixed $val): string
{
    if ($val === null || $val === '') {
        return '';
    }
    if (report_is_rupiah($key)) {
        return number_format((float) $val, 0, ',', '.');
    }
    if (in_array($key, ['tanggal', 'expired_date'], true)) {
        return format_tanggal((string) $val);
    }
    if ($key === 'sisa_hari') {
        $d = (int) $val;
        return $d < 0 ? 'Kedaluwarsa' : ($d . ' hari');
    }
    return (string) $val;
}

/**
 * Sel laporan untuk tampilan HTML. Return [html, classAlign].
 * classAlign: 'num' (rata kanan), 'ctr' (tengah), '' (kiri).
 */
function report_cell(string $key, mixed $val): array
{
    if ($val === null || $val === '') {
        return ['-', ''];
    }
    if (report_is_rupiah($key)) {
        return [format_uang($val), 'num'];
    }
    if (report_is_angka($key)) {
        return ['<span class="num">' . (string) (int) $val . '</span>', 'num'];
    }
    if (in_array($key, ['tanggal', 'expired_date'], true)) {
        return [format_tanggal((string) $val), 'ctr'];
    }
    if ($key === 'sisa_hari') {
        $d = (int) $val;
        if ($d < 0) {
            return ['<span class="badge-exp merah">Kedaluwarsa</span>', 'ctr'];
        }
        if ($d <= 30) {
            return ['<span class="badge-exp kuning">' . $d . ' hari</span>', 'ctr'];
        }
        return ['<span class="badge-exp abu">' . $d . ' hari</span>', 'ctr'];
    }
    return [e((string) $val), ''];
}

/* ---------- Pagination ---------- */
function paginate(int $total, int $perPage = 10): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    return [
        'page'       => $page,
        'perPage'    => $perPage,
        'total'      => $total,
        'totalPages' => $totalPages,
        'offset'     => ($page - 1) * $perPage,
    ];
}

/** URL halaman dengan parameter tambahan (preserve m & q). */
function page_url(int $page, array $extra = []): string
{
    $params = array_merge($_GET, ['page' => $page], $extra);
    unset($params['a']);
    return 'index.php?' . http_build_query($params);
}
