<?php
$isEdit = $data !== null;
$val = function (string $key, string $default = '') use ($data) {
    if (isset($_SESSION['old'][$key])) {
        return old($key);
    }
    return e((string) ($data[$key] ?? $default));
};
$isApotekerLocked = current_role() === 'apoteker';
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=obat') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=obat&a=update' : 'm=obat&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_obat'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nama Obat <span class="text-danger">*</span></label>
                    <input type="text" name="nama_obat" class="form-control" value="<?= $val('nama_obat') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control" value="<?= $val('kategori') ?>" list="kategoriList">
                    <datalist id="kategoriList">
                        <option value="Analgesik"><option value="Antibiotik"><option value="Vitamin">
                        <option value="Antihistamin"><option value="Lambung"><option value="Batuk">
                        <option value="Antiseptik"><option value="Kortikosteroid">
                    </datalist>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dosis</label>
                    <input type="text" name="dosis" class="form-control" value="<?= $val('dosis') ?>" placeholder="cth: 500 mg">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga" class="form-control" value="<?= $val('harga') ?>" min="0" step="1" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="stok_minimum" class="form-control" value="<?= $val('stok_minimum', '10') ?>" min="0">
                </div>

                <?php if (!$isEdit): ?>
                <div class="col-md-4">
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="stok" class="form-control" value="<?= $val('stok', '0') ?>" min="0">
                </div>
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label">Tanggal Kedaluwarsa</label>
                    <input type="date" name="expired_date" class="form-control" value="<?= $val('expired_date') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Apotek <span class="text-danger">*</span></label>
                    <?php if ($isApotekerLocked): ?>
                        <select class="form-select" disabled>
                            <option><?= e($apoteks[0]['nama_apotek'] ?? 'Apotek Anda') ?></option>
                        </select>
                    <?php else: ?>
                        <select name="id_apotek" class="form-select" required>
                            <option value="">— Pilih Apotek —</option>
                            <?php foreach ($apoteks as $a): ?>
                                <option value="<?= (int) $a['id_apotek'] ?>"
                                    <?= ((int) $val('id_apotek')) === (int) $a['id_apotek'] ? 'selected' : '' ?>>
                                    <?= e($a['nama_apotek']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isEdit): ?>
                <div class="alert alert-light border small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Stok tidak diubah lewat form ini. Gunakan tombol <strong>Stok Masuk</strong> untuk menambah stok.
                    Stok otomatis berkurang saat transaksi penjualan.
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                <a href="<?= url('m=obat') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
