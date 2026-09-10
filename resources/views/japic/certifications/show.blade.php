@extends('layouts.skydash-v')
@section('title', 'JAPIC Certification Profile')
@section('heading', 'JAPIC Certification')

@section('content')
@php($fr = $processing->surfacedFormerRebel)
<div class="fr-profile-container">
    <div class="module-nav-top">
        <a href="{{ route('japic.certifications.index') }}" class="module-back-link"><i class="mdi mdi-arrow-left"></i><span>Back to FRs for Certification</span></a>
        <ol class="module-breadcrumb" aria-label="Breadcrumb"><li><a href="{{ route('japic.dashboard') }}">Dashboard</a></li><li class="separator"><i class="mdi mdi-chevron-right"></i></li><li><a href="{{ route('japic.certifications.index') }}">Certifications</a></li><li class="separator"><i class="mdi mdi-chevron-right"></i></li><li class="active">{{ $fr->reference_number }}</li></ol>
    </div>

    <x-surfaced-fr-profile :record="$fr" information-heading="FR Profile Information" :show-status-tiles="false" :show-workflow-summary="false" />

    <section class="profile-card mb-4" aria-labelledby="overall-status-heading"><div class="profile-card-header"><h2 id="overall-status-heading"><i class="mdi mdi-clipboard-check-outline"></i>Overall FR Status</h2></div><div class="profile-card-body"><span class="badge-pill-status badge-pill-case">{{ $fr->overall_case_status }}</span></div></section>

    <section class="profile-card mb-4" aria-labelledby="certification-status-heading"><div class="profile-card-header"><h2 id="certification-status-heading"><i class="mdi mdi-timeline-check-outline"></i>JAPIC Certification Status and Timeline</h2><span class="badge badge-info">{{ $processing->status->value }}</span></div><div class="profile-card-body">
        @forelse($processing->histories as $history)
            <div class="history-card-item mb-2"><div class="history-icon"><i class="mdi mdi-check"></i></div><div><strong>{{ $history->event->value }}</strong><div class="text-muted">{{ $history->from_status?->value ?? 'Started' }} &rarr; {{ $history->to_status->value }} &middot; {{ $history->occurred_at->format('M d, Y h:i A') }} &middot; {{ $history->actor?->name ?? 'System' }}</div></div></div>
        @empty
            <p class="text-muted mb-0">No certification status events have been recorded.</p>
        @endforelse
    </div></section>

    <section class="profile-card mb-4" aria-labelledby="documents-heading"><div class="profile-card-header"><h2 id="documents-heading"><i class="mdi mdi-folder-multiple-outline"></i>Documents/Records</h2></div><div class="profile-card-body"><div class="workflow-tile-list">
        <a class="workflow-tile text-decoration-none" href="{{ route('japic.certifications.records.cdr', $processing) }}"><strong>CDR</strong><span>View the current authoritative final record</span></a>
        <a class="workflow-tile text-decoration-none" href="{{ route('japic.certifications.records.fea', $processing) }}"><strong>FEA Processing Documents</strong><span>View available processing documents</span></a>
        <a class="workflow-tile text-decoration-none" href="{{ route('japic.certifications.records.assistance', $processing) }}"><strong>Assistance Records</strong><span>View securely linked assistance information</span></a>
    </div></div></section>

    <section class="profile-card mb-4" aria-labelledby="certification-actions-heading"><div class="profile-card-header"><h2 id="certification-actions-heading"><i class="mdi mdi-file-certificate-outline"></i>JAPIC Certification Actions</h2></div><div class="profile-card-body">
        @if($processing->status === \App\Enums\JapicCertificationStatus::Drafting && $processing->draft)
            @can('submitForSigning', $processing)<form method="POST" action="{{ route('japic.certifications.submit-for-signing', $processing) }}">@csrf
                <input type="hidden" name="revision" value="{{ $processing->draft->revision }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
                @if($processing->delayed)<label for="submit-delay">Delay reason</label><textarea id="submit-delay" name="delay_reason" class="form-control mb-2" maxlength="2000" required></textarea>@endif
                <button class="btn btn-warning" type="submit">Submit / Mark for Signing</button>
            </form>@endcan
        @elseif($processing->status === \App\Enums\JapicCertificationStatus::ForSigning && $processing->draft)
            @can('confirmSigningComplete', $processing)<form method="POST" action="{{ route('japic.certifications.signing-complete', $processing) }}">@csrf
                <input type="hidden" name="revision" value="{{ $processing->draft->revision }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
                @if($processing->delayed)<label for="signing-delay">Delay reason</label><textarea id="signing-delay" name="delay_reason" class="form-control mb-2" maxlength="2000" required></textarea>@endif
                <div class="form-check mb-2"><input id="signing-complete" class="form-check-input" type="checkbox" name="signing_complete" value="1" required><label class="form-check-label" for="signing-complete">I confirm physical signing is complete.</label></div>
                <button class="btn btn-success" type="submit">Confirm Signing Complete</button>
            </form>@endcan
        @else
            <p class="text-muted mb-0">No certification status action is currently available.</p>
        @endif
    </div></section>

    <section class="profile-card mb-4" aria-labelledby="draft-actions-heading"><div class="profile-card-header"><h2 id="draft-actions-heading"><i class="mdi mdi-file-document-edit-outline"></i>Draft, Preview, and Print</h2></div><div class="profile-card-body"><p class="text-muted">{{ $processing->draft ? 'Encrypted revision '.$processing->draft->revision : 'No draft has been saved.' }}</p><div class="profile-footer-actions">
        @can('editDraft', $processing)<a class="btn-action-primary" href="{{ route('japic.certifications.draft.edit', $processing) }}">{{ $processing->draft ? 'Continue editing' : 'Create draft' }}</a>@endcan
        @can('previewDraft', $processing)<a class="btn-action-secondary" href="{{ route('japic.certifications.preview', $processing) }}">Preview</a><a class="btn-action-secondary" href="{{ route('japic.certifications.print', $processing) }}">Print draft</a>@endcan
    </div></div></section>

    <section class="profile-card" aria-labelledby="history-action-heading"><div class="profile-card-header"><h2 id="history-action-heading"><i class="mdi mdi-history"></i>Certification Draft History</h2></div><div class="profile-card-body"><a class="btn-action-secondary" href="{{ route('japic.certifications.history', $processing) }}">View History</a></div></section>
</div>
@endsection
