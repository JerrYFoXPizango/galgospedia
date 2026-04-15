<?php
$pageTitle = 'Galgospedia — Registro Genealógico del Galgo Español';
$pageDesc  = 'La base de datos más completa del Galgo Español. Árboles genealógicos, sementales certificados, reproductoras y registro de campeones. Gratis y en español.';

// Schema.org — WebSite + Organization + FAQPage
$extraHead = '
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "name": "Galgospedia",
      "url": "https://galgospedia.com",
      "description": "Registro genealógico del Galgo Español: árboles genealógicos, sementales, reproductoras y campeones.",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://galgospedia.com/galgos?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "Organization",
      "name": "Galgospedia",
      "url": "https://galgospedia.com",
      "logo": "https://galgospedia.com/logo/logo930-930.png",
      "email": "info@galgospedia.com",
      "description": "Enciclopedia y registro genealógico del Galgo Español. Sementales, reproductoras, torneos y carreras de galgos en España.",
      "sameAs": [],
      "knowsAbout": ["Galgo Español", "Carreras de galgos", "Genealogía canina", "Sementales de galgo", "Torneos de galgos"]
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "¿Qué es el Galgo Español?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "El Galgo Español (Canis lupus familiaris) es una raza canina autóctona de España, considerada una de las más antiguas del mundo. Se caracteriza por su cuerpo esbelto, patas largas y gran velocidad, siendo utilizado históricamente en la caza mayor por su extraordinaria vista y capacidad de rastreo."
          }
        },
        {
          "@type": "Question",
          "name": "¿A qué velocidad corre el Galgo Español?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "El Galgo Español puede alcanzar velocidades de hasta 60-65 km/h en carrera, lo que lo convierte en uno de los perros más rápidos del mundo, superando incluso al Greyhound inglés en pruebas de campo abierto gracias a su resistencia y capacidad de giro."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cuánto vive un Galgo Español?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "La esperanza de vida media del Galgo Español es de 10 a 14 años. Con cuidados adecuados, alimentación equilibrada y seguimiento veterinario, muchos ejemplares alcanzan los 12-13 años en plena forma."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cuál es la diferencia entre el Galgo Español y el Greyhound inglés?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "El Galgo Español es una raza autóctona española de origen antiguo, más rústico y resistente que el Greyhound inglés. El Greyhound fue seleccionado para pistas de carreras (velocidad en línea recta), mientras que el Galgo Español fue desarrollado para la caza en campo abierto, siendo superior en resistencia, cambios de dirección y trabajo en terrenos irregulares."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cómo puedo registrar mi galgo en Galgospedia?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Crear una cuenta en Galgospedia es completamente gratuito. Una vez registrado, puedes añadir tu galgo con su foto, fecha de nacimiento y datos de origen, vincular padre y madre para construir el árbol genealógico, y si es semental o reproductora, solicitar al administrador que lo marque como tal para que aparezca en el directorio oficial."
          }
        },
        {
          "@type": "Question",
          "name": "¿Qué información muestra el árbol genealógico de un galgo?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "El árbol genealógico de Galgospedia muestra hasta varias generaciones de ascendencia del galgo, incluyendo nombre, fotografía y datos de cada antepasado registrado. También calcula el Coeficiente de Consanguinidad (COI), un dato fundamental para criadores que quieren evitar la endogamia en sus camadas."
          }
        }
      ]
    }
  ]
}
</script>';

require APP_PATH . '/Views/layout/header.php';
?>

