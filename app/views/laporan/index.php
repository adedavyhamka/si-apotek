<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Laporan penjualan, stok, dan kinerja.</p>
    </div>
    <button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak / PDF</button>
</div>

<!-- Filter periode -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <input type="hidden" name="m" value="laporan">
            <div class="col-sm-5 col-lg-4">
                <label class="form-label small mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>">
            </div>
            <div class="col-sm-5 col-lg-4">
                <label class="form-label small mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>">
            </div>
            <div class="col-sm-2 col-lg-2">
                <button class="btn btn-primary w-100">Tampilkan</button>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <a class="btn btn-sm btn-outline-success" href="<?= url('m=laporan&a=export&jenis=penjualan&dari=' . e($dari) . '&sampai=' . e($sampai)) ?>">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Penjualan (Excel/CSV)
                </a>
                <a class="btn btn-sm btn-outline-success" href="<?= url('m=laporan&a=export&jenis=terlaris&dari=' . e($dari) . '&sampai=' . e($sampai)) ?>">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Obat Terlaris
                </a>
                <a class="btn btn-sm btn-outline-success" href="<?= url('m=laporan&a=export&jenis=stok') ?>">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Stok
                </a>
                <a class="btn btn-sm btn-outline-success" href="<?= url('m=laporan&a=export&jenis=kasir&dari=' . e($dari) . '&sampai=' . e($sampai)) ?>">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export per Kasir
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label">Total Penjualan (periode)</div>
                <div class="stat-value"><?= format_uang($ringkasan['total_penjualan']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-label">Total Transaksi (periode)</div>
                <div class="stat-value"><?= (int) $ringkasan['total_transaksi'] ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Penjualan harian -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-calendar3 me-1"></i> Penjualan Harian</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Tanggal</th><th class="text-center">Jumlah Transaksi</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                <?php if (empty($harian)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada penjualan pada periode ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($harian as $h): ?>
                        <tr>
                            <td><?= format_tanggal($h['tgl']) ?></td>
                            <td class="text-center"><?= (int) $h['jumlah_transaksi'] ?></td>
                            <td class="text-end fw-semibold"><?= format_uang($h['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-trophy me-1"></i> Obat Terlaris</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Obat</th><th class="text-center">Qty</th><th class="text-end">Nilai</th></tr></thead>
                    <tbody>
                    <?php if (empty($terlaris)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($terlaris as $t): ?>
                            <tr>
                                <td><?= e($t['nama_obat']) ?></td>
                                <td class="text-center"><?= (int) $t['total_qty'] ?></td>
                                <td class="text-end"><?= format_uang($t['total_nilai']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-badge me-1"></i> Transaksi per Kasir</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Kasir</th><th class="text-center">Transaksi</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    <?php if (empty($perKasir)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($perKasir as $k): ?>
                            <tr>
                                <td><?= e($k['nama_kasir']) ?></td>
                                <td class="text-center"><?= (int) $k['jumlah_transaksi'] ?></td>
                                <td class="text-end"><?= format_uang($k['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-box-seam me-1"></i> Laporan Stok Obat</div>
            <div class="card-body p-0" style="max-height:400px;overflow-y:auto;">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Obat</th><th class="text-center">Stok</th><th class="text-center">Min</th><th>Apotek</th></tr></thead>
                    <tbody>
                    <?php foreach ($stok as $s): ?>
                        <tr>
                            <td><?= e($s['nama_obat']) ?></td>
                            <td class="text-center <?= (int)$s['stok'] <= (int)$s['stok_minimum'] ? 'text-danger fw-bold' : '' ?>"><?= (int) $s['stok'] ?></td>
                            <td class="text-center"><?= (int) $s['stok_minimum'] ?></td>
                            <td><?= e($s['nama_apotek']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-calendar-x text-danger me-1"></i> Obat Kedaluwarsa</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Obat</th><th class="text-center">Stok</th><th class="text-end">Kedaluwarsa</th></tr></thead>
                    <tbody>
                    <?php if (empty($kedaluwarsa)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada obat kedaluwarsa.</td></tr>
                    <?php else: ?>
                        <?php foreach ($kedaluwarsa as $e2): ?>
                            <tr>
                                <td><?= e($e2['nama_obat']) ?></td>
                                <td class="text-center"><?= (int) $e2['stok'] ?></td>
                                <td class="text-end text-danger"><?= format_tanggal($e2['expired_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
