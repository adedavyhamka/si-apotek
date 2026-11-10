<?php
/**
 * Kop laporan (header). Expects: $report, $dari, $sampai.
 */
?>
<div class="report-kop">
    <img src="assets/images/logo-si-apotek.png" alt="SI-APOTEK" class="report-logo">
    <div class="apotek-info">
        <?php if (!empty($report['apotek'])): ?>
            <p class="apotek-nama"><?= e($report['apotek']['nama_apotek']) ?></p>
            <p class="apotek-detail">
                <?php if (!empty($report['apotek']['alamat'])): ?><?= e($report['apotek']['alamat']) ?><?php endif; ?>
                <?php if (!empty($report['apotek']['alamat']) && !empty($report['apotek']['no_telp'])): ?> &middot; <?php endif; ?>
                <?php if (!empty($report['apotek']['no_telp'])): ?><?= e($report['apotek']['no_telp']) ?><?php endif; ?>
            </p>
        <?php else: ?>
            <p class="apotek-nama">SI-APOTEK</p>
            <p class="apotek-detail">Sistem Informasi Manajemen Apotek</p>
        <?php endif; ?>
    </div>
    <div class="report-meta">
        <div class="judul-kecil"><?= e($report['judul']) ?></div>
        <div>Periode: <?= format_tanggal($dari) ?> s/d <?= format_tanggal($sampai) ?></div>
    </div>
</div>
