<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function navActive(string $path, string $current): string {
    if ($path === '/') return $current === '/' ? 'nav-link-active' : 'nav-link';
    return str_starts_with($current, $path) ? 'nav-link-active' : 'nav-link';
}
?>
<nav class="shadow-lg sticky top-0 z-[1100]" style="background-color: #991b1b;" x-data="{ mobileOpen: false }">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">

<style>
@keyframes gallop {
  0%, 100% { transform: translateY(0) skewX(0deg); }
  25% { transform: translateY(-3px) skewX(-2deg) scaleX(1.02); }
  50% { transform: translateY(0) skewX(0deg); }
  75% { transform: translateY(2px) skewX(1deg) scaleX(0.98); }
}
.logo-animate {
  animation: gallop 1.5s ease-in-out infinite;
  transform-origin: center bottom;
  will-change: transform;
}
.logo-animate:hover {
  animation-duration: 0.8s; /* Corre más rápido al pasar el ratón */
}
</style>
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2">
                <img src="/logo/galgospedia-logo450-128.png" alt="Galgospedia" class="h-9 logo-animate drop-shadow-[0_2px_4px_rgba(0,0,0,0.6)]">
            </a>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/" class="<?= navActive('/', $currentPath) ?>">Inicio</a>
                <a href="/galgos" class="<?= navActive('/galgos', $currentPath) ?>">Galgos</a>
                <a href="/sementales" class="<?= navActive('/sementales', $currentPath) ?>">Sementales</a>
                <a href="/reproductoras" class="<?= navActive('/reproductoras', $currentPath) ?>">Reproductoras</a>
                <a href="/oficina" class="<?= navActive('/oficina', $currentPath) ?>">Oficina Virtual</a>
                <a href="/torneos" class="<?= navActive('/torneos', $currentPath) ?>">Torneos</a>
                <a href="/apps" class="<?= navActive('/apps', $currentPath) ?>">Apps</a>
            </div>

            <!-- Desktop actions -->
            <div class="hidden md:flex items-center gap-3">
                <?php if (\Services\AuthService::isLoggedIn()): ?>
                    <a href="/galgos/nuevo" class="btn-gold text-sm">+ Añadir Galgo</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="nav-link flex items-center gap-1">
                            <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-50">
                            <a href="/mi-perfil" class="dropdown-item">Mi perfil</a>
                            <a href="/mi-billetera" class="dropdown-item">Mi billetera</a>
                            <?php if (\Services\AuthService::isAdmin()): ?>
                                <a href="/admin" class="dropdown-item">Administración</a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="/logout" class="dropdown-item text-red-600">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="nav-link">Iniciar sesión</a>
                    <a href="/registro" class="btn-red text-sm">Registrarse</a>
                <?php endif; ?>
            </div>

            <!-- Mobile hamburger -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden text-gray-300 hover:text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-transition class="md:hidden pb-4 space-y-1">
            <a href="/galgos" class="mobile-nav-link">Galgos</a>
            <a href="/sementales" class="mobile-nav-link">Sementales</a>
            <a href="/reproductoras" class="mobile-nav-link">Reproductoras</a>
            <a href="/oficina" class="mobile-nav-link">Oficina Virtual</a>
            <a href="/torneos" class="mobile-nav-link">Torneos</a>
            <a href="/apps" class="mobile-nav-link">Apps</a>
            <?php if (\Services\AuthService::isLoggedIn()): ?>
                <a href="/galgos/nuevo" class="mobile-nav-link font-semibold text-galgo-gold">+ Añadir Galgo</a>
                <a href="/mi-perfil" class="mobile-nav-link">Mi perfil</a>
                <a href="/mi-billetera" class="mobile-nav-link">Mi billetera</a>
                <a href="/logout" class="mobile-nav-link text-red-400">Cerrar sesión</a>
            <?php else: ?>
                <a href="/login" class="mobile-nav-link">Iniciar sesión</a>
                <a href="/registro" class="mobile-nav-link">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
