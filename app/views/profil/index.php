<div class="d-flex align-items-center mb-4">
    <h1 class="h4 mb-0"><?= e($title) ?></h1>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Informasi Akun</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Username</td><td class="fw-semibold"><?= e($user['username']) ?></td></tr>
                    <tr><td class="text-muted">Nama</td><td><?= e($user['nama_lengkap'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Role</td><td><?= e(ucwords(str_replace('_', ' ', $user['role']))) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Ganti Password</div>
            <div class="card-body p-4">
                <form method="post" action="<?= url('m=profil&a=gantiPassword') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_konfirmasi" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-key me-1"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
