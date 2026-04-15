<?php
$pageTitle = 'Torneos — Admin';
require APP_PATH . '/Views/layout/header.php';

function dLabel(string $d): string {
    return match($d) {
        'campo'           => 'Campo',
        'liebre_mecanica' => 'Liebre',
        'campeonato'      => 'Campeonato',
        'morfologico'     => 'Morfológico',
        'talleres'        => 'Talleres',
        'varios'          => 'Varios',
        default           => $d,
    };
}
function dBadge(string $d): string {
    return match($d) {
        'campo'           => 'badge-campo',
        'liebre_mecanica' => 'badge-liebre',
        'campeonato'      => 'badge-campeonato',
        default           => 'badge-gray',
    };
}
$statusLabel = fn(string $s) => match($s) {
    'published' => ['Publicado', 'bg-green-100 text-green-700'],
    'draft'     => ['Borrador',  'bg-yellow-100 text-yellow-700'],
    'cancelled' => ['Cancelado', 'bg-red-100 text-red-700'],
    default     => [$s, 'bg-gray-100 text-gray-600'],
};
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <a href="/admin" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Admin</a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">Torneos</h1>
        <a href="/torneos/nuevo" class="btn-gold text-sm">+ Publicar torneo</a>
    </div>

    <?php \Helpers\Flash::render() ?>

    <!-- Filtros -->
    <form method="GET" action="/admin/torneos" class="flex flex-wrap gap-3 mb-5">
        <select name="disciplina" class="form-input w-auto text-sm">
            <option value="">Todas las disciplinas</option>
            <option value="campo"           <?= $discipline === 'campo'           ? 'selected' : '' ?>>Galgos en Campo</option>
            <option value="liebre_mecanica" <?= $discipline === 'liebre_mecanica' ? 'selected' : '' ?>>Liebre Mecánica</option>
            <option value="campeonato"      <?= $discipline === 'campeonato'      ? 'selected' : '' ?>>Campeonatos</option>
            <option value="morfologico"     <?= $discipline === 'morfologico'     ? 'selected' : '' ?>>Morfológico</option>
            <option value="talleres"        <?= $discipline === 'talleres'        ? 'selected' : '' ?>>Talleres</option>
            <option value="varios"          <?= $discipline === 'varios'          ? 'selected' : '' ?>>Varios</option>
        </select>
        <select name="estado" class="form-input w-auto text-sm">
            <option value="">Todos los estados</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publicados</option>
            <option value="draft"     <?= $status === 'draft'     ? 'selected' : '' ?>>Borradores</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelados</option>
        </select>
        <button type="submit" class="btn-outline text-sm">Filtrar</button>
        <span class="text-sm text-gray-400 self-center"><?= $total ?> torneos</span>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($tournaments)): ?>
            <div class="text-center py-12 text-gray-400 text-sm">No hay torneos con estos filtros.</div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Título</th>
                        <th class="px-4 py-3 text-left">Disciplina</th>
                        <th class="px-4 py-3 text-left">Inicio</th>
                        <th class="px-4 py-3 text-left">Lugar</th>
                        <th class="px-4 py-3 text-left">Creado por</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($tournaments as $t):
                        [$sLabel, $sCss] = $statusLabel($t['status']);
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="/torneos/<?= $t['slug'] ?>" target="_blank"
                               class="font-medium text-gray-800 hover:text-galgo-red">
                                <?= htmlspecialchars($t['title']) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="<?= dBadge($t['discipline']) ?>"><?= dLabel($t['discipline']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <?= (new \DateTime($t['starts_at']))->format('d/m/Y H:i') ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <?= htmlspecialchars($t['location_name'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <?= htmlspecialchars($t['creator_username'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full <?= $sCss ?>"><?= $sLabel ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="/torneos/<?= $t['slug'] ?>/editar"
                                   class="text-xs text-blue-600 hover:underline">Editar</a>

                                <!-- Cambiar estado rápido -->
                                <form method="POST" action="/admin/torneos/<?= $t['id'] ?>/estado">
                                    <?= \Helpers\Csrf::field() ?>
                                    <select name="status" onchange="this.form.submit()"
                                            class="text-xs border border-gray-200 rounded px-1 py-0.5 text-gray-600 cursor-pointer">
                                        <option value="published" <?= $t['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                                        <option value="draft"     <?= $t['status'] === 'draft'     ? 'selected' : '' ?>>Borrador</option>
                                        <option value="cancelled" <?= $t['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($total > 30):
            $totalPages = (int) ceil($total / 30);
            $qs = http_build_query(array_filter(['disciplina' => $discipline, 'estado' => $status]));
        ?>
        <div class="flex justify-center items-center gap-2 px-4 py-3 border-t border-gray-100">
            <?php if ($page > 1): ?>
                <a href="?<?= $qs ?>&page=<?= $page - 1 ?>" class="btn-outline text-xs">← Anterior</a>
            <?php endif; ?>
            <span class="text-xs text-gray-500">Página <?= $page ?> de <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $qs ?>&page=<?= $page + 1 ?>" class="btn-outline text-xs">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
