<?php
$pageTitle = 'Aviso Legal';
$pageDesc  = 'Aviso legal de Galgospedia. Información sobre el titular del sitio web y condiciones de uso.';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-12 max-w-3xl">

    <h1 class="text-3xl font-display font-bold mb-2">Aviso Legal</h1>
    <p class="text-sm text-gray-400 mb-10">Última actualización: <?= date('d/m/Y') ?></p>

    <div class="prose prose-gray max-w-none space-y-8 text-sm leading-relaxed text-gray-700">

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">1. Identificación del titular</h2>
            <p>En cumplimiento del artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y Comercio Electrónico (LSSI-CE), se informa de los datos identificativos del titular de este sitio web:</p>
            <ul class="space-y-1 mt-3">
                <li><strong>Titular:</strong> Jerry Pizango</li>
                <li><strong>NIF:</strong> 02411363C</li>
                <li><strong>Domicilio:</strong> Yeles, Toledo, España</li>
                <li><strong>Correo electrónico:</strong> <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a></li>
                <li><strong>Teléfono:</strong> +34 744 450 139</li>
                <li><strong>Dominio:</strong> <a href="https://galgospedia.com" class="text-galgo-red hover:underline">galgospedia.com</a></li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">2. Objeto y condiciones de uso</h2>
            <p>Galgospedia es una plataforma de registro genealógico del Galgo Español, sin ánimo de lucro, orientada a criadores, clubs y aficionados. Permite registrar galgos, construir árboles genealógicos, gestionar clubs cinegéticos y consultar sementales y reproductoras.</p>
            <p class="mt-2">El acceso y uso de este sitio web implica la aceptación íntegra de las presentes condiciones. El titular se reserva el derecho a modificar en cualquier momento la presentación, configuración y contenido del sitio, así como las presentes condiciones legales.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">3. Menores de edad</h2>
            <p>Galgospedia no está dirigida a menores de <strong>14 años</strong>. De conformidad con el artículo 7 del Reglamento General de Protección de Datos (RGPD) y la legislación española, el tratamiento de datos de menores de 14 años requiere el consentimiento de los padres o tutores legales.</p>
            <p class="mt-2">Si eres menor de 14 años, no debes registrarte ni facilitar ningún dato personal. Si detectamos que un usuario es menor de dicha edad, procederemos a eliminar su cuenta y los datos asociados. Los padres o tutores que detecten que su hijo menor ha creado una cuenta pueden solicitarnos su eliminación en <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">4. Propiedad intelectual e industrial</h2>
            <p>Todos los contenidos del sitio web (diseño, código fuente, logotipos, textos e imágenes corporativas) son propiedad de Galgospedia o de sus respectivos autores, y están protegidos por la legislación española e internacional sobre propiedad intelectual e industrial.</p>
            <p class="mt-2">Las fotografías e información de galgos son aportadas por los propios usuarios, quienes declaran ser titulares o disponer de los derechos necesarios para publicarlas. Galgospedia no se responsabiliza del contenido aportado por terceros.</p>
            <p class="mt-2">Queda prohibida la reproducción, distribución, comunicación pública, transformación o extracción masiva de los contenidos sin autorización expresa y por escrito del titular.</p>
            <p class="mt-2"><strong>Uso de scraping o extracción automatizada:</strong> Queda expresamente prohibido el uso de robots, scrapers, arañas web o cualquier herramienta automatizada para extraer datos de Galgospedia sin autorización previa y escrita del titular.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">5. Reclamaciones por infracción de derechos (DMCA / Propiedad Intelectual)</h2>
            <p>Si consideras que algún contenido publicado en Galgospedia infringe tus derechos de propiedad intelectual, puedes notificárnoslo enviando un correo a <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a> con la siguiente información:</p>
            <ul class="list-disc pl-6 space-y-1 mt-2">
                <li>Identificación del contenido protegido y del contenido infractor.</li>
                <li>Datos de contacto del titular de los derechos.</li>
                <li>Declaración de buena fe de que el uso no está autorizado.</li>
            </ul>
            <p class="mt-2">Tramitaremos tu solicitud en un plazo máximo de <strong>15 días hábiles</strong>.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">6. Responsabilidad</h2>
            <p>Galgospedia no garantiza la disponibilidad continua del servicio ni se hace responsable de los daños que pudieran derivarse de interrupciones, errores técnicos o contenidos incorrectos introducidos por los usuarios.</p>
            <p class="mt-2">Los usuarios son responsables del uso correcto del servicio y de la veracidad de los datos que introducen. Cualquier uso fraudulento, contrario a la legislación vigente o a estas condiciones podrá dar lugar a la suspensión inmediata de la cuenta y, en su caso, a las acciones legales oportunas.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">7. Resolución de disputas en línea (UE)</h2>
            <p>De conformidad con el Reglamento (UE) nº 524/2013, la Comisión Europea pone a disposición de los consumidores europeos una plataforma de resolución de litigios en línea (ODR) accesible en: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener" class="text-galgo-red hover:underline">ec.europa.eu/consumers/odr</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">8. Legislación aplicable y jurisdicción</h2>
            <p>Este aviso legal se rige por la legislación española. Para cualquier controversia derivada del uso del sitio web, las partes se someten a los juzgados y tribunales de Toledo (España), con renuncia expresa a cualquier otro fuero que pudiera corresponderles, sin perjuicio de los derechos que la normativa vigente reconozca a los consumidores.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">9. Contacto</h2>
            <p>Para cualquier consulta relacionada con este aviso legal, puede contactar con nosotros en <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a> o llamando al +34 744 450 139.</p>
        </section>

    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
