@extends('layouts.skydash-v')
@section('title', 'E-CLIP Case')
@section('heading', 'E-CLIP Case Management')

@push('styles')
<style>
    .mblrc-case{--navy:#172b4d;--primary:#2f6fed;--border:#e7ecf3}.case-back{align-items:center;color:#64748b;display:inline-flex;font-size:.8rem;font-weight:600;margin-bottom:1.2rem}.case-back i{margin-right:.4rem}.case-back:hover{color:var(--primary);text-decoration:none}.case-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;overflow:hidden;padding:1.5rem;position:relative}.case-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:190px;position:absolute;right:-45px;top:-95px;width:190px}.case-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;opacity:.75;text-transform:uppercase}.case-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.case-meta{display:flex;flex-wrap:wrap;gap:.7rem 1.4rem}.case-meta span{align-items:center;display:inline-flex;font-size:.8rem;opacity:.9}.case-meta i{margin-right:.4rem}.case-status{background:rgba(255,255,255,.17);border:1px solid rgba(255,255,255,.28);border-radius:18px;font-size:.68rem;font-weight:700;padding:.4rem .75rem;position:relative;z-index:1}.overview-card,.panel-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045)}.overview-card .card-body,.panel-card .card-body{padding:1.4rem}.section-heading{align-items:center;display:flex;margin-bottom:1.2rem}.section-icon{align-items:center;background:#eaf1ff;border-radius:10px;color:var(--primary);display:flex;flex:0 0 40px;font-size:1.15rem;height:40px;justify-content:center;margin-right:1rem;width:40px}.section-title{color:var(--navy);font-size:1rem;font-weight:700;margin:0 0 .15rem}.section-subtitle{color:#8492a6;font-size:.72rem;margin:0}.overview-grid{display:grid;gap:.8rem;grid-template-columns:repeat(3,minmax(0,1fr))}.overview-item{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.85rem}.overview-item small{color:#8492a6;display:block;font-size:.64rem;font-weight:700;letter-spacing:.03em;text-transform:uppercase}.overview-item strong{color:#334155;display:block;font-size:.8rem;margin-top:.25rem;overflow-wrap:anywhere}.submit-panel{align-items:center;background:#eef5ff;border:1px solid #ccddfb;border-radius:12px;display:flex;justify-content:space-between;margin-top:1rem;padding:1rem}.submit-copy{align-items:flex-start;color:#476180;display:flex;font-size:.72rem;line-height:1.5}.submit-copy i{color:var(--primary);font-size:1.2rem;margin-right:.65rem}.submit-copy strong{color:#244a83;display:block;font-size:.78rem}.submit-button{border-radius:9px;flex:0 0 auto;font-size:.74rem;font-weight:600;margin-left:1rem;padding:.6rem .9rem}.submit-button i{margin-right:.35rem}.document-list{display:grid;gap:.85rem}.document-item{border:1px solid #e5eaf1;border-radius:12px;padding:1rem}.document-top{align-items:flex-start;display:flex;justify-content:space-between}.document-heading{align-items:center;display:flex;min-width:0}.document-icon{align-items:center;background:#f1f5f9;border-radius:9px;color:#64748b;display:flex;flex:0 0 38px;font-size:1.05rem;height:38px;justify-content:center;margin-right:.8rem;width:38px}.document-name{color:#334155;font-size:.8rem;font-weight:700;margin:0 0 .25rem}.requirement-badge,.status-badge{border-radius:11px;display:inline-flex;font-size:.6rem;font-weight:700;padding:.22rem .45rem}.requirement-required{background:#fff1f2;color:#c2414f}.requirement-optional{background:#f1f5f9;color:#64748b}.status-missing{background:#f1f5f9;color:#64748b}.status-pending{background:#fff5df;color:#9a6700}.status-authenticated{background:#eaf1ff;color:#2f6fed}.status-certified{background:#e8f8f1;color:#16845e}.status-invalid{background:#fff1f2;color:#c2414f}.version-links{align-items:center;color:#8492a6;display:flex;flex-wrap:wrap;font-size:.68rem;gap:.4rem;margin-top:.7rem}.version-links>i{margin-right:.1rem}.version-link{background:#eef4ff;border-radius:7px;color:#2f6fed;font-weight:600;padding:.25rem .45rem}.version-link:hover{background:#dfeaff;text-decoration:none}.upload-row{align-items:center;background:#f8fafc;border:1px dashed #b9c5d5;border-radius:10px;display:flex;justify-content:space-between;margin-top:.85rem;padding:.75rem}.file-picker{align-items:center;cursor:pointer;display:flex;margin:0;min-width:0}.file-picker-icon{align-items:center;background:#eaf1ff;border-radius:8px;color:#2f6fed;display:flex;flex:0 0 36px;font-size:1rem;height:36px;justify-content:center;margin-right:.65rem;width:36px}.file-picker strong,.file-picker small{display:block}.file-picker strong{color:#42526b;font-size:.72rem}.file-picker small{color:#8492a6;font-size:.64rem;overflow-wrap:anywhere}.file-input{border:0;clip:rect(0,0,0,0);height:1px;overflow:hidden;padding:0;position:absolute;white-space:nowrap;width:1px}.upload-button{border-radius:8px;flex:0 0 auto;font-size:.69rem;font-weight:600;margin-left:.8rem}.upload-button i{margin-right:.3rem}.document-note{align-items:flex-start;color:#8492a6;display:flex;font-size:.67rem;line-height:1.5;margin-top:1rem}.document-note i{color:#64748b;margin-right:.4rem}.empty-checklist{align-items:flex-start;background:#fff9e9;border:1px solid #f0dca6;border-radius:10px;color:#80601a;display:flex;font-size:.72rem;padding:.9rem}.empty-checklist i{font-size:1rem;margin-right:.5rem}.timeline{position:relative}.timeline-item{border-left:3px solid #d7e3f6;padding:0 0 1.2rem 1rem;position:relative}.timeline-item::before{background:#fff;border:3px solid #7fa3e8;border-radius:50%;content:'';height:11px;left:-7px;position:absolute;top:2px;width:11px}.timeline-item:last-child{padding-bottom:0}.timeline-status{color:#334155;font-size:.76rem;font-weight:700}.timeline-meta{color:#8492a6;font-size:.65rem;line-height:1.5;margin-top:.18rem}.timeline-remarks{background:#f8fafc;border-radius:7px;color:#64748b;font-size:.69rem;margin-top:.4rem;padding:.55rem .65rem}.empty-history{color:#8492a6;font-size:.72rem;padding:1.5rem 0;text-align:center}.empty-history i{display:block;font-size:1.8rem;margin-bottom:.35rem}
    @media(max-width:991px){.overview-grid{grid-template-columns:1fr}.history-column{margin-top:0}}
    @media(max-width:767px){.case-hero{padding:1.2rem}.case-hero h2{font-size:1.3rem}.case-status{margin-top:1rem}.case-meta{gap:.55rem 1rem}.overview-card .card-body,.panel-card .card-body{padding:1.1rem}.submit-panel,.upload-row{align-items:stretch;flex-direction:column}.submit-button,.upload-button{margin: .8rem 0 0;width:100%}.section-heading{align-items:flex-start}.document-top{gap:.7rem}.document-icon{margin-right:.65rem}}
</style>
@endpush

@section('content')
@php
    $documentsByRequirement = $case->documents->keyBy('requirement_id');
@endphp
<div class="mblrc-case">
    <a href="{{ route('mblrc.eclip.index') }}" class="case-back"><i class="mdi mdi-arrow-left"></i>Back to E-CLIP cases</a>

    <section class="case-hero mb-4" aria-labelledby="mblrc-case-number">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start position-relative" style="z-index:1">
            <div><div class="case-eyebrow mb-1">MBLRC case management</div><h2 id="mblrc-case-number" class="mb-3">{{ $case->case_number }}</h2><div class="case-meta"><span><i class="mdi mdi-account-key-outline"></i>{{ $case->formerRebel->classified_id }}</span><span><i class="mdi mdi-map-marker-outline"></i>{{ $case->formerRebel->municipality?->name ?? 'Municipality not assigned' }}</span><span><i class="mdi mdi-account-outline"></i>Created by {{ $case->creator->name }}</span></div></div>
            <span class="case-status"><i class="mdi mdi-progress-check mr-1"></i>{{ $case->status->label() }}</span>
        </div>
    </section>

    <div class="row">
        <div class="col-xl-8">
            <section class="card overview-card mb-4" aria-labelledby="overview-title"><div class="card-body">
                <div class="section-heading"><div class="section-icon"><i class="mdi mdi-clipboard-text-outline"></i></div><div><h3 id="overview-title" class="section-title">Case Overview</h3><p class="section-subtitle">Current ownership and processing information.</p></div></div>
                <div class="overview-grid"><div class="overview-item"><small>Beneficiary ID</small><strong>{{ $case->formerRebel->classified_id }}</strong></div><div class="overview-item"><small>Municipality</small><strong>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</strong></div><div class="overview-item"><small>Current Status</small><strong>{{ $case->status->label() }}</strong></div></div>
                <div class="submit-panel"><div class="submit-copy"><i class="mdi mdi-information-outline"></i><div><strong>LSWDO processing</strong><span>This case was created through an accepted referral. MBLRC access is monitoring-only after handoff.</span></div></div></div>
            </div></section>

            @can('uploadDocument', $case)
                <section class="card panel-card mb-4" aria-labelledby="documents-title"><div class="card-body">
                    <div class="section-heading"><div class="section-icon"><i class="mdi mdi-file-document-outline"></i></div><div><h3 id="documents-title" class="section-title">Supporting Documents</h3><p class="section-subtitle">Upload and manage the official documents required for JAPIC review.</p></div></div>
                    @if($requirements->isEmpty())
                        <div class="empty-checklist"><i class="mdi mdi-alert-outline"></i><span>The Katuparan Center has not configured the official document checklist.</span></div>
                    @else
                        <div class="document-list">
                        @foreach($requirements as $requirement)
                            @php
                                $document = $documentsByRequirement->get($requirement->id);
                                $documentStatus = $document?->status ?? 'missing';
                            @endphp
                            <article class="document-item">
                                <div class="document-top"><div class="document-heading"><div class="document-icon"><i class="mdi mdi-file-outline"></i></div><div><h4 class="document-name">{{ $requirement->name }}</h4><span class="requirement-badge {{ $requirement->is_required ? 'requirement-required' : 'requirement-optional' }}">{{ $requirement->is_required ? 'Required' : 'Optional' }}</span></div></div><span class="status-badge status-{{ $documentStatus }}">{{ str($documentStatus)->replace('_', ' ')->title() }}</span></div>
                                <div class="version-links"><i class="mdi mdi-history"></i><span>Versions:</span>@forelse($document?->versions?->sortByDesc('version_number') ?? [] as $version)<a class="version-link" href="{{ route('mblrc.eclip.documents.download', $version) }}">v{{ $version->version_number }}</a>@empty<span>None uploaded</span>@endforelse</div>
                                <form method="POST" action="{{ route('mblrc.eclip.documents.store', $case) }}" enctype="multipart/form-data" class="upload-row" data-upload-form>@csrf<input type="hidden" name="requirement_id" value="{{ $requirement->id }}"><label for="document-{{ $requirement->id }}" class="file-picker"><span class="file-picker-icon"><i class="mdi mdi-cloud-upload-outline"></i></span><span><strong>{{ $document ? 'Choose a replacement document' : 'Choose a document' }}</strong><small data-file-name>PDF, JPG, or PNG · Maximum 10 MB</small></span></label><input id="document-{{ $requirement->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="file-input" data-file-input><button class="btn btn-sm btn-outline-primary upload-button" data-upload-button><i class="mdi mdi-upload"></i>{{ $document ? 'Upload New Version' : 'Upload Document' }}</button></form>
                            </article>
                        @endforeach
                        </div>
                        <p class="document-note mb-0"><i class="mdi mdi-shield-check"></i><span>Replacing a file retains earlier versions for traceability and resets that document for JAPIC review.</span></p>
                    @endif
                </div></section>
            @endcan
        </div>

        <div class="col-xl-4 history-column">
            <section class="card panel-card mb-4" aria-labelledby="history-title"><div class="card-body">
                <div class="section-heading"><div class="section-icon"><i class="mdi mdi-history"></i></div><div><h3 id="history-title" class="section-title">Case History</h3><p class="section-subtitle">Traceable status changes throughout the workflow.</p></div></div>
                <div class="timeline">
                    @forelse($case->statusHistories->sortByDesc('created_at') as $history)
                        <article class="timeline-item"><div class="timeline-status">{{ $history->to_status->label() }}</div><div class="timeline-meta">{{ $history->created_at->format('M d, Y · h:i A') }}<br>Recorded by {{ $history->user->name }}</div>@if($history->remarks)<div class="timeline-remarks">{{ $history->remarks }}</div>@endif</article>
                    @empty
                        <div class="empty-history"><i class="mdi mdi-history"></i>No case history has been recorded.</div>
                    @endforelse
                </div>
            </div></section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-upload-form]').forEach(function(form){var input=form.querySelector('[data-file-input]');var name=form.querySelector('[data-file-name]');var button=form.querySelector('[data-upload-button]');input.addEventListener('change',function(){name.textContent=input.files.length?input.files[0].name:'PDF, JPG, or PNG · Maximum 10 MB';});form.addEventListener('submit',function(){button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Uploading...';});});var submitForm=document.querySelector('[data-submit-form]');if(submitForm){submitForm.addEventListener('submit',function(){var button=submitForm.querySelector('[data-submit-button]');button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Submitting...';});}
</script>
@endpush
