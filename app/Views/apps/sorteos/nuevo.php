<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="h-full bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-3xl">
        
        <div class="mb-8">
            <a href="/apps/sorteos" class="text-sm text-galgo-red hover:underline mb-2 inline-block font-medium">← Cancelar y volver</a>
            <h1 class="text-3xl font-display font-bold text-galgo-dark">Crear Nuevo Sorteo</h1>
            <p class="text-gray-500 mt-1">Configura los datos del campeonato antes de elegir a los participantes.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
            <form action="/apps/sorteos/participantes" method="GET">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Torneo / Evento <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-galgo-gold focus:ring-2 focus:ring-yellow-200 outline-none transition" placeholder="Ej: Copa Rey de la Pista 2026" required>
                    </div>

<!-- Keeping the rest the same up to the button -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Lugar / Localidad</label>
                            <input type="text" name="lugar" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-galgo-gold focus:ring-2 focus:ring-yellow-200 outline-none transition" placeholder="Ej: Toledo">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nivel</label>
                            <select name="modalidad" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-galgo-gold focus:ring-2 focus:ring-yellow-200 outline-none transition bg-white">
                                <option>Torneo Local</option>
                                <option>Torneo General</option>
                                <option>Campeonato Regional</option>
                                <option>Campeonato Nacional</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estructura del Cuadrante <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3" x-data="{ selected: '16' }">
                            
                            <label class="relative flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition"
                                   :class="selected === '4' ? 'border-galgo-gold bg-orange-50/50' : 'border-gray-200'">
                                <input type="radio" name="participantes" value="4" x-model="selected" class="hidden">
                                <div class="text-center">
                                    <span class="block text-lg font-bold" :class="selected === '4' ? 'text-galgo-gold' : 'text-galgo-dark'">4 Galgos</span>
                                    <span class="text-xs font-medium" :class="selected === '4' ? 'text-orange-600' : 'text-gray-400'">Torneo Express</span>
                                </div>
                            </label>

                            <label class="relative flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition"
                                   :class="selected === '8' ? 'border-galgo-gold bg-orange-50/50' : 'border-gray-200'">
                                <input type="radio" name="participantes" value="8" x-model="selected" class="hidden">
                                <div class="text-center">
                                    <span class="block text-lg font-bold" :class="selected === '8' ? 'text-galgo-gold' : 'text-galgo-dark'">8 Galgos</span>
                                    <span class="text-xs font-medium" :class="selected === '8' ? 'text-orange-600' : 'text-gray-400'">Torneo Rápido</span>
                                </div>
                            </label>

                            <label class="relative flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition"
                                   :class="selected === '16' ? 'border-galgo-gold bg-orange-50/50' : 'border-gray-200'">
                                <input type="radio" name="participantes" value="16" x-model="selected" class="hidden">
                                <div class="text-center">
                                    <span class="block text-lg font-bold" :class="selected === '16' ? 'text-galgo-gold' : 'text-galgo-dark'">16 Galgos</span>
                                    <span class="text-xs font-medium" :class="selected === '16' ? 'text-orange-600' : 'text-gray-400'">Torneo Clásico</span>
                                </div>
                            </label>

                            <label class="relative flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition"
                                   :class="selected === '32' ? 'border-galgo-gold bg-orange-50/50' : 'border-gray-200'">
                                <input type="radio" name="participantes" value="32" x-model="selected" class="hidden">
                                <div class="text-center">
                                    <span class="block text-lg font-bold" :class="selected === '32' ? 'text-galgo-gold' : 'text-galgo-dark'">32 Galgos</span>
                                    <span class="text-xs font-medium" :class="selected === '32' ? 'text-orange-600' : 'text-gray-400'">Gran Evento</span>
                                </div>
                            </label>

                        </div>
                    </div>

                    <div class="pt-8 mt-8 border-t border-gray-100 flex justify-end gap-3">
                        <a href="/apps/sorteos" class="px-6 py-3 rounded-xl font-semibold text-gray-600 hover:bg-gray-100 transition">Cancelar</a>
                        <button type="submit" class="px-8 py-3 rounded-xl font-bold text-white bg-galgo-dark hover:bg-black shadow-lg shadow-gray-200 transition transform hover:-translate-y-0.5">
                            Paso Siguiente: Añadir Galgos →
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
