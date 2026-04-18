<?php
/**
 * Scraper — FEG Intranet (Libro de Orígenes)
 *
 * Extrae ~15 000 galgos del intranet de fedegalgos.com:
 *   nombre, número de registro, sexo, propietario, padre y madre.
 *
 * USO:
 *   php tools/scraper_feg_lro.php --session=PHPSESSID_VALUE
 *
 * Opciones:
 *   --session=VALUE         Cookie PHPSESSID (obligatorio)
 *   --output=tools/data/feg_lro.csv
 *   --checkpoint=tools/data/feg_lro_checkpoint.json
 *   --log=tools/data/feg_lro.log
 *   --delay=5               Segundos base entre peticiones (default 5)
 *   --jitter=3              Jitter adicional máximo en segundos (default 3)
 *   --resume                Reanudar desde checkpoint (si existe)
 */

declare(strict_types=1);

// ── Config ────────────────────────────────────────────────────────────────────
$config = [
    'session'    => '',
    'output'     => __DIR__ . '/data/feg_lro.csv',
    'checkpoint' => __DIR__ . '/data/feg_lro_checkpoint.json',
    'log'        => __DIR__ . '/data/feg_lro.log',
    'delay'      => 5,
    'jitter'     => 3,
    'resume'     => false,
    'base_url'   => 'https://fedegalgos.com/intranet/index.php',
];

foreach ($argv as $arg) {
    if (preg_match('/^--(\w[\w-]*)(?:=(.*))?$/', $arg, $m)) {
        $key = str_replace('-', '_', $m[1]);
        $val = $m[2] ?? true;
        if (is_string($val) && is_numeric($val)) $val = (int)$val;
        $config[$key] = $val;
    }
}

if ($config['session'] === '') {
    fwrite(STDERR, "ERROR: Se requiere --session=PHPSESSID_VALUE\n");
    exit(1);
}

$dataDir = dirname($config['output']);
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

// ── Logger ────────────────────────────────────────────────────────────────────
function log_msg(string $msg): void {
    global $config;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    file_put_contents($config['log'], $line, FILE_APPEND);
}

// ── User Agents ───────────────────────────────────────────────────────────────
$USER_AGENTS = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Safari/605.1.15',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
];

// ── HTTP fetch ────────────────────────────────────────────────────────────────
/**
 * Returns ['body' => string, 'code' => int, 'expired' => bool]
 * expired=true when the session has been redirected to the login page.
 */
