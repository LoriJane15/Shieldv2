@extends('layouts.skydash-v')
@section('title', 'RCSP Areas')
@section('heading', '39th IB RCSP Areas')

@push('styles')
<style>
    .areas-page{--navy:#172b4d;--border:#e4e4e7}.areas-hero{align-items:center;background:linear-gradient(115deg,#152a4d 0%,#172f57 58%,#123c4d 100%);border-radius:15px;color:#fff;display:flex;justify-content:space-between;min-height:110px;padding:1.25rem 1.5rem}.hero-title-main{align-items:center;display:flex;gap:1rem;min-width:0;position:relative;z-index:1}.module-title-icon{align-items:center;background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.08);border-radius:13px;color:#45e0ba;display:flex;flex:0 0 54px;font-size:1.45rem;height:54px;justify-content:center;width:54px}.hero-eyebrow{color:#ff9a62;font-size:.62rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.areas-hero h2{color:#fff;font-size:1.48rem;font-weight:800;letter-spacing:-.025em;margin-bottom:.2rem}.areas-hero p{color:#bac7e5;font-size:.78rem}.map-link{align-items:center;background:#fff;border:1px solid rgba(255,255,255,.8);border-radius:10px;color:#280274;display:inline-flex;font-size:.72rem;font-weight:700;gap:.4rem;padding:.55rem .85rem;position:relative;text-decoration:none!important;z-index:1}.map-link:hover{background:#401595;border-color:#401595;color:#fff}.threshold-card,.areas-card{background:#fff;border:1px solid var(--border);border-radius:11px;box-shadow:none}.threshold-card .card-body{padding:.9rem 1rem}.thresholds{display:grid;gap:.65rem;grid-template-columns:repeat(4,minmax(0,1fr))}.threshold{align-items:center;background:#f8f9fb;border:1px solid #edf0f4;border-radius:9px;display:flex;gap:.55rem;padding:.65rem .8rem}.threshold-dot{background:var(--tone);border-radius:50%;flex:0 0 9px;height:9px;width:9px}.threshold strong{color:#334155;display:block;font-size:.72rem;font-weight:750}.threshold small{color:#8492a6;display:block;font-size:.62rem}.areas-card{overflow:hidden}.filter-bar{align-items:end;background:#fff;border-bottom:1px solid #edf1f6;display:flex;flex-wrap:wrap;gap:.7rem;padding:1rem 1.2rem}.filter-field label{color:#718096;display:block;font-size:.66rem;font-weight:700;margin-bottom:.25rem;text-transform:uppercase}.filter-search{min-width:230px}.filter-select{min-width:170px}.form-control{border-color:#dfe5ee;border-radius:8px}.form-control:focus{border-color:#401595;box-shadow:0 0 0 3px rgba(64,21,149,.11)}.area-table{margin:0}.area-table thead th{background:#f8f6fa;border:0;color:#857a90;font-size:.69rem;font-weight:700;letter-spacing:.04em;padding:.85rem 1rem;text-transform:uppercase;white-space:nowrap}.area-table tbody td{border-color:#edf1f6;color:#52616f;font-size:.8rem;padding:.8rem 1rem;vertical-align:middle}.barangay{color:#263a59;font-weight:700}.status-badge{border:1px solid var(--badge-border);background:var(--badge-bg);border-radius:14px;color:var(--badge-color);display:inline-flex;font-size:.63rem;font-weight:700;padding:.3rem .6rem;text-transform:uppercase}.status-konsolidado{--badge-bg:#ffebed;--badge-border:#facdd1;--badge-color:#bd3e49}.status-rekonsilida{--badge-bg:#fff0e2;--badge-border:#f5d4b6;--badge-color:#b75f13}.status-expansion{--badge-bg:#fff7d8;--badge-border:#efe0a4;--badge-color:#94700d}.status-recovery{--badge-bg:#e8f8f1;--badge-border:#bfe8d6;--badge-color:#16845e}.status-unclassified{--badge-bg:#f1f4f8;--badge-border:#dfe5ee;--badge-color:#64748b}.count-value{color:#401595;font-weight:750}.count-form{align-items:center;display:flex;gap:.45rem}.count-form input{max-width:80px}.set-button{border-radius:7px;font-weight:600}.areas-pagination{border-top:1px solid #edf1f6;padding:1rem 1.2rem}.empty-state{color:#8492a6;padding:3rem 1rem;text-align:center}.empty-state i{color:#bcc7d6;display:block;font-size:2.2rem;margin-bottom:.5rem}
    @media(max-width:767px){.areas-hero{align-items:flex-start;flex-direction:column;gap:1rem;padding:1.1rem}.map-link{margin-top:.5rem}.thresholds{grid-template-columns:1fr 1fr}.filter-field,.filter-search,.filter-select{width:100%;min-width:0}.filter-bar .btn{flex:1}.area-table thead{display:none}.area-table,.area-table tbody,.area-table tr,.area-table td{display:block;width:100%}.area-table tr{border-bottom:1px solid #e7ecf3;padding:.65rem 0}.area-table tbody td{border:0;padding:.3rem 1rem}.count-form input{max-width:110px}}
</style>
@endpush

@section('content')
@php
    $statusClass = fn ($status) => 'status-'.strtolower($status ?: 'unclassified');
@endphp
<div class="areas-page">
    <header class="areas-hero mb-4">
        <div class="hero-title-main">
            <span class="module-title-icon"><i class="mdi mdi-map-marker-radius" aria-hidden="true"></i></span>
            <div>
                <div class="hero-eyebrow mb-1">Operational Area Monitoring</div>
                <h2 class="mb-1">RCSP Barangay Classification</h2>
                <p class="mb-0">Update recorded former-rebel counts and monitor the resulting area classification.</p>
            </div>
        </div>
        <a href="{{ route('ib39.map') }}" class="map-link"><i class="mdi mdi-map"></i>Open Operational Map</a>
    </header>

    <section class="card threshold-card mb-4" aria-label="Classification thresholds">
        <div class="card-body">
            <div class="thresholds">
                <div class="threshold"><span class="threshold-dot" style="--tone:#dc4c58"></span><div><strong>Konsolidado</strong><small>20 or more FRs</small></div></div>
                <div class="threshold"><span class="threshold-dot" style="--tone:#e78328"></span><div><strong>Rekonsilida</strong><small>15–19 FRs</small></div></div>
                <div class="threshold"><span class="threshold-dot" style="--tone:#d4aa26"></span><div><strong>Expansion</strong><small>10–14 FRs</small></div></div>
                <div class="threshold"><span class="threshold-dot" style="--tone:#20a779"></span><div><strong>Recovery</strong><small>Fewer than 10 FRs</small></div></div>
            </div>
        </div>
    </section>

    <section class="card areas-card" aria-label="RCSP barangays">
        <form method="GET" class="filter-bar">
            <div class="filter-field filter-search"><label for="area-search">Search Barangay</label><input id="area-search" name="search" value="{{ request('search') }}" placeholder="Enter barangay name" class="form-control"></div>
            <div class="filter-field filter-select"><label for="municipality">Municipality or City</label><select id="municipality" name="municipality" class="form-control"><option value="">All municipalities</option>@foreach($municipalities as $municipality)<option value="{{ $municipality }}" @selected(request('municipality')===$municipality)>{{ $municipality }}</option>@endforeach</select></div>
            <div class="filter-field filter-select"><label for="area-status">Classification</label><select id="area-status" name="status" class="form-control"><option value="">All classifications</option>@foreach(['Konsolidado','Rekonsilida','Expansion','Recovery'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></div>
            <button class="btn btn-primary"><i class="mdi mdi-filter-variant mr-1"></i>Apply Filters</button>@if(request()->hasAny(['search','municipality','status']))<a href="{{ route('ib39.areas.index') }}" class="btn btn-light"><i class="mdi mdi-close mr-1"></i>Clear</a>@endif
        </form>
        <div class="table-responsive"><table class="table area-table"><thead><tr><th>Province</th><th>Municipality/City</th><th>Barangay</th><th>Classification</th><th>Recorded FRs</th><th>Update Count</th></tr></thead><tbody>
            @forelse($areas as $area)
                <tr><td>Davao del Sur</td><td>{{ $area->municipality }}</td><td><span class="barangay">{{ $area->barangay }}</span></td><td><span class="status-badge {{ $statusClass($area->status) }}">{{ $area->status ?: 'Unclassified' }}</span></td><td><span class="count-value">{{ number_format($area->frs) }}</span></td><td><form method="POST" action="{{ route('ib39.areas.update',$area) }}" class="count-form" data-count-form>@csrf @method('PUT')<label class="sr-only" for="frs-{{ $area->id }}">FR count for {{ $area->barangay }}</label><input id="frs-{{ $area->id }}" name="frs" type="number" min="0" value="{{ $area->frs }}" class="form-control" required><button class="btn btn-sm btn-outline-primary set-button" data-submit-button>Save</button></form></td></tr>
            @empty<tr><td colspan="6"><div class="empty-state"><i class="mdi mdi-map-marker-outline"></i><strong class="d-block text-dark mb-1">No barangays found</strong><span>Adjust the filters to view other RCSP areas.</span></div></td></tr>@endforelse
        </tbody></table></div>
        @if($areas->hasPages())<div class="areas-pagination">{{ $areas->links() }}</div>@endif
    </section>
</div>
@endsection

@push('scripts')
<script>document.querySelectorAll('[data-count-form]').forEach(function(form){form.addEventListener('submit',function(){var button=form.querySelector('[data-submit-button]');button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Saving';});});</script>
@endpush

