<?php
$pageTitle = 'Mi Billetera de Documentos';
require APP_PATH . '/Views/layout/header.php';

// ── Helpers ──────────────────────────────────────────────────

/** Devuelve clase CSS del badge de caducidad */
function expiryBadge(?string $date): string
{
    if (!$date) return '';
    $diff = (new DateTime($date))->diff(new DateTime())->days;
    $past = (new DateTime($date)) < new DateTime();
    if ($past)       return '<span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Caducado</span>';
    if ($diff <= 30) return '<span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">Caduca pronto</span>';
    return '<span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Vigente</span>';
}

/** Icono según tipo MIME */
function mimeIcon(string $mime): string
{
    if ($mime === 'application/pdf')    return '<svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8 17h8v-1H8v1zm0-3h8v-1H8v1zm0-3h5v-1H8v1z"/></svg>';
    if (str_starts_with($mime,'image/')) return '<svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2zM8.5 13.5l2.5 3 3.5-4.5 4.5 6H5l3.5-4.5z"/></svg>';
    return '<svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/></svg>';
}

/** Formatea bytes a KB/MB */
function fmtSize(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1024) . ' KB';
}
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">

    <!-- Cabecera -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-display font-bold flex items-center gap-2">
                <svg class="w-7 h-7 text-galgo-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Mi Billetera de Documentos
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Documentación privada — solo tú puedes verla</p>
        </div>
        <a href="/mi-billetera/subir" class="btn-gold text-sm">+ Añadir documento</a>
    </div>

    <!-- Barra de almacenamiento -->
    <?php
    $pct = min(100, round($usedBytes / $maxBytes * 100));
    $barColor = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-400' : 'bg-green-500');
    ?>
    <div class="card mb-8 py-3 px-5">
        <div class="flex items-center justify-between text-sm mb-1">
            <span class="text-gray-500">Almacenamiento usado</span>
            <span class="font-medium"><?= fmtSize($usedBytes) ?> / <?= fmtSize($maxBytes) ?></span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="<?= $barColor ?> h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
        </div>
    </div>

    <?php if ($total === 0): ?>
        <!-- Estado vacío -->
        <div class="card text-center py-16">
            <svg class="w-14 h-14 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-500 mb-2">Billetera vacía</h3>
            <p class="text-sm text-gray-400 mb-5">Guarda aquí tus cartillas veterinarias, licencias y certificados para tenerlos a mano en el campo.</p>
            <a href="/mi-billetera/subir" class="btn-gold">Añadir primer documento</a>
        </div>

    <?php else: ?>

        <!-- Documentos agrupados por tipo -->
        <?php foreach ($byType as $typeKey => $group): ?>
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">
                <?= htmlspecialchars($group['label']) ?>
                <span class="ml-1 text-gray-300">(<?= count($group['docs']) ?>)</span>
            </h2>

            <div class="grid gap-3">
                <?php foreach ($group['docs'] as $doc): ?>
                <?php
                    $expDate = $doc['expires_at'] ?? null;
                    $isPdf   = $doc['mime_type'] === 'application/pdf';
                    $isImg   = str_starts_with($doc['mime_type'] ?? '', 'image/');
                    $target  = ($isPdf || $isImg) ? ' target="_blank"' : '';
                ?>
                <div class="card flex items-start gap-4 py-4 px-5">
                    <!-- Icono tipo archivo -->
                    <div class="flex-shrink-0 mt-0.5">
                        <?= mimeIcon($doc['mime_type'] ?? '') ?>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1 mb-0.5">
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($doc['title']) ?></span>
                            <?php if ($expDate): echo expiryBadge($expDate); endif; ?>
                        </div>

                        <div class="text-xs text-gray-400 flex flex-wrap gap-x-3 gap-y-0.5">
                            <?php if ($doc['dog_name']): ?>
                                <span class="text-galgo-gold font-medium">
                                    🐕 <?= htmlspecialchars($doc['dog_name']) ?>
                                </span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($doc['original_name'] ?? '') ?></span>
                            <?php if ($doc['file_size']): ?>
                                <span><?= fmtSize((int)$doc['file_size']) ?></span>
                            <?php endif; ?>
                            <?php if ($expDate): ?>
                                <span>Caduca: <?= htmlspecialchars(date('d/m/Y', strtotime($expDate))) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($doc['notes']): ?>
                            <p class="text-xs text-gray-400 mt-1 italic"><?= htmlspecialchars($doc['notes']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Acciones -->
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <a href="/mi-billetera/<?= (int)$doc['id'] ?>/ver"<?= $target ?>
                           class="btn-outline text-xs py-1 px-3">
                            <?= $isPdf ? 'Abrir PDF' : ($isImg ? 'Ver imagen' : 'Descargar') ?>
                        </a>
                        <form method="POST" action="/mi-billetera/<?= (int)$doc['id'] ?>/eliminar"
                              onsubmit="return confirm('¿Eliminar este documento?')">
                            <?= \Helpers\Csrf::field() ?>
                            <button class="btn-danger text-xs py-1 px-3">Eliminar</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
