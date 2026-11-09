<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=obat') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?> — <?= e($obat['nama_obat']) ?></h1>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body p-4">
                <p class="text-muted mb-4">Stok saat ini: <strong class="text-primary"><?= (int) $obat['stok'] ?></strong> unit</p>
                <form method="post" action="<?= url('m=obat&a=prosesStokMasuk') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $obat['id_obat'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Masuk <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="cth: Pembelian dari distributor">
                    </div>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Tambah Stok
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
