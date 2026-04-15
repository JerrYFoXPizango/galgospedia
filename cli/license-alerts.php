#!/usr/bin/env php
<?php
/**
 * Galgospedia — Cron: avisos de licencias
 *
 * Uso:
 *   php cli/license-alerts.php           # ejecutar y enviar
 *   php cli/license-alerts.php --dry-run # simular sin enviar ni registrar
 *
 * Cron sugerido (diario a las 08:00):
 *   0 8 * * * /usr/bin/php /var/www/galgospedia/cli/license-alerts.php >> /var/log/galgo-alerts.log 2>&1
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('PUB_PATH',  BASE_PATH . '/public');

require BASE_PATH . '/bootstrap/app.php';

use Services\LicenseAlertService;

$dryRun = in_array('--dry-run', $argv ?? [], true);

echo '[' . date('Y-m-d H:i:s') . '] Iniciando revisión de licencias'
    . ($dryRun ? ' (DRY-RUN)' : '') . PHP_EOL;

$service = new LicenseAlertService();
$pending = $service->getPending();

if (!$pending) {
    echo '[' . date('Y-m-d H:i:s') . '] Sin alertas pendientes. Saliendo.' . PHP_EOL;
    exit(0);
}

echo '[' . date('Y-m-d H:i:s') . '] Alertas encontradas: ' . count($pending) . PHP_EOL;

foreach ($pending as $m) {
    echo '  · ' . $m['club_name'] . ' / ' . $m['member_name']
        . ' — ' . $m['alert_type']
        . ' (' . $m['license_expires_at'] . ')' . PHP_EOL;
}

$stats = $service->run($dryRun);

echo '[' . date('Y-m-d H:i:s') . '] Completado.'
    . ' Socios: '  . $stats['processed']
    . ' | Clubs: ' . $stats['clubs']
    . ' | Registrados: ' . $stats['sent']
    . ($stats['errors'] > 0 ? ' | Errores de envío: ' . $stats['errors'] : '')
    . PHP_EOL;
