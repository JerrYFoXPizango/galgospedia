<?php $pageTitle = 'Recuperar contraseña'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-display font-bold text-center mb-8">Recuperar contraseña</h1>
        <form method="POST" action="/recuperar" class="card space-y-4">
            <?= \Helpers\Csrf::field() ?>
            <p class="text-sm text-gray-500">Introduce tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>
            <div>
                <label class="form-label" for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required class="form-input" autofocus>
            </div>
            <button type="submit" class="btn-red w-full py-3">Enviar enlace</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4">
            <a href="/login" class="text-galgo-red hover:underline">← Volver al inicio de sesión</a>
        </p>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
