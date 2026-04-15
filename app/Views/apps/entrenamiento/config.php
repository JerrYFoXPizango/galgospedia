<?php
$pageTitle = 'Configurar límites — ' . htmlspecialchars($dog['name']);
require APP_PATH . '/Views/layout/header.php';
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
            <h1 class="text-xl font-display font-bold">Límites de entrenamiento</h1>
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

    <!-- Explicación -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 text-sm text-blue-700">
        <p class="font-semibold mb-1">¿Para qué sirve esta configuración?</p>
        <p class="text-xs text-blue-600 leading-relaxed">
            Define los umbrales personalizados para este galgo. El semáforo se vuelve <strong>naranja</strong> al alcanzar el 85% del límite
            y <strong>rojo</strong> al superarlo. Cada galgo es diferente — un perro joven o recién recuperado de lesión necesitará límites más bajos.
        </p>
    </div>

    <form method="POST" action="/apps/entrenamiento/<?= $dog['slug'] ?>/configurar" class="space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <!-- Km semanales máximos -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="text-2xl">📏</span>
                <div>
                    <label class="form-label" for="max_weekly_km">Kilómetros semanales máximos</label>
                    <p class="text-xs text-gray-400">Distancia total acumulada en 7 días. Semáforo rojo al superarla.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="number" id="max_weekly_km" name="max_weekly_km"
                       value="<?= htmlspecialchars((string)$cfg['max_weekly_km']) ?>"
                       min="1" max="200" step="0.5" required class="form-input w-32">
                <span class="text-sm text-gray-500">km / semana</span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Referencia: galgo en forma óptima 25-40 km/sem · en preparación 15-25 · recuperación &lt;15</p>
        </div>

        <!-- Días consecutivos alta intensidad -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="text-2xl">🔴</span>
                <div>
                    <label class="form-label" for="max_consecutive_high">Días consecutivos de alta intensidad</label>
                    <p class="text-xs text-gray-400">Máximo de días seguidos con sesiones de intensidad alta antes de alertar.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="number" id="max_consecutive_high" name="max_consecutive_high"
                       value="<?= htmlspecialchars((string)$cfg['max_consecutive_high']) ?>"
                       min="1" max="14" required class="form-input w-24">
                <span class="text-sm text-gray-500">días seguidos</span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Recomendado: 2-3 días. Superar este límite activa semáforo rojo por riesgo de sobrecarga muscular.</p>
        </div>

        <!-- Días de descanso tras competición -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="text-2xl">🏆</span>
                <div>
                    <label class="form-label" for="rest_days_after_competition">Días de descanso tras competición</label>
                    <p class="text-xs text-gray-400">Días mínimos de recuperación recomendados después de una competición oficial.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="number" id="rest_days_after_competition" name="rest_days_after_competition"
                       value="<?= htmlspecialchars((string)$cfg['rest_days_after_competition']) ?>"
                       min="0" max="30" required class="form-input w-24">
                <span class="text-sm text-gray-500">días mínimos</span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Recomendado: 2-3 días para galgos adultos en buen estado. Más si hubo esfuerzo extremo.</p>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <a href="/apps/entrenamiento/<?= $dog['slug'] ?>" class="btn-outline flex-1 text-center text-sm py-3">Cancelar</a>
            <button type="submit" class="btn-gold flex-1 text-sm py-3">Guardar configuración</button>
        </div>
    </form>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