function fetch(string $url, string $referer = ''): array {
    global $config, $USER_AGENTS;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,   // handle redirects manually to detect login expiry
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => $USER_AGENTS[array_rand($USER_AGENTS)],
        CURLOPT_COOKIE         => 'PHPSESSID=' . $config['session'],
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,*/*;q=0.9',
            'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
            'Referer: ' . ($referer ?: $config['base_url']),
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_ENCODING       => 'gzip, deflate',
    ]);

    $body     = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    // 302 redirect to login = session expired
    $expired = ($code === 302 && str_contains((string)$location, 'login'))
        || ($code === 200 && is_string($body) && str_contains($body, 'name="usuario"') && str_contains($body, 'name="password"'));

    return ['body' => (string)$body, 'code' => $code, 'expired' => $expired];
}

// ── Session expiry handler ────────────────────────────────────────────────────
function handle_expiry(): void {
    global $config;
    log_msg('⚠️  SESIÓN CADUCADA — se necesita un nuevo PHPSESSID');
    echo "\n";
    echo "════════════════════════════════════════════════════════════\n";
    echo "  La sesión ha caducado. Para continuar:\n";
    echo "  1. Ve a https://fedegalgos.com/intranet/ en tu navegador\n";
    echo "  2. Inicia sesión\n";
    echo "  3. Abre DevTools → Application → Cookies → PHPSESSID\n";
    echo "  4. Pega el valor aquí y pulsa Enter:\n";
    echo "════════════════════════════════════════════════════════════\n";
    echo "  Nuevo PHPSESSID: ";
    $new = trim((string)fgets(STDIN));
    if ($new === '') {
        log_msg('❌ No se proporcionó nueva sesión. Abortando.');
        exit(1);
    }
    $config['session'] = $new;
    log_msg('🔑 Sesión actualizada. Continuando...');
}

// ── Anti-ban delay ────────────────────────────────────────────────────────────
function wait(): void {
    global $config;
    $secs = $config['delay'] + rand(0, (int)$config['jitter']);
    // Occasional longer pause (1 in 20 requests) to appear more human
    if (rand(1, 20) === 1) $secs += rand(8, 20);
    log_msg("  ⏳ Esperando {$secs}s...");
    sleep($secs);
}

// ── Checkpoint ────────────────────────────────────────────────────────────────
function load_checkpoint(): array {
    global $config;
    if ($config['resume'] && file_exists($config['checkpoint'])) {
        $data = json_decode(file_get_contents($config['checkpoint']), true);
        log_msg("📌 Checkpoint cargado: " . count($data['seen_ids'] ?? []) . " IDs ya procesados");
        return $data;
    }
    return ['seen_ids' => [], 'scanned_prefixes' => [], 'dogs' => []];
}

function save_checkpoint(array $data): void {
    global $config;
    file_put_contents($config['checkpoint'], json_encode($data, JSON_UNESCAPED_UNICODE));
}

// ── Parse genealogy search results ───────────────────────────────────────────
/**
 * Returns array of ['id' => int, 'name' => string, 'number' => string]
 */
function parse_search_results(string $html): array {
    $results = [];
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($dom);

    // Links with idGalgo parameter → unique dog IDs
    $links = $xpath->query('//a[contains(@href,"idGalgo=")]');
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        if (preg_match('/idGalgo=(\d+)/', $href, $m)) {
            $id   = (int)$m[1];
            $name = trim($link->textContent);
            if ($id > 0 && $name !== '') {
                $results[$id] = ['id' => $id, 'name' => $name, 'number' => ''];
            }
        }
    }

    // Fallback: any element with idGalgo= in data attributes
    if (empty($results)) {
        preg_match_all('/idGalgo=(\d+)/i', $html, $matches);
        foreach ($matches[1] as $id) {
            $results[(int)$id] = ['id' => (int)$id, 'name' => '', 'number' => ''];
        }
    }

    return array_values($results);
}

// ── Parse propietarios search results (tries HTML table) ─────────────────────
/**
 * Returns array of ['name' => string, 'number' => string, 'gender' => string, 'owner_name' => string]
 * The propietarios table may be empty (rendered by JS) — returns [] if so.
 */
function parse_propietarios(string $html): array {
    $results = [];
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($dom);

    // Try to find a <table> with rows containing Tipo column
    // Typical FEG table: Nombre | Número | Provincia | Tipo | Propietario
    $rows = $xpath->query('//table//tr[position()>1]');
    foreach ($rows as $row) {
        $cells = $xpath->query('.//td', $row);
        if ($cells->length < 4) continue;

        $name      = trim($cells->item(0)->textContent);
        $number    = trim($cells->item(1)->textContent);
        $tipo      = trim($cells->item(3)->textContent);
        $ownerName = $cells->length >= 5 ? trim($cells->item(4)->textContent) : '';

        if ($name === '') continue;

        $gender = 'unknown';
        if (stripos($tipo, 'semental') !== false) $gender = 'male';
        elseif (stripos($tipo, 'reproductora') !== false) $gender = 'female';
        elseif (stripos($tipo, 'macho') !== false) $gender = 'male';
        elseif (stripos($tipo, 'hembra') !== false) $gender = 'female';

        $results[] = compact('name', 'number', 'gender', 'ownerName');
    }

    // Try JSON data embedded in JavaScript (some implementations use this pattern)
    if (empty($results)) {
        if (preg_match('/var\s+\w*[Dd]ata\s*=\s*(\[.+?\]);/s', $html, $m)
            || preg_match('/\.data\(\s*(\[.+?\])\s*\)/s', $html, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) continue;
                    $vals      = array_values($row);
                    $name      = trim((string)($vals[0] ?? ''));
                    $number    = trim((string)($vals[1] ?? ''));
                    $tipo      = trim((string)($vals[3] ?? ''));
                    $ownerName = trim((string)($vals[4] ?? ''));
                    if ($name === '') continue;
                    $gender = 'unknown';
                    if (stripos($tipo, 'semental') !== false) $gender = 'male';
                    elseif (stripos($tipo, 'reproductora') !== false) $gender = 'female';
                    $results[] = compact('name', 'number', 'gender', 'ownerName');
                }
            }
        }
    }

    return $results;
}

