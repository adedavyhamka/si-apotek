<?php
// Siapkan data grafik
$harianLabels = [];
$harianData = [];
foreach ($harian as $h) {
    $harianLabels[] = date('d/m', strtotime($h['d']));
    $harianData[] = (float) $h['total'];
}
$bulananLabels = [];
$bulananData = [];
foreach ($bulanan as $b) {
    $ts = strtotime($b['m'] . '-01');
    $bulananLabels[] = date('M y', $ts);
    $bulananData[] = (float) $b['total'];
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <img src="assets/images/logo-si-apotek.png" alt="SI-APOTEK" width="46" height="46">
        <div>
            <h1 class="h4 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Ringkasan operasional apotek hari ini.</p>
        </div>
    </div>
</div>

<!-- Kartu statistik -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label">Penjualan Hari Ini</div>
                <div class="stat-value"><?= format_uang($penjualanHariIni) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-label">Transaksi Hari Ini</div>
                <div class="stat-value"><?= $transaksiHariIni ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Total Pelanggan</div>
                <div class="stat-value"><?= $totalPelanggan ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-capsule"></i></div>
            <div>
                <div class="stat-label">Total Obat</div>
                <div class="stat-value"><?= $totalObat ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Kartu peringatan -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle text-warning"></i> Obat Hampir Habis
                <span class="badge bg-warning text-dark ms-auto"><?= $obatHampirHabis ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($listHampirHabis)): ?>
                    <p class="text-muted text-center py-4 mb-0">Tidak ada obat yang hampir habis. 👍</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Obat</th><th class="text-center">Stok</th><th class="text-center">Min.</th><th class="text-end">Kedaluwarsa</th></tr></thead>
                            <tbody>
                            <?php foreach ($listHampirHabis as $o): ?>
                                <tr>
                                    <td><?= e($o['nama_obat']) ?></td>
                                    <td class="text-center text-danger fw-bold"><?= (int) $o['stok'] ?></td>
                                    <td class="text-center"><?= (int) $o['stok_minimum'] ?></td>
                                    <td class="text-end"><?= format_tanggal($o['expired_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-calendar-x text-danger"></i> Obat Kedaluwarsa
                <span class="badge bg-danger ms-auto"><?= $obatKedaluwarsa ?></span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <i class="bi bi-capsule" style="font-size:2.5rem;color:#dc2626;"></i>
                    <p class="mb-0 mt-2 text-muted">
                        <?= $obatKedaluwarsa > 0
                            ? $obatKedaluwarsa . ' obat sudah melewati tanggal kedaluwarsa.'
                            : 'Tidak ada obat kedaluwarsa.' ?>
                    </p>
                    <a href="<?= url('m=obat') ?>" class="btn btn-sm btn-outline-danger mt-3">Lihat Data Obat</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik -->
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-graph-up me-1"></i> Penjualan 7 Hari Terakhir</div>
            <div class="card-body"><canvas id="chartHarian" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart me-1"></i> Penjualan 6 Bulan Terakhir</div>
            <div class="card-body"><canvas id="chartBulanan" height="220"></canvas></div>
        </div>
    </div>
</div>

<!-- Transaksi terbaru -->
<div class="card">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-clock-history me-1"></i> Transaksi Terbaru
        <a href="<?= url('m=transaksi&a=riwayat') ?>" class="btn btn-sm btn-outline-primary ms-auto">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($terbaru)): ?>
            <p class="text-muted text-center py-5 mb-0">Belum ada transaksi. Mulai transaksi pertama Anda.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($terbaru as $t): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($t['no_transaksi']) ?></td>
                            <td><?= format_datetime($t['tanggal']) ?></td>
                            <td><?= e($t['nama_pelanggan'] ?: 'Umum') ?></td>
                            <td><?= e($t['nama_kasir']) ?></td>
                            <td><span class="badge bg-light text-dark"><?= e($t['metode_pembayaran']) ?></span></td>
                            <td class="text-end fw-semibold"><?= format_uang($t['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var blue = '#2563eb', blueSoft = 'rgba(37,99,235,.2)';

    new Chart(document.getElementById('chartHarian'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($harianLabels) ?>,
            datasets: [{
                label: 'Penjualan (Rp)',
                data: <?= json_encode($harianData) ?>,
                backgroundColor: blueSoft,
                borderColor: blue,
                borderWidth: 2,
                borderRadius: 6,
                fill: true
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + v.toLocaleString('id-ID') } } }
        }
    });

    new Chart(document.getElementById('chartBulanan'), {
        type: 'line',
        data: {
            labels: <?= json_encode($bulananLabels) ?>,
            datasets: [{
                label: 'Penjualan (Rp)',
                data: <?= json_encode($bulananData) ?>,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,.15)',
                borderWidth: 2,
                tension: .35,
                fill: true,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + v.toLocaleString('id-ID') } } }
        }
    });
});
</script>
