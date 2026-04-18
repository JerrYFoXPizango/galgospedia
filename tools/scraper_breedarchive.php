<?php
/**
 * Scraper — galgoespanol.breedarchive.com
 *
 * USO:
 *   php tools/scraper_breedarchive.php
 *
 * Opciones:
 *   --batch=100      Galgos por petición (default: 50)
 *   --delay-min=4    Segundos mínimos entre peticiones (default: 4)
 *   --delay-max=9    Segundos máximos entre peticiones (default: 9)
 *   --output=data/galgos.csv
 *   --resume         Continuar desde el último checkpoint
 */

declare(strict_types=1);

// ── Configuración ─────────────────────────────────────────────────────────────
$config = [
    'base_url'   => 'https://galgoespanol.breedarchive.com',
    'endpoint'   => '/animal/get_entries',
    'batch'      => 50,
    'delay_min'  => 4,     // segundos mínimo entre requests
    'delay_max'  => 9,     // segundos máximo entre requests
    'output'     => __DIR__ . '/data/galgos.csv',
    'checkpoint' => __DIR__ . '/data/checkpoint.json',
    'log'        => __DIR__ . '/data/scraper.log',
    'max_retries'=> 4,
    'pause_on_429' => 120, // segundos a esperar si recibimos 429
];

// Parsear argumentos CLI
foreach ($argv as $arg) {
    if (preg_match('/^--(\w[\w-]*)=(.+)$/', $arg, $m)) {
        $key = str_replace('-', '_', $m[1]);
        $config[$key] = is_numeric($m[2]) ? (int)$m[2] : $m[2];
    }
}

$resume = in_array('--resume', $argv);

