<?php $pageTitle = 'Importar Galgos CSV'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="/admin" class="text-gray-400 hover:text-gray-600 text-sm">← Panel admin</a>
        <span class="text-gray-300">/</span>
        <h1 class="text-2xl font-display font-bold">Importar Galgos desde CSV</h1>
    </div>

    <?php if ($flash = \Helpers\Flash::get('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-6"><?= htmlspecialchars($flash) ?></div>
    <?php elseif ($flash = \Helpers\Flash::get('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-6"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if (!isset($preview) || $preview === null): ?>

    <!-- Upload form -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">1. Selecciona el archivo CSV</h2>
        <p class="text-sm text-gray-500 mb-4">
            El CSV debe tener las columnas generadas por el scraper:<br>
            <code class="bg-gray-100 px-1 rounded text-xs">name, gender, color, country, year_of_birth, sire_name, dam_name, champion, variety, uuid, link_name</code>
        </p>
        <form method="POST" action="/admin/importar/preview" enctype="multipart/form-data">
            <?= \Helpers\Csrf::field() ?>
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-galgo-gold transition mb-4" id="drop-zone">
                <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 mb-2" id="drop-label">Arrastra el CSV aquí o haz clic para seleccionar</p>
                <input type="file" name="csv" id="csv-input" accept=".csv,text/csv" class="hidden" required>
                <button type="button" onclick="document.getElementById('csv-input').click()"
                    class="btn-secondary text-sm px-4 py-2">Seleccionar archivo</button>
            </div>
            <button type="submit" class="btn-primary px-6 py-2">Analizar CSV →</button>
        </form>
    </div>

    <div class="card bg-blue-50 border border-blue-100">
        <h3 class="font-semibold text-blue-800 mb-2">Cómo funciona la importación</h3>
        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
            <li>Los galgos que <strong>ya existen</strong> en la base de datos (mismo nombre) se <strong>omiten automáticamente</strong>.</li>
            <li>Primero se insertan todos los galgos nuevos, luego se enlazan padre/madre por nombre.</li>
            <li>Se puede importar el mismo CSV varias veces sin generar duplicados.</li>
            <li>La importación puede tardar varios minutos para ficheros grandes (~15.000 filas).</li>
        </ul>
    </div>

    <?php else: ?>

    <!-- Preview -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-1">2. Vista previa del CSV</h2>
        <p class="text-sm text-gray-500 mb-4">
            <?= number_format($total) ?> filas en total &nbsp;·&nbsp;
            <span class="text-green-700 font-medium"><?= number_format($dryStats['inserted']) ?> nuevos</span> &nbsp;·&nbsp;
            <span class="text-gray-500"><?= number_format($dryStats['skipped']) ?> ya existen / sin nombre</span>
        </p>

        <div class="overflow-x-auto rounded-lg border border-gray-200 mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-3 py-2 text-left">Nombre</th>
                        <th class="px-3 py-2 text-left">Sexo</th>
                        <th class="px-3 py-2 text-left">Color</th>
                        <th class="px-3 py-2 text-left">País</th>
                        <th class="px-3 py-2 text-left">Año</th>
                        <th class="px-3 py-2 text-left">Padre</th>
                        <th class="px-3 py-2 text-left">Madre</th>
                        <th class="px-3 py-2 text-left">Méritos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($preview as $row): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium"><?= htmlspecialchars($row['name'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($row['color'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($row['country'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($row['year_of_birth'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($row['sire_name'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($row['dam_name'] ?? '') ?></td>
                        <td class="px-3 py-2 text-gray-500 max-w-[160px] truncate"><?= htmlspecialchars($row['champion'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total > 8): ?>
            <p class="text-xs text-gray-400 text-center">Mostrando 8 de <?= number_format($total) ?> filas</p>
        <?php endif; ?>
    </div>

    <?php if ($dryStats['inserted'] > 0): ?>
    <div class="card bg-amber-50 border border-amber-200 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-semibold text-amber-800">
                    Se van a insertar <?= number_format($dryStats['inserted']) ?> galgos nuevos.
                </p>
                <p class="text-sm text-amber-700 mt-1">
                    Esta operación puede tardar unos minutos. No cierres la ventana hasta que termine.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="/admin/importar/ejecutar" id="import-form">
        <?= \Helpers\Csrf::field() ?>
        <div class="flex gap-3 items-center">
            <button type="submit" id="run-btn" class="btn-primary px-8 py-3 font-semibold"
                onclick="this.disabled=true; this.textContent='Importando… por favor espera'; this.form.submit();">
                Importar <?= number_format($dryStats['inserted']) ?> galgos nuevos
            </button>
            <a href="/admin/importar" class="text-sm text-gray-500 hover:text-gray-700">Cancelar</a>
        </div>
    </form>

    <?php else: ?>
    <div class="card bg-green-50 border border-green-200 text-center py-8">
        <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-green-800 font-semibold text-lg">¡Todo al día!</p>
        <p class="text-green-700 text-sm mt-1">Todos los galgos del CSV ya existen en la base de datos.</p>
        <a href="/admin/importar" class="inline-block mt-4 btn-secondary text-sm px-4 py-2">Subir otro CSV</a>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<script>
const input = document.getElementById('csv-input');
const label = document.getElementById('drop-label');
if (input && label) {
    input.addEventListener('change', () => {
        label.textContent = input.files[0]?.name ?? 'Arrastra el CSV aquí o haz clic para seleccionar';
    });
}
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
