<?php require APP_PATH . '/Views/layout/header.php'; ?>

<main class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Breadcrumb & Header -->
        <div class="mb-8">
            <a href="/apps" class="text-sm text-galgo-red hover:underline mb-2 inline-block font-medium">← Volver al panel de Apps</a>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl lg:text-4xl font-display font-bold text-galgo-dark mb-2">🏅 Sorteos y Colleras</h1>
                    <p class="text-gray-500">Organiza competiciones, empareja a los galgos al azar y corona a tus campeones.</p>
                </div>
                <div>
                    <a href="/apps/sorteos/nuevo" class="block w-full md:w-auto text-center px-6 py-3 bg-galgo-gold text-white font-bold rounded-xl shadow-lg shadow-yellow-500/30 hover:bg-yellow-600 transition-colors hover:-translate-y-0.5 transform">
                        + Nuevo Sorteo
                    </a>
                </div>
            </div>
        </div>

        <!-- Empty State Dashboard -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 md:p-16 text-center mt-8">
            <div class="w-24 h-24 bg-gradient-to-br from-orange-50 to-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl shadow-inner">
                🏆
            </div>
            <h2 class="text-2xl md:text-3xl font-display font-bold text-galgo-dark mb-4">Aún no hay sorteos activos</h2>
            <p class="text-gray-500 max-w-lg mx-auto mb-8 leading-relaxed">
                Empieza creando tu primer sorteo. El sistema generará el cuadrante automáticamente emparejando a los galgos inscritos para que gestiones las mangas desde la pista.
            </p>
            <a href="/apps/sorteos/nuevo" class="inline-flex items-center justify-center px-8 py-4 bg-galgo-dark text-white font-bold rounded-2xl hover:bg-black hover:shadow-xl transition-all duration-300">
                Lanzar el primer Sorteo
            </a>
        </div>

    </div>
</main>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
