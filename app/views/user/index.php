<?php $roleLabel = ['super_admin' => 'Super Admin', 'pemilik' => 'Pemilik', 'apoteker' => 'Apoteker', 'kasir' => 'Kasir']; ?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Kelola akun pengguna dan hak akses.</p>
    </div>
    <a href="<?= url('m=user&a=tambah') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pengguna
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="m" value="user">
            <div class="col-sm-8 col-lg-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari username / nama..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <button class="btn btn-outline-secondary w-100">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Apotek</th>
                        <th>Status</th>
                        <th class="text-end" style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada pengguna.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['username']) ?></td>
                        <td><?= e($r['nama_lengkap']) ?></td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= e($roleLabel[$r['role']] ?? $r['role']) ?></span></td>
                        <td><?= e($r['nama_apotek'] ?: '-') ?></td>
                        <td>
                            <?php if ($r['status'] === 'aktif'): ?>
                                <span class="badge badge-soft-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-soft-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('m=user&a=edit&id=' . (int) $r['id_user']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if ($r['status'] === 'aktif'): ?>
                            <form method="post" action="<?= url('m=user&a=nonaktifkan') ?>" class="d-inline" onsubmit="return confirm('Nonaktifkan pengguna ini?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_user'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                            </form>
                            <?php else: ?>
                            <form method="post" action="<?= url('m=user&a=aktifkan') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_user'] ?>">
                                <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle"></i></button>
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
