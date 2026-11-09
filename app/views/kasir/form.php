<?php
$isEdit = $data !== null;
$val = function (string $key, string $default = '') use ($data) {
    if (isset($_SESSION['old'][$key])) {
        return old($key);
    }
    return e((string) ($data[$key] ?? $default));
};
// id_apotek terpilih (prioritas: old > data > 0)
$selectedApotek = 0;
if (isset($_SESSION['old']['id_apotek'])) {
    $selectedApotek = (int) $_SESSION['old']['id_apotek'];
} elseif ($data !== null) {
    $selectedApotek = (int) $data['id_apotek'];
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('m=kasir') ?>" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body p-4">
        <form method="post" action="<?= url($isEdit ? 'm=kasir&a=update' : 'm=kasir&a=simpan') ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $data['id_kasir'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nama Kasir <span class="text-danger">*</span></label>
                <input type="text" name="nama_kasir" class="form-control" value="<?= $val('nama_kasir') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Apotek <span class="text-danger">*</span></label>
                <select name="id_apotek" class="form-select" required>
                    <option value="">-- Pilih Apotek --</option>
                    <?php foreach ($apotek as $a): ?>
                        <option value="<?= (int) $a['id_apotek'] ?>" <?= (int) $a['id_apotek'] === $selectedApotek ? 'selected' : '' ?>>
                            <?= e($a['nama_apotek']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Shift</label>
                <input type="text" name="shift" class="form-control" placeholder="Contoh: Pagi / Sore" value="<?= $val('shift') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" class="form-control" value="<?= $val('no_telp') ?>">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="<?= url('m=kasir') ?>" class="btn btn-light px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
