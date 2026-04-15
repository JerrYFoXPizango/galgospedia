<?php
$pageTitle = 'Solicitar alta de club — Oficina Virtual';
require APP_PATH . '/Views/layout/header.php';
$old    = $old ?? [];
$errors = $errors ?? [];
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="/oficina" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Volver a Oficina Virtual</a>

    <h1 class="text-2xl font-display font-bold mb-6">Solicitar alta de club / coto</h1>

    <?php if ($errors): ?>
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 space-y-1">
            <?php foreach ($errors as $e): ?>
                <p class="text-sm"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/oficina/solicitar-club" class="space-y-5 bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div>
            <label class="form-label" for="name">Nombre del club / coto *</label>
            <input type="text" id="name" name="name" required maxlength="150"
                   value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                   class="form-input" placeholder="Club Galgos de Castilla">
        </div>

        <div>
            <label class="form-label" for="type">Tipo de entidad *</label>
            <select id="type" name="type" class="form-input">
                <option value="club"       <?= ($old['type'] ?? 'club') === 'club'       ? 'selected' : '' ?>>Club</option>
                <option value="coto"       <?= ($old['type'] ?? '') === 'coto'       ? 'selected' : '' ?>>Coto</option>
                <option value="federacion" <?= ($old['type'] ?? '') === 'federacion' ? 'selected' : '' ?>>Federación</option>
                <option value="otro"       <?= ($old['type'] ?? '') === 'otro'       ? 'selected' : '' ?>>Otro</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="province">Provincia</label>
                <input type="text" id="province" name="province" maxlength="100"
                       value="<?= htmlspecialchars($old['province'] ?? '') ?>"
                       class="form-input" placeholder="Toledo">
            </div>
            <div>
                <label class="form-label" for="autonomous_community">Comunidad Autónoma</label>
                <input type="text" id="autonomous_community" name="autonomous_community" maxlength="100"
                       value="<?= htmlspecialchars($old['autonomous_community'] ?? '') ?>"
                       class="form-input" placeholder="Castilla-La Mancha">
            </div>
        </div>

        <div>
            <label class="form-label" for="country">País</label>
            <input type="text" id="country" name="country" maxlength="100"
                   value="<?= htmlspecialchars($old['country'] ?? 'España') ?>"
                   class="form-input">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="contact_email">Email de contacto</label>
                <input type="email" id="contact_email" name="contact_email" maxlength="255"
                       value="<?= htmlspecialchars($old['contact_email'] ?? '') ?>"
                       class="form-input" placeholder="info@miclub.es">
            </div>
            <div>
                <label class="form-label" for="contact_phone">Teléfono</label>
                <input type="text" id="contact_phone" name="contact_phone" maxlength="30"
                       value="<?= htmlspecialchars($old['contact_phone'] ?? '') ?>"
                       class="form-input" placeholder="+34 600 000 000">
            </div>
        </div>

        <div>
            <label class="form-label" for="website">Web (opcional)</label>
            <input type="url" id="website" name="website" maxlength="255"
                   value="<?= htmlspecialchars($old['website'] ?? '') ?>"
                   class="form-input" placeholder="https://miclub.es">
        </div>

        <div>
            <label class="form-label" for="description">Descripción (opcional)</label>
            <textarea id="description" name="description" rows="3" maxlength="1000"
                      class="form-input resize-none"
                      placeholder="Breve descripción del club o coto..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>

        <p class="text-xs text-gray-400">
            Tu solicitud será revisada por un administrador. Recibirás confirmación una vez aprobada.
        </p>

        <div class="flex gap-3">
            <button type="submit" class="btn-gold">Enviar solicitud</button>
            <a href="/oficina" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
