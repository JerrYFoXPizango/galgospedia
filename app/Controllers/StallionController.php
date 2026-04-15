<?php
declare(strict_types=1);
namespace Controllers;

use Models\Stallion;

class StallionController extends BaseController
{
    public function index(array $p = []): void
    {
        $stallions = (new Stallion())->allActive();
        $this->render('stallions/index', compact('stallions'));
    }
}
