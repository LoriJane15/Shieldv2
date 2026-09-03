@extends('layouts.skydash-v')
@section('title', '39th IB Dashboard')
@section('heading', '39th Infantry Battalion')

@push('styles')
<style>
    .operations-dashboard{--ops-purple:#401595;--ops-orange:#ff5a0a;--ops-navy:#263b5a;--ops-muted:#7b8aa2;--ops-border:#e5e9f0;max-width:1680px;margin:0 auto}.ops-header{align-items:flex-end;display:flex;gap:1rem;justify-content:space-between;margin-bottom:.85rem}.ops-eyebrow{color:var(--ops-orange);font-size:.62rem;font-weight:800;letter-spacing:.11em;margin-bottom:.18rem;text-transform:uppercase}.ops-header h2{color:var(--ops-navy);font-size:1.45rem;font-weight:750;letter-spacing:-.02em;margin:0 0 .18rem}.ops-header p{color:var(--ops-muted);font-size:.75rem;margin:0}.workspace-status{align-items:center;background:#fff;border:1px solid var(--ops-border);border-radius:8px;color:#64748b;display:flex;font-size:.66rem;font-weight:650;gap:.35rem;padding:.5rem .65rem;white-space:nowrap}.workspace-status i{color:#16845e;font-size:.85rem}.quick-actions{align-items:center;background:#fff;border:1px solid var(--ops-border);border-radius:11px;display:flex;gap:.8rem;margin-bottom:.85rem;padding:.65rem .75rem}.quick-actions-title{align-items:center;color:#475569;display:flex;flex:0 0 auto;font-size:.7rem;font-weight:750;gap:.35rem;margin:0;padding:0 .25rem}.quick-actions-title i{color:var(--ops-purple);font-size:.9rem}.quick-actions-list{display:flex;flex:1;flex-wrap:wrap;gap:.42rem}.quick-action{align-items:center;background:#f8f7fb;border:1px solid #ebe6f2;border-radius:7px;color:#53416b;display:inline-flex;font-size:.67rem;font-weight:700;gap:.32rem;min-height:34px;padding:.38rem .58rem;text-decoration:none!important}.quick-action.primary{background:var(--ops-purple);border-color:var(--ops-purple);color:#fff}.quick-action i{font-size:.82rem}.kpi-grid{display:grid;gap:.7rem;grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:.85rem}.kpi-card{background:#fff;border:1px solid var(--ops-border);border-radius:11px;min-width:0;padding:.72rem .78rem}.kpi-top{align-items:flex-start;display:flex;gap:.55rem;justify-content:space-between}.kpi-icon{align-items:center;border-radius:8px;display:flex;flex:0 0 34px;font-size:.95rem;height:34px;justify-content:center;width:34px}.kpi-value{color:var(--ops-navy);font-size:1.55rem;font-weight:780;letter-spacing:-.035em;line-height:1}.kpi-label{color:#53647d;display:block;font-size:.66rem;font-weight:750;margin-top:.18rem}.kpi-detail{color:#8492a6;font-size:.6rem;line-height:1.3;margin:.5rem 0 .4rem;min-height:1.55em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.kpi-progress{background:#edf0f4;border-radius:999px;height:4px;overflow:hidden}.kpi-progress span{display:block;height:100%;max-width:100%}.kpi-info .kpi-icon{background:#e9f2ff;color:#2f6fed}.kpi-info .kpi-progress span{background:#2f6fed}.kpi-primary .kpi-icon{background:#f1ebfa;color:var(--ops-purple)}.kpi-primary .kpi-progress span{background:var(--ops-purple)}.kpi-success .kpi-icon{background:#e6f6ef;color:#16845e}.kpi-success .kpi-progress span{background:#20a779}.kpi-warning .kpi-icon{background:#fff3df;color:#ad6c00}.kpi-warning .kpi-progress span{background:#ee9b16}.kpi-danger .kpi-icon{background:#fff0f1;color:#c43d4d}.kpi-danger .kpi-progress span{background:#d64a59}.analytics-grid,.operations-grid{display:grid;gap:.85rem;grid-template-columns:minmax(0,1.15fr) minmax(340px,.85fr);margin-bottom:.85rem}.ops-card{background:#fff;border:1px solid var(--ops-border);border-radius:11px;min-width:0;overflow:hidden}.ops-card-header{align-items:center;border-bottom:1px solid #edf0f4;display:flex;gap:.8rem;justify-content:space-between;padding:.72rem .85rem}.ops-card-title{align-items:center;display:flex;gap:.58rem;min-width:0}.ops-card-icon{align-items:center;background:#f4f0f9;border-radius:7px;color:var(--ops-purple);display:flex;flex:0 0 32px;font-size:.88rem;height:32px;justify-content:center;width:32px}.ops-card-title h3{color:#344563;font-size:.78rem;font-weight:750;margin:0 0 .08rem}.ops-card-title p{color:#8a97aa;font-size:.59rem;margin:0}.chart-period{background:#f8f9fb;border:1px solid #e7eaf0;border-radius:6px;color:#718096;font-size:.58rem;font-weight:650;padding:.32rem .45rem;white-space:nowrap}.chart-wrap{height:250px;padding:.7rem .75rem .55rem;position:relative}.attention-list,.activity-list{display:grid}.attention-item,.activity-item{align-items:center;border-bottom:1px solid #edf0f4;color:inherit;display:flex;gap:.65rem;min-height:62px;padding:.65rem .8rem;text-decoration:none!important}.attention-item:last-child,.activity-item:last-child{border-bottom:0}.attention-signal,.activity-icon{align-items:center;border-radius:8px;display:flex;flex:0 0 34px;font-size:.9rem;height:34px;justify-content:center;width:34px}.attention-copy,.activity-copy{min-width:0}.attention-copy strong,.activity-copy strong{color:#40516a;display:block;font-size:.68rem;font-weight:750;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.attention-copy small,.activity-copy small{color:#8a97aa;display:block;font-size:.58rem;line-height:1.35;margin-top:.12rem}.attention-count{color:#40516a;font-size:.82rem;font-weight:800;margin-left:auto}.tone-danger{background:#fff0f1;color:#c43d4d}.tone-warning{background:#fff3df;color:#a96b00}.tone-caution{background:#fff9db;color:#8a7300}.tone-info{background:#eaf2ff;color:#2f6fed}.tone-primary{background:#f1ebfa;color:var(--ops-purple)}.tone-success{background:#e6f6ef;color:#16845e}.activity-item{min-height:57px}.activity-time{color:#98a3b3;flex:0 0 auto;font-size:.56rem;margin-left:auto;text-align:right}.empty-activity{color:#8a97aa;font-size:.66rem;padding:2.5rem 1rem;text-align:center}.empty-activity i{display:block;font-size:1.5rem;margin-bottom:.3rem}
    .analytics-section-heading{align-items:flex-end;display:flex;gap:1rem;justify-content:space-between;margin:.15rem 0 .65rem}.analytics-section-heading .analytics-eyebrow{color:var(--ops-purple);display:block;font-size:.6rem;font-weight:800;letter-spacing:.1em;margin-bottom:.18rem;text-transform:uppercase}.analytics-section-heading h3{color:#2d3e57;font-size:.94rem;font-weight:780;letter-spacing:-.015em;margin:0}.analytics-section-heading p{color:#8290a4;font-size:.62rem;margin:.18rem 0 0}.analytics-freshness{align-items:center;color:#64748b;display:flex;font-size:.59rem;font-weight:700;gap:.35rem;white-space:nowrap}.analytics-freshness i{color:#20a779;font-size:.72rem}.analytics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.analytics-card{background:linear-gradient(180deg,#fff 0%,#fdfcff 100%);border-color:#e3e6ed;box-shadow:0 8px 24px rgba(35,25,52,.045)}.analytics-card .ops-card-header{background:linear-gradient(90deg,#fbf9fe,#fff);padding:.85rem .95rem}.analytics-card .ops-card-icon{background:#eee7f7;border:1px solid #e2d6ef;color:#401595}.analytics-card-content{padding:.8rem .9rem .7rem}.analytics-metrics{display:grid;gap:.45rem;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:.55rem}.analytics-metrics.metrics-four{grid-template-columns:repeat(4,minmax(0,1fr))}.analytics-metric{align-items:center;background:#fafbfc;border:1px solid #eaedf2;border-radius:8px;display:flex;gap:.5rem;min-width:0;padding:.48rem .55rem}.analytics-metric-dot{background:currentColor;border-radius:50%;box-shadow:0 0 0 3px color-mix(in srgb,currentColor 14%,transparent);flex:0 0 7px;height:7px;width:7px}.analytics-metric-copy{min-width:0}.analytics-metric-label{color:#8995a6;display:block;font-size:.54rem;font-weight:700;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.analytics-metric-value{color:#33445d;display:block;font-size:.8rem;font-variant-numeric:tabular-nums;font-weight:820;line-height:1.15;margin-top:.1rem}
    .classification-grid{display:grid;gap:.7rem;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:.85rem}
    .classification-box{background:#fff;border:1px solid var(--ops-border);border-radius:10px;overflow:hidden;padding:.85rem .95rem;position:relative}
    .classification-box::before{background:var(--box-tone);content:'';height:3px;left:0;position:absolute;right:0;top:0}
    .classification-box-top{align-items:center;display:flex;justify-content:space-between;margin-bottom:.4rem}
    .classification-box h4{color:#334155;font-size:.76rem;font-weight:750;margin:0}
    .classification-box-icon{align-items:center;background:var(--box-bg);border-radius:7px;color:var(--box-tone);display:flex;height:30px;justify-content:center;width:30px;font-size:.85rem}
    .classification-box .classification-box-count{color:var(--ops-navy);font-size:1.45rem;font-weight:800;letter-spacing:-.03em;line-height:1;margin-bottom:.2rem}
    .classification-box p{color:#8492a6;font-size:.58rem;font-weight:600;margin:0;line-height:1.3}
    .box-red{--box-tone:#dc4c58;--box-bg:#ffebed}
    .box-orange{--box-tone:#e78328;--box-bg:#fff0e2}
    .box-yellow{--box-tone:#d4aa26;--box-bg:#fff7d8}
    .box-green{--box-tone:#20a779;--box-bg:#e8f8f1}
    @media(max-width:1199px){.kpi-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.classification-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.analytics-grid,.operations-grid{grid-template-columns:1fr}}
    @media(max-width:767px){.ops-header{align-items:flex-start;flex-direction:column}.kpi-grid{grid-template-columns:1fr 1fr}.classification-grid{grid-template-columns:1fr 1fr}.analytics-metrics.metrics-four{grid-template-columns:1fr 1fr}}
    @media(max-width:420px){.kpi-grid{grid-template-columns:1fr}.classification-grid{grid-template-columns:1fr}.analytics-metrics,.analytics-metrics.metrics-four{grid-template-columns:1fr 1fr}}
</style>
@endpush

@section('content')
@php
    $classifications = [
        ['Konsolidado', $statusCounts['Konsolidado'] ?? 0, 'box-red', 'mdi-flag-variant-outline', 'Organized NPA Influenced Areas'],
        ['Rekonsilida', $statusCounts['Rekonsilida'] ?? 0, 'box-orange', 'mdi-alert-outline', 'Less Influenced Areas'],
        ['Expansion', $statusCounts['Expansion'] ?? 0, 'box-yellow', 'mdi-arrow-expand-all', 'Potential Threat Areas'],
        ['Recovery', $statusCounts['Recovery'] ?? 0, 'box-green', 'mdi-check-circle-outline', 'Cleared Areas'],
    ];
@endphp
<div class="operations-dashboard">
    <header class="ops-header">
        <div class="ops-title-main">
            <span class="ops-title-icon"><i class="mdi mdi-shield-account-outline" aria-hidden="true"></i></span>
            <div><div class="ops-eyebrow">Operations command center</div><h2>39th Infantry Battalion Dashboard</h2><p>Authorized RCSP area classification and former-rebel monitoring overview.</p></div>
        </div>
        <div class="ops-header-tools">
            <div class="ops-title-badges">
                <span class="ops-role-badge">39th IB</span>
            </div>
            <div class="quick-actions">
                <div class="quick-actions-list">
                    <a href="{{ route('ib39.areas.index') }}" class="quick-action"><i class="mdi mdi-map-marker-radius"></i> Manage Areas</a>
                    <a href="{{ route('ib39.map') }}" class="quick-action primary"><i class="mdi mdi-map"></i> Operational Map</a>
                    <a href="{{ route('ib39.fr-profiles.create') }}" class="quick-action"><i class="mdi mdi-account-plus"></i> Record Surfaced FR</a>
                </div>
            </div>
        </div>
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
        <div><span class="analytics-eyebrow">Area Classification</span><h3>Current Barangay Distribution</h3><p>Area classification breakdown based on recorded former rebel counts.</p></div>
        <span class="analytics-freshness"><i class="mdi mdi-circle-medium" aria-hidden="true"></i>Updated from current authorized records</span>
    </header>
    <section class="classification-grid" aria-label="Area classifications">
        @foreach($classifications as [$title, $count, $tone, $icon, $description])
            <article class="classification-box {{ $tone }}">
                <div class="classification-box-top">
                    <h4>{{ $title }}</h4>
                    <span class="classification-box-icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></span>
                </div>
                <div class="classification-box-count">{{ number_format($count) }}</div>
                <p>{{ $description }}</p>
            </article>
        @endforeach
    </section>

    <header class="analytics-section-heading">
        <div><span class="analytics-eyebrow">Performance analytics</span><h3>Operational trends & statistics</h3><p>Threat classification distribution and municipal former rebel counts.</p></div>
        <span class="analytics-freshness"><i class="mdi mdi-circle-medium" aria-hidden="true"></i>Davao del Sur monitoring</span>
    </header>
    <div class="analytics-grid" aria-label="39th IB performance analytics">
        <section class="ops-card analytics-card" aria-labelledby="threat-chart-title">
            <header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-chart-bar" aria-hidden="true"></i></span><div><h3 id="threat-chart-title">Operational Threat Distribution</h3><p>Barangay classification comparison across operational areas.</p></div></div><span class="chart-period">Classification</span></header>
            <div class="analytics-card-content">
                <div class="analytics-metrics metrics-four" aria-label="Current classification totals">
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#dc4c58" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Konsolidado</span><strong class="analytics-metric-value">{{ $statusCounts['Konsolidado'] ?? 0 }}</strong></span></div>
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#e78328" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Rekonsilida</span><strong class="analytics-metric-value">{{ $statusCounts['Rekonsilida'] ?? 0 }}</strong></span></div>
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#d4aa26" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Expansion</span><strong class="analytics-metric-value">{{ $statusCounts['Expansion'] ?? 0 }}</strong></span></div>
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#20a779" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Recovery</span><strong class="analytics-metric-value">{{ $statusCounts['Recovery'] ?? 0 }}</strong></span></div>
                </div>
                <div class="chart-wrap"><canvas id="threatChart" role="img" aria-label="Bar chart of threat classifications"></canvas></div>
            </div>
        </section>
        <section class="ops-card analytics-card" aria-labelledby="muni-chart-title">
            <header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-chart-timeline-variant" aria-hidden="true"></i></span><div><h3 id="muni-chart-title">Recorded FRs by Municipality</h3><p>Former rebel distribution across Davao del Sur municipalities.</p></div></div><span class="chart-period">Municipalities</span></header>
            <div class="analytics-card-content">
                <div class="analytics-metrics" aria-label="Municipal stats">
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#401595" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Municipalities</span><strong class="analytics-metric-value">{{ $perMunicipality->count() }}</strong></span></div>
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#2f6fed" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Total FRs</span><strong class="analytics-metric-value">{{ number_format($stats['total_frs']) }}</strong></span></div>
                    <div class="analytics-metric"><span class="analytics-metric-dot" style="background:#20a779" aria-hidden="true"></span><span class="analytics-metric-copy"><span class="analytics-metric-label">Active Areas</span><strong class="analytics-metric-value">{{ number_format($stats['active_areas']) }}</strong></span></div>
                </div>
                <div class="chart-wrap"><canvas id="municipalityChart" role="img" aria-label="Bar chart of FRs per municipality"></canvas></div>
            </div>
        </section>
    </div>

    <div class="operations-grid">
        <section class="ops-card" aria-labelledby="attention-title"><header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-alert-decagram-outline" aria-hidden="true"></i></span><div><h3 id="attention-title">Needs Attention</h3><p>Operational records requiring review or action.</p></div></div></header><div class="attention-list">@foreach($attentionItems as $item)<a class="attention-item" href="{{ $item['url'] }}"><span class="attention-signal tone-{{ $item['tone'] }}"><i class="mdi {{ $item['icon'] }}" aria-hidden="true"></i></span><span class="attention-copy"><strong>{{ $item['title'] }}</strong><small>{{ $item['detail'] }}</small></span><span class="attention-count">{{ number_format($item['count']) }}</span></a>@endforeach</div></section>
        <section class="ops-card" aria-labelledby="activity-title"><header class="ops-card-header"><div class="ops-card-title"><span class="ops-card-icon"><i class="mdi mdi-history" aria-hidden="true"></i></span><div><h3 id="activity-title">Recent Activity</h3><p>Latest authorized surfaced FR, CDR, and RCSP area updates.</p></div></div></header><div class="activity-list">@forelse($recentActivity as $activity)<a class="activity-item" href="{{ $activity['url'] }}"><span class="activity-icon tone-{{ $activity['tone'] }}"><i class="mdi {{ $activity['icon'] }}" aria-hidden="true"></i></span><span class="activity-copy"><strong>{{ $activity['title'] }}</strong><small>{{ $activity['detail'] }}</small></span><time class="activity-time" datetime="{{ $activity['occurred_at']?->toIso8601String() }}">{{ $activity['occurred_at']?->diffForHumans() }}</time></a>@empty<div class="empty-activity"><i class="mdi mdi-clock-outline" aria-hidden="true"></i>No recent operational activity.</div>@endforelse</div></section>
    </div>
</div>
<div id="ib39Data" data-status='@json($statusCounts)' data-muni-labels='@json($perMunicipality->pluck('municipality'))' data-muni-values='@json($perMunicipality->pluck('frs'))' hidden></div>
@endsection

@push('scripts')
<script>
(function(){
    var el = document.getElementById('ib39Data');
    var status = JSON.parse(el.dataset.status || '{}');
    var municipalityLabels = JSON.parse(el.dataset.muniLabels || '[]');
    var municipalityValues = JSON.parse(el.dataset.muniValues || '[]');

    var threatCanvas = document.getElementById('threatChart');
    if (threatCanvas) {
        new Chart(threatCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Konsolidado', 'Rekonsilida', 'Expansion', 'Recovery'],
                datasets: [{
                    label: 'Barangays',
                    data: [
                        status['Konsolidado'] || 0,
                        status['Rekonsilida'] || 0,
                        status['Expansion'] || 0,
                        status['Recovery'] || 0
                    ],
                    backgroundColor: ['#dc4c58', '#e78328', '#d4aa26', '#20a779'],
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#8b96a7', fontSize: 10 },
                        gridLines: { color: '#eef1f5', drawBorder: false }
                    }],
                    xAxes: [{
                        ticks: { fontColor: '#7f8b9d', fontSize: 10, fontStyle: 'bold' },
                        gridLines: { display: false }
                    }]
                }
            }
        });
    }

    var muniCanvas = document.getElementById('municipalityChart');
    if (muniCanvas) {
        new Chart(muniCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: municipalityLabels.length ? municipalityLabels : ['No active areas'],
                datasets: [{
                    label: 'Former rebels',
                    data: municipalityValues.length ? municipalityValues : [0],
                    backgroundColor: '#401595',
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#8b96a7', fontSize: 10 },
                        gridLines: { color: '#eef1f5', drawBorder: false }
                    }],
                    xAxes: [{
                        ticks: { fontColor: '#7f8b9d', fontSize: 10, fontStyle: 'bold' },
                        gridLines: { display: false }
                    }]
                }
            }
        });
    }
})();
</script>
@endpush

