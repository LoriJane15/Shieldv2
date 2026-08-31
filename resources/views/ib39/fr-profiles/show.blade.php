@extends('layouts.skydash-v')
@section('title', 'View FR Profile')
@section('heading', 'View FR Profile')

@push('styles')
<style>
    .fr-profile-page{--navy:#172b4d;--border:#e2e8f0}.profile-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;padding:1.5rem}.profile-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.profile-hero p{font-size:.78rem;opacity:.86}.profile-reference{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);border-radius:10px;font-size:.75rem;font-weight:700;padding:.65rem .85rem}.profile-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.05)}.profile-card .card-body{padding:1.35rem}.section-title{border-bottom:1px solid #edf1f6;color:var(--navy);font-size:.95rem;font-weight:700;margin-bottom:1rem;padding-bottom:.65rem}.detail-grid{display:grid;gap:1rem;grid-template-columns:repeat(2,minmax(0,1fr))}.detail-item{min-width:0}.detail-item.full{grid-column:1/-1}.detail-label{color:#718096;display:block;font-size:.66rem;font-weight:700;letter-spacing:.04em;margin-bottom:.25rem;text-transform:uppercase}.detail-value{color:#334155;font-size:.82rem;overflow-wrap:anywhere}.fallback-list{display:grid;gap:.65rem;grid-template-columns:repeat(2,minmax(0,1fr))}.fallback-item,.history-item{background:#f7f9fc;border:1px solid #e8edf4;border-radius:10px;padding:.8rem}.fallback-item strong,.history-item strong{color:#334155;display:block;font-size:.75rem}.fallback-item span,.history-item span{color:#718096;font-size:.7rem}.privacy-note{background:#f7f9fc;border-left:4px solid #2f6fed;border-radius:8px;color:#64748b;font-size:.73rem;padding:.8rem .9rem}@media(max-width:767px){.profile-hero{padding:1.1rem}.detail-grid,.fallback-list{grid-template-columns:1fr}.detail-item.full{grid-column:auto}}
</style>
@endpush

@section('content')
<div class="fr-profile-page">
    <header class="profile-hero mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap:1rem">
            <div><h2 class="mb-1">{{ $record->display_name }}</h2><p class="mb-0">Read-only surfaced former rebel profile</p></div>
            <div class="profile-reference">{{ $record->reference_number }}</div>
        </div>
    </header>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <section class="card profile-card h-100" aria-labelledby="surfacing-details-heading">
                <div class="card-body">
                    <h3 id="surfacing-details-heading" class="section-title">Approved Surfacing Information</h3>
                    <dl class="detail-grid mb-0">
                        <div class="detail-item"><dt class="detail-label">First name</dt><dd class="detail-value mb-0">{{ $record->first_name }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Last name</dt><dd class="detail-value mb-0">{{ $record->last_name }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">FR category</dt><dd class="detail-value mb-0">{{ $record->category->value }}@if($record->other_category_specification) — {{ $record->other_category_specification }}@endif</dd></div>
                        <div class="detail-item"><dt class="detail-label">Date of surfacing</dt><dd class="detail-value mb-0">{{ $record->surfaced_at->format('F d, Y') }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Province</dt><dd class="detail-value mb-0">{{ $record->province }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Municipality</dt><dd class="detail-value mb-0">{{ $record->municipality->name }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Barangay</dt><dd class="detail-value mb-0">{{ $record->barangay?->name ?? 'Not provided' }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Specific location</dt><dd class="detail-value mb-0">{{ $record->specific_location ?: 'Not provided' }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Possessed firearms</dt><dd class="detail-value mb-0">{{ $record->possessed_firearms ? 'Yes' : 'No' }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">CDR status</dt><dd class="detail-value mb-0">{{ $record->cdr_status }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Overall case status</dt><dd class="detail-value mb-0">{{ $record->overall_case_status }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Created date</dt><dd class="detail-value mb-0">{{ $record->created_at->format('F d, Y · h:i A') }}</dd></div>
                        <div class="detail-item"><dt class="detail-label">Recorded by</dt><dd class="detail-value mb-0">{{ $recordedBy }}</dd></div>
                        <div class="detail-item full"><dt class="detail-label">Initial remarks</dt><dd class="detail-value mb-0">{{ $record->initial_remarks ?: 'No initial remarks recorded.' }}</dd></div>
                    </dl>
                </div>
            </section>
        </div>
        <div class="col-lg-5 mb-4">
            <section class="card profile-card h-100" aria-labelledby="workflow-heading">
                <div class="card-body">
                    <h3 id="workflow-heading" class="section-title">Related Workflows</h3>
                    <p class="privacy-note">Only the internal CDR status is linked to this record. No cross-agency records are inferred or matched.</p>
                    <div class="fallback-list mb-3">
                        <div class="fallback-item"><strong>CDR processing</strong><span>{{ $record->cdr_status }}</span></div>
                        @foreach (['JAPIC processing', 'PSWDO processing', 'Assistance records'] as $workflow)
                            <div class="fallback-item"><strong>{{ $workflow }}</strong><span>Not securely linked — unavailable</span></div>
                        @endforeach
                    </div>
                    <h4 class="section-title">FEA</h4>
                    <div class="fallback-list mb-3">
                        <div class="fallback-item"><strong>Process Status</strong><span>{{ $record->possessed_firearms ? 'Not Available' : 'Not Applicable' }}</span></div>
                        <div class="fallback-item"><strong>Documents</strong><span>{{ $record->possessed_firearms ? 'Not Available' : 'Not Applicable' }}</span></div>
                    </div>
                    <h4 class="section-title">Initial Status History</h4>
                    <div class="history-item"><strong>{{ $record->overall_case_status }}</strong><span>{{ $record->created_at->format('F d, Y · h:i A') }} · Recorded by {{ $recordedBy }}</span></div>
                </div>
            </section>
        </div>
    </div>

    @if($record->cdrProcessing)
        <a href="{{ route('ib39.cdr.show', $record->cdrProcessing) }}" class="btn btn-primary mr-2">Open CDR Workspace</a>
    @endif
    <a href="{{ route('ib39.fr-profiles.index') }}" class="btn btn-light"><i class="fa fa-arrow-left mr-1" aria-hidden="true"></i>Back to FR Profiles</a>
</div>
@endsection
