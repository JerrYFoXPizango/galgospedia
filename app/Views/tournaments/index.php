<?php
$pageTitle = 'Torneos y Campeonatos de Galgo Español';
$pageDesc  = 'Agenda completa de competiciones de Galgo Español: Galgos en Campo, Liebre Mecánica y Campeonatos nacionales. Consulta resultados y clasificaciones.';
$extraHead = '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => 'https://galgospedia.com'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Torneos y Campeonatos'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';

// Helpers
function disciplineLabel(string $d): string {
    return match($d) {
        'campo'            => 'Galgos en Campo',
        'liebre_mecanica'  => 'Liebre Mecánica',
        'campeonato'       => 'Campeonato',
        'morfologico'      => 'Morfológico',
        'talleres'         => 'Talleres',
        'varios'           => 'Varios',
        default            => $d,
    };
}
function disciplineBadge(string $d): string {
    return match($d) {
        'campo'           => 'badge-campo',
        'liebre_mecanica' => 'badge-liebre',
        'campeonato'      => 'badge-campeonato',
        'morfologico'     => 'badge-gray',
        'talleres'        => 'badge-gray',
        'varios'          => 'badge-gray',
        default           => 'badge-gray',
    };
}

require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-display font-bold">Torneos y Campeonatos de Galgo Español</h1>
            <p class="text-sm text-gray-500 mt-1">Agenda de competiciones · Galgos en Campo · Liebre Mecánica · Campeonatos · Morfológico · Talleres · Varios</p>
        </div>
        <?php if (\Services\AuthService::isLoggedIn()): ?>
            <a href="/torneos/nuevo" class="btn-gold text-sm self-start sm:self-auto">+ Publicar torneo</a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <form method="GET" action="/torneos" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                   placeholder="Buscar por nombre, lugar…"
                   class="form-input flex-1">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer whitespace-nowrap">
                <input type="checkbox" name="futuros" value="1" class="rounded accent-galgo-gold"
                       <?= $upcoming === '1' ? 'checked' : '' ?>>
                Solo eventos futuros
            </label>
            <button type="submit" class="btn-red text-sm">Buscar</button>
            <?php if ($q || $upcoming !== '1' || $discipline): ?>
                <a href="/torneos" class="btn-outline text-sm">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Tabs disciplina -->
    <div class="flex gap-1 mb-6 flex-wrap">
        <?php
        $tabs = [
            ''                 => 'Todos',
            'campo'            => 'Galgos en Campo',
            'liebre_mecanica'  => 'Liebre Mecánica',
            'campeonato'       => 'Campeonatos',
            'morfologico'      => 'Morfológico',
            'talleres'         => 'Talleres',
            'varios'           => 'Varios',
        ];
        $qs = http_build_query(array_filter(['q' => $q, 'futuros' => $upcoming === '1' ? '1' : null]));
        foreach ($tabs as $val => $label):
            $active = $discipline === $val;
            $href   = '/torneos?disciplina=' . $val . ($qs ? '&' . $qs : '');
        ?>
        <a href="<?= $href ?>"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                  <?= $active ? 'bg-galgo-dark text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-galgo-red hover:text-galgo-red' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Resultados -->
    <?php if (empty($tournaments)): ?>
        <div class="text-center py-20 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="font-medium">No hay torneos<?= $discipline ? ' de ' . disciplineLabel($discipline) : '' ?> disponibles.</p>
            <?php if (\Services\AuthService::isLoggedIn()): ?>
                <a href="/torneos/nuevo" class="mt-4 inline-block btn-gold text-sm">Publicar el primero</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid gap-4">
            <?php foreach ($tournaments as $t):
                $dt      = new \DateTime($t['starts_at']);
                $isPast  = $dt < new \DateTime();
                $isToday = $dt->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            ?>
            <a href="/torneos/<?= $t['slug'] ?>"
               class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-galgo-red transition-all overflow-hidden
                      <?= $isPast ? 'opacity-70' : '' ?>">
                <div class="flex">
                    <!-- Fecha -->
                    <div class="w-20 flex-shrink-0 flex flex-col items-center justify-center py-4 px-2
                                <?= $isToday ? 'bg-galgo-red text-white' : ($isPast ? 'bg-gray-100 text-gray-500' : 'bg-galgo-dark text-white') ?>">
                        <span class="text-2xl font-bold leading-none"><?= $dt->format('d') ?></span>
                        <span class="text-xs uppercase mt-0.5"><?= $dt->format('M') ?></span>
                        <span class="text-xs opacity-70"><?= $dt->format('Y') ?></span>
                    </div>
                    <!-- Info -->
                    <div class="flex-1 px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="<?= disciplineBadge($t['discipline']) ?>"><?= disciplineLabel($t['discipline']) ?></span>
                            <?php if ($t['category']): ?>
                                <span class="badge-gray"><?= htmlspecialchars($t['category']) ?></span>
                            <?php endif; ?>
                            <?php if ($t['registration_required']): ?>
                                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">Inscripción requerida</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-semibold text-gray-900 text-base leading-tight"><?= htmlspecialchars($t['title']) ?></h3>
                        <div class="flex flex-wrap gap-x-4 mt-1.5 text-xs text-gray-500">
                            <?php if ($t['location_name']): ?>
                                <span>📍 <?= htmlspecialchars($t['location_name']) ?></span>
                            <?php endif; ?>
                            <span>🕐 <?= $dt->format('H:i') ?>h
                                <?php if ($t['ends_at']): ?>
                                    – <?= (new \DateTime($t['ends_at']))->format('H:i') ?>h
                                <?php endif; ?>
                            </span>
                            <?php if ($t['meeting_time']): ?>
                                <span>🚗 Reunión: <?= substr($t['meeting_time'], 0, 5) ?>h</span>
                            <?php endif; ?>
                            <?php if ($t['organizer_name']): ?>
                                <span>👤 <?= htmlspecialchars($t['organizer_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Flecha -->
                    <div class="flex items-center pr-4 text-gray-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total > $perPage):
            $totalPages = (int) ceil($total / $perPage);
            $baseQs = http_build_query(array_filter([
                'disciplina' => $discipline,
                'q'          => $q,
                'futuros'    => $upcoming,
            ]));
        ?>
        <div class="flex justify-center items-center gap-2 mt-8">
            <?php if ($page > 1): ?>
                <a href="?<?= $baseQs ?>&page=<?= $page - 1 ?>" class="btn-outline text-sm">← Anterior</a>
            <?php endif; ?>
            <span class="text-sm text-gray-500">Página <?= $page ?> de <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $baseQs ?>&page=<?= $page + 1 ?>" class="btn-outline text-sm">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
