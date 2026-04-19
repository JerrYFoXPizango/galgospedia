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
                <li><strong>NIF:</strong> 02411363C</li>
                <li><strong>Domicilio:</strong> Yeles, Toledo, España</li>
                <li><strong>Correo electrónico:</strong> <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a></li>
                <li><strong>Teléfono:</strong> +34 744 450 139</li>
                <li><strong>Sitio web:</strong> <a href="https://galgospedia.com" class="text-galgo-red hover:underline">galgospedia.com</a></li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">2. Datos que recogemos</h2>
            <p>En Galgospedia recopilamos únicamente los datos necesarios para el funcionamiento del servicio:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><strong>Datos de cuenta:</strong> nombre de usuario, dirección de correo electrónico y contraseña (almacenada cifrada con bcrypt).</li>
                <li><strong>Datos de galgos:</strong> nombre, fecha de nacimiento, fotografías, árbol genealógico y notas introducidos voluntariamente por el usuario.</li>
                <li><strong>Datos de club:</strong> nombre del club, provincia y comunidad autónoma, documentos de la bóveda (visibles solo para el presidente del club).</li>
                <li><strong>Billetera personal:</strong> documentos subidos por el usuario (licencias, títulos), accesibles únicamente para el titular de la cuenta.</li>
                <li><strong>Datos técnicos:</strong> dirección IP, cookies de sesión y registros de acceso necesarios para la seguridad y funcionamiento de la plataforma.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">3. Menores de edad</h2>
            <p>Galgospedia no está dirigida a menores de <strong>14 años</strong>. No recopilamos conscientemente datos de menores de esa edad. Si eres padre o tutor y crees que tu hijo ha facilitado datos personales, contáctanos en <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a> y procederemos a su eliminación inmediata.</p>
            <p class="mt-2">Para usuarios entre 14 y 18 años, se recomienda que sus padres o tutores revisen estas condiciones y den su conformidad.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">4. Finalidad y base legal</h2>
            <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Finalidad</th>
                        <th class="px-3 py-2 text-left font-semibold">Base legal (RGPD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr><td class="px-3 py-2">Gestión de cuenta y acceso al servicio</td><td class="px-3 py-2">Ejecución de contrato — Art. 6.1.b</td></tr>
                    <tr><td class="px-3 py-2">Registro genealógico de galgos</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Gestión de clubs y socios</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Almacenamiento de documentos privados</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Estadísticas de uso (Google Analytics)</td><td class="px-3 py-2">Consentimiento — Art. 6.1.a</td></tr>
                    <tr><td class="px-3 py-2">Comunicaciones del servicio (email)</td><td class="px-3 py-2">Interés legítimo — Art. 6.1.f</td></tr>
                    <tr><td class="px-3 py-2">Seguridad y prevención del fraude</td><td class="px-3 py-2">Interés legítimo — Art. 6.1.f</td></tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">5. Plazo de conservación</h2>
            <p>Los datos se conservan mientras la cuenta esté activa. Si solicitas la eliminación de tu cuenta, tus datos personales se borrarán en un plazo máximo de <strong>30 días</strong>, salvo que la normativa fiscal o legal obligue a su conservación durante un período superior (máximo 5 años según legislación fiscal española).</p>
            <p class="mt-2">Los registros técnicos (logs de acceso) se conservan un máximo de <strong>12 meses</strong> por motivos de seguridad.</p>
            <p class="mt-2">Las fotografías de galgos marcadas como públicas permanecerán visibles hasta que el usuario titular las elimine explícitamente.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">6. Destinatarios y transferencias internacionales</h2>
            <p>No cedemos tus datos a terceros con fines comerciales. Los únicos encargados del tratamiento son:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><strong>Cloudflare R2</strong> — almacenamiento de imágenes y documentos. Región Europa Occidental (WEUR). Cloudflare Inc. cumple con el marco EU-US Data Privacy Framework (adecuación de la Comisión Europea).</li>
                <li><strong>Cloudflare CDN/DNS</strong> — protección y distribución del tráfico web. Puede procesar temporalmente metadatos de conexión. Cumple con RGPD y EU-US DPF.</li>
                <li><strong>Google Analytics 4</strong> — estadísticas de uso (solo con consentimiento previo). Google LLC, con servidor en la UE cuando está disponible. IP anonimizada y sin uso para publicidad personalizada.</li>
                <li><strong>Banahosting</strong> — servidor web y correo transaccional. Servidores en España.</li>
            </ul>
            <p class="mt-3 text-xs text-gray-500">Todas las transferencias internacionales se realizan con las garantías adecuadas previstas en el Capítulo V del RGPD (cláusulas contractuales tipo o decisiones de adecuación).</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">7. Decisiones automatizadas</h2>
            <p>Galgospedia <strong>no realiza decisiones automatizadas</strong> ni elaboración de perfiles con efectos jurídicos o significativos sobre los usuarios, conforme al artículo 22 del RGPD.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">8. Violaciones de seguridad (brechas de datos)</h2>
            <p>En caso de producirse una violación de seguridad que afecte a tus datos personales y suponga un riesgo para tus derechos y libertades, lo notificaremos a la <strong>Agencia Española de Protección de Datos (AEPD)</strong> en un plazo máximo de <strong>72 horas</strong>, conforme al artículo 33 del RGPD.</p>
            <p class="mt-2">Si la brecha supone un alto riesgo para los afectados, también te notificaremos directamente sin dilación indebida.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">9. Tus derechos (RGPD)</h2>
            <p>En virtud del RGPD tienes derecho a:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li><strong>Acceso:</strong> solicitar qué datos tenemos sobre ti.</li>
                <li><strong>Rectificación:</strong> corregir datos inexactos o incompletos.</li>
                <li><strong>Supresión ("derecho al olvido"):</strong> solicitar la eliminación de tus datos.</li>
                <li><strong>Portabilidad:</strong> recibir tus datos en formato estructurado y legible (JSON/CSV).</li>
                <li><strong>Oposición y limitación:</strong> oponerte a determinados tratamientos o solicitar que se limiten.</li>
                <li><strong>Retirar el consentimiento</strong> en cualquier momento, sin que ello afecte a la licitud del tratamiento previo.</li>
            </ul>
            <p class="mt-3">Para ejercer cualquiera de estos derechos, escríbenos a <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a>. Responderemos en el plazo máximo de <strong>30 días</strong> (prorrogable 2 meses en casos complejos).</p>
            <p class="mt-2">Si consideras que el tratamiento no es conforme al RGPD, puedes presentar una reclamación ante la <strong>Agencia Española de Protección de Datos (AEPD)</strong> en <a href="https://www.aepd.es" target="_blank" rel="noopener" class="text-galgo-red hover:underline">www.aepd.es</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">10. Usuarios fuera de la Unión Europea</h2>
            <p class="font-semibold text-gray-800">Residentes en California (EEUU) — CCPA/CPRA:</p>
            <p class="mt-1">Galgospedia <strong>no vende ni comparte</strong> datos personales de sus usuarios con terceros con fines comerciales o publicitarios, de conformidad con la California Consumer Privacy Act (CCPA) y la California Privacy Rights Act (CPRA). Los residentes en California tienen derecho a solicitar la información que conservamos sobre ellos y a solicitar su eliminación, escribiendo a <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a>.</p>
            <p class="mt-3 font-semibold text-gray-800">Resto del mundo:</p>
            <p class="mt-1">Independientemente de tu país de residencia, tratamos tus datos con las garantías del RGPD europeo, que es uno de los marcos de protección de datos más exigentes del mundo. Puedes ejercer los derechos indicados en el apartado anterior contactando con nosotros.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">11. Seguridad</h2>
            <p>Aplicamos medidas técnicas y organizativas adecuadas para proteger tus datos:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li>Contraseñas cifradas con <strong>bcrypt</strong>.</li>
                <li>Comunicaciones cifradas mediante <strong>HTTPS/TLS</strong>.</li>
                <li>Acceso restringido a documentos privados mediante URLs firmadas de un solo uso y duración limitada.</li>
                <li>Protección frente a ataques CSRF en todos los formularios.</li>
                <li>Acceso a la base de datos restringido al servidor de aplicaciones.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">12. Cookies</h2>
            <p>Para más información sobre el uso de cookies, consulta nuestra <a href="/cookies" class="text-galgo-red hover:underline">Política de Cookies</a>.</p>
        </section>

    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
