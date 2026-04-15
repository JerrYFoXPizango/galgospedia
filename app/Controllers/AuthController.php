<?php
declare(strict_types=1);
namespace Controllers;

use Models\User;
use Models\Club;
use Services\AuthService;
use Helpers\{Csrf, Flash, RateLimit};

class AuthController extends BaseController
{
    private User $users;
    private Club $clubs;

    public function __construct()
    {
        $this->users = new User();
        $this->clubs = new Club();
    }

    public function showLogin(array $p = []): void
    {
        if (AuthService::isLoggedIn()) $this->redirect('/');
        $this->render('auth/login');
    }

    public function login(array $p = []): void
    {
        Csrf::verify();

        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'login:' . $ip;

        if (RateLimit::tooMany($key, 5, 900)) {
            Flash::set('error', 'Demasiados intentos fallidos. Inténtalo de nuevo en 15 minutos.');
            $this->redirect('/login');
        }

        $email    = $this->input('email', '');
        $password = $this->input('password', '');

        $user = $this->users->findByEmail($email);
        if (!$user || !$this->users->verifyPassword($password, $user['password_hash'])) {
            RateLimit::hit($key, 900);
            Flash::set('error', 'Correo o contraseña incorrectos.');
            $this->redirect('/login');
        }
        if (!$user['is_active']) {
            RateLimit::hit($key, 900);
            Flash::set('error', 'Tu cuenta está desactivada.');
            $this->redirect('/login');
        }

        RateLimit::clear($key);
        AuthService::login($user);
        $this->redirect('/');
    }

    public function showRegister(array $p = []): void
    {
        if (AuthService::isLoggedIn()) $this->redirect('/');
        $this->render('auth/register', [
            'activeClubs' => $this->clubs->allActive(),
        ]);
    }

    public function register(array $p = []): void
    {
        Csrf::verify();
        $username   = $this->input('username', '');
        $email      = $this->input('email', '');
        $password   = $this->input('password', '');
        $confirm    = $this->input('password_confirm', '');
        $clubAction = $this->input('club_action', 'none'); // none | join | create
        $clubId     = (int) $this->input('club_id', 0);

        if (strlen($username) < 3 || strlen($username) > 50) {
            Flash::set('error', 'El nombre de usuario debe tener entre 3 y 50 caracteres.');
            $this->redirect('/registro');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Correo electrónico inválido.');
            $this->redirect('/registro');
        }
        if (strlen($password) < 8) {
            Flash::set('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('/registro');
        }
        if ($password !== $confirm) {
            Flash::set('error', 'Las contraseñas no coinciden.');
            $this->redirect('/registro');
        }
        if ($this->users->findByEmail($email)) {
            Flash::set('error', 'Ese correo ya está registrado.');
            $this->redirect('/registro');
        }
        if ($this->users->findByUsername($username)) {
            Flash::set('error', 'Ese nombre de usuario ya está en uso.');
            $this->redirect('/registro');
        }

        $userId = $this->users->create(compact('username', 'email', 'password'));
        $user   = $this->users->findById($userId);
        AuthService::login($user);

        // ── Club membership after registration ──────────────
        if ($clubAction === 'join' && $clubId > 0) {
            $club = $this->clubs->findById($clubId);
            if ($club && $club['status'] === 'active') {
                $db   = \Config\Database::pdo();
                $stmt = $db->prepare(
                    "INSERT IGNORE INTO club_members
                     (club_id, user_id, name, email, status)
                     VALUES (?, ?, ?, ?, 'pending')"
                );
                $stmt->execute([$clubId, $userId, $username, $email]);
                Flash::set('success', '¡Bienvenido/a, ' . htmlspecialchars($username) . '! Tu solicitud de ingreso al club está pendiente de aprobación.');
                $this->redirect('/');
                return;
            }
        }

        if ($clubAction === 'create') {
            Flash::set('success', '¡Bienvenido/a, ' . htmlspecialchars($username) . '! Completa el formulario para registrar tu club o coto.');
            $this->redirect('/oficina/solicitar-club');
            return;
        }

        Flash::set('success', '¡Bienvenido/a a Galgospedia, ' . htmlspecialchars($username) . '!');
        $this->redirect('/');
    }

    public function logout(array $p = []): void
    {
        AuthService::logout();
        $this->redirect('/');
    }

    public function verifyEmail(array $p = []): void
    {
        $userId = $this->users->consumeToken($p['token'], 'email_verify');
        if (!$userId) {
            Flash::set('error', 'El enlace de verificación no es válido o ha expirado.');
            $this->redirect('/login');
        }
        $this->users->markEmailVerified($userId);
        Flash::set('success', '¡Correo verificado! Ya puedes iniciar sesión.');
        $this->redirect('/login');
    }

    public function showForgot(array $p = []): void
    {
        $this->render('auth/forgot');
    }

    public function forgot(array $p = []): void
    {
        Csrf::verify();
        $email = $this->input('email', '');
        $user  = $this->users->findByEmail($email);
        // Always show success to prevent email enumeration
        Flash::set('success', 'Si ese correo existe, recibirás un enlace para restablecer tu contraseña.');
        if ($user) {
            $token = $this->users->createToken($user['id'], 'password_reset');
            // TODO: Send email via Mailer service
        }
        $this->redirect('/login');
    }

    public function showReset(array $p = []): void
    {
        $this->render('auth/reset', ['token' => $p['token']]);
    }

    public function reset(array $p = []): void
    {
        Csrf::verify();
        $password = $this->input('password', '');
        $confirm  = $this->input('password_confirm', '');

        if (strlen($password) < 8 || $password !== $confirm) {
            Flash::set('error', 'Contraseñas inválidas o no coinciden (mínimo 8 caracteres).');
            $this->redirect('/restablecer/' . $p['token']);
        }

        $userId = $this->users->consumeToken($p['token'], 'password_reset');
        if (!$userId) {
            Flash::set('error', 'Enlace inválido o expirado.');
            $this->redirect('/recuperar');
        }

        $this->users->updatePassword($userId, $password);
        Flash::set('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        $this->redirect('/login');
    }
}
