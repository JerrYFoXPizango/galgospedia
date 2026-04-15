<?php
declare(strict_types=1);

namespace Controllers;

class AppController extends BaseController
{
    public function index(array $p = []): void
    {
        $this->render('apps/index', [
            'pageTitle' => 'Apps y Herramientas — Galgospedia',
            'pageDesc'  => 'Módulo de gestión avanzada para galgueros profesionales. Crea y organiza sorteos, colleras y torneos.'
        ]);
    }

    public function sorteosIndex(array $p = []): void
    {
        $this->render('apps/sorteos/index', [
            'pageTitle' => 'Gestor de Sorteos y Colleras — Galgospedia',
            'pageDesc'  => 'Crea sorteos aleatorios, organiza las mangas y automatiza los cruces de tus torneos.'
        ]);
    }

    public function sorteosNuevo(array $p = []): void
    {
        $this->render('apps/sorteos/nuevo', [
            'pageTitle' => 'Nuevo Sorteo — Galgospedia',
            'pageDesc'  => 'Configura tu nuevo torneo y empareja a los galgos participantes.'
        ]);
    }

    public function sorteosParticipantes(array $p = []): void
    {
        $this->render('apps/sorteos/participantes', [
            'pageTitle' => 'Añadir Participantes al Sorteo — Galgospedia',
            'pageDesc'  => 'Busca a los galgos oficiales para añadirlos a las colleras de tu torneo.'
        ]);
    }

    public function descansoIndex(array $p = []): void
    {
        $this->render('apps/descanso/index', [
            'pageTitle' => 'Calculadora de Descanso — Galgospedia',
            'pageDesc'  => 'Calcula el tiempo de recuperación de tu galgo tras cada carrera en el Campeonato de España.'
        ]);
    }
}
