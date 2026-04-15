<?php
$pageTitle = 'Historial Veterinario';
require APP_PATH . '/Views/layout/header.php';
use Models\VetRecord;
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="/apps" class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-display font-bold leading-tight">Historial Veterinario</h1>
                <p class="text-xs text-gray-400 mt-0.5">Salud y seguimiento de tus galgos</p>
            </div>
        </div>
    </div>

    <?php \Helpers\Flash::render() ?>

    <?php if (empty($summary)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">🐕</div>
            <p class="font-semibold text-gray-700">No tienes galgos registrados</p>
            <p class="text-sm text-gray-400 mt-1">Añade un galgo para empezar a llevar su historial veterinario.</p>
            <a href="/galgos/nuevo" class="mt-4 inline-block btn-gold text-sm">+ Añadir galgo</a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($summary as $dog):
                // Calcular semáforo
                if ($dog['active_injuries'] > 0) {
                    $light = 'red'; $lightLabel = 'Lesión activa';
                    $lightCss = 'bg-red-500'; $cardBorder = 'border-red-100';
                } elseif ($dog['overdue'] > 0) {
                    $light = 'yellow'; $lightLabel = 'Tratamiento vencido';
                    $lightCss = 'bg-yellow-400'; $cardBorder = 'border-yellow-100';
                } elseif ($dog['due_soon'] > 0) {
                    $light = 'orange'; $lightLabel = 'Próxima dosis en 30 días';
                    $lightCss = 'bg-orange-400'; $cardBorder = 'border-orange-100';
                } else {
                    $light = 'green'; $lightLabel = 'Al día';
                    $lightCss = 'bg-green-400'; $cardBorder = 'border-green-100';
                }
            ?>
            <a href="/apps/veterinario/<?= htmlspecialchars($dog['slug']) ?>"
               class="flex items-center gap-4 bg-white rounded-2xl border <?= $cardBorder ?> shadow-sm p-4 hover:shadow-md transition-all group">

                <!-- Foto -->
                <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                    <?php if ($dog['photo_thumb']): ?>
                        <img src="<?= \Helpers\Asset::url($dog['photo_thumb']) ?>"
                             alt="<?= htmlspecialchars($dog['name']) ?>"
                             class="w-full h-full object-contain">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-2xl">🐕</div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate group-hover:text-galgo-red transition-colors">
                        <?= htmlspecialchars($dog['name']) ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <?= $dog['total_records'] ?> registro<?= $dog['total_records'] != 1 ? 's' : '' ?>
                    </p>
                </div>

                <!-- Semáforo -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="text-right">
                        <span class="text-xs font-medium <?= $light === 'green' ? 'text-green-600' : ($light === 'red' ? 'text-red-500' : 'text-yellow-600') ?>">
                            <?= $lightLabel ?>
                        </span>
                        <?php if ($dog['active_injuries'] > 0): ?>
                            <p class="text-xs text-red-400"><?= $dog['active_injuries'] ?> lesión<?= $dog['active_injuries'] > 1 ? 'es' : '' ?> activa<?= $dog['active_injuries'] > 1 ? 's' : '' ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="w-3 h-3 rounded-full <?= $lightCss ?> ring-2 ring-white shadow"></div>
                </div>

                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Leyenda semáforo -->
        <div class="mt-6 bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Leyenda</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-gray-500">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Al día</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400"></span> Dosis pronto</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Vencido</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Lesión activa</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
