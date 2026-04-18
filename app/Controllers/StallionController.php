<?php
declare(strict_types=1);
namespace Controllers;

use Models\Stallion;

class StallionController extends BaseController
{
    public function index(array $p = []): void
    {
        $q = trim($this->query('q', ''));
        $stallions = (new Stallion())->allActive(50, $q);
        $this->render('stallions/index', compact('stallions', 'q'));
    }
}
