<?php
$pageTitle = 'Mi Club — Oficina Virtual';
require APP_PATH . '/Views/layout/header.php';

$statusBadge = match($club['status']) {
    'active'    => 'bg-green-100 text-green-700',
    'pending'   => 'bg-yellow-100 text-yellow-700',
    'suspended' => 'bg-red-100 text-red-700',
    default     => 'bg-gray-100 text-gray-500',
};
$statusLabel = match($club['status']) {
    'active'    => 'Activo',
    'pending'   => 'Pendiente de aprobación',
    'suspended' => 'Suspendido',
    default     => $club['status'],
};
?>

<div class="container mx-auto px-4 py-8">

    <!-- ── Club header ──────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 flex flex-col sm:flex-row gap-4 items-start">
        <!-- Logo -->
        <div class="shrink-0">
            <?php if ($club['logo_path']): ?>
                <img src="<?= \Helpers\Asset::url($club['logo_path']) ?>"
                     alt="<?= htmlspecialchars($club['name']) ?>"
                     class="w-20 h-20 object-contain rounded-xl border border-gray-100">
            <?php else: ?>
                <div class="w-20 h-20 rounded-xl bg-galgo-dark flex items-center justify-center">
                    <svg class="w-10 h-10 text-galgo-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <h1 class="text-2xl font-display font-bold"><?= htmlspecialchars($club['name']) ?></h1>
                <span class="text-xs px-2 py-0.5 rounded-full <?= $statusBadge ?>"><?= $statusLabel ?></span>
                <?php if ($club['is_verified']): ?>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-galgo-gold/20 text-yellow-800">Verificado ✓</span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-gray-500">
                <?= htmlspecialchars(match($club['type']) {
                    'club'       => 'Club',
                    'coto'       => 'Coto',
                    'federacion' => 'Federación',
                    default      => 'Otro',
                }) ?>
                <?php
                $loc = array_filter([$club['province'] ?? null, $club['autonomous_community'] ?? null, $club['country'] ?? null]);
                if ($loc): ?>
                    · <?= htmlspecialchars(implode(', ', $loc)) ?>
                <?php endif; ?>
            </p>
            <?php if ($club['contact_email'] || $club['contact_phone']): ?>
            <p class="text-xs text-gray-400 mt-1">
                <?= htmlspecialchars(implode('  ·  ', array_filter([$club['contact_email'] ?? null, $club['contact_phone'] ?? null]))) ?>
            </p>
            <?php endif; ?>
        </div>

        <a href="/oficina" class="text-sm text-gray-400 hover:text-gray-600 shrink-0">← Oficina</a>
    </div>

    <?php if ($club['status'] === 'pending'): ?>
    <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-5 py-4 text-sm">
        Tu solicitud está pendiente de revisión por el administrador. Mientras tanto puedes preparar la información del club.
    </div>
    <?php endif; ?>

    <!-- ── Stats ─────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="card text-center py-3">
            <div class="text-2xl font-bold text-galgo-dark"><?= $stats['total'] ?></div>
            <div class="text-xs text-gray-500">Socios</div>
        </div>
        <div class="card text-center py-3">
            <div class="text-2xl font-bold text-green-600"><?= $stats['active'] ?></div>
            <div class="text-xs text-gray-500">Activos</div>
        </div>
        <div class="card text-center py-3">
            <div class="text-2xl font-bold text-yellow-500"><?= $stats['pending'] ?></div>
            <div class="text-xs text-gray-500">Pendientes</div>
        </div>
        <div class="card text-center py-3">
            <div class="text-2xl font-bold text-gray-400"><?= $stats['suspended'] ?></div>
            <div class="text-xs text-gray-500">Suspendidos</div>
        </div>
        <div class="card text-center py-3 <?= $stats['expired'] > 0 ? 'border border-red-200 bg-red-50' : '' ?>">
            <div class="text-2xl font-bold text-red-600"><?= $stats['expired'] ?></div>
            <div class="text-xs text-gray-500">Lic. caducadas</div>
        </div>
        <div class="card text-center py-3 <?= $stats['expiring_soon'] > 0 ? 'border border-yellow-200 bg-yellow-50' : '' ?>">
            <div class="text-2xl font-bold text-yellow-600"><?= $stats['expiring_soon'] ?></div>
            <div class="text-xs text-gray-500">Caducan pronto</div>
        </div>
    </div>

    <!-- ── Semáforo de licencias ─────────────────────────────── -->
    <?php if (!empty($alerts)): ?>
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
            <h2 class="font-semibold text-sm">Alertas de licencia</h2>
        </div>
        <ul class="divide-y divide-gray-50">
            <?php foreach ($alerts as $alert): ?>
            <?php
            $isExpired = $alert['alert_type'] === 'expired';
            $daysLeft  = (int) $alert['days_left'];
            $rowBg     = $isExpired ? 'bg-red-50' : 'bg-yellow-50';
            $dotColor  = $isExpired ? 'bg-red-500' : 'bg-yellow-400';
            $text      = $isExpired
                ? 'Caducó hace ' . abs($daysLeft) . ' día' . (abs($daysLeft) !== 1 ? 's' : '')
                : 'Caduca en ' . $daysLeft . ' día' . ($daysLeft !== 1 ? 's' : '');
            ?>
            <li class="flex items-center gap-3 px-5 py-3 <?= $rowBg ?>">
                <span class="w-2 h-2 rounded-full <?= $dotColor ?> shrink-0"></span>
                <span class="font-medium text-sm flex-1"><?= htmlspecialchars($alert['name']) ?></span>
                <?php if ($alert['license_type']): ?>
                    <span class="text-xs text-gray-500"><?= htmlspecialchars($alert['license_type']) ?></span>
                <?php endif; ?>
                <span class="text-xs font-medium <?= $isExpired ? 'text-red-700' : 'text-yellow-700' ?>"><?= $text ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- ── Socios ─────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold">Socios</h2>
            <a href="/oficina/mi-club/socios/nuevo" class="btn-gold text-sm">+ Añadir socio</a>
        </div>

        <?php if (empty($members)): ?>
            <div class="text-center py-12 text-gray-400 text-sm">
                No hay socios registrados. <a href="/oficina/mi-club/socios/nuevo" class="text-galgo-red hover:underline">Añadir el primero</a>.
            </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left">Contacto</th>
                        <th class="px-4 py-3 text-left">Licencia</th>
                        <th class="px-4 py-3 text-left">Vence</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($members as $m): ?>
                    <?php
                    // License semáforo per row
                    $licColor = '';
                    if ($m['license_expires_at']) {
                        $exp = new \DateTime($m['license_expires_at']);
                        $now = new \DateTime();
                        $diff = (int) $now->diff($exp)->days * ($exp >= $now ? 1 : -1);
                        if ($diff < 0)       $licColor = 'text-red-600 font-semibold';
                        elseif ($diff <= 30) $licColor = 'text-yellow-600 font-semibold';
                        else                 $licColor = 'text-green-600';
                    }
                    $statusCss = match($m['status']) {
                        'active'    => 'bg-green-100 text-green-700',
                        'pending'   => 'bg-yellow-100 text-yellow-700',
                        'suspended' => 'bg-red-100 text-red-700',
                        default     => 'bg-gray-100 text-gray-500',
                    };
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-medium"><?= htmlspecialchars($m['name']) ?></span>
                            <?php if ($m['is_delegate']): ?>
                                <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">Delegado</span>
                            <?php endif; ?>
                            <?php if ($m['username']): ?>
                                <div class="text-xs text-gray-400">@<?= htmlspecialchars($m['username']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            <?= $m['email'] ? htmlspecialchars($m['email']) : '' ?>
                            <?php if ($m['phone']): ?>
                                <div><?= htmlspecialchars($m['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <?= $m['license_number'] ? htmlspecialchars($m['license_number']) : '—' ?>
                            <?php if ($m['license_type']): ?>
                                <div class="text-gray-400"><?= htmlspecialchars($m['license_type']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-xs <?= $licColor ?>">
                            <?= $m['license_expires_at']
                                ? (new \DateTime($m['license_expires_at']))->format('d/m/Y')
                                : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full <?= $statusCss ?>">
                                <?= match($m['status']) {
                                    'active'    => 'Activo',
                                    'pending'   => 'Pendiente',
                                    'suspended' => 'Suspendido',
                                    default     => $m['status'],
                                } ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1 flex-wrap">
                                <?php if ($m['status'] === 'pending'): ?>
                                    <form method="POST" action="/oficina/mi-club/socios/<?= $m['id'] ?>/aprobar">
                                        <?= \Helpers\Csrf::field() ?>
                                        <button class="btn-gold text-xs py-1 px-2">Aprobar</button>
                                    </form>
                                <?php elseif ($m['status'] === 'active'): ?>
                                    <form method="POST" action="/oficina/mi-club/socios/<?= $m['id'] ?>/suspender"
                                          onsubmit="return confirm('¿Suspender a <?= htmlspecialchars(addslashes($m['name'])) ?>?')">
                                        <?= \Helpers\Csrf::field() ?>
                                        <button class="btn-outline text-xs py-1 px-2 text-red-600 border-red-200">Suspender</button>
                                    </form>
                                <?php elseif ($m['status'] === 'suspended'): ?>
                                    <form method="POST" action="/oficina/mi-club/socios/<?= $m['id'] ?>/aprobar">
                                        <?= \Helpers\Csrf::field() ?>
                                        <button class="btn-outline text-xs py-1 px-2 text-green-600 border-green-200">Reactivar</button>
                                    </form>
                                <?php endif; ?>
                                <a href="/oficina/mi-club/socios/<?= $m['id'] ?>/editar"
                                   class="btn-outline text-xs py-1 px-2">Editar</a>
                                <form method="POST" action="/oficina/mi-club/socios/<?= $m['id'] ?>/eliminar"
                                      onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($m['name'])) ?>? Esta acción no se puede deshacer.')">
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

    <!-- ── Próximas fases ─────────────────────────────────────── -->
    <div class="mt-6 grid sm:grid-cols-2 gap-4">
        <a href="/oficina/mi-club/documentos"
           class="card border border-gray-200 text-center py-8 hover:border-galgo-gold hover:shadow transition-shadow block">
            <svg class="w-8 h-8 mx-auto mb-2 text-galgo-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium text-gray-700">Bóveda de Documentos</p>
            <p class="text-xs mt-1 text-galgo-red">Gestionar &rarr;</p>
        </a>
        <a href="/oficina/mi-club/eventos"
           class="card border border-gray-200 text-center py-8 hover:border-galgo-gold hover:shadow transition-shadow block">
            <svg class="w-8 h-8 mx-auto mb-2 text-galgo-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-medium text-gray-700">Calendario de Eventos</p>
            <p class="text-xs mt-1 text-galgo-red">Gestionar &rarr;</p>
        </a>
    </div>

</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
