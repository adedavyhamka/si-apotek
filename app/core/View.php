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
    public static function render(string $view, array $data = [], ?string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);

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
    }
}
