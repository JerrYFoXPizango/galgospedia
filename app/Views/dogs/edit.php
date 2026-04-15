<?php $pageTitle = 'Editar — ' . htmlspecialchars($dog['name']); require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-display font-bold mb-8">Editar: <?= htmlspecialchars($dog['name']) ?></h1>

    <form method="POST" action="/galgos/<?= htmlspecialchars($dog['slug']) ?>/actualizar" enctype="multipart/form-data" class="space-y-6">
        <?= \Helpers\Csrf::field() ?>

        <!-- Photo -->
        <div class="card" x-data="imageUpload()">
            <label class="form-label">Foto del galgo</label>
            <div class="mt-2 flex flex-col items-center gap-4">
                <div class="relative w-full max-w-xs rounded-xl overflow-hidden bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center min-h-[180px]">
                    <?php if ($dog['photo_webp']): ?>
                        <img src="<?= \Helpers\Asset::url($dog['photo_webp']) ?>" alt="" class="w-full object-contain max-h-64" id="current-photo">
                    <?php else: ?>
                        <span class="text-5xl py-8">📷</span>
                    <?php endif; ?>
                    <img x-show="preview" :src="preview" class="w-full object-contain max-h-64 absolute inset-0">
                </div>
                <input type="file" name="photo" accept="image/*" class="text-sm text-gray-500" @change="handleFile($event)">
                <p class="text-xs text-gray-400">Deja vacío para conservar la foto actual.</p>
            </div>
        </div>

        <!-- Name & gender -->
        <div class="card space-y-4">
            <div>
                <label class="form-label" for="name">Nombre <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required class="form-input" value="<?= htmlspecialchars($dog['name']) ?>">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Sexo</label>
                    <select name="gender" class="form-input">
                        <option value="unknown" <?= $dog['gender'] === 'unknown'  ? 'selected' : '' ?>>Desconocido</option>
                        <option value="male"    <?= $dog['gender'] === 'male'     ? 'selected' : '' ?>>Macho</option>
                        <option value="female"  <?= $dog['gender'] === 'female'   ? 'selected' : '' ?>>Hembra</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Variedad</label>
                    <select name="breed_variant" class="form-input">
                        <option value="spanish_greyhound" <?= $dog['breed_variant'] === 'spanish_greyhound' ? 'selected' : '' ?>>Galgo Español</option>
                        <option value="english_greyhound" <?= $dog['breed_variant'] === 'english_greyhound' ? 'selected' : '' ?>>Galgo Inglés</option>
                        <option value="hybrid"            <?= $dog['breed_variant'] === 'hybrid'            ? 'selected' : '' ?>>Galgo Híbrido</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="card space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nacimiento</label>
                    <input type="date" name="date_of_birth" class="form-input" value="<?= htmlspecialchars($dog['date_of_birth'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Fallecimiento</label>
                    <input type="date" name="date_of_death" class="form-input" value="<?= htmlspecialchars($dog['date_of_death'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-input" value="<?= htmlspecialchars($dog['color'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Club</label>
                    <input type="text" name="club" class="form-input" value="<?= htmlspecialchars($dog['club'] ?? '') ?>" placeholder="Ej: Club Galgo España">
                </div>
                <div>
                    <label class="form-label">País</label>
                    <input type="text" name="country" class="form-input" value="<?= htmlspecialchars($dog['country'] ?? '') ?>" placeholder="Ej: España">
                </div>
            </div>
            <div>
                <label class="form-label">Títulos / Campeón</label>
                <input type="text" name="champion" class="form-input" value="<?= htmlspecialchars($dog['champion'] ?? '') ?>" placeholder="Ej: Campeón Nacional 2023, Copa del Rey 2022">
            </div>
            <div>
                <label class="form-label">Notas</label>
                <textarea name="notes" rows="3" class="form-input resize-none"><?= htmlspecialchars($dog['notes'] ?? '') ?></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_public" name="is_public" value="1" <?= $dog['is_public'] ? 'checked' : '' ?> class="rounded">
                <label for="is_public" class="text-sm">Perfil público</label>
            </div>
        </div>

        <!-- Tipo Reproductor -->
        <div class="card space-y-3">
            <h2 class="font-semibold">Tipo Reproductor</h2>
            <p class="text-xs text-gray-400">El galgo aparecerá automáticamente en la sección correspondiente.</p>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_stallion" value="1" class="rounded"
                           <?= $isStallion ? 'checked' : '' ?>>
                    <span class="text-sm font-medium">Semental</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_broodmare" value="1" class="rounded"
                           <?= $isBreeder ? 'checked' : '' ?>>
                    <span class="text-sm font-medium">Reproductora</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-red px-8 py-3">Guardar cambios</button>
            <a href="/galgos/<?= htmlspecialchars($dog['slug']) ?>" class="btn-outline px-8 py-3">Cancelar</a>
        </div>
    </form>
</div>

<?php
$extraScripts = '<script src="/js/alpine-components.js"></script>';
require APP_PATH . '/Views/layout/footer.php';
?>
