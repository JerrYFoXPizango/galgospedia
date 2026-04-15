<?php $pageTitle = 'Admin — Clubs'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">
            Clubs y Cotos
            <span class="text-gray-400 font-normal text-lg">(<?= $total ?>)</span>
        </h1>
        <a href="/admin" class="btn-outline text-sm">← Dashboard</a>
    </div>

    <?php if (empty($clubs)): ?>
        <p class="text-gray-400 text-center py-16">No hay clubs registrados.</p>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nombre</th>
                    <th class="px-4 py-3 text-left">Tipo</th>
                    <th class="px-4 py-3 text-left">Provincia</th>
                    <th class="px-4 py-3 text-left">Solicitado por</th>
                    <th class="px-4 py-3 text-left">Presidente</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($clubs as $club): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">
                        <?= htmlspecialchars($club['name']) ?>
                        <?php if ($club['is_verified']): ?>
                            <span class="text-galgo-gold text-xs" title="Verificado">✓</span>
                        <?php endif; ?>
                        <?php if ($club['contact_email']): ?>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($club['contact_email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 capitalize text-gray-500"><?= htmlspecialchars($club['type']) ?></td>
                    <td class="px-4 py-3 text-gray-500">
                        <?= htmlspecialchars(implode(', ', array_filter([
                            $club['province'] ?? null,
                            $club['autonomous_community'] ?? null,
                        ]))) ?: '—' ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        <?= htmlspecialchars($club['created_by_username'] ?? '—') ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        <?= htmlspecialchars($club['president_username'] ?? '—') ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $badge = match($club['status']) {
                            'active'    => 'bg-green-100 text-green-700',
                            'pending'   => 'bg-yellow-100 text-yellow-700',
                            'suspended' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                        $label = match($club['status']) {
                            'active'    => 'Activo',
                            'pending'   => 'Pendiente',
                            'suspended' => 'Suspendido',
                            default     => $club['status'],
                        };
                        ?>
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2 flex-wrap">
                            <?php if ($club['status'] === 'pending'): ?>
                                <form method="POST" action="/admin/clubs/<?= $club['id'] ?>/aprobar">
                                    <?= \Helpers\Csrf::field() ?>
                                    <button class="btn-gold text-xs py-1 px-2">Aprobar</button>
                                </form>
                                <form method="POST" action="/admin/clubs/<?= $club['id'] ?>/suspender"
                                      onsubmit="return confirm('¿Rechazar este club?')">
                                    <?= \Helpers\Csrf::field() ?>
                                    <button class="btn-outline text-xs py-1 px-2 text-red-600 border-red-200">Rechazar</button>
                                </form>
                            <?php elseif ($club['status'] === 'active'): ?>
                                <form method="POST" action="/admin/clubs/<?= $club['id'] ?>/suspender"
                                      onsubmit="return confirm('¿Suspender este club?')">
                                    <?= \Helpers\Csrf::field() ?>
                                    <button class="btn-outline text-xs py-1 px-2 text-red-600 border-red-200">Suspender</button>
                                </form>
                            <?php elseif ($club['status'] === 'suspended'): ?>
                                <form method="POST" action="/admin/clubs/<?= $club['id'] ?>/aprobar">
                                    <?= \Helpers\Csrf::field() ?>
                                    <button class="btn-outline text-xs py-1 px-2 text-green-600 border-green-200">Reactivar</button>
                                </form>
                            <?php endif; ?>
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
