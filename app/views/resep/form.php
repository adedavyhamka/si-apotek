<?php
$isEdit = $data !== null;
$val = function (string $key, string $default = '') use ($data) {
    if (isset($_SESSION['old'][$key])) {
        return old($key);
    }
    return e((string) ($data[$key] ?? $default));
};
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=resep') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:760px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=resep&a=update' : 'm=resep&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_resep'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">No. Resep <span class="text-danger">*</span></label>
                    <input type="text" name="no_resep" class="form-control" value="<?= $val('no_resep') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Resep</label>
                    <input type="date" name="tanggal_resep" class="form-control" value="<?= $val('tanggal_resep', date('Y-m-d')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Dokter</label>
                    <input type="text" name="nama_dokter" class="form-control" value="<?= $val('nama_dokter') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" name="nama_pasien" class="form-control" value="<?= $val('nama_pasien') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pelanggan (opsional)</label>
                    <select name="id_pelanggan" class="form-select">
                        <option value="0">— Tidak terkait pelanggan —</option>
                        <?php foreach ($pelanggans as $p): ?>
                            <option value="<?= (int) $p['id_pelanggan'] ?>" <?= ((int) $val('id_pelanggan')) === (int) $p['id_pelanggan'] ? 'selected' : '' ?>>
                                <?= e($p['nama_pelanggan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apoteker yang Melayani (opsional)</label>
                    <select name="id_apoteker" class="form-select">
                        <option value="0">— Belum ditentukan —</option>
                        <?php foreach ($apotekers as $a): ?>
                            <option value="<?= (int) $a['id_apoteker'] ?>" <?= ((int) $val('id_apoteker')) === (int) $a['id_apoteker'] ? 'selected' : '' ?>>
                                <?= e($a['nama_apoteker']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi Resep</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="cth: Amoxicillin 3x1 selama 5 hari"><?= $val('deskripsi') ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                <a href="<?= url('m=resep') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