<!-- Hero -->
<section class="relative text-white overflow-hidden hero-animated">

    <!-- Animated gradient background -->
    <div class="absolute inset-0 hero-gradient"></div>

    <!-- Speed lines layer -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <?php for ($i = 0; $i < 18; $i++):
            $top    = rand(3, 97);
            $width  = rand(12, 45);
            $delay  = round(($i * 0.37) + lcg_value() * 0.5, 2);
            $dur    = round(lcg_value() * 1.8 + 2.0, 2);
            $opacity = round(lcg_value() * 0.13 + 0.05, 2);
        ?>
        <div class="speed-line"
             style="top:<?= $top ?>%;width:<?= $width ?>%;animation-delay:<?= $delay ?>s;animation-duration:<?= $dur ?>s;opacity:<?= $opacity ?>"></div>
        <?php endfor; ?>
    </div>

    <div class="relative container mx-auto px-4 py-20 text-center">
        <h1 class="font-display text-5xl md:text-6xl font-bold mb-6 leading-tight">
            El Epicentro del<br>
            <span class="text-galgo-gold">Galgo Español</span>
        </h1>
        <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
            Mucho más que una base de datos: es la casa de todos los galgueros. Registra tus ejemplares,
            traza su genealogía, gestiona tu cuadra y conecta con aficionados de todo el mundo.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/galgos" class="btn-gold px-8 py-3 text-lg">Explorar Galgos</a>
            <?php if (!\Services\AuthService::isLoggedIn()): ?>
                <a href="/registro" class="btn-outline px-8 py-3 text-lg">Registrarse Gratis</a>
            <?php else: ?>
                <a href="/galgos/nuevo" class="btn-outline px-8 py-3 text-lg">+ Añadir mi Galgo</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats bar -->
<section class="bg-galgo-red text-white py-4">
    <div class="container mx-auto px-4 flex flex-wrap justify-center gap-8 text-center text-sm font-medium">
        <div><span class="text-2xl font-bold text-galgo-gold"><?= $totalDogs ?></span> Galgos registrados</div>
        <div><span class="text-2xl font-bold text-galgo-gold"><?= $totalStallions ?></span> Sementales</div>
        <div><span class="text-2xl font-bold text-galgo-gold"><?= $totalBroodmares ?></span> Reproductoras</div>
    </div>
</section>

<!-- Galgos añadidos recientemente -->
<?php if (!empty($recentDogs['data'])): ?>
<section class="container mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-display font-bold text-galgo-dark">Últimos Galgos Registrados</h2>
        <a href="/galgos" class="text-galgo-red hover:underline text-sm font-medium">Ver directorio →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3">
        <?php foreach ($recentDogs['data'] as $d): ?>
        <a href="/galgos/<?= htmlspecialchars($d['slug']) ?>" class="group text-center">
            <div class="aspect-square rounded-xl overflow-hidden bg-gray-200 mb-2 ring-1 ring-gray-200 group-hover:ring-2 group-hover:ring-galgo-red transition">
                <?php if ($d['photo_thumb']): ?>
                    <img src="<?= \Helpers\Asset::url($d['photo_thumb']) ?>"
                         alt="Galgo Español <?= htmlspecialchars($d['name']) ?>"
                         class="w-full h-full object-contain group-hover:scale-105 transition duration-300" loading="lazy">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-10 h-10 object-contain opacity-25">
                    </div>
                <?php endif; ?>
            </div>
            <p class="text-xs font-semibold truncate text-gray-700"><?= htmlspecialchars($d['name']) ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Sementales destacados -->
<?php if (!empty($stallions)): ?>
<section class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-display font-bold text-galgo-dark">Sementales Destacados</h2>
            <a href="/sementales" class="text-galgo-red hover:underline text-sm font-medium">Ver todos →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            <?php foreach ($stallions as $s): ?>
            <a href="/galgos/<?= htmlspecialchars($s['slug']) ?>" class="group text-center">
                <div class="aspect-square rounded-xl overflow-hidden bg-gray-200 mb-2 ring-2 ring-galgo-gold group-hover:ring-4 transition">
                    <?php if ($s['photo_webp']): ?>
                        <img src="<?= \Helpers\Asset::url($s['photo_webp']) ?>"
                             alt="Semental Galgo Español <?= htmlspecialchars($s['name']) ?>"
                             class="w-full h-full object-contain group-hover:scale-105 transition duration-300" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-12 h-12 object-contain opacity-25">
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-xs font-semibold truncate text-gray-700"><?= htmlspecialchars($s['name']) ?></p>
                <?php if (!empty($s['club']) || !empty($s['country'])): ?>
                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars(implode(' · ', array_filter([$s['club'] ?? null, $s['country'] ?? null]))) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Reproductoras destacadas -->
