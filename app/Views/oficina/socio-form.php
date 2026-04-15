<?php
$pageTitle = 'Añadir socio — Oficina Virtual';
require APP_PATH . '/Views/layout/header.php';
$errors = $errors ?? [];
$m      = $member ?? [];   // null on create
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="/oficina/mi-club" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Mi Club</a>

    <h1 class="text-2xl font-display font-bold mb-6">
        <?= $m ? 'Editar socio' : 'Añadir socio' ?>
    </h1>

    <?php if ($errors): ?>
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 space-y-1">
            <?php foreach ($errors as $e): ?>
                <p class="text-sm"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/oficina/mi-club/socios<?= $m ? '/' . $m['id'] . '/actualizar' : '' ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="form-label" for="name">Nombre completo *</label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= htmlspecialchars($m['name'] ?? '') ?>"
                       class="form-input" placeholder="Juan García López">
            </div>

            <div>
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="255"
                       value="<?= htmlspecialchars($m['email'] ?? '') ?>"
                       class="form-input" placeholder="socio@ejemplo.com">
            </div>
            <div>
                <label class="form-label" for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" maxlength="30"
                       value="<?= htmlspecialchars($m['phone'] ?? '') ?>"
                       class="form-input" placeholder="+34 600 000 000">
            </div>
        </div>

        <div class="border-t border-gray-100 pt-5 grid sm:grid-cols-3 gap-4">
            <div>
                <label class="form-label" for="license_number">Nº de licencia</label>
                <input type="text" id="license_number" name="license_number" maxlength="80"
                       value="<?= htmlspecialchars($m['license_number'] ?? '') ?>"
                       class="form-input" placeholder="LIC-2024-00123">
            </div>
            <div>
                <label class="form-label" for="license_type">Tipo de licencia</label>
                <input type="text" id="license_type" name="license_type" maxlength="80"
                       value="<?= htmlspecialchars($m['license_type'] ?? '') ?>"
                       class="form-input" placeholder="Caza / Galgo / Federativo">
            </div>
            <div>
                <label class="form-label" for="license_expires_at">Fecha de vencimiento</label>
                <input type="date" id="license_expires_at" name="license_expires_at"
                       value="<?= htmlspecialchars($m['license_expires_at'] ?? '') ?>"
                       class="form-input">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="notes">Notas internas</label>
                <textarea id="notes" name="notes" rows="2" maxlength="1000"
                          class="form-input resize-none"
                          placeholder="Observaciones..."><?= htmlspecialchars($m['notes'] ?? '') ?></textarea>
            </div>
            <div class="flex items-center gap-3 pt-6">
                <input type="checkbox" id="is_delegate" name="is_delegate" value="1"
                       class="w-4 h-4 rounded accent-galgo-gold"
                       <?= !empty($m['is_delegate']) ? 'checked' : '' ?>>
                <label for="is_delegate" class="text-sm text-gray-700 cursor-pointer">
                    Es delegado del club
                </label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-gold">
                <?= $m ? 'Guardar cambios' : 'Añadir socio' ?>
            </button>
            <a href="/oficina/mi-club" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
