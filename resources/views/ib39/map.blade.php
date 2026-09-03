@extends('layouts.skydash-v')
@section('title', 'Operational Map')
@section('heading', '39th IB Operational Map')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/ib39-operational-map.css') }}">
    @vite('resources/js/ib39-operational-map.js')
@endpush

@section('content')
<div class="operational-map-page">
    <a href="{{ route('ib39.dashboard') }}" class="map-back"><i class="mdi mdi-arrow-left"></i> Back to 39th IB dashboard</a>

    <section class="map-hero mb-4" aria-labelledby="operational-map-title">
        <div class="row align-items-center position-relative" style="z-index:1">
            <div class="col-md-8"><div class="map-eyebrow mb-1">Authorized operational view</div><h2 id="operational-map-title" class="mb-2">Former Rebel Locations</h2><p class="mb-0"><i class="mdi mdi-map-marker mr-1"></i>Davao del Sur · Restricted location monitoring</p></div>
            <div class="col-md-4"><div class="map-count-card" aria-live="polite"><span class="map-count-value" data-visible-count>0</span><span class="map-count-label">visible records</span><span class="map-count-divider">of</span><strong data-total-count>0</strong></div></div>
        </div>
    </section>

    <section class="map-card" aria-label="Operational location map">
        <div class="map-toolbar" aria-label="Map filters">
            <div class="map-filter map-search"><label for="mapSearch">Search Records</label><div class="map-input-wrap"><i class="mdi mdi-magnify" aria-hidden="true"></i><input id="mapSearch" type="search" class="form-control" placeholder="Name, address, batch, or status" data-map-search></div></div>
            <div class="map-filter"><label for="mapStatus">Record Status</label><select id="mapStatus" class="form-control" data-map-status><option value="">All statuses</option>@foreach(['Active','On hold','Reintegrated','Inactive','Under Review','Disengaged','Pending','Suspended','Completed','Deceased','Relocated'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></div>
            <button type="button" class="btn btn-light map-tool-button" data-map-reset><i class="mdi mdi-refresh mr-1" aria-hidden="true"></i>Reset</button>
            <button type="button" class="btn btn-primary map-tool-button" data-map-fit><i class="mdi mdi-crosshairs-gps mr-1" aria-hidden="true"></i>Fit Markers</button>
        </div>

        <div class="map-meta-bar"><span><i class="mdi mdi-circle-medium map-live-dot"></i><span data-data-status>Waiting for authorized data</span></span><span><i class="mdi mdi-clock-outline"></i>Data generated <time data-generated-at>when the map loads</time></span><a href="{{ route('ib39.areas.index') }}"><i class="mdi mdi-format-list-bulleted"></i>Manage RCSP areas</a></div>

        <div class="map-layout">
            <aside class="map-legend" aria-label="Marker status legend"><div class="legend-heading"><i class="mdi mdi-map-marker-radius"></i><div><h3>Marker Status</h3><p>Colors represent the current record status.</p></div></div><div class="legend-items" data-map-legend></div><div class="map-privacy-note"><i class="mdi mdi-lock" aria-hidden="true"></i><span><strong>Restricted information</strong>Location details are visible only to authorized 39th IB users.</span></div></aside>
            <div class="map-stage"><div id="ib39Map" data-source="{{ route('ib39.map.data') }}"></div><div class="map-state" data-map-state><span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span><span data-map-state-text>Loading authorized map data…</span></div></div>
        </div>
    </section>
</div>
@endsection
