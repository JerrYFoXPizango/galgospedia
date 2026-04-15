<?php
$pageTitle = 'Editar torneo — ' . htmlspecialchars($tournament['title'] ?? '');
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">
    <a href="/torneos/<?= htmlspecialchars($tournament['slug']) ?>"
       class="text-sm text-gray-400 hover:text-gray-600 inline-flex items-center gap-1 mb-5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Ver torneo
    </a>

    <h1 class="text-2xl font-display font-bold mb-6">Editar torneo</h1>

    <?php
    $formAction = '/torneos/' . htmlspecialchars($tournament['slug']) . '/actualizar';
    require APP_PATH . '/Views/tournaments/form.php';
    ?>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
