<?php
/**
 * Front controller — entry point aplikasi SI-APOTEK.
 * Routing sederhana: index.php?m=<module>&a=<action>
 */
declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/helpers.php';
require __DIR__ . '/core/Auth.php';
require __DIR__ . '/core/View.php';
require __DIR__ . '/controllers/BaseController.php';

$access = require __DIR__ . '/config/access.php';

$module = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($_GET['m'] ?? 'dashboard'))) ?: 'dashboard';
$action = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($_GET['a'] ?? 'index'))) ?: 'index';

// ---- Autentikasi & RBAC (fail-closed) ----
if ($module !== 'auth') {
    if (!Auth::check()) {
        redirect('index.php?m=auth&a=login');
    }
    $allowed = $access[$module] ?? [];
    if (!in_array(Auth::role(), $allowed, true)) {
        http_response_code(403);
        View::render('errors/403', [], Auth::check() ? 'app' : null);
        exit;
    }
}

// ---- Dispatch controller ----
$controllerClass = ucfirst($module) . 'Controller';
$file = __DIR__ . '/controllers/' . $controllerClass . '.php';

if (!is_file($file)) {
    http_response_code(404);
    View::render('errors/404', [], ($module !== 'auth' && Auth::check()) ? 'app' : null);
    exit;
}

require $file;

if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
    http_response_code(404);
    View::render('errors/404', [], ($module !== 'auth' && Auth::check()) ? 'app' : null);
    exit;
}

$controller = new $controllerClass();
$controller->$action();
