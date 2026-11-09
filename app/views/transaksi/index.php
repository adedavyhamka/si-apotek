<?php
$punyaKasir = !empty(current_user()['id_kasir']);
?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Kasir: <?= e(current_user_name()) ?></p>
    </div>
    <a href="<?= url('m=transaksi&a=riwayat') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-clock-history me-1"></i> Riwayat
    </a>
</div>

<div class="row g-4">
    <!-- Kiri: pencarian & daftar obat -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <form method="get" class="d-flex gap-2">
                    <input type="hidden" name="m" value="transaksi">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Cari obat..." value="<?= e($q) ?>">
                    </div>
                    <button class="btn btn-primary">Cari</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:520px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="sticky-top bg-white">
                            <tr><th>Obat</th><th class="text-center">Stok</th><th class="text-end">Harga</th><th style="width:90px"></th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($obats)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-5">Obat tidak ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($obats as $o): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($o['nama_obat']) ?></div>
                                    <small class="text-muted"><?= e($o['kategori'] ?: '-') ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ((int) $o['stok'] === 0): ?>
                                        <span class="badge badge-soft-danger">Habis</span>
                                    <?php else: ?>
                                        <?= (int) $o['stok'] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= format_uang($o['harga']) ?></td>
                                <td>
                                    <?php if ((int) $o['stok'] > 0): ?>
                                    <form method="post" action="<?= url('m=transaksi&a=tambahItem') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_obat" value="<?= (int) $o['id_obat'] ?>">
                                        <button class="btn btn-sm btn-primary w-100"><i class="bi bi-plus-lg"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanan: keranjang & checkout -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-cart3 me-2"></i> Keranjang
                <?php if (!empty($cart)): ?>
                    <a href="<?= url('m=transaksi&a=kosongkan') ?>" class="btn btn-sm btn-link text-danger ms-auto">Kosongkan</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($cart)): ?>
                    <p class="text-muted text-center py-4 mb-0">Keranjang kosong. Tambahkan obat dari daftar.</p>
                <?php else: ?>
                    <div class="mb-3" style="max-height:280px;overflow-y:auto;">
                        <?php foreach ($cart as $it): ?>
                        <div class="d-flex align-items-start gap-2 border-bottom py-2">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= e($it['nama_obat']) ?></div>
                                <div class="text-muted small"><?= format_uang($it['harga']) ?> × <?= (int) $it['jumlah'] ?></div>
                                <div class="small text-primary fw-semibold"><?= format_uang($it['harga'] * $it['jumlah']) ?></div>
                            </div>
                            <form method="post" action="<?= url('m=transaksi&a=ubahItem') ?>" class="d-flex gap-1 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_obat" value="<?= (int) $it['id_obat'] ?>">
                                <input type="number" name="jumlah" value="<?= (int) $it['jumlah'] ?>" min="0" class="form-control form-control-sm" style="width:60px">
                                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-repeat"></i></button>
                            </form>
                            <form method="post" action="<?= url('m=transaksi&a=hapusItem') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_obat" value="<?= (int) $it['id_obat'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center fs-5 fw-bold mb-3">
                        <span>Total</span>
                        <span class="text-primary"><?= format_uang($total) ?></span>
                    </div>

                    <form method="post" action="<?= url('m=transaksi&a=checkout') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select form-select-sm">
                                <option>Tunai</option>
                                <option>QRIS</option>
                                <option>Debit</option>
                                <option>Transfer Bank</option>
                                <option>E-Wallet</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Pelanggan (opsional)</label>
                            <select name="id_pelanggan" class="form-select form-select-sm">
                                <option value="0">— Umum (tanpa pelanggan) —</option>
                                <?php foreach ($pelanggans as $p): ?>
                                    <option value="<?= (int) $p['id_pelanggan'] ?>"><?= e($p['nama_pelanggan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Resep (opsional)</label>
                            <select name="id_resep" class="form-select form-select-sm">
                                <option value="0">— Tanpa resep —</option>
                                <?php foreach ($reseps as $r): ?>
                                    <option value="<?= (int) $r['id_resep'] ?>"><?= e($r['no_resep']) ?> (<?= e($r['nama_pasien']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!$punyaKasir): ?>
                        <div class="mb-3">
                            <label class="form-label small mb-1">Kasir</label>
                            <select name="id_kasir" class="form-select form-select-sm">
                                <?php foreach ($kasirs as $k): ?>
                                    <option value="<?= (int) $k['id_kasir'] ?>"><?= e($k['nama_kasir']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold"
                                onclick="return confirm('Proses pembayaran sebesar <?= format_uang($total) ?>?');">
                            <i class="bi bi-check2-circle me-1"></i> Proses Pembayaran
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
