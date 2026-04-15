<?php
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__, 2) . '/app');
    define('BASE_PATH', dirname(__DIR__, 2));
    define('PUB_PATH',  dirname(__DIR__, 2) . '/public');
}
$pageTitle = 'Error del servidor';
require APP_PATH . '/Views/layout/header.php'; ?>
<div class="min-h-[60vh] flex items-center justify-center text-center px-4">
    <div>
        <div class="text-8xl mb-6">⚠️</div>
        <h1 class="text-4xl font-display font-bold mb-3">500 — Error del servidor</h1>
        <p class="text-gray-500 mb-8">Ha ocurrido un error inesperado. Por favor inténtalo de nuevo.</p>
        <a href="/" class="btn-red px-8 py-3">Volver al inicio</a>
    </div>
</div>
<?php require APP_PATH . '/Views/layout/footer.php'; ?>
