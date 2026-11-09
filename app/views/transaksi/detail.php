<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=transaksi&a=riwayat') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Informasi Transaksi</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">No. Transaksi</td><td class="fw-semibold"><?= e($trx['no_transaksi']) ?></td></tr>
                    <tr><td class="text-muted">Tanggal</td><td><?= format_datetime($trx['tanggal']) ?></td></tr>
                    <tr><td class="text-muted">Kasir</td><td><?= e($trx['nama_kasir']) ?></td></tr>
                    <tr><td class="text-muted">Pelanggan</td><td><?= e($trx['nama_pelanggan'] ?: 'Umum') ?></td></tr>
                    <tr><td class="text-muted">Resep</td><td><?= $trx['no_resep'] ? e($trx['no_resep']) . ' (' . e($trx['nama_pasien']) . ')' : '-' ?></td></tr>
                    <tr><td class="text-muted">Metode</td><td><?= e($trx['metode_pembayaran']) ?></td></tr>
                    <tr><td class="text-muted">Total</td><td class="fw-bold text-primary"><?= format_uang($trx['total']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Item Transaksi</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Obat</th><th class="text-center">Jumlah</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach ($trx['items'] as $i): ?>
                        <tr>
                            <td><?= e($i['nama_obat']) ?></td>
                            <td class="text-center"><?= (int) $i['jumlah'] ?></td>
                            <td class="text-end"><?= format_uang($i['harga_satuan']) ?></td>
                            <td class="text-end fw-semibold"><?= format_uang($i['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
