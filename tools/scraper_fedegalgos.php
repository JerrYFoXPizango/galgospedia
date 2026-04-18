<?php
/**
 * Scraper — fedegalgos.com (Campeonatos de España)
 *
 * USO:
 *   php tools/scraper_fedegalgos.php
 *
 * Opciones:
 *   --output=tools/data/fedegalgos.csv
 *   --delay=5   Segundos entre peticiones (default: 5)
 */

declare(strict_types=1);

$config = [
    'output'    => __DIR__ . '/data/fedegalgos.csv',
    'log'       => __DIR__ . '/data/fedegalgos.log',
    'delay'     => 5,
    'urls'      => [
        'https://www.fedegalgos.com/project/2020-lxxxii-campeonato-de-espana-de-galgos-en-campo-copa-s-m-el-rey-copy/',
        'https://www.fedegalgos.com/project/2020-lxxxii-campeonato-de-espana-de-galgos-en-campo-copa-s-m-el-rey-copy-copy/',
        'https://www.fedegalgos.com/project/2020-lxxxii-campeonato-de-espana-de-galgos-en-campo-copa-s-m-el-rey-copy-copy-copy/',
        'https://www.fedegalgos.com/project/2020-lxxxii-campeonato-de-espana-de-galgos-en-campo-copa-s-m-el-rey-copy-copy-copy-2/',
        'https://www.fedegalgos.com/project/2020-lxxxii-campeonato-de-espana-de-galgos-en-campo-copa-s-m-el-rey-copy-copy-copy-2-copy/',
        'https://www.fedegalgos.com/project/2020-lxxxii-campeonato-de-espana-de-galgos-en-campo-copa-s-m-el-rey-copy-copy-copy-2-copy-2/',
    ],
];

foreach ($argv as $arg) {
    if (preg_match('/^--(\w+)=(.+)$/', $arg, $m)) {
        $config[$m[1]] = is_numeric($m[2]) ? (int)$m[2] : $m[2];
    }
}

$dataDir = dirname($config['output']);
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

