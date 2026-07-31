@extends('layouts.skydash-h')
@section('title', 'RCSP Forms')
@section('heading', 'RCSP Form Review')

@php
    // original tab order: Ongoing / Approved / Returned Form / Completed Document
    $tabMeta = [
        'pending'   => ['Ongoing', 'badge-info'],
        'approved'  => ['Approved', 'badge-success'],
        'returned'  => ['Returned Form', 'badge-warning'],
        'completed' => ['Completed Document', 'badge-secondary'],
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title mb-2" style="font-weight: bold; font-size: 1.5rem; text-align: left;">Bulk File Submission</h1>
                <p class="card-description mb-3">
                    <span style="color: #280274;">Add and manage RCSP evaluation data</span>
                    for barangays within the municipality/city, including information, documentation, and monitoring details.
                </p>

                <div class="alert alert-info mt-3 py-2" role="alert">
                    <i class="ti-info-alt"></i>
                    Documents are retained for 28 days per policy. Some exceptions apply.
                </div>

                <ul class="nav nav-pills nav-pills-success" role="tablist" id="pills-tab" style="margin-bottom: 0; border-bottom: 0;">
                    @foreach ($tabMeta as $key => [$label, $badge])
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill" href="#tab-{{ $key }}" role="tab">
                                {{ $label }}
                                @if ($tabs[$key]->count())<span class="badge {{ $badge }}">{{ $tabs[$key]->count() }}</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    @foreach ($tabMeta as $key => [$label, $badge])
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-12 d-flex align-items-end justify-content-start">
                                    <div class="form-group mb-0 me-3" style="width: 200px">
                                        <select class="form-control form-control-sm"><option>Filter by: All</option></select>
                                    </div>
                                    <div class="form-group mb-0 me-3" style="width: 200px">
                                        <input type="date" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group mb-0 me-3" style="width: 200px">
                                        <input type="text" class="form-control form-control-sm" placeholder="Search...">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Form</th>
                                            <th>Barangay</th>
                                            <th>City/Municipality</th>
                                            <th>Form ID</th>
                                            <th>Phase</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tabs[$key] as $b)
                                            <tr class="clickable-row" style="cursor:pointer;"
                                                onclick="window.location='{{ route('admin.rcsp.show', $b) }}'">
                                                <td>
                                                    Form<br>
                                                    <small class="text-muted">Generated on: {{ $b->updated_at?->timezone(config('app.display_timezone'))->format('M d, Y g:i A') }}</small>
                                                </td>
                                                <td>{{ $b->barangay?->name ?? 'Barangay #'.$b->barangay_id }}</td>
                                                <td>{{ $b->municipality?->name ?? 'No Municipality' }}</td>
                                                <td>FILE-{{ $b->id }}</td>
                                                <td>Phase {{ $b->current_phase }}</td>
                                                <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center">No submitted forms available for review.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-pills { padding: 0; margin-bottom: 0 !important; position: relative; z-index: 1; }
    .nav-pills .nav-item { margin-right: 6px; }
    .nav-pills .nav-link {
        border: 1px solid #dee2e6; border-radius: 8px 8px 0 0; padding: 8px 25px;
        background: #f8f9fa; color: #666; transition: all 0.2s ease;
    }
    .nav-pills .nav-link.active {
        background: #fff !important; color: #000 !important;
        border-bottom-color: transparent; border-top: 5px solid #280274; padding-top: 6px;
    }
    .nav-pills .nav-item:hover .nav-link:not(.active) { background: #f0f0f0 !important; }
    .nav-pills .nav-link .badge { margin-left: 5px; vertical-align: middle; }
    .tab-content { border: 1px solid #dee2e6; padding: 20px; background: #fff; margin-top: -1px; }
    .clickable-row:hover { background-color: #f8f9fa; }
    .row.mt-3 { margin-top: 0 !important; margin-bottom: 12px; }
</style>
@endpush
