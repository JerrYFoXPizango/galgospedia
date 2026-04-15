<?php $pageTitle = 'Admin — Usuarios'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-display font-bold">Usuarios <span class="text-gray-400 font-normal text-lg">(<?= $total ?>)</span></h1>
        <a href="/admin" class="btn-outline text-sm">← Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Usuario</th>
                    <th class="px-4 py-3 text-left">Correo</th>
                    <th class="px-4 py-3 text-left">Rol</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-left">Cambiar rol</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="px-4 py-3 capitalize"><?= htmlspecialchars($user['role']) ?></td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $user['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $user['is_active'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="/admin/usuarios/<?= $user['id'] ?>/rol" class="flex gap-1">
                            <?= \Helpers\Csrf::field() ?>
                            <select name="role" class="form-input form-input-sm text-xs py-1">
                                <option value="user"       <?= $user['role'] === 'user'       ? 'selected' : '' ?>>user</option>
                                <option value="moderator"  <?= $user['role'] === 'moderator'  ? 'selected' : '' ?>>moderator</option>
                                <option value="president"  <?= $user['role'] === 'president'  ? 'selected' : '' ?>>president</option>
                                <option value="admin"      <?= $user['role'] === 'admin'      ? 'selected' : '' ?>>admin</option>
                            </select>
                            <button class="btn-outline text-xs py-1 px-2">Guardar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
