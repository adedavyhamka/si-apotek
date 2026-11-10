<?php
/** Halaman cetak laporan (tanpa sidebar). Expects: $report, $dari, $sampai, $pengguna. */
$colspan = count($report['columns']) + 1;
?>

<?php include BASE_PATH . '/views/partials/report_header.php'; ?>

<h1 class="report-title"><?= e($report['judul']) ?></h1>
<p class="report-subtitle">
    <?php if (!empty($report['apotek'])): ?>Apotek <?= e($report['apotek']['nama_apotek']) ?> &mdash; <?php endif; ?>
    Periode <?= format_tanggal($dari) ?> s/d <?= format_tanggal($sampai) ?>
</p>

<div class="report-meta-row">
    <span><b>Tanggal Cetak:</b> <?= date('d/m/Y H:i') ?></span>
    <span><b>Dicetak oleh:</b> <?= e($pengguna) ?></span>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="ctr" style="width:34px;">No</th>
            <?php foreach ($report['columns'] as $c): ?>
                <th><?= e($c['label']) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($report['rows'])): ?>
            <tr><td colspan="<?= $colspan ?>" class="ctr" style="padding:20px;color:#888;">Tidak ada data pada periode ini.</td></tr>
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

<div class="report-summary">
    <h4>Ringkasan</h4>
    <div class="sum-grid">
        <?php foreach ($report['ringkasan'] as $s): ?>
            <div class="sum-item">
                <span><?= e($s['label']) ?></span>
                <span class="sum-val"><?= e($s['value']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="report-sign">
    <div class="sign-box">
        Mengetahui,<br>
        Pemilik Apotek / Penanggung Jawab
        <div class="sign-line">( ________________________ )</div>
    </div>
    <div class="sign-box">
        &nbsp;<br>
        Apoteker Penanggung Jawab
        <div class="sign-line">( ________________________ )</div>
    </div>
</div>

<div class="report-footer">
    <span>Dokumen dihasilkan secara otomatis oleh Sistem Informasi Manajemen Apotek (SI-APOTEK).</span>
    <span>Dicetak: <?= date('d/m/Y H:i') ?></span>
</div>
