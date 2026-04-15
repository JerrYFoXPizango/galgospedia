<?php
declare(strict_types=1);
namespace Controllers;

class LegalController extends BaseController
{
    public function privacidad(array $p = []): void
    {
        $this->render('legal/privacidad');
    }

    public function avisoLegal(array $p = []): void
    {
        $this->render('legal/aviso-legal');
    }

    public function cookies(array $p = []): void
    {
        $this->render('legal/cookies');
    }
}