// ── Parse fichaGenealogia detail page ─────────────────────────────────────────
/**
 * Returns [
 *   'name'    => string,   'number'  => string,
 *   'gender'  => string,   'dob'     => string,    (YYYY-MM-DD or '')
 *   'father'  => string,   'mother'  => string,
 *   'owner'   => string,   'club'    => string,
 * ]
 */
function parse_detail(string $html): array {
    $dog = [
        'name'   => '', 'number' => '', 'gender' => 'unknown',
        'dob'    => '', 'father' => '', 'mother' => '',
        'owner'  => '', 'club'   => '',
    ];

    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($dom);

    // ── Name ─────────────────────────────────────────────────────────────────
    // Try <h1>, <h2>, page title, or bold text at top
    foreach (['//h1', '//h2', '//title'] as $q) {
        $nodes = $xpath->query($q);
        if ($nodes->length > 0) {
            $t = trim($nodes->item(0)->textContent);
            if ($t && !preg_match('/fedegalgos|intranet/i', $t)) {
                $dog['name'] = $t;
                break;
            }
        }
    }

    // ── Parse all label:value pairs from the page ─────────────────────────────
    // Pattern: <strong>Label:</strong> Value  OR  label | value in <td> pairs
    $allText = $dom->textContent;

    // Registration number: "Número:", "Nº:", "LRO:", "Registro:"
    if (preg_match('/(?:N[úu]mero|N[°º]|LRO|Registro)[:\s]+([A-Z0-9\/\-]+)/i', $allText, $m)) {
        $dog['number'] = trim($m[1]);
    }

    // Gender: "Semental", "Reproductora", "Macho", "Hembra", "Sexo:"
    if (preg_match('/\b(semental|reproductora|macho|hembra)\b/i', $allText, $m)) {
        $t = strtolower($m[1]);
        $dog['gender'] = ($t === 'semental' || $t === 'macho') ? 'male' : 'female';
    }

    // Date of birth: "Fecha de nacimiento:", "Nacido:"
    if (preg_match('/(?:Fecha\s+de\s+nacimiento|Nacid[oa])\s*[:\s]+(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4}|\d{4})/i', $allText, $m)) {
        $raw = trim($m[1]);
        if (preg_match('/^(\d{4})$/', $raw, $y)) {
            $dog['dob'] = $y[1] . '-01-01';
        } elseif (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $raw, $p)) {
            $dog['dob'] = $p[3] . '-' . str_pad($p[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($p[1], 2, '0', STR_PAD_LEFT);
        }
    }

    // Owner
    if (preg_match('/Propietari[ao]\s*[:\s]+([^\n\r<]+)/i', $allText, $m)) {
        $dog['owner'] = trim($m[1]);
    }

    // Club
    if (preg_match('/Club\s*(?:Galguero)?\s*[:\s]+([^\n\r<]+)/i', $allText, $m)) {
        $dog['club'] = trim($m[1]);
    }

    // ── Pedigree tree — father and mother ─────────────────────────────────────
    // The pedigree tree is typically structured as a grid/table showing ancestors.
    // Position 1 (direct parents): father on top half, mother on bottom half.
    // We try multiple strategies.

    // Strategy 1: table cells — first two non-header rows are father, mother
    $pedigreeTables = $xpath->query('//table[contains(@class,"pedigree") or contains(@class,"genealog") or contains(@class,"arbol")]');
    if ($pedigreeTables->length > 0) {
        $rows = $xpath->query('.//tr', $pedigreeTables->item(0));
        $names = [];
        foreach ($rows as $row) {
            $text = trim($row->textContent);
            if ($text && strlen($text) > 2 && !preg_match('/^(padre|madre|sire|dam|nombre)/i', $text)) {
                $names[] = $text;
            }
            if (count($names) >= 2) break;
        }
        if (!empty($names[0])) $dog['father'] = $names[0];
        if (!empty($names[1])) $dog['mother'] = $names[1];
    }

    // Strategy 2: labeled rows — "Padre:" and "Madre:"
    if ($dog['father'] === '') {
        if (preg_match('/Padre\s*[:\s]+([A-ZÁÉÍÓÚÑÜ][^\n\r<]{2,60})/u', $allText, $m)) {
            $dog['father'] = trim($m[1]);
        }
    }
    if ($dog['mother'] === '') {
        if (preg_match('/Madre\s*[:\s]+([A-ZÁÉÍÓÚÑÜ][^\n\r<]{2,60})/u', $allText, $m)) {
            $dog['mother'] = trim($m[1]);
        }
    }

    // Strategy 3: div grid — first two uppercase-starting text blocks near a pedigree container
    if ($dog['father'] === '' || $dog['mother'] === '') {
        $containers = $xpath->query('//*[contains(@class,"pedigree") or contains(@class,"genealog") or contains(@id,"pedigree") or contains(@id,"genealog")]');
        if ($containers->length > 0) {
            $container = $containers->item(0);
            $cells = $xpath->query('.//*[self::div or self::td or self::span][not(descendant::*[self::div or self::td])]', $container);
            $names = [];
            foreach ($cells as $cell) {
                $t = trim($cell->textContent);
                if ($t && preg_match('/^[A-ZÁÉÍÓÚÑÜ]/u', $t) && strlen($t) >= 3 && strlen($t) <= 80) {
                    $names[] = $t;
                }
                if (count($names) >= 2) break;
            }
            if ($dog['father'] === '' && !empty($names[0])) $dog['father'] = $names[0];
            if ($dog['mother'] === '' && !empty($names[1])) $dog['mother'] = $names[1];
        }
    }

    // Clean up parent names — strip trailing garbage
    foreach (['father', 'mother'] as $p) {
        $dog[$p] = preg_replace('/\s{2,}/', ' ', $dog[$p]);
        $dog[$p] = trim($dog[$p], " \t\n\r\0\x0B/\\-_|");
        // Don't accept strings that look like labels
        if (preg_match('/^(padre|madre|nombre|número|fecha|propietario)/i', $dog[$p])) {
            $dog[$p] = '';
        }
    }

    return $dog;
}

// ── Generate all 2-letter prefixes A-Z ───────────────────────────────────────
function generate_prefixes(): array {
    // Start with single letters, then expand to 2-letter if needed
    $prefixes = [];
    for ($i = ord('A'); $i <= ord('Z'); $i++) {
        $prefixes[] = chr($i);
    }
    return $prefixes;
}

function expand_prefix(string $prefix): array {
    $expanded = [];
    for ($i = ord('A'); $i <= ord('Z'); $i++) {
        $expanded[] = $prefix . chr($i);
    }
    return $expanded;
}

// ── Try AJAX endpoint for propietarios ───────────────────────────────────────
/**
 * The propietarios table is rendered by JavaScript.
 * We try several common AJAX patterns used in older PHP/jQuery sites.
 */
function fetch_propietarios_ajax(string $prefix): array {
    global $config;
    $base = $config['base_url'];

    // Pattern 1: Same URL with extra params that trigger JSON/HTML response
    $attempts = [
        $base . '?buscarnombre=' . urlencode($prefix) . '&page=club_federado_propietarios&format=json',
        $base . '?buscarnombre=' . urlencode($prefix) . '&page=club_federado_propietarios&ajax=1',
        $base . '?buscarnombre=' . urlencode($prefix) . '&page=club_federado_propietarios&draw=1&start=0&length=100',
        $base . '?buscarnombre=' . urlencode($prefix) . '&page=club_federado_propietarios',  // plain (may have inline data)
    ];

    foreach ($attempts as $url) {
        $res = fetch($url, $base . '?page=club_federado_propietarios');
        if ($res['expired']) return ['expired' => true, 'data' => []];
        if ($res['code'] === 200 && strlen($res['body']) > 200) {
            // Try to detect if it's JSON
            $trimmed = ltrim($res['body']);
            if ($trimmed[0] === '{' || $trimmed[0] === '[') {
                $json = json_decode($res['body'], true);
                if (is_array($json)) {
                    return ['expired' => false, 'data' => parse_propietarios_json($json, $prefix)];
                }
            }
            // HTML table
            $rows = parse_propietarios($res['body']);
            if (!empty($rows)) {
                return ['expired' => false, 'data' => $rows];
            }
        }
        usleep(800_000); // 0.8s between attempts
    }

    return ['expired' => false, 'data' => []];
}

function parse_propietarios_json(array $json, string $prefix): array {
    $results = [];
    // DataTables format: {'data': [[col0, col1, ...], ...]}
    $rows = $json['data'] ?? (isset($json[0]) ? $json : []);
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $vals      = array_values($row);
        $name      = trim(strip_tags((string)($vals[0] ?? '')));
        $number    = trim(strip_tags((string)($vals[1] ?? '')));
        $tipo      = trim(strip_tags((string)($vals[3] ?? '')));
        $ownerName = trim(strip_tags((string)($vals[4] ?? '')));
        if ($name === '' || !str_starts_with(strtoupper($name), $prefix)) continue;
        $gender = 'unknown';
        if (stripos($tipo, 'semental') !== false) $gender = 'male';
        elseif (stripos($tipo, 'reproductora') !== false) $gender = 'female';
        $results[] = compact('name', 'number', 'gender', 'ownerName');
    }
    return $results;
}

