<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Riwayat seluruh transaksi penjualan.</p>
    </div>
    <a href="<?= url('m=transaksi') ?>" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i> Transaksi Baru</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="m" value="transaksi">
            <input type="hidden" name="a" value="riwayat">
            <div class="col-sm-6 col-lg-3">
                <input type="text" name="q" class="form-control" placeholder="Cari no. transaksi / pelanggan..." value="<?= e($q) ?>">
            </div>
            <div class="col-sm-6 col-lg-3">
                <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>">
            </div>
            <div class="col-sm-6 col-lg-3">
                <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>">
            </div>
            <div class="col-sm-6 col-lg-3">
                <button class="btn btn-outline-secondary w-100">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th class="text-center">Item</th>
                        <th>Metode</th>
                        <th class="text-end">Total</th>
                        <th class="text-end" style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">Belum ada transaksi.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['no_transaksi']) ?></td>
                        <td><?= format_datetime($r['tanggal']) ?></td>
                        <td><?= e($r['nama_pelanggan'] ?: 'Umum') ?></td>
                        <td><?= e($r['nama_kasir']) ?></td>
                        <td class="text-center"><?= (int) $r['jumlah_item'] ?></td>
                        <td><span class="badge bg-light text-dark"><?= e($r['metode_pembayaran']) ?></span></td>
                        <td class="text-end fw-semibold"><?= format_uang($r['total']) ?></td>
                        <td class="text-end">
                            <a href="<?= url('m=transaksi&a=detail&id=' . (int) $r['id_transaksi']) ?>" class="btn btn-sm btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="<?= url('m=transaksi&a=struk&id=' . (int) $r['id_transaksi']) ?>" class="btn btn-sm btn-outline-primary" title="Struk"><i class="bi bi-receipt"></i></a>
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
