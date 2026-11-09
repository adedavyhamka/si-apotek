<?php $today = date('Y-m-d'); ?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Kelola data obat dan stok per apotek.</p>
    </div>
    <a href="<?= url('m=obat&a=tambah') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Obat
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="m" value="obat">
            <div class="col-sm-6 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari nama / kategori..."
                           value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= e($k['kategori']) ?>" <?= $kategori === $k['kategori'] ? 'selected' : '' ?>>
                            <?= e($k['kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <select name="filter" class="form-select">
                    <option value="aktif" <?= $filter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $filter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    <option value="semua" <?= $filter === 'semua' ? 'selected' : '' ?>>Semua</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <button class="btn btn-outline-secondary w-100">Terapkan</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Obat</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Kedaluwarsa</th>
                        <th>Status</th>
                        <th class="text-end" style="width:200px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada data obat.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <?php
                        $stok = (int) $r['stok'];
                        $exp = $r['expired_date'];
                        $isExpired = $exp !== null && $exp <= $today;
                        $isLow = !$isExpired && $stok > 0 && $stok <= (int) $r['stok_minimum'];
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($r['nama_obat']) ?></div>
                            <small class="text-muted"><?= e($r['dosis'] ?: '-') ?> · <?= e($r['nama_apotek']) ?></small>
                        </td>
                        <td><?= e($r['kategori'] ?: '-') ?></td>
                        <td class="text-center">
                            <?php if ($stok === 0): ?>
                                <span class="badge badge-soft-danger">Habis</span>
                            <?php elseif ($isLow): ?>
                                <span class="badge badge-soft-warning"><?= $stok ?></span>
                            <?php else: ?>
                                <?= $stok ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= format_uang($r['harga']) ?></td>
                        <td class="text-center">
                            <?php if ($isExpired): ?>
                                <span class="badge badge-soft-danger"><?= format_tanggal($exp) ?></span>
                            <?php else: ?>
                                <?= format_tanggal($exp) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'aktif'): ?>
                                <span class="badge badge-soft-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-soft-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('m=obat&a=edit&id=' . (int) $r['id_obat']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="<?= url('m=obat&a=stokMasuk&id=' . (int) $r['id_obat']) ?>" class="btn btn-sm btn-outline-success" title="Stok masuk"><i class="bi bi-box-arrow-in-down"></i></a>
                            <a href="<?= url('m=obat&a=riwayat&id=' . (int) $r['id_obat']) ?>" class="btn btn-sm btn-outline-info" title="Riwayat stok"><i class="bi bi-clock-history"></i></a>
                            <?php if ($r['status'] === 'aktif'): ?>
                            <form method="post" action="<?= url('m=obat&a=nonaktifkan') ?>" class="d-inline" onsubmit="return confirm('Nonaktifkan obat ini?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_obat'] ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Nonaktifkan"><i class="bi bi-x-circle"></i></button>
                            </form>
                            <?php else: ?>
                            <form method="post" action="<?= url('m=obat&a=aktifkan') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_obat'] ?>">
                                <button class="btn btn-sm btn-outline-success" title="Aktifkan"><i class="bi bi-check-circle"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php include BASE_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
