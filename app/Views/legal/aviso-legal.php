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
                <li><strong>Correo electrónico:</strong> <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a></li>
                <li><strong>Teléfono:</strong> +34 744 450 139</li>
                <li><strong>País:</strong> España</li>
                <li><strong>Dominio:</strong> <a href="https://galgospedia.com" class="text-galgo-red hover:underline">galgospedia.com</a></li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">2. Objeto y condiciones de uso</h2>
            <p>Galgospedia es una plataforma de registro genealógico del Galgo Español. Permite a criadores, clubs y aficionados registrar galgos, construir árboles genealógicos, gestionar clubs cinegéticos y consultar sementales y reproductoras.</p>
            <p class="mt-2">El acceso y uso de este sitio web implica la aceptación de las presentes condiciones. El titular se reserva el derecho a modificar, en cualquier momento, la presentación y configuración del sitio, así como las presentes condiciones.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">3. Propiedad intelectual e industrial</h2>
            <p>Todos los contenidos del sitio web (diseño, código fuente, logotipos, textos e imágenes corporativas) son propiedad de Galgospedia o de sus respectivos autores, y están protegidos por la legislación española e internacional sobre propiedad intelectual e industrial.</p>
            <p class="mt-2">Las fotografías e información de galgos son aportadas por los propios usuarios, quienes declaran ser titulares o disponer de los derechos necesarios para publicarlas. Galgospedia no se responsabiliza del contenido aportado por terceros.</p>
            <p class="mt-2">Queda prohibida la reproducción, distribución, comunicación pública o transformación de los contenidos sin autorización expresa del titular.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">4. Responsabilidad</h2>
            <p>Galgospedia no garantiza la disponibilidad continua del servicio ni se hace responsable de los daños que pudieran derivarse de interrupciones, errores técnicos o contenidos incorrectos introducidos por los usuarios.</p>
            <p class="mt-2">Los usuarios son responsables del uso correcto del servicio y de la veracidad de los datos que introducen. Cualquier uso fraudulento o contrario a la legislación vigente podrá dar lugar a la suspensión de la cuenta.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">5. Legislación aplicable</h2>
            <p>Este aviso legal se rige por la legislación española. Para cualquier controversia derivada del uso del sitio web, las partes se someten a los juzgados y tribunales de España, con renuncia expresa a cualquier otro fuero que pudiera corresponderles.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-2">6. Contacto</h2>
            <p>Para cualquier consulta relacionada con este aviso legal, puede contactar con nosotros en <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline">info@galgospedia.com</a>.</p>
        </section>

    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
