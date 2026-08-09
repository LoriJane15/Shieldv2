@extends('layouts.skydash-h')
@section('title', 'Locations')
@section('heading', 'RCSP Locations')

@push('styles')
<style>
    .location-note { background: #eef5ff; border: 1px solid #d7e6ff; border-radius: 10px; color: #365b88; padding: 1rem; }
</style>
@endpush

@section('content')
    <div class="location-note mb-4"><i class="mdi mdi-shield-lock-outline mr-1"></i>Beneficiary names, addresses, and precise coordinates are excluded from this monitoring view.</div>

    {{-- RCSP barangays grouped by municipality --}}
    <div class="row">
        <div class="col-12">
            @forelse ($municipalities as $m)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">{{ $m['name'] }}</h5>
                            <span class="badge badge-info">{{ $m['barangays']->count() }} barangay(s)</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @foreach ($m['barangays'] as $b)
                                <span class="badge badge-secondary">
                                    {{ $b->barangay?->name ?? 'Barangay #'.$b->barangay_id }} · P{{ $b->current_phase }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No RCSP locations yet.</p>
            @endforelse
        </div>
    </div>
@endsection
