<?php $pageTitle = 'Administración'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-display font-bold mb-8">Panel de Administración</h1>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
        <div class="card text-center"><div class="text-3xl font-bold text-galgo-red"><?= $stats['dogs'] ?></div><div class="text-sm text-gray-500">Galgos</div></div>
        <div class="card text-center"><div class="text-3xl font-bold text-blue-600"><?= $stats['users'] ?></div><div class="text-sm text-gray-500">Usuarios</div></div>
        <div class="card text-center"><div class="text-3xl font-bold text-galgo-gold"><?= $stats['stallions'] ?></div><div class="text-sm text-gray-500">Sementales</div></div>
        <div class="card text-center"><div class="text-3xl font-bold text-pink-600"><?= $stats['broodmares'] ?></div><div class="text-sm text-gray-500">Reproductoras</div></div>
        <div class="card text-center"><div class="text-3xl font-bold text-green-600"><?= $stats['clubs_active'] ?></div><div class="text-sm text-gray-500">Clubs activos</div></div>
        <div class="card text-center relative">
            <div class="text-3xl font-bold text-yellow-500"><?= $stats['clubs_pending'] ?></div>
            <div class="text-sm text-gray-500">Clubs pendientes</div>
            <?php if ($stats['clubs_pending'] > 0): ?>
                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
            <?php endif; ?>
        </div>
        <div class="card text-center">
            <div class="text-3xl font-bold text-purple-600"><?= $stats['tournaments'] ?></div>
            <div class="text-sm text-gray-500">Torneos</div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="grid md:grid-cols-5 gap-4">
        <a href="/admin/galgos" class="card hover:border-galgo-red hover:shadow-md transition border border-transparent">
            <h3 class="font-bold text-lg mb-1">Gestionar Galgos</h3>
            <p class="text-sm text-gray-500">Ver, editar y gestionar sementales/reproductoras</p>
        </a>
        <a href="/admin/usuarios" class="card hover:border-galgo-red hover:shadow-md transition border border-transparent">
            <h3 class="font-bold text-lg mb-1">Gestionar Usuarios</h3>
            <p class="text-sm text-gray-500">Ver usuarios y cambiar roles</p>
        </a>
        <a href="/admin/clubs" class="card hover:border-galgo-gold hover:shadow-md transition <?= $stats['clubs_pending'] > 0 ? 'border border-yellow-200 bg-yellow-50' : 'border border-transparent' ?>">
            <h3 class="font-bold text-lg mb-1">Gestionar Clubs</h3>
            <p class="text-sm text-gray-500">Aprobar solicitudes y gestionar clubs y cotos</p>
            <?php if ($stats['clubs_pending'] > 0): ?>
                <span class="mt-2 inline-block text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">
                    <?= $stats['clubs_pending'] ?> pendiente<?= $stats['clubs_pending'] > 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="/admin/alertas" class="card hover:border-red-400 hover:shadow-md transition border border-transparent">
            <h3 class="font-bold text-lg mb-1">Alertas de licencia</h3>
            <p class="text-sm text-gray-500">Ver pendientes, historial y lanzar envío manual</p>
        </a>
        <a href="/admin/torneos" class="card hover:border-purple-400 hover:shadow-md transition border border-transparent">
            <h3 class="font-bold text-lg mb-1">Torneos</h3>
            <p class="text-sm text-gray-500">Gestionar eventos publicados, borradores y cancelados</p>
        </a>
        <a href="/admin/patrocinadores" class="card hover:border-yellow-400 hover:shadow-md transition border border-transparent">
            <h3 class="font-bold text-lg mb-1">🤝 Patrocinadores</h3>
            <p class="text-sm text-gray-500">Gestionar logos y enlaces del carrusel en la landing page</p>
        </a>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
