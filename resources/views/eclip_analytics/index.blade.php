@extends(auth()->user()->hasRole('admin') ? 'layouts.skydash-h' : 'layouts.skydash-v')
@section('title', 'E-CLIP Analytics')
@section('heading', 'E-CLIP Analytics and Reporting')

@push('styles')
<style>
    .analytics-page { --analytics-primary: #2f6fed; --analytics-navy: #172b4d; --analytics-border: #e8edf5; }
    .analytics-hero { background: linear-gradient(125deg, #173b74 0%, #2f6fed 62%, #5795ff 100%); border-radius: 18px; box-shadow: 0 12px 28px rgba(47, 111, 237, .18); color: #fff; overflow: hidden; padding: 1.75rem; position: relative; }
    .analytics-hero::after { background: rgba(255, 255, 255, .08); border-radius: 50%; content: ''; height: 220px; position: absolute; right: -65px; top: -100px; width: 220px; }
    .analytics-eyebrow { font-size: .72rem; font-weight: 700; letter-spacing: .09em; opacity: .78; text-transform: uppercase; }
    .analytics-hero h2 { color: #fff; font-size: 1.65rem; }
    .analytics-scope { align-items: center; display: flex; flex-wrap: wrap; font-size: .85rem; gap: .65rem 1.25rem; opacity: .92; }
    .analytics-scope span { align-items: center; display: inline-flex; gap: .4rem; }
    .analytics-export { background: #fff; border: 0; border-radius: 10px; color: #245cc4; font-weight: 600; padding: .7rem 1rem; position: relative; z-index: 1; }
    .analytics-export:hover { background: #f4f7ff; color: #173b74; }
    .local-committee-analytics .analytics-hero { align-items: center; background: linear-gradient(115deg, #152a4d 0%, #172f57 58%, #123c4d 100%); border: 0; border-radius: 15px; box-shadow: none; display: flex; gap: 1.25rem; justify-content: space-between; min-height: 118px; padding: 1.25rem 1.5rem; }
    .local-committee-analytics .analytics-hero::after { display: none; }
    .analytics-title-main { align-items: center; display: flex; gap: 1rem; min-width: 0; position: relative; z-index: 1; }
    .analytics-title-icon { align-items: center; background: rgba(255, 255, 255, .09); border: 1px solid rgba(255, 255, 255, .08); border-radius: 13px; color: #45e0ba; display: flex; flex: 0 0 54px; font-size: 1.45rem; height: 54px; justify-content: center; width: 54px; }
    .local-committee-analytics .analytics-eyebrow { color: #ff9a62; font-size: .62rem; font-weight: 800; letter-spacing: .14em; opacity: 1; }
    .local-committee-analytics .analytics-hero h2 { font-size: 1.48rem; font-weight: 800; letter-spacing: -.025em; margin: .18rem 0 .25rem; }
    .analytics-header-copy { color: #bac7e5; font-size: .78rem; line-height: 1.45; margin: 0; }
    .analytics-header-actions { align-items: flex-end; display: flex; flex: 0 0 auto; flex-direction: column; gap: .55rem; position: relative; z-index: 1; }
    .analytics-header-badges { align-items: center; display: flex; flex-wrap: wrap; gap: .4rem; justify-content: flex-end; }
    .analytics-header-badge { align-items: center; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .13); border-radius: 999px; color: #d9e2f1; display: inline-flex; font-size: .63rem; font-weight: 700; gap: .35rem; min-height: 30px; padding: .35rem .65rem; }
    .analytics-header-badge i { color: #75e4c8; }
    .local-committee-analytics .analytics-export { align-items: center; border: 1px solid rgba(255, 255, 255, .8); border-radius: 7px; color: #280274; display: inline-flex; font-size: .68rem; font-weight: 750; gap: .35rem; min-height: 36px; padding: .45rem .7rem; }
    .local-committee-analytics .analytics-export:hover, .local-committee-analytics .analytics-export:focus { background: #f4effa; color: #280274; }
    .metric-card, .analytics-card { border: 1px solid var(--analytics-border); border-radius: 14px; box-shadow: 0 4px 16px rgba(23, 43, 77, .045); }
    .metric-card { overflow: hidden; position: relative; }
    .metric-card::before { background: var(--metric-color); content: ''; height: 4px; left: 0; position: absolute; right: 0; top: 0; }
    .metric-card .card-body { padding: 1.3rem; }
    .metric-icon { align-items: center; background: var(--metric-bg); border-radius: 11px; color: var(--metric-color); display: flex; font-size: 1.25rem; height: 42px; justify-content: center; width: 42px; }
    .metric-label { color: #718096; font-size: .78rem; font-weight: 600; letter-spacing: .035em; text-transform: uppercase; }
    .metric-value { color: var(--analytics-navy); font-size: 1.8rem; font-weight: 700; line-height: 1.15; }
    .metric-blue { --metric-bg: #eaf1ff; --metric-color: #2f6fed; }
    .metric-green { --metric-bg: #e8f8f1; --metric-color: #20a779; }
    .metric-amber { --metric-bg: #fff5dc; --metric-color: #d89400; }
    .metric-purple { --metric-bg: #f2ebff; --metric-color: #805ad5; }
    .section-title { color: var(--analytics-navy); font-size: 1rem; font-weight: 700; margin-bottom: .25rem; }
    .section-subtitle { color: #8492a6; font-size: .78rem; margin: 0; }
    .analytics-card .card-body { padding: 1.4rem; }
    .financial-grid { display: grid; gap: .8rem; grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .financial-item { background: #f7f9fc; border: 1px solid #edf1f7; border-radius: 10px; padding: 1rem; }
    .financial-item small { color: #718096; display: block; font-weight: 600; margin-bottom: .3rem; }
    .financial-item strong { color: #173b74; font-size: 1.05rem; }
    .chart-box { height: 270px; position: relative; }
    .status-list { max-height: 270px; overflow: auto; padding-right: .25rem; }
    .status-row { align-items: center; border-bottom: 1px solid #f0f3f8; display: flex; gap: .6rem; justify-content: space-between; padding: .55rem 0; }
    .status-row:last-child { border-bottom: 0; }
    .status-dot { background: var(--dot); border-radius: 50%; flex: 0 0 auto; height: 9px; width: 9px; }
    .status-name { color: #52616f; flex: 1; font-size: .82rem; }
    .status-count { background: #f1f4f9; border-radius: 12px; color: #334155; font-size: .75rem; font-weight: 700; min-width: 28px; padding: .2rem .5rem; text-align: center; }
    .analytics-table { margin-bottom: 0; }
    .analytics-table thead th { background: #f7f9fc; border: 0; color: #718096; font-size: .72rem; font-weight: 700; letter-spacing: .04em; padding: .75rem; text-transform: uppercase; white-space: nowrap; }
    .analytics-table tbody td { border-color: #edf1f7; color: #52616f; font-size: .82rem; padding: .75rem; vertical-align: middle; }
    .doc-progress { background: #edf2f8; border-radius: 10px; height: 12px; overflow: hidden; }
    .doc-progress-bar { background: linear-gradient(90deg, #2f6fed, #51b3ff); border-radius: 10px; height: 100%; min-width: 0; transition: width .4s ease; }
    .completion-ring { align-items: center; background: conic-gradient(#2f6fed var(--percentage), #edf2f8 0); border-radius: 50%; display: flex; height: 96px; justify-content: center; position: relative; width: 96px; }
    .completion-ring::before { background: #fff; border-radius: 50%; content: ''; height: 72px; position: absolute; width: 72px; }
    .completion-ring strong { color: #173b74; font-size: 1.1rem; position: relative; }
    .service-tile { align-items: center; background: #f8fafc; border: 1px solid #edf1f7; border-radius: 10px; display: flex; gap: .75rem; padding: .9rem; }
    .service-tile i { color: #2f6fed; font-size: 1.25rem; }
    .service-tile strong { color: #172b4d; display: block; font-size: 1.15rem; }
    .service-tile span { color: #718096; font-size: .75rem; }
    .empty-state { color: #8492a6; padding: 2rem 1rem; text-align: center; }
    @media (max-width: 1199px) { .financial-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 767px) { .analytics-hero { padding: 1.25rem; } .analytics-hero h2 { font-size: 1.35rem; } .analytics-export { margin-top: 1rem; width: 100%; } .local-committee-analytics .analytics-hero { align-items: flex-start; flex-direction: column; min-height: 0; padding: 1.1rem; } .analytics-title-main { align-items: flex-start; } .analytics-title-icon { flex-basis: 44px; font-size: 1.18rem; height: 44px; width: 44px; } .analytics-header-actions, .analytics-header-badges { align-items: stretch; justify-content: flex-start; width: 100%; } .analytics-header-badge { justify-content: center; } .local-committee-analytics .analytics-export { justify-content: center; margin-top: 0; width: 100%; } .financial-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 480px) { .financial-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $isLocalCommittee = auth()->user()->hasRole('local_eclip_committee');
    $summaryMeta = $isLocalCommittee
        ? [
            'total' => ['icon' => 'mdi-clipboard-text-outline', 'tone' => 'blue'],
            'completed' => ['icon' => 'mdi-clipboard-check-outline', 'tone' => 'green'],
            'delayed' => ['icon' => 'mdi-progress-clock', 'tone' => 'amber'],
            'returned' => ['icon' => 'mdi-reply-outline', 'tone' => 'purple'],
        ]
        : [
            'total' => ['icon' => 'mdi-folder-multiple-outline', 'tone' => 'blue'],
            'completed' => ['icon' => 'mdi-check-circle-outline', 'tone' => 'green'],
            'delayed' => ['icon' => 'mdi-clock-alert-outline', 'tone' => 'amber'],
            'returned' => ['icon' => 'mdi-undo-variant', 'tone' => 'purple'],
        ];
    $statusColors = ['#2f6fed', '#20a779', '#f2b134', '#805ad5', '#e65b65', '#36a2ae', '#7d8da8', '#e8893c'];
    $activeStatuses = collect($statusCounts)->filter(fn ($count) => $count > 0);
@endphp

<div class="analytics-page {{ $isLocalCommittee ? 'local-committee-analytics' : '' }}">
    @if($isLocalCommittee)
        <header class="analytics-hero mb-4" aria-labelledby="analytics-title">
            <div class="analytics-title-main">
                <span class="analytics-title-icon"><i class="mdi mdi-chart-line-variant" aria-hidden="true"></i></span>
                <div>
                    <div class="analytics-eyebrow">Monitoring dashboard</div>
                    <h2 id="analytics-title">E-CLIP Program Overview</h2>
                    <p class="analytics-header-copy">Monitor municipal case progress, financial delivery, and recorded program outcomes.</p>
                </div>
            </div>
            <div class="analytics-header-actions">
                <div class="analytics-header-badges" aria-label="Analytics reporting scope">
                    <span class="analytics-header-badge"><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i>{{ $scopeLabel }}</span>
                    <span class="analytics-header-badge"><i class="mdi mdi-clock-outline" aria-hidden="true"></i>Delayed after {{ $delayDays }} days</span>
                </div>
                <a href="{{ route('eclip.analytics.export') }}" class="btn analytics-export">
                    <i class="mdi mdi-download" aria-hidden="true"></i>Export Aggregate CSV
                </a>
            </div>
        </header>
    @else
        <section class="analytics-hero mb-4" aria-labelledby="analytics-title">
            <div class="row align-items-center">
                <div class="col-md-8 position-relative" style="z-index: 1;">
                    <div class="analytics-eyebrow mb-2">Monitoring dashboard</div>
                    <h2 id="analytics-title" class="font-weight-bold mb-2">E-CLIP Program Overview</h2>
                    <div class="analytics-scope">
                        <span><i class="mdi mdi-map-marker-outline"></i> {{ $scopeLabel }}</span>
                        <span><i class="mdi mdi-clock-outline"></i> Delayed after {{ $delayDays }} days without an update</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-right">
                    <a href="{{ route('eclip.analytics.export') }}" class="btn analytics-export">
                        <i class="mdi mdi-download mr-1"></i> Export Aggregate CSV
                    </a>
                </div>
            </div>
        </section>
    @endif

    <div class="row">
        @foreach($summary as $label => $value)
            @php
                $meta = $summaryMeta[$label] ?? ['icon' => 'mdi-chart-box-outline', 'tone' => 'blue'];
            @endphp
            <div class="col-sm-6 col-xl-3 mb-4">
                <div class="card metric-card metric-{{ $meta['tone'] }} h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label mb-2">{{ str($label)->replace('_', ' ')->title() }}</div>
                            <div class="metric-value">{{ number_format($value) }}</div>
                        </div>
                        <div class="metric-icon"><i class="mdi {{ $meta['icon'] }}" aria-hidden="true"></i></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($financial)
        <section class="card analytics-card mb-4" aria-labelledby="financial-title">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 id="financial-title" class="section-title">Aggregate Financial Monitoring</h3>
                        <p class="section-subtitle">Consolidated amounts for the authorized reporting scope.</p>
                    </div>
                    <i class="mdi mdi-shield-lock-outline text-primary h4 mb-0" title="Restricted financial information"></i>
                </div>
                <div class="financial-grid">
                    @foreach($financial as $label => $amount)
                        <div class="financial-item">
                            <small>{{ str($label)->replace('_', ' ')->title() }}</small>
                            <strong>₱{{ number_format($amount, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="row">
        <div class="col-xl-7 mb-4">
            <section class="card analytics-card h-100" aria-labelledby="status-title">
                <div class="card-body">
                    <div class="mb-3">
                        <h3 id="status-title" class="section-title">Cases by Status</h3>
                        <p class="section-subtitle">Current distribution across the E-CLIP workflow.</p>
                    </div>
                    @if($activeStatuses->isNotEmpty())
                        <div class="row align-items-center">
                            <div class="col-md-7"><div class="chart-box"><canvas id="statusChart" aria-hidden="true"></canvas></div></div>
                            <div class="col-md-5">
                                <div class="status-list">
                                    @foreach($activeStatuses as $status => $count)
                                        <div class="status-row">
                                            <span class="status-dot" style="--dot: {{ $statusColors[$loop->index % count($statusColors)] }};"></span>
                                            <span class="status-name">{{ $status }}</span>
                                            <span class="status-count">{{ number_format($count) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state"><i class="mdi mdi-chart-donut h3 d-block mb-2"></i>No case data is available for this scope.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-xl-5 mb-4">
            <section class="card analytics-card h-100" aria-labelledby="stage-title">
                <div class="card-body">
                    <div class="mb-3">
                        <h3 id="stage-title" class="section-title">Average Time in Stage</h3>
                        <p class="section-subtitle">Average elapsed hours before the next recorded transition.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table analytics-table">
                            <thead><tr><th>Workflow stage</th><th class="text-right">Hours</th></tr></thead>
                            <tbody>
                                @forelse($stageHours as $stage => $hours)
                                    <tr><td>{{ $stage }}</td><td class="text-right font-weight-bold">{{ number_format($hours, 1) }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="empty-state">Not enough transition data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <section class="card analytics-card h-100" aria-labelledby="documents-title">
                <div class="card-body">
                    <h3 id="documents-title" class="section-title">Required Document Completeness</h3>
                    <p class="section-subtitle mb-4">Certification progress for required case documents.</p>
                    <div class="d-flex align-items-center">
                        <div class="completion-ring mr-4" style="--percentage: {{ min(100, max(0, $documentCompleteness['percentage'])) }}%;">
                            <strong>{{ number_format($documentCompleteness['percentage'], 1) }}%</strong>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Certified</span>
                                <strong>{{ number_format($documentCompleteness['certified']) }} / {{ number_format($documentCompleteness['expected']) }}</strong>
                            </div>
                            <div class="doc-progress" role="progressbar" aria-valuenow="{{ $documentCompleteness['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="doc-progress-bar" style="width: {{ min(100, max(0, $documentCompleteness['percentage'])) }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-7 mb-4">
            <section class="card analytics-card h-100" aria-labelledby="services-title">
                <div class="card-body">
                    <h3 id="services-title" class="section-title">Basic Services Monitoring</h3>
                    <p class="section-subtitle mb-3">Delivery status of services supporting reintegration.</p>
                    <div class="row">
                        @foreach($basicServices as $label => $count)
                            <div class="col-sm-6 mb-3">
                                <div class="service-tile">
                                    <i class="mdi {{ $label === 'completed' ? 'mdi-check-circle-outline' : ($label === 'overdue' ? 'mdi-alert-circle-outline' : 'mdi-progress-clock') }}"></i>
                                    <div><strong>{{ number_format($count) }}</strong><span>{{ str($label)->replace('_', ' ')->title() }}</span></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="section-subtitle"><i class="mdi mdi-information-outline mr-1"></i>These indicators do not determine E-CLIP eligibility automatically.</p>
                </div>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="{{ $municipalities ? 'col-xl-6' : 'col-12' }} mb-4">
            <section class="card analytics-card h-100" aria-labelledby="trend-title">
                <div class="card-body">
                    <h3 id="trend-title" class="section-title">Completed Cases — Last 12 Months</h3>
                    <p class="section-subtitle mb-3">Monthly completion trend based on recorded status changes.</p>
                    <div class="chart-box"><canvas id="completionChart" aria-hidden="true"></canvas></div>
                    <table class="sr-only">
                        <caption>Completed cases over the last 12 months</caption>
                        <thead><tr><th>Month</th><th>Completed cases</th></tr></thead>
                        <tbody>@foreach($completedTrend as $month => $count)<tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>@endforeach</tbody>
                    </table>
                </div>
            </section>
        </div>

        @if($municipalities)
            <div class="col-xl-6 mb-4">
                <section class="card analytics-card h-100" aria-labelledby="municipality-title">
                    <div class="card-body">
                        <h3 id="municipality-title" class="section-title">Municipality Performance</h3>
                        <p class="section-subtitle mb-3">Aggregate case outcomes by municipality.</p>
                        <div class="table-responsive">
                            <table class="table analytics-table">
                                <thead><tr><th>Municipality</th><th class="text-right">Total</th><th class="text-right">Completed</th><th class="text-right">Delayed</th></tr></thead>
                                <tbody>
                                    @foreach($municipalities as $municipality)
                                        <tr>
                                            <td class="font-weight-medium">{{ $municipality['name'] }}</td>
                                            <td class="text-right">{{ number_format($municipality['total']) }}</td>
                                            <td class="text-right text-success font-weight-bold">{{ number_format($municipality['completed']) }}</td>
                                            <td class="text-right {{ $municipality['delayed'] > 0 ? 'text-warning font-weight-bold' : '' }}">{{ number_format($municipality['delayed']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        var activeStatusLabels = @json($activeStatuses->keys()->values());
        var activeStatusValues = @json($activeStatuses->values());
        var palette = @json($statusColors);
        var statusCanvas = document.getElementById('statusChart');

        if (statusCanvas && activeStatusValues.length) {
            new Chart(statusCanvas.getContext('2d'), {
                type: 'doughnut',
                data: { labels: activeStatusLabels, datasets: [{ data: activeStatusValues, backgroundColor: activeStatusValues.map(function (_, index) { return palette[index % palette.length]; }), borderWidth: 3, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, cutoutPercentage: 68, legend: { display: false }, tooltips: { callbacks: { label: function (item, data) { return ' ' + data.labels[item.index] + ': ' + data.datasets[0].data[item.index]; } } } }
            });
        }

        var trendCanvas = document.getElementById('completionChart');
        if (trendCanvas) {
            new Chart(trendCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(array_keys($completedTrend)),
                    datasets: [{ label: 'Completed cases', data: @json(array_values($completedTrend)), borderColor: '#2f6fed', backgroundColor: 'rgba(47, 111, 237, .10)', pointBackgroundColor: '#2f6fed', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, borderWidth: 2, fill: true, lineTension: .3 }]
                },
                options: { responsive: true, maintainAspectRatio: false, legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 }, gridLines: { color: '#edf1f7', drawBorder: false } }], xAxes: [{ gridLines: { display: false }, ticks: { maxRotation: 45, minRotation: 0 } }] } }
            });
        }
    })();
</script>
@endpush