<?php if (!empty($broodmares)): ?>
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-display font-bold text-galgo-dark">Reproductoras Destacadas</h2>
            <a href="/reproductoras" class="text-galgo-red hover:underline text-sm font-medium">Ver todas →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            <?php foreach ($broodmares as $b): ?>
            <a href="/galgos/<?= htmlspecialchars($b['slug']) ?>" class="group text-center">
                <div class="aspect-square rounded-xl overflow-hidden bg-gray-200 mb-2 ring-2 ring-galgo-red group-hover:ring-4 transition">
                    <?php if ($b['photo_webp']): ?>
                        <img src="<?= \Helpers\Asset::url($b['photo_webp']) ?>"
                             alt="Reproductora Galgo Español <?= htmlspecialchars($b['name']) ?>"
                             class="w-full h-full object-contain group-hover:scale-105 transition duration-300" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <img src="/logo/logo512-512.png" alt="Galgospedia" class="w-12 h-12 object-contain opacity-25">
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-xs font-semibold truncate text-gray-700"><?= htmlspecialchars($b['name']) ?></p>
                <?php if (!empty($b['club']) || !empty($b['country'])): ?>
                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars(implode(' · ', array_filter([$b['club'] ?? null, $b['country'] ?? null]))) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Texto SEO — ¿Qué es el Galgo Español? -->
<section class="bg-galgo-dark text-white py-16">
    <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-3xl font-display font-bold text-galgo-gold mb-8 text-center">¿Qué es el Galgo Español?</h2>
        <div class="grid md:grid-cols-3 gap-8 text-sm text-gray-300 leading-relaxed">
            <div>
                <h3 class="text-white font-bold text-base mb-3">Una raza milenaria</h3>
                <p>El Galgo Español es una de las razas caninas más antiguas del mundo, con orígenes que se remontan a más de 4.000 años. Descendiente directo de los galgos traídos a la Península Ibérica por fenicios y cartagineses, ha sido compañero inseparable de la nobleza española durante siglos, apareciendo en pinturas de Velázquez y en el escudo de armas de las grandes casas de caza.</p>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3">Velocidad y resistencia</h3>
                <p>Con una velocidad máxima de 60-65 km/h, el Galgo Español es uno de los perros más rápidos del planeta. A diferencia del Greyhound inglés, optimizado para pistas rectas, el galgo español fue seleccionado durante siglos para la caza en campo abierto: puede mantener altas velocidades durante kilómetros, sortear obstáculos y girar en seco persiguiendo liebres en terreno irregular.</p>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3">El galgo hoy</h3>
                <p>Hoy el Galgo Español vive un momento de revalorización. Criadores, clubs cinegéticos y asociaciones protectoras trabajan juntos para preservar la pureza de la raza, mejorar su bienestar y documentar su genealogía. Galgospedia nace de esa necesidad: una plataforma abierta donde cada galgo tiene su historia, su árbol genealógico y su lugar en el registro colectivo de la raza.</p>
            </div>
        </div>
    </div>
</section>

