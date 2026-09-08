@extends('layouts.skydash-v')
@section('title', 'View FR Profile')
@section('heading', 'View FR Profile')

@push('styles')
<style>
    .fr-profile-container{max-width:1240px;margin:0 auto;padding-bottom:2rem}
    .module-nav-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem}
    .module-back-link{display:inline-flex;align-items:center;gap:.45rem;color:#4a5568;font-size:.84rem;font-weight:700;text-decoration:none!important;transition:color .15s ease}
    .module-back-link:hover{color:#401595}
    .module-back-link i{font-size:1.1rem}
    .module-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#718096;margin:0;padding:0;list-style:none}
    .module-breadcrumb li a{color:#718096;text-decoration:none;font-weight:600}
    .module-breadcrumb li a:hover{color:#401595}
    .module-breadcrumb li.active{color:#2d3748;font-weight:700}
    .module-breadcrumb .separator{color:#cbd5e0;font-size:.7rem}

    .profile-hero{background:linear-gradient(115deg,#152a4d 0%,#172f57 58%,#123c4d 100%);border-radius:15px;color:#fff;padding:1.35rem 1.6rem;margin-bottom:1.25rem;min-height:115px;box-shadow:0 8px 24px rgba(21,42,77,.12);position:relative;overflow:hidden}
    .hero-top-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;position:relative;z-index:1}
    .hero-main{display:flex;align-items:center;gap:1.1rem;min-width:0}
    .hero-icon-box{display:flex;align-items:center;justify-content:center;width:56px;height:56px;flex:0 0 56px;border-radius:14px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.12);color:#45e0ba;font-size:1.6rem}
    .hero-eyebrow{color:#ff9a62;font-size:.62rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
    .profile-hero h1{color:#fff;font-size:1.48rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.25rem;line-height:1.2}
    .profile-hero p{color:#bac7e5;font-size:.8rem;margin:0}
    .hero-badges{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
    .hero-ref-badge{display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:.5rem .85rem;color:#fff;font-size:.8rem;font-weight:750}
    .hero-ref-badge i{color:#45e0ba;font-size:1rem}
    .hero-status-pill{display:inline-flex;align-items:center;gap:.35rem;background:#401595;border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.5rem .85rem;color:#fff;font-size:.76rem;font-weight:700}

    .profile-card{background:#fff;border:1px solid #e4e4e7;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.03);overflow:hidden}
    .profile-card-header{display:flex;align-items:center;justify-content:space-between;padding:1.1rem 1.4rem;border-bottom:1px solid #edf1f6;background:#fafbfc}
    .profile-card-header h2,.profile-card-header h3{color:#1e293b;font-size:.95rem;font-weight:750;margin:0;display:flex;align-items:center;gap:.55rem}
    .profile-card-header h2 i,.profile-card-header h3 i{color:#401595;font-size:1.15rem}
    .profile-card-body{padding:1.4rem}

    .info-tile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}
    .info-tile{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.85rem 1rem;transition:border-color .15s ease}
    .info-tile:hover{border-color:#d4ddec}
    .info-tile.full-width{grid-column:1/-1}
    .info-tile-label{display:flex;align-items:center;gap:.35rem;color:#718096;font-size:.66rem;font-weight:750;letter-spacing:.04em;text-transform:uppercase;margin-bottom:.35rem}
    .info-tile-label i{font-size:.85rem;color:#8a97aa}
    .info-tile-value{color:#1e293b;font-size:.86rem;font-weight:650;line-height:1.4;margin:0;overflow-wrap:anywhere}

    .badge-pill-status{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700}
    .badge-pill-yes{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
    .badge-pill-no{background:#f1f5f9;border:1px solid #e2e8f0;color:#475569}
    .badge-pill-cdr{background:#f1ebfa;border:1px solid #d8c8f0;color:#401595}
    .badge-pill-case{background:#e0f2fe;border:1px solid #bae6fd;color:#0369a1}

    .privacy-box{display:flex;align-items:flex-start;gap:.75rem;background:#f4effa;border-left:4px solid #401595;border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem;color:#43286b;font-size:.78rem;line-height:1.45}
    .privacy-box i{color:#401595;font-size:1.1rem;margin-top:.1rem}

    .sub-section-title{display:flex;align-items:center;gap:.45rem;color:#334155;font-size:.86rem;font-weight:750;margin:1.4rem 0 .75rem;padding-bottom:.45rem;border-bottom:1px solid #edf1f6}
    .sub-section-title i{color:#401595;font-size:1rem}

    .workflow-tile-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .workflow-tile{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.8rem .95rem}
    .workflow-tile.active-flow{background:#faf7fd;border-color:#e4d7f5}
    .workflow-tile strong{display:block;color:#1e293b;font-size:.78rem;font-weight:750;margin-bottom:.25rem}
    .workflow-tile span{display:block;color:#718096;font-size:.72rem}

    .history-card-item{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.85rem 1rem;display:flex;align-items:flex-start;gap:.75rem}
    .history-card-item .history-icon{display:flex;align-items:center;justify-content:center;width:34px;height:34px;flex:0 0 34px;border-radius:8px;background:#f1ebfa;color:#401595;font-size:1.05rem}
    .history-card-item strong{display:block;color:#1e293b;font-size:.82rem;font-weight:750}
    .history-card-item span{display:block;color:#718096;font-size:.72rem;margin-top:.15rem}

    .profile-footer-actions{display:flex;align-items:center;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap}
    .btn-action-primary{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;background:#401595;border:1px solid #401595;color:#fff;font-size:.82rem;font-weight:750;padding:.65rem 1.35rem;border-radius:9px;box-shadow:0 4px 12px rgba(64,21,149,.2);text-decoration:none!important;transition:all .15s ease}
    .btn-action-primary:hover,.btn-action-primary:focus{background:#280274;border-color:#280274;color:#fff;box-shadow:0 6px 16px rgba(40,2,116,.25)}
    .btn-action-secondary{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;background:#fff;border:1px solid #d8e0ea;color:#475569;font-size:.82rem;font-weight:650;padding:.65rem 1.2rem;border-radius:9px;text-decoration:none!important;transition:all .15s ease}
    .btn-action-secondary:hover,.btn-action-secondary:focus{background:#f8fafc;color:#1e293b}

    @media(max-width:991px){.info-tile-grid,.workflow-tile-list{grid-template-columns:1fr}}
    @media(max-width:767px){.profile-hero{padding:1.2rem}.hero-top-row{flex-direction:column;align-items:flex-start}.hero-badges{width:100%}.profile-card-body{padding:1.1rem}.profile-footer-actions{flex-direction:column;width:100%}.btn-action-primary,.btn-action-secondary{width:100%}}
</style>
@endpush

@section('content')
<div class="fr-profile-container">
    @if($record->cancellation)
        <div class="alert alert-warning" role="status">
            <strong>Cancelled {{ $record->cancellation->cancelled_at->format('F d, Y h:i A') }}</strong>
            <div>Previous status: {{ $record->cancellation->previous_overall_status }}</div>
            <div>Reason: {{ $record->cancellation->reason }}</div>
        </div>
    @endif
    {{-- Top Navigation & History --}}
    <div class="module-nav-top">
        <a href="{{ route('ib39.fr-profiles.index') }}" class="module-back-link">
            <i class="mdi mdi-arrow-left"></i>
            <span>Back to FR Profiles</span>
        </a>
        <ol class="module-breadcrumb" aria-label="Breadcrumb">
            <li><a href="{{ route('ib39.dashboard') }}">Dashboard</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li><a href="{{ route('ib39.fr-profiles.index') }}">FR Profiles</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li class="active" aria-current="page">{{ $record->reference_number }}</li>
        </ol>
    </div>

    {{-- Modern Hero Banner --}}
    <header class="profile-hero mb-4">
        <div class="hero-top-row">
            <div class="hero-main">
                <div class="hero-icon-box">
                    <i class="mdi mdi-account-card-details" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="hero-eyebrow mb-1">Surfaced FR Profile</div>
                    <h1 class="mb-1">{{ $record->display_name }}</h1>
                    <p class="mb-0">Read-only surfaced former rebel profile</p>
                </div>
            </div>
            <div class="hero-badges">
                <div class="hero-ref-badge">
                    <i class="mdi mdi-shield-check"></i>
                    <span>{{ $record->reference_number }}</span>
                </div>
                <div class="hero-status-pill">
                    <i class="mdi mdi-check-circle-outline"></i>
                    <span>{{ $record->overall_case_status }}</span>
                </div>
            </div>
        </div>
    </header>

    <div class="row">
        {{-- Left Column: Approved Surfacing Information --}}
        <div class="col-lg-7 mb-4">
            <section class="profile-card h-100" aria-labelledby="surfacing-details-heading">
                <div class="profile-card-header">
                    <h2 id="surfacing-details-heading">
                        <i class="mdi mdi-account-card-details" aria-hidden="true"></i>
                        <span>Approved Surfacing Information</span>
                    </h2>
                </div>
                <div class="profile-card-body">
                    <dl class="info-tile-grid mb-0">
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-account"></i> First name</dt>
                            <dd class="info-tile-value">{{ $record->first_name }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-account"></i> Last name</dt>
                            <dd class="info-tile-value">{{ $record->last_name }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-tag-outline"></i> FR category</dt>
                            <dd class="info-tile-value">{{ $record->category->value }}@if($record->other_category_specification) — {{ $record->other_category_specification }}@endif</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-calendar"></i> Date of surfacing</dt>
                            <dd class="info-tile-value">{{ $record->surfaced_at->format('F d, Y') }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-map-marker-outline"></i> Province</dt>
                            <dd class="info-tile-value">{{ $record->province }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-city"></i> Municipality</dt>
                            <dd class="info-tile-value">{{ $record->municipality->name }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-home-map-marker"></i> Barangay</dt>
                            <dd class="info-tile-value">{{ $record->barangay?->name ?? 'Not provided' }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-crosshairs-gps"></i> Specific location</dt>
                            <dd class="info-tile-value">{{ $record->specific_location ?: 'Not provided' }}</dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-pistol"></i> Possessed firearms</dt>
                            <dd class="info-tile-value">
                                <span class="badge-pill-status {{ $record->possessed_firearms ? 'badge-pill-yes' : 'badge-pill-no' }}">
                                    {{ $record->possessed_firearms ? 'Yes' : 'No' }}
                                </span>
                            </dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-file-document-outline"></i> CDR status</dt>
                            <dd class="info-tile-value">
                                <span class="badge-pill-status badge-pill-cdr">{{ $record->cdr_status }}</span>
                            </dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-folder-outline"></i> Overall case status</dt>
                            <dd class="info-tile-value">
                                <span class="badge-pill-status badge-pill-case">{{ $record->overall_case_status }}</span>
                            </dd>
                        </div>
                        <div class="info-tile">
                            <dt class="info-tile-label"><i class="mdi mdi-clock-outline"></i> Created date</dt>
                            <dd class="info-tile-value">{{ $record->created_at->format('F d, Y · h:i A') }}</dd>
                        </div>
                        <div class="info-tile full-width">
                            <dt class="info-tile-label"><i class="mdi mdi-account-edit"></i> Recorded by</dt>
                            <dd class="info-tile-value">{{ $recordedBy }}</dd>
                        </div>
                        <div class="info-tile full-width">
                            <dt class="info-tile-label"><i class="mdi mdi-comment-text-outline"></i> Initial remarks</dt>
                            <dd class="info-tile-value">{{ $record->initial_remarks ?: 'No initial remarks recorded.' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>
        </div>

        {{-- Right Column: Related Workflows, FEA & History --}}
        <div class="col-lg-5 mb-4">
            <section class="profile-card h-100" aria-labelledby="workflow-heading">
                <div class="profile-card-header">
                    <h3 id="workflow-heading">
                        <i class="mdi mdi-lan-connect" aria-hidden="true"></i>
                        <span>Related Workflows</span>
                    </h3>
                </div>
                <div class="profile-card-body">
                    <div class="privacy-box">
                        <i class="mdi mdi-shield" aria-hidden="true"></i>
                        <div>
                            Only the internal CDR status is linked to this record. No cross-agency records are inferred or matched.
                        </div>
                    </div>

                    <div class="workflow-tile-list mb-3">
                        <div class="workflow-tile active-flow">
                            <strong>CDR processing</strong>
                            <span class="badge-pill-status badge-pill-cdr mt-1">{{ $record->cdr_status }}</span>
                        </div>
                        @foreach (['Assistance records'] as $workflow)
                            <div class="workflow-tile">
                                <strong>{{ $workflow }}</strong>
                                <span>Not securely linked — unavailable</span>
                            </div>
                        @endforeach
                    </div>

                    <h4 class="sub-section-title">
                        <i class="mdi mdi-file-check"></i>
                        <span>FEA</span>
                    </h4>
                    <div class="workflow-tile-list mb-3">
                        <div class="workflow-tile">
                            <strong>Process Status</strong>
                            <span>{{ $record->feaProcessing?->overallStatus()->value ?? ($record->possessed_firearms ? 'Not Available' : 'Not Applicable') }}</span>
                        </div>
                        <div class="workflow-tile">
                            <strong>Documents</strong>
                            <span>{{ $record->feaProcessing ? $record->feaProcessing->documents->count().' requirements' : ($record->possessed_firearms ? 'Not Available' : 'Not Applicable') }}</span>
                        </div>
                    </div>

                    <h4 class="sub-section-title">
                        <i class="mdi mdi-history"></i>
                        <span>Initial Status History</span>
                    </h4>
                    <div class="history-card-item">
                        <div class="history-icon">
                            <i class="mdi mdi-flag-checkered"></i>
                        </div>
                        <div>
                            <strong>{{ $record->overall_case_status }}</strong>
                            <span>{{ $record->created_at->format('F d, Y · h:i A') }} · Recorded by {{ $recordedBy }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Bottom Actions --}}
    <div class="profile-footer-actions">
        @if($record->cdrProcessing)
            <a href="{{ route('ib39.cdr.show', $record->cdrProcessing) }}" class="btn-action-primary">
                <i class="mdi mdi-file-document-edit-outline"></i>
                <span>Open CDR Workspace</span>
            </a>
        @endif
        @if($record->feaProcessing)
            <a href="{{ route('ib39.fea.show', $record->feaProcessing) }}" class="btn-action-secondary">
                <i class="mdi mdi-file-document-outline"></i>
                <span>View FEA Record</span>
            </a>
        @endif
        <a href="{{ route('ib39.fr-profiles.index') }}" class="btn-action-secondary">
            <i class="mdi mdi-arrow-left"></i>
            <span>Back to FR Profiles</span>
        </a>
        @can('cancel', $record)
            <form method="POST" action="{{ route('ib39.fr-profiles.cancel', $record) }}" class="card card-body mt-3 w-100">
                @csrf
                <label for="cancellation-reason" class="form-label font-weight-bold">Cancellation reason</label>
                <textarea id="cancellation-reason" name="reason" class="form-control" rows="3" maxlength="2000" required>{{ old('reason') }}</textarea>
                <div class="form-check mt-2">
                    <input id="cancellation-confirmed" name="confirmed" value="1" class="form-check-input" type="checkbox" required>
                    <label class="form-check-label" for="cancellation-confirmed">I confirm that I am cancelling the correct surfaced FR.</label>
                </div>
                <button type="submit" class="btn btn-danger mt-3 align-self-start">Cancel FR</button>
            </form>
        @endcan
    </div>
</div>
@endsection
