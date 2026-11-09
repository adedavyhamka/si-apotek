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
    <a href="<?= url('m=user') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=user&a=update' : 'm=user&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_user'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?= $val('username') ?>" <?= $isEdit ? 'readonly' : '' ?> required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= $val('nama_lengkap') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <?= $isEdit ? '(kosongkan jika tidak diganti)' : '<span class="text-danger">*</span>' ?></label>
                    <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?> placeholder="Minimal 6 karakter">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="">— Pilih Role —</option>
                        <option value="super_admin" <?= $val('role') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                        <option value="pemilik" <?= $val('role') === 'pemilik' ? 'selected' : '' ?>>Pemilik Apotek</option>
                        <option value="apoteker" <?= $val('role') === 'apoteker' ? 'selected' : '' ?>>Apoteker</option>
                        <option value="kasir" <?= $val('role') === 'kasir' ? 'selected' : '' ?>>Kasir</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apotek (scope)</label>
                    <select name="id_apotek" class="form-select">
                        <option value="0">— Semua —</option>
                        <?php foreach ($apoteks as $a): ?>
                            <option value="<?= (int) $a['id_apotek'] ?>" <?= ((int) $val('id_apotek')) === (int) $a['id_apotek'] ? 'selected' : '' ?>><?= e($a['nama_apotek']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kasir terkait (untuk role kasir)</label>
                    <select name="id_kasir" class="form-select">
                        <option value="0">— Tidak terhubung —</option>
                        <?php foreach ($kasirs as $k): ?>
                            <option value="<?= (int) $k['id_kasir'] ?>" <?= ((int) $val('id_kasir')) === (int) $k['id_kasir'] ? 'selected' : '' ?>><?= e($k['nama_kasir']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apoteker terkait (untuk role apoteker)</label>
                    <select name="id_apoteker" class="form-select">
                        <option value="0">— Tidak terhubung —</option>
                        <?php foreach ($apotekers as $ap): ?>
                            <option value="<?= (int) $ap['id_apoteker'] ?>" <?= ((int) $val('id_apoteker')) === (int) $ap['id_apoteker'] ? 'selected' : '' ?>><?= e($ap['nama_apoteker']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                <a href="<?= url('m=user') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
