<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <a href="<?= url('m=transaksi') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
    <button class="btn btn-primary ms-auto" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak</button>
</div>

<div class="struk" id="areaCetak">
    <div class="text-center mb-3">
        <strong><?= e($trx['nama_apotek']) ?></strong><br>
        <span><?= e($trx['apotek_alamat']) ?></span><br>
        <span><?= e($trx['apotek_telp']) ?></span>
    </div>
    <hr>
    <div class="mb-2">
        <div><?= e($trx['no_transaksi']) ?></div>
        <div><?= format_datetime($trx['tanggal']) ?></div>
        <div>Kasir: <?= e($trx['nama_kasir']) ?></div>
        <?php if ($trx['nama_pelanggan']): ?><div>Pelanggan: <?= e($trx['nama_pelanggan']) ?></div><?php endif; ?>
    </div>
    <hr>
    <?php foreach ($trx['items'] as $i): ?>
        <div class="d-flex justify-content-between">
            <span><?= e($i['nama_obat']) ?><br><small><?= (int) $i['jumlah'] ?> × <?= format_uang($i['harga_satuan']) ?></small></span>
            <span><?= format_uang($i['subtotal']) ?></span>
        </div>
    <?php endforeach; ?>
    <hr>
    <div class="d-flex justify-content-between fw-bold">
        <span>TOTAL</span>
        <span><?= format_uang($trx['total']) ?></span>
    </div>
    <div class="d-flex justify-content-between">
        <span>Metode</span><span><?= e($trx['metode_pembayaran']) ?></span>
    </div>
    <hr>
    <div class="text-center small">Terima kasih atas kunjungan Anda.<br>Semoga lekas sehat.</div>
</div>
