@extends('layouts.skydash-v')
@section('title', 'Dashboard')
@section('heading', 'MBLRC — Overview')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    .mblrc-dashboard{--navy:#172b4d;--primary:#2f6fed;--border:#e7ecf3}.dashboard-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:18px;box-shadow:0 12px 30px rgba(47,111,237,.18);color:#fff;overflow:hidden;padding:1.7rem;position:relative}.dashboard-hero::before,.dashboard-hero::after{background:rgba(255,255,255,.07);border-radius:50%;content:'';position:absolute}.dashboard-hero::before{height:230px;right:-65px;top:-115px;width:230px}.dashboard-hero::after{bottom:-100px;height:170px;right:120px;width:170px}.hero-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.11em;opacity:.76;text-transform:uppercase}.dashboard-hero h2{color:#fff;font-size:1.6rem;font-weight:700}.dashboard-hero p{font-size:.8rem;line-height:1.5;opacity:.86}.hero-badge{align-items:center;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);border-radius:12px;display:flex;padding:.65rem .85rem;position:relative;z-index:1}.hero-badge i{font-size:1.25rem;margin-right:.65rem}.hero-badge small,.hero-badge strong{display:block}.hero-badge small{font-size:.6rem;opacity:.75;text-transform:uppercase}.hero-badge strong{font-size:.78rem}.stat-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045);overflow:hidden;position:relative}.stat-card::before{background:var(--stat-color);bottom:0;content:'';left:0;position:absolute;top:0;width:4px}.stat-card .card-body{align-items:center;display:flex;padding:1rem}.stat-icon{align-items:center;background:var(--stat-bg);border-radius:10px;color:var(--stat-color);display:flex;flex:0 0 40px;font-size:1.15rem;height:40px;justify-content:center;margin-right:.75rem;width:40px}.stat-card strong{color:var(--navy);display:block;font-size:1.2rem;line-height:1.1}.stat-card small{color:#718096;display:block;font-size:.65rem;font-weight:700;margin-top:.2rem;text-transform:uppercase}.stat-primary{--stat-bg:#eaf1ff;--stat-color:#2f6fed}.stat-green{--stat-bg:#e8f8f1;--stat-color:#20a779}.stat-purple{--stat-bg:#f2ebff;--stat-color:#805ad5}.stat-navy{--stat-bg:#e9edf4;--stat-color:#42526b}.stat-amber{--stat-bg:#fff5dc;--stat-color:#d89400}.stat-gray{--stat-bg:#f1f5f9;--stat-color:#718096}.dashboard-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045)}.dashboard-card .card-body{padding:1.35rem}.card-heading{align-items:center;display:flex;margin-bottom:1.15rem}.heading-icon{align-items:center;background:#eaf1ff;border-radius:10px;color:#2f6fed;display:flex;flex:0 0 40px;font-size:1.1rem;height:40px;justify-content:center;margin-right:1rem;width:40px}.card-heading h3{color:var(--navy);font-size:.95rem;font-weight:700;margin:0 0 .15rem}.card-heading p{color:#8492a6;font-size:.7rem;margin:0}.chart-wrap{height:280px;position:relative}.map-wrap{border-radius:11px;overflow:hidden;position:relative}.map-note{align-items:center;background:#f8fafc;border:1px solid #edf1f6;border-radius:9px;color:#718096;display:flex;font-size:.68rem;margin-bottom:.9rem;padding:.65rem .75rem}.map-note i{color:#2f6fed;margin-right:.45rem}.fr-map{height:420px;width:100%}.leaflet-container{font-family:inherit}
    @media(max-width:767px){.dashboard-hero{padding:1.2rem}.dashboard-hero h2{font-size:1.35rem}.hero-badge{margin-top:1rem}.dashboard-card .card-body{padding:1.1rem}.chart-wrap{height:240px}.fr-map{height:340px}}
</style>
@endpush

@section('content')
@php
    $statCards = [
        ['Registered FRs', $stats['registered'], 'mdi-account-multiple', 'stat-primary'],
        ['Active', $stats['active'], 'mdi-account-check', 'stat-green'],
        ['Reintegrated', $stats['reintegrated'], 'mdi-account-convert', 'stat-purple'],
        ['Completed', $stats['completed'], 'mdi-school', 'stat-navy'],
        ['On-going', $stats['ongoing'], 'mdi-progress-clock', 'stat-amber'],
        ['Not Started', $stats['not_started'], 'mdi-pause-circle', 'stat-gray'],
    ];
@endphp
<div class="mblrc-dashboard">
    <section class="dashboard-hero mb-4" aria-labelledby="dashboard-title"><div class="row align-items-center position-relative" style="z-index:1"><div class="col-md-8"><div class="hero-eyebrow mb-1">Monitoring overview</div><h2 id="dashboard-title" class="mb-1">MBLRC Reintegration Dashboard</h2><p class="mb-0">Monitor registered former rebels, reintegration progress, program trends, and authorized location records.</p></div><div class="col-md-4 d-flex justify-content-md-end"><div class="hero-badge"><i class="mdi mdi-shield-check"></i><div><small>Workspace</small><strong>Secure MBLRC Monitoring</strong></div></div></div></div></section>

    <div class="row">
        @foreach($statCards as [$label, $value, $icon, $theme])
            <div class="col-sm-6 col-lg-4 col-xl-2 mb-3"><article class="card stat-card {{ $theme }} h-100"><div class="card-body"><div class="stat-icon"><i class="mdi {{ $icon }}"></i></div><div><strong>{{ number_format($value) }}</strong><small>{{ $label }}</small></div></div></article></div>
        @endforeach
    </div>

    <div class="row mt-1">
        <div class="col-lg-6 mb-4"><section class="card dashboard-card h-100" aria-labelledby="program-chart-title"><div class="card-body"><div class="card-heading"><div class="heading-icon"><i class="mdi mdi-chart-line"></i></div><div><h3 id="program-chart-title">Program Status Analytics</h3><p>Reintegration progress during the last seven months.</p></div></div><div class="chart-wrap"><canvas id="programChart"></canvas></div></div></section></div>
        <div class="col-lg-6 mb-4"><section class="card dashboard-card h-100" aria-labelledby="overall-chart-title"><div class="card-body"><div class="card-heading"><div class="heading-icon"><i class="mdi mdi-chart-line"></i></div><div><h3 id="overall-chart-title">Overall Statistics</h3><p>Registered and reintegrated records during the last seven months.</p></div></div><div class="chart-wrap"><canvas id="overallChart"></canvas></div></div></section></div>
    </div>

    <section class="card dashboard-card mb-4" aria-labelledby="locations-title"><div class="card-body"><div class="card-heading"><div class="heading-icon"><i class="mdi mdi-map-marker-outline"></i></div><div><h3 id="locations-title">Former Rebel Locations</h3><p>Authorized geotagged records displayed by recorded coordinates.</p></div></div><div class="map-note"><i class="mdi mdi-information-outline"></i><span>Only records with saved coordinates appear on this map. Select a marker to open its authorized profile.</span></div><div class="map-wrap"><div id="frMap" class="fr-map" data-locations="{{ route('mblrc.fr.locations') }}"></div></div></div></section>
</div>
<div id="mblrcData" data-analytics="{{ route('mblrc.analytics') }}" hidden></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
(function(){
    const ds = (label,data,color)=>({label,data,borderColor:color,backgroundColor:color+'22',tension:.35,fill:true,pointRadius:3});
    fetch(document.getElementById('mblrcData').dataset.analytics).then(r=>r.json()).then(d=>{
        new Chart(document.getElementById('programChart'),{type:'line',data:{labels:d.labels,datasets:[
            ds('Not-Started',d.program.not_started,'#98a2b3'),ds('On-going',d.program.ongoing,'#f79009'),ds('Completed',d.program.completed,'#039855')]},
            options:{maintainAspectRatio:false,responsive:true,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
        new Chart(document.getElementById('overallChart'),{type:'line',data:{labels:d.labels,datasets:[
            ds('Registered',d.overall.registered,'#2c4199'),ds('Reintegrated',d.overall.reintegrated,'#12b76a')]},
            options:{maintainAspectRatio:false,responsive:true,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
    });
    const map=L.map('frMap').setView([6.7497,125.3572],10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);
    const cluster=L.markerClusterGroup(); map.addLayer(cluster);
    fetch(document.getElementById('frMap').dataset.locations).then(r=>r.json()).then(rows=>{
        const b=[]; rows.forEach(fr=>{ L.marker([fr.lat,fr.lng]).bindPopup('<strong>'+fr.name+'</strong><br>'+fr.status+'<br><a href="'+fr.url+'">View profile</a>').addTo(cluster); b.push([fr.lat,fr.lng]); });
        if(b.length) map.fitBounds(b,{padding:[30,30]});
    });
})();
</script>
@endpush
