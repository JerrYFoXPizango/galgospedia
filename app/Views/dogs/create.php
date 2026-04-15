<?php $pageTitle = 'Añadir Galgo'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-display font-bold mb-8">Añadir Galgo</h1>

    <form method="POST" action="/galgos" enctype="multipart/form-data" class="space-y-6">
        <?= \Helpers\Csrf::field() ?>

        <!-- Photo upload -->
        <div class="card" x-data="imageUpload()">
            <label class="form-label">Foto del galgo</label>
            <div class="mt-2 flex flex-col items-center gap-4">
                <div class="w-40 h-40 rounded-xl overflow-hidden bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center"
                     :class="{ 'border-galgo-gold': isDragging }"
                     @dragover.prevent="isDragging = true"
                     @dragleave="isDragging = false"
                     @drop.prevent="handleDrop($event)">
                    <img x-show="preview" :src="preview" class="w-full h-full object-cover">
                    <span x-show="!preview" class="text-5xl">📷</span>
                </div>
                <input type="file" name="photo" accept="image/*" class="text-sm text-gray-500"
                       @change="handleFile($event)">
                <p class="text-xs text-gray-400">JPEG, PNG o WebP. Máx 10 MB. Se convierte a WebP automáticamente.</p>
            </div>
        </div>

        <!-- Name & gender -->
        <div class="card space-y-4">
            <div>
                <label class="form-label" for="name">Nombre del galgo <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required class="form-input" maxlength="120">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="gender">Sexo</label>
                    <select id="gender" name="gender" class="form-input">
                        <option value="unknown">Desconocido</option>
                        <option value="male">Macho</option>
                        <option value="female">Hembra</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="breed_variant">Variedad</label>
                    <select id="breed_variant" name="breed_variant" class="form-input">
                        <option value="spanish_greyhound">Galgo Español</option>
                        <option value="english_greyhound">Galgo Inglés</option>
                        <option value="hybrid">Galgo Híbrido</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="card space-y-4">
            <h2 class="font-semibold">Detalles (opcional)</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="date_of_birth">Fecha de nacimiento</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input">
                </div>
                <div>
                    <label class="form-label" for="color">Color del pelaje</label>
                    <input type="text" id="color" name="color" class="form-input" placeholder="Ej: Atigrado negro">
                </div>
                <div>
                    <label class="form-label" for="club">Club</label>
                    <input type="text" id="club" name="club" class="form-input" placeholder="Ej: Club Galgo España">
                </div>
                <div>
                    <label class="form-label" for="country">País</label>
                    <input type="text" id="country" name="country" class="form-input" placeholder="Ej: España">
                </div>
            </div>
            <div>
                <label class="form-label" for="champion">Títulos / Campeón</label>
                <input type="text" id="champion" name="champion" class="form-input" placeholder="Ej: Campeón Nacional 2023, Copa del Rey 2022">
            </div>
            <div>
                <label class="form-label" for="notes">Notas adicionales</label>
                <textarea id="notes" name="notes" rows="3" class="form-input resize-none"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_public" name="is_public" value="1" checked class="rounded">
                <label for="is_public" class="text-sm text-gray-600">Perfil público (visible para todos)</label>
            </div>
        </div>

        <!-- Tipo Reproductor -->
        <div class="card space-y-3">
            <h2 class="font-semibold">Tipo Reproductor</h2>
            <p class="text-xs text-gray-400">El galgo aparecerá automáticamente en la sección correspondiente.</p>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_stallion" value="1" class="rounded">
                    <span class="text-sm font-medium">Semental</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_broodmare" value="1" class="rounded">
                    <span class="text-sm font-medium">Reproductora</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-red px-8 py-3">Guardar Galgo</button>
            <a href="/galgos" class="btn-outline px-8 py-3">Cancelar</a>
        </div>
    </form>
</div>

<?php
$extraScripts = '<script src="/js/alpine-components.js"></script>';
require APP_PATH . '/Views/layout/footer.php';
?>
