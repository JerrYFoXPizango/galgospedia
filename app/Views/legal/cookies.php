<?php
$pageTitle = 'Política de Cookies';
$pageDesc  = 'Política de cookies de Galgospedia. Información sobre qué cookies usamos y cómo gestionarlas.';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-12 max-w-3xl">

    <h1 class="text-3xl font-display font-bold mb-2">Política de Cookies</h1>
    <p class="text-sm text-gray-400 mb-10">Última actualización: <?= date('d/m/Y') ?></p>

    <div class="prose prose-gray max-w-none space-y-8 text-sm leading-relaxed text-gray-700">

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">1. ¿Qué son las cookies?</h2>
            <p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Sirven para mantener tu sesión activa, recordar tus preferencias o recopilar estadísticas de uso anónimas.</p>
            <p class="mt-2">De conformidad con la Ley 34/2002 (LSSI-CE), el Reglamento General de Protección de Datos (RGPD) y la Directiva ePrivacy (2002/58/CE), te informamos sobre las cookies que utilizamos y cómo puedes gestionarlas.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">2. Cookies que utiliza Galgospedia</h2>

            <h3 class="font-semibold text-gray-800 mt-4 mb-2">A) Cookies técnicas — no requieren consentimiento</h3>
            <p>Son imprescindibles para el funcionamiento del sitio. Sin ellas no podrías iniciar sesión ni navegar de forma segura. Están exentas de consentimiento según el artículo 22.2 de la LSSI-CE.</p>
            <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden mt-3">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Cookie</th>
                        <th class="px-3 py-2 text-left font-semibold">Proveedor</th>
                        <th class="px-3 py-2 text-left font-semibold">Finalidad</th>
                        <th class="px-3 py-2 text-left font-semibold">Duración</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-3 py-2 font-mono">PHPSESSID</td>
                        <td class="px-3 py-2">Galgospedia</td>
                        <td class="px-3 py-2">Mantener la sesión iniciada</td>
                        <td class="px-3 py-2">Sesión</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">csrf_token</td>
                        <td class="px-3 py-2">Galgospedia</td>
                        <td class="px-3 py-2">Protección contra ataques CSRF en formularios</td>
                        <td class="px-3 py-2">Sesión</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">cookie_consent</td>
                        <td class="px-3 py-2">Galgospedia</td>
                        <td class="px-3 py-2">Guardar tu preferencia de cookies (aceptar/rechazar)</td>
                        <td class="px-3 py-2">1 año</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">__cf_bm</td>
                        <td class="px-3 py-2">Cloudflare</td>
                        <td class="px-3 py-2">Protección contra bots y gestión de tráfico (seguridad)</td>
                        <td class="px-3 py-2">30 minutos</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">_cfuvid</td>
                        <td class="px-3 py-2">Cloudflare</td>
                        <td class="px-3 py-2">Gestión de limitación de tasa de peticiones (seguridad)</td>
                        <td class="px-3 py-2">Sesión</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-xs text-gray-500">Las cookies de Cloudflare son necesarias para la seguridad y disponibilidad del servicio. Cloudflare Inc. cumple con el RGPD y el marco EU-US Data Privacy Framework. Más info en <a href="https://www.cloudflare.com/privacypolicy/" target="_blank" rel="noopener" class="text-galgo-red hover:underline">cloudflare.com/privacypolicy</a>.</p>

            <h3 class="font-semibold text-gray-800 mt-6 mb-2">B) Cookies analíticas — requieren consentimiento</h3>
            <p>Solo se activan si aceptas las cookies mediante el banner de consentimiento. Nos permiten entender cómo se usa el sitio para mejorarlo, sin identificarte personalmente.</p>
            <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden mt-3">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Cookie</th>
                        <th class="px-3 py-2 text-left font-semibold">Proveedor</th>
                        <th class="px-3 py-2 text-left font-semibold">Finalidad</th>
                        <th class="px-3 py-2 text-left font-semibold">Duración</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-3 py-2 font-mono">_ga</td>
                        <td class="px-3 py-2">Google Analytics</td>
                        <td class="px-3 py-2">Identificar sesiones únicas de forma anónima</td>
                        <td class="px-3 py-2">2 años</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">_ga_*</td>
                        <td class="px-3 py-2">Google Analytics</td>
                        <td class="px-3 py-2">Estado de sesión de Analytics</td>
                        <td class="px-3 py-2">2 años</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-xs text-gray-500">Google Analytics 4 se configura con IP anonimizada y sin uso de datos para publicidad personalizada. Puedes consultar la política de privacidad de Google en <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-galgo-red hover:underline">policies.google.com/privacy</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">3. Cómo gestionar tu consentimiento</h2>
            <p>Al entrar por primera vez en Galgospedia verás un banner donde puedes <strong>aceptar o rechazar</strong> las cookies analíticas. Puedes cambiar tu preferencia en cualquier momento:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li>Haciendo clic en <strong>"Gestionar cookies"</strong> en el pie de página.</li>
                <li>Borrando las cookies de tu navegador (el banner volverá a aparecer).</li>
            </ul>
            <p class="mt-2">Rechazar las cookies analíticas <strong>no afecta</strong> al funcionamiento normal del sitio.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">4. Cómo desactivar cookies en tu navegador</h2>
            <p>Puedes desactivar o eliminar cookies directamente desde la configuración de tu navegador:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" class="text-galgo-red hover:underline">Google Chrome</a></li>
                <li><a href="https://support.mozilla.org/es/kb/cookies-informacion-que-los-sitios-web-guardan-en-" target="_blank" rel="noopener" class="text-galgo-red hover:underline">Mozilla Firefox</a></li>
                <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener" class="text-galgo-red hover:underline">Safari</a></li>
                <li><a href="https://support.microsoft.com/es-es/windows/eliminar-y-administrar-cookies-168dab11-0753-043d-7c16-ede5947fc64d" target="_blank" rel="noopener" class="text-galgo-red hover:underline">Microsoft Edge</a></li>
            </ul>
            <p class="mt-2 text-xs text-gray-500">Ten en cuenta que desactivar las cookies técnicas puede impedir el inicio de sesión y otros funcionamientos esenciales del sitio.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">5. Más información</h2>
            <p>Para cualquier consulta sobre el uso de cookies, contacta con nosotros en <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a>. Consulta también nuestra <a href="/privacidad" class="text-galgo-red hover:underline">Política de Privacidad</a> y el <a href="/aviso-legal" class="text-galgo-red hover:underline">Aviso Legal</a>.</p>
        </section>

    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
