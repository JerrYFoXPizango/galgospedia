<?php
$isEdit    = $record !== null;
$pageTitle = $isEdit ? 'Editar sesión' : 'Nueva sesión de entrenamiento';
require APP_PATH . '/Views/layout/header.php';

$old = $old ?? $record ?? [];
$v   = fn(string $k, string $d = '') => htmlspecialchars((string)($old[$k] ?? $d));

// Precargar valor de distancia si es edición
$distanceM    = (int)($old['distance_m'] ?? 0);
$distValue    = '';
$distUnit     = 'm';
if ($distanceM > 0) {
    if ($distanceM >= 1000 && $distanceM % 100 === 0) {
        $distValue = number_format($distanceM / 1000, 1, '.', '');
        $distUnit  = 'km';
    } else {
        $distValue = $distanceM;
        $distUnit  = 'm';
    }
}
?>

<div class="container mx-auto px-4 py-8 max-w-xl">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="/apps/entrenamiento/<?= $dog['slug'] ?>"
           class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-display font-bold"><?= $isEdit ? 'Editar sesión' : 'Nueva sesión' ?></h1>
            <p class="text-xs text-gray-400"><?= htmlspecialchars($dog['name']) ?></p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-5">
        <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php
    $action = $isEdit
        ? '/apps/entrenamiento/sesion/' . $record['id'] . '/actualizar'
        : '/apps/entrenamiento/' . $dog['slug'] . '/nuevo';
    ?>
    <form method="POST" action="<?= $action ?>" x-data="trainForm()" class="space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <!-- Tipo de sesión -->
        <div>
            <label class="form-label">Tipo de sesión <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <?php foreach ([
                    'run_free'    => '🏃 Carrera libre',
                    'run_hare'    => '🐇 Con liebre',
                    'walk'        => '🦶 Paseo',
                    'track'       => '🏟️ Pista',
                    'active_rest' => '😴 Descanso activo',
                    'competition' => '🏆 Competición',
                ] as $val => $label): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="<?= $val ?>"
                           <?= $v('type', 'run_free') === $val ? 'checked' : '' ?>
                           x-model="type" class="sr-only">
                    <div :class="type==='<?= $val ?>' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                         class="border rounded-xl px-3 py-2.5 text-sm font-medium text-center transition-all">
                        <?= $label ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Intensidad -->
        <div>
            <label class="form-label">Intensidad <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
                <?php foreach (['low'=>'🟢 Baja','medium'=>'🟡 Media','high'=>'🔴 Alta'] as $val=>$label): ?>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="intensity" value="<?= $val ?>"
                           <?= $v('intensity','medium') === $val ? 'checked' : '' ?>
                           x-model="intensity" class="sr-only">
                    <div :class="intensity==='<?= $val ?>' ? '<?= $val==='low'?'border-green-400 bg-green-50 text-green-700':($val==='medium'?'border-yellow-400 bg-yellow-50 text-yellow-700':'border-red-400 bg-red-50 text-red-700') ?>' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                         class="border rounded-xl px-3 py-2.5 text-sm font-medium text-center transition-all">
                        <?= $label ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Fecha -->
        <div>
            <label class="form-label" for="date">Fecha <span class="text-red-400">*</span></label>
            <input type="date" id="date" name="date"
                   value="<?= $v('date', date('Y-m-d')) ?>" required class="form-input">
        </div>

        <!-- Distancia con selector de unidad -->
        <div x-show="type !== 'active_rest'">
            <label class="form-label">Distancia</label>
            <div class="flex gap-2">
                <input type="number" name="distance_value" id="distance_value"
                       value="<?= htmlspecialchars((string)$distValue) ?>"
                       step="0.1" min="0"
                       placeholder="Ej: 500 m o 3.5 km"
                       class="form-input flex-1">
                <select name="distance_unit" x-model="distUnit" class="form-input w-24">
                    <option value="m" <?= $distUnit === 'm' ? 'selected' : '' ?>>metros</option>
                    <option value="km" <?= $distUnit === 'km' ? 'selected' : '' ?>>km</option>
                </select>
            </div>
            <p class="text-xs text-gray-400 mt-1" x-show="distUnit==='m'">Para carreras cortas: 200-800 m. Para largos: cambia a km.</p>
            <p class="text-xs text-gray-400 mt-1" x-show="distUnit==='km'">Para paseos o jornadas de campo: 3-15 km.</p>
        </div>

        <!-- Duración -->
        <div>
            <label class="form-label" for="duration_min">Duración (minutos)</label>
            <input type="number" id="duration_min" name="duration_min"
                   value="<?= $v('duration_min') ?>"
                   min="1" max="999" placeholder="Ej: 45"
                   class="form-input">
        </div>

        <!-- Terreno -->
        <div x-show="type !== 'active_rest'">
            <label class="form-label" for="terrain">Tipo de terreno</label>
            <select id="terrain" name="terrain" class="form-input">
                <option value="">— Seleccionar —</option>
                <?php foreach (['campo'=>'Campo','monte'=>'Monte','pista'=>'Pista','arena'=>'Arena','hierba'=>'Hierba','barro'=>'Barro','mixto'=>'Mixto'] as $val=>$lbl): ?>
                <option value="<?= $val ?>" <?= $v('terrain') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Estado del galgo al terminar -->
        <div>
            <label class="form-label">Estado del galgo al terminar</label>
            <div class="flex gap-3">
                <?php foreach (['good'=>'😊 Bien','tired'=>'😓 Cansado','very_tired'=>'😩 Muy cansado'] as $val=>$lbl): ?>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="dog_condition" value="<?= $val ?>"
                           <?= $v('dog_condition') === $val ? 'checked' : '' ?>
                           x-model="condition" class="sr-only">
                    <div :class="condition==='<?= $val ?>' ? 'border-galgo-red bg-red-50 text-galgo-red' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                         class="border rounded-xl px-2 py-2.5 text-xs font-medium text-center transition-all">
                        <?= $lbl ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Temperatura -->
        <div>
            <label class="form-label" for="temperature_c">Temperatura aproximada (°C)</label>
            <input type="number" id="temperature_c" name="temperature_c"
                   value="<?= $v('temperature_c') ?>"
                   min="-10" max="50" placeholder="Ej: 18"
                   class="form-input">
        </div>

        <!-- Notas -->
        <div>
            <label class="form-label" for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="2"
                      placeholder="Observaciones, terreno concreto, rendimiento..."
                      class="form-input"><?= $v('notes') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <a href="/apps/entrenamiento/<?= $dog['slug'] ?>" class="btn-outline flex-1 text-center text-sm py-3">Cancelar</a>
            <button type="submit" class="btn-gold flex-1 text-sm py-3">
                <?= $isEdit ? 'Guardar cambios' : 'Añadir sesión' ?>
            </button>
        </div>
    </form>
</div>

<script>
function trainForm() {
    return {
        type:      '<?= htmlspecialchars($v('type', 'run_free')) ?>',
        intensity: '<?= htmlspecialchars($v('intensity', 'medium')) ?>',
        condition: '<?= htmlspecialchars($v('dog_condition', '')) ?>',
        distUnit:  '<?= htmlspecialchars($distUnit) ?>',
    };
}
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
