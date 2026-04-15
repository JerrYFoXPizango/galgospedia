<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
    $siteBase    = 'https://galgospedia.com';
    $titleSuffix = ' — Enciclopedia del Galgo Español';
    $metaTitle   = htmlspecialchars(($pageTitle ?? 'Galgospedia') . $titleSuffix);
    $metaDesc    = htmlspecialchars($pageDesc ?? 'Galgospedia es el registro genealógico del Galgo Español. Consulta sementales, reproductoras, torneos y carreras de galgos en España.');
    $metaUrl     = htmlspecialchars($siteBase . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
    $metaImage   = htmlspecialchars($ogImage  ?? $siteBase . '/logo/logo930-930.png');
    $metaType    = htmlspecialchars($ogType   ?? 'website');
?>
    <title><?= $metaTitle ?></title>
    <meta name="description" content="<?= $metaDesc ?>">
    <link rel="canonical" href="<?= $metaUrl ?>">
    <meta name="robots" content="<?= htmlspecialchars($metaRobots ?? 'index, follow') ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="<?= $metaType ?>">
    <meta property="og:site_name"   content="Galgospedia">
    <meta property="og:title"       content="<?= $metaTitle ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <meta property="og:url"         content="<?= $metaUrl ?>">
    <meta property="og:image"       content="<?= $metaImage ?>">
    <meta property="og:locale"      content="es_ES">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $metaTitle ?>">
    <meta name="twitter:description" content="<?= $metaDesc ?>">
    <meta name="twitter:image"       content="<?= $metaImage ?>">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" type="image/png">
    <link rel="icon" href="/logo/logo512-512.png" type="image/png" sizes="512x512">
    <link rel="shortcut icon" href="/favicon.ico">
    <!-- PWA / Mobile App Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Galgospedia">
    <link rel="apple-touch-icon" href="/logo/logo512-512.png">
    <!-- Tailwind CSS (compiled) -->
    <link rel="stylesheet" href="/css/app.css">
    <!-- Print / PDF styles -->
    <link rel="stylesheet" href="/css/print.css">

    <!-- Preload hero font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <!-- Alpine.js (deferred) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <?php $gaId = \Config\Config::gaId(); if ($gaId): ?>
    <!-- Google Analytics 4 — Consent Mode v2 (RGPD compliant) -->
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        // Default: deny analytics until user consents
        gtag('consent', 'default', {
            analytics_storage: 'denied',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            wait_for_update: 500
        });
        // If user already accepted cookies, grant immediately
        if (localStorage.getItem('ga_consent') === 'accepted') {
            gtag('consent', 'update', { analytics_storage: 'granted' });
        }
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaId) ?>"></script>
    <script>
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($gaId) ?>', { anonymize_ip: true });
    </script>
    <?php endif; ?>

    <?php if (!empty($extraHead)) echo $extraHead; /* internal use only — never pass user input here */ ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col">

<?php require APP_PATH . '/Views/layout/nav.php'; ?>

<!-- Flash messages -->
<?php $flashHtml = \Helpers\Flash::render(); if ($flashHtml): ?>
<div class="container mx-auto px-4 mt-4">
    <?= $flashHtml ?>
</div>
<?php endif; ?>

<main class="flex-1">
