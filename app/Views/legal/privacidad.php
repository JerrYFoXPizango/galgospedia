<?php
$pageTitle = 'Política de Privacidad';
$pageDesc  = 'Política de privacidad de Galgospedia. Información sobre el tratamiento de tus datos personales conforme al RGPD.';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-12 max-w-3xl">

    <h1 class="text-3xl font-display font-bold mb-2">Política de Privacidad</h1>
    <p class="text-sm text-gray-400 mb-10">Última actualización: <?= date('d/m/Y') ?></p>

    <div class="prose prose-gray max-w-none space-y-8 text-sm leading-relaxed text-gray-700">

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">1. Responsable del tratamiento</h2>
            <ul class="space-y-1">
                <li><strong>Titular:</strong> Jerry Pizango</li>
                <li><strong>Correo electrónico:</strong> <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a></li>
                <li><strong>Teléfono:</strong> +34 744 450 139</li>
                <li><strong>País:</strong> España</li>
                <li><strong>Sitio web:</strong> <a href="https://galgospedia.com" class="text-galgo-red hover:underline">galgospedia.com</a></li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">2. Datos que recogemos</h2>
            <p>En Galgospedia recopilamos únicamente los datos necesarios para el funcionamiento del servicio:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><strong>Datos de cuenta:</strong> nombre de usuario, dirección de correo electrónico y contraseña (almacenada cifrada).</li>
                <li><strong>Datos de galgos:</strong> nombre, fecha de nacimiento, fotografías, árbol genealógico y notas que el usuario introduce voluntariamente.</li>
                <li><strong>Datos de club:</strong> nombre del club o coto, provincia y comunidad autónoma, documentos de la bóveda (solo visibles para el presidente del club).</li>
                <li><strong>Billetera personal:</strong> documentos que el propio usuario sube (licencias, títulos), almacenados de forma privada y accesibles solo para el titular.</li>
                <li><strong>Datos técnicos:</strong> dirección IP y cookies de sesión necesarias para el funcionamiento de la plataforma.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">3. Finalidad y base legal</h2>
            <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Finalidad</th>
                        <th class="px-3 py-2 text-left font-semibold">Base legal (RGPD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr><td class="px-3 py-2">Gestión de cuenta y acceso</td><td class="px-3 py-2">Ejecución de contrato — Art. 6.1.b</td></tr>
                    <tr><td class="px-3 py-2">Registro genealógico de galgos</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Gestión de clubs y socios</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Almacenamiento de documentos privados</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Estadísticas de uso (Google Analytics)</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Comunicaciones del servicio (email)</td><td class="px-3 py-2">Interés legítimo — Art. 6.1.f</td></tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">4. Plazo de conservación</h2>
            <p>Los datos se conservan mientras la cuenta esté activa. Si solicitas la eliminación de tu cuenta, tus datos personales se borrarán en un plazo máximo de <strong>30 días</strong>, salvo que la normativa fiscal o legal obligue a su conservación durante un período superior.</p>
            <p class="mt-2">Las fotografías de galgos marcadas como públicas pueden permanecer visibles hasta que el usuario titular las elimine explícitamente.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">5. Destinatarios y transferencias internacionales</h2>
            <p>No cedemos tus datos a terceros con fines comerciales. Los únicos encargados del tratamiento son:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><strong>Cloudflare R2</strong> — almacenamiento de imágenes y documentos. Servidor en región Europa Occidental (WEUR). Cloudflare Inc. cumple con el marco EU-US Data Privacy Framework.</li>
                <li><strong>Google Analytics 4</strong> — estadísticas de uso (solo si el usuario otorga consentimiento mediante el banner de cookies). Google LLC, con servidor en la UE cuando está disponible.</li>
                <li><strong>Banahosting</strong> — servidor de correo electrónico transaccional (verificación de cuenta, recuperación de contraseña).</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">6. Tus derechos</h2>
            <p>En virtud del RGPD tienes derecho a:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><strong>Acceso:</strong> solicitar qué datos tenemos sobre ti.</li>
                <li><strong>Rectificación:</strong> corregir datos inexactos o incompletos.</li>
                <li><strong>Supresión ("derecho al olvido"):</strong> solicitar la eliminación de tus datos.</li>
                <li><strong>Portabilidad:</strong> recibir tus datos en formato estructurado y legible.</li>
                <li><strong>Oposición y limitación:</strong> oponerte a determinados tratamientos o solicitar que se limiten.</li>
                <li><strong>Retirar el consentimiento</strong> en cualquier momento, sin que ello afecte a la licitud del tratamiento previo.</li>
            </ul>
            <p class="mt-3">Para ejercer cualquiera de estos derechos, escríbenos a <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a>. Responderemos en el plazo máximo de <strong>30 días</strong>.</p>
            <p class="mt-2">Si consideras que el tratamiento no es conforme al RGPD, puedes presentar una reclamación ante la <strong>Agencia Española de Protección de Datos (AEPD)</strong> en <a href="https://www.aepd.es" target="_blank" rel="noopener" class="text-galgo-red hover:underline">www.aepd.es</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">7. Seguridad</h2>
            <p>Aplicamos medidas técnicas y organizativas adecuadas para proteger tus datos frente a accesos no autorizados, pérdida o destrucción: contraseñas cifradas con bcrypt, comunicaciones HTTPS, acceso restringido a documentos privados mediante URLs firmadas de un solo uso y duración limitada.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">8. Cookies</h2>
            <p>Para más información sobre el uso de cookies, consulta nuestra <a href="/cookies" class="text-galgo-red hover:underline">Política de Cookies</a>.</p>
        </section>

    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
