<?php
$pageTitle = $tournament['title'] . ' — Torneo de Galgo Español';
$pageDesc  = 'Torneo ' . $tournament['title'] .
             (!empty($tournament['location']) ? ' en ' . $tournament['location'] : '') .
             (!empty($tournament['date_start']) ? '. Fecha: ' . date('d/m/Y', strtotime($tournament['date_start'])) : '') .
             '. Consulta participantes, resultados y clasificación en Galgospedia.';
$ogType    = 'article';

// Schema.org — SportsEvent + BreadcrumbList
$_base = 'https://galgospedia.com';
$_eventStatusMap = [
    'published' => 'https://schema.org/EventScheduled',
    'finished'  => 'https://schema.org/EventScheduled',
    'cancelled' => 'https://schema.org/EventCancelled',
    'draft'     => 'https://schema.org/EventScheduled',
];
$_event = [
    '@type'       => 'SportsEvent',
    'name'        => $tournament['title'],
    'url'         => $_base . '/torneos/' . $tournament['slug'],
    'description' => $pageDesc,
    'sport'       => 'Carreras de Galgos',
    'eventStatus' => $_eventStatusMap[$tournament['status']] ?? 'https://schema.org/EventScheduled',
    'startDate'   => $tournament['starts_at'],
];
if (!empty($tournament['ends_at']))         $_event['endDate']  = $tournament['ends_at'];
if (!empty($tournament['organizer_name']))  $_event['organizer'] = ['@type' => 'Organization', 'name' => $tournament['organizer_name']];
if (!empty($tournament['location_name'])) {
    $_loc = ['@type' => 'Place', 'name' => $tournament['location_name']];
    if (!empty($tournament['location_address'])) $_loc['address'] = $tournament['location_address'];
    if (!empty($tournament['location_lat']) && !empty($tournament['location_lng'])) {
        $_loc['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $tournament['location_lat'],
            'longitude' => (float) $tournament['location_lng'],
        ];
    }
    $_event['location'] = $_loc;
}