<!-- Cómo funciona -->
<section class="container mx-auto px-4 py-16 text-center">
    <h2 class="text-3xl font-display font-bold mb-4">¿Cómo funciona Galgospedia?</h2>
    <p class="text-gray-500 mb-12 max-w-xl mx-auto">Registra tu galgo, construye su árbol genealógico y conéctate con otros criadores. Totalmente gratis.</p>
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">📸</div>
            <h3 class="font-bold text-lg mb-2">Registra tu Galgo</h3>
            <p class="text-gray-500 text-sm">Añade una foto y los datos de tu galgo. La imagen se optimiza automáticamente a WebP y se guarda en la nube.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">🌳</div>
            <h3 class="font-bold text-lg mb-2">Construye el árbol</h3>
            <p class="text-gray-500 text-sm">Vincula padre y madre. El árbol genealógico crece automáticamente con generaciones ilimitadas y calcula el COI (Coeficiente de Consanguinidad).</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">🏆</div>
            <h3 class="font-bold text-lg mb-2">Gestiona tu club</h3>
            <p class="text-gray-500 text-sm">Crea o únete a un club cinegético. Gestiona socios, eventos, documentos y bóveda de licencias desde la Oficina Virtual.</p>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-gray-50 py-16">
    <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-3xl font-display font-bold text-center mb-10">Preguntas frecuentes sobre el Galgo Español</h2>
        <div class="space-y-4" x-data="{ open: null }">

            <?php
            $faqs = [
                [
                    'q' => '¿Qué es el Galgo Español?',
                    'a' => 'El Galgo Español es una raza canina autóctona de España con más de 4.000 años de historia. Es un perro de caza mayor por excelencia, conocido por su velocidad extraordinaria, resistencia y temperamento equilibrado. Está reconocido por la Real Sociedad Canina de España (RSCE) y la FCI como raza oficial española.'
                ],
                [
                    'q' => '¿A qué velocidad corre el Galgo Español?',
                    'a' => 'El Galgo Español puede alcanzar velocidades de hasta 60-65 km/h en campo abierto. Su diseño aerodinámico, pecho profundo y musculatura poderosa le permiten mantener esa velocidad en distancias largas, superando al Greyhound inglés en resistencia y cambios de dirección.'
                ],
                [
                    'q' => '¿Cuánto vive un Galgo Español?',
                    'a' => 'La esperanza de vida media del Galgo Español es de 10 a 14 años. Con cuidados veterinarios regulares, alimentación adecuada y ejercicio suficiente, muchos ejemplares gozan de excelente salud hasta los 12-13 años.'
                ],
                [
                    'q' => '¿Cuál es la diferencia entre el Galgo Español y el Greyhound inglés?',
                    'a' => 'Son razas distintas aunque emparentadas. El Galgo Español es más rústico y resistente, con un cuerpo ligeramente más largo y una mayor capacidad para trabajar en terreno irregular. El Greyhound inglés fue seleccionado para carreras en pista ovalada (velocidad punta en línea recta), mientras el galgo español fue perfeccionado para la caza en campo, donde priman la resistencia y los giros bruscos.'
                ],
                [
                    'q' => '¿Cómo puedo registrar mi galgo en Galgospedia?',
                    'a' => 'El registro es completamente gratuito. Crea tu cuenta, accede a "Añadir Galgo" y completa el formulario con nombre, fecha de nacimiento, sexo y foto. Después puedes vincular padre y madre para construir el árbol genealógico. Si tu galgo es semental o reproductora, puedes solicitar que aparezca en el directorio oficial.'
                ],
                [
                    'q' => '¿Qué es el Coeficiente de Consanguinidad (COI) y para qué sirve?',
                    'a' => 'El COI (Coeficiente de Endogamia o Inbreeding) es un porcentaje que indica cuánto comparte un galgo con sigo mismo genéticamente debido a la presencia de antepasados comunes. Un COI bajo (por debajo del 6,25%) indica buena diversidad genética. Galgospedia lo calcula automáticamente a partir del árbol genealógico, siendo una herramienta fundamental para criadores responsables.'
                ],
            ];
            foreach ($faqs as $i => $faq):
            ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <button class="w-full text-left px-6 py-4 flex items-center justify-between font-semibold text-gray-900 hover:bg-gray-50 transition"
                        @click="open = open === <?= $i ?> ? null : <?= $i ?>">
                    <span><?= htmlspecialchars($faq['q']) ?></span>
                    <svg class="w-5 h-5 text-galgo-red shrink-0 transition-transform"
                         :class="open === <?= $i ?> ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === <?= $i ?>" x-transition class="px-6 pb-4 text-sm text-gray-600 leading-relaxed">
                    <?= htmlspecialchars($faq['a']) ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- Patrocinadores & Auspiciadores -->
