<?php
$pageTitle = 'Calendario — ' . htmlspecialchars($club['name']);
require APP_PATH . '/Views/layout/header.php';

$typeLabel = [
    'tirada'   => 'Tirada',
    'carrera'  => 'Carrera',
    'veda'     => 'Veda',
    'reunion'  => 'Reunión',
    'otro'     => 'Otro',
];
$typeColor = [
    'tirada'   => 'bg-red-100 text-red-700',
    'carrera'  => 'bg-green-100 text-green-700',
    'veda'     => 'bg-orange-100 text-orange-700',
    'reunion'  => 'bg-blue-100 text-blue-700',
    'otro'     => 'bg-gray-100 text-gray-500',
];

function fmtDatetime(string $dt): string {
    $d = new \DateTime($dt);
    return $d->format('d/m/Y') . ' · ' . $d->format('H:i');
}
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">
    <a href="/oficina/mi-club" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Mi Club</a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">Calendario de Eventos</h1>
        <a href="/oficina/mi-club/eventos/nuevo" class="btn-gold">+ Nuevo evento</a>
    </div>

    <?php \Helpers\Flash::render() ?>

    <!-- ── Próximos ──────────────────────────────────────────── -->
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Próximos</h2>

    <?php if (empty($upcoming)): ?>
        <div class="bg-white rounded-xl border border-dashed border-gray-200 text-center py-10 text-gray-400 text-sm mb-8">
            No hay eventos próximos.
            <a href="/oficina/mi-club/eventos/nuevo" class="text-galgo-red hover:underline ml-1">Crear uno</a>.
        </div>
    <?php else: ?>
    <div class="space-y-3 mb-8">
        <?php foreach ($upcoming as $ev): ?>
        <?php
        $type  = $ev['type'] ?? 'otro';
        $badge = $typeColor[$type]  ?? 'bg-gray-100 text-gray-500';
        $label = $typeLabel[$type]  ?? $type;
        ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4 items-start">
            <!-- Date block -->
            <?php $d = new \DateTime($ev['starts_at']); ?>
            <div class="shrink-0 text-center w-12">
                <div class="text-2xl font-bold text-galgo-dark leading-none"><?= $d->format('d') ?></div>
                <div class="text-xs text-gray-400 uppercase"><?= $d->format('M') ?></div>
                <div class="text-xs text-gray-400"><?= $d->format('Y') ?></div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $badge ?>"><?= $label ?></span>
                    <span class="font-semibold text-sm"><?= htmlspecialchars($ev['title']) ?></span>
                </div>
                <div class="text-xs text-gray-500 flex flex-wrap gap-3">
                    <span><?= $d->format('H:i') ?> h
                        <?php if ($ev['ends_at']): ?>
                            &mdash; <?= (new \DateTime($ev['ends_at']))->format('H:i') ?> h
                        <?php endif; ?>
                    </span>
                    <?php if ($ev['location']): ?>
                        <span>📍 <?= htmlspecialchars($ev['location']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($ev['description']): ?>
                    <p class="text-xs text-gray-400 mt-1 line-clamp-2"><?= htmlspecialchars($ev['description']) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex gap-1 shrink-0">
                <a href="/oficina/mi-club/eventos/<?= $ev['id'] ?>/editar"
                   class="btn-outline text-xs py-1 px-2">Editar</a>
                <form method="POST" action="/oficina/mi-club/eventos/<?= $ev['id'] ?>/eliminar"
                      onsubmit="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($ev['title'])) ?>»?')">
                    <?= \Helpers\Csrf::field() ?>
                    <button class="btn-outline text-xs py-1 px-2 text-red-600 border-red-200">Eliminar</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Pasados ───────────────────────────────────────────── -->
    <?php if (!empty($past)): ?>
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Historial</h2>
    <div class="space-y-2">
        <?php foreach ($past as $ev): ?>
        <?php
        $type  = $ev['type'] ?? 'otro';
        $badge = $typeColor[$type]  ?? 'bg-gray-100 text-gray-500';
        $label = $typeLabel[$type]  ?? $type;
        $d     = new \DateTime($ev['starts_at']);
        ?>
        <div class="bg-white rounded-xl border border-gray-100 p-3 flex gap-3 items-center opacity-70 hover:opacity-100 transition-opacity">
            <span class="text-xs px-2 py-0.5 rounded-full <?= $badge ?>"><?= $label ?></span>
            <span class="text-sm font-medium flex-1"><?= htmlspecialchars($ev['title']) ?></span>
            <?php if ($ev['location']): ?>
                <span class="text-xs text-gray-400 hidden sm:block"><?= htmlspecialchars($ev['location']) ?></span>
            <?php endif; ?>
            <span class="text-xs text-gray-400"><?= $d->format('d/m/Y') ?></span>
            <div class="flex gap-1">
                <a href="/oficina/mi-club/eventos/<?= $ev['id'] ?>/editar"
                   class="btn-outline text-xs py-0.5 px-1.5">Editar</a>
                <form method="POST" action="/oficina/mi-club/eventos/<?= $ev['id'] ?>/eliminar"
                      onsubmit="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($ev['title'])) ?>»?')">
                    <?= \Helpers\Csrf::field() ?>
                    <button class="btn-outline text-xs py-0.5 px-1.5 text-red-600 border-red-200">✕</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
