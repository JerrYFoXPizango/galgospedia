<?php
$pageTitle = 'Árbol de ' . htmlspecialchars($dog['name']);
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>';
require APP_PATH . '/Views/layout/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <a href="/galgos/<?= htmlspecialchars($dog['slug']) ?>" class="text-sm text-gray-500 hover:text-galgo-red">
                ← <?= htmlspecialchars($dog['name']) ?>
            </a>
            <h1 class="text-2xl font-display font-bold mt-1">Árbol Genealógico</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <label class="text-sm text-gray-500">Generaciones:</label>
            <select id="gen-select" class="form-input form-input-sm w-24">
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5" selected>5</option>
                <option value="6">6</option>
                <option value="8">8</option>
                <option value="10">10</option>
            </select>
            <?php
            $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $treeUrl  = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/arbol/' . urlencode($dog['slug']);
            $treeText = urlencode('🌳 Árbol genealógico de ' . $dog['name'] . ' — Galgospedia');
            ?>
            <!-- Instagram -->
            <button id="btn-instagram-tree"
               class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
               style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:radial-gradient(circle at 30% 107%,#fdf497 0%,#fdf497 5%,#fd5949 45%,#d6249f 60%,#285AEB 90%)">
                <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.308.975.975 1.246 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.308 3.608-.975.975-2.242 1.246-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.334-3.608-1.308-.975-.975-1.246-2.242-1.308-3.608C2.175 15.584 2.163 15.204 2.163 12s.012-3.584.07-4.85c.062-1.366.334-2.633 1.308-3.608C4.516 2.497 5.783 2.226 7.149 2.163 8.415 2.105 8.795 2.163 12 2.163zm0-2.163C8.741 0 8.332.014 7.052.072 5.197.157 3.355.673 2.014 2.014.673 3.355.157 5.197.072 7.052.014 8.332 0 8.741 0 12c0 3.259.014 3.668.072 4.948.085 1.855.601 3.697 1.942 5.038 1.341 1.341 3.183 1.857 5.038 1.942C8.332 23.986 8.741 24 12 24s3.668-.014 4.948-.072c1.855-.085 3.697-.601 5.038-1.942 1.341-1.341 1.857-3.183 1.942-5.038.058-1.28.072-1.689.072-4.948s-.014-3.668-.072-4.948c-.085-1.855-.601-3.697-1.942-5.038C20.645.673 18.803.157 16.948.072 15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.162a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                </svg>
                <span>Instagram</span>
            </button>
            <!-- WhatsApp -->
            <a href="https://wa.me/?text=<?= $treeText ?>%20<?= urlencode($treeUrl) ?>"
               target="_blank" rel="noopener"
               class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
               style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:#25D366">
                <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 32 32" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 3C8.82 3 3 8.82 3 16c0 2.34.63 4.53 1.73 6.43L3 29l6.75-1.69A13 13 0 0 0 16 29c7.18 0 13-5.82 13-13S23.18 3 16 3zm0 23.85a10.84 10.84 0 0 1-5.53-1.52l-.4-.23-4.01 1 1.02-3.9-.26-.41A10.85 10.85 0 1 1 16 26.85zm5.95-8.1c-.33-.16-1.94-.96-2.24-1.07-.3-.1-.52-.16-.73.17-.22.33-.84 1.07-1.03 1.28-.19.22-.38.25-.71.08-.33-.16-1.39-.51-2.65-1.63-.98-.87-1.64-1.95-1.83-2.28-.19-.33-.02-.5.14-.67.15-.14.33-.38.5-.57.16-.19.22-.33.33-.55.11-.22.06-.41-.03-.57-.08-.16-.73-1.77-1-2.43-.26-.63-.53-.55-.73-.56h-.62c-.22 0-.57.08-.86.41-.3.33-1.13 1.1-1.13 2.69s1.16 3.12 1.32 3.34c.16.22 2.28 3.48 5.52 4.88.77.33 1.37.53 1.84.68.77.25 1.48.21 2.03.13.62-.09 1.91-.78 2.18-1.54.27-.75.27-1.4.19-1.54-.08-.13-.3-.22-.63-.38z"/>
                </svg>
                <span>WhatsApp</span>
            </a>
            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($treeUrl) ?>"
               target="_blank" rel="noopener"
               class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
               style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:#1877F2">
                <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.5 8.5H16V5.5H13.5C11.57 5.5 10 7.07 10 9v1.5H8V13.5h2V22h3v-8.5h2.5l.5-3H13V9c0-.28.22-.5.5-.5z"/>
                </svg>
                <span>Facebook</span>
            </a>
            <!-- X / Twitter -->
            <a href="https://twitter.com/intent/tweet?text=<?= $treeText ?>&url=<?= urlencode($treeUrl) ?>"
               target="_blank" rel="noopener"
               class="px-3 py-1.5 rounded-lg text-white text-sm font-medium transition"
               style="display:inline-flex;align-items:center;gap:8px;flex-direction:row;background:#000">
                <svg style="display:block;flex-shrink:0;width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.75 3h3.08l-6.73 7.7L22 21h-6.24l-4.38-5.73L5.9 21H2.82l7.2-8.23L2 3h6.4l3.96 5.23L17.75 3zm-1.08 16.2h1.7L7.42 4.74H5.6l11.07 14.46z"/>
                </svg>
                <span>X</span>
            </a>
            <!-- Copiar enlace -->
            <button id="btn-copy-tree"
               class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition"
               style="display:inline-flex;align-items:center;gap:6px;flex-direction:row">
                <svg style="display:block;flex-shrink:0;width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                <span>Copiar enlace</span>
            </button>
            <!-- PDF -->
            <button onclick="window.print()"
               class="px-3 py-1.5 rounded-lg bg-galgo-red text-white text-sm font-medium hover:bg-red-700 transition"
               style="display:inline-flex;align-items:center;gap:6px;flex-direction:row">
                <svg style="display:block;flex-shrink:0;width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Guardar PDF</span>
            </button>
            <!-- Exportar SVG -->
            <button onclick="exportSVG()" class="btn-outline text-sm">Exportar SVG</button>
        </div>
    </div>

    <!-- Tree container -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="tree-loading" class="flex items-center justify-center h-96 text-gray-400">
            <div class="text-center">
                <div class="animate-spin text-4xl mb-4">🌀</div>
                <p>Cargando árbol genealógico...</p>
            </div>
        </div>
        <div id="tree-error" class="hidden flex items-center justify-center h-96 text-gray-400">
            <div class="text-center">
                <div class="text-4xl mb-4">⚠️</div>
                <p>No se pudo cargar el árbol. <button onclick="loadTree()" class="text-galgo-red underline">Reintentar</button></p>
            </div>
        </div>
        <svg id="tree-svg" class="hidden w-full" style="min-height: 500px;"></svg>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-4 mt-4 text-xs text-gray-500">
        <div class="flex items-center gap-1.5"><div class="w-4 h-4 rounded border-2 border-galgo-red bg-red-50"></div> Macho</div>
        <div class="flex items-center gap-1.5"><div class="w-4 h-4 rounded border-2 border-galgo-gold bg-yellow-50"></div> Hembra</div>
        <div class="flex items-center gap-1.5"><div class="w-4 h-4 rounded border-2 border-blue-400 bg-blue-50"></div> Semental</div>
        <div class="flex items-center gap-1.5"><div class="w-4 h-4 rounded border-2 border-pink-400 bg-pink-50"></div> Reproductora</div>
        <div class="flex items-center gap-1.5 ml-auto"><span>🖱️ Arrastra para mover · Rueda para zoom · Clic en nodo para ver perfil</span></div>
    </div>
</div>

<?php
$dogSlug    = $dog['slug'];
$extraScripts = <<<JS
<script>
const DOG_SLUG = '{$dogSlug}';
</script>
<script src="/js/tree.js"></script>
<script>
document.getElementById('btn-instagram-tree').addEventListener('click', function () {
    const url = window.location.href;
    const text = document.title;
    if (navigator.share) {
        navigator.share({ title: text, url: url });
    } else {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Enlace copiado. Pégalo en Instagram.');
    }
});
document.getElementById('btn-copy-tree').addEventListener('click', function () {
    const btn = this;
    const url = window.location.href;
    const orig = btn.innerHTML;
    function showCopied() {
        btn.innerHTML = '<span style="color:#16a34a">✓ Copiado</span>';
        setTimeout(function(){ btn.innerHTML = orig; }, 2000);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(showCopied);
    } else {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showCopied();
    }
});
</script>
JS;
require APP_PATH . '/Views/layout/footer.php';
?>
