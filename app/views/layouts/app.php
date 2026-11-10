<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/images/favicon-si-apotek.png">
    <link href="assets/css/app.css" rel="stylesheet">
    <link href="assets/css/report.css" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="assets/images/logo-si-apotek.png" alt="<?= APP_NAME ?>" class="brand-logo">
            <span><?= APP_NAME ?></span>
        </div>
        <nav class="sidebar-nav">
            <?php
            $menu = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'm' => 'dashboard', 'roles' => ['super_admin','pemilik','apoteker','kasir']],
                ['heading' => 'Data Master'],
                ['label' => 'Pemilik', 'icon' => 'bi-person-badge', 'm' => 'pemilik', 'roles' => ['super_admin','pemilik']],
                ['label' => 'Apotek', 'icon' => 'bi-shop', 'm' => 'apotek', 'roles' => ['super_admin','pemilik']],
                ['label' => 'Apoteker', 'icon' => 'bi-file-medical', 'm' => 'apoteker', 'roles' => ['super_admin','pemilik']],
                ['label' => 'Kasir', 'icon' => 'bi-cash-coin', 'm' => 'kasir', 'roles' => ['super_admin','pemilik']],
                ['label' => 'Obat', 'icon' => 'bi-capsule', 'm' => 'obat', 'roles' => ['super_admin','pemilik','apoteker']],
                ['label' => 'Pelanggan', 'icon' => 'bi-people', 'm' => 'pelanggan', 'roles' => ['super_admin','pemilik','kasir','apoteker']],
                ['heading' => 'Transaksi'],
                ['label' => 'Penjualan', 'icon' => 'bi-cart-check', 'm' => 'transaksi', 'roles' => ['super_admin','pemilik','kasir']],
                ['label' => 'Resep', 'icon' => 'bi-file-earmark-medical', 'm' => 'resep', 'roles' => ['super_admin','pemilik','apoteker']],
                ['heading' => 'Laporan'],
                ['label' => 'Laporan', 'icon' => 'bi-bar-chart-line', 'm' => 'laporan', 'roles' => ['super_admin','pemilik']],
                ['heading' => 'Pengaturan'],
                ['label' => 'Pengguna', 'icon' => 'bi-person-gear', 'm' => 'user', 'roles' => ['super_admin']],
                ['label' => 'Profil & Password', 'icon' => 'bi-person-circle', 'm' => 'profil', 'roles' => ['super_admin','pemilik','apoteker','kasir']],
            ];
            $role = current_role();
            $active = $_GET['m'] ?? 'dashboard';
            foreach ($menu as $item) {
                if (isset($item['heading'])) {
                    echo '<div class="sidebar-heading">' . e($item['heading']) . '</div>';
                    continue;
                }
                if (!in_array($role, $item['roles'], true)) {
                    continue;
                }
                $isActive = ($active === $item['m']) ? ' active' : '';
                echo '<a class="sidebar-link' . $isActive . '" href="' . url('m=' . $item['m']) . '">'
                    . '<i class="bi ' . $item['icon'] . '"></i><span>' . e($item['label']) . '</span></a>';
            }
            ?>
        </nav>
    </aside>

    <!-- Konten -->
    <div class="main-area">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn btn-light d-md-none" id="sidebarToggle" aria-label="Menu">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="role-badge"><?= e(ucwords(str_replace('_', ' ', current_role() ?? ''))) ?></span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="d-none d-sm-inline"><?= e(current_user_name()) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('m=profil') ?>"><i class="bi bi-person-circle me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('m=auth&a=logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <main class="content">
            <?php foreach (flash_all() as $type => $msg): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : e($type) ?> alert-dismissible fade show" role="alert">
                    <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
