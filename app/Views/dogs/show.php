<?php
$pageTitle = $dog['name'] . ' — Galgo Español';
$pageDesc  = 'Ficha de ' . $dog['name'] . ', galgo español' .
             (!empty($dog['club'])    ? ' del club ' . $dog['club']       : '') .
             (!empty($dog['country']) ? ' (' . $dog['country'] . ')'      : '') .
             '. Consulta su genealogía, historial de torneos y más en Galgospedia.';
$ogType    = 'profile';
$_base     = 'https://galgospedia.com';
if (!empty($dog['photo_webp'])) {
    $ogImage = $_base . $dog['photo_webp'];
}

// Schema.org — Animal + BreadcrumbList
$_animal = [
    '@type'       => 'Animal',
    'name'        => $dog['name'],
    'url'         => $_base . '/galgos/' . $dog['slug'],
    'description' => $pageDesc,
    'breed'       => 'Galgo Español',
    'gender'      => $dog['gender'] === 'male' ? 'Male' : ($dog['gender'] === 'female' ? 'Female' : null),
];
if (!empty($dog['photo_webp']))       $_animal['image']     = $_base . $dog['photo_webp'];
if (!empty($dog['date_of_birth']))    $_animal['birthDate'] = $dog['date_of_birth'];
if (!empty($dog['color']))            $_animal['color']     = $dog['color'];
if (!empty($dog['club']))             $_animal['memberOf']  = ['@type' => 'SportsOrganization', 'name' => $dog['club']];

