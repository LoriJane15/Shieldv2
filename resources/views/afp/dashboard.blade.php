@extends('layouts.skydash-h')
@section('title', 'Dashboard')
@section('heading', 'Armed Forces of the Philippines')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .afp-welcome { background: #f8f9fc; }
    .stats-container { display: flex; gap: 20px; height: 600px; overflow-y: auto; }
    .stats-column { flex: 1; display: flex; flex-direction: column; gap: 20px; padding-right: 8px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 22px; display: flex; align-items: center; gap: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.08); flex: 1; min-height: 120px; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,.1); }
    .stat-card img { width: 60px; height: 60px; object-fit: contain; }
    .stat-card .municipality { font-size: 18px; color: #333; margin-bottom: 6px; font-weight: 500; }
    .stat-card .count { font-size: 30px; font-weight: bold; color: #000; line-height: 1.2; margin-bottom: 6px; }
    .stat-card .label { font-size: 14px; color: #666; line-height: 1.4; }
    .embed-container { position: relative; width: 100%; height: 600px; min-height: 420px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
    #afpMap { position: absolute; inset: 0; width: 100%; height: 100%; }
    .afp-map-legend { min-width: 150px; padding: 10px 12px; background: rgba(255,255,255,.96); border: 1px solid #dfe3e8; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.18); color: #333; line-height: 1.35; }
    .afp-map-legend-title { margin-bottom: 7px; font-size: 13px; font-weight: 700; }
    .afp-map-legend-item { display: flex; align-items: center; gap: 8px; margin-top: 5px; font-size: 12px; white-space: nowrap; }
    .afp-map-legend-marker { width: 12px; height: 12px; flex: 0 0 12px; border: 2px solid currentColor; border-radius: 50%; background: currentColor; box-shadow: inset 0 0 0 1px rgba(255,255,255,.45); }
    @media (max-width: 768px) { .stats-container, .embed-container { height: 420px; } }
</style>
@endpush

@section('content')
<div class="card border-0 afp-welcome">
    <div class="card-body">
        <h2 class="mb-3">
            <span class="text-secondary">Welcome</span>
            <span class="text-warning">Armed Forces of the Philippines</span>
        </h2>
        <p class="text-muted font-italic mb-4">
            Transforming lives through a comprehensive three-month program that empowers former rebels, nurtures growth,
            and restores hope, preparing them for successful reintegration into mainstream society.
        </p>

        <div class="row">
            {{-- Municipality seal cards --}}
            <div class="col-md-5">
                <div class="stats-container">
                    @foreach ($municipalities->chunk(ceil(max($municipalities->count(), 1) / 2)) as $col)
                        <div class="stats-column">
                            @foreach ($col as $m)
                                <div class="stat-card">
                                    @if ($m['seal'])
                                        <img src="{{ asset('assets/LGUS/'.$m['seal']) }}" alt="{{ $m['name'] }}">
                                    @else
                                        <div style="width:60px;height:60px;border-radius:50%;background:#e2e8f0;"></div>
                                    @endif
                                    <div class="stat-content">
                                        <div class="municipality">{{ $m['name'] }}</div>
                                        <div class="count">{{ $m['recognized'] }}</div>
                                        <div class="label">Total Recognized RCSP Barangay</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Operational map --}}
            <div class="col-md-7">
                <div class="embed-container">
                    <div id="afpMap"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const map = L.map('afpMap', { zoomControl: true }).setView([6.7497, 125.3572], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    const resizeMap = () => map.invalidateSize({ pan: false });
    requestAnimationFrame(resizeMap);
    window.addEventListener('load', resizeMap, { once: true });
    window.addEventListener('resize', resizeMap);

    if ('ResizeObserver' in window) {
        const mapResizeObserver = new ResizeObserver(resizeMap);
        mapResizeObserver.observe(document.querySelector('.embed-container'));
    }
    const points = @json($frPoints);
    const indicators = [
        { status: 'Active', color: '#22c55e' },
        { status: 'Reintegrated', color: '#2c4199' },
        { status: 'Inactive', color: '#94a3b8' },
        { status: 'Under Review', color: '#f59e0b' },
        { status: 'Other', color: '#64748b' },
    ];
    const colors = Object.fromEntries(indicators.map(indicator => [indicator.status, indicator.color]));
    const fallbackColor = colors.Other;
    const bounds = [];
    points.forEach(p => {
        const markerColor = colors[p.status] || fallbackColor;
        L.circleMarker([p.lat, p.lng], { radius:7, color: markerColor, fillColor: markerColor, fillOpacity:.85, weight:2 })
            .bindPopup('<strong>'+p.name+'</strong><br>'+p.status+'<br>'+(p.address||''))
            .addTo(map);
        bounds.push([p.lat, p.lng]);
    });

    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = () => {
        const container = L.DomUtil.create('div', 'afp-map-legend');
        container.setAttribute('role', 'group');
        container.setAttribute('aria-label', 'Former rebel status legend');

        const title = L.DomUtil.create('div', 'afp-map-legend-title', container);
        title.textContent = 'Former Rebel Status';

        indicators.forEach(indicator => {
            const item = L.DomUtil.create('div', 'afp-map-legend-item', container);
            const marker = L.DomUtil.create('span', 'afp-map-legend-marker', item);
            marker.style.color = indicator.color;

            const label = L.DomUtil.create('span', '', item);
            label.textContent = indicator.status;
        });

        L.DomEvent.disableClickPropagation(container);

        return container;
    };
    legend.addTo(map);

    if (bounds.length) map.fitBounds(bounds, { padding:[40,40] });
    requestAnimationFrame(resizeMap);
})();
</script>
@endpush
