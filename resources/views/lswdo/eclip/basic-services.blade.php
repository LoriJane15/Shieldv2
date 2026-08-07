@extends('layouts.skydash-v')
@section('title', 'E-CLIP Basic Services')
@section('heading', 'Basic Services Monitoring')

@push('styles')
<style>
    .services-page { --service-primary: #2f6fed; --service-navy: #172b4d; --service-border: #e7ecf3; }
    .services-back { align-items: center; color: #64748b; display: inline-flex; font-size: .82rem; font-weight: 600; gap: .4rem; margin-bottom: 1rem; }
    .services-back:hover { color: #2f6fed; text-decoration: none; }
    .services-hero { background: linear-gradient(125deg, #173b74, #2f6fed); border-radius: 16px; box-shadow: 0 10px 28px rgba(47, 111, 237, .16); color: #fff; overflow: hidden; padding: 1.5rem; position: relative; }
    .services-hero::after { background: rgba(255, 255, 255, .08); border-radius: 50%; content: ''; height: 190px; position: absolute; right: -50px; top: -95px; width: 190px; }
    .services-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .1em; opacity: .75; text-transform: uppercase; }
    .services-hero h2 { color: #fff; font-size: 1.55rem; font-weight: 700; }
    .case-meta { display: flex; flex-wrap: wrap; gap: .75rem 1.5rem; }
    .case-meta span { align-items: center; display: inline-flex; font-size: .82rem; gap: .5rem; opacity: .92; }
    .case-meta span > i { align-items: center; background: rgba(255, 255, 255, .1); border-radius: 7px; display: inline-flex; flex: 0 0 28px; height: 28px; justify-content: center; width: 28px; }
    .scope-notice { align-items: flex-start; background: #eef5ff; border: 1px solid #ccddfb; border-radius: 11px; color: #36537c; display: flex; font-size: .78rem; gap: .65rem; padding: .8rem 1rem; }
    .scope-notice i { align-items: center; color: #2f6fed; display: inline-flex; flex: 0 0 24px; font-size: 1.05rem; height: 24px; justify-content: center; width: 24px; }
    .summary-card, .service-card, .add-service-card { border: 1px solid var(--service-border); border-radius: 14px; box-shadow: 0 4px 16px rgba(23, 43, 77, .045); }
    .summary-card .card-body { align-items: center; display: flex; gap: .85rem; padding: 1rem; }
    .summary-icon { align-items: center; background: var(--summary-bg); border-radius: 10px; color: var(--summary-color); display: flex; flex: 0 0 40px; font-size: 1.15rem; height: 40px; justify-content: center; width: 40px; }
    .summary-card strong { color: var(--service-navy); display: block; font-size: 1.35rem; line-height: 1.15; }
    .summary-card span { color: #718096; font-size: .72rem; font-weight: 600; text-transform: uppercase; }
    .summary-total { --summary-bg: #eaf1ff; --summary-color: #2f6fed; }
    .summary-active { --summary-bg: #fff5dc; --summary-color: #d89400; }
    .summary-complete { --summary-bg: #e8f8f1; --summary-color: #20a779; }
    .summary-overdue { --summary-bg: #ffebed; --summary-color: #d84f5a; }
    .add-service-card .card-header { background: #fff; border-bottom: 1px solid #edf1f6; border-radius: 14px 14px 0 0; padding: 1.15rem 1.35rem; }
    .add-service-card .card-body, .service-card .card-body { padding: 1.35rem; }
    .section-icon { align-items: center; background: #eaf1ff; border-radius: 10px; color: #2f6fed; display: flex; flex: 0 0 40px; font-size: 1.15rem; height: 40px; justify-content: center; width: 40px; }
    .section-title { color: var(--service-navy); font-size: 1rem; font-weight: 700; margin: 0 0 .2rem; }
    .section-subtitle { color: #8492a6; font-size: .76rem; margin: 0; }
    .field-label { color: #42526b; font-size: .77rem; font-weight: 600; }
    .field-label .optional { color: #94a3b8; font-size: .68rem; font-weight: 400; }
    .form-control { border-color: #dfe5ee; border-radius: 8px; }
    .form-control:focus { border-color: #80a6ee; box-shadow: 0 0 0 3px rgba(47, 111, 237, .1); }
    .service-card { overflow: hidden; }
    .service-card-header { align-items: center; background: #f8fafc; border-bottom: 1px solid #edf1f6; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; padding: 1rem 1.25rem; }
    .service-title-wrap { align-items: center; display: flex; gap: .75rem; }
    .service-type-icon { align-items: center; background: #eaf1ff; border-radius: 10px; color: #2f6fed; display: flex; flex: 0 0 40px; font-size: 1.1rem; height: 40px; justify-content: center; width: 40px; }
    .service-title { color: #263a59; font-size: .95rem; font-weight: 700; margin: 0; }
    .service-agency { color: #8492a6; font-size: .72rem; margin-top: .12rem; }
    .status-badge { border-radius: 14px; display: inline-flex; font-size: .7rem; font-weight: 700; padding: .35rem .7rem; }
    .status-pending { background: #f1f4f8; color: #64748b; }
    .status-referred { background: #eaf1ff; color: #2f6fed; }
    .status-in-progress { background: #fff4d6; color: #9a6800; }
    .status-completed { background: #e6f7ef; color: #16845e; }
    .status-not-applicable { background: #f2ebff; color: #7351b8; }
    .date-warning { color: #c74650; font-size: .7rem; font-weight: 600; }
    .service-divider { border-top: 1px solid #edf1f6; margin: 1.25rem 0; }
    .evidence-heading { color: #42526b; font-size: .82rem; font-weight: 700; }
    .service-evidence-upload { background: #f8fafc; border: 1px dashed #aebbd0; border-radius: 10px; padding: .85rem; transition: border-color .2s, background-color .2s; }
    .service-evidence-upload:focus-within { background: #f2f6ff; border-color: #2f6fed; box-shadow: 0 0 0 3px rgba(47, 111, 237, .1); }
    .service-evidence-picker { align-items: center; cursor: pointer; display: flex; gap: .65rem; margin: 0; }
    .service-evidence-icon { align-items: center; background: #eaf1ff; border-radius: 8px; color: #2f6fed; display: inline-flex; flex: 0 0 36px; height: 36px; justify-content: center; }
    .service-evidence-title { color: #334155; display: block; font-size: .78rem; font-weight: 600; }
    .service-evidence-help, .service-evidence-name { color: #8492a6; display: block; font-size: .68rem; }
    .service-evidence-name { color: #64748b; margin-top: .15rem; overflow-wrap: anywhere; }
    .service-evidence-upload input[type=file] { border: 0; clip: rect(0, 0, 0, 0); height: 1px; overflow: hidden; padding: 0; position: absolute; white-space: nowrap; width: 1px; }
    .evidence-document-list { display: grid; gap: .55rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: .8rem; }
    .evidence-preview-link { align-items: center; border: 1px solid #dfe5ee; border-radius: 9px; color: #36537c; display: flex; gap: .6rem; min-width: 0; padding: .6rem .7rem; text-decoration: none; }
    .evidence-preview-link:hover { background: #f3f7ff; border-color: #afc5ef; color: #245cc4; text-decoration: none; }
    .evidence-preview-link i { align-items: center; background: #eaf1ff; border-radius: 7px; color: #2f6fed; display: flex; flex: 0 0 32px; height: 32px; justify-content: center; }
    .evidence-preview-link span { min-width: 0; }
    .evidence-preview-link strong, .evidence-preview-link small { display: block; }
    .evidence-preview-link strong { font-size: .75rem; }
    .evidence-preview-link small { color: #8492a6; font-size: .68rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .latest-badge { background: #e6f7ef; border-radius: 10px; color: #16845e; font-size: .58rem; margin-left: .25rem; padding: .16rem .38rem; }
    .empty-services { background: #fff; border: 1px dashed #cbd5e1; border-radius: 14px; color: #8492a6; padding: 2.5rem 1rem; text-align: center; }
    .empty-services i { color: #b7c2d1; display: block; font-size: 2.25rem; margin-bottom: .5rem; }
    .services-page .btn { align-items: center; display: inline-flex; gap: .3rem; justify-content: center; }
    @media (max-width: 767px) { .services-hero { padding: 1.2rem; } .services-hero h2 { font-size: 1.3rem; } .case-meta { align-items: flex-start; flex-direction: column; gap: .5rem; } .evidence-document-list { grid-template-columns: 1fr; } .service-evidence-upload .btn { margin-top: .75rem; width: 100%; } .service-card-header { align-items: flex-start; } }
</style>
@endpush

@section('content')
@php
    $services = $case->basicServices->sortBy('service_type');
    $completedCount = $services->where('status', 'completed')->count();
    $activeCount = $services->whereIn('status', ['pending', 'referred', 'in_progress'])->count();
    $overdueCount = $services->filter(fn ($service) => ! in_array($service->status, ['completed', 'not_applicable'], true) && $service->target_completion_date?->isBefore(today()))->count();
    $serviceIcons = [
        'health' => 'mdi-medical-bag', 'education' => 'mdi-school-outline', 'housing' => 'mdi-home-outline',
        'legal' => 'mdi-scale-balance', 'psychosocial' => 'mdi-head-heart-outline',
        'livelihood_preparation' => 'mdi-briefcase-outline', 'government_registration' => 'mdi-card-account-details-outline',
        'employment' => 'mdi-account-hard-hat',
    ];
    $statusOptions = ['pending' => 'Pending', 'referred' => 'Referred', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'not_applicable' => 'Not Applicable'];
@endphp

<div class="services-page">
    <a href="{{ route('lswdo.eclip.show', $case) }}" class="services-back"><i class="mdi mdi-arrow-left"></i> Back to eligibility review</a>

    <section class="services-hero mb-3" aria-labelledby="services-title">
        <div class="position-relative" style="z-index: 1;">
            <div class="services-eyebrow mb-1">E-CLIP Case {{ $case->case_number }}</div>
            <h2 id="services-title" class="mb-3">Basic Services Monitoring</h2>
            <div class="case-meta">
                <span><i class="mdi mdi-account-key-outline"></i> Beneficiary {{ $case->formerRebel->classified_id }}</span>
                <span><i class="mdi mdi-folder-check-outline"></i> {{ $services->count() }} service {{ Str::plural('record', $services->count()) }}</span>
            </div>
        </div>
    </section>

    <div class="scope-notice mb-4" role="note"><i class="mdi mdi-information-outline"></i><span>Basic-service completion is monitored separately and does not automatically determine E-CLIP eligibility.</span></div>

    <div class="row">
        <div class="col-6 col-xl-3 mb-4"><div class="card summary-card summary-total h-100"><div class="card-body"><div class="summary-icon"><i class="mdi mdi-format-list-checks"></i></div><div><strong>{{ $services->count() }}</strong><span>Total services</span></div></div></div></div>
        <div class="col-6 col-xl-3 mb-4"><div class="card summary-card summary-active h-100"><div class="card-body"><div class="summary-icon"><i class="mdi mdi-progress-clock"></i></div><div><strong>{{ $activeCount }}</strong><span>Active</span></div></div></div></div>
        <div class="col-6 col-xl-3 mb-4"><div class="card summary-card summary-complete h-100"><div class="card-body"><div class="summary-icon"><i class="mdi mdi-check-circle-outline"></i></div><div><strong>{{ $completedCount }}</strong><span>Completed</span></div></div></div></div>
        <div class="col-6 col-xl-3 mb-4"><div class="card summary-card summary-overdue h-100"><div class="card-body"><div class="summary-icon"><i class="mdi mdi-clock-alert-outline"></i></div><div><strong>{{ $overdueCount }}</strong><span>Overdue</span></div></div></div></div>
    </div>

    <section class="card add-service-card mb-4" aria-labelledby="add-service-title">
        <div class="card-header">
            <div class="d-flex align-items-center"><div class="section-icon mr-3"><i class="mdi mdi-plus-circle-outline"></i></div><div><h3 id="add-service-title" class="section-title">Add Service Need or Referral</h3><p class="section-subtitle">Record a new service requirement and assign a partner agency when available.</p></div></div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('lswdo.eclip.basic-services.store', $case) }}" data-service-form>
                @csrf
                <div class="row">
                    <div class="col-lg-4 form-group"><label for="service_type" class="field-label">Service Type</label><select id="service_type" name="service_type" class="form-control @error('service_type') is-invalid @enderror" required><option value="">Select service type</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(old('service_type') === $value)>{{ $label }}</option>@endforeach</select>@error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-lg-4 form-group"><label for="gov_agency_id" class="field-label">Partner Agency <span class="optional">(optional)</span></label><select id="gov_agency_id" name="gov_agency_id" class="form-control @error('gov_agency_id') is-invalid @enderror"><option value="">Not assigned</option>@foreach($agencies as $agency)<option value="{{ $agency->id }}" @selected((string) old('gov_agency_id') === (string) $agency->id)>{{ $agency->acronym ?: $agency->name }}</option>@endforeach</select>@error('gov_agency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-lg-4 form-group"><label for="status" class="field-label">Initial Status</label><select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(old('status', 'pending') === $value)>{{ $label }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4 form-group"><label for="referral_date" class="field-label">Referral Date <span class="optional">(optional)</span></label><input id="referral_date" type="date" name="referral_date" value="{{ old('referral_date') }}" class="form-control @error('referral_date') is-invalid @enderror">@error('referral_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4 form-group"><label for="target_completion_date" class="field-label">Target Completion <span class="optional">(optional)</span></label><input id="target_completion_date" type="date" name="target_completion_date" value="{{ old('target_completion_date') }}" class="form-control @error('target_completion_date') is-invalid @enderror">@error('target_completion_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4 form-group"><label for="remarks" class="field-label">Remarks <span class="optional">(optional)</span></label><textarea id="remarks" name="remarks" rows="2" maxlength="5000" class="form-control @error('remarks') is-invalid @enderror" placeholder="Add referral notes...">{{ old('remarks') }}</textarea>@error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="text-right"><button class="btn btn-primary" data-submit-button><i class="mdi mdi-plus mr-1"></i>Add Service Record</button></div>
            </form>
        </div>
    </section>

    <div class="d-flex align-items-end justify-content-between mb-3"><div><h3 class="section-title">Recorded Services</h3><p class="section-subtitle">Update delivery status, dates, agency assignment, and supporting evidence.</p></div><span class="text-muted small">{{ $services->count() }} {{ Str::plural('record', $services->count()) }}</span></div>

    @forelse($services as $service)
        @php
            $isOverdue = ! in_array($service->status, ['completed', 'not_applicable'], true) && $service->target_completion_date?->isBefore(today());
            $serviceLabel = $types[$service->service_type] ?? str($service->service_type)->headline();
        @endphp
        <article class="card service-card mb-3" aria-labelledby="service-{{ $service->id }}-title">
            <div class="service-card-header">
                <div class="service-title-wrap">
                    <div class="service-type-icon"><i class="mdi {{ $serviceIcons[$service->service_type] ?? 'mdi-hand-heart-outline' }}"></i></div>
                    <div><h4 id="service-{{ $service->id }}-title" class="service-title">{{ $serviceLabel }}</h4><div class="service-agency">{{ $service->agency?->name ?? 'No partner agency assigned' }} @if($isOverdue)<span class="date-warning ml-2"><i class="mdi mdi-alert-circle-outline"></i> Target date overdue</span>@endif</div></div>
                </div>
                <span class="status-badge status-{{ str($service->status)->slug() }}">{{ str($service->status)->replace('_', ' ')->title() }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lswdo.eclip.basic-services.update', $service) }}" data-service-form>
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-lg-3 col-md-6 form-group"><label for="agency-{{ $service->id }}" class="field-label">Partner Agency</label><select id="agency-{{ $service->id }}" name="gov_agency_id" class="form-control"><option value="">Not assigned</option>@foreach($agencies as $agency)<option value="{{ $agency->id }}" @selected($service->gov_agency_id === $agency->id)>{{ $agency->acronym ?: $agency->name }}</option>@endforeach</select></div>
                        <div class="col-lg-3 col-md-6 form-group"><label for="status-{{ $service->id }}" class="field-label">Status</label><select id="status-{{ $service->id }}" name="status" class="form-control">@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected($service->status === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-lg-3 col-md-6 form-group"><label for="referral-{{ $service->id }}" class="field-label">Referral Date</label><input id="referral-{{ $service->id }}" type="date" name="referral_date" value="{{ $service->referral_date?->format('Y-m-d') }}" class="form-control"></div>
                        <div class="col-lg-3 col-md-6 form-group"><label for="target-{{ $service->id }}" class="field-label">Target Completion</label><input id="target-{{ $service->id }}" type="date" name="target_completion_date" value="{{ $service->target_completion_date?->format('Y-m-d') }}" class="form-control"></div>
                    </div>
                    <div class="form-group"><label for="remarks-{{ $service->id }}" class="field-label">Remarks</label><textarea id="remarks-{{ $service->id }}" name="remarks" rows="2" maxlength="5000" class="form-control">{{ $service->remarks }}</textarea></div>
                    <div class="text-right"><button class="btn btn-sm btn-primary" data-submit-button><i class="mdi mdi-content-save-outline mr-1"></i>Save Changes</button></div>
                </form>

                <div class="service-divider"></div>
                <div class="evidence-heading mb-2"><i class="mdi mdi-paperclip mr-1 text-primary"></i>Supporting Evidence</div>
                <form method="POST" action="{{ route('lswdo.eclip.basic-services.documents.store', $service) }}" enctype="multipart/form-data" class="service-evidence-upload" data-evidence-upload>
                    @csrf
                    <input id="service-file-{{ $service->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required data-evidence-input>
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                        <label for="service-file-{{ $service->id }}" class="service-evidence-picker">
                            <span class="service-evidence-icon"><i class="mdi mdi-cloud-upload-outline"></i></span>
                            <span><span class="service-evidence-title">Choose supporting evidence</span><span class="service-evidence-help">PDF, JPG, or PNG · Maximum 10 MB</span><span class="service-evidence-name" data-evidence-name>No file selected</span></span>
                        </label>
                        <button class="btn btn-sm btn-outline-primary" data-upload-button><i class="mdi mdi-upload mr-1"></i>Upload New Version</button>
                    </div>
                    @error('document')<div class="text-danger small mt-2" role="alert">{{ $message }}</div>@enderror
                </form>

                <div class="evidence-document-list">
                    @forelse($service->documents->sortByDesc('version_number') as $document)
                        <a class="evidence-preview-link" href="{{ route('lswdo.eclip.basic-services.documents.preview', $document) }}" title="Preview {{ $document->original_name }}"><i class="mdi mdi-eye-outline"></i><span><strong>Version {{ $document->version_number }} @if($loop->first)<span class="latest-badge">Latest</span>@endif</strong><small>{{ $document->original_name }}</small></span></a>
                    @empty
                        <div class="text-muted small py-2"><i class="mdi mdi-file-hidden mr-1"></i>No supporting evidence uploaded.</div>
                    @endforelse
                </div>
            </div>
        </article>
    @empty
        <div class="empty-services"><i class="mdi mdi-hand-heart-outline"></i><strong class="d-block text-dark mb-1">No services recorded yet</strong><span class="small">Use the form above to add the beneficiary's first service need or referral.</span></div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-evidence-upload]').forEach(function (form) {
    var input = form.querySelector('[data-evidence-input]');
    var name = form.querySelector('[data-evidence-name]');
    var button = form.querySelector('[data-upload-button]');
    input.addEventListener('change', function () { name.textContent = input.files.length ? input.files[0].name : 'No file selected'; });
    form.addEventListener('submit', function () { button.disabled = true; button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Uploading...'; });
});

document.querySelectorAll('[data-service-form]').forEach(function (form) {
    var button = form.querySelector('[data-submit-button]');
    form.addEventListener('submit', function () { button.disabled = true; button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...'; });
});
</script>
@endpush
