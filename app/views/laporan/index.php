<?php
$q = 'jenis=' . urlencode($jenis) . '&dari=' . urlencode($dari) . '&sampai=' . urlencode($sampai);
if ($id_apotek) {
    $q .= '&id_apotek=' . $id_apotek;
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <img src="assets/images/logo-si-apotek.png?v=<?= ASSET_VERSION ?>" alt="SI-APOTEK" width="46" height="46">
        <div>
            <h1 class="h4 mb-1">Laporan</h1>
            <p class="text-muted mb-0">Cetak &amp; ekspor laporan resmi apotek.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('m=laporan&a=cetak&' . $q . '&autoprint=1') ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
        <a href="<?= url('m=laporan&a=cetak&' . $q) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
        </a>
        <a href="<?= url('m=laporan&a=export&format=excel&' . $q) ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <a href="<?= url('m=laporan&a=export&format=csv&' . $q) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <input type="hidden" name="m" value="laporan">
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small mb-1">Jenis Laporan</label>
                <select name="jenis" class="form-select">
                    <?php foreach ($jenisLabel as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $jenis === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label small mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label small mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small mb-1">Apotek</label>
                <select name="id_apotek" class="form-select">
                    <option value="0">— Semua Apotek —</option>
                    <?php foreach ($apoteks as $a): ?>
                        <option value="<?= (int) $a['id_apotek'] ?>" <?= $id_apotek === (int) $a['id_apotek'] ? 'selected' : '' ?>>
                            <?= e($a['nama_apotek']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
            </div>
        </form>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mt-3 mb-0 py-2">
                <i class="bi bi-exclamation-triangle me-1"></i><?= e($error) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($report): ?>
    <!-- Ringkasan -->
    <div class="row g-3 mb-4">
        <?php foreach ($report['ringkasan'] as $s): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-clipboard-data"></i></div>
                    <div>
                        <div class="stat-label"><?= e($s['label']) ?></div>
                        <div class="stat-value" style="font-size:1.15rem;"><?= e($s['value']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pratinjau laporan -->
    <div class="card">
        <div class="card-body">
            <div class="report-doc">
                <?php include BASE_PATH . '/views/partials/report_header.php'; ?>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="ctr" style="width:36px;">No</th>
                            <?php foreach ($report['columns'] as $c): ?>
                                <th><?= e($c['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report['rows'])): ?>
                            <tr><td colspan="<?= count($report['columns']) + 1 ?>" class="ctr" style="padding:20px;color:#888;">Tidak ada data pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($report['rows'] as $i => $r): ?>
                                <tr>
                                    <td class="ctr"><?= $i + 1 ?></td>
                                    <?php foreach ($report['columns'] as $c): [$html, $align] = report_cell($c['key'], $r[$c['key']] ?? null); ?>
                                        <td class="<?= $align ?>"><?= $html ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
