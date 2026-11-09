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
    <a href="<?= url('m=pemilik') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=pemilik&a=update' : 'm=pemilik&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_pemilik'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" value="<?= $val('nama') ?>" required>
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
                <a href="<?= url('m=pemilik') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
