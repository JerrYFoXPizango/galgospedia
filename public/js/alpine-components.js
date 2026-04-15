/**
 * Galgospedia — Alpine.js Components
 * Loaded on pages that need reactivity (dog forms, search autocomplete).
 */

// ── Dog autocomplete search ───────────────────────────────────
function dogSearch() {
    return {
        fatherQuery:      '',
        motherQuery:      '',
        selectedFatherId: '',
        selectedMotherId: '',
        suggestions:      [],
        searchType:       'father',

        async search(query, type) {
            this.searchType = type;
            if (query.length < 2) { this.suggestions = []; return; }
            try {
                const res  = await fetch(`/api/galgos/buscar?q=${encodeURIComponent(query)}`);
                this.suggestions = await res.json();
            } catch {
                this.suggestions = [];
            }
        },

        selectDog(dog) {
            if (this.searchType === 'father') {
                this.fatherQuery      = dog.name;
                this.selectedFatherId = dog.id;
            } else {
                this.motherQuery      = dog.name;
                this.selectedMotherId = dog.id;
            }
            this.suggestions = [];
        },
    };
}

// ── Image upload preview ──────────────────────────────────────
function imageUpload() {
    return {
        preview:    null,
        isDragging: false,

        handleFile(event) {
            const file = event.target.files[0];
            if (file) this.readFile(file);
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                // Also update the real file input
                const input = this.$el.querySelector('input[type="file"]');
                if (input) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                }
                this.readFile(file);
            }
        },

        readFile(file) {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = (e) => { this.preview = e.target.result; };
            reader.readAsDataURL(file);
        },
    };
}
