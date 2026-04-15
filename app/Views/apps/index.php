<?php require APP_PATH . '/Views/layout/header.php'; ?>

<main class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <div class="mb-10 text-center md:text-left">
            <h1 class="text-4xl font-display font-bold text-galgo-dark mb-2">Apps</h1>
            <p class="text-gray-500">Herramientas profesionales de gestión, sorteos y calculadoras.</p>
        </div>

        <!-- Pestañas (Pills) -->
        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-10">
            <button class="px-5 py-2 rounded-full bg-galgo-dark text-white text-sm font-medium transition focus:outline-none shadow-md">
                Todos
            </button>
            <button class="px-5 py-2 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-100 transition focus:outline-none">
                Competición
            </button>
            <button class="px-5 py-2 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-100 transition focus:outline-none">
                Salud
            </button>
            <button class="px-5 py-2 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-100 transition focus:outline-none">
                Morfología
            </button>
        </div>

        <!-- Grid de Apps -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- App Card: Sorteos y Colleras -->
            <a href="/apps/sorteos" class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-galgo-gold transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                <div class="h-3 bg-gradient-to-r from-galgo-gold to-yellow-400"></div>
                <div class="p-6">
                    <div class="w-14 h-14 bg-orange-50 rounded-xl flex items-center justify-center mb-5 text-3xl">
                        🏅
                    </div>
                    <h3 class="text-xl font-bold text-galgo-dark mb-2 group-hover:text-galgo-gold transition-colors">Sorteo y Colleras</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-5">
                        El modo más profesional de organizar competiciones. Genera el cuadrante oficial de enfrentamientos, anota resultados y guarda campeones.
                    </p>
                    <div class="flex items-center text-sm font-semibold text-galgo-gold">
                        Abrir herramienta 
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- App Card: Calculadora de Descanso -->
            <a href="/apps/descanso" class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-galgo-red transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                <div class="h-3 bg-gradient-to-r from-galgo-red to-red-400"></div>
                <div class="p-6">
                    <div class="w-14 h-14 bg-red-50 text-galgo-red rounded-xl flex items-center justify-center mb-5 text-3xl">
                        ⏱️
                    </div>
                    <h3 class="text-xl font-bold text-galgo-dark mb-2 group-hover:text-galgo-red transition-colors">Descanso de Carrera</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-5">
                        Basada en el reglamento FEG. Calcula el tiempo de recuperación obligatorio y la hora exacta de vuelta a meta según la duración de la pasada.
                    </p>
                    <div class="flex items-center text-sm font-semibold text-galgo-red">
                        Abrir calculadora
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- App Card: Historial Veterinario -->
            <a href="/apps/veterinario" class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-green-400 transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                <div class="h-3 bg-gradient-to-r from-green-500 to-emerald-400"></div>
                <div class="p-6">
                    <div class="w-14 h-14 bg-green-50 rounded-xl flex items-center justify-center mb-5 text-3xl">
                        🏥
                    </div>
                    <h3 class="text-xl font-bold text-galgo-dark mb-2 group-hover:text-green-600 transition-colors">Historial Veterinario</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-5">
                        Registra vacunas, desparasitaciones y lesiones de cada galgo. Semáforo de salud, alertas de próximas dosis y seguimiento de lesiones.
                    </p>
                    <div class="flex items-center text-sm font-semibold text-green-600">
                        Abrir historial
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- App Card: Diario de Entrenamiento -->
            <a href="/apps/entrenamiento" class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-blue-400 transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                <div class="h-3 bg-gradient-to-r from-blue-500 to-cyan-400"></div>
                <div class="p-6">
                    <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center mb-5 text-3xl">
                        🏃
                    </div>
                    <h3 class="text-xl font-bold text-galgo-dark mb-2 group-hover:text-blue-600 transition-colors">Diario de Entrenamiento</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-5">
                        Registra sesiones, distancias y terrenos. Semáforo de carga semanal, control de sobreentrenamiento y gráfico de rendimiento por galgo.
                    </p>
                    <div class="flex items-center text-sm font-semibold text-blue-600">
                        Abrir diario
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- App Card Placeholder: Gestación -->
            <div class="group block bg-white rounded-2xl shadow-sm border border-dashed border-gray-300 overflow-hidden opacity-60">
                <div class="p-6">
                    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-5 text-3xl grayscale">
                        🍼
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Gestor de Camadas</h3>
                    <p class="text-gray-400 text-sm leading-relaxed mb-5">
                        Calculadora exacta de preñez, fechas estimadas de ecografías y seguimiento evolutivo y de peso de todos los cachorros.
                    </p>
                    <div class="inline-flex px-3 py-1 bg-gray-200 text-gray-500 text-xs font-bold rounded-full">
                        PRÓXIMAMENTE
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
