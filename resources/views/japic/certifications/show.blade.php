@extends('layouts.skydash-v')
@section('title', 'JAPIC Certification Profile')
@section('heading', 'JAPIC Certification')

@section('content')
@php
    $fr = $processing->surfacedFormerRebel;
    $cdr = $fr->cdrProcessing;
    $finalCdr = $cdr?->currentFinalVersion;
    $fea = $fr->feaProcessing;
@endphp
<a href="{{ route('japic.certifications.index') }}" class="d-inline-block mb-3">← Back to FRs for Certification</a>

@if($fr->cancellation)
    <div class="alert alert-warning"><strong>FR cancelled {{ $fr->cancellation->cancelled_at->format('M d, Y h:i A') }}</strong><div>Reason: {{ $fr->cancellation->reason }}</div></div>
@endif

<div class="card mb-4"><div class="card-body d-flex justify-content-between flex-wrap">
    <div><span class="text-muted small">SURFACED FR PROFILE</span><h1 class="h4 mb-1">{{ $fr->display_name }}</h1><strong>{{ $fr->reference_number }}</strong></div>
    <div class="text-right"><span class="badge badge-info">{{ $processing->status->value }}</span><div class="mt-2 {{ $processing->delayed ? 'text-danger' : 'text-success' }}">{{ $processing->deadline_label }}</div></div>
</div></div>

<section class="card mb-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div><h2 class="h6 font-weight-bold">Certification draft</h2>
            <p class="mb-2">{{ $processing->draft ? 'Encrypted revision '.$processing->draft->revision : 'No draft has been saved.' }}</p></div>
        <div>
            @can('editDraft', $processing)<a class="btn btn-sm btn-primary" href="{{ route('japic.certifications.draft.edit', $processing) }}">{{ $processing->draft ? 'Continue editing' : 'Create draft' }}</a>@endcan
            @can('previewDraft', $processing)<a class="btn btn-sm btn-outline-primary" href="{{ route('japic.certifications.preview', $processing) }}">Preview</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('japic.certifications.print', $processing) }}">Print draft</a>@endcan
        </div>
    </div>
    @if($processing->status === \App\Enums\JapicCertificationStatus::Drafting && $processing->draft)
        @can('submitForSigning', $processing)<form method="POST" action="{{ route('japic.certifications.submit-for-signing', $processing) }}" class="mt-3">@csrf
            <input type="hidden" name="revision" value="{{ $processing->draft->revision }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
            @if($processing->delayed)<label for="submit-delay">Delay reason</label><textarea id="submit-delay" name="delay_reason" class="form-control mb-2" maxlength="2000" required></textarea>@endif
            <button class="btn btn-warning" type="submit">Submit / Mark for Signing</button>
        </form>@endcan
    @elseif($processing->status === \App\Enums\JapicCertificationStatus::ForSigning && $processing->draft)
        @can('confirmSigningComplete', $processing)<form method="POST" action="{{ route('japic.certifications.signing-complete', $processing) }}" class="mt-3">@csrf
            <input type="hidden" name="revision" value="{{ $processing->draft->revision }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
            @if($processing->delayed)<label for="signing-delay">Delay reason</label><textarea id="signing-delay" name="delay_reason" class="form-control mb-2" maxlength="2000" required></textarea>@endif
            <div class="form-check mb-2"><input id="signing-complete" class="form-check-input" type="checkbox" name="signing_complete" value="1" required><label class="form-check-label" for="signing-complete">I confirm physical signing is complete.</label></div>
            <button class="btn btn-success" type="submit">Confirm Signing Complete</button>
        </form>@endcan
    @endif
    @if($processing->draftHistories->isNotEmpty())<h3 class="h6 mt-4">Immutable draft revisions</h3>@foreach($processing->draftHistories as $revision)<div class="border-top py-2">Revision {{ $revision->revision }} · {{ $revision->saved_at->format('M d, Y h:i A') }} · {{ $revision->savedBy?->name ?? 'System' }}</div>@endforeach @endif
</div></section>

