<?php
$pageTitle = 'Patrocinadores — Admin';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="/admin" class="w-9 h-9 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-display font-bold">Patrocinadores</h1>
                <p class="text-xs text-gray-400">Gestiona los logos del carrusel en la landing page</p>
            </div>
        </div>
        <a href="/admin/patrocinadores/nuevo" class="btn-gold text-sm">+ Añadir</a>
    </div>

    <?php \Helpers\Flash::render() ?>

    <?php if (empty($sponsors)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="text-4xl mb-3">🤝</div>
            <p class="font-semibold text-gray-700">Sin patrocinadores todavía</p>
            <a href="/admin/patrocinadores/nuevo" class="mt-4 inline-block btn-gold text-sm">+ Añadir primero</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">#</th>
                        <th class="px-4 py-3 text-left">Logo</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Web</th>
                        <th class="px-4 py-3 text-center">Orden</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($sponsors as $s): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400"><?= $s['id'] ?></td>
                        <td class="px-4 py-3">
                            <div class="w-20 h-12 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center border border-gray-200">
                                <img src="<?= htmlspecialchars($s['logo_path']) ?>"
                                     alt="<?= htmlspecialchars($s['name']) ?>"
                                     class="max-w-full max-h-full object-contain p-1">
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($s['name']) ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <?php if ($s['website_url']): ?>
                                <a href="<?= htmlspecialchars($s['website_url']) ?>" target="_blank" rel="noopener"
                                   class="text-blue-500 hover:underline truncate max-w-xs block">
                                    <?= htmlspecialchars(parse_url($s['website_url'], PHP_URL_HOST) ?: $s['website_url']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-300">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500"><?= $s['sort_order'] ?></td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="/admin/patrocinadores/<?= $s['id'] ?>/toggle" class="inline">
                                <?= \Helpers\Csrf::field() ?>
                                <button type="submit"
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium transition-colors <?= $s['active'] ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $s['active'] ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                                    <?= $s['active'] ? 'Activo' : 'Inactivo' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="/admin/patrocinadores/<?= $s['id'] ?>/editar"
                                   class="text-xs text-blue-500 hover:underline">Editar</a>
                                <form method="POST" action="/admin/patrocinadores/<?= $s['id'] ?>/eliminar"
                                      onsubmit="return confirm('¿Eliminar este patrocinador?')">
                                    <?= \Helpers\Csrf::field() ?>
                                    <button type="submit" class="text-xs text-red-400 hover:underline">Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-400 mt-4 text-center">
            Los patrocinadores activos aparecen en el carrusel de la landing page ordenados por el campo "Orden" (menor número = primero).
        </p>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