// ── Logger ────────────────────────────────────────────────────────────────────
function log_msg(string $msg, string $logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

// ── HTTP fetch ────────────────────────────────────────────────────────────────
function fetch_html(string $url): ?string {
    $agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/17.4.1 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => $agents[array_rand($agents)],
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,*/*',
            'Accept-Language: es-ES,es;q=0.9',
            'Referer: https://www.fedegalgos.com/',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($code === 200 && $body) ? $body : null;
}

// ── Date parser — "19 de abril de 2023" → "2023-04-19" ───────────────────────
function parse_date(string $text): string {
    $months = [
        'enero'=>'01','febrero'=>'02','marzo'=>'03','abril'=>'04',
        'mayo'=>'05','junio'=>'06','julio'=>'07','agosto'=>'08',
        'septiembre'=>'09','octubre'=>'10','noviembre'=>'11','diciembre'=>'12',
    ];
    if (preg_match('/(\d{1,2})\s+de\s+(\w+)\s+de\s+(\d{4})/i', $text, $m)) {
        $month = $months[strtolower($m[2])] ?? '01';
        return $m[3] . '-' . $month . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    }
    // Fallback: just the year
    if (preg_match('/(\d{4})/', $text, $m)) return $m[1] . '-01-01';
    return '';
}

// ── Parse dogs from HTML ──────────────────────────────────────────────────────
function parse_dogs(string $html): array {
    $dogs = [];

    // Each dog is inside a <div class="et_pb_text_inner"> block
    // that starts with <p><strong>NAME</strong></p>
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($dom);

    // Find all et_pb_text_inner divs that contain a <strong> as first meaningful element
    $divs = $xpath->query('//div[contains(@class,"et_pb_text_inner")]');

    foreach ($divs as $div) {
        // Get all <p> children
        $paras = $xpath->query('.//p', $div);
        if ($paras->length === 0) continue;

        // First <p> must have a <strong> with the dog name
        $firstP  = $paras->item(0);
        $strongs = $xpath->query('.//strong', $firstP);
        if ($strongs->length === 0) continue;

        $name = trim(rtrim($strongs->item(0)->textContent, ':. '));
        // Strip championship prefix from name: "CAMPEONA DE ESPAÑA ZAÍNA..." → "ZAÍNA..."
        $name = preg_replace('/^(?:SUB)?CAMPE[OÓ]N[AO]?\s+DE\s+ESPAÑA\s+/iu', '', $name);
        $name = trim($name);
        // Skip non-dog entries: too short, section headings, lowercase starts, or sentences
        if (strlen($name) < 4) continue;
        if (preg_match('/^\d|^(lugar|sorteo|cuartos|octavos|semi|final|gala|participantes|clasif|campeonato|lo que|estadis)/i', $name)) continue;
        // Must start with uppercase — dog names never start lowercase
        if (!preg_match('/^[A-ZÁÉÍÓÚÑÜ]/u', $name)) continue;
        // Skip if it looks like a sentence (contains comma+space mid-text suggesting prose)
        if (preg_match('/[a-záéíóú]{4,},\s+[a-záéíóú]/u', $name)) continue;

        $gender        = 'unknown';
        $color         = '';
        $date_of_birth = '';
        $year_of_birth = '';
        $champion      = '';
        $owner_name    = '';
        $club          = '';

        for ($i = 1; $i < $paras->length; $i++) {
            $text = trim($paras->item($i)->textContent);
            if ($text === '') continue;

            // "Hembra, barcina. Nacida el 19 de abril de 2023"
            // "Macho, colorado. Nacido el 5 de marzo de 2022"
            if (preg_match('/^(Hembra|Macho)[,.]?\s*(.*?)\.\s*Naci[do]{2}\s+el\s+(.+)/i', $text, $m)) {
                $gender        = strtolower($m[1]) === 'hembra' ? 'female' : 'male';
                $color         = trim(rtrim($m[2], '.'));
                $date_of_birth = parse_date($m[3]);
                $year_of_birth = substr($date_of_birth, 0, 4);
                continue;
            }
            // Line with only gender+color+date but different format
            if (preg_match('/^(Hembra|Macho)/i', $text, $m)) {
                $gender = strtolower($m[1]) === 'hembra' ? 'female' : 'male';
                if (preg_match('/Naci[do]{2}\s+el\s+(.+)/i', $text, $dm)) {
                    $date_of_birth = parse_date($dm[1]);
                    $year_of_birth = substr($date_of_birth, 0, 4);
                }
                if (preg_match('/^(?:Hembra|Macho)[,.]?\s*([^.]+)\./i', $text, $cm)) {
                    $color = trim($cm[1]);
                }
                continue;
            }
            if (preg_match('/^Propietario[:\s]+(.+)/i', $text, $m)) {
                $owner_name = trim($m[1]);
                continue;
            }
            if (preg_match('/^Club\s+Galguero[:\s]+(.+)/i', $text, $m)) {
                $club = trim($m[1]);
                continue;
            }
            // Remaining non-empty lines after the first are likely champion titles
            if ($i >= 2 && $champion === '' && !preg_match('/^Representante/i', $text)) {
                // Skip owner/club lines already caught
                if (!preg_match('/^(Propietari[ao]|Club|Representante)/i', $text)) {
                    $champion = $text;
                }
            }
        }

        if ($name === '') continue;

        $dogs[] = compact('name','gender','color','date_of_birth','year_of_birth','champion','owner_name','club');
    }

    return $dogs;
}

// ── CSV ───────────────────────────────────────────────────────────────────────
$csvHeaders = ['name','gender','color','country','year_of_birth','sire_name','dam_name','champion','variety','uuid','link_name'];

$fh = fopen($config['output'], 'w');
fprintf($fh, "\xEF\xBB\xBF"); // UTF-8 BOM
fputcsv($fh, $csvHeaders);

// ── Main ──────────────────────────────────────────────────────────────────────
log_msg('═══════════════════════════════════════', $config['log']);
log_msg('Scraper FEG — Campeonatos de España', $config['log']);
log_msg('═══════════════════════════════════════', $config['log']);

$totalSaved = 0;
$seen       = []; // deduplicate within this run

foreach ($config['urls'] as $i => $url) {
    log_msg("📄 Página " . ($i + 1) . "/" . count($config['urls']) . " — $url", $config['log']);

    $html = fetch_html($url);
    if (!$html) {
        log_msg("  ❌ No se pudo obtener la página.", $config['log']);
        continue;
    }

    $dogs = parse_dogs($html);
    $saved = 0;

    foreach ($dogs as $dog) {
        $key = strtolower($dog['name']);
        if (isset($seen[$key])) continue; // dedup within run
        $seen[$key] = true;

        fputcsv($fh, [
            $dog['name'],
            $dog['gender'],
            $dog['color'],
            'España',
            $dog['year_of_birth'],
            '', // sire_name — not available (pedigree is image)
            '', // dam_name
            $dog['champion'],
            '', // variety
            '', // uuid
            '', // link_name
        ]);
        $saved++;
        $totalSaved++;
    }

    log_msg("  ✅ $saved galgos extraídos (total acumulado: $totalSaved)", $config['log']);

    if ($i < count($config['urls']) - 1) {
        $delay = $config['delay'] + rand(0, 3);
        log_msg("  ⏳ Esperando {$delay}s...", $config['log']);
        sleep($delay);
    }
}

fclose($fh);
log_msg('═══════════════════════════════════════', $config['log']);
log_msg("✅ COMPLETADO — $totalSaved galgos guardados en {$config['output']}", $config['log']);
log_msg('═══════════════════════════════════════', $config['log']);
