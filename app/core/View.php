<?php
/**
 * View — render view ke dalam layout.
 */
declare(strict_types=1);

class View
{
    /**
     * Render view dengan layout.
     * @param string $view   path relatif views/ (tanpa .php)
     * @param array  $data   variabel untuk view
     * @param string|null $layout layout (null = tanpa layout)
     */
    public static function render(string $view, array $viewData = [], ?string $layout = 'app'): void
    {
        // PENTING: parameter diberi nama $viewData (bukan $data) agar key 'data'
        // dari controller dapat di-extract dengan benar. Jika memakai $data,
        // extract(EXTR_SKIP) melewatkan key 'data' (karena $data sudah jadi parameter),
        // sehingga di view $data = array (bukan null/record) dan $isEdit salah.
        extract($viewData, EXTR_SKIP);

        ob_start();
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            ob_end_clean();
            throw new RuntimeException("View tidak ditemukan: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';
        require $layoutFile;

        // Bersihkan input lama (old) setelah halaman dirender, agar nilai form
        // yang gagal validasi tidak "menempel" pada kunjungan form berikutnya.
        if (isset($_SESSION['old'])) {
            unset($_SESSION['old']);
        }
    }
}
