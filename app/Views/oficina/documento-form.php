<?php
$pageTitle = 'Subir documento — ' . htmlspecialchars($club['name']);
require APP_PATH . '/Views/layout/header.php';
$errors = $errors ?? [];
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="/oficina/mi-club/documentos" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Bóveda</a>

    <h1 class="text-2xl font-display font-bold mb-6">Subir documento</h1>

    <?php if ($errors): ?>
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 space-y-1">
            <?php foreach ($errors as $e): ?>
                <p class="text-sm"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/oficina/mi-club/documentos/subir"
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <div>
            <label class="form-label" for="title">Título *</label>
            <input type="text" id="title" name="title" required maxlength="200"
                   class="form-input" placeholder="Seguro de responsabilidad civil 2025">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="category">Categoría</label>
                <select id="category" name="category" class="form-input">
                    <option value="acta">Acta</option>
                    <option value="seguro">Seguro</option>
                    <option value="permiso">Permiso</option>
                    <option value="resolucion_coto">Resolución Coto</option>
                    <option value="federativo">Federativo</option>
                    <option value="otro" selected>Otro</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="expires_at">Fecha de vencimiento</label>
                <input type="date" id="expires_at" name="expires_at" class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label" for="document">Archivo *</label>
            <input type="file" id="document" name="document" required
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                   class="form-input file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-galgo-dark file:text-white hover:file:bg-gray-700 cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">PDF, JPEG, PNG, DOC, DOCX — máximo 20 MB</p>
        </div>

        <div>
            <label class="form-label" for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="2" maxlength="500"
                      class="form-input resize-none"
                      placeholder="Observaciones..."></textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-gold">Subir documento</button>
            <a href="/oficina/mi-club/documentos" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
