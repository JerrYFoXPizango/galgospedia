/**
 * tournament-map.js
 * Dos modos:
 *   - Detalle (#tournament-map):  mapa de solo lectura con marcador
 *   - Formulario (#map-picker):   mapa clickable + búsqueda Nominatim
 */

(function () {
    var detailEl = document.getElementById('tournament-map');
    var pickerEl = document.getElementById('map-picker');

    if (detailEl) initDetailMap(detailEl);
    if (pickerEl) initPickerMap(pickerEl);
}());

/* ── Modo detalle (show.php) ─────────────────────────────── */
function initDetailMap(el) {
    var lat  = parseFloat(el.dataset.lat);
    var lng  = parseFloat(el.dataset.lng);
    var name = el.dataset.name || 'Ubicación';

    var map = L.map(el, { zoomControl: true, scrollWheelZoom: false })
               .setView([lat, lng], 14);

    window.addEventListener('load', function () { map.invalidateSize(); }, { once: true });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var icon = L.divIcon({
        className: '',
        html: '<div style="width:20px;height:20px;background:#c0392b;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.35)"></div>',
        iconAnchor: [10, 10]
    });

    L.marker([lat, lng], { icon: icon })
     .addTo(map)
     .bindPopup('<strong>' + name + '</strong>')
     .openPopup();
}

/* ── Modo picker (form.php) ──────────────────────────────── */
function initPickerMap(el) {
    var latInput     = document.getElementById('location_lat');
    var lngInput     = document.getElementById('location_lng');
    var coordsDisp   = document.getElementById('coords-display');
    var clearBtn     = document.getElementById('clear-coords-btn');
    var geocodeBtn   = document.getElementById('geocode-btn');
    var addressInput = document.getElementById('location_address');
    var resultsBox   = document.getElementById('geocode-results');

    var hasCoords   = latInput.value !== '' && lngInput.value !== '';
    var initLat     = hasCoords ? parseFloat(latInput.value) : 40.4168;
    var initLng     = hasCoords ? parseFloat(lngInput.value) : -3.7038;
    var initZoom    = hasCoords ? 14 : 6;

    var map = L.map(el, { scrollWheelZoom: false })
               .setView([initLat, initLng], initZoom);

    window.addEventListener('load', function () { map.invalidateSize(); }, { once: true });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = null;

    var pinIcon = L.divIcon({
        className: '',
        html: '<div style="width:20px;height:20px;background:#c0392b;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.35);cursor:grab"></div>',
        iconAnchor: [10, 10]
    });

    if (hasCoords) {
        marker = L.marker([initLat, initLng], { icon: pinIcon, draggable: true }).addTo(map);
        setupDraggable(marker);
        showClearBtn();
    }

    /* Click en el mapa */
    map.on('click', function (e) {
        setPin(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    /* ── Helpers ── */
    function setPin(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { icon: pinIcon, draggable: true }).addTo(map);
            setupDraggable(marker);
        }
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        updateCoordsDisplay(lat, lng);
        showClearBtn();
    }

    function setupDraggable(m) {
        m.on('dragend', function () {
            var pos = m.getLatLng();
            latInput.value = pos.lat.toFixed(7);
            lngInput.value = pos.lng.toFixed(7);
            updateCoordsDisplay(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        });
    }

    function updateCoordsDisplay(lat, lng) {
        if (!coordsDisp) return;
        var ns = lat >= 0 ? 'N' : 'S';
        var ew = lng >= 0 ? 'E' : 'O';
        coordsDisp.textContent = Math.abs(lat).toFixed(5) + '° ' + ns + ', ' +
                                  Math.abs(lng).toFixed(5) + '° ' + ew;
        coordsDisp.classList.remove('hidden');
    }

    function showClearBtn() {
        if (clearBtn) clearBtn.classList.remove('hidden');
    }

    /* Reverse geocode → rellena dirección si está vacía */
    function reverseGeocode(lat, lng) {
        if (!addressInput) return;
        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
            headers: { 'Accept-Language': 'es' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.display_name && !addressInput.value.trim()) {
                addressInput.value = data.display_name;
            }
        })
        .catch(function () {});
    }

    /* Geocodificación directa (botón Buscar / Enter) */
    var searchDebounce = null;

    function geocodeAddress(q) {
        if (q.length < 3) return;
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) +
              '&format=json&limit=5&countrycodes=es', {
            headers: { 'Accept-Language': 'es' }
        })
        .then(function (r) { return r.json(); })
        .then(function (results) {
            if (!results.length) return;
            if (results.length === 1) {
                selectResult(results[0]);
            } else {
                showResults(results);
            }
        })
        .catch(function () {});
    }

    function showResults(results) {
        if (!resultsBox) return;
        resultsBox.innerHTML = '';
        results.forEach(function (r) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'block w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b border-gray-100 last:border-0';
            btn.textContent = r.display_name;
            btn.addEventListener('click', function () {
                selectResult(r);
                resultsBox.innerHTML = '';
                resultsBox.classList.add('hidden');
            });
            resultsBox.appendChild(btn);
        });
        resultsBox.classList.remove('hidden');
    }

    function selectResult(r) {
        var lat = parseFloat(r.lat);
        var lng = parseFloat(r.lon);
        setPin(lat, lng);
        map.setView([lat, lng], 14);
        if (addressInput) addressInput.value = r.display_name;
        if (resultsBox) {
            resultsBox.innerHTML = '';
            resultsBox.classList.add('hidden');
        }
    }

    if (geocodeBtn) {
        geocodeBtn.addEventListener('click', function () {
            if (addressInput) geocodeAddress(addressInput.value.trim());
        });
    }

    if (addressInput) {
        addressInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                geocodeAddress(this.value.trim());
            }
        });
    }

    /* Limpiar pin */
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (marker) { map.removeLayer(marker); marker = null; }
            latInput.value = '';
            lngInput.value = '';
            if (coordsDisp) { coordsDisp.textContent = ''; coordsDisp.classList.add('hidden'); }
            clearBtn.classList.add('hidden');
        });
    }

    /* Cerrar resultados al hacer clic fuera */
    document.addEventListener('click', function (e) {
        if (resultsBox && !resultsBox.contains(e.target) && e.target !== addressInput && e.target !== geocodeBtn) {
            resultsBox.classList.add('hidden');
        }
    });
}
