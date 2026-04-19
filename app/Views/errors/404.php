<?php
$pageTitle  = 'Página no encontrada';
$pageDesc   = 'La página que buscas no existe en Galgospedia.';
$metaRobots = 'noindex, nofollow';
require APP_PATH . '/Views/layout/header.php';
?>

<section class="relative overflow-hidden" style="background:linear-gradient(160deg,#1c0a00 0%,#2d1200 45%,#0f0500 100%);min-height:80vh;display:flex;align-items:center;">

    <!-- Galgo silhouette -->
    <div class="absolute inset-0 pointer-events-none flex items-center justify-end opacity-[0.06]">
        <svg viewBox="0 0 500 300" class="h-64 w-auto mr-8" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M480 200 C460 180 440 160 420 155 C410 152 400 158 390 165 C370 178 355 185 340 180
                     C320 174 305 155 290 140 C270 122 250 118 230 125 C210 132 195 148 175 155
                     C155 162 135 158 115 148 C95 138 78 122 60 115 C42 108 25 112 12 125
                     L8 130 L15 128 C28 124 42 128 58 136 C75 145 92 160 112 170
                     C132 180 155 183 178 175 C200 167 215 150 235 143
                     C255 136 272 140 290 156 C308 172 323 192 345 198
                     C365 204 385 196 402 182 C415 170 428 162 445 168
                     C460 174 472 188 485 205 Z"/>
            <ellipse cx="80" cy="108" rx="18" ry="12" transform="rotate(-15 80 108)"/>
        </svg>
    </div>

    <div class="relative container mx-auto px-4 py-20 text-center">

        <!-- 404 number -->
        <div class="font-display font-bold text-galgo-gold leading-none mb-2" style="font-size:clamp(6rem,18vw,12rem);opacity:.15;line-height:1;">404</div>

        <div style="margin-top:-2rem;">
            <h1 class="font-display text-3xl md:text-4xl font-bold text-white mb-4">
                Este galgo se escapó del cercado
            </h1>
            <p class="text-gray-400 text-lg mb-10 max-w-lg mx-auto">
                La página que buscas no existe, fue eliminada o la URL no es correcta.
            </p>

            <!-- Quick search -->
            <form action="/galgos" method="GET" class="flex items-center gap-2 max-w-sm mx-auto mb-10">
                <input type="text" name="q" placeholder="Buscar un galgo..."
                       class="flex-1 px-4 py-3 rounded-xl text-gray-900 text-sm bg-white/90 focus:outline-none focus:ring-2 focus:ring-galgo-gold focus:bg-white transition">
                <button type="submit" class="btn-gold px-5 py-3 rounded-xl text-sm font-semibold whitespace-nowrap">Buscar</button>
            </form>

            <!-- Quick links -->
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="/" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Inicio
                </a>
                <a href="/galgos" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Directorio de Galgos
                </a>
                <a href="/sementales" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-galgo-gold/20 hover:bg-galgo-gold/30 text-galgo-gold text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Sementales
                </a>
                <a href="/reproductoras" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-galgo-red/20 hover:bg-galgo-red/30 text-red-300 text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    Reproductoras
                </a>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
