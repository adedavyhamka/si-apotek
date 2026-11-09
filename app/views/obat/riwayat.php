<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=obat') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="h4 mb-0"><?= e($title) ?> — <?= e($obat['nama_obat']) ?></h1>
        <p class="text-muted mb-0">Stok saat ini: <?= (int) $obat['stok'] ?> unit</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Sebelum</th>
                        <th class="text-center">Sesudah</th>
                        <th>Keterangan</th>
                        <th>Pengguna</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">Belum ada riwayat stok.</td></tr>
                <?php else: ?>
                    <?php foreach ($riwayat as $h): ?>
                    <tr>
                        <td><?= format_datetime($h['tanggal']) ?></td>
                        <td>
                            <?php if ($h['jenis'] === 'masuk'): ?>
                                <span class="badge badge-soft-success">Masuk</span>
                            <?php else: ?>
                                <span class="badge badge-soft-danger">Keluar</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-semibold"><?= $h['jenis'] === 'masuk' ? '+' : '-' ?><?= (int) $h['jumlah'] ?></td>
                        <td class="text-center"><?= (int) $h['stok_sebelum'] ?></td>
                        <td class="text-center"><?= (int) $h['stok_sesudah'] ?></td>
                        <td><?= e($h['keterangan'] ?: '-') ?></td>
                        <td><?= e($h['username'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
