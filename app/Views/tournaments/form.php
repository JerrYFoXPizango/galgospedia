<?php
/**
 * Partial: formulario de torneo (crear y editar)
 * Variables esperadas:
 *   $tournament  — array con datos existentes (editar) o null/array vacío (crear)
 *   $errors      — array de mensajes de error por campo
 *   $formAction  — URL del action del form
 */

$t  = $tournament ?? [];
$e  = $errors ?? [];
$v  = fn(string $k, mixed $def = '') => htmlspecialchars((string)($t[$k] ?? $def));

function fmtDtInput(?string $datetime): string {
    if (!$datetime) return '';
    try { return (new \DateTime($datetime))->format('Y-m-d\TH:i'); }
    catch (\Exception) { return ''; }
}
$isAdmin = \Services\AuthService::isAdmin();
?>

<form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
    <?= \Helpers\Csrf::field() ?>

    <!-- ── 1. Información básica ────────────────────────── -->
    <div class="card mb-5">
        <h2 class="font-semibold text-gray-700 mb-4">Información básica</h2>
        <div class="grid sm:grid-cols-2 gap-4">

            <!-- Título -->
            <div class="sm:col-span-2">
                <label class="form-label" for="title">Título <span class="text-galgo-red">*</span></label>
                <input type="text" id="title" name="title" value="<?= $v('title') ?>"
                       class="form-input <?= isset($e['title']) ? 'border-red-400' : '' ?>"
                       placeholder="Ej: XI Campeonato Regional de Galgo Español 2026" required>
                <?php if (isset($e['title'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $e['title'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Disciplina -->
            <div>
                <label class="form-label" for="discipline">Disciplina <span class="text-galgo-red">*</span></label>
                <select id="discipline" name="discipline"
                        class="form-input <?= isset($e['discipline']) ? 'border-red-400' : '' ?>" required>
                    <option value="">— Seleccionar —</option>
                    <option value="campo"           <?= ($t['discipline'] ?? '') === 'campo'           ? 'selected' : '' ?>>Galgos en Campo</option>
                    <option value="liebre_mecanica" <?= ($t['discipline'] ?? '') === 'liebre_mecanica' ? 'selected' : '' ?>>Liebre Mecánica</option>
                    <option value="campeonato"      <?= ($t['discipline'] ?? '') === 'campeonato'      ? 'selected' : '' ?>>Campeonato</option>
                    <option value="morfologico"     <?= ($t['discipline'] ?? '') === 'morfologico'     ? 'selected' : '' ?>>Morfológico</option>
                    <option value="talleres"        <?= ($t['discipline'] ?? '') === 'talleres'        ? 'selected' : '' ?>>Talleres</option>
                    <option value="varios"          <?= ($t['discipline'] ?? '') === 'varios'          ? 'selected' : '' ?>>Varios</option>
                </select>
                <?php if (isset($e['discipline'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $e['discipline'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Categoría -->
            <div>
                <label class="form-label" for="category">Categoría</label>
                <input type="text" id="category" name="category" value="<?= $v('category') ?>"
                       class="form-input" placeholder="Ej: Regional, Nacional, Local…">
            </div>

            <!-- Estado (solo admin o al editar) -->
            <?php if ($isAdmin || !empty($t['id'])): ?>
            <div>
                <label class="form-label" for="status">Estado</label>
                <select id="status" name="status" class="form-input">
                    <option value="published" <?= ($t['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Publicado</option>
                    <option value="draft"     <?= ($t['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Borrador</option>
                    <option value="cancelled" <?= ($t['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── 2. Fechas ─────────────────────────────────────── -->
    <div class="card mb-5">
        <h2 class="font-semibold text-gray-700 mb-4">Fechas y hora</h2>
        <div class="grid sm:grid-cols-3 gap-4">

            <div>
                <label class="form-label" for="starts_at">Inicio <span class="text-galgo-red">*</span></label>
                <input type="datetime-local" id="starts_at" name="starts_at"
                       value="<?= fmtDtInput($t['starts_at'] ?? null) ?>"
                       class="form-input <?= isset($e['starts_at']) ? 'border-red-400' : '' ?>" required>
                <?php if (isset($e['starts_at'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $e['starts_at'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="form-label" for="ends_at">Fin (opcional)</label>
                <input type="datetime-local" id="ends_at" name="ends_at"
                       value="<?= fmtDtInput($t['ends_at'] ?? null) ?>"
                       class="form-input <?= isset($e['ends_at']) ? 'border-red-400' : '' ?>">
                <?php if (isset($e['ends_at'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $e['ends_at'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="form-label" for="meeting_time">Hora de reunión</label>
                <input type="time" id="meeting_time" name="meeting_time"
                       value="<?= !empty($t['meeting_time']) ? substr($t['meeting_time'], 0, 5) : '' ?>"
                       class="form-input" placeholder="08:30">
                <p class="text-xs text-gray-400 mt-1">Hora a la que deben estar en el punto de reunión</p>
            </div>
        </div>
    </div>

    <!-- ── 3. Ubicación ───────────────────────────────────── -->
    <div class="card mb-5">
        <h2 class="font-semibold text-gray-700 mb-4">Ubicación</h2>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="form-label" for="location_name">Nombre del lugar</label>
                <input type="text" id="location_name" name="location_name"
                       value="<?= $v('location_name') ?>"
                       class="form-input" placeholder='Ej: Finca "El Espinar" (Nava del Rey)'>
            </div>
            <div class="relative">
                <label class="form-label" for="location_address">Dirección</label>
                <div class="flex gap-2">
                    <input type="text" id="location_address" name="location_address"
                           value="<?= $v('location_address') ?>"
                           class="form-input flex-1" placeholder="Calle, municipio, provincia…">
                    <button type="button" id="geocode-btn"
                            class="flex-shrink-0 btn-outline text-sm px-3">
                        Buscar
                    </button>
                </div>
                <!-- Resultados Nominatim -->
                <div id="geocode-results"
                     class="hidden absolute z-50 top-full left-0 w-full bg-white border border-gray-200 rounded-lg shadow-xl text-xs overflow-hidden">
                </div>
            </div>
        </div>

        <!-- Mapa picker -->
        <div class="relative" style="isolation:isolate;z-index:0;">
            <div id="map-picker"
                 data-lat="<?= htmlspecialchars((string)($t['location_lat'] ?? '')) ?>"
                 data-lng="<?= htmlspecialchars((string)($t['location_lng'] ?? '')) ?>"
                 class="rounded-lg overflow-hidden border border-gray-200"
                 style="height: 300px;">
            </div>
            <p class="text-xs text-gray-400 mt-2">
                Haz clic en el mapa o busca una dirección para fijar las coordenadas. El marcador es arrastrable.
            </p>
        </div>

        <input type="hidden" id="location_lat" name="location_lat"
               value="<?= htmlspecialchars((string)($t['location_lat'] ?? '')) ?>">
        <input type="hidden" id="location_lng" name="location_lng"
               value="<?= htmlspecialchars((string)($t['location_lng'] ?? '')) ?>">

        <div class="flex items-center gap-3 mt-2">
            <span id="coords-display" class="text-xs text-gray-500 font-mono
                  <?= empty($t['location_lat']) ? 'hidden' : '' ?>">
                <?php if (!empty($t['location_lat'])): ?>
                    <?= number_format((float)$t['location_lat'], 5) ?>° <?= (float)$t['location_lat'] >= 0 ? 'N' : 'S' ?>,
                    <?= number_format(abs((float)$t['location_lng']), 5) ?>° <?= (float)$t['location_lng'] >= 0 ? 'E' : 'O' ?>
                <?php endif; ?>
            </span>
            <button type="button" id="clear-coords-btn"
                    class="text-xs text-gray-400 hover:text-red-500 transition <?= empty($t['location_lat']) ? 'hidden' : '' ?>">
                × Limpiar pin
            </button>
        </div>

        <!-- Link externo alternativo -->
        <div class="mt-4 pt-4 border-t border-gray-100">
            <label class="form-label" for="map_url">Link a mapa externo (alternativa al pin)</label>
            <input type="url" id="map_url" name="map_url" value="<?= $v('map_url') ?>"
                   class="form-input" placeholder="https://maps.google.com/...">
            <p class="text-xs text-gray-400 mt-1">Si el lugar es difícil de encontrar por coordenadas, pega aquí un link de Google Maps, What3Words, etc.</p>
        </div>
    </div>

    <!-- ── 4. Punto de reunión y notas ────────────────────── -->
    <div class="card mb-5">
        <h2 class="font-semibold text-gray-700 mb-4">Punto de reunión y notas</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="meeting_point">Punto de reunión</label>
                <textarea id="meeting_point" name="meeting_point" rows="3"
                          class="form-input resize-none"
                          placeholder="Ej: Gasolinera Repsol (Ctra. CL-602). Salida en convoy a las 08:30h."><?= $v('meeting_point') ?></textarea>
                <p class="text-xs text-gray-400 mt-1">Lugar donde concentrarse antes de ir al campo</p>
            </div>
            <div>
                <label class="form-label" for="notes">
                    ⚠️ Notas y advertencias de acceso
                </label>
                <textarea id="notes" name="notes" rows="3"
                          class="form-input resize-none"
                          placeholder="Ej: El último tramo es un camino de tierra de 2km apto para todos los vehículos."><?= $v('notes') ?></textarea>
                <p class="text-xs text-gray-400 mt-1">Información sobre el acceso, terreno, requisitos…</p>
            </div>
        </div>
    </div>

    <!-- ── 5. Descripción ────────────────────────────────── -->
    <div class="card mb-5">
        <h2 class="font-semibold text-gray-700 mb-4">Descripción (opcional)</h2>
        <textarea id="description" name="description" rows="4"
                  class="form-input resize-none"
                  placeholder="Información adicional sobre el evento, formato, reglas…"><?= $v('description') ?></textarea>
    </div>

    <!-- ── 6. Organización ───────────────────────────────── -->
    <div class="card mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">Organización</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="organizer_name">Organizador</label>
                <input type="text" id="organizer_name" name="organizer_name"
                       value="<?= $v('organizer_name') ?>"
                       class="form-input" placeholder="Club, federación o persona…">
            </div>
            <div>
                <label class="form-label" for="contact_info">Contacto</label>
                <input type="text" id="contact_info" name="contact_info"
                       value="<?= $v('contact_info') ?>"
                       class="form-input" placeholder="Teléfono, email, WhatsApp…">
            </div>
            <div>
                <label class="form-label" for="max_participants">Plazas máximas</label>
                <input type="number" id="max_participants" name="max_participants"
                       value="<?= $v('max_participants') ?>"
                       min="1" class="form-input" placeholder="Dejar vacío = sin límite">
            </div>
            <div class="flex items-start pt-6" x-data="{ reg: <?= ($t['registration_required'] ?? 0) ? 'true' : 'false' ?> }">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="registration_required" value="1"
                           class="rounded accent-galgo-red w-4 h-4"
                           x-model="reg"
                           <?= ($t['registration_required'] ?? 0) ? 'checked' : '' ?>>
                    <span class="text-sm font-medium text-gray-700">Inscripción obligatoria</span>
                </label>
                <div x-show="reg" class="w-full mt-3" x-cloak>
                    <label class="form-label" for="registration_url">URL de inscripción</label>
                    <input type="url" id="registration_url" name="registration_url"
                           value="<?= $v('registration_url') ?>"
                           class="form-input" placeholder="https://…">
                </div>
            </div>
        </div>
    </div>

    <!-- ── 7. Cartel del evento ─────────────────────────────── -->
    <div class="card mb-6" x-data="posterUpload()">
        <h2 class="font-semibold text-gray-700 mb-1">Cartel del evento</h2>
        <p class="text-xs text-gray-400 mb-4">Sube la imagen promocional del torneo (JPG, PNG o WebP, máx. 8 MB).</p>

        <?php $currentPoster = $t['poster'] ?? null; ?>

        <!-- Vista previa actual (al editar) -->
        <?php if ($currentPoster): ?>
        <div class="mb-4" x-show="!removed">
            <img src="<?= htmlspecialchars(\Helpers\Asset::url($currentPoster)) ?>"
                 alt="Cartel actual"
                 class="max-h-64 rounded-lg border border-gray-200 object-contain">
            <div class="mt-2 flex items-center gap-3">
                <span class="text-xs text-gray-500">Cartel actual</span>
                <button type="button" @click="removeExisting()"
                        class="text-xs text-red-500 hover:underline">× Eliminar cartel</button>
            </div>
            <input type="hidden" name="remove_poster" :value="removed ? '1' : '0'">
        </div>
        <?php endif; ?>

        <!-- Zona de subida -->
        <div x-show="!previewUrl && !<?= $currentPoster ? '(hasCurrent && !removed)' : 'false' ?>"
             class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-galgo-red transition cursor-pointer"
             @click="$refs.posterInput.click()"
             @dragover.prevent
             @drop.prevent="handleDrop($event)">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm text-gray-500">Arrastra una imagen aquí o <span class="text-galgo-red font-medium">selecciona un archivo</span></p>
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP · Máx. 8 MB</p>
        </div>

        <!-- Vista previa nueva imagen -->
        <div x-show="previewUrl" class="mt-3">
            <img :src="previewUrl" alt="Vista previa" class="max-h-64 rounded-lg border border-gray-200 object-contain">
            <div class="mt-2 flex items-center gap-3">
                <span class="text-xs text-gray-500" x-text="fileName"></span>
                <button type="button" @click="clearPreview()"
                        class="text-xs text-red-500 hover:underline">× Quitar</button>
            </div>
        </div>

        <input type="file" name="poster" accept="image/*" x-ref="posterInput"
               class="hidden" @change="handleFile($event)">
    </div>

    <!-- Submit -->
    <div class="flex gap-3">
        <button type="submit" class="btn-red">
            <?= !empty($t['id']) ? 'Guardar cambios' : 'Publicar torneo' ?>
        </button>
        <a href="<?= !empty($t['id']) ? '/torneos/' . $t['slug'] : '/torneos' ?>" class="btn-outline">
            Cancelar
        </a>
    </div>
</form>

<script>
function posterUpload() {
    return {
        previewUrl: null,
        fileName: '',
        removed: false,
        hasCurrent: <?= $currentPoster ? 'true' : 'false' ?>,
        handleFile(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.fileName   = file.name;
            this.previewUrl = URL.createObjectURL(file);
        },
        handleDrop(e) {
            const file = e.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            this.$refs.posterInput.files = e.dataTransfer.files;
            this.fileName   = file.name;
            this.previewUrl = URL.createObjectURL(file);
        },
        clearPreview() {
            this.previewUrl = null;
            this.fileName   = '';
            this.$refs.posterInput.value = '';
        },
        removeExisting() {
            this.removed = true;
        },
    };
}
</script>
