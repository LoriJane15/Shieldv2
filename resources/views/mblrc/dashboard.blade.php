@extends('layouts.skydash-v')
@section('title', 'Operations Dashboard')
@section('heading', 'Overview')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<style>
    .operations-dashboard{--ops-purple:#401595;--ops-orange:#ff5a0a;--ops-navy:#263b5a;--ops-muted:#7b8aa2;--ops-border:#e5e9f0;max-width:1680px;margin:0 auto}.ops-header{align-items:flex-end;display:flex;gap:1rem;justify-content:space-between;margin-bottom:.85rem}.ops-eyebrow{color:var(--ops-orange);font-size:.62rem;font-weight:800;letter-spacing:.11em;margin-bottom:.18rem;text-transform:uppercase}.ops-header h2{color:var(--ops-navy);font-size:1.45rem;font-weight:750;letter-spacing:-.02em;margin:0 0 .18rem}.ops-header p{color:var(--ops-muted);font-size:.75rem;margin:0}.workspace-status{align-items:center;background:#fff;border:1px solid var(--ops-border);border-radius:8px;color:#64748b;display:flex;font-size:.66rem;font-weight:650;gap:.35rem;padding:.5rem .65rem;white-space:nowrap}.workspace-status i{color:#16845e;font-size:.85rem}.quick-actions{align-items:center;background:#fff;border:1px solid var(--ops-border);border-radius:11px;display:flex;gap:.8rem;margin-bottom:.85rem;padding:.65rem .75rem}.quick-actions-title{align-items:center;color:#475569;display:flex;flex:0 0 auto;font-size:.7rem;font-weight:750;gap:.35rem;margin:0;padding:0 .25rem}.quick-actions-title i{color:var(--ops-purple);font-size:.9rem}.quick-actions-list{display:flex;flex:1;flex-wrap:wrap;gap:.42rem}.quick-action{align-items:center;background:#f8f7fb;border:1px solid #ebe6f2;border-radius:7px;color:#53416b;display:inline-flex;font-size:.67rem;font-weight:700;gap:.32rem;min-height:34px;padding:.38rem .58rem;text-decoration:none!important}.quick-action.primary{background:var(--ops-purple);border-color:var(--ops-purple);color:#fff}.quick-action i{font-size:.82rem}.kpi-grid{display:grid;gap:.7rem;grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:.85rem}.kpi-card{background:#fff;border:1px solid var(--ops-border);border-radius:11px;min-width:0;padding:.72rem .78rem}.kpi-top{align-items:flex-start;display:flex;gap:.55rem;justify-content:space-between}.kpi-icon{align-items:center;border-radius:8px;display:flex;flex:0 0 34px;font-size:.95rem;height:34px;justify-content:center;width:34px}.kpi-value{color:var(--ops-navy);font-size:1.55rem;font-weight:780;letter-spacing:-.035em;line-height:1}.kpi-label{color:#53647d;display:block;font-size:.66rem;font-weight:750;margin-top:.18rem}.kpi-detail{color:#8492a6;font-size:.6rem;line-height:1.3;margin:.5rem 0 .4rem;min-height:1.55em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.kpi-progress{background:#edf0f4;border-radius:999px;height:4px;overflow:hidden}.kpi-progress span{display:block;height:100%;max-width:100%}.kpi-info .kpi-icon{background:#e9f2ff;color:#2f6fed}.kpi-info .kpi-progress span{background:#2f6fed}.kpi-primary .kpi-icon{background:#f1ebfa;color:var(--ops-purple)}.kpi-primary .kpi-progress span{background:var(--ops-purple)}.kpi-success .kpi-icon{background:#e6f6ef;color:#16845e}.kpi-success .kpi-progress span{background:#20a779}.kpi-warning .kpi-icon{background:#fff3df;color:#ad6c00}.kpi-warning .kpi-progress span{background:#ee9b16}.kpi-danger .kpi-icon{background:#fff0f1;color:#c43d4d}.kpi-danger .kpi-progress span{background:#d64a59}.analytics-grid,.operations-grid{display:grid;gap:.85rem;grid-template-columns:minmax(0,1.15fr) minmax(340px,.85fr);margin-bottom:.85rem}.ops-card{background:#fff;border:1px solid var(--ops-border);border-radius:11px;min-width:0;overflow:hidden}.ops-card-header{align-items:center;border-bottom:1px solid #edf0f4;display:flex;gap:.8rem;justify-content:space-between;padding:.72rem .85rem}.ops-card-title{align-items:center;display:flex;gap:.58rem;min-width:0}.ops-card-icon{align-items:center;background:#f4f0f9;border-radius:7px;color:var(--ops-purple);display:flex;flex:0 0 32px;font-size:.88rem;height:32px;justify-content:center;width:32px}.ops-card-title h3{color:#344563;font-size:.78rem;font-weight:750;margin:0 0 .08rem}.ops-card-title p{color:#8a97aa;font-size:.59rem;margin:0}.chart-period{background:#f8f9fb;border:1px solid #e7eaf0;border-radius:6px;color:#718096;font-size:.58rem;font-weight:650;padding:.32rem .45rem;white-space:nowrap}.chart-wrap{height:250px;padding:.7rem .75rem .55rem;position:relative}.attention-list,.activity-list{display:grid}.attention-item,.activity-item{align-items:center;border-bottom:1px solid #edf0f4;color:inherit;display:flex;gap:.65rem;min-height:62px;padding:.65rem .8rem;text-decoration:none!important}.attention-item:last-child,.activity-item:last-child{border-bottom:0}.attention-signal,.activity-icon{align-items:center;border-radius:8px;display:flex;flex:0 0 34px;font-size:.9rem;height:34px;justify-content:center;width:34px}.attention-copy,.activity-copy{min-width:0}.attention-copy strong,.activity-copy strong{color:#40516a;display:block;font-size:.68rem;font-weight:750;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.attention-copy small,.activity-copy small{color:#8a97aa;display:block;font-size:.58rem;line-height:1.35;margin-top:.12rem}.attention-count{color:#40516a;font-size:.82rem;font-weight:800;margin-left:auto}.tone-danger{background:#fff0f1;color:#c43d4d}.tone-warning{background:#fff3df;color:#a96b00}.tone-caution{background:#fff9db;color:#8a7300}.tone-info{background:#eaf2ff;color:#2f6fed}.tone-primary{background:#f1ebfa;color:var(--ops-purple)}.tone-success{background:#e6f6ef;color:#16845e}.activity-item{min-height:57px}.activity-time{color:#98a3b3;flex:0 0 auto;font-size:.56rem;margin-left:auto;text-align:right}.empty-activity{color:#8a97aa;font-size:.66rem;padding:2.5rem 1rem;text-align:center}.empty-activity i{display:block;font-size:1.5rem;margin-bottom:.3rem}.map-card{margin-bottom:.25rem}.map-toolbar{align-items:center;display:flex;flex-wrap:wrap;gap:.45rem;padding:.62rem .75rem}.map-filter-group,.map-layer-group{align-items:center;background:#f6f7f9;border-radius:7px;display:flex;gap:.16rem;padding:.18rem}.map-filter,.map-layer{background:transparent;border:0;border-radius:5px;color:#6d7b8f;font-size:.58rem;font-weight:700;min-height:28px;padding:.3rem .5rem}.map-filter[aria-pressed="true"],.map-layer[aria-pressed="true"]{background:#fff;box-shadow:0 1px 3px rgba(40,27,59,.1);color:var(--ops-purple)}.map-search{flex:1;min-width:190px;position:relative}.map-search i{color:#9aa5b5;font-size:.78rem;left:.62rem;position:absolute;top:50%;transform:translateY(-50%)}.map-search input{background:#f8f9fb;border:1px solid #e3e7ed;border-radius:7px;color:#475569;font-size:.63rem;height:34px;padding:.38rem .6rem .38rem 1.75rem;width:100%}.map-result-count{color:#8492a6;font-size:.58rem;margin-left:auto;white-space:nowrap}.fr-map{height:340px;width:100%}.leaflet-container{font-family:inherit}.map-popup{min-width:205px;padding:.15rem}.map-popup-id{color:#344563;font-size:.74rem;font-weight:800;margin-bottom:.45rem}.map-popup-grid{display:grid;gap:.28rem}.map-popup-row{display:flex;font-size:.61rem;gap:.5rem;justify-content:space-between}.map-popup-row span{color:#8b98aa}.map-popup-row strong{color:#475569;font-weight:700;text-align:right}.map-popup-link{align-items:center;background:var(--ops-purple);border-radius:6px;color:#fff!important;display:flex;font-size:.62rem;font-weight:750;justify-content:center;margin-top:.55rem;padding:.4rem;text-decoration:none!important}.leaflet-popup-content{margin:11px 13px}.leaflet-popup-content-wrapper{border-radius:9px;box-shadow:0 8px 24px rgba(35,25,52,.16)}
    .analytics-section-heading{align-items:flex-end;display:flex;gap:1rem;justify-content:space-between;margin:.15rem 0 .65rem}.analytics-section-heading .analytics-eyebrow{color:var(--ops-purple);display:block;font-size:.6rem;font-weight:800;letter-spacing:.1em;margin-bottom:.18rem;text-transform:uppercase}.analytics-section-heading h3{color:#2d3e57;font-size:.94rem;font-weight:780;letter-spacing:-.015em;margin:0}.analytics-section-heading p{color:#8290a4;font-size:.62rem;margin:.18rem 0 0}.analytics-freshness{align-items:center;color:#64748b;display:flex;font-size:.59rem;font-weight:700;gap:.35rem;white-space:nowrap}.analytics-freshness i{color:#20a779;font-size:.72rem}.analytics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.analytics-card{background:linear-gradient(180deg,#fff 0%,#fdfcff 100%);border-color:#e3e6ed;box-shadow:0 8px 24px rgba(35,25,52,.045)}.analytics-card .ops-card-header{background:linear-gradient(90deg,#fbf9fe,#fff);padding:.85rem .95rem}.analytics-card .ops-card-icon{background:#eee7f7;border:1px solid #e2d6ef;color:#401595}.analytics-card-content{padding:.8rem .9rem .7rem}.analytics-metrics{display:grid;gap:.45rem;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:.55rem}.analytics-metrics.metrics-four{grid-template-columns:repeat(4,minmax(0,1fr))}.analytics-metric{align-items:center;background:#fafbfc;border:1px solid #eaedf2;border-radius:8px;display:flex;gap:.5rem;min-width:0;padding:.48rem .55rem}.analytics-metric-dot{background:currentColor;border-radius:50%;box-shadow:0 0 0 3px color-mix(in srgb,currentColor 14%,transparent);flex:0 0 7px;height:7px;width:7px}.analytics-metric-copy{min-width:0}.analytics-metric-label{color:#8995a6;display:block;font-size:.54rem;font-weight:700;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.analytics-metric-value{color:#33445d;display:block;font-size:.8rem;font-variant-numeric:tabular-nums;font-weight:820;line-height:1.15;margin-top:.1rem}.metric-neutral{color:#8b95a5}.metric-warning{color:#e58b08}.metric-success{color:#16976d}.metric-primary{color:#401595}.metric-info{color:#2f6fed}.metric-accent{color:#ff5a0a}.analytics-card .chart-wrap{height:225px;padding:.45rem .25rem .15rem}.analytics-chart-state{align-items:center;color:#8290a4;display:flex;font-size:.56rem;gap:.3rem;margin-top:.35rem;min-height:14px}.analytics-chart-state i{color:#401595;font-size:.68rem}.analytics-loading .analytics-metric-value{animation:analytics-shimmer 1.15s linear infinite!important;background:linear-gradient(90deg,#edf0f4 25%,#f8f9fb 45%,#edf0f4 65%);background-size:220% 100%;border-radius:4px;color:transparent!important;max-width:48px;min-height:.8rem}.analytics-loading .chart-wrap::after{animation:analytics-shimmer 1.15s linear infinite!important;background:linear-gradient(90deg,rgba(239,241,245,.35),rgba(250,250,252,.82),rgba(239,241,245,.35));background-size:220% 100%;border-radius:8px;content:"";inset:.45rem .25rem .15rem;position:absolute;z-index:2}.analytics-loaded .analytics-metric{animation:analytics-metric-in 520ms cubic-bezier(.22,1,.36,1) both!important}.analytics-loaded .analytics-metric:nth-child(2){animation-delay:70ms!important}.analytics-loaded .analytics-metric:nth-child(3){animation-delay:140ms!important}.analytics-loaded .analytics-metric:nth-child(4){animation-delay:210ms!important}.analytics-loaded .chart-wrap canvas{animation:analytics-canvas-in 620ms ease-out both!important}.analytics-loaded .analytics-chart-state{animation:analytics-content-fade 450ms ease-out both!important;animation-delay:220ms!important}@keyframes analytics-shimmer{to{background-position:-220% 0}}@keyframes analytics-metric-in{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}@keyframes analytics-canvas-in{from{filter:blur(2px);opacity:0}to{filter:blur(0);opacity:1}}@keyframes analytics-content-fade{from{opacity:0}to{opacity:1}}
    @media(max-width:1199px){.kpi-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.analytics-grid,.operations-grid{grid-template-columns:1fr}.analytics-card .chart-wrap{height:240px}}
    @media(max-width:767px){.ops-header{align-items:flex-start;flex-direction:column}.workspace-status{display:none}.quick-actions{align-items:flex-start;flex-direction:column}.quick-actions-list{width:100%}.quick-action{flex:1;justify-content:center;min-width:calc(50% - .25rem)}.kpi-grid{grid-template-columns:1fr 1fr}.kpi-card:last-child{grid-column:1/-1}.analytics-section-heading{align-items:flex-start;flex-direction:column}.analytics-metrics.metrics-four{grid-template-columns:1fr 1fr}.analytics-card .chart-wrap{height:230px}.map-toolbar{align-items:stretch}.map-search{flex-basis:100%}.map-filter-group{overflow-x:auto}.map-layer-group{margin-left:0}.map-result-count{align-self:center}.fr-map{height:300px}}
    @media(max-width:420px){.kpi-grid{grid-template-columns:1fr}.kpi-card:last-child{grid-column:auto}.quick-action{min-width:100%}.analytics-metrics,.analytics-metrics.metrics-four{grid-template-columns:1fr 1fr}.analytics-card-content{padding:.7rem}.analytics-card .chart-wrap{height:210px}}
    @media(prefers-reduced-motion:reduce){.analytics-loading .analytics-metric-value,.analytics-loading .chart-wrap::after,.analytics-loaded .analytics-metric,.analytics-loaded .chart-wrap canvas,.analytics-loaded .analytics-chart-state{animation:none!important;filter:none!important;opacity:1!important;transform:none!important}}

    .map-expand-button{align-items:center;background:#fff;border:1px solid #dfe4eb;border-radius:7px;color:#526177;display:inline-flex;flex:0 0 auto;font-size:.62rem;font-weight:750;gap:.32rem;min-height:34px;padding:.38rem .62rem}.map-expand-button:hover,.map-expand-button:focus{background:#f4f0f9;border-color:#cbbade;color:var(--ops-purple);outline:0}.map-expand-button:focus-visible{box-shadow:0 0 0 3px rgba(64,21,149,.14)}.map-expand-button i{font-size:.85rem}.map-expand-modal .modal-dialog{margin:1rem auto;max-width:1280px;width:calc(100% - 2rem)}.map-expand-modal .modal-content{border:0;border-radius:16px;box-shadow:0 28px 80px rgba(22,27,43,.34);max-height:calc(100vh - 2rem);max-height:calc(100dvh - 2rem);overflow:hidden}.map-expand-modal .modal-header{align-items:center;background:linear-gradient(110deg,#280274,#401595);border:0;color:#fff;min-height:76px;padding:1rem 1.2rem}.map-expand-modal-heading{align-items:center;display:flex;gap:.7rem;min-width:0}.map-expand-modal-icon{align-items:center;background:rgba(255,255,255,.12);border-radius:9px;display:flex;flex:0 0 38px;font-size:1.05rem;height:38px;justify-content:center;width:38px}.map-expand-modal .modal-title{color:#fff;font-size:.95rem;font-weight:780;margin:0}.map-expand-modal-subtitle{color:rgba(255,255,255,.78);font-size:.64rem;margin:.12rem 0 0}.map-expand-close{align-items:center;background:rgba(255,255,255,.1);border:0;border-radius:50%;color:#fff;display:flex;flex:0 0 38px;font-size:1.15rem;height:38px;justify-content:center;margin-left:1rem;padding:0;width:38px}.map-expand-close:hover,.map-expand-close:focus{background:rgba(255,255,255,.2);color:#fff;outline:0}.map-expand-modal .modal-body{background:#fff;min-height:0;overflow:hidden;padding:0}.map-modal-host .map-toolbar{border-bottom:1px solid #edf0f4}.map-modal-host .fr-map{height:calc(100vh - 190px);height:calc(100dvh - 190px);max-height:720px;min-height:440px}.map-expand-modal .leaflet-container{isolation:isolate}@media(max-width:767px){.map-expand-button span{display:none}.map-expand-button{justify-content:center;padding:.35rem;width:34px}.map-expand-modal .modal-dialog{margin:.5rem auto;width:calc(100% - 1rem)}.map-expand-modal .modal-content{border-radius:12px;max-height:calc(100vh - 1rem);max-height:calc(100dvh - 1rem)}.map-expand-modal .modal-header{min-height:68px;padding:.85rem}.map-expand-modal-subtitle{display:none}.map-modal-host .map-toolbar{max-height:142px;overflow-y:auto}.map-modal-host .fr-map{height:calc(100vh - 220px);height:calc(100dvh - 220px);max-height:none;min-height:300px}}

</style>
@endpush

@section('content')
<div class="operations-dashboard">
    <header class="ops-header">
        <div class="ops-title-main">
            <span class="ops-title-icon"><i class="mdi mdi-account-group-outline" aria-hidden="true"></i></span>
            <div><div class="ops-eyebrow">Operations command center</div><h2>MBLRC Reintegration Overview</h2><p>Prioritize assigned monitoring work, review program performance, and inspect authorized location records.</p></div>
        </div>
        <div class="ops-title-badges"><span class="ops-role-badge">MBLRC</span></div>
    </header>

    <section class="kpi-grid" aria-label="Operational performance indicators">
        @foreach($kpis as $kpi)
            <article class="kpi-card kpi-{{ $kpi['tone'] }}">
                <div class="kpi-top"><span class="kpi-icon"><i class="mdi {{ $kpi['icon'] }}" aria-hidden="true"></i></span><span class="kpi-detail">{{ $kpi['detail'] }}</span></div>
                <strong class="kpi-value">{{ number_format($kpi['value']) }}</strong>
                <span class="kpi-label">{{ $kpi['label'] }}</span>
            </article>
        @endforeach
    </section>

    <header class="analytics-section-heading">
        <div><span class="analytics-eyebrow">Performance analytics</span><h3>Reintegration trends at a glance</h3><p>Seven-month program movement and cumulative registry outcomes.</p></div>
        <span class="analytics-freshness"><i class="mdi mdi-circle-medium" aria-hidden="true"></i>Updated from current authorized records</span>
    </header>
    <div class="analytics-grid" aria-label="MBLRC performance analytics">
        <section class="ops-card analytics-card" aria-labelledby="program-chart-title">
            <header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-chart-bar-stacked" aria-hidden="true"></i></span><div><h3 id="program-chart-title">Monthly Program Movement</h3><p>Status updates recorded during each reporting month.</p></div></div><span class="chart-period">7 months</span></header>
            <div class="analytics-card-content">
                <div class="analytics-metrics" aria-label="Latest month program updates">
                    <div class="analytics-metric metric-neutral"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Not Started</span><strong class="analytics-metric-value" data-program-not-started>—</strong></span></div>
                    <div class="analytics-metric metric-warning"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Ongoing</span><strong class="analytics-metric-value" data-program-ongoing>—</strong></span></div>
                    <div class="analytics-metric metric-success"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Completed</span><strong class="analytics-metric-value" data-program-completed>—</strong></span></div>
                </div>
                <div class="chart-wrap"><canvas id="programChart" role="img" aria-label="Stacked bar chart of monthly program status updates"></canvas></div>
                <div class="analytics-chart-state" data-program-state><i class="mdi mdi-chart-timeline-variant" aria-hidden="true"></i><span>Loading monthly program movement…</span></div>
            </div>
        </section>
        <section class="ops-card analytics-card" aria-labelledby="overall-chart-title">
            <header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-chart-line-variant" aria-hidden="true"></i></span><div><h3 id="overall-chart-title">Registry Outcome Trend</h3><p>Cumulative profile status and reintegration performance.</p></div></div><span class="chart-period">Cumulative</span></header>
            <div class="analytics-card-content">
                <div class="analytics-metrics metrics-four" aria-label="Current registry outcome totals">
                    <div class="analytics-metric metric-primary"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Registered</span><strong class="analytics-metric-value" data-overall-registered>—</strong></span></div>
                    <div class="analytics-metric metric-info"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Active</span><strong class="analytics-metric-value" data-overall-active>—</strong></span></div>
                    <div class="analytics-metric metric-success"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Reintegrated</span><strong class="analytics-metric-value" data-overall-reintegrated>—</strong></span></div>
                    <div class="analytics-metric metric-accent"><span class="analytics-metric-dot" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Completion</span><strong class="analytics-metric-value" data-overall-rate>—</strong></span></div>
                </div>
                <div class="chart-wrap"><canvas id="overallChart" role="img" aria-label="Line chart of cumulative registry and reintegration outcomes"></canvas></div>
                <div class="analytics-chart-state" data-overall-state><i class="mdi mdi-trending-up" aria-hidden="true"></i><span>Loading cumulative registry outcomes…</span></div>
            </div>
        </section>
    </div>

    <div class="operations-grid">
        <section class="ops-card" aria-labelledby="attention-title"><header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-alert-decagram-outline" aria-hidden="true"></i></span><div><h3 id="attention-title">Needs Attention</h3><p>Real records requiring review or missing operational information.</p></div></div></header><div class="attention-list">@foreach($attentionItems as $item)<a class="attention-item" href="{{ $item['url'] }}"><span class="attention-signal tone-{{ $item['tone'] }}"><i class="mdi {{ $item['icon'] }}" aria-hidden="true"></i></span><span class="attention-copy"><strong>{{ $item['title'] }}</strong><small>{{ $item['detail'] }}</small></span><span class="attention-count">{{ number_format($item['count']) }}</span></a>@endforeach</div></section>
        <section class="ops-card" aria-labelledby="activity-title"><header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-history" aria-hidden="true"></i></span><div><h3 id="activity-title">Recent Activity</h3><p>Latest authorized registry updates.</p></div></div></header><div class="activity-list">@forelse($recentActivity as $activity)<a class="activity-item" href="{{ $activity['url'] }}"><span class="activity-icon tone-{{ $activity['tone'] }}"><i class="mdi {{ $activity['icon'] }}" aria-hidden="true"></i></span><span class="activity-copy"><strong>{{ $activity['title'] }}</strong><small>{{ $activity['detail'] }}</small></span><time class="activity-time" datetime="{{ $activity['occurred_at']?->toIso8601String() }}">{{ $activity['occurred_at']?->diffForHumans() }}</time></a>@empty<div class="empty-activity"><i class="mdi mdi-clock-outline" aria-hidden="true"></i>No recent operational activity.</div>@endforelse</div></section>
    </div>

    <section class="ops-card map-card" id="locations" aria-labelledby="locations-title">
        <header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-map-marker-radius" aria-hidden="true"></i></span><div><h3 id="locations-title">Geographic Monitoring Map</h3><p>Authorized geotagged FR/FVE records with program and location context.</p></div></div><button type="button" class="map-expand-button" data-bs-toggle="modal" data-bs-target="#mapExpandModal" data-map-expand aria-haspopup="dialog" aria-controls="mapExpandModal"><i class="mdi mdi-fullscreen" aria-hidden="true"></i><span>Expand Map</span></button></header>
        <div data-map-source>
            <div data-map-workspace>
                <div class="map-toolbar" aria-label="Map controls">
                    <div class="map-filter-group" role="group" aria-label="Filter map records by status"><button class="map-filter" type="button" data-map-filter="all" aria-pressed="true">All</button><button class="map-filter" type="button" data-map-filter="active" aria-pressed="false">Active</button><button class="map-filter" type="button" data-map-filter="ongoing" aria-pressed="false">Ongoing</button><button class="map-filter" type="button" data-map-filter="completed" aria-pressed="false">Completed</button></div>
                    <label class="map-search" for="map-location-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><span class="sr-only">Search mapped locations</span><input id="map-location-search" type="search" placeholder="Search identifier, municipality, or location" autocomplete="off" data-map-search></label>
                    <div class="map-layer-group" role="group" aria-label="Select map layer"><button class="map-layer" type="button" data-map-layer="map" aria-pressed="true">Map</button><button class="map-layer" type="button" data-map-layer="satellite" aria-pressed="false">Satellite</button></div><span class="map-result-count" data-map-result-count aria-live="polite">Loading locations…</span>
                </div>
                <div id="frMap" class="fr-map" data-locations="{{ route('mblrc.fr.locations') }}"></div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade map-expand-modal" id="mapExpandModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="map-expand-title" aria-describedby="map-expand-description" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <header class="modal-header">
                <div class="map-expand-modal-heading"><span class="map-expand-modal-icon"><i class="mdi mdi-map-marker-radius" aria-hidden="true"></i></span><div><h3 class="modal-title" id="map-expand-title">Geographic Monitoring Map</h3><p class="map-expand-modal-subtitle" id="map-expand-description">Expanded authorized location monitoring workspace.</p></div></div>
                <button type="button" class="map-expand-close" data-bs-dismiss="modal" aria-label="Close expanded map"><i class="mdi mdi-close" aria-hidden="true"></i></button>
            </header>
            <div class="modal-body"><div class="map-modal-host" data-map-modal-host></div></div>
        </div>
    </div>
</div>
<div id="mblrcData" data-analytics="{{ route('mblrc.analytics') }}" hidden></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
(function () {
    var dataNode = document.getElementById('mblrcData');
    var analyticsGrid = document.querySelector('.analytics-grid');
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var numberFormatter = new Intl.NumberFormat('en-US');
    var chartAnimation = prefersReducedMotion ? false : {
        duration: 950,
        easing: 'easeOutQuart',
        delay: function (context) {
            return context.type === 'data' && context.mode === 'default'
                ? (context.dataIndex * 55) + (context.datasetIndex * 90)
                : 0;
        }
    };
    var tooltip = { backgroundColor: '#263b5a', cornerRadius: 7, padding: 10, titleFont: { size: 10 }, bodyFont: { size: 10 }, displayColors: true, boxPadding: 4 };
    if (analyticsGrid) analyticsGrid.classList.add('analytics-loading');
    function revealAnalytics() {
        if (!analyticsGrid) return;
        analyticsGrid.classList.remove('analytics-loading');
        analyticsGrid.classList.add('analytics-loaded');
    }
    function latest(values) {
        return Number(values[values.length - 1] || 0);
    }
    function animateMetric(selector, value, suffix) {
        var element = document.querySelector(selector);
        if (!element) return;
        var finalValue = Number(value) || 0;
        if (prefersReducedMotion) {
            element.textContent = numberFormatter.format(finalValue) + (suffix || '');
            return;
        }
        var startedAt = null;
        function frame(timestamp) {
            if (startedAt === null) startedAt = timestamp;
            var progress = Math.min((timestamp - startedAt) / 760, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = numberFormatter.format(Math.round(finalValue * eased)) + (suffix || '');
            if (progress < 1) window.requestAnimationFrame(frame);
        }
        window.requestAnimationFrame(frame);
    }
    fetch(dataNode.dataset.analytics, { headers: { Accept: 'application/json' } }).then(function (response) { if (!response.ok) throw new Error(); return response.json(); }).then(function (data) {
        var programLatest = {
            notStarted: latest(data.program.not_started),
            ongoing: latest(data.program.ongoing),
            completed: latest(data.program.completed)
        };
        var overallLatest = {
            registered: latest(data.overall.registered),
            active: latest(data.overall.active),
            reintegrated: latest(data.overall.reintegrated),
            rate: latest(data.overall.completion_rate)
        };
        new Chart(document.getElementById('programChart'), { type: 'bar', data: { labels: data.labels, datasets: [
            { label: 'Not Started', data: data.program.not_started, backgroundColor: '#aeb7c4', borderRadius: 5, borderSkipped: false, maxBarThickness: 34 },
            { label: 'Ongoing', data: data.program.ongoing, backgroundColor: '#ee9b16', borderRadius: 5, borderSkipped: false, maxBarThickness: 34 },
            { label: 'Completed', data: data.program.completed, backgroundColor: '#20a779', borderRadius: 5, borderSkipped: false, maxBarThickness: 34 }
        ] }, options: { animation: chartAnimation, maintainAspectRatio: false, responsive: true, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: false }, tooltip: tooltip }, scales: { x: { stacked: true, border: { display: false }, grid: { display: false }, ticks: { color: '#7f8b9d', font: { size: 9, weight: 600 } } }, y: { stacked: true, beginAtZero: true, border: { display: false }, grace: 1, ticks: { color: '#8b96a7', precision: 0, font: { size: 9 } }, grid: { color: '#eef1f5' } } } } });
        var overallCanvas = document.getElementById('overallChart');
        var overallGradient = overallCanvas.getContext('2d').createLinearGradient(0, 0, 0, 220);
        overallGradient.addColorStop(0, 'rgba(64, 21, 149, .22)');
        overallGradient.addColorStop(1, 'rgba(64, 21, 149, .01)');
        new Chart(overallCanvas, { type: 'line', data: { labels: data.labels, datasets: [
            { label: 'Registered', data: data.overall.registered, borderColor: '#401595', backgroundColor: overallGradient, borderWidth: 2.2, fill: true, pointBackgroundColor: '#fff', pointBorderColor: '#401595', pointBorderWidth: 2, pointRadius: 2.5, pointHoverRadius: 4, tension: .35 },
            { label: 'Active', data: data.overall.active, borderColor: '#2f6fed', backgroundColor: '#2f6fed', borderWidth: 1.8, fill: false, pointRadius: 2, pointHoverRadius: 4, tension: .35 },
            { label: 'Reintegrated', data: data.overall.reintegrated, borderColor: '#20a779', backgroundColor: '#20a779', borderWidth: 1.8, fill: false, pointRadius: 2, pointHoverRadius: 4, tension: .35 },
            { label: 'Completion %', data: data.overall.completion_rate, borderColor: '#ff5a0a', backgroundColor: '#ff5a0a', borderDash: [5, 4], borderWidth: 1.7, fill: false, pointRadius: 2, pointHoverRadius: 4, tension: .35, yAxisID: 'rate' }
        ] }, options: { animation: chartAnimation, maintainAspectRatio: false, responsive: true, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: false }, tooltip: tooltip }, scales: { x: { border: { display: false }, grid: { display: false }, ticks: { color: '#7f8b9d', font: { size: 9, weight: 600 } } }, y: { beginAtZero: true, border: { display: false }, ticks: { color: '#8b96a7', precision: 0, font: { size: 9 } }, grid: { color: '#eef1f5' } }, rate: { beginAtZero: true, max: 100, position: 'right', border: { display: false }, grid: { display: false }, ticks: { color: '#ff7a3c', callback: function (value) { return value + '%'; }, font: { size: 9 } } } } } });
        revealAnalytics();
        animateMetric('[data-program-not-started]', programLatest.notStarted);
        animateMetric('[data-program-ongoing]', programLatest.ongoing);
        animateMetric('[data-program-completed]', programLatest.completed);
        animateMetric('[data-overall-registered]', overallLatest.registered);
        animateMetric('[data-overall-active]', overallLatest.active);
        animateMetric('[data-overall-reintegrated]', overallLatest.reintegrated);
        animateMetric('[data-overall-rate]', overallLatest.rate, '%');
        document.querySelector('[data-program-state] span').textContent = data.labels[data.labels.length - 1] + ': ' + numberFormatter.format(programLatest.notStarted + programLatest.ongoing + programLatest.completed) + ' status updates recorded.';
        document.querySelector('[data-overall-state] span').textContent = 'Completion compares reintegrated profiles with the current registered total.';
    }).catch(function () {
        revealAnalytics();
        document.querySelectorAll('.chart-wrap').forEach(function (wrap) { wrap.setAttribute('aria-label', 'Analytics are temporarily unavailable.'); });
        document.querySelectorAll('.analytics-chart-state span').forEach(function (state) { state.textContent = 'Analytics are temporarily unavailable.'; });
    });

    var mapNode = document.getElementById('frMap');
    var map = L.map(mapNode, { zoomAnimation: false, fadeAnimation: false, markerZoomAnimation: false }).setView([6.7497, 125.3572], 10);
    var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' });
    var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: 'Tiles &copy; Esri' });
    streetLayer.addTo(map);
    var cluster = L.markerClusterGroup({ animate: false }); map.addLayer(cluster);
    var markerItems = []; var currentFilter = 'all';
    var resultCount = document.querySelector('[data-map-result-count]'); var searchInput = document.querySelector('[data-map-search]');
    function popupFor(row) {
        var popup = document.createElement('div'); popup.className = 'map-popup';
        var heading = document.createElement('div'); heading.className = 'map-popup-id'; heading.textContent = row.classified_id; popup.appendChild(heading);
        var grid = document.createElement('div'); grid.className = 'map-popup-grid';
        [['Status', row.status], ['Municipality', row.municipality], ['Program', row.program_status], ['Last Updated', row.last_updated || 'Not available']].forEach(function (field) {
            var line = document.createElement('div'); line.className = 'map-popup-row'; var label = document.createElement('span'); var value = document.createElement('strong'); label.textContent = field[0]; value.textContent = field[1]; line.append(label, value); grid.appendChild(line);
        });
        popup.appendChild(grid); var link = document.createElement('a'); link.className = 'map-popup-link'; link.href = row.url; link.textContent = 'View Profile'; popup.appendChild(link); return popup;
    }
    function matchesStatus(row) { if (currentFilter === 'active') return row.status === 'Active'; if (currentFilter === 'ongoing') return row.program_status === 'On-going'; if (currentFilter === 'completed') return ['Completed', 'Reintegrated'].includes(row.status) || row.program_status === 'Completed'; return true; }
    function renderMarkers(fitMap) {
        var query = searchInput.value.trim().toLowerCase(); var visible = markerItems.filter(function (item) { var searchable = [item.row.classified_id, item.row.municipality, item.row.address].filter(Boolean).join(' ').toLowerCase(); return matchesStatus(item.row) && (!query || searchable.includes(query)); });
        cluster.clearLayers(); visible.forEach(function (item) { cluster.addLayer(item.marker); }); resultCount.textContent = visible.length + (visible.length === 1 ? ' location' : ' locations');
        if (fitMap && visible.length) map.fitBounds(L.latLngBounds(visible.map(function (item) { return [item.row.lat, item.row.lng]; })), { padding: [24, 24], maxZoom: 14 });
    }
    fetch(mapNode.dataset.locations, { headers: { Accept: 'application/json' } }).then(function (response) { if (!response.ok) throw new Error(); return response.json(); }).then(function (rows) { markerItems = rows.map(function (row) { return { row: row, marker: L.marker([row.lat, row.lng]).bindPopup(popupFor(row)) }; }); renderMarkers(true); }).catch(function () { resultCount.textContent = 'Locations unavailable'; });
    document.querySelectorAll('[data-map-filter]').forEach(function (button) { button.addEventListener('click', function () { currentFilter = button.dataset.mapFilter; document.querySelectorAll('[data-map-filter]').forEach(function (item) { item.setAttribute('aria-pressed', item === button ? 'true' : 'false'); }); renderMarkers(true); }); });
    searchInput.addEventListener('input', function () { renderMarkers(true); });
    document.querySelectorAll('[data-map-layer]').forEach(function (button) { button.addEventListener('click', function () { var satellite = button.dataset.mapLayer === 'satellite'; if (satellite) { map.removeLayer(streetLayer); satelliteLayer.addTo(map); } else { map.removeLayer(satelliteLayer); streetLayer.addTo(map); } document.querySelectorAll('[data-map-layer]').forEach(function (item) { item.setAttribute('aria-pressed', item === button ? 'true' : 'false'); }); }); });
    var mapModal = document.getElementById('mapExpandModal'); var mapWorkspace = document.querySelector('[data-map-workspace]'); var mapSource = document.querySelector('[data-map-source]'); var mapModalHost = document.querySelector('[data-map-modal-host]'); var mapExpandButton = document.querySelector('[data-map-expand]');
    function resizeMap() { window.requestAnimationFrame(function () { map.invalidateSize({ pan: false }); }); }
    mapModal.addEventListener('show.bs.modal', function () { mapModalHost.appendChild(mapWorkspace); });
    mapModal.addEventListener('shown.bs.modal', resizeMap);
    mapModal.addEventListener('hidden.bs.modal', function () { mapSource.appendChild(mapWorkspace); resizeMap(); if (mapExpandButton) mapExpandButton.focus(); });
})();
</script>
@endpush
