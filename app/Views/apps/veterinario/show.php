<?php
$pageTitle = htmlspecialchars($dog['name']) . ' — Historial Veterinario';
require APP_PATH . '/Views/layout/header.php';
use Models\VetRecord;

$typeGroups = [
    'injury'    => [],
    'vaccine'   => [],
    'deworming' => [],
    'visit'     => [],
    'weight'    => [],
];
foreach ($records as $r) {
    $typeGroups[$r['type']][] = $r;
}
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="/apps/veterinario" class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
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
        <a href="/apps/veterinario/<?= $dog['slug'] ?>/nuevo" class="btn-gold text-sm">+ Añadir</a>
    </div>

    <?php \Helpers\Flash::render() ?>

    <!-- Alertas activas -->
    <?php if (!empty($overdue) || !empty($activeInjuries) || !empty($upcoming)): ?>
    <div class="space-y-2 mb-6">
        <?php foreach ($activeInjuries as $inj): ?>
        <div class="flex items-center gap-3 bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm">
            <span class="text-base">🩹</span>
            <span class="text-red-700 font-medium">Lesión activa:</span>
            <span class="text-red-600"><?= htmlspecialchars($inj['title']) ?></span>
            <span class="text-xs text-red-400 ml-auto"><?= (new DateTime($inj['date']))->format('d/m/Y') ?></span>
        </div>
        <?php endforeach; ?>
        <?php foreach ($overdue as $ov): ?>
        <div class="flex items-center gap-3 bg-yellow-50 border border-yellow-100 rounded-xl px-4 py-3 text-sm">
            <span class="text-base">⚠️</span>
            <span class="text-yellow-700 font-medium">Vencido:</span>
            <span class="text-yellow-600"><?= htmlspecialchars($ov['title']) ?></span>
            <span class="text-xs text-yellow-500 ml-auto">Venció <?= (new DateTime($ov['next_due_date']))->format('d/m/Y') ?></span>
        </div>
        <?php endforeach; ?>
        <?php foreach ($upcoming as $up): ?>
        <div class="flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm">
            <span class="text-base">📅</span>
            <span class="text-blue-700 font-medium">Próxima dosis:</span>
            <span class="text-blue-600"><?= htmlspecialchars($up['title']) ?></span>
            <span class="text-xs text-blue-400 ml-auto"><?= (new DateTime($up['next_due_date']))->format('d/m/Y') ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Historial completo -->
    <?php if (empty($records)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="text-4xl mb-3">📋</div>
            <p class="font-semibold text-gray-700">Sin registros aún</p>
            <p class="text-sm text-gray-400 mt-1">Añade el primer registro veterinario de <?= htmlspecialchars($dog['name']) ?>.</p>
            <a href="/apps/veterinario/<?= $dog['slug'] ?>/nuevo" class="mt-4 inline-block btn-gold text-sm">+ Añadir registro</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <?php foreach ($records as $i => $r):
                $dateStr = (new DateTime($r['date']))->format('d/m/Y');
                $isLast  = $i === count($records) - 1;
            ?>
            <div class="<?= $isLast ? '' : 'border-b border-gray-50' ?> px-5 py-4 hover:bg-gray-50 transition-colors group">
                <div class="flex items-start gap-3">
                    <!-- Icono tipo -->
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0 bg-gray-50 group-hover:bg-white transition-colors">
                        <?= VetRecord::typeIcon($r['type']) ?>
                    </div>

                    <!-- Contenido -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-0.5">
                            <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($r['title']) ?></span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= VetRecord::typeBadgeClass($r['type']) ?>">
                                <?= VetRecord::typeLabel($r['type']) ?>
                            </span>
                            <?php if ($r['severity']): ?>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= VetRecord::severityClass($r['severity']) ?>">
                                    <?= VetRecord::severityLabel($r['severity']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($r['type'] === 'injury' && !$r['resolved_at']): ?>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600 font-medium">Activa</span>
                            <?php elseif ($r['type'] === 'injury' && $r['resolved_at']): ?>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-600 font-medium">Recuperado</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-400">
                            <span>📅 <?= $dateStr ?></span>
                            <?php if ($r['body_part']): ?>
                                <span>🦴 <?= htmlspecialchars($r['body_part']) ?></span>
                            <?php endif; ?>
                            <?php if ($r['next_due_date']): ?>
                                <?php $nd = new DateTime($r['next_due_date']); $isOvr = $nd < new DateTime(); ?>
                                <span class="<?= $isOvr ? 'text-red-400' : 'text-blue-400' ?>">
                                    🔄 Próxima: <?= $nd->format('d/m/Y') ?><?= $isOvr ? ' ⚠️' : '' ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($r['weight_kg']): ?>
                                <span>⚖️ <?= number_format((float)$r['weight_kg'], 1) ?> kg</span>
                            <?php endif; ?>
                            <?php if ($r['resolved_at']): ?>
                                <span class="text-green-500">✓ Alta: <?= (new DateTime($r['resolved_at']))->format('d/m/Y') ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($r['treatment']): ?>
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?= htmlspecialchars($r['treatment']) ?></p>
                        <?php endif; ?>
                        <?php if ($r['notes']): ?>
                            <p class="text-xs text-gray-400 mt-0.5 italic line-clamp-1"><?= htmlspecialchars($r['notes']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="/apps/veterinario/registro/<?= $r['id'] ?>/editar"
                           class="text-xs text-blue-500 hover:underline">Editar</a>
                        <form method="POST" action="/apps/veterinario/registro/<?= $r['id'] ?>/eliminar"
                              onsubmit="return confirm('¿Eliminar este registro?')">
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
