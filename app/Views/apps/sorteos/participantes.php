<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="h-full bg-gray-50 py-10" x-data="sorteoManager(<?= htmlspecialchars($_GET['participantes'] ?? 16) ?>)">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Header con Breadcrumb -->
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <a href="/apps/sorteos/nuevo" class="text-sm text-galgo-red hover:underline mb-2 inline-block font-medium">← Volver a configuración</a>
                <h1 class="text-3xl font-display font-bold text-galgo-dark mb-1">Añadir Participantes</h1>
                <p class="text-gray-500">
                    Torneo: <strong class="text-galgo-gold"><?= htmlspecialchars($_GET['nombre'] ?? 'Sin Nombre') ?></strong> 
                    <span class="mx-2 text-gray-300">|</span> 
                    Cuadrante para <strong x-text="maxParticipants"></strong> galgos
                </p>
            </div>
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="text-right">
                    <span class="block text-3xl font-bold text-galgo-dark leading-none"><span x-text="participants.length"></span><span class="text-gray-300 text-lg">/<span x-text="maxParticipants"></span></span></span>
                    <span class="text-xs text-gray-500 uppercase font-bold tracking-wide">Inscritos</span>
                </div>
                <div class="w-12 h-12 rounded-full border-4 flex items-center justify-center transition-colors duration-500" :class="isFull ? 'border-green-100 text-green-500 bg-green-50' : 'border-gray-100 text-gray-400'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-show="!isFull" d="M12 4v16m8-8H4"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-show="isFull" x-cloak d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Zona Izquierda: Buscador -->
            <div class="lg:col-span-2 flex flex-col">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex-1">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-50 text-galgo-gold rounded-full flex items-center justify-center text-xl">🔍</div>
                            <div>
                                <h2 class="text-xl font-bold text-galgo-dark">Buscador Oficial</h2>
                                <p class="text-sm text-gray-500">Busca a los galgos registrados en Galgospedia.</p>
                            </div>
                        </div>
                        <div x-show="isSearching" class="text-galgo-gold">
                            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>
                    
                    <div class="relative mb-8">
                        <input type="text" x-model="query" @input.debounce.500ms="search()" :disabled="isFull" class="w-full pl-12 pr-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:bg-white focus:border-galgo-gold focus:ring-4 focus:ring-yellow-100 outline-none transition text-lg disabled:opacity-50 disabled:cursor-not-allowed" placeholder="Nombre completo, micropel o tatuaje...">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Estado Inicial / Vacío -->
                    <div class="border-t border-gray-100 pt-8 text-center py-12" x-show="query.length === 0 && results.length === 0">
                        <div class="inline-flex w-16 h-16 bg-gray-50 rounded-full items-center justify-center text-gray-300 mb-4 border border-gray-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-500 mb-1" x-text="isFull ? '¡Modo bloqueado!' : 'Empieza a escribir'"></h3>
                        <p class="text-gray-400 text-sm" x-text="isFull ? 'El torneo ya tiene el cupo cubierto.' : 'Los resultados aparecerán aquí y podrás añadirlos como participantes.'"></p>
                    </div>

                    <!-- Resultados -->
                    <div class="border-t border-gray-100 pt-6" x-show="results.length > 0" x-cloak>
                        <h3 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider">Resultados Encontrados</h3>
                        <div class="space-y-3">
                            <template x-for="galgo in results" :key="galgo.id">
                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-2xl border border-gray-100 hover:border-galgo-gold/30 hover:bg-white transition shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-xl flex items-center justify-center bg-gray-200 overflow-hidden shrink-0 border border-gray-200">
                                            <img :src="galgo.photo_url || '/logo/galgospedia-logo450-128.png'" class="w-full h-full object-contain" :class="!galgo.photo_url ? 'p-2 opacity-50' : ''" alt="Foto">
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-galgo-dark text-lg" x-text="galgo.name"></h4>
                                            <p class="text-xs text-gray-500 font-medium" x-text="galgo.gender === 'male' ? 'Macho' : (galgo.gender === 'female' ? 'Hembra' : 'Desconocido')"></p>
                                        </div>
                                    </div>
                                    <button @click="addParticipant(galgo)" :disabled="isFull || isAdded(galgo.id)" class="p-3 rounded-xl transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" :class="isAdded(galgo.id) ? 'bg-green-50 text-green-500' : 'bg-galgo-gold/10 text-galgo-gold hover:bg-galgo-gold hover:text-white hover:shadow-lg hover:-translate-y-0.5'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isAdded(galgo.id)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isAdded(galgo.id)" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Sin resultados -->
                    <div class="border-t border-gray-100 pt-8 text-center py-6" x-show="query.length > 0 && results.length === 0 && !isSearching" x-cloak>
                        <p class="text-gray-400">No se encontraron galgos que coincidan.</p>
                    </div>

                </div>
            </div>

            <!-- Zona Derecha: Panel de Inscritos -->
            <div class="lg:col-span-1">
                <div class="bg-galgo-dark text-white rounded-3xl p-6 h-full min-h-[500px] flex flex-col relative overflow-hidden shadow-2xl">
                    <!-- Decoración fondo -->
                    <div class="absolute -right-12 -top-12 text-galgo-gold opacity-10 text-[12rem] pointer-events-none">🏆</div>
                    
                    <h3 class="text-xl font-bold mb-6 z-10 flex items-center gap-2">
                        <span>Listado Oficial</span>
                        <span class="ml-auto text-xs py-1 px-2 rounded-lg" :class="isFull ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-orange-500/20 text-orange-300 border border-orange-500/30'" x-text="isFull ? 'COMPLETO' : 'PENDIENTE'"></span>
                    </h3>
                    
                    <!-- Estado vacío -->
                    <div class="flex-1 border-2 border-dashed border-gray-700/50 rounded-2xl flex flex-col items-center justify-center p-8 text-center bg-white/5 z-10" x-show="!hasParticipants">
                        <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center text-white/50 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">Vacío por el momento.</p>
                        <p class="text-gray-500 text-xs mt-1">Busca e inserta galgos desde el panel principal.</p>
                    </div>

                    <!-- Lista de Inscritos -->
                    <div class="flex-1 overflow-y-auto space-y-2 relative z-10 pr-2 custom-scrollbar" x-show="hasParticipants" x-cloak>
                        <template x-for="(p, index) in participants" :key="p.id">
                            <div class="flex items-center justify-between bg-white/5 hover:bg-white/10 p-3 rounded-xl border border-gray-700/50 transition duration-300">
                                <div class="flex items-center gap-3 w-full pr-3 overflow-hidden">
                                    <div class="w-7 h-7 rounded-full bg-galgo-gold/20 flex items-center justify-center text-xs font-bold text-galgo-gold shrink-0 border border-galgo-gold/30" x-text="index + 1"></div>
                                    <div class="truncate">
                                        <p class="text-sm font-bold text-white truncate" x-text="p.name"></p>
                                    </div>
                                </div>
                                <button @click="removeParticipant(index)" class="text-gray-500 hover:text-red-400 transition" title="Eliminar del torneo">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button" class="w-full mt-6 py-4 rounded-xl font-bold transition flex items-center justify-center gap-2 border z-10 shadow-lg"
                            :class="canGenerate ? 'bg-galgo-gold text-white border-yellow-500 hover:bg-yellow-600 hover:-translate-y-0.5' : 'bg-gray-800 text-gray-500 border-gray-700 cursor-not-allowed'">
                        <span x-text="canGenerate ? 'Sorteo Manual' : 'Generar Cuadrante Pista'"></span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!canGenerate"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="canGenerate" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    <p class="text-center text-xs mt-3 z-10 transition-colors" :class="isFull ? 'text-green-400 font-medium' : 'text-gray-500'">
                        <span x-show="!isFull">Faltan <span x-text="maxParticipants - participants.length"></span> galgos para activar</span>
                        <span x-show="isFull" x-cloak>Cupo completado correctamente.</span>
                    </p>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
/* Custom scrollbar for the dark panel */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sorteoManager', (maxItems) => ({
        query: '',
        results: [],
        participants: [],
        isSearching: false,
        maxParticipants: parseInt(maxItems) || 16,
        
        get hasParticipants() { return this.participants.length > 0; },
        get isFull() { return this.participants.length >= this.maxParticipants; },
        get canGenerate() { return this.participants.length === this.maxParticipants; },

        isAdded(id) {
            return this.participants.some(p => p.id === id);
        },

        async search() {
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }
            this.isSearching = true;
            try {
                let res = await fetch('/api/galgos/buscar?q=' + encodeURIComponent(this.query));
                if (res.ok) {
                    this.results = await res.json();
                }
            } catch (e) {
                console.error("Error buscando:", e);
            } finally {
                this.isSearching = false;
            }
        },

        addParticipant(galgo) {
            if (this.isFull) return;
            if (this.isAdded(galgo.id)) return;
            
            this.participants.push(galgo);
        },

        removeParticipant(index) {
            this.participants.splice(index, 1);
            // Optionally, un-disable the input if they want to search again
            if (this.query && !this.isFull) {
                this.search();
            }
        }
    }));
});
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
