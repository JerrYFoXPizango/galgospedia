<?php $pageTitle = 'Registrarse'; require APP_PATH . '/Views/layout/header.php'; ?>

<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-20 h-20 mx-auto mb-4">
            <h1 class="text-2xl font-display font-bold">Crear cuenta</h1>
            <p class="text-gray-500 text-sm mt-1">Únete a la comunidad del Galgo Español</p>
        </div>

        <form method="POST" action="/registro" class="card space-y-4">
            <?= \Helpers\Csrf::field() ?>

            <div>
                <label class="form-label" for="username">Nombre de usuario</label>
                <input type="text" id="username" name="username" required autofocus
                       class="form-input" placeholder="mi_usuario" minlength="3" maxlength="50">
            </div>
            <div>
                <label class="form-label" for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required class="form-input" placeholder="tu@correo.com">
            </div>
            <div>
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" id="password" name="password" required
                       class="form-input" placeholder="••••••••" minlength="8">
            </div>
            <div>
                <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       class="form-input" placeholder="••••••••">
            </div>

            <!-- Club / Coto -->
            <div x-data="{ choice: 'none' }" class="border-t border-gray-100 pt-4 space-y-3">
                <p class="form-label mb-1">¿Perteneces a un club o coto?</p>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="club_action" value="none" x-model="choice" checked>
                    <span class="text-sm text-gray-700">No, por ahora no</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="club_action" value="join" x-model="choice">
                    <span class="text-sm text-gray-700">Quiero unirme a un club existente</span>
                </label>

                <!-- Club selector -->
                <div x-show="choice === 'join'" x-transition class="pl-6">
                    <?php if (empty($activeClubs)): ?>
                        <p class="text-xs text-gray-400">No hay clubs activos en este momento.</p>
                    <?php else: ?>
                        <label class="form-label" for="club_id">Selecciona tu club</label>
                        <select id="club_id" name="club_id" class="form-input">
                            <option value="">-- Elige un club --</option>
                            <?php foreach ($activeClubs as $c): ?>
                                <?php
                                $loc = array_filter([$c['province'] ?? null, $c['autonomous_community'] ?? null]);
                                $label = $c['name'] . ($loc ? ' (' . implode(', ', $loc) . ')' : '');
                                ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Tu solicitud quedará pendiente hasta que el presidente la apruebe.</p>
                    <?php endif; ?>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="club_action" value="create" x-model="choice">
                    <span class="text-sm text-gray-700">Quiero registrar mi club o coto</span>
                </label>

                <!-- Create club notice -->
                <div x-show="choice === 'create'" x-transition class="pl-6">
                    <p class="text-xs text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                        Tras registrarte, te llevaremos al formulario de solicitud de club.
                        Un administrador lo revisará y te asignará el rol de presidente.
                    </p>
                </div>
            </div>

            <!-- Aceptación de privacidad (RGPD) -->
            <div class="border-t border-gray-100 pt-4">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="accept_privacy" required
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-galgo-red focus:ring-galgo-red">
                    <span class="text-sm text-gray-600">
                        He leído y acepto la
                        <a href="/privacidad" target="_blank" class="text-galgo-red hover:underline font-medium">Política de Privacidad</a>
                        y el
                        <a href="/aviso-legal" target="_blank" class="text-galgo-red hover:underline font-medium">Aviso Legal</a>.
                    </span>
                </label>
            </div>

            <button type="submit" class="btn-red w-full py-3 text-base">Crear cuenta</button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            ¿Ya tienes cuenta?
            <a href="/login" class="text-galgo-red font-medium hover:underline">Inicia sesión</a>
        </p>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
