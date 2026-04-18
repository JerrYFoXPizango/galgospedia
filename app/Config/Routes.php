<?php

declare(strict_types=1);

namespace Config;

/**
 * Route definitions.
 * Each route: [method, pattern, Controller::class, 'method', middleware[]]
 *
 * Pattern supports {param} placeholders.
 * Middleware: 'auth' requires login, 'admin' requires admin role.
 */
class Routes
{
    public static function all(): array
    {
        return [
            // ── Public ──────────────────────────────────────
            ['GET',  '/',                             \Controllers\HomeController::class,      'index',        []],
            ['GET',  '/sitemap.xml',                  \Controllers\SitemapController::class,   'index',        []],

            // Legal
            ['GET',  '/privacidad',                   \Controllers\LegalController::class,     'privacidad',   []],
            ['GET',  '/aviso-legal',                  \Controllers\LegalController::class,     'avisoLegal',   []],
            ['GET',  '/cookies',                      \Controllers\LegalController::class,     'cookies',      []],

            // Auth
            ['GET',  '/login',                        \Controllers\AuthController::class,      'showLogin',    []],
            ['POST', '/login',                        \Controllers\AuthController::class,      'login',        []],
            ['GET',  '/registro',                     \Controllers\AuthController::class,      'showRegister', []],
            ['POST', '/registro',                     \Controllers\AuthController::class,      'register',     []],
            ['GET',  '/logout',                       \Controllers\AuthController::class,      'logout',       []],
            ['GET',  '/verificar/{token}',            \Controllers\AuthController::class,      'verifyEmail',  []],
            ['GET',  '/recuperar',                    \Controllers\AuthController::class,      'showForgot',   []],
            ['POST', '/recuperar',                    \Controllers\AuthController::class,      'forgot',       []],
            ['GET',  '/restablecer/{token}',          \Controllers\AuthController::class,      'showReset',    []],
            ['POST', '/restablecer/{token}',          \Controllers\AuthController::class,      'reset',        []],

            // Dogs — public
            ['GET',  '/galgos',                       \Controllers\DogController::class,       'index',   []],

            // Stallions & Broodmares — public
            ['GET',  '/sementales',                   \Controllers\StallionController::class,  'index',   []],
            ['GET',  '/reproductoras',                \Controllers\BroodmareController::class, 'index',   []],

            // Tree — public
            ['GET',  '/arbol/{slug}',                 \Controllers\TreeController::class,      'show',    []],

            // Apps Hub — public
            ['GET',  '/apps',                         \Controllers\AppController::class,       'index',   []],
            ['GET',  '/apps/sorteos',                 \Controllers\AppController::class,       'sorteosIndex', []],
            ['GET',  '/apps/descanso',                \Controllers\AppController::class,       'descansoIndex', ['auth']],

            // ── Authenticated ────────────────────────────────
            // Apps - Sorteos
            ['GET',  '/apps/sorteos/nuevo',           \Controllers\AppController::class,       'sorteosNuevo', ['auth']],
            ['GET',  '/apps/sorteos/participantes',   \Controllers\AppController::class,       'sorteosParticipantes', ['auth']],

            // Dogs — create / edit / delete
            ['GET',  '/galgos/nuevo',                 \Controllers\DogController::class,       'create',  ['auth']],
            ['GET',  '/galgos/{slug}',                \Controllers\DogController::class,       'show',    []],
            ['POST', '/galgos',                       \Controllers\DogController::class,       'store',   ['auth']],
            ['GET',  '/galgos/{slug}/tarjeta',          \Controllers\TarjetaController::class,   'generate', []],
            ['GET',  '/galgos/{slug}/editar',         \Controllers\DogController::class,       'edit',    ['auth']],
            ['POST', '/galgos/{slug}/actualizar',     \Controllers\DogController::class,       'update',  ['auth']],
            ['POST', '/galgos/{slug}/eliminar',       \Controllers\DogController::class,       'destroy', ['auth']],

            // Tree — link parents
            ['POST', '/galgos/{slug}/padre',          \Controllers\TreeController::class,      'setFather', ['auth']],
            ['POST', '/galgos/{slug}/madre',          \Controllers\TreeController::class,      'setMother', ['auth']],

            // Profile
            ['GET',  '/mi-perfil',                   \Controllers\ProfileController::class,   'show',    ['auth']],
            ['POST', '/mi-perfil/avatar',             \Controllers\ProfileController::class,   'uploadAvatar',   ['auth']],
            ['POST', '/mi-perfil/club-logo',          \Controllers\ProfileController::class,   'uploadClubLogo', ['auth']],

            // ── JSON API ─────────────────────────────────────
            ['GET',  '/api/arbol/{slug}',             \Controllers\TreeController::class,      'apiTree',     []],
            ['GET',  '/api/galgos/buscar',            \Controllers\DogController::class,       'apiSearch',   []],
            ['POST', '/api/imagen/subir',             \Controllers\ImageController::class,     'upload',      ['auth']],
            ['GET',  '/api/galgos/{id}/parientes',    \Controllers\TreeController::class,      'apiRelatives',[]],

            // ── Oficina Virtual ──────────────────────────────
            ['GET',  '/oficina',                                    \Controllers\OficinaController::class, 'index',            []],
            ['GET',  '/oficina/mi-club',                         \Controllers\OficinaController::class, 'miClub',           ['auth']],
            ['GET',  '/oficina/solicitar-club',                  \Controllers\OficinaController::class, 'showSolicitarClub',['auth']],
            ['POST', '/oficina/solicitar-club',                  \Controllers\OficinaController::class, 'solicitarClub',    ['auth']],
            ['GET',  '/oficina/mi-club/socios/nuevo',            \Controllers\OficinaController::class, 'nuevoSocio',       ['auth']],
            ['POST', '/oficina/mi-club/socios',                  \Controllers\OficinaController::class, 'storeSocio',       ['auth']],
            ['GET',  '/oficina/mi-club/eventos',                         \Controllers\OficinaController::class, 'calendario',         ['auth']],
            ['GET',  '/oficina/mi-club/eventos/nuevo',                 \Controllers\OficinaController::class, 'nuevoEvento',        ['auth']],
            ['POST', '/oficina/mi-club/eventos/nuevo',                 \Controllers\OficinaController::class, 'guardarEvento',      ['auth']],
            ['GET',  '/oficina/mi-club/eventos/{id}/editar',           \Controllers\OficinaController::class, 'editarEvento',       ['auth']],
            ['POST', '/oficina/mi-club/eventos/{id}/actualizar',       \Controllers\OficinaController::class, 'actualizarEvento',   ['auth']],
            ['POST', '/oficina/mi-club/eventos/{id}/eliminar',         \Controllers\OficinaController::class, 'eliminarEvento',     ['auth']],
            ['GET',  '/oficina/mi-club/documentos',                      \Controllers\OficinaController::class, 'boveda',              ['auth']],
            ['GET',  '/oficina/mi-club/documentos/subir',              \Controllers\OficinaController::class, 'subirDocumento',      ['auth']],
            ['POST', '/oficina/mi-club/documentos/subir',              \Controllers\OficinaController::class, 'guardarDocumento',    ['auth']],
            ['GET',  '/oficina/mi-club/documentos/{id}/descargar',     \Controllers\OficinaController::class, 'descargarDocumento',  ['auth']],
            ['POST', '/oficina/mi-club/documentos/{id}/eliminar',      \Controllers\OficinaController::class, 'eliminarDocumento',   ['auth']],
            ['GET',  '/oficina/mi-club/socios/{id}/editar',       \Controllers\OficinaController::class, 'editarSocio',      ['auth']],
            ['POST', '/oficina/mi-club/socios/{id}/actualizar',  \Controllers\OficinaController::class, 'actualizarSocio',  ['auth']],
            ['POST', '/oficina/mi-club/socios/{id}/eliminar',    \Controllers\OficinaController::class, 'eliminarSocio',    ['auth']],
            ['POST', '/oficina/mi-club/socios/{id}/aprobar',     \Controllers\OficinaController::class, 'aprobarSocio',     ['auth']],
            ['POST', '/oficina/mi-club/socios/{id}/suspender',   \Controllers\OficinaController::class, 'suspenderSocio',   ['auth']],

            // ── Billetera de documentos ──────────────────────────────
            ['GET',  '/mi-billetera',                    \Controllers\WalletController::class, 'index',   ['auth']],
            ['GET',  '/mi-billetera/subir',              \Controllers\WalletController::class, 'upload',  ['auth']],
            ['POST', '/mi-billetera',                    \Controllers\WalletController::class, 'store',   ['auth']],
            ['GET',  '/mi-billetera/{id}/ver',           \Controllers\WalletController::class, 'view',    ['auth']],
            ['POST', '/mi-billetera/{id}/eliminar',      \Controllers\WalletController::class, 'destroy', ['auth']],

            // ── Torneos — public ─────────────────────────────────────
            ['GET',  '/torneos',                       \Controllers\TournamentController::class, 'index',   []],

            // ── Torneos — authenticated (estáticas antes que dinámicas) ──
            ['GET',  '/torneos/nuevo',                 \Controllers\TournamentController::class, 'create',  ['auth']],
            ['POST', '/torneos',                       \Controllers\TournamentController::class, 'store',   ['auth']],
            ['GET',  '/torneos/{slug}',                \Controllers\TournamentController::class, 'show',    []],
            ['GET',  '/torneos/{slug}/editar',         \Controllers\TournamentController::class, 'edit',    ['auth']],
            ['POST', '/torneos/{slug}/actualizar',     \Controllers\TournamentController::class, 'update',  ['auth']],
            ['POST', '/torneos/{slug}/eliminar',       \Controllers\TournamentController::class, 'destroy', ['auth']],

            // ── Veterinario ──────────────────────────────────
            ['GET',  '/apps/veterinario',                      \Controllers\VetController::class, 'index',   ['auth']],
            ['GET',  '/apps/veterinario/{slug}',               \Controllers\VetController::class, 'show',    ['auth']],
            ['GET',  '/apps/veterinario/{slug}/nuevo',         \Controllers\VetController::class, 'create',  ['auth']],
            ['POST', '/apps/veterinario/{slug}/nuevo',         \Controllers\VetController::class, 'store',   ['auth']],
            ['GET',  '/apps/veterinario/registro/{id}/editar', \Controllers\VetController::class, 'edit',    ['auth']],
            ['POST', '/apps/veterinario/registro/{id}/actualizar', \Controllers\VetController::class, 'update', ['auth']],
            ['POST', '/apps/veterinario/registro/{id}/eliminar',   \Controllers\VetController::class, 'destroy',['auth']],

            // ── Entrenamiento ─────────────────────────────────
            ['GET',  '/apps/entrenamiento',                                \Controllers\TrainingController::class, 'index',      ['auth']],
            ['GET',  '/apps/entrenamiento/sesion/{id}/editar',             \Controllers\TrainingController::class, 'edit',       ['auth']],
            ['POST', '/apps/entrenamiento/sesion/{id}/actualizar',         \Controllers\TrainingController::class, 'update',     ['auth']],
            ['POST', '/apps/entrenamiento/sesion/{id}/eliminar',           \Controllers\TrainingController::class, 'destroy',    ['auth']],
            ['GET',  '/apps/entrenamiento/{slug}/nuevo',                   \Controllers\TrainingController::class, 'create',     ['auth']],
            ['POST', '/apps/entrenamiento/{slug}/nuevo',                   \Controllers\TrainingController::class, 'store',      ['auth']],
            ['GET',  '/apps/entrenamiento/{slug}/configurar',              \Controllers\TrainingController::class, 'config',     ['auth']],
            ['POST', '/apps/entrenamiento/{slug}/configurar',              \Controllers\TrainingController::class, 'saveConfig', ['auth']],
            ['GET',  '/apps/entrenamiento/{slug}',                         \Controllers\TrainingController::class, 'show',       ['auth']],

            // ── Admin ────────────────────────────────────────
            ['GET',  '/admin',                        \Controllers\AdminController::class,     'dashboard', ['admin']],
            ['GET',  '/admin/galgos',                 \Controllers\AdminController::class,     'dogs',      ['admin']],
            ['GET',  '/admin/usuarios',               \Controllers\AdminController::class,     'users',     ['admin']],
            ['POST', '/admin/sementales/{id}',        \Controllers\AdminController::class,     'toggleStallion',  ['admin']],
            ['POST', '/admin/reproductoras/{id}',     \Controllers\AdminController::class,     'toggleBroodmare', ['admin']],
            ['POST', '/admin/usuarios/{id}/rol',      \Controllers\AdminController::class,     'changeRole',      ['admin']],
            ['GET',  '/admin/clubs',                  \Controllers\AdminController::class,     'clubs',           ['admin']],
            ['POST', '/admin/clubs/{id}/aprobar',     \Controllers\AdminController::class,     'approveClub',     ['admin']],
            ['POST', '/admin/clubs/{id}/suspender',   \Controllers\AdminController::class,     'suspendClub',     ['admin']],
            ['GET',  '/admin/alertas',               \Controllers\AdminController::class,     'alertas',               ['admin']],
            ['POST', '/admin/alertas/enviar',        \Controllers\AdminController::class,     'enviarAlertas',         ['admin']],
            ['GET',  '/admin/torneos',               \Controllers\AdminController::class,     'tournaments',           ['admin']],
            ['POST', '/admin/torneos/{id}/estado',   \Controllers\AdminController::class,     'updateTournamentStatus',['admin']],

            // ── Patrocinadores ────────────────────────────────
            ['GET',  '/admin/patrocinadores',                  \Controllers\SponsorController::class, 'index',   ['admin']],
            ['GET',  '/admin/patrocinadores/nuevo',            \Controllers\SponsorController::class, 'create',  ['admin']],
            ['POST', '/admin/patrocinadores/nuevo',            \Controllers\SponsorController::class, 'store',   ['admin']],
            ['GET',  '/admin/patrocinadores/{id}/editar',      \Controllers\SponsorController::class, 'edit',    ['admin']],
            ['POST', '/admin/patrocinadores/{id}/actualizar',  \Controllers\SponsorController::class, 'update',  ['admin']],
            ['POST', '/admin/patrocinadores/{id}/eliminar',    \Controllers\SponsorController::class, 'destroy', ['admin']],
            ['POST', '/admin/patrocinadores/{id}/toggle',      \Controllers\SponsorController::class, 'toggle',  ['admin']],
        ];
    }
}
