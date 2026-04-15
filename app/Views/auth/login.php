<?php $pageTitle = 'Iniciar sesión'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-20 h-20 mx-auto mb-4">
            <h1 class="text-2xl font-display font-bold">Bienvenido de vuelta</h1>
            <p class="text-gray-500 text-sm mt-1">Inicia sesión en tu cuenta</p>
        </div>

        <form method="POST" action="/login" class="card space-y-4">
            <?= \Helpers\Csrf::field() ?>
            <div>
                <label class="form-label" for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required autofocus
                       class="form-input" placeholder="tu@correo.com">
            </div>
            <div>
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" id="password" name="password" required
                       class="form-input" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-red w-full py-3 text-base">Iniciar sesión</button>
        </form>

        <div class="text-center mt-6 space-y-2">
            <a href="/recuperar" class="text-sm text-gray-500 hover:text-galgo-red">¿Olvidaste tu contraseña?</a>
            <p class="text-sm text-gray-500">
                ¿No tienes cuenta?
                <a href="/registro" class="text-galgo-red font-medium hover:underline">Regístrate gratis</a>
            </p>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
