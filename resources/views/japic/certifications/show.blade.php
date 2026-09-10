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
<div class="fr-profile-container">
    <div class="module-nav-top">
        <a href="{{ route('japic.certifications.index') }}" class="module-back-link"><i class="mdi mdi-arrow-left"></i><span>Back to FRs for Certification</span></a>
        <ol class="module-breadcrumb" aria-label="Breadcrumb"><li><a href="{{ route('japic.dashboard') }}">Dashboard</a></li><li class="separator"><i class="mdi mdi-chevron-right"></i></li><li><a href="{{ route('japic.certifications.index') }}">Certifications</a></li><li class="separator"><i class="mdi mdi-chevron-right"></i></li><li class="active">{{ $fr->reference_number }}</li></ol>
    </div>

    <x-surfaced-fr-profile :record="$fr" />

    <section class="profile-card mb-4" aria-labelledby="certification-workspace-heading"><div class="profile-card-header"><h2 id="certification-workspace-heading"><i class="mdi mdi-file-certificate-outline"></i>JAPIC Certification Workspace</h2><span class="badge badge-info">{{ $processing->status->value }}</span></div><div class="profile-card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div><strong>Certification draft</strong><p class="mb-2 text-muted">{{ $processing->draft ? 'Encrypted revision '.$processing->draft->revision : 'No draft has been saved.' }}</p></div>
            <div class="mb-2">
                @can('editDraft', $processing)<a class="btn btn-sm btn-primary" href="{{ route('japic.certifications.draft.edit', $processing) }}">{{ $processing->draft ? 'Continue editing' : 'Create draft' }}</a>@endcan
                @can('previewDraft', $processing)<a class="btn btn-sm btn-outline-primary" href="{{ route('japic.certifications.preview', $processing) }}">Preview</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('japic.certifications.print', $processing) }}">Print draft</a>@endcan
            </div>
        </div>
        <dl class="row mb-3"><dt class="col-md-3">Received</dt><dd class="col-md-3">{{ $processing->received_at->format('F d, Y') }}</dd><dt class="col-md-3">Due</dt><dd class="col-md-3">{{ $processing->due_at->format('F d, Y') }}</dd></dl>
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
        @if($processing->photoVersions->isNotEmpty())
            <h3 class="h6 mt-4">Private certification photographs</h3>
            @foreach($processing->photoVersions as $photo)
                <div class="border-top py-2">Version {{ $photo->version_number }} · {{ $photo->width }}×{{ $photo->height }} · {{ $photo->uploaded_at->format('M d, Y h:i A') }} <a href="{{ route('japic.certifications.photos.show', [$processing, $photo]) }}">View securely</a>@if($processing->current_photo_version_id === $photo->id) <span class="badge badge-success">Selected</span>@endif</div>
            @endforeach
        @endif
        @if($processing->draftHistories->isNotEmpty())<h3 class="h6 mt-4">Immutable draft revisions</h3>@foreach($processing->draftHistories as $revision)<div class="border-top py-2">Revision {{ $revision->revision }} · {{ $revision->saved_at->format('M d, Y h:i A') }} · {{ $revision->savedBy?->name ?? 'System' }}</div>@endforeach @endif
        @foreach($processing->histories->whereNotNull('delay_reason') as $history)<div class="alert alert-secondary mt-2 mb-0"><strong>Delay remark:</strong> {{ $history->delay_reason }}</div>@endforeach
    </div></section>

    <section class="profile-card mb-4"><div class="profile-card-header"><h2><i class="mdi mdi-file-document-check-outline"></i>Current Final CDR</h2></div><div class="profile-card-body">
        @if($cdr && $finalCdr)
            <p>Status: <strong>{{ $cdr->status->value }}</strong> · Completed: {{ $cdr->completed_at?->format('F d, Y h:i A') ?? 'Unavailable' }} · Version {{ $finalCdr->version_number }} · {{ $finalCdr->original_filename }}</p>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('japic.cdr.documents.preview', [$cdr, $finalCdr]) }}">Secure preview</a>
            @if($finalCdr->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded)<a class="btn btn-sm btn-outline-secondary" href="{{ route('japic.cdr.documents.download', [$cdr, $finalCdr]) }}">Secure download</a>@endif
        @else
            <p class="text-muted mb-0">No authoritative current final CDR is available.</p>
        @endif
    </div></section>

    <section class="profile-card mb-4"><div class="profile-card-header"><h2><i class="mdi mdi-file-check-outline"></i>FEA Information</h2></div><div class="profile-card-body">
        @if(!$fr->possessed_firearms)<p class="text-muted mb-0">FEA is not applicable.</p>
        @elseif(!$fea)<p class="text-muted mb-0">No FEA processing record is available.</p>
        @else
            <p>Overall status: <strong>{{ $fea->overallStatus()->value }}</strong></p>
            @foreach($fea->documents as $document)<div class="border-top py-3"><strong>{{ $document->document_type->label() }}</strong> · {{ $document->status->value }}<div class="mt-2">
                @foreach(['currentDraftVersion', 'currentSupportingPhotoVersion', 'currentSurrenderedPhotoVersion'] as $relation)@if($version = $document->{$relation})<a class="btn btn-sm btn-outline-primary mr-1" href="{{ route('japic.fea.documents.versions.preview', [$fea, $document, $version]) }}">Preview {{ $version->slot->label() }}</a><a class="btn btn-sm btn-outline-secondary mr-1" href="{{ route('japic.fea.documents.versions.download', [$fea, $document, $version]) }}">Download {{ $version->slot->label() }}</a>@endif @endforeach
            </div></div>@endforeach
        @endif
    </div></section>

    <section class="profile-card"><div class="profile-card-header"><h2><i class="mdi mdi-hand-heart-outline"></i>Assistance</h2></div><div class="profile-card-body"><p class="text-muted mb-0">No linked assistance record available.</p></div></section>
</div>
@endsection
