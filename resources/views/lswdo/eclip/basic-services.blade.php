@extends('layouts.skydash-v')
@section('title', 'E-CLIP Basic Services')
@section('heading', 'Basic Services Monitoring')

@push('styles')
<style>
    .services-page { --service-blue: #2f6fed; --service-purple: #6246c9; --service-navy: #172b4d; --service-border: #dfe6f0; color: #334155; font-size: .94rem; }
    .services-page button:focus-visible, .services-page a:focus-visible, .services-page input:focus-visible, .services-page select:focus-visible, .services-page textarea:focus-visible { box-shadow: 0 0 0 3px rgba(47, 111, 237, .22) !important; outline: 2px solid transparent; }
    .services-back { align-items: center; color: #5f6f85; display: inline-flex; font-size: .9rem; font-weight: 600; gap: .45rem; margin-bottom: 1rem; }
    .services-back:hover { color: var(--service-blue); text-decoration: none; }
    .services-hero { background: linear-gradient(125deg, #173b74, #2f6fed); border-radius: 14px; box-shadow: 0 8px 22px rgba(47, 111, 237, .14); color: #fff; overflow: hidden; padding: 1.45rem 1.6rem; position: relative; }
    .services-hero::after { background: rgba(255, 255, 255, .08); border-radius: 50%; content: ''; height: 190px; position: absolute; right: -50px; top: -95px; width: 190px; }
    .services-eyebrow { font-size: .76rem; font-weight: 700; letter-spacing: .09em; opacity: .82; text-transform: uppercase; }
    .services-hero h2 { color: #fff; font-size: 1.65rem; font-weight: 700; margin: .2rem 0 1rem; }
    .case-meta { display: flex; flex-wrap: wrap; gap: .75rem 1.6rem; }
    .case-meta-item { align-items: center; display: flex; gap: .55rem; min-width: 145px; }
    .case-meta-item > i { align-items: center; background: rgba(255, 255, 255, .11); border-radius: 7px; display: inline-flex; flex: 0 0 30px; height: 30px; justify-content: center; width: 30px; }
    .case-meta-label { display: block; font-size: .68rem; opacity: .72; text-transform: uppercase; }
    .case-meta-value { display: block; font-size: .86rem; font-weight: 600; }
    .scope-notice { align-items: center; background: #eef5ff; border: 1px solid #ccddfb; border-radius: 10px; color: #36537c; display: flex; font-size: .88rem; gap: .65rem; padding: .75rem 1rem; }
    .scope-notice i { color: var(--service-blue); font-size: 1.15rem; }
    .summary-card, .service-card, .add-service-card { border: 1px solid var(--service-border); border-radius: 12px; box-shadow: 0 3px 12px rgba(23, 43, 77, .04); }
    .summary-card .card-body { align-items: center; display: flex; gap: .85rem; padding: .9rem 1rem; }
    .summary-icon { align-items: center; background: var(--summary-bg); border-radius: 9px; color: var(--summary-color); display: flex; flex: 0 0 40px; font-size: 1.15rem; height: 40px; justify-content: center; width: 40px; }
    .summary-card strong { color: var(--service-navy); display: block; font-size: 1.45rem; line-height: 1.05; }
    .summary-card span { color: #64748b; font-size: .75rem; font-weight: 700; letter-spacing: .025em; text-transform: uppercase; }
    .summary-total { --summary-bg: #eaf1ff; --summary-color: #2f6fed; }
    .summary-active { --summary-bg: #fff4d6; --summary-color: #9a6800; }
    .summary-complete { --summary-bg: #e6f7ef; --summary-color: #16845e; }
    .summary-overdue { --summary-bg: #ffebed; --summary-color: #c74650; }
    .attention-line { color: #52657d; font-size: .88rem; margin: -.25rem 0 1.25rem; }
    .attention-line strong { color: var(--service-navy); }
    .services-section-head { align-items: center; display: flex; gap: 1rem; justify-content: space-between; margin-bottom: .85rem; }
    .section-title { color: var(--service-navy); font-size: 1.08rem; font-weight: 700; margin: 0 0 .15rem; }
    .section-subtitle { color: #718096; font-size: .82rem; margin: 0; }
    .add-service-card { display: none; overflow: hidden; }
    .add-service-card.is-open { display: block; }
    .add-service-card .card-header { align-items: center; background: #f8fafc; border-bottom: 1px solid #e8edf4; display: flex; justify-content: space-between; padding: 1rem 1.2rem; }
    .add-service-card .card-body { padding: 1.2rem; }
    .field-label { color: #3d4f67; font-size: .84rem; font-weight: 600; }
    .field-label .optional { color: #8492a6; font-size: .76rem; font-weight: 400; }
    .services-page .form-control { border-color: #d5deea; border-radius: 8px; font-size: .9rem; min-height: 42px; }
    .services-page textarea.form-control { min-height: 76px; }
    .services-page .btn { align-items: center; border-radius: 8px; display: inline-flex; font-size: .88rem; font-weight: 600; gap: .35rem; justify-content: center; min-height: 40px; padding-left: .9rem; padding-right: .9rem; }
    .services-page .btn-primary { background: var(--service-purple); border-color: var(--service-purple); }
    .service-card { overflow: hidden; }
    .service-card.is-completed { box-shadow: none; }
    .service-card.is-overdue { border-left: 3px solid #d95a64; }
    .service-card-head { align-items: center; background: #fff; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; padding: 1rem 1.15rem .75rem; }
    .service-title-wrap { align-items: center; display: flex; gap: .75rem; min-width: 0; }
    .service-type-icon { align-items: center; background: #edf3ff; border-radius: 9px; color: var(--service-blue); display: flex; flex: 0 0 40px; font-size: 1.1rem; height: 40px; justify-content: center; width: 40px; }
    .service-card.is-completed .service-type-icon { background: #edf8f3; color: #16845e; }
    .service-title { color: #263a59; font-size: 1rem; font-weight: 700; margin: 0; }
    .service-agency { color: #718096; font-size: .8rem; margin-top: .15rem; }
    .service-status { align-items: center; border-radius: 999px; display: inline-flex; font-size: .73rem; font-weight: 700; gap: .25rem; padding: .35rem .65rem; text-transform: uppercase; }
    .service-status-pending { background: #fff4d6; color: #876000; }
    .service-status-referred, .service-status-in_progress { background: #eaf1ff; color: #245cc4; }
    .service-status-completed { background: #e6f7ef; color: #147553; }
    .service-status-not_applicable { background: #f0ecfa; color: #654aa8; }
    .service-status-overdue { background: #ffebed; color: #b63f49; }
    .service-overview { display: grid; gap: .8rem; grid-template-columns: repeat(5, minmax(0, 1fr)); padding: .2rem 1.15rem .95rem; }
    .metadata-label { color: #7a889b; display: block; font-size: .7rem; font-weight: 700; letter-spacing: .025em; margin-bottom: .18rem; text-transform: uppercase; }
    .metadata-value { color: #30445f; display: block; font-size: .86rem; font-weight: 600; overflow-wrap: anywhere; }
    .target-note { display: block; font-size: .74rem; font-weight: 600; margin-top: .15rem; }
    .target-note.overdue { color: #b63f49; }
    .target-note.upcoming { color: #526f9c; }
    .service-actions { align-items: center; border-top: 1px solid #edf1f6; display: flex; gap: .5rem; justify-content: flex-end; padding: .7rem 1.15rem; }
    .service-panel { border-top: 1px solid #e6ebf2; display: none; padding: 1.15rem; }
    .service-panel.is-open { display: block; }
    .details-grid { display: grid; gap: 1rem 1.25rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .details-wide { grid-column: 1 / -1; }
    .remarks-copy { color: #52657d; font-size: .86rem; line-height: 1.55; margin: 0; white-space: pre-line; }
    .evidence-section { background: #f8fafc; border: 1px solid #e3e9f1; border-radius: 10px; margin-top: 1.1rem; padding: 1rem; }
    .evidence-head { align-items: center; display: flex; justify-content: space-between; margin-bottom: .75rem; }
    .evidence-head h5 { color: #30445f; font-size: .92rem; font-weight: 700; margin: 0; }
    .evidence-count { color: #718096; font-size: .78rem; }
    .evidence-document { align-items: center; background: #fff; border: 1px solid #dfe6ef; border-radius: 9px; display: flex; gap: .75rem; justify-content: space-between; padding: .75rem; }
    .evidence-document + .evidence-document { margin-top: .55rem; }
    .evidence-file { align-items: center; display: flex; gap: .65rem; min-width: 0; }
    .evidence-file-icon { align-items: center; background: #edf3ff; border-radius: 8px; color: var(--service-blue); display: flex; flex: 0 0 36px; height: 36px; justify-content: center; width: 36px; }
    .evidence-name { color: #30445f; display: block; font-size: .84rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .evidence-meta { color: #718096; display: block; font-size: .73rem; margin-top: .12rem; }
    .latest-badge { background: #e6f7ef; border-radius: 999px; color: #147553; font-size: .65rem; font-weight: 700; margin-left: .25rem; padding: .15rem .4rem; text-transform: uppercase; }
    .evidence-upload { align-items: center; background: #fff; border: 1px dashed #aebbd0; border-radius: 9px; display: flex; gap: .75rem; justify-content: space-between; margin-top: .75rem; padding: .75rem; }
    .evidence-upload input { max-width: 100%; }
    .activity-list { border-top: 1px solid #e8edf4; margin-top: 1rem; padding-top: 1rem; }
    .activity-item { align-items: flex-start; color: #52657d; display: flex; font-size: .78rem; gap: .5rem; margin-top: .45rem; }
    .activity-item i { color: #8091a8; margin-top: .1rem; }
    .empty-services { background: #fff; border: 1px dashed #bdc9d9; border-radius: 12px; color: #718096; padding: 2.5rem 1rem; text-align: center; }
    .empty-services > i { color: #9eacbf; display: block; font-size: 2.25rem; margin-bottom: .5rem; }
    [hidden] { display: none !important; }
    @media (max-width: 991px) { .service-overview { grid-template-columns: repeat(3, minmax(0, 1fr)); } .details-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) { .services-hero { padding: 1.2rem; } .services-hero h2 { font-size: 1.4rem; } .case-meta { flex-direction: column; gap: .55rem; } .services-section-head { align-items: flex-start; } .service-overview, .details-grid { grid-template-columns: 1fr 1fr; } .service-card-head { align-items: flex-start; } .service-actions { flex-wrap: wrap; } .service-actions .btn { flex: 1 1 130px; } .evidence-upload, .evidence-document { align-items: stretch; flex-direction: column; } .evidence-document .btn, .evidence-upload .btn { width: 100%; } }
    @media (max-width: 479px) { .service-overview, .details-grid { grid-template-columns: 1fr; } .details-wide { grid-column: auto; } .services-section-head { flex-direction: column; } .services-section-head .btn { width: 100%; } }
</style>
@endpush

@section('content')
@php
    $services = $case->basicServices;
    $completedCount = $services->where('status', 'completed')->count();
    $activeCount = $services->whereIn('status', ['pending', 'referred', 'in_progress'])->count();
    $overdueCount = $services->filter->isOverdue()->count();
    $lastUpdated = $services->max('updated_at') ?? $case->updated_at;
    $hasCreateErrors = $errors->hasAny(['service_type', 'gov_agency_id', 'status', 'referral_date', 'target_completion_date', 'remarks']);
    $serviceIcons = ['health' => 'mdi-medical-bag', 'education' => 'mdi-school-outline', 'housing' => 'mdi-home-outline', 'legal' => 'mdi-scale-balance', 'psychosocial' => 'mdi-head-heart-outline', 'livelihood_preparation' => 'mdi-briefcase-outline', 'government_registration' => 'mdi-card-account-details-outline', 'employment' => 'mdi-account-hard-hat'];
    $statusOptions = ['pending' => 'Pending', 'referred' => 'Referred', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'not_applicable' => 'Not Applicable'];
@endphp

<div class="services-page" data-services-page>
    <a href="{{ route('lswdo.eclip.show', $case) }}" class="services-back"><i class="mdi mdi-arrow-left" aria-hidden="true"></i> Back to eligibility review</a>

    <section class="services-hero mb-3" aria-labelledby="services-title">
        <div class="shield-hero-primary position-relative" style="z-index: 1;">
            <span class="shield-title-icon"><i class="mdi mdi-heart-pulse" aria-hidden="true"></i></span>
            <div><div class="services-eyebrow">E-CLIP Case {{ $case->case_number }}</div>
            <h2 id="services-title">Basic Services Monitoring</h2>
            <div class="case-meta">
                <div class="case-meta-item"><i class="mdi mdi-account-key-outline" aria-hidden="true"></i><span><span class="case-meta-label">Beneficiary</span><span class="case-meta-value">{{ $case->formerRebel->classified_id }}</span></span></div>
                <div class="case-meta-item"><i class="mdi mdi-folder-check-outline" aria-hidden="true"></i><span><span class="case-meta-label">Recorded Services</span><span class="case-meta-value">{{ $services->count() }}</span></span></div>
                <div class="case-meta-item"><i class="mdi mdi-heart-pulse" aria-hidden="true"></i><span><span class="case-meta-label">Overall Service Status</span><span class="case-meta-value">{{ $services->isEmpty() ? 'Not started' : ($activeCount === 0 ? 'All services resolved' : $activeCount.' active') }}</span></span></div>
                @if($lastUpdated)<div class="case-meta-item"><i class="mdi mdi-update" aria-hidden="true"></i><span><span class="case-meta-label">Last Updated</span><span class="case-meta-value">{{ $lastUpdated->format('M d, Y · g:i A') }}</span></span></div>@endif
            </div>
            </div>
        </div>
    </section>

    <div class="scope-notice mb-4" role="note"><i class="mdi mdi-information-outline" aria-hidden="true"></i><span>Basic service completion is monitored separately from E-CLIP eligibility.</span></div>

    <div class="row">
        @foreach([
            ['summary-total', 'mdi-format-list-checks', $services->count(), 'Total services'],
            ['summary-active', 'mdi-progress-clock', $activeCount, 'Active'],
            ['summary-complete', 'mdi-check-circle-outline', $completedCount, 'Completed'],
            ['summary-overdue', 'mdi-clock-alert-outline', $overdueCount, 'Overdue'],
        ] as [$class, $icon, $count, $label])
            <div class="col-6 col-xl-3 mb-3"><div class="card summary-card {{ $class }} h-100"><div class="card-body"><div class="summary-icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></div><div><strong>{{ $count }}</strong><span>{{ $label }}</span></div></div></div></div>
        @endforeach
    </div>

    @if($overdueCount > 0)
        <p class="attention-line"><strong>{{ $overdueCount }} {{ $overdueCount === 1 ? 'service has' : 'services have' }} passed the target completion date.</strong> Review the highlighted {{ Str::plural('record', $overdueCount) }}.</p>
    @elseif($activeCount > 0)
        <p class="attention-line"><strong>{{ $activeCount }} {{ $activeCount === 1 ? 'service requires' : 'services require' }} attention.</strong> Review target dates and partner-agency updates below.</p>
    @elseif($services->isNotEmpty())
        <p class="attention-line"><strong>All recorded basic services are currently resolved.</strong></p>
    @endif

    <div class="services-section-head">
        <div><h3 class="section-title">Basic Services</h3><p class="section-subtitle">{{ $services->count() }} service {{ Str::plural('record', $services->count()) }} for this beneficiary</p></div>
        <button type="button" class="btn btn-primary" data-add-toggle aria-controls="add-service-panel" aria-expanded="{{ $hasCreateErrors ? 'true' : 'false' }}"><i class="mdi mdi-plus" aria-hidden="true"></i> Add Service</button>
    </div>

    <section id="add-service-panel" class="card add-service-card mb-4 {{ $hasCreateErrors ? 'is-open' : '' }}" aria-labelledby="add-service-title">
        <div class="card-header"><div><h3 id="add-service-title" class="section-title">Add Service Need / Referral</h3><p class="section-subtitle">Record a service requirement and partner agency when available.</p></div><button type="button" class="btn btn-sm btn-light" data-add-cancel aria-label="Close add service form"><i class="mdi mdi-close" aria-hidden="true"></i></button></div>
        <div class="card-body">
            <form method="POST" action="{{ route('lswdo.eclip.basic-services.store', $case) }}" data-service-form>
                @csrf
                <div class="row">
                    <div class="col-lg-4 form-group"><label for="service_type" class="field-label">Service Type <span class="text-danger" aria-hidden="true">*</span></label><select id="service_type" name="service_type" class="form-control @error('service_type') is-invalid @enderror" required aria-describedby="service_type_error"><option value="">Select service type</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(old('service_type') === $value)>{{ $label }}</option>@endforeach</select>@error('service_type')<div id="service_type_error" class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-lg-4 form-group"><label for="gov_agency_id" class="field-label">Partner Agency <span class="optional">(optional)</span></label><select id="gov_agency_id" name="gov_agency_id" class="form-control @error('gov_agency_id') is-invalid @enderror"><option value="">Not assigned</option>@foreach($agencies as $agency)<option value="{{ $agency->id }}" @selected((string) old('gov_agency_id') === (string) $agency->id)>{{ $agency->acronym ?: $agency->name }}</option>@endforeach</select>@error('gov_agency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-lg-4 form-group"><label for="status" class="field-label">Initial Status <span class="text-danger" aria-hidden="true">*</span></label><select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(old('status', 'pending') === $value)>{{ $label }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4 form-group"><label for="referral_date" class="field-label">Referral Date <span class="optional">(optional)</span></label><input id="referral_date" type="date" name="referral_date" value="{{ old('referral_date') }}" class="form-control @error('referral_date') is-invalid @enderror">@error('referral_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4 form-group"><label for="target_completion_date" class="field-label">Target Completion <span class="optional">(optional)</span></label><input id="target_completion_date" type="date" name="target_completion_date" value="{{ old('target_completion_date') }}" class="form-control @error('target_completion_date') is-invalid @enderror">@error('target_completion_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4 form-group"><label for="remarks" class="field-label">Remarks <span class="optional">(optional)</span></label><textarea id="remarks" name="remarks" rows="2" maxlength="5000" class="form-control @error('remarks') is-invalid @enderror" placeholder="Enter referral notes...">{{ old('remarks') }}</textarea>@error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="d-flex flex-wrap justify-content-end" style="gap: .5rem;"><button type="button" class="btn btn-light" data-add-cancel>Cancel</button><button class="btn btn-primary" data-submit-button><i class="mdi mdi-plus" aria-hidden="true"></i> Add Service</button></div>
            </form>
        </div>
    </section>

    <div class="mb-3"><h3 class="section-title">Recorded Services</h3><p class="section-subtitle">Open one record to view evidence or edit its monitoring details.</p></div>

    @forelse($services as $service)
        @php
            $isOverdue = $service->isOverdue();
            $dateDifference = $service->targetDateDifferenceInDays();
            $serviceLabel = $types[$service->service_type] ?? str($service->service_type)->headline();
            $latestDocument = $service->documents->first();
        @endphp
        <article class="card service-card {{ $service->status === 'completed' ? 'is-completed' : '' }} {{ $isOverdue ? 'is-overdue' : '' }} mb-3" aria-labelledby="service-{{ $service->id }}-title" data-service-card>
            <div class="service-card-head">
                <div class="service-title-wrap"><div class="service-type-icon"><i class="mdi {{ $serviceIcons[$service->service_type] ?? 'mdi-hand-heart-outline' }}" aria-hidden="true"></i></div><div><h4 id="service-{{ $service->id }}-title" class="service-title">{{ $serviceLabel }}</h4><div class="service-agency">{{ $service->agency ? 'Partner Agency: '.($service->agency->acronym ?: $service->agency->name) : 'No partner agency assigned' }}</div></div></div>
                <x-eclip.service-status-badge :status="$service->status" :overdue="$isOverdue" />
            </div>
            <div class="service-overview">
                <div><span class="metadata-label">Partner Agency</span><span class="metadata-value">{{ $service->agency?->acronym ?: ($service->agency?->name ?? 'Not assigned') }}</span></div>
                <div><span class="metadata-label">Referral</span><span class="metadata-value">{{ $service->referral_date?->format('M d, Y') ?? 'Not recorded' }}</span></div>
                <div><span class="metadata-label">Target</span><span class="metadata-value">{{ $service->target_completion_date?->format('M d, Y') ?? 'Not set' }}</span>@if($dateDifference !== null)<span class="target-note {{ $dateDifference < 0 ? 'overdue' : 'upcoming' }}">{{ $dateDifference < 0 ? abs($dateDifference).' '.Str::plural('day', abs($dateDifference)).' overdue' : ($dateDifference === 0 ? 'Due today' : $dateDifference.' '.Str::plural('day', $dateDifference).' remaining') }}</span>@endif</div>
                <div><span class="metadata-label">Evidence</span><span class="metadata-value">{{ $service->documents->count() }} {{ Str::plural('file', $service->documents->count()) }}</span></div>
                <div><span class="metadata-label">Last Updated</span><span class="metadata-value">{{ $service->updated_at->format('M d, Y') }}</span><span class="evidence-meta">{{ $service->updated_at->format('g:i A') }}</span></div>
            </div>
            <div class="service-actions">
                <button type="button" class="btn btn-sm btn-outline-primary" data-panel-toggle="details-{{ $service->id }}" aria-controls="details-{{ $service->id }}" aria-expanded="false"><i class="mdi mdi-eye-outline" aria-hidden="true"></i> View Details</button>
                @can('update', $service)<button type="button" class="btn btn-sm btn-primary" data-panel-toggle="edit-{{ $service->id }}" aria-controls="edit-{{ $service->id }}" aria-expanded="false"><i class="mdi mdi-pencil-outline" aria-hidden="true"></i> Edit</button>@endcan
            </div>

            <section id="details-{{ $service->id }}" class="service-panel" aria-label="{{ $serviceLabel }} details">
                <div class="details-grid">
                    <div><span class="metadata-label">Service Type</span><span class="metadata-value">{{ $serviceLabel }}</span></div>
                    <div><span class="metadata-label">Partner Agency</span><span class="metadata-value">{{ $service->agency?->name ?? 'Not assigned' }}</span></div>
                    <div><span class="metadata-label">Status</span><span class="metadata-value">{{ $statusOptions[$service->status] ?? str($service->status)->headline() }}</span></div>
                    <div><span class="metadata-label">Last Updated By</span><span class="metadata-value">{{ $service->updater?->name ?? 'Not available' }}</span></div>
                    <div class="details-wide"><span class="metadata-label">Remarks</span><p class="remarks-copy">{{ filled($service->remarks) ? $service->remarks : 'No remarks recorded.' }}</p></div>
                </div>

                <div class="evidence-section">
                    <div class="evidence-head"><h5>Supporting Evidence</h5><span class="evidence-count">{{ $service->documents->count() }} {{ Str::plural('file', $service->documents->count()) }}</span></div>
                    @forelse($service->documents as $document)
                        <div class="evidence-document"><div class="evidence-file"><span class="evidence-file-icon"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i></span><span style="min-width: 0;"><span class="evidence-name" title="{{ $document->original_name }}">{{ $document->original_name }}</span><span class="evidence-meta">Version {{ $document->version_number }} @if($loop->first)<span class="latest-badge">Latest</span>@else · Previous @endif · Uploaded {{ $document->created_at->format('M d, Y · g:i A') }}@if($document->uploader) · by {{ $document->uploader->name }}@endif</span></span></div><a class="btn btn-sm btn-outline-primary" href="{{ route('lswdo.eclip.basic-services.documents.preview', $document) }}"><i class="mdi mdi-eye-outline" aria-hidden="true"></i> View</a></div>
                    @empty
                        <p class="remarks-copy">No supporting evidence uploaded.</p>
                    @endforelse

                    @can('uploadDocument', $service)
                        <form method="POST" action="{{ route('lswdo.eclip.basic-services.documents.store', $service) }}" enctype="multipart/form-data" class="evidence-upload" data-evidence-upload>
                            @csrf
                            <div><label for="service-file-{{ $service->id }}" class="field-label mb-1">{{ $latestDocument ? 'Upload New Version' : 'Upload Supporting Evidence' }}</label><input id="service-file-{{ $service->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required data-evidence-input><small class="evidence-meta" data-evidence-name>PDF, JPG, or PNG · Maximum 10 MB</small></div>
                            <button class="btn btn-sm btn-outline-primary" data-upload-button><i class="mdi mdi-upload" aria-hidden="true"></i> {{ $latestDocument ? 'Upload New Version' : 'Upload Evidence' }}</button>
                        </form>
                    @endcan
                </div>

                @if($service->histories->isNotEmpty())
                    <div class="activity-list"><span class="metadata-label">Recent Activity</span>@foreach($service->histories->take(3) as $history)<div class="activity-item"><i class="mdi mdi-circle-small" aria-hidden="true"></i><span>{{ str($history->action)->replace('_', ' ')->title() }} · {{ $history->created_at->format('M d, Y · g:i A') }}@if($history->user) · {{ $history->user->name }}@endif</span></div>@endforeach</div>
                @endif
            </section>

            @can('update', $service)
                <section id="edit-{{ $service->id }}" class="service-panel" aria-label="Edit {{ $serviceLabel }}">
                    <form method="POST" action="{{ route('lswdo.eclip.basic-services.update', $service) }}" data-service-form data-edit-form>
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-lg-3 col-md-6 form-group"><label for="agency-{{ $service->id }}" class="field-label">Partner Agency</label><select id="agency-{{ $service->id }}" name="gov_agency_id" class="form-control"><option value="">Not assigned</option>@foreach($agencies as $agency)<option value="{{ $agency->id }}" @selected($service->gov_agency_id === $agency->id)>{{ $agency->acronym ?: $agency->name }}</option>@endforeach</select></div>
                            <div class="col-lg-3 col-md-6 form-group"><label for="status-{{ $service->id }}" class="field-label">Status</label><select id="status-{{ $service->id }}" name="status" class="form-control" required>@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected($service->status === $value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="col-lg-3 col-md-6 form-group"><label for="referral-{{ $service->id }}" class="field-label">Referral Date</label><input id="referral-{{ $service->id }}" type="date" name="referral_date" value="{{ $service->referral_date?->format('Y-m-d') }}" class="form-control"></div>
                            <div class="col-lg-3 col-md-6 form-group"><label for="target-{{ $service->id }}" class="field-label">Target Completion</label><input id="target-{{ $service->id }}" type="date" name="target_completion_date" value="{{ $service->target_completion_date?->format('Y-m-d') }}" class="form-control"></div>
                        </div>
                        <div class="form-group"><label for="remarks-{{ $service->id }}" class="field-label">Remarks</label><textarea id="remarks-{{ $service->id }}" name="remarks" rows="3" maxlength="5000" class="form-control" placeholder="No remarks recorded">{{ $service->remarks }}</textarea></div>
                        <div class="d-flex flex-wrap justify-content-end" style="gap: .5rem;"><button type="button" class="btn btn-light" data-panel-close>Cancel</button><button class="btn btn-primary" data-submit-button><i class="mdi mdi-content-save-outline" aria-hidden="true"></i> Save Changes</button></div>
                    </form>
                </section>
            @endcan
        </article>
    @empty
        <div class="empty-services"><i class="mdi mdi-hand-heart-outline" aria-hidden="true"></i><strong class="d-block text-dark mb-1">No basic services recorded yet.</strong><span class="d-block mb-3">Add a service need or referral to begin monitoring assistance for this beneficiary.</span><button type="button" class="btn btn-primary" data-add-toggle><i class="mdi mdi-plus" aria-hidden="true"></i> Add Service</button></div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
(function () {
    var page = document.querySelector('[data-services-page]');
    if (!page) return;

    var addPanel = page.querySelector('#add-service-panel');
    function setAddPanel(open) {
        addPanel.classList.toggle('is-open', open);
        page.querySelectorAll('[data-add-toggle]').forEach(function (button) { button.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        if (open) addPanel.querySelector('select, input, textarea')?.focus();
    }
    page.querySelectorAll('[data-add-toggle]').forEach(function (button) { button.addEventListener('click', function () { setAddPanel(!addPanel.classList.contains('is-open')); }); });
    page.querySelectorAll('[data-add-cancel]').forEach(function (button) { button.addEventListener('click', function () { setAddPanel(false); }); });

    function closePanels() {
        page.querySelectorAll('.service-panel.is-open').forEach(function (panel) { panel.classList.remove('is-open'); });
        page.querySelectorAll('[data-panel-toggle]').forEach(function (button) { button.setAttribute('aria-expanded', 'false'); });
    }
    page.querySelectorAll('[data-panel-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var panel = document.getElementById(button.getAttribute('data-panel-toggle'));
            var shouldOpen = !panel.classList.contains('is-open');
            closePanels();
            if (shouldOpen) { panel.classList.add('is-open'); button.setAttribute('aria-expanded', 'true'); panel.querySelector('select, input, textarea, a, button')?.focus(); }
        });
    });
    page.querySelectorAll('[data-panel-close]').forEach(function (button) { button.addEventListener('click', closePanels); });

    page.querySelectorAll('[data-evidence-upload]').forEach(function (form) {
        var input = form.querySelector('[data-evidence-input]');
        var name = form.querySelector('[data-evidence-name]');
        var button = form.querySelector('[data-upload-button]');
        input.addEventListener('change', function () { name.textContent = input.files.length ? input.files[0].name : 'PDF, JPG, or PNG · Maximum 10 MB'; });
        form.addEventListener('submit', function () { button.disabled = true; button.innerHTML = '<i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i> Uploading...'; });
    });
    page.querySelectorAll('[data-service-form]').forEach(function (form) {
        var button = form.querySelector('[data-submit-button]');
        form.addEventListener('submit', function () { button.disabled = true; button.innerHTML = '<i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i> Saving...'; });
    });
})();
</script>
@endpush
