<?php
$isEdit    = isset($sponsor);
$pageTitle = $isEdit ? 'Editar patrocinador' : 'Nuevo patrocinador';
require APP_PATH . '/Views/layout/header.php';
$v = fn(string $k, string $d = '') => htmlspecialchars((string)($old[$k] ?? $d));
?>

<div class="container mx-auto px-4 py-8 max-w-xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="/admin/patrocinadores"
           class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-display font-bold"><?= $isEdit ? 'Editar patrocinador' : 'Nuevo patrocinador' ?></h1>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-5">
        <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php $action = $isEdit
        ? '/admin/patrocinadores/' . $sponsor['id'] . '/actualizar'
        : '/admin/patrocinadores/nuevo'; ?>

    <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <!-- Nombre -->
        <div>
            <label class="form-label" for="name">Nombre del patrocinador <span class="text-red-400">*</span></label>
            <input type="text" id="name" name="name"
                   value="<?= $v('name') ?>" required
                   placeholder="Ej: Clínica Veterinaria Galgo Sur"
                   class="form-input">
        </div>

        <!-- Logo -->
        <div>
            <label class="form-label">Logo <span class="text-red-400"><?= $isEdit ? '' : '*' ?></span></label>
            <?php if ($isEdit && !empty($old['logo_path'])): ?>
            <div class="mb-3 p-3 bg-gray-50 rounded-xl border border-gray-100 inline-block">
                <p class="text-xs text-gray-400 mb-2">Logo actual:</p>
                <img src="<?= htmlspecialchars($old['logo_path']) ?>"
                     alt="Logo actual"
                     class="h-16 object-contain">
            </div>
            <?php endif; ?>
            <input type="file" id="logo" name="logo"
                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                   class="form-input">
            <p class="text-xs text-gray-400 mt-1">
                PNG, JPG, WebP o SVG · Máx. 2 MB · Recomendado: fondo transparente, 300×120 px mínimo<?= $isEdit ? ' · Deja vacío para mantener el actual' : '' ?>
            </p>
        </div>

        <!-- URL web -->
        <div>
            <label class="form-label" for="website_url">URL de la web (opcional)</label>
            <input type="url" id="website_url" name="website_url"
                   value="<?= $v('website_url') ?>"
                   placeholder="https://www.ejemplo.com"
                   class="form-input">
            <p class="text-xs text-gray-400 mt-1">Si se indica, el logo será un enlace clicable.</p>
        </div>

        <!-- Orden -->
        <div>
            <label class="form-label" for="sort_order">Orden de aparición</label>
            <input type="number" id="sort_order" name="sort_order"
                   value="<?= $v('sort_order', '0') ?>"
                   min="0" max="999"
                   class="form-input w-32">
            <p class="text-xs text-gray-400 mt-1">Menor número = aparece antes en el carrusel.</p>
        </div>

        <!-- Activo (solo en edición) -->
        <?php if ($isEdit): ?>
        <div class="flex items-center gap-3">
            <input type="checkbox" id="active" name="active" value="1"
                   <?= ($old['active'] ?? 1) ? 'checked' : '' ?>
                   class="w-4 h-4 accent-galgo-red">
            <label for="active" class="text-sm text-gray-700">Visible en el carrusel</label>
        </div>
        <?php endif; ?>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <a href="/admin/patrocinadores" class="btn-outline flex-1 text-center text-sm py-3">Cancelar</a>
            <button type="submit" class="btn-gold flex-1 text-sm py-3">
                <?= $isEdit ? 'Guardar cambios' : 'Añadir patrocinador' ?>
            </button>
        </div>
    </form>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
