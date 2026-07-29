@extends('layouts.skydash-v')
@section('title', 'Dashboard')
@section('heading', 'Government Agency')

@php
    $agencyLogo = $agency?->profile
        ? $agency->profile_url
        : auth()->user()->logo_url;
@endphp

@push('styles')
<style>
    .agency-welcome { background: linear-gradient(to right, orange 1%, #fff 70%); border-radius: 12px; border: none; }
    .agency-welcome .banner-name {
        position: absolute; bottom: 0; left: 0; background-color: orange; color: #fff;
        font-weight: bold; padding: .5rem 1.25rem; border-radius: 0 30px 0 0;
    }
    .table td div { max-width: 220px; white-space: normal; word-break: break-word; line-height: 1.4; }
    .table th { font-weight: 600; background-color: #f8f9fa; }
    .table td, .table th { vertical-align: middle; border-bottom: 1px solid #dee2e6; }
    .table tbody tr:hover { background-color: #fff8e8; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Welcome banner --}}
    <div class="card shadow-sm mb-4 position-relative agency-welcome">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <img src="{{ $agencyLogo }}" alt="Logo"
                     onerror="this.onerror=null;this.src='{{ asset('assets/img/kc-logo.svg') }}'"
                     style="height:130px;margin-right:20px;object-fit:contain;">
            </div>
            <div class="text-end" style="margin-right:10px;">
                <h2 class="font-weight-bold">Welcome <span>{{ $agency?->acronym ?? 'Agency' }}!</span></h2>
                <p class="text-muted mb-3">Strengthening Institutions and Empowering Localities Against Discrimination Programs for Former Rebels</p>
                <a href="{{ route('gov_agency.implan.index') }}" class="btn btn-warning">View Implan</a>
            </div>
        </div>
        <div class="banner-name">{{ $agency?->name }}</div>
    </div>

    {{-- Assigned Implementation Overview --}}
    <div class="card shadow-sm mb-4 mt-5">
        <div class="card-body p-4">
            <h4 class="card-title mb-4">Assigned Implementation Overview</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="py-3">Issues or Concern</th>
                            <th class="py-3">Program</th>
                            <th class="py-3">Responsible Agency</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">View Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accepted as $row)
                            <tr>
                                <td class="py-3"><div>{{ $row->issues ?: 'No issue specified' }}</div></td>
                                <td class="py-3"><div>{{ $row->program ?: 'No program specified' }}</div></td>
                                <td class="py-3">
                                    <div class="d-flex gap-2">
                                        @forelse (($row->agencies ?? []) as $aid)
                                            @php $ag = $agenciesById[$aid] ?? null; @endphp
                                            @if ($ag && $ag->profile)
                                                <img src="{{ $ag->profile_url }}"
                                                     alt="{{ $ag->acronym }}" title="{{ $ag->acronym }}"
                                                     style="width:40px;height:40px;object-fit:contain;"
                                                     onerror="this.style.display='none'">
                                            @elseif ($ag)
                                                <span class="badge badge-secondary">{{ $ag->acronym }}</span>
                                            @endif
                                        @empty
                                            <div class="text-muted">—</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-3"><div>{{ ucfirst($row->status ?? 'Pending') }}</div></td>
                                <td class="py-3">
                                    <a href="{{ route('gov_agency.implan.show', $row) }}" class="btn btn-warning btn-sm px-3">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-3">No data available for this agency.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