$_schema = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        $_animal,
        [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',  'item' => $_base],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Galgos', 'item' => $_base . '/galgos'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $dog['name']],
            ],
        ],
    ],
];
$extraHead = '<script type="application/ld+json">'
    . json_encode($_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . '</script>';
unset($_base, $_animal, $_schema);

$canEdit   = \Services\AuthService::isLoggedIn() &&
             (\Services\AuthService::currentUserId() === (int)$dog['created_by'] || \Services\AuthService::isAdmin());
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="grid lg:grid-cols-3 gap-8">

        <!-- Left: Photo + quick info -->
        <div class="space-y-4">
            <!-- Photo -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                <?php if ($dog['photo_webp']): ?>
                    <img src="<?= \Helpers\Asset::url($dog['photo_webp']) ?>"
                         alt="<?= htmlspecialchars($dog['name']) ?>"
                         class="w-full object-contain max-h-[500px] bg-gray-50">
                <?php else: ?>
                    <div class="w-full aspect-[4/3] flex items-center justify-center bg-gray-50">
                        <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-40 h-40 object-contain opacity-30">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Badges -->
            <div class="flex flex-wrap gap-2">
                <?php if ($isStallion): ?>
                    <span class="badge-gold">Semental</span>
                <?php endif; ?>
                <?php if ($isBreeder): ?>
                    <span class="badge-red">Reproductora</span>
                <?php endif; ?>
                <span class="badge-gray">
                    <?= $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : 'Desconocido') ?>
                </span>
            </div>

            <!-- COI -->
            <?php if ($coi > 0): ?>
            <div class="card text-sm">
                <p class="text-gray-500 mb-1">Coeficiente de consanguinidad (COI)</p>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-galgo-red rounded-full h-2" style="width: <?= min(100, round($coi * 100)) ?>%"></div>
                    </div>
                    <span class="font-semibold text-galgo-red"><?= round($coi * 100, 2) ?>%</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Edit / Delete buttons -->
            <?php if ($canEdit): ?>
            <div class="flex gap-2">
                <a href="/galgos/<?= htmlspecialchars($dog['slug']) ?>/editar" class="btn-outline flex-1 text-center text-sm">Editar</a>
                <form method="POST" action="/galgos/<?= htmlspecialchars($dog['slug']) ?>/eliminar"
                      onsubmit="return confirm('¿Eliminar este galgo? Esta acción no se puede deshacer.')">
                    <?= \Helpers\Csrf::field() ?>
                    <button type="submit" class="btn-danger text-sm">Eliminar</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Details + genealogy -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Name & registration -->
            <div>
                <h1 class="text-3xl font-display font-bold"><?= htmlspecialchars($dog['name']) ?></h1>
                <?php if ($dog['club']): ?>
                    <p class="text-gray-500 text-sm mt-1">Club: <strong><?= htmlspecialchars($dog['club']) ?></strong></p>
                <?php endif; ?>
                <?php if ($dog['country']): ?>
                    <p class="text-gray-500 text-sm">País: <?= htmlspecialchars($dog['country']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Share buttons -->
            <div class="no-print flex flex-wrap items-center gap-2">
                <?php
                $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $shareUrl  = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/galgos/' . urlencode($dog['slug']);
                $shareText = urlencode('🐕 ' . $dog['name'] . ' — Árbol genealógico en Galgospedia');
                ?>
                <!-- Instagram -->
                <button id="btn-instagram"
                   class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
                   style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:radial-gradient(circle at 30% 107%,#fdf497 0%,#fdf497 5%,#fd5949 45%,#d6249f 60%,#285AEB 90%)">
                    <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.308.975.975 1.246 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.308 3.608-.975.975-2.242 1.246-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.334-3.608-1.308-.975-.975-1.246-2.242-1.308-3.608C2.175 15.584 2.163 15.204 2.163 12s.012-3.584.07-4.85c.062-1.366.334-2.633 1.308-3.608C4.516 2.497 5.783 2.226 7.149 2.163 8.415 2.105 8.795 2.163 12 2.163zm0-2.163C8.741 0 8.332.014 7.052.072 5.197.157 3.355.673 2.014 2.014.673 3.355.157 5.197.072 7.052.014 8.332 0 8.741 0 12c0 3.259.014 3.668.072 4.948.085 1.855.601 3.697 1.942 5.038 1.341 1.341 3.183 1.857 5.038 1.942C8.332 23.986 8.741 24 12 24s3.668-.014 4.948-.072c1.855-.085 3.697-.601 5.038-1.942 1.341-1.341 1.857-3.183 1.942-5.038.058-1.28.072-1.689.072-4.948s-.014-3.668-.072-4.948c-.085-1.855-.601-3.697-1.942-5.038C20.645.673 18.803.157 16.948.072 15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.162a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                    </svg>
                    <span>Instagram</span>
                </button>
                <!-- WhatsApp -->
                <a href="https://wa.me/?text=<?= $shareText ?>%20<?= urlencode($shareUrl) ?>"
                   target="_blank" rel="noopener"
                   class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
                   style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:#25D366">
                    <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 32 32" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 3C8.82 3 3 8.82 3 16c0 2.34.63 4.53 1.73 6.43L3 29l6.75-1.69A13 13 0 0 0 16 29c7.18 0 13-5.82 13-13S23.18 3 16 3zm0 23.85a10.84 10.84 0 0 1-5.53-1.52l-.4-.23-4.01 1 1.02-3.9-.26-.41A10.85 10.85 0 1 1 16 26.85zm5.95-8.1c-.33-.16-1.94-.96-2.24-1.07-.3-.1-.52-.16-.73.17-.22.33-.84 1.07-1.03 1.28-.19.22-.38.25-.71.08-.33-.16-1.39-.51-2.65-1.63-.98-.87-1.64-1.95-1.83-2.28-.19-.33-.02-.5.14-.67.15-.14.33-.38.5-.57.16-.19.22-.33.33-.55.11-.22.06-.41-.03-.57-.08-.16-.73-1.77-1-2.43-.26-.63-.53-.55-.73-.56h-.62c-.22 0-.57.08-.86.41-.3.33-1.13 1.1-1.13 2.69s1.16 3.12 1.32 3.34c.16.22 2.28 3.48 5.52 4.88.77.33 1.37.53 1.84.68.77.25 1.48.21 2.03.13.62-.09 1.91-.78 2.18-1.54.27-.75.27-1.4.19-1.54-.08-.13-.3-.22-.63-.38z"/>
                    </svg>
                    <span>WhatsApp</span>
                </a>
                <!-- Facebook -->
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
                   target="_blank" rel="noopener"
                   class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
                   style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:#1877F2">
                    <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.5 8.5H16V5.5H13.5C11.57 5.5 10 7.07 10 9v1.5H8V13.5h2V22h3v-8.5h2.5l.5-3H13V9c0-.28.22-.5.5-.5z"/>
                    </svg>
                    <span>Facebook</span>
                </a>
                <!-- X / Twitter -->
                <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= urlencode($shareUrl) ?>"
                   target="_blank" rel="noopener"
                   class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
                   style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:#000">
                    <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.75 3h3.08l-6.73 7.7L22 21h-6.24l-4.38-5.73L5.9 21H2.82l7.2-8.23L2 3h6.4l3.96 5.23L17.75 3zm-1.08 16.2h1.7L7.42 4.74H5.6l11.07 14.46z"/>
                    </svg>
                    <span>X</span>
                </a>
                <!-- Copiar enlace -->
                <button id="btn-copy" data-url="<?= htmlspecialchars($shareUrl) ?>"
                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition"
                   style="display:inline-flex;align-items:center;gap:6px;flex-direction:row">
                    <svg style="display:block;flex-shrink:0;width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    <span>Copiar enlace</span>
                </button>
                <!-- PDF -->
                <button onclick="window.print()"
                   class="px-3 py-1.5 rounded-lg bg-galgo-red text-white text-sm font-medium hover:bg-red-700 transition"
                   style="display:inline-flex;align-items:center;gap:6px;flex-direction:row">
                    <svg style="display:block;flex-shrink:0;width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Guardar PDF</span>
                </button>
            </div>

            <!-- Info table -->
            <div class="card">
                <h2 class="font-semibold mb-3">Información</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <?php if ($dog['date_of_birth']): ?>
                    <div><dt class="text-gray-400">Nacimiento</dt><dd class="font-medium"><?= htmlspecialchars($dog['date_of_birth']) ?></dd></div>
                    <?php endif; ?>
                    <?php if ($dog['date_of_death']): ?>
                    <div><dt class="text-gray-400">Fallecimiento</dt><dd class="font-medium"><?= htmlspecialchars($dog['date_of_death']) ?></dd></div>
                    <?php endif; ?>
                    <?php if ($dog['color']): ?>
                    <div><dt class="text-gray-400">Color</dt><dd class="font-medium"><?= htmlspecialchars($dog['color']) ?></dd></div>
                    <?php endif; ?>
                    <div>
                        <dt class="text-gray-400">Variedad</dt>
                        <dd class="font-medium">
                            <?= match($dog['breed_variant']) {
                                'english_greyhound' => 'Galgo Inglés',
                                'hybrid'            => 'Galgo Híbrido',
                                default             => 'Galgo Español',
                            } ?>
                        </dd>
                    </div>
                    <?php if ($dog['owner_username']): ?>
                    <div><dt class="text-gray-400">Propietario</dt><dd class="font-medium"><?= htmlspecialchars($dog['owner_username']) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Parents -->
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Genealogía Directa</h2>
                    <a href="/arbol/<?= htmlspecialchars($dog['slug']) ?>" class="text-sm text-galgo-red hover:underline">Ver árbol completo →</a>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-blue-500 font-medium mb-1">PADRE</p>
                        <?php if ($dog['father_name']): ?>
                            <a href="/galgos/<?= htmlspecialchars($dog['father_slug']) ?>" class="font-semibold hover:underline text-blue-800"><?= htmlspecialchars($dog['father_name']) ?></a>
                        <?php else: ?>
                            <span class="text-gray-400 italic">Sin registrar</span>
                        <?php endif; ?>
                    </div>
                    <div class="bg-pink-50 rounded-lg p-3">
                        <p class="text-xs text-pink-500 font-medium mb-1">MADRE</p>
                        <?php if ($dog['mother_name']): ?>
                            <a href="/galgos/<?= htmlspecialchars($dog['mother_slug']) ?>" class="font-semibold hover:underline text-pink-800"><?= htmlspecialchars($dog['mother_name']) ?></a>
                        <?php else: ?>
                            <span class="text-gray-400 italic">Sin registrar</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Link parents (if owner) -->
                <?php if ($canEdit): ?>
                <div class="mt-4 space-y-3" x-data="dogSearch()">
                    <p class="text-xs text-gray-400 uppercase font-medium">Vincular padres</p>

                    <!-- Father link -->
                    <form method="POST" action="/galgos/<?= htmlspecialchars($dog['slug']) ?>/padre" class="flex gap-2">
                        <?= \Helpers\Csrf::field() ?>
                        <input type="hidden" name="father_id" x-model="selectedFatherId">
                        <input type="text" class="form-input flex-1 text-sm" placeholder="Buscar padre por nombre..."
                               @input.debounce.400ms="search($event.target.value, 'father')"
                               @focus="searchType = 'father'"
                               x-model="fatherQuery">
                        <button type="submit" class="btn-outline text-sm">Vincular padre</button>
                    </form>

                    <!-- Mother link -->
                    <form method="POST" action="/galgos/<?= htmlspecialchars($dog['slug']) ?>/madre" class="flex gap-2">
                        <?= \Helpers\Csrf::field() ?>
                        <input type="hidden" name="mother_id" x-model="selectedMotherId">
                        <input type="text" class="form-input flex-1 text-sm" placeholder="Buscar madre por nombre..."
                               @input.debounce.400ms="search($event.target.value, 'mother')"
                               @focus="searchType = 'mother'"
                               x-model="motherQuery">
                        <button type="submit" class="btn-outline text-sm">Vincular madre</button>
                    </form>

                    <!-- Autocomplete dropdown -->
                    <div x-show="suggestions.length > 0" class="bg-white border rounded-lg shadow-lg max-h-40 overflow-y-auto z-10">
                        <template x-for="dog in suggestions" :key="dog.id">
                            <button type="button" @click="selectDog(dog)"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm flex items-center gap-2">
                                <img :src="dog.photo_url || ''" class="w-8 h-8 rounded-full object-cover bg-gray-200">
                                <span>
                                    <strong x-text="dog.name"></strong>
                                    <span class="text-gray-400 text-xs" x-text="dog.registration_number ? ' — ' + dog.registration_number : ''"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Children -->
            <?php if (!empty($children)): ?>
            <div class="card">
                <h2 class="font-semibold mb-3">Descendencia directa (<?= count($children) ?>)</h2>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    <?php foreach ($children as $child): ?>
                    <a href="/galgos/<?= htmlspecialchars($child['slug']) ?>" class="text-center group">
                        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 mb-1">
                            <?php if ($child['photo_thumb']): ?>
                                <img src="<?= \Helpers\Asset::url($child['photo_thumb']) ?>"
                                     alt="<?= htmlspecialchars($child['name']) ?>"
                                     class="w-full h-full object-contain group-hover:scale-105 transition" loading="lazy">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-10 h-10 object-contain opacity-25">
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs font-medium truncate"><?= htmlspecialchars($child['name']) ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historial Veterinario (solo dueño) -->
            <?php if (\Services\AuthService::isLoggedIn() && (int)$dog['owner_user_id'] === \Services\AuthService::currentUserId()):
                $vetModel   = new \Models\VetRecord();
                $vetRecent  = $vetModel->recentForDog((int)$dog['id'], \Services\AuthService::currentUserId(), 3);
                $vetStatus  = $vetModel->healthStatus((int)$dog['id'], \Services\AuthService::currentUserId());
                $statusConf = [
                    'green'  => ['Al día',          'bg-green-100 text-green-700',  '🟢'],
                    'yellow' => ['Tratamiento vencido', 'bg-yellow-100 text-yellow-700', '🟡'],
                    'red'    => ['Lesión activa',    'bg-red-100 text-red-700',      '🔴'],
                ];
                [$statusText, $statusCss, $statusIcon] = $statusConf[$vetStatus];
            ?>
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold flex items-center gap-2">
                        🏥 Historial Veterinario
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $statusCss ?>">
                            <?= $statusIcon ?> <?= $statusText ?>
                        </span>
                    </h2>
                    <a href="/apps/veterinario/<?= $dog['slug'] ?>" class="text-xs text-blue-500 hover:underline">Ver todo →</a>
                </div>
                <?php if (empty($vetRecent)): ?>
                    <p class="text-sm text-gray-400">Sin registros. <a href="/apps/veterinario/<?= $dog['slug'] ?>/nuevo" class="text-galgo-red hover:underline">Añadir primero</a></p>
                <?php else: ?>
                    <ul class="space-y-2">
                    <?php foreach ($vetRecent as $vr): ?>
                        <li class="flex items-center gap-2 text-sm">
                            <span class="text-base"><?= \Models\VetRecord::typeIcon($vr['type']) ?></span>
                            <span class="font-medium text-gray-800 truncate"><?= htmlspecialchars($vr['title']) ?></span>
                            <?php if ($vr['type']==='injury' && !$vr['resolved_at']): ?>
                                <span class="text-xs bg-red-100 text-red-600 rounded-full px-1.5 py-0.5 ml-auto flex-shrink-0">Activa</span>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 ml-auto flex-shrink-0"><?= (new DateTime($vr['date']))->format('d/m/Y') ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                    <a href="/apps/veterinario/<?= $dog['slug'] ?>/nuevo"
                       class="mt-3 inline-block text-xs text-galgo-red hover:underline">+ Añadir registro</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if ($dog['notes']): ?>
            <div class="card">
                <h2 class="font-semibold mb-2">Notas</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line"><?= htmlspecialchars($dog['notes']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<HTML
<script src="/js/alpine-components.js"></script>
<script>
document.getElementById('btn-instagram').addEventListener('click', function () {
    const url = window.location.href;
    const text = document.title;
    if (navigator.share) {
        navigator.share({ title: text, url: url });
    } else {
        // Fallback: copy link
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Enlace copiado. Pégalo en Instagram.');
    }
});
document.getElementById('btn-copy').addEventListener('click', function () {
    const btn = this;
    const url = window.location.href;
    const orig = btn.innerHTML;
    function showCopied() {
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> ¡Copiado!';
        btn.classList.add('border-green-400', 'text-green-600');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('border-green-400','text-green-600'); }, 2000);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(showCopied);
    } else {
        // Fallback for HTTP
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showCopied();
    }
});
</script>
HTML;
require APP_PATH . '/Views/layout/footer.php';
?>
