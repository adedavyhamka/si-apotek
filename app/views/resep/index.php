<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Kelola resep dokter masuk.</p>
    </div>
    <a href="<?= url('m=resep&a=tambah') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Resep
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="m" value="resep">
            <div class="col-sm-8 col-lg-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari no. resep / pasien / dokter..." value="<?= e($q) ?>">
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
                        <th>No. Resep</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Apoteker</th>
                        <th class="text-end" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada resep.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['no_resep']) ?></td>
                        <td><?= e($r['nama_pasien'] ?: '-') ?></td>
                        <td><?= e($r['nama_dokter'] ?: '-') ?></td>
                        <td><?= format_tanggal($r['tanggal_resep']) ?></td>
                        <td><?= e($r['nama_pelanggan'] ?: '-') ?></td>
                        <td><?= e($r['nama_apoteker'] ?: '-') ?></td>
                        <td class="text-end">
                            <a href="<?= url('m=resep&a=edit&id=' . (int) $r['id_resep']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= url('m=resep&a=hapus') ?>" class="d-inline" onsubmit="return confirm('Hapus resep ini?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_resep'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
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
