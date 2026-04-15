<?php $pageTitle = 'Sin acceso'; require APP_PATH . '/Views/layout/header.php'; ?>
<div class="min-h-[60vh] flex items-center justify-center text-center px-4">
    <div>
        <div class="text-8xl mb-6">🔒</div>
        <h1 class="text-4xl font-display font-bold mb-3">403 — Sin acceso</h1>
        <p class="text-gray-500 mb-8">No tienes permiso para acceder a esta sección.</p>
        <a href="/" class="btn-red px-8 py-3">Volver al inicio</a>
    </div>
</div>
<?php require APP_PATH . '/Views/layout/footer.php'; ?>
