<?php
$pageTitle = 'Añadir documento — Billetera';
require APP_PATH . '/Views/layout/header.php';

$o = $old ?? [];
$v = fn(string $k, string $d = '') => htmlspecialchars($o[$k] ?? $d);
?>

<div class="container mx-auto px-4 py-8 max-w-xl">

    <!-- Cabecera -->
    <div class="mb-6">
        <a href="/mi-billetera" class="text-sm text-gray-400 hover:text-galgo-gold transition flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a la billetera
        </a>
        <h1 class="text-2xl font-display font-bold">Añadir documento</h1>
        <p class="text-sm text-gray-500 mt-1">Máximo 10 MB por archivo · JPEG, PNG, WEBP o PDF</p>
    </div>

    <!-- Errores -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/mi-billetera" enctype="multipart/form-data"
          class="card space-y-5" x-data="{ hasReg: <?= isset($o['dog_id']) && $o['dog_id'] ? 'true' : 'false' ?> }">
        <?= \Helpers\Csrf::field() ?>

        <!-- Tipo de documento -->
        <div>
            <label class="form-label">Tipo de documento <span class="text-red-500">*</span></label>
            <select name="doc_type" class="form-input">
                <?php foreach ($docTypes as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($v('doc_type','otro') === $key) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Título -->
        <div>
            <label class="form-label">Título <span class="text-red-500">*</span></label>
            <input type="text" name="title" class="form-input"
                   placeholder="Ej: Cartilla de Trueno, Licencia 2025..."
                   value="<?= $v('title') ?>" maxlength="200">
        </div>

        <!-- Galgo asociado -->
        <?php if (!empty($dogs)): ?>
        <div>
            <label class="form-label">Galgo (opcional)</label>
            <select name="dog_id" class="form-input">
                <option value="0">— Sin galgo específico —</option>
                <?php foreach ($dogs as $dog): ?>
                    <option value="<?= (int)$dog['id'] ?>"
                        <?= ((int)($o['dog_id'] ?? 0) === (int)$dog['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dog['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-gray-400 mt-1">Vincula el documento a uno de tus galgos para organizarlo mejor.</p>
        </div>
        <?php else: ?>
            <input type="hidden" name="dog_id" value="0">
        <?php endif; ?>

        <!-- Fecha de caducidad -->
        <div>
            <label class="form-label">Fecha de caducidad (opcional)</label>
            <input type="date" name="expires_at" class="form-input" value="<?= $v('expires_at') ?>">
            <p class="text-xs text-gray-400 mt-1">Útil para licencias anuales — te mostrará un aviso visual cuando se acerque la fecha.</p>
        </div>

        <!-- Archivo -->
        <div>
            <label class="form-label">Archivo <span class="text-red-500">*</span></label>
            <input type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf"
                   class="block w-full text-sm text-gray-600
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-lg file:border-0
                          file:text-sm file:font-semibold
                          file:bg-galgo-gold/10 file:text-yellow-800
                          hover:file:bg-galgo-gold/20 cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">Los archivos se almacenan de forma privada y solo tú puedes verlos.</p>
        </div>

        <!-- Notas -->
        <div>
            <label class="form-label">Notas (opcional)</label>
            <textarea name="notes" class="form-input" rows="2"
                      placeholder="Cualquier observación sobre este documento..."><?= $v('notes') ?></textarea>
        </div>

        <!-- Acciones -->
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-gold flex-1">Guardar documento</button>
            <a href="/mi-billetera" class="btn-outline flex-1 text-center">Cancelar</a>
        </div>
    </form>

</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
