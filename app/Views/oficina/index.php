<?php
$pageTitle = 'Oficina Virtual — Clubs y Cotos';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-display font-bold">Oficina Virtual</h1>
            <p class="text-gray-500 mt-1">Directorio de clubs y cotos galgueriles</p>
        </div>
        <?php if (\Services\AuthService::isLoggedIn()): ?>
            <a href="/oficina/mi-club" class="btn-gold">Mi Club</a>
        <?php else: ?>
            <a href="/registro" class="btn-red text-sm">Registrarse para crear un club</a>
        <?php endif; ?>
    </div>

    <!-- Flash -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- Clubs grid -->
    <?php if (empty($clubs)): ?>
        <div class="text-center py-20 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-xl">No hay clubs registrados aún.</p>
            <?php if (\Services\AuthService::isLoggedIn()): ?>
                <a href="/oficina/solicitar-club" class="btn-gold mt-4 inline-block">Crear el primero</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            <?php foreach ($clubs as $club): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2 hover:shadow-md transition">
                <!-- Logo / Icon -->
                <div class="flex items-center gap-3">
                    <?php if ($club['logo_path']): ?>
                        <img src="<?= \Helpers\Asset::url($club['logo_path']) ?>"
                             alt="<?= htmlspecialchars($club['name']) ?>"
                             class="w-12 h-12 object-contain rounded-lg border border-gray-100">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-lg bg-galgo-dark flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-galgo-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($club['name']) ?></p>
                        <span class="text-xs text-gray-400">
                            <?= match($club['type']) {
                                'club'       => 'Club',
                                'coto'       => 'Coto',
                                'federacion' => 'Federación',
                                default      => 'Otro',
                            } ?>
                            <?php if ($club['is_verified']): ?>
                                <span class="text-galgo-gold" title="Verificado">✓</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Location -->
                <?php
                $loc = array_filter([
                    $club['province'] ?? null,
                    $club['autonomous_community'] ?? null,
                    $club['country'] !== 'España' ? ($club['country'] ?? null) : null,
                ]);
                ?>
                <?php if ($loc): ?>
                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars(implode(', ', $loc)) ?></p>
                <?php endif; ?>

                <!-- President -->
                <?php if ($club['president_username']): ?>
                    <p class="text-xs text-gray-400">Presidente: <?= htmlspecialchars($club['president_username']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?= $paginator->render('/oficina') ?>
    <?php endif; ?>

    <!-- Info box for non-logged users -->
    <?php if (!\Services\AuthService::isLoggedIn()): ?>
    <div class="mt-12 bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
        <h2 class="text-lg font-semibold mb-2">¿Eres presidente de un club o coto?</h2>
        <p class="text-gray-500 text-sm mb-4">
            Regístrate en Galgospedia y solicita el alta de tu entidad para acceder a la gestión de socios,
            documentos y calendario de eventos.
        </p>
        <a href="/registro" class="btn-red">Crear cuenta</a>
    </div>
    <?php endif; ?>

</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
