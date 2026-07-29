@extends('layouts.skydash-v')
@section('title', 'Operational Map')
@section('heading', 'Operational Map')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/ib39-operational-map.css') }}">
    @vite('resources/js/ib39-operational-map.js')
@endpush

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="map-page-heading">
                        <div>
                            <h3 class="font-weight-bold mb-1">Former Rebel Locations</h3>
                            <p class="text-muted mb-0">Authorized operational view for Davao del Sur</p>
                        </div>
                        <div class="map-count-card" aria-live="polite">
                            <span class="map-count-value" data-visible-count>0</span>
                            <span class="map-count-label">visible of <span data-total-count>0</span></span>
                        </div>
                    </div>

                    <div class="map-toolbar" aria-label="Map filters">
                        <div class="map-search">
                            <i class="mdi mdi-magnify" aria-hidden="true"></i>
                            <label class="visually-hidden" for="mapSearch">Search map records</label>
                            <input id="mapSearch" type="search" class="form-control"
                                   placeholder="Search name, address, batch, or status"
                                   data-map-search>
                        </div>

                        <div>
                            <label class="visually-hidden" for="mapStatus">Filter by status</label>
                            <select id="mapStatus" class="form-select" data-map-status>
                                <option value="">All statuses</option>
                                @foreach ([
                                    'Active', 'On hold', 'Reintegrated', 'Inactive', 'Under Review',
                                    'Disengaged', 'Pending', 'Suspended', 'Completed', 'Deceased', 'Relocated',
                                ] as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" class="btn btn-outline-secondary" data-map-reset>
                            <i class="mdi mdi-filter-remove-outline me-1" aria-hidden="true"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary" data-map-fit>
                            <i class="mdi mdi-crosshairs-gps me-1" aria-hidden="true"></i> Show all
                        </button>
                    </div>

                    <div class="map-layout">
                        <aside class="map-legend" aria-label="Marker legend">
                            <h6 class="mb-3">Marker status</h6>
                            <div data-map-legend></div>
                            <p class="map-privacy-note">
                                <i class="mdi mdi-shield-lock-outline" aria-hidden="true"></i>
                                Location information is restricted to authorized users.
                            </p>
                        </aside>

                        <div class="map-stage">
                            <div id="ib39Map" data-source="{{ route('ib39.map.data') }}"></div>
                            <div class="map-state" data-map-state>
                                <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                <span data-map-state-text>Loading authorized map data…</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
