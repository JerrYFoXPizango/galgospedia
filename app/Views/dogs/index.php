<?php
$pageTitle = 'Directorio de Galgos Españoles';
$pageDesc  = 'Consulta el directorio completo de Galgos Españoles registrados en Galgospedia. Busca por nombre, propietario, club y más.';
$extraHead = '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => 'https://galgospedia.com'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Directorio de Galgos'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
require APP_PATH . '/Views/layout/header.php';
$paginator = new \Helpers\Paginator($total, $page, $perPage);
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-display font-bold">Directorio de Galgos Españoles</h1>
        <?php if (\Services\AuthService::isLoggedIn()): ?>
            <a href="/galgos/nuevo" class="btn-gold">+ Añadir Galgo</a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <form method="GET" action="/galgos" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-8 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label" for="q">Buscar</label>
            <input type="text" id="q" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>"
                   class="form-input" placeholder="Nombre, Club, País, Campeón...">
        </div>
        <div>
            <label class="form-label" for="gender">Sexo</label>
            <select id="gender" name="gender" class="form-input">
                <option value="">Todos</option>
                <option value="male"   <?= ($filters['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Macho</option>
                <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Hembra</option>
            </select>
        </div>
        <button type="submit" class="btn-red">Buscar</button>
        <a href="/galgos" class="btn-outline">Limpiar</a>
    </form>

    <!-- Grid -->
    <?php if (empty($dogs)): ?>
        <div class="text-center py-20 text-gray-400">
            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-24 h-24 object-contain opacity-25 mb-4 mx-auto">
            <p class="text-xl">No se encontraron galgos.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($dogs as $dog): ?>
            <a href="/galgos/<?= htmlspecialchars($dog['slug']) ?>" class="group bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="h-40 bg-gray-100 overflow-hidden flex items-center justify-center">
                    <?php if ($dog['photo_webp']): ?>
                        <img src="<?= \Helpers\Asset::url($dog['photo_webp']) ?>"
                             alt="Galgo Español <?= htmlspecialchars($dog['name']) ?>"
                             class="w-full h-full object-contain group-hover:scale-105 transition duration-300" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-16 h-16 object-contain opacity-25">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-2">
                    <p class="font-semibold text-sm truncate"><?= htmlspecialchars($dog['name']) ?></p>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs px-1.5 py-0.5 rounded-full <?= $dog['gender'] === 'male' ? 'bg-blue-100 text-blue-700' : ($dog['gender'] === 'female' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-500') ?>">
                            <?= $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '?') ?>
                        </span>
                        <?php if ($dog['stallion_id']): ?>
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-galgo-gold/20 text-yellow-800">S</span>
                        <?php endif; ?>
                        <?php if ($dog['broodmare_id']): ?>
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-galgo-red/10 text-red-700">R</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($dog['club']) || !empty($dog['country'])): ?>
                    <div class="mt-1 text-xs text-gray-400 truncate">
                        <?= htmlspecialchars(implode(' · ', array_filter([$dog['club'] ?? null, $dog['country'] ?? null]))) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?= $paginator->render('/galgos?' . http_build_query(array_filter($filters))) ?>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
