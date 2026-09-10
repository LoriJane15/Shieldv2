@props([
    'record',
    'informationHeading' => 'Approved Surfacing Information',
    'showStatusTiles' => true,
    'showWorkflowSummary' => true,
])

@once
@push('styles')
<style>
    .fr-profile-container{max-width:1240px;margin:0 auto;padding-bottom:2rem}.module-nav-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem}.module-back-link{display:inline-flex;align-items:center;gap:.45rem;color:#4a5568;font-size:.84rem;font-weight:700;text-decoration:none!important}.module-back-link:hover{color:#401595}.module-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#718096;margin:0;padding:0;list-style:none}.module-breadcrumb a{color:#718096;text-decoration:none;font-weight:600}.module-breadcrumb .active{color:#2d3748;font-weight:700}.module-breadcrumb .separator{color:#cbd5e0;font-size:.7rem}
    .profile-hero{background:linear-gradient(115deg,#152a4d 0%,#172f57 58%,#123c4d 100%);border-radius:15px;color:#fff;padding:1.35rem 1.6rem;margin-bottom:1.25rem;min-height:115px;box-shadow:0 8px 24px rgba(21,42,77,.12)}.hero-top-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}.hero-main{display:flex;align-items:center;gap:1.1rem;min-width:0}.hero-icon-box{display:flex;align-items:center;justify-content:center;width:56px;height:56px;flex:0 0 56px;border-radius:14px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.12);color:#45e0ba;font-size:1.6rem}.hero-eyebrow{color:#ff9a62;font-size:.62rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.profile-hero h1{color:#fff;font-size:1.48rem;font-weight:800;margin-bottom:.25rem;line-height:1.2}.profile-hero p{color:#bac7e5;font-size:.8rem;margin:0}.hero-badges{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}.hero-ref-badge,.hero-status-pill{display:inline-flex;align-items:center;gap:.45rem;border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:.5rem .85rem;color:#fff;font-size:.78rem;font-weight:700}.hero-ref-badge{background:rgba(255,255,255,.08)}.hero-status-pill{background:#401595}
    .profile-card{background:#fff;border:1px solid #e4e4e7;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.03);overflow:hidden}.profile-card-header{display:flex;align-items:center;justify-content:space-between;padding:1.1rem 1.4rem;border-bottom:1px solid #edf1f6;background:#fafbfc}.profile-card-header h2,.profile-card-header h3{color:#1e293b;font-size:.95rem;font-weight:750;margin:0;display:flex;align-items:center;gap:.55rem}.profile-card-header i{color:#401595;font-size:1.15rem}.profile-card-body{padding:1.4rem}.info-tile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}.info-tile{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.85rem 1rem}.info-tile.full-width{grid-column:1/-1}.info-tile-label{display:flex;align-items:center;gap:.35rem;color:#718096;font-size:.66rem;font-weight:750;letter-spacing:.04em;text-transform:uppercase;margin-bottom:.35rem}.info-tile-value{color:#1e293b;font-size:.86rem;font-weight:650;line-height:1.4;margin:0;overflow-wrap:anywhere}.badge-pill-status{display:inline-flex;align-items:center;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700}.badge-pill-yes{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}.badge-pill-no{background:#f1f5f9;border:1px solid #e2e8f0;color:#475569}.badge-pill-cdr{background:#f1ebfa;border:1px solid #d8c8f0;color:#401595}.badge-pill-case{background:#e0f2fe;border:1px solid #bae6fd;color:#0369a1}.privacy-box{display:flex;gap:.75rem;background:#f4effa;border-left:4px solid #401595;border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem;color:#43286b;font-size:.78rem}.workflow-tile-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.workflow-tile{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.8rem .95rem}.workflow-tile strong,.workflow-tile span{display:block}.workflow-tile strong{font-size:.78rem;color:#1e293b}.workflow-tile span{font-size:.72rem;color:#718096}.sub-section-title{display:flex;align-items:center;gap:.45rem;color:#334155;font-size:.86rem;font-weight:750;margin:1.4rem 0 .75rem;padding-bottom:.45rem;border-bottom:1px solid #edf1f6}.profile-footer-actions{display:flex;align-items:center;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap}.btn-action-primary,.btn-action-secondary{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;font-size:.82rem;font-weight:700;padding:.65rem 1.2rem;border-radius:9px;text-decoration:none!important}.btn-action-primary{background:#401595;border:1px solid #401595;color:#fff}.btn-action-secondary{background:#fff;border:1px solid #d8e0ea;color:#475569}.history-card-item{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.85rem 1rem;display:flex;gap:.75rem}.history-icon{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:#f1ebfa;color:#401595}
    @media(max-width:991px){.info-tile-grid,.workflow-tile-list{grid-template-columns:1fr}}@media(max-width:767px){.profile-hero{padding:1.2rem}.hero-top-row{flex-direction:column;align-items:flex-start}.profile-card-body{padding:1.1rem}.profile-footer-actions{flex-direction:column}.btn-action-primary,.btn-action-secondary{width:100%}}
</style>
@endpush
@endonce

@if($record->cancellation)
    <div class="alert alert-warning" role="status">
        <strong>Cancelled {{ $record->cancellation->cancelled_at->format('F d, Y h:i A') }}</strong>
        <div>Previous status: {{ $record->cancellation->previous_overall_status }}</div>
        <div>Reason: {{ $record->cancellation->reason }}</div>
    </div>
@endif

<header class="profile-hero">
    <div class="hero-top-row">
        <div class="hero-main"><div class="hero-icon-box"><i class="mdi mdi-account-card-details" aria-hidden="true"></i></div><div>
            <div class="hero-eyebrow mb-1">Surfaced FR Profile</div><h1>{{ $record->display_name }}</h1><p>Read-only surfaced former rebel profile</p>
        </div></div>
        <div class="hero-badges"><div class="hero-ref-badge"><i class="mdi mdi-shield-check"></i>{{ $record->reference_number }}</div>@if($showStatusTiles)<div class="hero-status-pill">{{ $record->overall_case_status }}</div>@endif</div>
    </div>
</header>

<div class="row">
    <div class="{{ $showWorkflowSummary ? 'col-lg-7' : 'col-12' }} mb-4"><section class="profile-card h-100" aria-labelledby="surfacing-details-heading"><div class="profile-card-header"><h2 id="surfacing-details-heading"><i class="mdi mdi-account-card-details"></i>{{ $informationHeading }}</h2></div><div class="profile-card-body"><dl class="info-tile-grid mb-0">
        @foreach([
            ['First name',$record->first_name],['Last name',$record->last_name],
            ['FR category',$record->category->value.($record->other_category_specification ? ' — '.$record->other_category_specification : '')],
            ['Date of surfacing',$record->surfaced_at->format('F d, Y')],['Province',$record->province],
            ['Municipality',$record->municipality?->name ?? 'Not provided'],['Barangay',$record->barangay?->name ?? 'Not provided'],
            ['Specific location',$record->specific_location ?: 'Not provided'],
        ] as [$label,$value])
            <div class="info-tile"><dt class="info-tile-label">{{ $label }}</dt><dd class="info-tile-value">{{ $value }}</dd></div>
        @endforeach
        <div class="info-tile"><dt class="info-tile-label">Possessed firearms</dt><dd class="info-tile-value"><span class="badge-pill-status {{ $record->possessed_firearms ? 'badge-pill-yes' : 'badge-pill-no' }}">{{ $record->possessed_firearms ? 'Yes' : 'No' }}</span></dd></div>
        @if($showStatusTiles)
            <div class="info-tile"><dt class="info-tile-label">CDR status</dt><dd class="info-tile-value"><span class="badge-pill-status badge-pill-cdr">{{ $record->cdr_status }}</span></dd></div>
            <div class="info-tile"><dt class="info-tile-label">Overall case status</dt><dd class="info-tile-value"><span class="badge-pill-status badge-pill-case">{{ $record->overall_case_status }}</span></dd></div>
        @endif
        <div class="info-tile"><dt class="info-tile-label">Created date</dt><dd class="info-tile-value">{{ $record->created_at->format('F d, Y · h:i A') }}</dd></div>
        <div class="info-tile full-width"><dt class="info-tile-label">Initial remarks</dt><dd class="info-tile-value">{{ $record->initial_remarks ?: 'No initial remarks recorded.' }}</dd></div>
    </dl></div></section></div>
    @if($showWorkflowSummary)<div class="col-lg-5 mb-4"><section class="profile-card h-100" aria-labelledby="workflow-heading"><div class="profile-card-header"><h3 id="workflow-heading"><i class="mdi mdi-lan-connect"></i>Related Workflows</h3></div><div class="profile-card-body">
        <div class="privacy-box"><i class="mdi mdi-shield"></i><div>Only the internal CDR status is linked to this record. No cross-agency records are inferred or matched.</div></div>
        <div class="workflow-tile-list"><div class="workflow-tile"><strong>CDR processing</strong><span>{{ $record->cdr_status }}</span></div><div class="workflow-tile"><strong>Assistance records</strong><span>Not securely linked — unavailable</span></div></div>
        <h4 class="sub-section-title"><i class="mdi mdi-file-check"></i>FEA</h4><div class="workflow-tile-list"><div class="workflow-tile"><strong>Process Status</strong><span>{{ $record->feaProcessing?->overallStatus()->value ?? ($record->possessed_firearms ? 'Not Available' : 'Not Applicable') }}</span></div><div class="workflow-tile"><strong>Documents</strong><span>{{ $record->feaProcessing ? $record->feaProcessing->documents->count().' requirements' : ($record->possessed_firearms ? 'Not Available' : 'Not Applicable') }}</span></div></div>
    </div></section></div>@endif
</div>
