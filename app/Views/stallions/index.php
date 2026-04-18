<?php
$pageTitle = 'Sementales de Galgo Español';
$pageDesc  = 'Directorio de sementales de Galgo Español disponibles para reproducción. Consulta genealogía, historial y propietario de cada semental.';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-display font-bold mb-2">Sementales de Galgo Español</h1>
        <p class="text-gray-500 mb-6">Los mejores machos reproductores del Galgo Español</p>
        <form action="/sementales" method="GET" class="flex items-center justify-center gap-2">
            <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>"
                   placeholder="🔍 Buscar semental…"
                   class="w-72 px-4 py-2 rounded-full border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-galgo-gold">
            <button type="submit" class="px-5 py-2 rounded-full bg-galgo-gold text-white text-sm font-semibold hover:bg-yellow-500 transition">Buscar</button>
            <?php if (!empty($q)): ?>
                <a href="/sementales" class="px-4 py-2 rounded-full border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($stallions)): ?>
        <div class="text-center py-20 text-gray-400">
            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-24 h-24 object-contain opacity-25 mb-4 mx-auto">
            <p>No hay sementales registrados aún.</p>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($stallions as $s): ?>
            <a href="/galgos/<?= htmlspecialchars($s['slug']) ?>"
               class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition hover:-translate-y-1">
                <div class="aspect-square bg-gray-100 overflow-hidden relative">
                    <?php if ($s['photo_webp']): ?>
                        <img src="<?= \Helpers\Asset::url($s['photo_webp']) ?>"
                             alt="Semental Galgo Español <?= htmlspecialchars($s['name']) ?>"
                             class="w-full h-full object-contain group-hover:scale-105 transition duration-500" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-50 to-yellow-100">
                            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-20 h-20 object-contain opacity-25">
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-2 left-2">
                        <span class="badge-gold text-xs">Semental</span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate"><?= htmlspecialchars($s['name']) ?></h3>
                    <?php if (!empty($s['club']) || !empty($s['country'])): ?>
                        <p class="text-xs text-gray-400 mt-0.5 truncate"><?= htmlspecialchars(implode(' · ', array_filter([$s['club'] ?? null, $s['country'] ?? null]))) ?></p>
                    <?php endif; ?>
                    <?php if ($s['description']): ?>
                        <p class="text-sm text-gray-500 mt-2 line-clamp-2"><?= htmlspecialchars($s['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($s['achievements']): ?>
                        <p class="text-xs text-galgo-gold font-medium mt-2">🏆 <?= htmlspecialchars($s['achievements']) ?></p>
                    <?php endif; ?>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                        <span>Ver árbol genealógico →</span>
                        <?php if ($s['date_of_birth']): ?>
                            <span><?= date('Y', strtotime($s['date_of_birth'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
