<?php
$pageTitle = 'Alertas de licencia — Admin';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-5xl">
    <a href="/admin" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Admin</a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">Alertas de licencia</h1>
    </div>

    <?php \Helpers\Flash::render() ?>

    <!-- ── Pendientes ────────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <?php if (!empty($pending)): ?>
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                <?php endif; ?>
                <h2 class="font-semibold">Pendientes de envío
                    <span class="ml-1 text-sm font-normal text-gray-400">(<?= count($pending) ?>)</span>
                </h2>
            </div>

            <form method="POST" action="/admin/alertas/enviar" class="flex gap-2 items-center">
                <?= \Helpers\Csrf::field() ?>
                <label class="flex items-center gap-1.5 text-sm text-gray-500 cursor-pointer">
                    <input type="checkbox" name="dry_run" value="1" class="rounded accent-galgo-gold">
                    Simulación
                </label>
                <?php if (!empty($pending)): ?>
                    <button type="submit" class="btn-gold text-sm"
                            onclick="return confirm('¿Enviar alertas ahora?')">
                        Enviar alertas
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn-outline text-sm text-gray-400" disabled>
                        Sin pendientes
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($pending)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">
                No hay alertas pendientes. Todos los socios han sido notificados.
            </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Club</th>
                        <th class="px-4 py-3 text-left">Socio</th>
                        <th class="px-4 py-3 text-left">Licencia</th>
                        <th class="px-4 py-3 text-left">Vence</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-left">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($pending as $p): ?>
                    <?php
                    $isExpired = $p['alert_type'] === 'expired';
                    $days      = (int) $p['days_left'];
                    $expText   = $isExpired
                        ? 'Caducó hace ' . abs($days) . 'd'
                        : 'Caduca en '  . $days . 'd';
                    $expCss    = $isExpired ? 'text-red-600 font-semibold' : 'text-yellow-600 font-semibold';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs text-gray-500"><?= htmlspecialchars($p['club_name']) ?></td>
                        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($p['member_name']) ?></td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <?= htmlspecialchars($p['license_number'] ?? '—') ?>
                            <?php if ($p['license_type']): ?>
                                <div class="text-gray-400"><?= htmlspecialchars($p['license_type']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <?= (new \DateTime($p['license_expires_at']))->format('d/m/Y') ?>
                        </td>
                        <td class="px-4 py-3 text-xs <?= $expCss ?>"><?= $expText ?></td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            <?php if ($p['president_email']): ?>
                                <div>👤 <?= htmlspecialchars($p['president_email']) ?></div>
                            <?php endif; ?>
                            <?php if ($p['member_email']): ?>
                                <div>✉ <?= htmlspecialchars($p['member_email']) ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Historial ─────────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold">Historial de envíos
                <span class="ml-1 text-sm font-normal text-gray-400">(últimos 60)</span>
            </h2>
        </div>

        <?php if (empty($history)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">No se han enviado alertas todavía.</div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Enviado</th>
                        <th class="px-4 py-3 text-left">Club</th>
                        <th class="px-4 py-3 text-left">Socio</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-left">Vencimiento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($history as $h): ?>
                    <?php
                    $typeCss   = $h['type'] === 'expired' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700';
                    $typeLabel = $h['type'] === 'expired' ? 'Caducada' : 'Próxima';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-xs text-gray-400">
                            <?= (new \DateTime($h['sent_at']))->format('d/m/Y H:i') ?>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-gray-500"><?= htmlspecialchars($h['club_name']) ?></td>
                        <td class="px-4 py-2.5 font-medium text-sm"><?= htmlspecialchars($h['member_name']) ?></td>
                        <td class="px-4 py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full <?= $typeCss ?>"><?= $typeLabel ?></span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-gray-500">
                            <?= $h['license_expires_at']
                                ? (new \DateTime($h['license_expires_at']))->format('d/m/Y')
                                : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
