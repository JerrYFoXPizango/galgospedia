<?php
$isEdit    = $record !== null;
$pageTitle = $isEdit ? 'Editar registro veterinario' : 'Nuevo registro veterinario';
require APP_PATH . '/Views/layout/header.php';

$old = $old ?? $record ?? [];
$v   = fn(string $k, string $d = '') => htmlspecialchars((string)($old[$k] ?? $d));

// Lesiones comunes en galgos
$commonInjuries = [
    'Fractura dedo', 'Fractura metacarpo', 'Fractura metatarso',
    'Desgarro muscular', 'Contractura muscular', 'Luxación tarso',
    'Luxación carpo', 'Rotura tendón', 'Esguince', 'Hematoma',
    'Herida en almohadilla', 'Problema de espalda', 'Cojera sin diagnóstico',
];
$bodyParts = [
    'Dedo delantero izquierdo', 'Dedo delantero derecho',
    'Dedo trasero izquierdo', 'Dedo trasero derecho',
    'Pata delantera izquierda', 'Pata delantera derecha',
    'Pata trasera izquierda', 'Pata trasera derecha',
    'Tarso izquierdo', 'Tarso derecho', 'Carpo izquierdo', 'Carpo derecho',
    'Columna / Espalda', 'Cuello', 'Cadera', 'Pecho / Costillas',
    'Almohadilla', 'Muslo', 'General',
];
?>

<div class="container mx-auto px-4 py-8 max-w-xl">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="/apps/veterinario/<?= $dog['slug'] ?>"
           class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-display font-bold"><?= $isEdit ? 'Editar registro' : 'Nuevo registro' ?></h1>
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
        ? '/apps/veterinario/registro/' . $record['id'] . '/actualizar'
        : '/apps/veterinario/' . $dog['slug'] . '/nuevo';
    ?>
    <form method="POST" action="<?= $action ?>" x-data="vetForm()" class="space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <!-- Tipo -->
        <div>
            <label class="form-label">Tipo de registro <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <?php foreach (['vaccine'=>'💉 Vacuna','deworming'=>'🐛 Desparasitación','injury'=>'🩹 Lesión','visit'=>'🏥 Visita vet.','weight'=>'⚖️ Peso'] as $val=>$label): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="<?= $val ?>"
                           <?= $v('type') === $val || (!$isEdit && !$old && $val === 'vaccine') ? 'checked' : '' ?>
                           x-model="type" class="sr-only">
                    <div :class="type==='<?= $val ?>' ? 'border-galgo-red bg-red-50 text-galgo-red' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                         class="border rounded-xl px-3 py-2.5 text-sm font-medium text-center transition-all">
                        <?= $label ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Título -->
        <div>
            <label class="form-label" for="title">Título / Descripción <span class="text-red-400">*</span></label>
            <!-- Sugerencias para lesiones -->
            <div x-show="type==='injury'" class="flex flex-wrap gap-1.5 mb-2">
                <?php foreach ($commonInjuries as $inj): ?>
                <button type="button"
                        @click="$refs.titleInput.value='<?= addslashes($inj) ?>'; $refs.titleInput.dispatchEvent(new Event('input'))"
                        class="text-xs bg-red-50 text-red-600 border border-red-100 rounded-full px-2.5 py-1 hover:bg-red-100 transition-colors">
                    <?= $inj ?>
                </button>
                <?php endforeach; ?>
            </div>
            <input type="text" id="title" name="title" x-ref="titleInput"
                   value="<?= $v('title') ?>" required
                   placeholder="Ej: Vacuna rabia, Fractura dedo..."
                   class="form-input">
        </div>

        <!-- Fecha -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="date">Fecha <span class="text-red-400">*</span></label>
                <input type="date" id="date" name="date"
                       value="<?= $v('date', date('Y-m-d')) ?>" required class="form-input">
            </div>
            <div x-show="type!=='weight' && type!=='injury'">
                <label class="form-label" for="next_due_date">Próxima dosis</label>
                <input type="date" id="next_due_date" name="next_due_date"
                       value="<?= $v('next_due_date') ?>" class="form-input">
            </div>
        </div>

        <!-- Campos específicos lesión -->
        <div x-show="type==='injury'" class="space-y-4 bg-red-50 rounded-xl p-4 border border-red-100">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Datos de la lesión</p>
            <div>
                <label class="form-label" for="body_part">Zona afectada</label>
                <select id="body_part" name="body_part" class="form-input">
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($bodyParts as $bp): ?>
                    <option value="<?= $bp ?>" <?= $v('body_part') === $bp ? 'selected' : '' ?>><?= $bp ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Gravedad</label>
                <div class="flex gap-3">
                    <?php foreach (['mild'=>'🟡 Leve','moderate'=>'🟠 Moderada','severe'=>'🔴 Grave'] as $val=>$label): ?>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="severity" value="<?= $val ?>"
                               <?= $v('severity') === $val ? 'checked' : '' ?> class="accent-galgo-red">
                        <span class="text-sm text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <label class="form-label" for="resolved_at">Fecha de alta (si ya recuperado)</label>
                <input type="date" id="resolved_at" name="resolved_at"
                       value="<?= $v('resolved_at') ?>" class="form-input">
            </div>
        </div>

        <!-- Peso (solo tipo weight) -->
        <div x-show="type==='weight'">
            <label class="form-label" for="weight_kg">Peso (kg)</label>
            <input type="number" id="weight_kg" name="weight_kg" step="0.1" min="0" max="99"
                   value="<?= $v('weight_kg') ?>"
                   placeholder="Ej: 27.5" class="form-input">
        </div>

        <!-- Tratamiento -->
        <div x-show="type!=='weight'">
            <label class="form-label" for="treatment">Tratamiento / Medicación</label>
            <textarea id="treatment" name="treatment" rows="2"
                      placeholder="Medicación, dosis, pauta..."
                      class="form-input"><?= $v('treatment') ?></textarea>
        </div>

        <!-- Notas -->
        <div>
            <label class="form-label" for="notes">Notas adicionales</label>
            <textarea id="notes" name="notes" rows="2"
                      placeholder="Observaciones, nombre del veterinario, clínica..."
                      class="form-input"><?= $v('notes') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <a href="/apps/veterinario/<?= $dog['slug'] ?>" class="btn-outline flex-1 text-center text-sm py-3">Cancelar</a>
            <button type="submit" class="btn-gold flex-1 text-sm py-3">
                <?= $isEdit ? 'Guardar cambios' : 'Añadir registro' ?>
            </button>
        </div>
    </form>
</div>

<script>
function vetForm() {
    return {
        type: '<?= htmlspecialchars($v('type', 'vaccine')) ?>',
    };
}
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