// ── Main ──────────────────────────────────────────────────────────────────────
log_msg('═══════════════════════════════════════════════════════════');
log_msg('Scraper FEG LRO — Libro de Orígenes (v2)');
log_msg('═══════════════════════════════════════════════════════════');

$cp = load_checkpoint();
$seenIds     = array_flip($cp['seen_ids']   ?? []);  // id → true
$seenPfx     = array_flip($cp['scanned_prefixes'] ?? []);
$dogsMap     = [];  // id → dog data
foreach ($cp['dogs'] ?? [] as $d) {
    $dogsMap[$d['id'] ?? 0] = $d;
}

// Build propietarios index from checkpoint
$propietariosMap = []; // lowercase_name → ['gender', 'number', 'owner']
foreach ($cp['propietarios'] ?? [] as $p) {
    $propietariosMap[strtolower($p['name'])] = $p;
}

// ── Phase 1: Collect dog IDs via genealogy search ────────────────────────────
log_msg('');
log_msg('FASE 1 — Búsqueda genealógica por prefijo');
log_msg('─────────────────────────────────────────');

$prefixes   = generate_prefixes();
$prefixQueue = $prefixes;

$pIdx = 0;
while (!empty($prefixQueue)) {
    $prefix = array_shift($prefixQueue);
    $pIdx++;

    if (isset($seenPfx[$prefix])) {
        log_msg("  ↩ Prefijo \"$prefix\" ya escaneado, saltando.");
        continue;
    }

    $url = $config['base_url'] . '?buscarnombre=' . urlencode($prefix)
         . '&buscarnumero=&buscarafijo=&page=club_federado_genealogia';

    log_msg("🔍 Prefijo \"$prefix\" — $url");

    $res = fetch($url, $config['base_url'] . '?page=club_federado_genealogia');

    if ($res['expired']) {
        handle_expiry();
        // Retry same prefix after new session
        array_unshift($prefixQueue, $prefix);
        continue;
    }

    if ($res['code'] !== 200) {
        log_msg("  ⚠️ HTTP {$res['code']} — reintentando en 30s...");
        sleep(30);
        array_unshift($prefixQueue, $prefix);
        continue;
    }

    $found = parse_search_results($res['body']);
    $new   = 0;
    foreach ($found as $dog) {
        if (!isset($seenIds[$dog['id']])) {
            $seenIds[$dog['id']] = true;
            $dogsMap[$dog['id']] = ['id' => $dog['id'], 'name' => $dog['name'], 'number' => $dog['number'],
                                    'gender' => 'unknown', 'dob' => '', 'father' => '', 'mother' => '',
                                    'owner' => '', 'club' => ''];
            $new++;
        }
    }

    $total = count($seenIds);
    log_msg("  ✓ {$new} nuevos ({$total} total)");

    // If exactly 20 results were returned, the page is likely truncated — expand prefix
    if (count($found) >= 20) {
        $expanded = expand_prefix($prefix);
        log_msg("  ⚡ 20 resultados — expandiendo a prefijos de 2 letras (" . count($expanded) . ")");
        $prefixQueue = array_merge($expanded, $prefixQueue);
    }

    $seenPfx[$prefix] = true;

    // Save checkpoint every 10 prefixes
    if ($pIdx % 10 === 0) {
        save_checkpoint([
            'seen_ids'          => array_keys($seenIds),
            'scanned_prefixes'  => array_keys($seenPfx),
            'dogs'              => array_values($dogsMap),
            'propietarios'      => array_values($propietariosMap),
        ]);
        log_msg("  💾 Checkpoint guardado.");
    }

    wait();
}