<?php if (!empty($sponsors)): ?>
<section class="sponsors-section overflow-hidden">

    <!-- Cabecera -->
    <div class="container mx-auto px-4 pt-12 pb-8 text-center">
        <div class="inline-flex items-center gap-2 bg-galgo-gold/10 border border-galgo-gold/30 rounded-full px-4 py-1.5 mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-galgo-gold animate-pulse"></span>
            <p class="text-xs font-bold text-galgo-gold uppercase tracking-widest">Hacen posible Galgospedia</p>
            <span class="w-1.5 h-1.5 rounded-full bg-galgo-gold animate-pulse"></span>
        </div>
        <h2 class="text-3xl font-display font-bold text-galgo-dark">Patrocinadores &amp; Auspiciadores</h2>
        <p class="text-sm text-gray-400 mt-2">Las marcas y empresas que apoyan el futuro del Galgo Español</p>
    </div>

    <!-- Carrusel marquee -->
    <div class="relative w-full overflow-hidden sponsors-track-wrap">

        <!-- Degradados laterales -->
        <div class="sponsor-fade-left pointer-events-none absolute inset-y-0 left-0 w-32 z-10"></div>
        <div class="sponsor-fade-right pointer-events-none absolute inset-y-0 right-0 w-32 z-10"></div>

        <div class="sponsors-track">
            <?php
            // Triplicamos para loop infinito sin saltos con hasta 20 patrocinadores
            $loop = array_merge($sponsors, $sponsors, $sponsors);
            foreach ($loop as $sp):
                $img = '<img src="' . htmlspecialchars($sp['logo_path']) . '"'
                     . ' alt="' . htmlspecialchars($sp['name']) . '"'
                     . ' loading="lazy" class="sponsor-logo">';
            ?>
            <div class="sponsor-item" title="<?= htmlspecialchars($sp['name']) ?>">
                <?php if ($sp['website_url']): ?>
                    <a href="<?= htmlspecialchars($sp['website_url']) ?>" target="_blank" rel="noopener sponsored" class="sponsor-link">
                        <?= $img ?>
                        <span class="sponsor-name"><?= htmlspecialchars($sp['name']) ?></span>
                    </a>
                <?php else: ?>
                    <div class="sponsor-link">
                        <?= $img ?>
                        <span class="sponsor-name"><?= htmlspecialchars($sp['name']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="container mx-auto px-4 pb-10 pt-6 text-center">
        <p class="text-sm text-gray-400">
            ¿Quieres que tu marca aparezca aquí?
            <a href="mailto:info@galgospedia.com" class="text-galgo-red hover:underline font-semibold">Contáctanos</a>
            y únete a los que apoyan el proyecto.
        </p>
    </div>
</section>

<style>
/* ── Sección ─────────────────────────────────────── */
.sponsors-section {
    background: linear-gradient(180deg, #f9f9f7 0%, #ffffff 100%);
    border-top: 1px solid #f0ede8;
    border-bottom: 1px solid #f0ede8;
}

/* ── Track ───────────────────────────────────────── */
.sponsors-track-wrap {
    cursor: default;
    display: flex;
    justify-content: center;
}

.sponsors-track {
    display: flex;
    align-items: center;
    justify-content: center;
    width: max-content;
    gap: 0;
    /* Velocidad: ~3s por patrocinador — fluido hasta 20 logos */
    animation: sponsors-scroll 20s linear infinite;
}
.sponsors-track:hover {
    animation-play-state: paused;
}

@keyframes sponsors-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(calc(-100% / 3)); }
}

