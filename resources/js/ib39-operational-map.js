import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

(() => {
    const root = document.getElementById('ib39Map');

    if (!root) {
        return;
    }

    const state = document.querySelector('[data-map-state]');
    const stateText = document.querySelector('[data-map-state-text]');
    const search = document.querySelector('[data-map-search]');
    const status = document.querySelector('[data-map-status]');
    const visibleCount = document.querySelector('[data-visible-count]');
    const totalCount = document.querySelector('[data-total-count]');
    const legend = document.querySelector('[data-map-legend]');
    const fitButton = document.querySelector('[data-map-fit]');
    const resetButton = document.querySelector('[data-map-reset]');
    const generatedAt = document.querySelector('[data-generated-at]');
    const dataStatus = document.querySelector('[data-data-status]');

    const colors = {
        Active: '#16a34a',
        'On hold': '#a855f7',
        Reintegrated: '#2c4199',
        Inactive: '#64748b',
        'Under Review': '#f59e0b',
        Disengaged: '#7c3aed',
        Pending: '#0ea5e9',
        Suspended: '#e11d48',
        Completed: '#059669',
        Deceased: '#334155',
        Relocated: '#0891b2',
    };

    let map;
    let markerLayer;
    let records = [];

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const setState = (message, type = '') => {
        state.hidden = false;
        state.classList.toggle('is-error', type === 'error');
        state.classList.toggle('is-empty', type === 'empty');
        state.querySelector('.spinner-border')?.toggleAttribute('hidden', type !== '');
        stateText.textContent = message;
    };

    const hideState = () => {
        state.hidden = true;
    };

    const renderLegend = () => {
        legend.innerHTML = Object.entries(colors).map(([label, color]) => `
            <div class="map-legend-item">
                <span class="map-legend-dot" style="background:${color};color:${color}" aria-hidden="true"></span>
                <span>${escapeHtml(label)}</span>
            </div>
        `).join('');
    };

    const popupHtml = (record) => {
        const color = colors[record.status] || '#64748b';

        return `
            <div class="map-popup">
                <div class="map-popup-name">${escapeHtml(record.name || 'Unnamed record')}</div>
                <span class="map-popup-status" style="background:${color}">
                    ${escapeHtml(record.status || 'Unclassified')}
                </span>
                <div class="map-popup-meta">Batch: ${escapeHtml(record.batch || '—')}</div>
                <div class="map-popup-address">${escapeHtml(record.address || 'No placement address')}</div>
            </div>
        `;
    };

    const filteredRecords = () => {
        const term = search.value.trim().toLocaleLowerCase();
        const selectedStatus = status.value;

        return records.filter((record) => {
            const matchesStatus = !selectedStatus || record.status === selectedStatus;
            const searchable = [
                record.name,
                record.address,
                record.batch,
                record.status,
            ].join(' ').toLocaleLowerCase();

            return matchesStatus && (!term || searchable.includes(term));
        });
    };

    const fitMarkers = (markers) => {
        if (markers.length === 1) {
            map.setView(markers[0].getLatLng(), 14);
        } else if (markers.length > 1) {
            map.fitBounds(L.featureGroup(markers).getBounds(), { padding: [35, 35], maxZoom: 15 });
        }
    };

    const renderMarkers = (fit = false) => {
        markerLayer.clearLayers();
        const markers = [];

        filteredRecords().forEach((record) => {
            const color = colors[record.status] || '#64748b';
            const marker = L.circleMarker([record.lat, record.lng], {
                radius: 8,
                color: '#fff',
                fillColor: color,
                fillOpacity: 0.92,
                weight: 2,
            }).bindPopup(popupHtml(record), { maxWidth: 280 });

            marker.addTo(markerLayer);
            markers.push(marker);
        });

        visibleCount.textContent = String(markers.length);

        if (markers.length === 0) {
            setState(
                records.length === 0
                    ? 'No Former Rebel records currently have map coordinates.'
                    : 'No markers match the selected filters.',
                'empty'
            );
        } else {
            hideState();
        }

        if (fit && markers.length) {
            fitMarkers(markers);
        }

        return markers;
    };

    const initialize = async () => {
        renderLegend();

        map = L.map(root, {
            zoomControl: true,
            minZoom: 8,
        }).setView([6.7497, 125.3572], 10);

        const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        tiles.on('tileerror', () => {
            if (!records.length) {
                setState('The base map could not be loaded. Check the internet connection.', 'error');
            }
        });

        L.control.scale({ imperial: false }).addTo(map);
        markerLayer = L.layerGroup().addTo(map);

        const resizeMap = () => map.invalidateSize({ pan: false });

        if ('ResizeObserver' in window) {
            new ResizeObserver(resizeMap).observe(root);
        }
        window.addEventListener('resize', resizeMap);
        window.setTimeout(resizeMap, 150);

        try {
            const response = await fetch(root.dataset.source, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`Map data request failed with status ${response.status}`);
            }

            const payload = await response.json();
            if (dataStatus) {
                dataStatus.textContent = 'Authorized data loaded securely';
            }
            if (generatedAt && payload.meta?.generated_at) {
                const generatedDate = new Date(payload.meta.generated_at);
                generatedAt.dateTime = payload.meta.generated_at;
                generatedAt.textContent = Number.isNaN(generatedDate.getTime())
                    ? 'recently'
                    : generatedDate.toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
            }
            records = (payload.markers || []).filter((record) => (
                Number.isFinite(Number(record.lat))
                && Number.isFinite(Number(record.lng))
                && Number(record.lat) >= -90
                && Number(record.lat) <= 90
                && Number(record.lng) >= -180
                && Number(record.lng) <= 180
            )).map((record) => ({
                ...record,
                lat: Number(record.lat),
                lng: Number(record.lng),
            }));

            totalCount.textContent = String(records.length);
            renderMarkers(true);
        } catch (error) {
            if (dataStatus) {
                dataStatus.textContent = 'Authorized data unavailable';
            }
            setState('Authorized map data could not be loaded. Please reload or contact an administrator.', 'error');
        }
    };

    let searchTimer;
    search.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => renderMarkers(false), 180);
    });
    status.addEventListener('change', () => renderMarkers(true));
    fitButton.addEventListener('click', () => fitMarkers(renderMarkers(false)));
    resetButton.addEventListener('click', () => {
        search.value = '';
        status.value = '';
        renderMarkers(true);
        search.focus();
    });

    initialize();
})();