log_msg('');
log_msg('Total IDs recopilados: ' . count($seenIds));

// ── Phase 2: Fetch propietarios for gender + owner name ──────────────────────
log_msg('');
log_msg('FASE 2 — Propietarios (sexo + propietario)');
log_msg('──────────────────────────────────────────');

$allPrefixes = generate_prefixes();
foreach ($allPrefixes as $prefix) {
    $cacheKey = 'prop_' . $prefix;
    if (isset($seenPfx[$cacheKey])) continue;

    log_msg("👤 Propietarios \"$prefix\"");

    $result = fetch_propietarios_ajax($prefix);
    if ($result['expired']) {
        handle_expiry();
        // Retry
        $result = fetch_propietarios_ajax($prefix);
    }

    $propRows = $result['data'];
    if (!empty($propRows)) {
        foreach ($propRows as $p) {
            $key = strtolower($p['name']);
            $propietariosMap[$key] = $p;
        }
        log_msg("  ✓ " . count($propRows) . " entradas de propietarios");
    } else {
        log_msg("  ⚠️ Sin datos (tabla JS — solo genealogía disponible para este prefijo)");
    }

    $seenPfx[$cacheKey] = true;

    if (count($propRows) >= 20) {
        // Not expanding here — propietarios may have a different pagination
        log_msg("  ⚡ 20 resultados — considera expandir este prefijo manualmente si faltan datos");
    }

    // Checkpoint every 5 propietarios prefixes
    static $propCount = 0;
    $propCount++;
    if ($propCount % 5 === 0) {
        save_checkpoint([
            'seen_ids'         => array_keys($seenIds),
            'scanned_prefixes' => array_keys($seenPfx),
            'dogs'             => array_values($dogsMap),
            'propietarios'     => array_values($propietariosMap),
        ]);
    }

    wait();
}

