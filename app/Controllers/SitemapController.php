<?php

declare(strict_types=1);

namespace Controllers;

use Config\{Config, Database};

class SitemapController extends BaseController
{
    public function index(): void
    {
        $base = 'https://galgospedia.com';
        $now  = date('Y-m-d');

        $urls = [];

        // ── Páginas estáticas ─────────────────────────────────────────
        $statics = [
            ['loc' => '/',              'priority' => '1.0', 'freq' => 'daily'],
            ['loc' => '/galgos',        'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => '/sementales',    'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => '/reproductoras', 'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => '/torneos',       'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => '/oficina',       'priority' => '0.5', 'freq' => 'monthly'],
            ['loc' => '/apps',          'priority' => '0.4', 'freq' => 'monthly'],
            ['loc' => '/privacidad',    'priority' => '0.2', 'freq' => 'yearly'],
            ['loc' => '/aviso-legal',   'priority' => '0.2', 'freq' => 'yearly'],
            ['loc' => '/cookies',       'priority' => '0.2', 'freq' => 'yearly'],
        ];

        foreach ($statics as $s) {
            $urls[] = [
                'loc'        => $base . $s['loc'],
                'lastmod'    => $now,
                'changefreq' => $s['freq'],
                'priority'   => $s['priority'],
            ];
        }

        // ── Galgos ────────────────────────────────────────────────────
        $pdo  = Database::pdo();
        $dogs = $pdo->query(
            "SELECT slug, updated_at FROM dogs WHERE is_public = 1 ORDER BY updated_at DESC"
        )->fetchAll();

        foreach ($dogs as $dog) {
            $urls[] = [
                'loc'        => $base . '/galgos/' . rawurlencode($dog['slug']),
                'lastmod'    => substr($dog['updated_at'] ?? $now, 0, 10),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        // ── Torneos ───────────────────────────────────────────────────
        $tournaments = $pdo->query(
            "SELECT slug, updated_at FROM tournaments WHERE status IN ('published','finished') ORDER BY updated_at DESC"
        )->fetchAll();

        foreach ($tournaments as $t) {
            $urls[] = [
                'loc'        => $base . '/torneos/' . rawurlencode($t['slug']),
                'lastmod'    => substr($t['updated_at'] ?? $now, 0, 10),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }

        // ── Output XML ────────────────────────────────────────────────
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            echo "    <lastmod>{$url['lastmod']}</lastmod>\n";
            echo "    <changefreq>{$url['changefreq']}</changefreq>\n";
            echo "    <priority>{$url['priority']}</priority>\n";
            echo "  </url>\n";
        }

        echo '</urlset>';
        exit;
    }
}
