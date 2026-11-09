<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Kelola data pelanggan.</p>
    </div>
    <a href="<?= url('m=pelanggan&a=tambah') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pelanggan
    </a>
</div>

<div class="card">
    <div class="card-body">
        <!-- Pencarian & filter -->
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="m" value="pelanggan">
            <div class="col-sm-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari nama / telepon..."
                           value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-sm-4 col-lg-3">
                <select name="filter" class="form-select">
                    <option value="aktif" <?= $filter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $filter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    <option value="semua" <?= $filter === 'semua' ? 'selected' : '' ?>>Semua</option>
                </select>
            </div>
            <div class="col-sm-2">
                <button class="btn btn-outline-secondary w-100">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>No. Telepon</th>
                        <th class="text-center">Tanggal Daftar</th>
                        <th class="text-center">Transaksi</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">Tidak ada data.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int) $r['id_pelanggan'] ?></td>
                        <td class="fw-semibold"><?= e($r['nama_pelanggan']) ?></td>
                        <td><?= e($r['alamat'] ?: '-') ?></td>
                        <td><?= e($r['no_telp'] ?: '-') ?></td>
                        <td class="text-center"><?= e(format_tanggal($r['tanggal_daftar'])) ?></td>
                        <td class="text-center"><?= (int) $r['jumlah_transaksi'] ?></td>
                        <td class="text-center">
                            <?php if ($r['status'] === 'aktif'): ?>
                                <span class="badge badge-soft-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-soft-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('m=pelanggan&a=edit&id=' . (int) $r['id_pelanggan']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($r['status'] === 'aktif'): ?>
                            <form method="post" action="<?= url('m=pelanggan&a=nonaktifkan') ?>" class="d-inline"
                                  onsubmit="return confirm('Nonaktifkan pelanggan ini?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_pelanggan'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                            </form>
                            <?php else: ?>
                            <form method="post" action="<?= url('m=pelanggan&a=aktifkan') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_pelanggan'] ?>">
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