// Merge propietarios data into dogs map
foreach ($dogsMap as $id => &$dog) {
    $key = strtolower($dog['name']);
    if (isset($propietariosMap[$key])) {
        $p = $propietariosMap[$key];
        if ($dog['gender'] === 'unknown' && isset($p['gender'])) $dog['gender'] = $p['gender'];
        if ($dog['number'] === '' && isset($p['number']))         $dog['number'] = $p['number'];
        if ($dog['owner'] === ''  && isset($p['ownerName']))      $dog['owner']  = $p['ownerName'];
    }
}
unset($dog);

// ── Phase 3: Fetch detail pages for parents + fill missing gender ─────────────
log_msg('');
log_msg('FASE 3 — Fichas genealógicas (padres, sexo, número)');
log_msg('────────────────────────────────────────────────────');

$detailQueue = [];
foreach ($dogsMap as $id => $dog) {
    if ($dog['father'] === '' && $dog['mother'] === '') {
        $detailQueue[] = $id;
    }
}

log_msg('Fichas a obtener: ' . count($detailQueue));

$dIdx = 0;
foreach ($detailQueue as $id) {
    $dIdx++;
    $progress = sprintf('%d/%d', $dIdx, count($detailQueue));

    if (isset($seenIds["detail_$id"])) {
        continue;
    }

    $url = $config['base_url'] . '?page=club_federado_fichaGenealogia&idGalgo=' . $id;
    log_msg("📋 [$progress] Galgo #$id — $url");

    $res = fetch($url, $config['base_url'] . '?page=club_federado_genealogia');

    if ($res['expired']) {
        handle_expiry();
        $res = fetch($url, $config['base_url'] . '?page=club_federado_genealogia');
    }

    if ($res['code'] !== 200 || strlen($res['body']) < 200) {
        log_msg("  ⚠️ HTTP {$res['code']} — saltando.");
        $seenIds["detail_$id"] = true;
        continue;
    }

    $detail = parse_detail($res['body']);

    // Apply detail data (don't overwrite existing good values)
    $dog = &$dogsMap[$id];
    if ($dog['name'] === ''    && $detail['name'] !== '')   $dog['name']   = $detail['name'];
    if ($dog['number'] === ''  && $detail['number'] !== '') $dog['number'] = $detail['number'];
    if ($dog['gender'] === 'unknown' && $detail['gender'] !== 'unknown') $dog['gender'] = $detail['gender'];
    if ($dog['dob'] === ''     && $detail['dob'] !== '')    $dog['dob']    = $detail['dob'];
    if ($dog['father'] === ''  && $detail['father'] !== '') $dog['father'] = $detail['father'];
    if ($dog['mother'] === ''  && $detail['mother'] !== '') $dog['mother'] = $detail['mother'];
    if ($dog['owner'] === ''   && $detail['owner'] !== '')  $dog['owner']  = $detail['owner'];
    if ($dog['club'] === ''    && $detail['club'] !== '')   $dog['club']   = $detail['club'];
    unset($dog);

    $seenIds["detail_$id"] = true;

    log_msg(sprintf('  ✓ %s | sexo:%s | padre:%s | madre:%s',
        $dogsMap[$id]['name'] ?: "id=$id",
        $dogsMap[$id]['gender'],
        $dogsMap[$id]['father'] ?: '—',
        $dogsMap[$id]['mother'] ?: '—'));

    // Checkpoint every 50 detail fetches
    if ($dIdx % 50 === 0) {
        save_checkpoint([
            'seen_ids'         => array_keys($seenIds),
            'scanned_prefixes' => array_keys($seenPfx),
            'dogs'             => array_values($dogsMap),
            'propietarios'     => array_values($propietariosMap),
        ]);
        log_msg("  💾 Checkpoint guardado ($dIdx fichas procesadas).");
    }

    wait();
}

