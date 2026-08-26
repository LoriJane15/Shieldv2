@extends('layouts.skydash-v')
@section('title', 'FEA Case Processing')
@section('heading', 'FEA Case Processing')

@section('content')
@php
    $routePrefix = request()->user()->role.'.eclip-fea';
    $activity = $workspace['activity'];
    $formerRebel = $case->formerRebel;
@endphp

<style>
    .fea-workspace { color: #18233a; }
    .fea-workspace .card { border: 1px solid #e1e7f0; border-radius: 14px; box-shadow: 0 5px 18px rgba(29, 45, 75, .045); }
    .fea-back-link { color: #40587d; font-weight: 600; }
    .fea-back-link:hover { color: #32109a; text-decoration: none; }
    .fea-case-header { position: relative; overflow: hidden; padding: 26px 28px; color: #fff; border-radius: 16px; background: linear-gradient(125deg, #182f59 0%, #16465b 100%); }
    .fea-case-header::after { content: ''; position: absolute; width: 220px; height: 220px; right: -70px; top: -95px; border-radius: 50%; background: rgba(37, 231, 191, .08); }
    .fea-eyebrow { color: #ff9568; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .fea-case-title { margin: 4px 0 8px; color: #fff; font-size: 1.65rem; font-weight: 700; }
    .fea-case-meta { color: #d7e8fb; font-size: .9rem; }
    .fea-status { display: inline-flex; position: relative; z-index: 1; align-items: center; gap: 8px; padding: 8px 13px; border: 1px solid rgba(255, 255, 255, .2); border-radius: 999px; background: rgba(255, 255, 255, .11); color: #fff; font-size: .78rem; font-weight: 700; }
    .fea-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #ffbd4a; box-shadow: 0 0 0 4px rgba(255, 189, 74, .15); }
    .fea-status--complete .fea-status-dot { background: #51e0b1; box-shadow: 0 0 0 4px rgba(81, 224, 177, .15); }
    .fea-status--danger .fea-status-dot { background: #ff7575; box-shadow: 0 0 0 4px rgba(255, 117, 117, .15); }
    .fea-status--verification .fea-status-dot { background: #94b9ff; box-shadow: 0 0 0 4px rgba(148, 185, 255, .15); }
    .fea-progress-card { margin-top: 16px; }
    .fea-progress-card .card-body { padding: 18px 22px; }
    .fea-progress-label { color: #66738c; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .fea-stages { display: grid; grid-template-columns: repeat(4, 1fr); margin-top: 15px; }
    .fea-stage { position: relative; display: flex; align-items: center; gap: 10px; min-width: 0; }
    .fea-stage:not(:last-child)::after { content: ''; position: absolute; z-index: 0; height: 2px; left: 37px; right: 8px; top: 15px; background: #dfe5ee; }
    .fea-stage--complete:not(:last-child)::after { background: #2bb68a; }
    .fea-stage-number { display: inline-flex; position: relative; z-index: 1; flex: 0 0 31px; align-items: center; justify-content: center; width: 31px; height: 31px; border: 2px solid #e0e6ef; border-radius: 50%; background: #f7f9fc; color: #8290a7; font-size: .72rem; font-weight: 800; }
    .fea-stage--active .fea-stage-number { border-color: #4e2cbd; background: #4e2cbd; color: #fff; box-shadow: 0 0 0 5px rgba(78, 44, 189, .09); }
    .fea-stage--complete .fea-stage-number { border-color: #2bb68a; background: #e8faf4; color: #15815f; }
    .fea-stage-copy { position: relative; z-index: 1; min-width: 0; padding-right: 12px; background: #fff; }
    .fea-stage-copy strong { display: block; overflow: hidden; color: #28364e; font-size: .76rem; text-overflow: ellipsis; white-space: nowrap; }
    .fea-stage-copy small { color: #8a96aa; font-size: .66rem; }
    .fea-section-title { margin: 0; color: #1f2b42; font-size: 1rem; font-weight: 700; }
    .fea-section-subtitle { margin: 4px 0 0; color: #7c899f; font-size: .77rem; }
    .fea-icon-box { display: inline-flex; flex: 0 0 38px; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: #f1ecff; color: #4e2cbd; font-size: 1.15rem; }
    .fea-card-heading { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
    .fea-summary-list { margin: 0; }
    .fea-summary-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding: 11px 0; border-bottom: 1px solid #edf0f5; }
    .fea-summary-row:last-child { border-bottom: 0; }
    .fea-summary-row dt { margin: 0; color: #7e8aa0; font-size: .73rem; font-weight: 600; }
    .fea-summary-row dd { margin: 0; color: #253149; font-size: .8rem; font-weight: 700; text-align: right; }
    .fea-inline-status { display: inline-flex; align-items: center; gap: 6px; }
    .fea-inline-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #f3a82b; }
    .fea-upload-zone { display: flex; min-height: 174px; margin-bottom: 15px; padding: 20px; align-items: center; justify-content: center; border: 2px dashed #cbd5e4; border-radius: 12px; background: #f9fbfe; text-align: center; cursor: pointer; transition: .18s ease; }
    .fea-upload-zone:hover, .fea-upload-zone.is-dragging, .fea-upload-zone:focus-within { border-color: #6847ce; background: #f7f4ff; }
    .fea-upload-zone.is-selected { border-color: #2fa57f; background: #f1fbf7; }
    .fea-upload-zone input { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; }
    .fea-upload-icon { display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; margin-bottom: 8px; border-radius: 50%; background: #ede7ff; color: #4e2cbd; font-size: 1.4rem; }
    .fea-upload-zone strong { display: block; color: #27354d; font-size: .9rem; }
    .fea-upload-zone p { margin: 4px 0; color: #718097; font-size: .76rem; }
    .fea-upload-zone .fea-browse { color: #4e2cbd; font-weight: 700; }
    .fea-upload-zone small { color: #96a1b2; font-size: .68rem; }
    .fea-security-note { display: flex; gap: 9px; margin-top: 14px; padding: 10px 12px; border-radius: 9px; background: #eef8f6; color: #426b64; font-size: .7rem; line-height: 1.45; }
    .fea-security-note i { color: #178367; font-size: 1rem; }
    .fea-requirement-progress { height: 7px; overflow: hidden; border-radius: 999px; background: #e8edf4; }
    .fea-requirement-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #4320aa, #7350d7); transition: width .25s ease; }
    .fea-requirement-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px; }
    .fea-requirement { display: flex; align-items: center; gap: 10px; min-height: 62px; padding: 11px 13px; border: 1px solid #e3e8ef; border-radius: 10px; background: #fbfcfe; }
    .fea-requirement i { display: inline-flex; flex: 0 0 27px; align-items: center; justify-content: center; width: 27px; height: 27px; border-radius: 50%; background: #edf1f6; color: #8794a8; }
    .fea-requirement--complete { border-color: #cdeadd; background: #f4fbf8; }
    .fea-requirement--complete i { background: #d8f5e9; color: #17815f; }
    .fea-requirement strong { display: block; color: #33415a; font-size: .74rem; line-height: 1.25; }
    .fea-requirement small { display: block; color: #8793a6; font-size: .66rem; }
    .fea-documents-table th { border-top: 0; color: #7c889d; font-size: .67rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .fea-documents-table td { vertical-align: middle; color: #435069; font-size: .75rem; }
    .fea-file-name { display: flex; align-items: center; gap: 9px; max-width: 300px; color: #26344d; font-weight: 600; }
    .fea-file-name i { color: #5130ba; font-size: 1.15rem; }
    .fea-file-name span { overflow-wrap: anywhere; }
    .fea-received { display: inline-flex; align-items: center; gap: 5px; color: #17785d; font-size: .7rem; font-weight: 700; }
    .fea-empty-state { padding: 38px 20px; text-align: center; }
    .fea-empty-state i { color: #b2bbca; font-size: 2rem; }
    .fea-empty-state h4 { margin: 8px 0 4px; color: #39465d; font-size: .92rem; }
    .fea-empty-state p { margin-bottom: 13px; color: #8490a4; font-size: .75rem; }
    .fea-activity-list { position: relative; margin: 0; padding: 0; list-style: none; }
    .fea-activity-list::before { content: ''; position: absolute; top: 10px; bottom: 12px; left: 12px; width: 1px; background: #dfe5ed; }
    .fea-activity-item { position: relative; display: grid; grid-template-columns: 26px minmax(0, 1fr) auto; gap: 10px; padding-bottom: 18px; }
    .fea-activity-item:last-child { padding-bottom: 0; }
    .fea-activity-marker { display: inline-flex; z-index: 1; align-items: center; justify-content: center; width: 25px; height: 25px; border: 4px solid #fff; border-radius: 50%; background: #e7e0fb; color: #4c29b3; font-size: .62rem; box-shadow: 0 0 0 1px #d8deea; }
    .fea-activity-item strong { display: block; color: #344159; font-size: .74rem; }
    .fea-activity-item small { display: block; color: #8793a6; font-size: .66rem; }
    .fea-activity-time { color: #8793a6; font-size: .66rem; white-space: nowrap; }
    .fea-completion { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 17px 19px; border: 1px solid #ddd5f8; border-left: 4px solid #5c38c5; border-radius: 11px; background: #faf8ff; }
    .fea-completion--done { border-color: #cce9dd; border-left-color: #1f936f; background: #f4fbf8; }
    .fea-completion h4 { margin: 0 0 3px; color: #2e3a51; font-size: .85rem; }
    .fea-completion p { margin: 0; color: #76839a; font-size: .72rem; }
    .fea-btn-primary { border-color: #5634bd; background: #5634bd; color: #fff; }
    .fea-btn-primary:hover, .fea-btn-primary:focus { border-color: #43279b; background: #43279b; color: #fff; }
    @media (max-width: 991.98px) {
        .fea-case-header { padding: 22px; }
        .fea-requirement-grid { grid-template-columns: 1fr; }
        .fea-stages { grid-template-columns: repeat(2, 1fr); gap: 16px 8px; }
        .fea-stage:nth-child(2)::after { display: none; }
    }
    @media (max-width: 575.98px) {
        .fea-case-header .d-flex { align-items: flex-start !important; flex-direction: column; }
        .fea-status { margin-top: 16px; }
        .fea-stages { grid-template-columns: 1fr; }
        .fea-stage::after { display: none; }
        .fea-completion { align-items: stretch; flex-direction: column; }
        .fea-completion form, .fea-completion .btn { width: 100%; }
        .fea-activity-item { grid-template-columns: 26px minmax(0, 1fr); }
        .fea-activity-time { grid-column: 2; }
    }
</style>

<div class="fea-workspace">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <a class="fea-back-link" href="{{ route($routePrefix.'.index') }}">
            <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i> Back to FEA Queue
        </a>
        @can('viewWorkflow', $case)
            <a href="{{ route('eclip.workflow.show', $case) }}" class="btn btn-outline-primary btn-sm">
                <i class="mdi mdi-timeline-text-outline mr-1" aria-hidden="true"></i> View Full Case Workflow
            </a>
        @endcan
    </div>

    <header class="fea-case-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="position-relative" style="z-index: 1;">
                <div class="fea-eyebrow">FEA Case Processing</div>
                <h1 class="fea-case-title">{{ $case->case_number }}</h1>
                <div class="fea-case-meta">
                    Beneficiary: {{ $formerRebel->classified_id }} <span class="mx-1" aria-hidden="true">&middot;</span>
                    {{ $formerRebel->municipality?->name ?? 'Municipality unavailable' }} <span class="mx-1" aria-hidden="true">&middot;</span>
                    {{ $workspace['assigned_office'] }}
                </div>
            </div>
            <div class="fea-status fea-status--{{ $workspace['status']['tone'] }}" role="status">
                <span class="fea-status-dot" aria-hidden="true"></span>{{ $workspace['status']['label'] }}
            </div>
        </div>
    </header>

    <section class="card fea-progress-card" aria-labelledby="case-progress-title">
        <div class="card-body">
            <div id="case-progress-title" class="fea-progress-label">Case Progress</div>
            <div class="fea-stages">
                @foreach($workspace['stages'] as $stage)
                    <div class="fea-stage fea-stage--{{ $stage['state'] }}" @if($stage['state'] === 'active') aria-current="step" @endif>
                        <span class="fea-stage-number">
                            @if($stage['state'] === 'complete')<i class="mdi mdi-check" aria-hidden="true"></i>@else{{ str_pad($stage['number'], 2, '0', STR_PAD_LEFT) }}@endif
                        </span>
                        <span class="fea-stage-copy">
                            <strong>{{ $stage['label'] }}</strong>
                            <small>{{ $stage['state'] === 'complete' ? 'Completed' : ($stage['state'] === 'active' ? 'Current step' : 'Upcoming') }}</small>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="row mt-4">
        <div class="col-lg-4 mb-4">
            <section class="card h-100" aria-labelledby="case-summary-title"><div class="card-body">
                <div class="fea-card-heading"><span class="fea-icon-box"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i></span><div><h2 id="case-summary-title" class="fea-section-title">Case Summary</h2><p class="fea-section-subtitle">Assigned beneficiary context.</p></div></div>
                <dl class="fea-summary-list">
                    <div class="fea-summary-row"><dt>Beneficiary ID</dt><dd>{{ $formerRebel->classified_id }}</dd></div>
                    <div class="fea-summary-row"><dt>Municipality</dt><dd>{{ $formerRebel->municipality?->name ?? 'Unavailable' }}</dd></div>
                    <div class="fea-summary-row"><dt>Case Type</dt><dd>E-CLIP / FEA</dd></div>
                    <div class="fea-summary-row"><dt>Assigned Office</dt><dd>{{ $workspace['assigned_office'] }}</dd></div>
                    <div class="fea-summary-row"><dt>Case Status</dt><dd><span class="fea-inline-status">{{ $workspace['status']['label'] }}</span></dd></div>
                    <div class="fea-summary-row"><dt>Last Updated</dt><dd>{{ $workspace['last_updated']?->format('M d, Y') ?? 'Unavailable' }}</dd></div>
                </dl>
                @can('viewWorkflow', $case)<a href="{{ route('eclip.workflow.show', $case) }}" class="btn btn-link px-0 mt-3 mb-0 font-weight-bold">View Full Case Workflow <i class="mdi mdi-arrow-right ml-1" aria-hidden="true"></i></a>@endcan
            </div></section>
        </div>

        <div class="col-lg-8 mb-4">
            <section id="fea-upload-card" class="card h-100" aria-labelledby="upload-document-title"><div class="card-body">
                <div class="fea-card-heading"><span class="fea-icon-box"><i class="mdi mdi-cloud-upload-outline" aria-hidden="true"></i></span><div><h2 id="upload-document-title" class="fea-section-title">Upload FEA Document</h2><p class="fea-section-subtitle">Add an authorized record to this case.</p></div></div>
                @if($workspace['can_upload'])
                    <form id="fea-upload-form" method="POST" action="{{ route($routePrefix.'.documents.store', $case) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="document_type" class="font-weight-bold">Document Type <span class="text-danger" aria-hidden="true">*</span></label>
                            <select id="document_type" name="document_type" class="form-control @error('document_type') is-invalid @enderror" required>
                                @foreach($workspace['type_labels'] as $value => $label)<option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>@endforeach
                            </select>
                            @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <label id="fea-upload-zone" class="fea-upload-zone @error('document') border-danger @enderror" for="document">
                            <input id="document" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required aria-describedby="fea-file-help fea-file-error">
                            <span><span class="fea-upload-icon"><i class="mdi mdi-cloud-upload-outline" aria-hidden="true"></i></span><strong id="fea-file-title">Upload Document</strong><p>Drag and drop your file here, or <span class="fea-browse">Browse Files</span></p><small id="fea-file-help">PDF, JPG, PNG &middot; Maximum 10 MB</small></span>
                        </label>
                        @error('document')<div id="fea-file-error" class="text-danger small mt-n2 mb-3" role="alert">{{ $message }}</div>@enderror
                        <button id="fea-upload-submit" type="submit" class="btn fea-btn-primary"><i class="mdi mdi-lock-outline mr-1" aria-hidden="true"></i> <span>Upload Secure Document</span></button>
                        <div class="fea-security-note"><i class="mdi mdi-shield-lock-outline" aria-hidden="true"></i><span><strong>Secure Document Upload.</strong> Files are stored privately and are accessible only to authorized personnel.</span></div>
                    </form>
                @else
                    <div class="alert alert-light border mb-0" role="status"><i class="mdi mdi-lock-outline mr-1" aria-hidden="true"></i>Document uploads are closed because this FEA activity is {{ str($activity->status)->replace('_', ' ') }}.</div>
                @endif
            </div></section>
        </div>
    </div>

    <section class="card mb-4" aria-labelledby="required-documents-title"><div class="card-body">
        <div class="d-flex flex-wrap align-items-end justify-content-between mb-2"><div><h2 id="required-documents-title" class="fea-section-title">Required Documents</h2><p class="fea-section-subtitle">Official FEA records required before verification.</p></div><strong class="text-primary mt-2 mt-sm-0">{{ $workspace['required_completed'] }} of {{ $workspace['required_total'] }} completed</strong></div>
        <div class="fea-requirement-progress" role="progressbar" aria-label="Required document completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $workspace['required_percent'] }}"><span style="width: {{ $workspace['required_percent'] }}%"></span></div>
        <div class="fea-requirement-grid">
            @foreach($workspace['required_documents'] as $requirement)
                <div class="fea-requirement {{ $requirement['uploaded'] ? 'fea-requirement--complete' : '' }}"><i class="mdi {{ $requirement['uploaded'] ? 'mdi-check' : 'mdi-file-document-outline' }}" aria-hidden="true"></i><span><strong>{{ $requirement['label'] }}</strong><small>{{ $requirement['uploaded'] ? 'Received' : 'Required' }}</small></span></div>
            @endforeach
        </div>
    </div></section>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <section class="card h-100" aria-labelledby="uploaded-documents-title">
                <div class="card-body pb-2"><div class="fea-card-heading mb-2"><span class="fea-icon-box"><i class="mdi mdi-file-document-box-multiple-outline" aria-hidden="true"></i></span><div><h2 id="uploaded-documents-title" class="fea-section-title">Uploaded FEA Documents</h2><p class="fea-section-subtitle">Authorized records securely attached to this case.</p></div></div></div>
                @if($workspace['documents']->isNotEmpty())
                    <div class="table-responsive"><table class="table fea-documents-table mb-0">
                        <thead><tr><th>Document</th><th>Type</th><th>Uploaded By</th><th>Date</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                        <tbody>@foreach($workspace['documents'] as $document)<tr>
                            <td><span class="fea-file-name"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i><span>{{ $document->original_name }}</span></span></td>
                            <td>{{ $document->typeLabel() }}</td><td>{{ $document->uploader?->name ?? 'Authorized personnel' }}</td><td>{{ $document->created_at->format('M d, Y') }}</td>
                            <td><span class="fea-received"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i> Received</span></td>
                            <td class="text-right"><a href="{{ route('eclip-fea.documents.download', $document) }}" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-eye-outline mr-1" aria-hidden="true"></i> View Document</a></td>
                        </tr>@endforeach</tbody>
                    </table></div>
                @else
                    <div class="fea-empty-state"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i><h4>No documents uploaded yet</h4><p>Upload the required FEA processing documents to continue this case.</p>@if($workspace['can_upload'])<button type="button" id="fea-upload-first" class="btn btn-outline-primary btn-sm">Upload First Document</button>@endif</div>
                @endif
            </section>
        </div>
        <div class="col-xl-4 mb-4">
            <section class="card h-100" aria-labelledby="case-activity-title"><div class="card-body">
                <div class="fea-card-heading"><span class="fea-icon-box"><i class="mdi mdi-history" aria-hidden="true"></i></span><div><h2 id="case-activity-title" class="fea-section-title">Case Activity</h2><p class="fea-section-subtitle">Recent FEA workflow events.</p></div></div>
                <ol class="fea-activity-list">
                    @forelse($workspace['activity_items'] as $item)
                        <li class="fea-activity-item"><span class="fea-activity-marker"><i class="mdi {{ $item['tone'] === 'upload' ? 'mdi-upload' : 'mdi-circle-medium' }}" aria-hidden="true"></i></span><span><strong>{{ $item['title'] }}</strong><small>{{ $item['detail'] ?: 'Official workflow record' }}</small></span><time class="fea-activity-time" datetime="{{ $item['occurred_at']->toAtomString() }}">{{ $item['occurred_at']->diffForHumans() }}</time></li>
                    @empty
                        <li class="text-muted small">No FEA activity has been recorded yet.</li>
                    @endforelse
                </ol>
            </div></section>
        </div>
    </div>

    @if(in_array($activity->status, ['completed', 'not_applicable'], true))
        <section class="fea-completion fea-completion--done mb-4" role="status"><div><h4><i class="mdi mdi-check-circle-outline text-success mr-1" aria-hidden="true"></i> {{ $activity->status === 'completed' ? 'FEA documents completed' : 'FEA processing marked not applicable' }}</h4><p>{{ $activity->status === 'completed' ? 'The required records were received and this activity has been completed.' : 'This outcome is recorded in the official case history.' }}</p></div><a href="{{ route('eclip.workflow.show', $case) }}" class="btn btn-outline-success">View Case Workflow <i class="mdi mdi-arrow-right ml-1" aria-hidden="true"></i></a></section>
    @elseif($workspace['can_complete'])
        <section class="fea-completion mb-4"><div><h4>All required documents are ready</h4><p>Review the uploaded records, then complete this FEA processing activity.</p></div><form method="POST" action="{{ route('eclip.workflow-activities.update', $activity) }}" onsubmit="return confirm('Complete this FEA processing activity? This action will be recorded in the official case history.');">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button type="submit" class="btn fea-btn-primary">Complete FEA Verification <i class="mdi mdi-arrow-right ml-1" aria-hidden="true"></i></button></form></section>
    @else
        <section class="fea-completion mb-4" role="status"><div><h4>Complete the required documents</h4><p>PTIS, TIR, and CVIF must be uploaded before this FEA activity can move forward.</p></div><button type="button" class="btn btn-outline-secondary" disabled>Continue to Verification <i class="mdi mdi-lock-outline ml-1" aria-hidden="true"></i></button></section>
    @endif
</div>
@endsection

@push('scripts')
@if($workspace['can_upload'])
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('document');
        const zone = document.getElementById('fea-upload-zone');
        const title = document.getElementById('fea-file-title');
        const submit = document.getElementById('fea-upload-submit');
        const form = document.getElementById('fea-upload-form');
        const firstUpload = document.getElementById('fea-upload-first');
        if (!input || !zone || !title || !submit) return;

        const updateSelection = function () {
            const file = input.files && input.files[0];
            title.textContent = file ? file.name : 'Upload Document';
            submit.disabled = !file;
            zone.classList.toggle('is-selected', Boolean(file));
        };
        ['dragenter', 'dragover'].forEach(function (eventName) {
            zone.addEventListener(eventName, function (event) { event.preventDefault(); zone.classList.add('is-dragging'); });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            zone.addEventListener(eventName, function (event) { event.preventDefault(); zone.classList.remove('is-dragging'); });
        });
        zone.addEventListener('drop', function (event) {
            if (event.dataTransfer && event.dataTransfer.files.length) { input.files = event.dataTransfer.files; updateSelection(); }
        });
        input.addEventListener('change', updateSelection);
        updateSelection();
        form.addEventListener('submit', function () {
            submit.disabled = true;
            submit.querySelector('span').textContent = 'Uploading securely...';
        });
        if (firstUpload) firstUpload.addEventListener('click', function () {
            document.getElementById('fea-upload-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
            window.setTimeout(function () { input.click(); }, 300);
        });
    });
</script>
@endif
@endpush
