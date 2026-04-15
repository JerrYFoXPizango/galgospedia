<?php $pageTitle = 'Admin — Galgos'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">Galgos <span class="text-gray-400 font-normal text-lg">(<?= $total ?>)</span></h1>
        <a href="/admin" class="btn-outline text-sm">← Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Galgo</th>
                    <th class="px-4 py-3 text-left">Sexo</th>
                    <th class="px-4 py-3 text-left">Registro</th>
                    <th class="px-4 py-3 text-left">Rol</th>
                    <th class="px-4 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($dogs as $dog): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php if ($dog['photo_thumb']): ?>
                                <img src="<?= \Helpers\Asset::url($dog['photo_thumb']) ?>" class="w-10 h-10 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                    <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-7 h-7 object-contain opacity-40">
                                </div>
                            <?php endif; ?>
                            <a href="/galgos/<?= htmlspecialchars($dog['slug']) ?>" class="font-medium hover:text-galgo-red">
                                <?= htmlspecialchars($dog['name']) ?>
                            </a>
                        </div>
                    </td>
                    <td class="px-4 py-3 capitalize"><?= htmlspecialchars($dog['gender']) ?></td>
                    <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($dog['registration_number'] ?? '—') ?></td>
                    <td class="px-4 py-3">
                        <?php if ($dog['stallion_id']): ?><span class="badge-gold text-xs mr-1">S</span><?php endif; ?>
                        <?php if ($dog['broodmare_id']): ?><span class="badge-red text-xs">R</span><?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <form method="POST" action="/admin/sementales/<?= $dog['id'] ?>">
                                <?= \Helpers\Csrf::field() ?>
                                <button class="text-xs btn-outline py-1">
                                    <?= $dog['stallion_id'] ? 'Quitar semental' : 'Hacer semental' ?>
                                </button>
                            </form>
                            <form method="POST" action="/admin/reproductoras/<?= $dog['id'] ?>">
                                <?= \Helpers\Csrf::field() ?>
                                <button class="text-xs btn-outline py-1">
                                    <?= $dog['broodmare_id'] ? 'Quitar reproductora' : 'Hacer reproductora' ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