// ── Write CSV ─────────────────────────────────────────────────────────────────
log_msg('');
log_msg('FASE 4 — Exportando CSV');
log_msg('────────────────────────');

$csvHeaders = ['name','gender','color','country','year_of_birth','sire_name','dam_name','champion','variety','uuid','link_name','registration_number','owner_name'];

$fh = fopen($config['output'], 'w');
fprintf($fh, "\xEF\xBB\xBF"); // UTF-8 BOM
fputcsv($fh, $csvHeaders);

$exported = 0;
$noName   = 0;
foreach ($dogsMap as $dog) {
    if ($dog['name'] === '') { $noName++; continue; }

    $yearOfBirth = '';
    if ($dog['dob'] !== '') $yearOfBirth = substr($dog['dob'], 0, 4);

    fputcsv($fh, [
        $dog['name'],
        $dog['gender'],
        '',          // color — not available in LRO
        'España',
        $yearOfBirth,
        $dog['father'],
        $dog['mother'],
        '',          // champion — not in LRO
        '',          // variety
        '',          // uuid
        '',          // link_name
        $dog['number'],
        $dog['owner'],
    ]);
    $exported++;
}
fclose($fh);

// Final checkpoint
save_checkpoint([
    'seen_ids'         => array_keys($seenIds),
    'scanned_prefixes' => array_keys($seenPfx),
    'dogs'             => array_values($dogsMap),
    'propietarios'     => array_values($propietariosMap),
]);

log_msg('═══════════════════════════════════════════════════════════');
log_msg("✅ COMPLETADO");
log_msg("   Galgos exportados : $exported");
log_msg("   Sin nombre (omit) : $noName");
log_msg("   Archivo CSV       : {$config['output']}");
log_msg('═══════════════════════════════════════════════════════════');
