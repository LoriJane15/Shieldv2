@extends('layouts.skydash-v')
@section('title', 'JAPIC Document Review')
@section('heading', 'JAPIC Document Processing')

@push('styles')
<style>
    .japic-case{--navy:#172b4d;--primary:#2f6fed;--border:#e7ecf3}.case-back{align-items:center;color:#64748b;display:inline-flex;font-size:.8rem;font-weight:600;margin-bottom:1.2rem}.case-back i{margin-right:.4rem}.case-back:hover{color:var(--primary);text-decoration:none}.case-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;overflow:hidden;padding:1.5rem;position:relative}.case-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:190px;position:absolute;right:-45px;top:-95px;width:190px}.case-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;opacity:.75;text-transform:uppercase}.case-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.case-meta{display:flex;flex-wrap:wrap;gap:.7rem 1.4rem}.case-meta span{align-items:center;display:inline-flex;font-size:.8rem;opacity:.9}.case-meta i{margin-right:.4rem}.case-status{background:rgba(255,255,255,.17);border:1px solid rgba(255,255,255,.28);border-radius:18px;font-size:.68rem;font-weight:700;padding:.4rem .75rem;position:relative;z-index:1}.requirements-heading{align-items:center;display:flex;justify-content:space-between;margin:1.5rem 0 1rem}.requirements-heading h3{color:var(--navy);font-size:1rem;font-weight:700}.requirements-heading p{color:#8492a6;font-size:.74rem}.requirements-count{background:#eaf1ff;border-radius:13px;color:var(--primary);font-size:.66rem;font-weight:700;padding:.3rem .6rem}.document-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.04);overflow:hidden}.document-card .card-body{padding:1.35rem}.document-header{align-items:flex-start;display:flex;justify-content:space-between}.document-heading{align-items:flex-start;display:flex;min-width:0}.document-icon{align-items:center;background:#eef4ff;border-radius:10px;color:var(--primary);display:flex;flex:0 0 42px;font-size:1.2rem;height:42px;justify-content:center;margin-right:1rem;width:42px}.document-title{color:var(--navy);font-size:.95rem;font-weight:700;margin:0 0 .25rem}.document-meta{align-items:center;color:#8492a6;display:flex;flex-wrap:wrap;font-size:.68rem;gap:.4rem .7rem}.requirement-badge,.document-status{border-radius:12px;font-size:.62rem;font-weight:700;padding:.25rem .5rem}.requirement-required{background:#fff1f2;color:#c2414f}.requirement-optional{background:#f1f5f9;color:#64748b}.status-missing{background:#f1f5f9;color:#64748b}.status-pending{background:#fff5df;color:#9a6700}.status-authenticated{background:#eaf1ff;color:#2f6fed}.status-certified{background:#e8f8f1;color:#16845e}.status-invalid{background:#fff1f2;color:#c2414f}.version-panel{align-items:center;background:#f8fafc;border:1px solid #edf1f6;border-radius:11px;display:flex;justify-content:space-between;margin-top:1.1rem;padding:.8rem .9rem}.version-info{align-items:center;display:flex;min-width:0}.version-info>i{color:#64748b;font-size:1.25rem;margin-right:.7rem}.version-info strong,.version-info small{display:block}.version-info strong{color:#334155;font-size:.76rem}.version-info small{color:#8492a6;font-size:.67rem;overflow-wrap:anywhere}.view-document{align-items:center;border-radius:8px;display:inline-flex;flex:0 0 auto;font-size:.7rem;font-weight:600;margin-left:1rem}.view-document i{margin-right:.3rem}.review-panel{border-top:1px solid #edf1f6;margin-top:1.1rem;padding-top:1.1rem}.field-label{color:#42526b;font-size:.74rem;font-weight:600}.field-help{color:#94a3b8;font-size:.65rem;font-weight:400}.form-control{border-color:#dfe5ee;border-radius:8px}.form-control:focus{border-color:#80a6ee;box-shadow:0 0 0 3px rgba(47,111,237,.1)}.review-submit{border-radius:8px;font-size:.74rem;font-weight:600;padding:.55rem .85rem}.review-submit i{margin-right:.3rem}.history-block{border-top:1px solid #edf1f6;margin-top:1.15rem;padding-top:1.1rem}.history-title{color:#42526b;font-size:.72rem;font-weight:700;margin-bottom:.8rem;text-transform:uppercase}.history-item{border-left:3px solid #d7e3f6;padding:0 0 1rem .9rem}.history-item:last-child{padding-bottom:0}.history-decision{color:#334155;font-size:.76rem;font-weight:700}.history-meta{color:#8492a6;font-size:.66rem;line-height:1.5;margin-top:.15rem}.history-remarks{background:#f8fafc;border-radius:7px;color:#64748b;font-size:.7rem;margin-top:.4rem;padding:.55rem .65rem}.missing-panel{align-items:center;background:#fafbfc;border:1px dashed #ccd5e2;border-radius:10px;color:#7d8ca1;display:flex;font-size:.72rem;margin-top:1rem;padding:.85rem}.missing-panel i{font-size:1.1rem;margin-right:.5rem}.no-requirements{background:#fff9e9;border:1px solid #f0dca6;border-radius:12px;color:#80601a;padding:1rem}
    @media(max-width:767px){.case-hero{padding:1.2rem}.case-hero h2{font-size:1.3rem}.case-status{margin-top:1rem}.case-meta{gap:.55rem 1rem}.document-card .card-body{padding:1.1rem}.document-header{gap:.8rem}.document-icon{margin-right:.75rem}.version-panel{align-items:stretch;flex-direction:column}.view-document{justify-content:center;margin: .8rem 0 0;width:100%}.review-submit{width:100%}}
</style>
@endpush

@section('content')
@php
    $documentsByRequirement = $case->documents->keyBy('requirement_id');
@endphp
<div class="japic-case">
    <a href="{{ route('japic.eclip.index') }}" class="case-back"><i class="mdi mdi-arrow-left"></i>Back to document review queue</a>
    <section class="case-hero mb-4" aria-labelledby="japic-case-number">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start position-relative" style="z-index:1">
            <div><div class="case-eyebrow mb-1">JAPIC document review</div><h2 id="japic-case-number" class="mb-3">{{ $case->case_number }}</h2><div class="case-meta"><span><i class="mdi mdi-account-key-outline"></i>{{ $case->formerRebel->classified_id }}</span><span><i class="mdi mdi-map-marker-outline"></i>{{ $case->formerRebel->municipality?->name ?? 'Municipality not assigned' }}</span><span><i class="mdi mdi-file-document-outline"></i>{{ $case->documents->count() }} {{ Str::plural('document', $case->documents->count()) }} submitted</span></div></div>
            <span class="case-status"><i class="mdi mdi-progress-check mr-1"></i>{{ $case->status->label() }}</span>
        </div>
    </section>

    <div class="requirements-heading"><div><h3 class="mb-1">Document Requirements</h3><p class="mb-0">Review each active requirement against its latest submitted version.</p></div><span class="requirements-count">{{ $requirements->count() }} requirements</span></div>

    @forelse($requirements as $requirement)
        @php
            $document = $documentsByRequirement->get($requirement->id);
            $documentStatus = $document?->status ?? 'missing';
        @endphp
        <article class="card document-card mb-3" aria-labelledby="requirement-{{ $requirement->id }}">
            <div class="card-body">
                <div class="document-header">
                    <div class="document-heading"><div class="document-icon"><i class="mdi mdi-file-document-outline"></i></div><div><h4 id="requirement-{{ $requirement->id }}" class="document-title">{{ $requirement->name }}</h4><div class="document-meta"><span class="requirement-badge {{ $requirement->is_required ? 'requirement-required' : 'requirement-optional' }}">{{ $requirement->is_required ? 'Required' : 'Optional' }}</span><span class="document-status status-{{ $documentStatus }}">{{ str($documentStatus)->replace('_', ' ')->title() }}</span></div></div></div>
                </div>

                @if($document && $document->latestVersion)
                    <div class="version-panel"><div class="version-info"><i class="mdi mdi-file-outline"></i><div><strong>Latest version · v{{ $document->latestVersion->version_number }}</strong><small>{{ $document->latestVersion->original_name }} · Uploaded {{ $document->latestVersion->created_at->format('M d, Y · h:i A') }}</small></div></div><a target="_blank" rel="noopener" href="{{ route('japic.eclip.documents.download', $document->latestVersion) }}" class="btn btn-sm btn-outline-primary view-document"><i class="mdi mdi-eye-outline"></i>View Document</a></div>

                    @can('reviewDocument', $case)
                        @if(in_array($document->status, ['pending', 'authenticated'], true))
                        <form class="review-panel" method="POST" action="{{ route('japic.eclip.documents.review', $document) }}" data-review-form>@csrf
                            <div class="form-row"><div class="form-group col-md-4"><label for="decision-{{ $document->id }}" class="field-label">Review Decision</label><select id="decision-{{ $document->id }}" name="decision" class="form-control" required><option value="">Select decision</option>@if($document->status === 'pending')<option value="authenticated">Authenticated and valid</option>@endif @if($document->status === 'authenticated')<option value="certified">Certify document</option>@endif @if(in_array($document->status, ['pending','authenticated']))<option value="invalid">Invalid / return</option>@endif</select></div><div class="form-group col-md-8"><label for="remarks-{{ $document->id }}" class="field-label">Remarks <span class="field-help">(required when invalid)</span></label><textarea id="remarks-{{ $document->id }}" name="remarks" rows="3" maxlength="5000" class="form-control" placeholder="Record verification findings or return instructions..."></textarea></div></div><div class="text-right"><button class="btn btn-primary review-submit" data-submit-button><i class="mdi mdi-content-save"></i>Record Review</button></div>
                        </form>
                        @endif
                    @endcan

                    @if($document->reviews->isNotEmpty())
                        <section class="history-block" aria-label="Review history for {{ $requirement->name }}"><h5 class="history-title"><i class="mdi mdi-history mr-1"></i>Review History</h5>@foreach($document->reviews->sortByDesc('reviewed_at') as $review)<div class="history-item"><div class="history-decision">{{ str($review->decision)->replace('_', ' ')->title() }}</div><div class="history-meta">{{ $review->reviewer->name }} · {{ $review->reviewed_at->format('M d, Y · h:i A') }}</div>@if($review->remarks)<div class="history-remarks">{{ $review->remarks }}</div>@endif</div>@endforeach</section>
                    @endif
                @else
                    <div class="missing-panel"><i class="mdi mdi-alert-circle-outline"></i><span>No document has been uploaded for this requirement.</span></div>
                @endif
            </div>
        </article>
    @empty
        <div class="no-requirements"><i class="mdi mdi-alert-outline mr-2"></i>No active document requirements are configured.</div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-review-form]').forEach(function(form){form.addEventListener('submit',function(){var button=form.querySelector('[data-submit-button]');button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Recording...';});});
</script>
@endpush