// ── Preparar directorio ───────────────────────────────────────────────────────
$dataDir = dirname($config['output']);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// ── Logger ────────────────────────────────────────────────────────────────────
function log_msg(string $msg, string $logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

// ── Checkpoint ────────────────────────────────────────────────────────────────
function load_checkpoint(string $file): array {
    if (!file_exists($file)) return ['start' => 0, 'total' => 0, 'saved' => 0];
    return json_decode(file_get_contents($file), true) ?? ['start' => 0, 'total' => 0, 'saved' => 0];
}

function save_checkpoint(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// ── HTTP Request con reintentos ───────────────────────────────────────────────
function fetch(string $url, array $config): array {
    // User-Agents reales de navegadores para rotar
    $agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
    ];

    for ($attempt = 1; $attempt <= $config['max_retries']; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => $agents[array_rand($agents)],
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json, text/plain, */*',
                'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
                'Referer: ' . $config['base_url'] . '/animal/browse',
                'X-Requested-With: XMLHttpRequest',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_msg("  cURL error (intento $attempt/$config[max_retries]): $err", $config['log']);
            sleep(rand(5, 12));
            continue;
        }

        if ($code === 429 || $code === 503) {
            log_msg("  ⚠️  HTTP $code — esperando {$config['pause_on_429']}s antes de reintentar...", $config['log']);
            sleep($config['pause_on_429']);
            continue;
        }

        if ($code === 200) {
            $data = json_decode($body, true);
            if ($data !== null) return ['ok' => true, 'data' => $data, 'code' => $code];
            log_msg("  JSON inválido (intento $attempt)", $config['log']);
        } else {
            log_msg("  HTTP $code (intento $attempt)", $config['log']);
        }

        // Backoff exponencial: 5s, 15s, 45s
        $wait = (int)(5 * pow(3, $attempt - 1));
        log_msg("  Esperando {$wait}s...", $config['log']);
        sleep($wait);
    }

    return ['ok' => false, 'data' => null, 'code' => 0];
}

// ── CSV helpers ───────────────────────────────────────────────────────────────
$csvHeaders = [
    'name', 'gender', 'color', 'country', 'year_of_birth',
    'sire_name', 'dam_name', 'champion', 'variety', 'uuid', 'link_name',
];

function open_csv(string $file, array $headers, bool $resume): mixed {
    if ($resume && file_exists($file)) {
        return fopen($file, 'a');
    }
    $fh = fopen($file, 'w');
    fprintf($fh, "\xEF\xBB\xBF"); // UTF-8 BOM para Excel
    fputcsv($fh, $headers);
    return $fh;
}

function map_animal(array $a): array {
    $titles = trim(($a['prefixTitles'] ?? '') . ' ' . ($a['suffixTitles'] ?? ''));
    return [
        'name'          => trim($a['registeredName'] ?? ''),
        'gender'        => ($a['sex'] ?? 0) == 1 ? 'male' : (($a['sex'] ?? 0) == 2 ? 'female' : 'unknown'),
        'color'         => trim($a['color'] ?? ''),
        'country'       => trim($a['landOfBirth'] ?? ''),
        'year_of_birth' => $a['yearOfBirth'] ?? '',
        'sire_name'     => trim($a['sireName'] ?? ''),
        'dam_name'      => trim($a['damName'] ?? ''),
        'champion'      => $titles ?: '',
        'variety'       => trim($a['variety'] ?? ''),
        'uuid'          => $a['uuid'] ?? '',
        'link_name'     => $a['linkName'] ?? '',
    ];
}

// ── Main ──────────────────────────────────────────────────────────────────────
log_msg('═══════════════════════════════════════', $config['log']);
log_msg('Scraper Breed Archive — Galgo Español', $config['log']);
log_msg('Batch: ' . $config['batch'] . ' | Delay: ' . $config['delay_min'] . '-' . $config['delay_max'] . 's', $config['log']);
log_msg('═══════════════════════════════════════', $config['log']);

$cp  = load_checkpoint($config['checkpoint']);
$start = $resume ? $cp['start'] : 0;
$saved = $resume ? $cp['saved'] : 0;

if ($resume && $start > 0) {
    log_msg("▶ Retomando desde posición $start ($saved galgos guardados)", $config['log']);
} else {
    $start = 0;
    $saved = 0;
    log_msg('▶ Iniciando desde el principio', $config['log']);
}

$fh      = open_csv($config['output'], $csvHeaders, $resume && $start > 0);
$hasMore = true;
$page    = 0;

while ($hasMore) {
    $url = $config['base_url'] . $config['endpoint']
         . '?start=' . $start . '&length=' . $config['batch'];

    log_msg("📄 Página " . ($page + 1) . " | start=$start | guardados=$saved", $config['log']);

    $res = fetch($url, $config);

    if (!$res['ok']) {
        log_msg('❌ No se pudo obtener la página. Guardando checkpoint y saliendo.', $config['log']);
        save_checkpoint($config['checkpoint'], ['start' => $start, 'total' => $cp['total'], 'saved' => $saved]);
        fclose($fh);
        exit(1);
    }

    $animals = $res['data']['animals'] ?? $res['data']['data'] ?? [];
    $hasMore = (bool)($res['data']['hasMore'] ?? false);

    if (empty($animals)) {
        log_msg('✅ Sin más resultados.', $config['log']);
        break;
    }

    foreach ($animals as $animal) {
        fputcsv($fh, array_values(map_animal($animal)));
        $saved++;
    }

    $start += count($animals);
    $page++;

    // Guardar checkpoint después de cada batch
    save_checkpoint($config['checkpoint'], [
        'start'  => $start,
        'total'  => $res['data']['total'] ?? 0,
        'saved'  => $saved,
        'updated'=> date('Y-m-d H:i:s'),
    ]);

    if ($hasMore) {
        // Delay aleatorio — patrón humano
        $delay = rand($config['delay_min'], $config['delay_max']);
        // Cada 10 páginas, pausa más larga (simula comportamiento humano)
        if ($page % 10 === 0) {
            $delay += rand(10, 25);
            log_msg("  ☕ Pausa larga: {$delay}s", $config['log']);
        } else {
            log_msg("  ⏳ Esperando {$delay}s...", $config['log']);
        }
        sleep($delay);
    }
}

fclose($fh);
log_msg("✅ COMPLETADO — $saved galgos guardados en {$config['output']}", $config['log']);
log_msg('═══════════════════════════════════════', $config['log']);
