<?php $pageTitle = 'Mi Perfil'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="grid md:grid-cols-3 gap-8">
        <!-- Left: Avatar + info -->
        <div class="space-y-4">
            <div class="card text-center">
                <div class="w-24 h-24 mx-auto rounded-full overflow-hidden bg-gray-200 mb-3">
                    <?php if ($user['avatar_path']): ?>
                        <img src="<?= \Helpers\Asset::url($user['avatar_path']) ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-4xl bg-galgo-red/10 text-galgo-red font-bold">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h2 class="font-bold text-xl"><?= htmlspecialchars($user['username']) ?></h2>
                <p class="text-sm text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                <span class="inline-block mt-2 text-xs px-2 py-0.5 bg-galgo-gold/20 text-yellow-800 rounded-full">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>

            <!-- Upload avatar -->
            <div class="card">
                <h3 class="font-semibold text-sm mb-3">Cambiar avatar</h3>
                <form method="POST" action="/mi-perfil/avatar" enctype="multipart/form-data" class="space-y-2">
                    <?= \Helpers\Csrf::field() ?>
                    <input type="file" name="avatar" accept="image/*" class="text-sm text-gray-500 w-full">
                    <button type="submit" class="btn-outline text-sm w-full">Subir avatar</button>
                </form>
            </div>

            <!-- Plan badge + club logo -->
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-sm">Plan</h3>
                    <?php if (($user['plan'] ?? 'free') === 'club'): ?>
                        <span class="text-xs px-2 py-0.5 bg-galgo-gold text-white rounded-full font-bold">Club</span>
                    <?php else: ?>
                        <span class="text-xs px-2 py-0.5 bg-gray-200 text-gray-600 rounded-full">Gratuito</span>
                    <?php endif; ?>
                </div>

                <?php if (($user['plan'] ?? 'free') === 'club'): ?>
                    <!-- Club logo preview -->
                    <?php if (!empty($user['club_logo_path'])): ?>
                        <div class="mb-3 p-2 bg-gray-50 rounded-lg flex items-center justify-center">
                            <img src="<?= \Helpers\Asset::url($user['club_logo_path']) ?>"
                                 alt="Logo del club" class="max-h-12 object-contain">
                        </div>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500 mb-2">Tu logo aparecerá en todas las fotos de tus galgos.</p>
                    <form method="POST" action="/mi-perfil/club-logo" enctype="multipart/form-data" class="space-y-2">
                        <?= \Helpers\Csrf::field() ?>
                        <input type="file" name="club_logo" accept="image/*" class="text-sm text-gray-500 w-full">
                        <button type="submit" class="btn-gold text-sm w-full">Subir logo del club</button>
                    </form>
                <?php else: ?>
                    <p class="text-xs text-gray-500">Con el plan <strong>Club</strong> puedes añadir el logo de tu club a todas las fotos de tus galgos.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Dogs -->
        <div class="md:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-display font-bold">Mis Galgos</h2>
                <a href="/galgos/nuevo" class="btn-gold text-sm">+ Añadir</a>
            </div>

            <?php if (empty($dogs['data'])): ?>
                <div class="card text-center py-12 text-gray-400">
                    <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-20 h-20 object-contain opacity-25 mb-4 mx-auto">
                    <p>Aún no has añadido ningún galgo.</p>
                    <a href="/galgos/nuevo" class="btn-red mt-4 inline-block">Añadir mi primer galgo</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <?php foreach ($dogs['data'] as $dog): ?>
                    <a href="/galgos/<?= htmlspecialchars($dog['slug']) ?>" class="group bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="aspect-square bg-gray-100 overflow-hidden flex items-center justify-center">
                            <?php if ($dog['photo_thumb']): ?>
                                <img src="<?= \Helpers\Asset::url($dog['photo_thumb']) ?>"
                                     alt="<?= htmlspecialchars($dog['name']) ?>"
                                     class="w-full h-full object-contain group-hover:scale-105 transition" loading="lazy">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-12 h-12 object-contain opacity-25">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-2">
                            <p class="text-sm font-semibold truncate"><?= htmlspecialchars($dog['name']) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
