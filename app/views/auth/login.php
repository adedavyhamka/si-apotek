<div class="auth-card">
    <div class="auth-header">
        <img src="assets/images/logo-si-apotek.png" alt="<?= APP_NAME ?>" width="76" height="76" style="filter: drop-shadow(0 2px 6px rgba(0,0,0,.25));">
        <h1 class="h4 mt-3 mb-1"><?= APP_NAME ?></h1>
        <p class="mb-0 small opacity-75">Sistem Informasi Manajemen Apotek</p>
    </div>
    <div class="auth-body-inner">
        <?php if (flash_has('error')): ?>
            <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i><?= e(flash_get('error')) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= url('m=auth&a=authenticate') ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username" class="form-control"
                           value="<?= old('username') ?>" required autofocus autocomplete="username">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control"
                           required autocomplete="current-password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
        <p class="text-center text-muted small mt-4 mb-0">
            Akses terbatas untuk pegawai apotek.
        </p>
    </div>
</div>
