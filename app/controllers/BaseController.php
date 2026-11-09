<?php
/**
 * BaseController — menyediakan helper render view.
 */
declare(strict_types=1);

abstract class BaseController
{
    protected function view(string $view, array $data = [], ?string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    /** Redirect dengan flash sukses. */
    protected function flashRedirect(string $module, string $action, string $type, string $msg, array $extra = []): never
    {
        flash_set($type, $msg);
        redirect_route($module, $action, $extra);
    }

    /** Simpan input lama agar form terisi kembali saat validasi gagal. */
    protected function backWithInput(array $input, string $module, string $action, array $extra = []): never
    {
        $_SESSION['old'] = $input;
        redirect_route($module, $action, $extra);
    }
}
