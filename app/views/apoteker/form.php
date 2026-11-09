<?php
$isEdit = $data !== null;
$val = function (string $key, string $default = '') use ($data) {
    if (isset($_SESSION['old'][$key])) {
        return old($key);
    }
    return e((string) ($data[$key] ?? $default));
};
$selectedApotek = isset($_SESSION['old']['id_apotek'])
    ? (int) $_SESSION['old']['id_apotek']
    : (int) ($data['id_apotek'] ?? 0);
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=apoteker') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=apoteker&a=update' : 'm=apoteker&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_apoteker'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nama Apoteker <span class="text-danger">*</span></label>
                <input type="text" name="nama_apoteker" class="form-control" value="<?= $val('nama_apoteker') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">No. SIPA</label>
                <input type="text" name="no_sipa" class="form-control" value="<?= $val('no_sipa') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" class="form-control" value="<?= $val('no_telp') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Apotek <span class="text-danger">*</span></label>
                <select name="id_apotek" class="form-select" required>
                    <option value="">-- Pilih Apotek --</option>
                    <?php foreach ($apotekList as $a): ?>
                        <option value="<?= (int) $a['id_apotek'] ?>" <?= $selectedApotek === (int) $a['id_apotek'] ? 'selected' : '' ?>>
                            <?= e($a['nama_apotek']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="<?= url('m=apoteker') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
