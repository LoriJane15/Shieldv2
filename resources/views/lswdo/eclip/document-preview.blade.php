@extends('layouts.skydash-v')
@section('title', 'Document Preview')
@section('heading', 'Secure Document Preview')

@push('styles')
<style>
    .document-preview-shell { background: #eef1f6; border: 1px solid #d9deea; border-radius: 12px; min-height: 68vh; overflow: hidden; }
    .document-preview-frame { width: 100%; min-height: 68vh; border: 0; background: #fff; }
    .document-preview-image-wrap { min-height: 68vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
    .document-preview-image { max-width: 100%; max-height: 72vh; border-radius: 6px; box-shadow: 0 8px 30px rgba(25, 35, 55, .18); }
    .document-meta dt { color: #6c7585; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="card mb-3"><div class="card-body">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start">
        <div><p class="text-uppercase text-muted small mb-1">{{ $case->case_number }}</p><h3 class="mb-1">{{ $title }}</h3><p class="text-muted mb-0 text-break">{{ $filename }}</p></div>
        <div class="mt-3 mt-lg-0"><a href="{{ $backUrl }}" class="btn btn-outline-secondary mr-2"><i class="ti-arrow-left"></i> Back</a><a href="{{ $contentUrl }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="ti-new-window"></i> Open Full Size</a></div>
    </div>
    <hr><dl class="row document-meta mb-0">
        <dt class="col-sm-2">Version</dt><dd class="col-sm-4">v{{ $versionNumber }}</dd>
        <dt class="col-sm-2">Status</dt><dd class="col-sm-4"><span class="badge badge-info">{{ str($status)->replace('_', ' ')->title() }}</span></dd>
        <dt class="col-sm-2">File type</dt><dd class="col-sm-4">{{ $mimeType }}</dd>
        <dt class="col-sm-2">File size</dt><dd class="col-sm-4">{{ number_format($sizeBytes / 1024, 1) }} KB</dd>
        <dt class="col-sm-2">Uploaded by</dt><dd class="col-sm-4">{{ $uploadedBy ?? 'Unknown user' }}</dd>
        <dt class="col-sm-2">Uploaded</dt><dd class="col-sm-4">{{ $uploadedAt?->format('M d, Y · h:i A') ?? '—' }}</dd>
    </dl>
</div></div>

<div class="document-preview-shell" aria-label="Preview of {{ $filename }}">
    @if(str_starts_with($mimeType, 'image/'))
        <div class="document-preview-image-wrap"><img src="{{ $contentUrl }}" class="document-preview-image" alt="Preview of {{ $title }}"></div>
    @else
        <iframe src="{{ $contentUrl }}#toolbar=1&navpanes=0" class="document-preview-frame" title="Preview of {{ $title }}"></iframe>
    @endif
</div>
<p class="text-muted small mt-2"><i class="ti-lock mr-1"></i>This document is private and available only to authorized users. Browser caching is disabled.</p>
@endsection
