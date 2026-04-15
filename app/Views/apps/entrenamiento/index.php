<?php
$pageTitle = 'Diario de Entrenamiento';
require APP_PATH . '/Views/layout/header.php';
use Models\Training;
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="/apps" class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-display font-bold leading-tight">Diario de Entrenamiento</h1>
                <p class="text-xs text-gray-400 mt-0.5">Control de carga y rendimiento de tus galgos</p>
            </div>
        </div>
    </div>

    <?php \Helpers\Flash::render() ?>

    <?php if (empty($summary)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">🐕</div>
            <p class="font-semibold text-gray-700">No tienes galgos registrados</p>
            <p class="text-sm text-gray-400 mt-1">Añade un galgo para empezar su diario de entrenamiento.</p>
            <a href="/galgos/nuevo" class="mt-4 inline-block btn-gold text-sm">+ Añadir galgo</a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($summary as $dog):
                $status = $dog['ot_status'];
                $dotCss  = ['green'=>'bg-green-400','orange'=>'bg-orange-400','red'=>'bg-red-500','gray'=>'bg-gray-300'][$status] ?? 'bg-gray-300';
                $borderCss = ['green'=>'border-green-100','orange'=>'border-orange-100','red'=>'border-red-100','gray'=>'border-gray-100'][$status] ?? 'border-gray-100';
                $labelCss  = ['green'=>'text-green-600','orange'=>'text-orange-500','red'=>'text-red-500','gray'=>'text-gray-400'][$status] ?? 'text-gray-400';
                $maxKm     = (float)$dog['max_weekly_km'];
                $weekKm    = (float)$dog['week_km'];
                $pct       = $maxKm > 0 ? min(100, round($weekKm / $maxKm * 100)) : 0;
                $barCss    = $pct >= 100 ? 'bg-red-400' : ($pct >= 85 ? 'bg-orange-400' : 'bg-green-400');
                $lastSess  = $dog['last_session'] ? (new DateTime($dog['last_session']))->format('d/m/Y') : null;
            ?>
            <a href="/apps/entrenamiento/<?= htmlspecialchars($dog['slug']) ?>"
               class="block bg-white rounded-2xl border <?= $borderCss ?> shadow-sm p-4 hover:shadow-md transition-all group">

                <div class="flex items-center gap-4">
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
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                            <span><?= $dog['total_sessions'] ?> sesión<?= $dog['total_sessions'] != 1 ? 'es' : '' ?></span>
                            <?php if ($lastSess): ?><span>· Última: <?= $lastSess ?></span><?php endif; ?>
                        </div>

                        <!-- Barra de carga semanal -->
                        <div class="mt-2">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="text-xs text-gray-400">Carga semanal</span>
                                <span class="text-xs font-medium <?= $labelCss ?>"><?= number_format($weekKm, 1) ?> / <?= number_format($maxKm, 1) ?> km</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full <?= $barCss ?> rounded-full transition-all" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Semáforo -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="text-right hidden sm:block">
                            <span class="text-xs font-medium <?= $labelCss ?>"><?= htmlspecialchars($dog['ot_label']) ?></span>
                        </div>
                        <div class="w-3 h-3 rounded-full <?= $dotCss ?> ring-2 ring-white shadow"></div>
                    </div>

                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Leyenda -->
        <div class="mt-6 bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Semáforo de carga</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-gray-500">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Carga normal</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400"></span> Cerca del límite</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Sobreentrenamiento</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span> Sin datos</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
