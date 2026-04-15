<?php
$pageTitle = ($event ? 'Editar evento' : 'Nuevo evento') . ' — ' . htmlspecialchars($club['name']);
require APP_PATH . '/Views/layout/header.php';
$errors = $errors ?? [];
$ev     = $event ?? [];

// Format datetime for datetime-local input (Y-m-d\TH:i)
function fmtDtInput(?string $dt): string {
    if (!$dt) return '';
    return (new \DateTime($dt))->format('Y-m-d\TH:i');
}
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="/oficina/mi-club/eventos" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">&larr; Calendario</a>

    <h1 class="text-2xl font-display font-bold mb-6">
        <?= $ev ? 'Editar evento' : 'Nuevo evento' ?>
    </h1>

    <?php if ($errors): ?>
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 space-y-1">
            <?php foreach ($errors as $e): ?>
                <p class="text-sm"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST"
          action="/oficina/mi-club/eventos/<?= $ev ? $ev['id'] . '/actualizar' : 'nuevo' ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        <?= \Helpers\Csrf::field() ?>

        <div>
            <label class="form-label" for="title">Título *</label>
            <input type="text" id="title" name="title" required maxlength="200"
                   value="<?= htmlspecialchars($ev['title'] ?? '') ?>"
                   class="form-input" placeholder="Tirada de primavera 2025">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="type">Tipo</label>
                <select id="type" name="type" class="form-input">
                    <?php foreach (['tirada' => 'Tirada', 'carrera' => 'Carrera', 'veda' => 'Veda', 'reunion' => 'Reunión', 'otro' => 'Otro'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($ev['type'] ?? 'otro') === $val ? 'selected' : '' ?>>
                            <?= $lbl ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label" for="location">Lugar</label>
                <input type="text" id="location" name="location" maxlength="255"
                       value="<?= htmlspecialchars($ev['location'] ?? '') ?>"
                       class="form-input" placeholder="Campo de Valjunquera, Teruel">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="starts_at">Inicio *</label>
                <input type="datetime-local" id="starts_at" name="starts_at" required
                       value="<?= fmtDtInput($ev['starts_at'] ?? '') ?>"
                       class="form-input">
            </div>
            <div>
                <label class="form-label" for="ends_at">Fin</label>
                <input type="datetime-local" id="ends_at" name="ends_at"
                       value="<?= fmtDtInput($ev['ends_at'] ?? '') ?>"
                       class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label" for="description">Descripción</label>
            <textarea id="description" name="description" rows="3" maxlength="2000"
                      class="form-input resize-none"
                      placeholder="Detalles del evento..."><?= htmlspecialchars($ev['description'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-gold">
                <?= $ev ? 'Guardar cambios' : 'Crear evento' ?>
            </button>
            <a href="/oficina/mi-club/eventos" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
