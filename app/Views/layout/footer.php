</main>

<!-- Footer -->
<footer class="text-white mt-0" style="background-color: #991b1b;">
    <div class="container mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
            <div>
                <img src="/logo/galgospedia-logo450-128.png" alt="Galgospedia" class="h-10 mx-auto md:mx-0 mb-3 brightness-200 logo-animate drop-shadow-[0_2px_4px_rgba(0,0,0,0.6)]">
                <p class="text-sm text-white font-medium">La enciclopedia del Galgo Español. Registro genealógico, sementales y reproductoras.</p>
                <a href="mailto:info@galgospedia.com"
                   class="inline-flex items-center justify-center md:justify-start gap-1.5 text-sm text-galgo-gold hover:underline mt-3 w-full md:w-auto">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    info@galgospedia.com
                </a>

            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Navegación</h4>
                <ul class="space-y-1 text-sm">
                    <li><a href="/galgos" class="hover:text-galgo-gold transition">Directorio de Galgos</a></li>
                    <li><a href="/sementales" class="hover:text-galgo-gold transition">Sementales</a></li>
                    <li><a href="/reproductoras" class="hover:text-galgo-gold transition">Reproductoras</a></li>
                    <li><a href="/torneos" class="hover:text-galgo-gold transition">Torneos</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Mi cuenta</h4>
                <ul class="space-y-1 text-sm">
                    <?php if (\Services\AuthService::isLoggedIn()): ?>
                        <li><a href="/mi-perfil" class="hover:text-galgo-gold transition">Mi perfil</a></li>
                        <li><a href="/mi-billetera" class="hover:text-galgo-gold transition">Mi billetera</a></li>
                        <li><a href="/galgos/nuevo" class="hover:text-galgo-gold transition">Añadir galgo</a></li>
                        <li><a href="/logout" class="hover:text-galgo-gold transition">Cerrar sesión</a></li>
                    <?php else: ?>
                        <li><a href="/login" class="hover:text-galgo-gold transition">Iniciar sesión</a></li>
                        <li><a href="/registro" class="hover:text-galgo-gold transition">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Legal</h4>
                <ul class="space-y-1 text-sm">
                    <li><a href="/aviso-legal" class="hover:text-galgo-gold transition">Aviso Legal</a></li>
                    <li><a href="/privacidad" class="hover:text-galgo-gold transition">Política de Privacidad</a></li>
                    <li><a href="/cookies" class="hover:text-galgo-gold transition">Política de Cookies</a></li>
                    <li>
                        <button onclick="resetCookieConsent()"
                                class="hover:text-galgo-gold transition w-full md:w-auto text-center md:text-left">
                            Gestionar cookies
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white mt-8 pt-6 text-xs text-white text-center font-medium">
            &copy; <?= date('Y') ?> Galgospedia.com — Todos los derechos reservados
        </div>
    </div>
</footer>

<!-- Cookie Consent Banner -->
<?php $gaId = \Config\Config::gaId(); ?>
<div id="cookie-banner"
     class="fixed bottom-0 left-0 right-0 z-50 bg-galgo-dark border-t border-gray-700 shadow-2xl"
     style="display:none;">
    <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="text-sm text-gray-300 flex-1">
            <strong class="text-white">Usamos cookies</strong> — Las técnicas son necesarias para el funcionamiento del sitio.
            Las analíticas (Google Analytics) nos ayudan a mejorarlo, pero requieren tu consentimiento.
            <a href="/cookies" class="text-galgo-gold hover:underline ml-1">Más información</a>
        </div>
        <div class="flex gap-3 shrink-0">
            <button onclick="rejectCookies()"
                    class="px-4 py-2 text-sm border border-gray-500 text-gray-300 rounded-lg hover:border-gray-300 hover:text-white transition">
                Rechazar
            </button>
            <button onclick="acceptCookies()"
                    class="px-4 py-2 text-sm bg-galgo-gold text-galgo-dark font-semibold rounded-lg hover:opacity-90 transition">
                Aceptar
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var consent = localStorage.getItem('ga_consent');
        if (!consent) {
            document.getElementById('cookie-banner').style.display = 'block';
        }
    })();

    function acceptCookies() {
        localStorage.setItem('ga_consent', 'accepted');
        document.getElementById('cookie-banner').style.display = 'none';
        <?php if ($gaId): ?>
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', { analytics_storage: 'granted' });
        }
        <?php endif; ?>
    }

    function rejectCookies() {
        localStorage.setItem('ga_consent', 'rejected');
        document.getElementById('cookie-banner').style.display = 'none';
    }

    function resetCookieConsent() {
        localStorage.removeItem('ga_consent');
        document.getElementById('cookie-banner').style.display = 'block';
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }

    function copyBizumLanding() {
        var el = document.getElementById('bizum-number-landing');
        if (!el) return;
        navigator.clipboard.writeText('744450139').then(function () {
            el.textContent = '✓ ¡Copiado!';
            el.style.color = '#86efac';
            setTimeout(function () {
                el.textContent = '744 450 139';
                el.style.color = '';
            }, 2000);
        });
    }
</script>

<?php if (!empty($extraScripts)) echo $extraScripts; /* internal use only — never pass user input here */ ?>
</body>
</html>
