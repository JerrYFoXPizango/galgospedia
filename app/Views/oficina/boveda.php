<?php
$pageTitle = 'Bóveda de Documentos — ' . htmlspecialchars($club['name']);
require APP_PATH . '/Views/layout/header.php';

$categoryLabel = [
    'resolucion_coto' => 'Resolución Coto',
    'seguro'          => 'Seguro',
    'acta'            => 'Acta',
    'permiso'         => 'Permiso',
    'federativo'      => 'Federativo',
    'otro'            => 'Otro',
];
$categoryColor = [
    'resolucion_coto' => 'bg-blue-100 text-blue-700',
    'seguro'          => 'bg-purple-100 text-purple-700',
    'acta'            => 'bg-green-100 text-green-700',
    'permiso'         => 'bg-yellow-100 text-yellow-700',
    'federativo'      => 'bg-indigo-100 text-indigo-700',
    'otro'            => 'bg-gray-100 text-gray-500',
];

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 0)    . ' KB';
    return $bytes . ' B';
}
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <a href="/oficina/mi-club" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Mi Club</a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">Bóveda de Documentos</h1>
        <a href="/oficina/mi-club/documentos/subir" class="btn-gold">+ Subir documento</a>
    </div>

    <?php \Helpers\Flash::render() ?>

    <?php if (empty($docs)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">No hay documentos.</p>
            <p class="text-xs mt-1">
                <a href="/oficina/mi-club/documentos/subir" class="text-galgo-red hover:underline">Subir el primero</a>
            </p>
        </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Documento</th>
                    <th class="px-4 py-3 text-left">Categoría</th>
                    <th class="px-4 py-3 text-left">Vence</th>
                    <th class="px-4 py-3 text-left">Tamaño</th>
                    <th class="px-4 py-3 text-left">Subido</th>
                    <th class="px-4 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($docs as $doc): ?>
                <?php
                $expCss = '';
                $expText = '—';
                if ($doc['expires_at']) {
                    $exp  = new \DateTime($doc['expires_at']);
                    $now  = new \DateTime();
                    $diff = (int) $now->diff($exp)->days * ($exp >= $now ? 1 : -1);
                    $expText = $exp->format('d/m/Y');
                    if ($diff < 0)       $expCss = 'text-red-600 font-semibold';
                    elseif ($diff <= 30) $expCss = 'text-yellow-600 font-semibold';
                    else                 $expCss = 'text-green-600';
                }
                $cat   = $doc['category'] ?? 'otro';
                $badge = $categoryColor[$cat]  ?? 'bg-gray-100 text-gray-500';
                $label = $categoryLabel[$cat]  ?? $cat;
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-medium"><?= htmlspecialchars($doc['title']) ?></span>
                        <?php if ($doc['notes']): ?>
                            <div class="text-xs text-gray-400 truncate max-w-xs"><?= htmlspecialchars($doc['notes']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs <?= $expCss ?>"><?= $expText ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        <?= $doc['file_size'] ? formatBytes((int) $doc['file_size']) : '—' ?>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        <?= (new \DateTime($doc['created_at']))->format('d/m/Y') ?>
                        <?php if ($doc['uploader_username']): ?>
                            <div>@<?= htmlspecialchars($doc['uploader_username']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <a href="/oficina/mi-club/documentos/<?= $doc['id'] ?>/descargar"
                               class="btn-outline text-xs py-1 px-2">Descargar</a>
                            <form method="POST" action="/oficina/mi-club/documentos/<?= $doc['id'] ?>/eliminar"
                                  onsubmit="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($doc['title'])) ?>»? Esta acción no se puede deshacer.')">
                                <?= \Helpers\Csrf::field() ?>
                                <button class="btn-outline text-xs py-1 px-2 text-red-600 border-red-200">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