<div class="row">
    <div class="col-lg-6 mb-4"><section class="card h-100"><div class="card-body">
        <h2 class="h6 font-weight-bold">Surfacing information</h2>
        <dl class="row mb-0">
            <dt class="col-sm-4">Category</dt><dd class="col-sm-8">{{ $fr->category->value }}{{ $fr->other_category_specification ? ' — '.$fr->other_category_specification : '' }}</dd>
            <dt class="col-sm-4">Surfaced</dt><dd class="col-sm-8">{{ $fr->surfaced_at->format('F d, Y') }}</dd>
            <dt class="col-sm-4">Area</dt><dd class="col-sm-8">{{ $fr->specific_location ? $fr->specific_location.', ' : '' }}{{ $fr->barangay?->name ? $fr->barangay->name.', ' : '' }}{{ $fr->municipality->name }}, {{ $fr->province }}</dd>
            <dt class="col-sm-4">Firearms</dt><dd class="col-sm-8">{{ $fr->possessed_firearms ? 'Yes' : 'No' }}</dd>
            <dt class="col-sm-4">Overall status</dt><dd class="col-sm-8">{{ $fr->overall_case_status }}</dd>
        </dl>
    </div></section></div>
    <div class="col-lg-6 mb-4"><section class="card h-100"><div class="card-body">
        <h2 class="h6 font-weight-bold">Certification timing</h2>
        <dl class="row mb-0">
            <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ $processing->status->value }}</dd>
            <dt class="col-sm-5">Received</dt><dd class="col-sm-7">{{ $processing->received_at->format('F d, Y') }}</dd>
            <dt class="col-sm-5">Due</dt><dd class="col-sm-7">{{ $processing->due_at->format('F d, Y') }}</dd>
            <dt class="col-sm-5">Timing</dt><dd class="col-sm-7">{{ $processing->deadline_label }}</dd>
        </dl>
        @foreach($processing->histories->whereNotNull('delay_reason') as $history)
            <div class="alert alert-secondary mt-2 mb-0"><strong>Delay remark:</strong> {{ $history->delay_reason }}</div>
        @endforeach
    </div></section></div>
</div>

<section class="card mb-4"><div class="card-body">
    <h2 class="h6 font-weight-bold">Final CDR</h2>
    @if($cdr && $finalCdr)
        <p class="mb-2">Status: <strong>{{ $cdr->status->value }}</strong> · Completed: {{ $cdr->completed_at?->format('F d, Y h:i A') ?? 'Unavailable' }} · Version {{ $finalCdr->version_number }} · {{ $finalCdr->original_filename }}</p>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('japic.cdr.documents.preview', [$cdr, $finalCdr]) }}">Secure preview</a>
        @if($finalCdr->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded)
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('japic.cdr.documents.download', [$cdr, $finalCdr]) }}">Secure download</a>
        @endif
        <h3 class="h6 mt-4">CDR history</h3>
        @forelse($cdr->statusHistories->sortByDesc('created_at')->take(50) as $history)
            <div class="border-top py-2">{{ $history->event }} · {{ $history->to_status->value }} · {{ $history->created_at->format('M d, Y h:i A') }}</div>
        @empty<p class="text-muted mb-0">No CDR history is available.</p>@endforelse
    @else
        <p class="text-muted mb-0">No authoritative current final CDR is available.</p>
    @endif
</div></section>

<section class="card mb-4"><div class="card-body">
    <h2 class="h6 font-weight-bold">FEA information</h2>
    @if(!$fr->possessed_firearms)
        <p class="text-muted mb-0">FEA is not applicable.</p>
    @elseif(!$fea)
        <p class="text-muted mb-0">No FEA processing record is available.</p>
    @else
        <p>Overall status: <strong>{{ $fea->overallStatus()->value }}</strong></p>
        @foreach($fea->documents as $document)
            <div class="border-top py-3">
                <strong>{{ $document->document_type->label() }}</strong> · {{ $document->status->value }}
                <div class="mt-2">
                    @foreach(['currentDraftVersion', 'currentSupportingPhotoVersion', 'currentSurrenderedPhotoVersion'] as $relation)
                        @if($version = $document->{$relation})
                            <a class="btn btn-sm btn-outline-primary mr-1" href="{{ route('japic.fea.documents.versions.preview', [$fea, $document, $version]) }}">Preview {{ $version->slot->label() }}</a>
                            <a class="btn btn-sm btn-outline-secondary mr-1" href="{{ route('japic.fea.documents.versions.download', [$fea, $document, $version]) }}">Download {{ $version->slot->label() }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div></section>

<section class="card"><div class="card-body"><h2 class="h6 font-weight-bold">Assistance</h2><p class="text-muted mb-0">No linked assistance record available.</p></div></section>
@endsection
