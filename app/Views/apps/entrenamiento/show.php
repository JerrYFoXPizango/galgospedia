<?php
$pageTitle = htmlspecialchars($dog['name']) . ' — Diario de Entrenamiento';
require APP_PATH . '/Views/layout/header.php';
use Models\Training;

// Calcular max para escalar barras del gráfico
$maxWeekKm = (float)$config['max_weekly_km'];
$chartMax  = max($maxWeekKm, ...array_column($weeks, 'total_km'), 1);
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="/apps/entrenamiento" class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex items-center gap-3">
                <?php if ($dog['photo_thumb']): ?>
                    <img src="<?= \Helpers\Asset::url($dog['photo_thumb']) ?>"
                         alt="<?= htmlspecialchars($dog['name']) ?>"
                         class="w-10 h-10 rounded-xl object-contain bg-gray-100">
                <?php endif; ?>
                <div>
                    <h1 class="text-xl font-display font-bold leading-tight"><?= htmlspecialchars($dog['name']) ?></h1>
                    <a href="/galgos/<?= $dog['slug'] ?>" class="text-xs text-gray-400 hover:text-galgo-red">Ver perfil →</a>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="/apps/entrenamiento/<?= $dog['slug'] ?>/configurar" class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600" title="Configurar límites">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
            <a href="/apps/entrenamiento/<?= $dog['slug'] ?>/nuevo" class="btn-gold text-sm">+ Sesión</a>
        </div>
    </div>

    <?php \Helpers\Flash::render() ?>

    <!-- Semáforo de sobreentrenamiento -->
    <?php
    $otStatus = $overtrain['status'];
    $otBg = ['green'=>'bg-green-50 border-green-100','orange'=>'bg-orange-50 border-orange-100','red'=>'bg-red-50 border-red-100','gray'=>'bg-gray-50 border-gray-100'][$otStatus] ?? 'bg-gray-50 border-gray-100';
    $otDot = ['green'=>'bg-green-400','orange'=>'bg-orange-400','red'=>'bg-red-500','gray'=>'bg-gray-300'][$otStatus] ?? 'bg-gray-300';
    $otText = ['green'=>'text-green-700','orange'=>'text-orange-700','red'=>'text-red-700','gray'=>'text-gray-500'][$otStatus] ?? 'text-gray-500';
    ?>
    <div class="flex items-center gap-3 <?= $otBg ?> border rounded-xl px-4 py-3 mb-5">
        <div class="w-3 h-3 rounded-full <?= $otDot ?> ring-2 ring-white shadow flex-shrink-0"></div>
        <div class="flex-1">
            <span class="text-sm font-semibold <?= $otText ?>"><?= htmlspecialchars($overtrain['label']) ?></span>
            <span class="text-sm <?= $otText ?> opacity-80 ml-1">— <?= htmlspecialchars($overtrain['detail']) ?></span>
        </div>
        <?php if ($daysAfterComp !== null): ?>
            <?php $compAlert = $daysAfterComp < (int)$config['rest_days_after_competition']; ?>
            <span class="text-xs px-2 py-1 rounded-full <?= $compAlert ? 'bg-red-100 text-red-600' : 'bg-blue-50 text-blue-600' ?> font-medium flex-shrink-0">
                🏆 <?= $daysAfterComp ?>d desde última comp.<?= $compAlert ? ' ⚠️' : '' ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Stats del cazador (mes actual) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 text-center">
            <p class="text-2xl font-bold text-galgo-dark"><?= $monthStats['sessions'] ?: '0' ?></p>
            <p class="text-xs text-gray-400 mt-0.5">Sesiones (mes)</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 text-center">
            <p class="text-2xl font-bold text-galgo-dark"><?= number_format($monthStats['total_m'] / 1000, 1) ?></p>
            <p class="text-xs text-gray-400 mt-0.5">Km este mes</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 text-center">
            <?php $h = floor($monthStats['total_min'] / 60); $m = $monthStats['total_min'] % 60; ?>
            <p class="text-2xl font-bold text-galgo-dark"><?= $h > 0 ? "{$h}h{$m}'" : $monthStats['total_min'] . "'" ?></p>
            <p class="text-xs text-gray-400 mt-0.5">Tiempo (mes)</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 text-center">
            <?php $bd = Training::formatDistance($monthStats['best_distance_m'] ?: null); ?>
            <p class="text-2xl font-bold text-galgo-dark"><?= $bd ?></p>
            <p class="text-xs text-gray-400 mt-0.5">Mejor distancia</p>
        </div>
    </div>

    <!-- Info extra del cazador -->
    <?php if ($favTerrain || $lastDays !== null): ?>
    <div class="flex flex-wrap gap-2 mb-6">
        <?php if ($lastDays !== null): ?>
            <span class="inline-flex items-center gap-1.5 text-xs bg-white border border-gray-100 rounded-full px-3 py-1.5 text-gray-500">
                📅 <?= $lastDays === 0 ? 'Entrenado hoy' : "Última sesión hace {$lastDays} día" . ($lastDays > 1 ? 's' : '') ?>
            </span>
        <?php endif; ?>
        <?php if ($favTerrain): ?>
            <span class="inline-flex items-center gap-1.5 text-xs bg-white border border-gray-100 rounded-full px-3 py-1.5 text-gray-500">
                🌍 Terreno favorito: <?= Training::terrainLabel($favTerrain) ?>
            </span>
        <?php endif; ?>
        <span class="inline-flex items-center gap-1.5 text-xs bg-white border border-gray-100 rounded-full px-3 py-1.5 text-gray-500">
            ⚙️ Límite semanal: <?= number_format($config['max_weekly_km'], 1) ?> km · Max <?= $config['max_consecutive_high'] ?> días alta intensidad
        </span>
    </div>
    <?php endif; ?>

    <!-- Gráfico de carga — últimas 6 semanas -->
    <?php if (!empty($weeks) && array_sum(array_column($weeks, 'sessions')) > 0): ?>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-700">Carga — últimas 6 semanas</p>
            <span class="text-xs text-gray-400">Límite: <?= number_format($maxWeekKm, 1) ?> km/sem</span>
        </div>

        <!-- Barras -->
        <div class="flex items-end gap-2 h-28">
            <?php foreach ($weeks as $w):
                $barH = $chartMax > 0 ? max(2, round($w['total_km'] / $chartMax * 100)) : 2;
                $pct  = $maxWeekKm > 0 ? $w['total_km'] / $maxWeekKm : 0;
                $barColor = $pct >= 1.0 ? 'bg-red-400' : ($pct >= 0.85 ? 'bg-orange-400' : 'bg-blue-400');
                $hasComp  = $w['competition_count'] > 0;
            ?>
            <div class="flex-1 flex flex-col items-center gap-1 group relative">
                <!-- Tooltip -->
                <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 z-10 hidden group-hover:block w-36 bg-gray-800 text-white text-xs rounded-lg p-2 shadow-lg pointer-events-none">
                    <p class="font-semibold mb-1"><?= $w['label'] ?></p>
                    <p>📏 <?= number_format($w['total_km'], 1) ?> km</p>
                    <p>🏃 <?= $w['sessions'] ?> sesión<?= $w['sessions'] != 1 ? 'es' : '' ?></p>
                    <?php if ($w['high_count']): ?><p>🔴 Alta: <?= $w['high_count'] ?></p><?php endif; ?>
                    <?php if ($w['medium_count']): ?><p>🟡 Media: <?= $w['medium_count'] ?></p><?php endif; ?>
                    <?php if ($w['low_count']): ?><p>🟢 Baja: <?= $w['low_count'] ?></p><?php endif; ?>
                    <?php if ($hasComp): ?><p>🏆 Competición</p><?php endif; ?>
                </div>

                <?php if ($hasComp): ?>
                    <span class="text-xs">🏆</span>
                <?php endif; ?>

                <!-- Barra -->
                <div class="w-full rounded-t-lg <?= $barColor ?> transition-all duration-300" style="height:<?= $barH ?>%"></div>

                <!-- Línea de límite (marcador) -->
                <p class="text-xs text-gray-400 mt-1 truncate w-full text-center"><?= $w['label'] ?></p>
                <?php if ($w['sessions'] > 0): ?>
                    <p class="text-xs font-medium text-gray-600"><?= number_format($w['total_km'], 1) ?>k</p>
                <?php else: ?>
                    <p class="text-xs text-gray-300">—</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Línea de límite visual -->
        <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
            <div class="flex-1 border-t border-dashed border-gray-200"></div>
            <span>Intensidad:
                <span class="text-green-600 font-medium">Baja</span> ·
                <span class="text-yellow-600 font-medium">Media</span> ·
                <span class="text-red-600 font-medium">Alta</span>
            </span>
        </div>

        <!-- Distribución de intensidad del total -->
        <?php
        $totalSessions = array_sum(array_column($weeks, 'sessions'));
        $totalHigh   = array_sum(array_column($weeks, 'high_count'));
        $totalMed    = array_sum(array_column($weeks, 'medium_count'));
        $totalLow    = array_sum(array_column($weeks, 'low_count'));
        if ($totalSessions > 0):
            $pctH = round($totalHigh / $totalSessions * 100);
            $pctM = round($totalMed  / $totalSessions * 100);
            $pctL = 100 - $pctH - $pctM;
        ?>
        <div class="mt-3">
            <p class="text-xs text-gray-400 mb-1">Distribución de intensidad (6 semanas)</p>
            <div class="flex h-2 rounded-full overflow-hidden gap-0.5">
                <?php if ($pctH): ?><div class="bg-red-400 transition-all" style="width:<?= $pctH ?>%"></div><?php endif; ?>
                <?php if ($pctM): ?><div class="bg-yellow-400 transition-all" style="width:<?= $pctM ?>%"></div><?php endif; ?>
                <?php if ($pctL): ?><div class="bg-green-400 transition-all" style="width:<?= $pctL ?>%"></div><?php endif; ?>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>🔴 <?= $pctH ?>% Alta</span>
                <span>🟡 <?= $pctM ?>% Media</span>
                <span>🟢 <?= $pctL ?>% Baja</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Historial de sesiones -->
    <?php if (empty($sessions)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="text-4xl mb-3">📋</div>
            <p class="font-semibold text-gray-700">Sin sesiones registradas</p>
            <p class="text-sm text-gray-400 mt-1">Añade la primera sesión de entrenamiento.</p>
            <a href="/apps/entrenamiento/<?= $dog['slug'] ?>/nuevo" class="mt-4 inline-block btn-gold text-sm">+ Añadir sesión</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Historial completo</p>
                <p class="text-xs text-gray-400"><?= count($sessions) ?> sesiones</p>
            </div>
            <?php foreach ($sessions as $i => $s):
                $isLast = $i === count($sessions) - 1;
                $dateStr = (new DateTime($s['date']))->format('d/m/Y');
            ?>
            <div class="<?= $isLast ? '' : 'border-b border-gray-50' ?> px-5 py-4 hover:bg-gray-50 transition-colors group">
                <div class="flex items-start gap-3">
                    <!-- Icono tipo -->
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0 bg-gray-50 group-hover:bg-white">
                        <?= Training::typeIcon($s['type']) ?>
                    </div>

                    <!-- Contenido -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-0.5">
                            <span class="text-sm font-semibold text-gray-900"><?= Training::typeLabel($s['type']) ?></span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= Training::intensityClass($s['intensity']) ?>">
                                <?= Training::intensityLabel($s['intensity']) ?>
                            </span>
                            <?php if ($s['dog_condition']): ?>
                                <span class="text-xs"><?= Training::conditionIcon($s['dog_condition']) ?> <?= Training::conditionLabel($s['dog_condition']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-400 mt-1">
                            <span>📅 <?= $dateStr ?></span>
                            <?php if ($s['distance_m']): ?>
                                <span>📏 <?= Training::formatDistance($s['distance_m']) ?></span>
                            <?php endif; ?>
                            <?php if ($s['duration_min']): ?>
                                <?php $h = floor($s['duration_min']/60); $m = $s['duration_min']%60; ?>
                                <span>⏱️ <?= $h > 0 ? "{$h}h {$m}'" : $s['duration_min'] . "'" ?></span>
                            <?php endif; ?>
                            <?php if ($s['terrain']): ?>
                                <span>🌍 <?= Training::terrainLabel($s['terrain']) ?></span>
                            <?php endif; ?>
                            <?php if ($s['temperature_c'] !== null && $s['temperature_c'] !== ''): ?>
                                <span>🌡️ <?= $s['temperature_c'] ?>°C</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($s['notes']): ?>
                            <p class="text-xs text-gray-400 mt-1 italic line-clamp-1"><?= htmlspecialchars($s['notes']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="/apps/entrenamiento/sesion/<?= $s['id'] ?>/editar"
                           class="text-xs text-blue-500 hover:underline">Editar</a>
                        <form method="POST" action="/apps/entrenamiento/sesion/<?= $s['id'] ?>/eliminar"
                              onsubmit="return confirm('¿Eliminar esta sesión?')">
                            <?= \Helpers\Csrf::field() ?>
                            <button type="submit" class="text-xs text-red-400 hover:underline">Borrar</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