$_schema = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        $_event,
        [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',   'item' => $_base],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Torneos', 'item' => $_base . '/torneos'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $tournament['title']],
            ],
        ],
    ],
];
$extraHead = '<script type="application/ld+json">'
    . json_encode($_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . '</script>';
unset($_base, $_event, $_schema, $_eventStatusMap, $_loc);

function disciplineLabel(string $d): string {
    return match($d) {
        'campo'           => 'Galgos en Campo',
        'liebre_mecanica' => 'Liebre Mecánica',
        'campeonato'      => 'Campeonato',
        'morfologico'     => 'Morfológico',
        'talleres'        => 'Talleres',
        'varios'          => 'Varios',
        default           => $d,
    };
}
function disciplineBadge(string $d): string {
    return match($d) {
        'campo'           => 'badge-campo',
        'liebre_mecanica' => 'badge-liebre',
        'campeonato'      => 'badge-campeonato',
        default           => 'badge-gray',
    };
}

$extraHead = '<link rel="stylesheet" href="/css/leaflet.css">';
require APP_PATH . '/Views/layout/header.php';

$dt      = new \DateTime($tournament['starts_at']);
$dtEnd   = $tournament['ends_at'] ? new \DateTime($tournament['ends_at']) : null;
$isCancelled = $tournament['status'] === 'cancelled';
$isDraft     = $tournament['status'] === 'draft';
$lat = $tournament['location_lat'];
$lng = $tournament['location_lng'];
?>

<div class="container mx-auto px-4 py-8 max-w-5xl">

    <!-- Breadcrumb -->
    <a href="/torneos" class="text-sm text-gray-400 hover:text-gray-600 inline-flex items-center gap-1 mb-5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Torneos
    </a>

    <?php if ($isDraft): ?>
        <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm px-4 py-2 rounded-lg">
            ⚠️ Este torneo está en <strong>borrador</strong> y no es visible para el público.
        </div>
    <?php endif; ?>
    <?php if ($isCancelled): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2 rounded-lg">
            ❌ Este torneo ha sido <strong>cancelado</strong>.
        </div>
    <?php endif; ?>

    <div class="space-y-4 max-w-3xl mx-auto">

        <!-- Header del evento -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="<?= disciplineBadge($tournament['discipline']) ?>"><?= disciplineLabel($tournament['discipline']) ?></span>
                <?php if ($tournament['category']): ?>
                    <span class="badge-gray"><?= htmlspecialchars($tournament['category']) ?></span>
                <?php endif; ?>
            </div>
            <h1 class="text-2xl font-display font-bold text-gray-900 mb-4"><?= htmlspecialchars($tournament['title']) ?></h1>

            <!-- Fechas -->
            <div class="flex items-start gap-3 text-sm mb-3">
                <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-galgo-dark text-white flex flex-col items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-800"><?= $dt->format('l, d \d\e F \d\e Y') ?></div>
                    <div class="text-gray-500">
                        <?= $dt->format('H:i') ?>h
                        <?php if ($dtEnd): ?>
                            – <?= $dtEnd->format('H:i') ?>h
                            <?php if ($dtEnd->format('Y-m-d') !== $dt->format('Y-m-d')): ?>
                                (hasta el <?= $dtEnd->format('d/m/Y') ?>)
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ubicación -->
            <?php if ($tournament['location_name'] || $tournament['location_address']): ?>
            <div class="flex items-start gap-3 text-sm">
                <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-red-50 text-galgo-red flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <?php if ($tournament['location_name']): ?>
                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($tournament['location_name']) ?></div>
                    <?php endif; ?>
                    <?php if ($tournament['location_address']): ?>
                        <div class="text-gray-500"><?= htmlspecialchars($tournament['location_address']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mapa Leaflet -->
        <?php if ($hasCoords):
            $lat = (float) $tournament['location_lat'];
            $lng = (float) $tournament['location_lng'];
        ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div id="tournament-map"
                 data-lat="<?= $lat ?>"
                 data-lng="<?= $lng ?>"
                 data-name="<?= htmlspecialchars($tournament['location_name'] ?? $tournament['title']) ?>"
                 style="height:300px;"></div>
            <div class="p-4 flex flex-wrap gap-2">
                <a href="https://www.google.com/maps?q=<?= $lat ?>,<?= $lng ?>" target="_blank" rel="noopener"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-sm hover:border-blue-400 hover:bg-blue-50 transition-colors text-gray-700 font-medium">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#4285F4"/><circle cx="12" cy="9" r="2.5" fill="white"/></svg>
                    Google Maps
                </a>
                <a href="https://waze.com/ul?ll=<?= $lat ?>,<?= $lng ?>&amp;navigate=yes" target="_blank" rel="noopener"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-sm hover:border-sky-400 hover:bg-sky-50 transition-colors text-gray-700 font-medium">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#33CCFF"><path d="M12 2C6.48 2 2 6.48 2 12c0 3.93 2.27 7.34 5.57 9.07L8 22l.5-1.94C9.58 20.64 10.77 21 12 21c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
                    Waze
                </a>
                <a href="https://maps.apple.com/?daddr=<?= $lat ?>,<?= $lng ?>&amp;dirflg=d" target="_blank" rel="noopener"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-sm hover:border-gray-400 hover:bg-gray-50 transition-colors text-gray-700 font-medium">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#000"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg>
                    Apple Maps
                </a>
            </div>
        </div>
        <?php elseif ($tournament['map_url']): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <a href="<?= htmlspecialchars($tournament['map_url']) ?>" target="_blank" rel="noopener"
               class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg bg-galgo-dark text-white text-sm font-semibold hover:opacity-90 transition">
                🗺️ Ver ubicación en el mapa
            </a>
        </div>
        <?php endif; ?>

        <!-- Cartel del evento -->
        <?php if (!empty($tournament['poster'])): ?>
        <?php $posterUrl = htmlspecialchars(\Helpers\Asset::url($tournament['poster'])); ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <img src="<?= $posterUrl ?>"
                 alt="Cartel de <?= htmlspecialchars($tournament['title']) ?>"
                 class="max-w-full mx-auto rounded-lg object-contain cursor-zoom-in transition-opacity hover:opacity-90"
                 style="max-height:500px"
                 onclick="document.getElementById('poster-lightbox').classList.remove('hidden')">
            <p class="text-xs text-gray-400 mt-2">Toca la imagen para ver a pantalla completa</p>
        </div>
        <div id="poster-lightbox" class="hidden fixed inset-0 flex items-center justify-center p-4" style="z-index:1200;background:rgba(0,0,0,0.88)" onclick="this.classList.add('hidden')">
            <button class="absolute top-4 right-4 text-white text-3xl leading-none font-bold opacity-70 hover:opacity-100" onclick="document.getElementById('poster-lightbox').classList.add('hidden')">&times;</button>
            <img src="<?= $posterUrl ?>" alt="Cartel" class="max-w-full max-h-full rounded-lg object-contain" style="max-height:92vh" onclick="event.stopPropagation()">
        </div>
        <script>document.addEventListener('keydown',function(e){if(e.key==='Escape')document.getElementById('poster-lightbox').classList.add('hidden');});</script>
        <?php endif; ?>

        <!-- Punto de reunión -->
        <?php if ($tournament['meeting_point'] || $tournament['meeting_time']): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
            <h2 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Punto de reunión
                <?php if ($tournament['meeting_time']): ?>
                    <span class="ml-auto text-sm font-normal bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">🕐 <?= substr($tournament['meeting_time'], 0, 5) ?>h</span>
                <?php endif; ?>
            </h2>
            <?php if ($tournament['meeting_point']): ?>
                <p class="text-amber-900 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($tournament['meeting_point'])) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Notas -->
        <?php if ($tournament['notes']): ?>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
            <h2 class="font-semibold text-orange-800 flex items-center gap-2 mb-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Notas
            </h2>
            <p class="text-orange-900 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($tournament['notes'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Descripción -->
        <?php if ($tournament['description']): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Descripción</h2>
            <p class="text-sm text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($tournament['description'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Organización -->
        <?php if ($tournament['organizer_name'] || $tournament['contact_info'] || $tournament['registration_required']): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Organización</h2>
            <dl class="space-y-2 text-sm">
                <?php if ($tournament['organizer_name']): ?>
                <div class="flex gap-2"><dt class="text-gray-500 w-28 flex-shrink-0">Organizador</dt><dd class="text-gray-800 font-medium"><?= htmlspecialchars($tournament['organizer_name']) ?></dd></div>
                <?php endif; ?>
                <?php if ($tournament['contact_info']): ?>
                <div class="flex gap-2"><dt class="text-gray-500 w-28 flex-shrink-0">Contacto</dt><dd class="text-gray-800"><?= htmlspecialchars($tournament['contact_info']) ?></dd></div>
                <?php endif; ?>
                <?php if ($tournament['max_participants']): ?>
                <div class="flex gap-2"><dt class="text-gray-500 w-28 flex-shrink-0">Plazas</dt><dd class="text-gray-800"><?= $tournament['max_participants'] ?></dd></div>
                <?php endif; ?>
                <?php if ($tournament['registration_required']): ?>
                <div class="flex gap-2 items-start">
                    <dt class="text-gray-500 w-28 flex-shrink-0">Inscripción</dt>
                    <dd>
                        <span class="bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">Requerida</span>
                        <?php if ($tournament['registration_url']): ?>
                            <a href="<?= htmlspecialchars($tournament['registration_url']) ?>" target="_blank" rel="noopener" class="ml-2 text-xs text-galgo-red hover:underline">→ Inscribirse</a>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
        <?php endif; ?>

        <!-- Footer: compartir + meta + acciones -->
        <div class="flex flex-wrap items-center gap-3 pb-6">
            <button onclick="copyLink()" id="share-btn" class="px-4 py-2 rounded-lg border border-gray-200 text-sm hover:border-gray-400 transition text-gray-600">📋 Copiar enlace</button>
            <script>function copyLink(){navigator.clipboard.writeText(window.location.href).then(function(){var b=document.getElementById('share-btn');b.textContent='✓ Enlace copiado';setTimeout(function(){b.textContent='📋 Copiar enlace';},2000);});}</script>
            <span class="text-xs text-gray-400 ml-auto">
                Publicado por <span class="font-medium text-gray-600"><?= htmlspecialchars($tournament['creator_username'] ?? '—') ?></span>
                · <?= (new \DateTime($tournament['created_at']))->format('d/m/Y') ?>
            </span>
            <?php if ($canEdit): ?>
            <a href="/torneos/<?= $tournament['slug'] ?>/editar" class="btn-outline text-sm">Editar</a>
            <form method="POST" action="/torneos/<?= $tournament['slug'] ?>/eliminar" onsubmit="return confirm('¿Eliminar este torneo?')">
                <?= \Helpers\Csrf::field() ?>
                <button type="submit" class="btn-danger text-sm">Eliminar</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
