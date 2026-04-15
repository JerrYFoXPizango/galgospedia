<?php
declare(strict_types=1);
namespace Controllers;

use Models\{User, Dog};
use Services\ImageProcessor;
use Helpers\{Csrf, Flash};

class ProfileController extends BaseController
{
    public function show(array $p = []): void
    {
        $userId = $this->currentUserId();
        $user   = (new User())->findById($userId);
        $dogs   = (new Dog())->directory(1, 50, ['owner' => $userId]);
        $this->render('profile/show', compact('user', 'dogs'));
    }

    public function uploadAvatar(array $p = []): void
    {
        Csrf::verify();
        if (empty($_FILES['avatar'])) {
            Flash::set('error', 'No se seleccionó ninguna imagen.');
            $this->redirect('/mi-perfil');
        }
        try {
            $processor = new ImageProcessor();
            $rel       = $processor->processAvatar($_FILES['avatar']);
            (new User())->updateAvatar($this->currentUserId(), $rel);
            Flash::set('success', 'Avatar actualizado.');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
        }
        $this->redirect('/mi-perfil');
    }

    public function uploadClubLogo(array $p = []): void
    {
        Csrf::verify();
        $userId = $this->currentUserId();
        $user   = (new User())->findById($userId);

        if (($user['plan'] ?? 'free') !== 'club') {
            Flash::set('error', 'Esta función solo está disponible en el plan Club.');
            $this->redirect('/mi-perfil');
        }

        if (empty($_FILES['club_logo'])) {
            Flash::set('error', 'No se seleccionó ningún logo.');
            $this->redirect('/mi-perfil');
        }

        try {
            $processor = new ImageProcessor();
            $rel       = $processor->processClubLogo($_FILES['club_logo']);

            // Delete old logo
            if (!empty($user['club_logo_path'])) {
                $old = PUB_PATH . '/' . ltrim($user['club_logo_path'], '/');
                if (file_exists($old)) {
                    unlink($old);
                }
            }

            (new User())->updateClubLogo($userId, $rel);
            Flash::set('success', 'Logo del club actualizado.');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
        }
        $this->redirect('/mi-perfil');
    }
}
