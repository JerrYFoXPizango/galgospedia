<?php $pageTitle = 'Nueva contraseña'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-display font-bold text-center mb-8">Nueva contraseña</h1>
        <form method="POST" action="/restablecer/<?= htmlspecialchars($token) ?>" class="card space-y-4">
            <?= \Helpers\Csrf::field() ?>
            <div>
                <label class="form-label" for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password" required
                       class="form-input" minlength="8" autofocus>
            </div>
            <div>
                <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       class="form-input" minlength="8">
            </div>
            <button type="submit" class="btn-red w-full py-3">Guardar contraseña</button>
        </form>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
