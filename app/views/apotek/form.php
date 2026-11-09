<?php
$isEdit = $data !== null;
$val = function (string $key, string $default = '') use ($data) {
    if (isset($_SESSION['old'][$key])) {
        return old($key);
    }
    return e((string) ($data[$key] ?? $default));
};
$pemilikList = $pemilik ?? [];
// Nilai pemilik terpilih: prioritaskan input lama saat validasi gagal, lalu data saat edit.
$selectedPemilik = isset($_SESSION['old']['id_pemilik'])
    ? (int) $_SESSION['old']['id_pemilik']
    : (int) ($data['id_pemilik'] ?? 0);
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=apotek') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=apotek&a=update' : 'm=apotek&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_apotek'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nama Apotek <span class="text-danger">*</span></label>
                <input type="text" name="nama_apotek" class="form-control" value="<?= $val('nama_apotek') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Pemilik <span class="text-danger">*</span></label>
                <select name="id_pemilik" class="form-select" required>
                    <option value="">-- Pilih Pemilik --</option>
                    <?php foreach ($pemilikList as $p): ?>
                        <option value="<?= (int) $p['id_pemilik'] ?>" <?= $selectedPemilik === (int) $p['id_pemilik'] ? 'selected' : '' ?>>
                            <?= e($p['id_pemilik']) ?> - <?= e($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?= $val('alamat') ?></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" class="form-control" value="<?= $val('no_telp') ?>">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="<?= url('m=apotek') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
