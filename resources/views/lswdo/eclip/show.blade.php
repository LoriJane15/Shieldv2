@extends('layouts.skydash-v')
@section('title', 'Eligibility Review')
@section('heading', 'LSWDO Eligibility Review')

@push('styles')
<style>
    .review-page { --review-primary: #2f6fed; --review-navy: #172b4d; --review-border: #e7ecf3; }
    .review-back { align-items: center; color: #64748b; display: inline-flex; font-size: .82rem; font-weight: 600; gap: .4rem; margin-bottom: 1rem; }
    .review-back:hover { color: #2f6fed; text-decoration: none; }
    .case-hero { background: linear-gradient(125deg, #173b74, #2f6fed); border-radius: 16px; box-shadow: 0 10px 28px rgba(47, 111, 237, .16); color: #fff; overflow: hidden; padding: 1.5rem; position: relative; }
    .case-hero::after { background: rgba(255, 255, 255, .08); border-radius: 50%; content: ''; height: 180px; position: absolute; right: -45px; top: -90px; width: 180px; }
    .case-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .1em; opacity: .75; text-transform: uppercase; }
    .case-number { color: #fff; font-size: 1.6rem; font-weight: 700; }
    .case-status { background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); border-radius: 20px; color: #fff; display: inline-flex; font-size: .75rem; font-weight: 700; padding: .4rem .75rem; position: relative; z-index: 1; }
    .case-details { display: flex; flex-wrap: wrap; gap: .8rem 1.75rem; }
    .case-detail { align-items: center; display: flex; gap: .55rem; }
    .case-detail i { font-size: 1.1rem; opacity: .82; }
    .case-detail small { display: block; font-size: .67rem; opacity: .7; text-transform: uppercase; }
    .case-detail strong { display: block; font-size: .86rem; }
    .review-card { border: 1px solid var(--review-border); border-radius: 14px; box-shadow: 0 4px 16px rgba(23, 43, 77, .045); }
    .review-card .card-body { padding: 1.4rem; }
    .section-icon { align-items: center; background: #eaf1ff; border-radius: 10px; color: #2f6fed; display: flex; flex: 0 0 40px; font-size: 1.15rem; height: 40px; justify-content: center; width: 40px; }
    .section-title { color: var(--review-navy); font-size: 1rem; font-weight: 700; margin: 0 0 .2rem; }
    .section-subtitle { color: #8492a6; font-size: .78rem; margin: 0; }
    .service-link { align-items: center; background: #f7f9fc; border: 1px solid #e5eaf2; border-radius: 10px; color: #36537c; display: flex; font-size: .82rem; font-weight: 600; gap: .7rem; padding: .8rem 1rem; }
    .service-link:hover { background: #edf4ff; border-color: #b9cdf5; color: #245cc4; text-decoration: none; }
    .service-link i:last-child { margin-left: auto; }
    .decision-option { cursor: pointer; display: block; margin-bottom: .7rem; position: relative; }
    .decision-option input { opacity: 0; position: absolute; }
    .decision-content { align-items: center; border: 1px solid #dfe5ee; border-radius: 11px; display: flex; gap: .75rem; padding: .85rem; transition: border-color .15s, background-color .15s, box-shadow .15s; }
    .decision-content i { align-items: center; background: #f1f4f8; border-radius: 9px; color: #64748b; display: flex; flex: 0 0 36px; font-size: 1.05rem; height: 36px; justify-content: center; }
    .decision-content strong, .decision-content small { display: block; }
    .decision-content strong { color: #334155; font-size: .84rem; }
    .decision-content small { color: #8492a6; font-size: .72rem; }
    .decision-option input:focus + .decision-content { box-shadow: 0 0 0 3px rgba(47, 111, 237, .12); }
    .decision-option input:checked + .decision-content { background: #f1f6ff; border-color: #2f6fed; }
    .decision-option input:checked + .decision-content i { background: #2f6fed; color: #fff; }
    .decision-option.ineligible input:checked + .decision-content { background: #fff5f5; border-color: #e65b65; }
    .decision-option.ineligible input:checked + .decision-content i { background: #e65b65; }
    .decision-option.returned input:checked + .decision-content { background: #fff9e9; border-color: #d89400; }
    .decision-option.returned input:checked + .decision-content i { background: #d89400; }
    .remarks-label { color: #42526b; font-size: .8rem; font-weight: 600; }
    .remarks-help { color: #8492a6; font-size: .72rem; font-weight: 400; }
    .review-submit { border-radius: 9px; font-weight: 600; padding: .65rem 1rem; }
    .checklist-progress { color: #64748b; font-size: .78rem; }
    .document-item { border: 1px solid #e7ecf3; border-radius: 12px; margin-bottom: 1rem; overflow: hidden; }
    .document-item:last-child { margin-bottom: 0; }
    .document-heading { align-items: center; background: #f8fafc; border-bottom: 1px solid #edf1f6; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; padding: .85rem 1rem; }
    .requirement-name { color: #334155; font-size: .86rem; font-weight: 700; }
    .required-mark { color: #dc3545; }
    .document-status { border-radius: 14px; display: inline-flex; font-size: .7rem; font-weight: 700; padding: .3rem .65rem; }
    .status-missing { background: #f1f4f8; color: #64748b; }
    .status-pending { background: #fff4d6; color: #9a6800; }
    .status-certified, .status-approved { background: #e6f7ef; color: #16845e; }
    .status-rejected, .status-incomplete { background: #ffebed; color: #bd3e49; }
    .document-body { padding: 1rem; }
    .version-grid { display: grid; gap: .55rem; }
    .document-preview-link { align-items: center; border: 1px solid #dfe5ee; border-radius: 9px; color: #36537c; display: flex; gap: .65rem; padding: .65rem .75rem; text-decoration: none; transition: border-color .15s, background-color .15s, transform .15s; }
    .document-preview-link:hover { background: #f3f7ff; border-color: #afc5ef; color: #245cc4; text-decoration: none; transform: translateY(-1px); }
    .document-preview-link i { align-items: center; background: #eaf1ff; border-radius: 8px; color: #2f6fed; display: flex; flex: 0 0 34px; height: 34px; justify-content: center; }
    .document-preview-link strong, .document-preview-link small { display: block; }
    .document-preview-link strong { font-size: .78rem; }
    .document-preview-link small { color: #8492a6; font-size: .7rem; max-width: 210px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .latest-badge { background: #e6f7ef; border-radius: 10px; color: #16845e; font-size: .6rem; margin-left: .3rem; padding: .18rem .4rem; }
    .no-document { align-items: center; background: #f8fafc; border: 1px dashed #d8e0ea; border-radius: 9px; color: #8492a6; display: flex; font-size: .78rem; gap: .5rem; justify-content: center; min-height: 58px; padding: .75rem; }
    .supporting-upload { background: #f8fafc; border: 1px dashed #aebbd0; border-radius: 10px; padding: .8rem; transition: border-color .2s, background-color .2s; }
    .supporting-upload:focus-within { background: #f2f6ff; border-color: #2f6fed; box-shadow: 0 0 0 3px rgba(47, 111, 237, .1); }
    .supporting-upload-picker { align-items: center; cursor: pointer; display: flex; gap: .65rem; margin-bottom: .55rem; }
    .supporting-upload-icon { align-items: center; background: #eaf1ff; border-radius: 8px; color: #2f6fed; display: inline-flex; flex: 0 0 36px; height: 36px; justify-content: center; }
    .supporting-upload-title { color: #334155; display: block; font-size: .8rem; font-weight: 600; }
    .supporting-upload-help, .supporting-upload-name { display: block; font-size: .7rem; }
    .supporting-upload-name { color: #64748b; margin-bottom: .55rem; overflow-wrap: anywhere; }
    .supporting-upload input[type=file] { border: 0; clip: rect(0, 0, 0, 0); height: 1px; overflow: hidden; padding: 0; position: absolute; white-space: nowrap; width: 1px; }
    .history-timeline { list-style: none; margin: 0; padding: 0; }
    .history-item { padding: 0 0 1.25rem 2rem; position: relative; }
    .history-item:last-child { padding-bottom: 0; }
    .history-item::before { background: #dce5f2; bottom: 0; content: ''; left: .43rem; position: absolute; top: 14px; width: 2px; }
    .history-item:last-child::before { display: none; }
    .history-dot { background: #fff; border: 3px solid #2f6fed; border-radius: 50%; height: 15px; left: 0; position: absolute; top: 3px; width: 15px; }
    .history-status { color: #334155; font-size: .83rem; font-weight: 700; }
    .history-meta { color: #8492a6; font-size: .7rem; margin-top: .15rem; }
    .history-remarks { background: #f8fafc; border-radius: 8px; color: #64748b; font-size: .76rem; margin-top: .45rem; padding: .6rem .7rem; }
    @media (max-width: 767px) { .case-hero { padding: 1.2rem; } .case-number { font-size: 1.35rem; } .case-status { margin-top: .75rem; } .review-card .card-body { padding: 1.1rem; } }
</style>
@endpush

@section('content')
@php
    $documentsByRequirement = $case->documents->keyBy('requirement_id');
    $submittedDocuments = $requirements->filter(fn ($requirement) => $documentsByRequirement->has($requirement->id))->count();
@endphp

<div class="review-page">
    <a href="{{ route('lswdo.eclip.index') }}" class="review-back"><i class="mdi mdi-arrow-left"></i> Back to eligibility cases</a>

    <section class="case-hero mb-4" aria-labelledby="case-number">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start position-relative" style="z-index: 1;">
            <div>
                <div class="case-eyebrow mb-1">E-CLIP Case</div>
                <h2 id="case-number" class="case-number mb-3">{{ $case->case_number }}</h2>
                <div class="case-details">
                    <div class="case-detail"><i class="mdi mdi-account-key-outline"></i><div><small>Beneficiary ID</small><strong>{{ $case->formerRebel->classified_id }}</strong></div></div>
                    <div class="case-detail"><i class="mdi mdi-map-marker-outline"></i><div><small>Municipality</small><strong>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</strong></div></div>
                    <div class="case-detail"><i class="mdi mdi-calendar-check-outline"></i><div><small>Submitted</small><strong>{{ $case->submitted_at?->format('M d, Y') ?? 'Not recorded' }}</strong></div></div>
                </div>
            </div>
            <span class="case-status"><i class="mdi mdi-progress-check mr-1"></i>{{ $case->status->label() }}</span>
        </div>
    </section>

    <div class="row">
        <div class="col-xl-8">
            @can('reviewEligibility', $case)
                <section class="card review-card mb-4" aria-labelledby="decision-title">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon mr-3"><i class="mdi mdi-clipboard-check-outline"></i></div>
                            <div><h3 id="decision-title" class="section-title">Record Eligibility Decision</h3><p class="section-subtitle">Review the case information and select the appropriate outcome.</p></div>
                        </div>

                        <form method="POST" action="{{ route('lswdo.eclip.eligibility.decide', $case) }}" data-decision-form>
                            @csrf
                            <fieldset>
                                <legend class="remarks-label mb-2">Decision</legend>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="decision-option eligible">
                                            <input type="radio" name="decision" value="eligible" @checked(old('decision') === 'eligible') required>
                                            <span class="decision-content"><i class="mdi mdi-check-circle-outline"></i><span><strong>Eligible</strong><small>Proceed with E-CLIP processing</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="decision-option ineligible">
                                            <input type="radio" name="decision" value="ineligible" @checked(old('decision') === 'ineligible') required>
                                            <span class="decision-content"><i class="mdi mdi-close-circle-outline"></i><span><strong>Ineligible</strong><small>Does not meet requirements</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="decision-option returned">
                                            <input type="radio" name="decision" value="returned" @checked(old('decision') === 'returned') required>
                                            <span class="decision-content"><i class="mdi mdi-undo-variant"></i><span><strong>Return</strong><small>Send back for correction</small></span></span>
                                        </label>
                                    </div>
                                </div>
                                @error('decision')<div class="text-danger small mb-3" role="alert">{{ $message }}</div>@enderror
                            </fieldset>

                            <div class="form-group mt-2">
                                <label for="remarks" class="remarks-label">Remarks <span class="remarks-help" data-remarks-help>(optional for eligible decisions)</span></label>
                                <textarea id="remarks" name="remarks" rows="4" maxlength="5000" class="form-control @error('remarks') is-invalid @enderror" placeholder="Provide a clear reason or instructions for the submitting office...">{{ old('remarks') }}</textarea>
                                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mt-3">
                                <small class="text-muted mb-2 mb-sm-0"><i class="mdi mdi-information-outline mr-1"></i>This decision will be recorded in the case history.</small>
                                <button class="btn btn-primary review-submit" data-submit-button><i class="mdi mdi-content-save-check-outline mr-1"></i>Save Decision</button>
                            </div>
                        </form>
                    </div>
                </section>
            @endcan

            @can('uploadDocument', $case)
                <section class="card review-card mb-4" aria-labelledby="documents-title">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center mr-3">
                                <div class="section-icon mr-3"><i class="mdi mdi-file-document-multiple-outline"></i></div>
                                <div><h3 id="documents-title" class="section-title">Supporting Documents</h3><p class="section-subtitle">Review existing files or upload a new version.</p></div>
                            </div>
                            @if($requirements->isNotEmpty())
                                <span class="checklist-progress mt-2 mt-sm-0"><strong>{{ $submittedDocuments }}</strong> of <strong>{{ $requirements->count() }}</strong> requirements uploaded</span>
                            @endif
                        </div>

                        @if($requirements->isEmpty())
                            <div class="alert alert-warning mb-0"><i class="mdi mdi-alert-outline mr-1"></i>The Katuparan Center has not configured the official document checklist.</div>
                        @else
                            @foreach($requirements as $requirement)
                                @php
                                    $document = $documentsByRequirement->get($requirement->id);
                                    $documentStatus = $document?->status ?? 'missing';
                                @endphp
                                <article class="document-item">
                                    <div class="document-heading">
                                        <div class="requirement-name">{{ $loop->iteration }}. {{ $requirement->name }} @if($requirement->is_required)<span class="required-mark" title="Required">*</span>@endif</div>
                                        <span class="document-status status-{{ str($documentStatus)->slug() }}">{{ str($documentStatus)->replace('_', ' ')->title() }}</span>
                                    </div>
                                    <div class="document-body">
                                        <div class="row align-items-start">
                                            <div class="col-lg-6 mb-3 mb-lg-0">
                                                <div class="version-grid">
                                                    @forelse($document?->versions?->sortByDesc('version_number') ?? [] as $version)
                                                        <a class="document-preview-link" href="{{ route('lswdo.eclip.documents.preview', $version) }}" title="Preview {{ $version->original_name }}">
                                                            <i class="mdi mdi-eye-outline"></i>
                                                            <span><strong>Version {{ $version->version_number }} @if($loop->first)<span class="latest-badge">Latest</span>@endif</strong><small>{{ $version->original_name }}</small></span>
                                                        </a>
                                                    @empty
                                                        <div class="no-document"><i class="mdi mdi-file-hidden"></i>No document uploaded yet</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <form method="POST" action="{{ route('lswdo.eclip.documents.store', $case) }}" enctype="multipart/form-data" class="supporting-upload" data-document-upload>
                                                    @csrf
                                                    <input type="hidden" name="requirement_id" value="{{ $requirement->id }}">
                                                    <input id="requirement-file-{{ $requirement->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required data-document-input>
                                                    <label for="requirement-file-{{ $requirement->id }}" class="supporting-upload-picker">
                                                        <span class="supporting-upload-icon"><i class="mdi mdi-cloud-upload-outline"></i></span>
                                                        <span><span class="supporting-upload-title">Choose a file</span><span class="supporting-upload-help text-muted">PDF, JPG, or PNG · Maximum 10 MB</span></span>
                                                    </label>
                                                    <span class="supporting-upload-name" data-document-name>No file selected</span>
                                                    @error('document')<div class="text-danger small mb-2" role="alert">{{ $message }}</div>@enderror
                                                    <button class="btn btn-sm btn-outline-primary btn-block" data-upload-button><i class="mdi mdi-upload mr-1"></i>{{ $document ? 'Upload New Version' : 'Upload Document' }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                            <p class="section-subtitle mt-3 mb-0"><i class="mdi mdi-history mr-1"></i>Uploading a replacement retains earlier versions and resets the JAPIC review.</p>
                        @endif
                    </div>
                </section>
            @endcan
        </div>

        <div class="col-xl-4">
            <section class="card review-card mb-4" aria-labelledby="actions-title">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon mr-3"><i class="mdi mdi-heart-pulse"></i></div>
                        <div><h3 id="actions-title" class="section-title">Case Services</h3><p class="section-subtitle">Related reintegration monitoring.</p></div>
                    </div>
                    <a href="{{ route('lswdo.eclip.basic-services.index', $case) }}" class="service-link"><i class="mdi mdi-clipboard-pulse-outline"></i><span>Monitor Basic Services</span><i class="mdi mdi-chevron-right"></i></a>
                </div>
            </section>

            <section class="card review-card mb-4" aria-labelledby="history-title">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="section-icon mr-3"><i class="mdi mdi-history"></i></div>
                        <div><h3 id="history-title" class="section-title">Case History</h3><p class="section-subtitle">Recorded workflow activity.</p></div>
                    </div>
                    <ol class="history-timeline">
                        @forelse($case->statusHistories->sortByDesc('created_at') as $history)
                            <li class="history-item">
                                <span class="history-dot" aria-hidden="true"></span>
                                <div class="history-status">{{ $history->to_status->label() }}</div>
                                <div class="history-meta"><i class="mdi mdi-account-outline"></i> {{ $history->user->name }} · <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M d, Y · h:i A') }}</time></div>
                                @if($history->remarks)<div class="history-remarks">{{ $history->remarks }}</div>@endif
                            </li>
                        @empty
                            <li class="text-muted small">No case activity has been recorded.</li>
                        @endforelse
                    </ol>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-document-upload]').forEach(function (form) {
    var input = form.querySelector('[data-document-input]');
    var name = form.querySelector('[data-document-name]');
    var button = form.querySelector('[data-upload-button]');

    input.addEventListener('change', function () {
        name.textContent = input.files.length ? input.files[0].name : 'No file selected';
    });
    form.addEventListener('submit', function () {
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Uploading...';
    });
});

var decisionForm = document.querySelector('[data-decision-form]');
if (decisionForm) {
    var remarks = decisionForm.querySelector('#remarks');
    var remarksHelp = decisionForm.querySelector('[data-remarks-help]');
    var submitButton = decisionForm.querySelector('[data-submit-button]');

    function updateRemarksRequirement() {
        var selected = decisionForm.querySelector('input[name="decision"]:checked');
        var isRequired = selected && ['ineligible', 'returned'].indexOf(selected.value) !== -1;
        remarks.required = isRequired;
        remarksHelp.textContent = isRequired ? '(required for this decision)' : '(optional for eligible decisions)';
    }

    decisionForm.querySelectorAll('input[name="decision"]').forEach(function (input) {
        input.addEventListener('change', updateRemarksRequirement);
    });
    decisionForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Saving decision...';
    });
    updateRemarksRequirement();
}
</script>
@endpush