/* ── Degradados laterales ────────────────────────── */
.sponsor-fade-left  { background: linear-gradient(to right, #f9f9f7 0%, transparent 100%); }
.sponsor-fade-right { background: linear-gradient(to left,  #f9f9f7 0%, transparent 100%); }

/* ── Tarjeta de cada patrocinador ────────────────── */
.sponsor-item {
    flex-shrink: 0;
    padding: 0 20px;
}

.sponsor-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1.5px solid #ede9e3;
    border-radius: 16px;
    padding: 18px 28px;
    width: 200px;
    min-height: 110px;
    justify-content: center;
    transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
    text-decoration: none;
}
.sponsor-item:hover .sponsor-link {
    border-color: #b45309;
    box-shadow: 0 8px 28px rgba(180,83,9,.12);
    transform: translateY(-4px) scale(1.03);
}

/* ── Logo ────────────────────────────────────────── */
.sponsor-logo {
    height: 70px;
    max-width: 150px;
    width: auto;
    object-fit: contain;
    filter: grayscale(40%) opacity(0.75);
    transition: filter .3s ease;
}
.sponsor-item:hover .sponsor-logo {
    filter: grayscale(0%) opacity(1);
}

/* ── Nombre bajo el logo ─────────────────────────── */
.sponsor-name {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-align: center;
    letter-spacing: .04em;
    transition: color .25s ease;
    line-height: 1.3;
}
.sponsor-item:hover .sponsor-name {
    color: #b45309;
}

/* ── Entrada ─────────────────────────────────────── */
@keyframes sponsor-entrance {
    0%   { opacity: 0; transform: translateY(12px); }
    100% { opacity: 1; transform: translateY(0); }
}
.sponsors-track-wrap {
    animation: sponsor-entrance .7s ease both;
    padding: 16px 0 20px;
}
</style>
<?php endif; ?>

<!-- Donaciones -->
<section class="relative py-20 overflow-hidden" style="background: linear-gradient(160deg, #1c0a00 0%, #2d1200 40%, #1a0800 70%, #0f0500 100%);">

    <!-- Fondo decorativo: silueta de galgo -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.04] flex items-center justify-end pr-8">
        <svg viewBox="0 0 500 280" class="h-72 w-auto" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M480 200 C460 180 440 160 420 155 C410 152 400 158 390 165 C370 178 355 185 340 180
                     C320 174 305 155 290 140 C270 122 250 118 230 125 C210 132 195 148 175 155
                     C155 162 135 158 115 148 C95 138 78 122 60 115 C42 108 25 112 12 125
                     L8 130 L15 128 C28 124 42 128 58 136 C75 145 92 160 112 170
                     C132 180 155 183 178 175 C200 167 215 150 235 143
                     C255 136 272 140 290 156 C308 172 323 192 345 198
                     C365 204 385 196 402 182 C415 170 428 162 445 168
                     C460 174 472 188 485 205 Z"/>
            <ellipse cx="80" cy="108" rx="18" ry="12" transform="rotate(-15 80 108)"/>
            <ellipse cx="470" cy="158" rx="10" ry="6" transform="rotate(10 470 158)"/>
        </svg>
    </div>

    <div class="relative container mx-auto px-4 max-w-3xl">

        <!-- Icono corazón + patitas -->
        <div class="flex items-center justify-center gap-2 mb-5">
            <svg class="w-6 h-6 text-galgo-gold" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <span class="text-galgo-gold font-semibold text-sm uppercase tracking-widest">Apoya el proyecto</span>
            <svg class="w-6 h-6 text-galgo-gold" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </div>

        <h2 class="text-4xl md:text-5xl font-display font-bold text-white mb-6 leading-tight text-center">
            Esta es tu casa.<br>
            <span class="text-galgo-gold">Ayúdanos a mantenerla.</span>
        </h2>

        <p class="text-gray-300 mb-6 leading-relaxed text-lg text-justify">
            Galgospedia no cuenta con grandes multinacionales detrás ni publicidad intrusiva que entorpezca la experiencia.
            <span class="text-white font-medium">Somos un equipo humano de apasionados trabajando codo a codo con toda la comunidad galguera</span>, dedicando nuestro esfuerzo y conocimientos tecnológicos para poner a vuestra disposición la herramienta definitiva de gestión y registro.
        </p>
        <p class="text-gray-300 mb-12 leading-relaxed text-lg text-justify">
            Nuestro objetivo es claro: <span class="text-white font-medium">decirle adiós al papeleo interminable</span> y digitalizar por fin nuestra afición. Queremos ahorrarte horas de gestión para que disfrutes de lo que de verdad importa. Si esta plataforma te resulta útil, <span class="text-white font-medium">tu apoyo económico</span> es vital para que podamos seguir innovando y cubriendo los gastos de los servidores. ¡Gracias por respaldar el futuro del Galgo Español!
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-5 w-full max-w-lg mx-auto">

            <!-- Bizum -->
            <button onclick="copyBizumLanding()"
                    class="donate-btn-bizum flex items-center justify-center gap-3 px-5 py-3 rounded-xl
                           text-white transition-all duration-200 active:scale-95 cursor-pointer select-none w-full sm:w-64"
                    style="background-color:#0049AC;">
                <span class="flex items-center justify-center w-7 h-7 rounded-full shrink-0" style="background:rgba(255,255,255,0.15)">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none">
                        <path d="M4 10.5l4 4 8-8" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-lg font-black tracking-tight">bizum</span>
                <span class="w-px h-4 shrink-0" style="background:rgba(255,255,255,0.3)"></span>
                <span id="bizum-number-landing" class="font-mono text-sm font-bold whitespace-nowrap" style="letter-spacing:.12em;color:rgba(255,255,255,.9)">744 450 139</span>
            </button>

            <!-- PayPal -->
            <form action="https://www.paypal.com/donate" method="post" target="_top" class="m-0 w-full sm:w-64">
                <input type="hidden" name="hosted_button_id" value="9ADK8KZJBNSRC">
                <button type="submit"
                        class="donate-btn-paypal flex items-center justify-center gap-3 px-5 py-3 rounded-xl w-full
                               transition-all duration-200 active:scale-95 cursor-pointer"
                        style="background-color:#FFC439;color:#003087;">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full shrink-0" style="background:rgba(0,48,135,0.12)">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none">
                            <path d="M4 2.5h7c2.5 0 4 1.3 3.6 3.8C14.2 9 12 10.5 9.5 10.5H7.5L6.5 16H4z" fill="#003087"/>
                            <path d="M5 4H11c1.8 0 2.8.9 2.5 2.8C13.2 9 11.5 10 9.5 10H7.5L6.5 15H5z" fill="#009CDE"/>
                        </svg>
                    </span>
                    <span class="text-lg font-black tracking-tight" style="color:#003087;">PayPal</span>
                    <span class="w-px h-4 shrink-0" style="background:rgba(0,48,135,0.25)"></span>
                    <span class="text-sm font-semibold whitespace-nowrap" style="color:#003087;">Donar ahora</span>
                </button>
            </form>

        </div>
    </div>
</section>

<!-- CTA Final -->
<?php if (!\Services\AuthService::isLoggedIn()): ?>
<section class="bg-galgo-red text-white py-16 text-center">
    <div class="container mx-auto px-4 max-w-xl">
        <h2 class="text-3xl font-display font-bold mb-4">Registra tu galgo hoy, gratis</h2>
        <p class="text-red-100 mb-8">Únete a la comunidad de criadores y aficionados al Galgo Español. Sin cuotas, sin límites de registros.</p>
        <a href="/registro" class="inline-block bg-white text-galgo-red font-bold px-10 py-4 rounded-xl hover:bg-gray-100 transition text-lg">
            Crear cuenta gratis
        </a>
    </div>
</section>
<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
