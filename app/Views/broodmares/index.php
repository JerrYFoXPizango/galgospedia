<?php
$pageTitle = 'Reproductoras de Galgo Español';
$pageDesc  = 'Directorio de reproductoras de Galgo Español. Consulta pedigríes, historial de camadas y propietarios registrados en Galgospedia.';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-display font-bold mb-2">Reproductoras de Galgo Español</h1>
        <p class="text-gray-500 mb-6">Las mejores hembras reproductoras del Galgo Español</p>
        <form action="/reproductoras" method="GET" class="flex items-center justify-center gap-2">
            <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>"
                   placeholder="🔍 Buscar reproductora…"
                   class="w-72 px-4 py-2 rounded-full border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-galgo-gold">
            <button type="submit" class="px-5 py-2 rounded-full bg-galgo-gold text-white text-sm font-semibold hover:bg-yellow-500 transition">Buscar</button>
            <?php if (!empty($q)): ?>
                <a href="/reproductoras" class="px-4 py-2 rounded-full border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($broodmares)): ?>
        <div class="text-center py-20 text-gray-400">
            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-24 h-24 object-contain opacity-25 mb-4 mx-auto">
            <p>No hay reproductoras registradas aún.</p>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($broodmares as $b): ?>
            <a href="/galgos/<?= htmlspecialchars($b['slug']) ?>"
               class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition hover:-translate-y-1">
                <div class="aspect-square bg-gray-100 overflow-hidden relative">
                    <?php if ($b['photo_webp']): ?>
                        <img src="<?= \Helpers\Asset::url($b['photo_webp']) ?>"
                             alt="Reproductora Galgo Español <?= htmlspecialchars($b['name']) ?>"
                             class="w-full h-full object-contain group-hover:scale-105 transition duration-500" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-red-50 to-red-100">
                            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-20 h-20 object-contain opacity-25">
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-2 left-2">
                        <span class="badge-red text-xs">Reproductora</span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate"><?= htmlspecialchars($b['name']) ?></h3>
                    <?php if (!empty($b['club']) || !empty($b['country'])): ?>
                        <p class="text-xs text-gray-400 mt-0.5 truncate"><?= htmlspecialchars(implode(' · ', array_filter([$b['club'] ?? null, $b['country'] ?? null]))) ?></p>
                    <?php endif; ?>
                    <?php if ($b['description']): ?>
                        <p class="text-sm text-gray-500 mt-2 line-clamp-2"><?= htmlspecialchars($b['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($b['achievements']): ?>
                        <p class="text-xs text-galgo-red font-medium mt-2">🏆 <?= htmlspecialchars($b['achievements']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
