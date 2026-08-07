@extends('layouts.skydash-v')
@section('title', 'Document Preview')
@section('heading', 'Secure Document Preview')

@push('styles')
<style>
    .preview-page { --preview-border: #e7ecf3; --preview-navy: #172b4d; --preview-primary: #2f6fed; }
    .preview-back { align-items: center; color: #64748b; display: inline-flex; font-size: .82rem; font-weight: 600; gap: .4rem; margin-bottom: 1rem; }
    .preview-back:hover { color: var(--preview-primary); text-decoration: none; }
    .preview-header { border: 1px solid var(--preview-border); border-radius: 14px; box-shadow: 0 4px 16px rgba(23, 43, 77, .045); overflow: hidden; }
    .preview-header .card-body { padding: 1.35rem; }
    .preview-heading { align-items: flex-start; display: flex; gap: .8rem; min-width: 0; }
    .preview-file-icon { align-items: center; background: #eaf1ff; border-radius: 11px; color: var(--preview-primary); display: inline-flex; flex: 0 0 44px; font-size: 1.25rem; height: 44px; justify-content: center; width: 44px; }
    .preview-kicker { color: #8492a6; font-size: .67rem; font-weight: 700; letter-spacing: .08em; margin: 0 0 .2rem; text-transform: uppercase; }
    .preview-title { color: var(--preview-navy); font-size: 1.15rem; font-weight: 700; line-height: 1.3; margin: 0 0 .2rem; }
    .preview-filename { color: #718096; font-size: .76rem; margin: 0; overflow-wrap: anywhere; }
    .preview-actions { display: flex; flex: 0 0 auto; gap: .55rem; }
    .preview-actions .btn { align-items: center; border-radius: 8px; display: inline-flex; gap: .4rem; justify-content: center; }
    .document-meta { border-top: 1px solid #edf1f6; display: grid; gap: .75rem; grid-template-columns: repeat(3, minmax(0, 1fr)); margin: 1.15rem 0 0; padding-top: 1rem; }
    .document-meta-item { align-items: center; display: flex; gap: .6rem; min-width: 0; }
    .document-meta-icon { align-items: center; background: #f2f5f9; border-radius: 8px; color: #718096; display: inline-flex; flex: 0 0 34px; height: 34px; justify-content: center; width: 34px; }
    .document-meta-label, .document-meta-value { display: block; }
    .document-meta-label { color: #94a3b8; font-size: .61rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .document-meta-value { color: #42526b; font-size: .75rem; font-weight: 600; overflow-wrap: anywhere; }
    .document-preview-shell { background: #eef1f6; border: 1px solid #d9deea; border-radius: 14px; min-height: 68vh; overflow: hidden; }
    .document-preview-frame { background: #fff; border: 0; min-height: 68vh; width: 100%; }
    .document-preview-image-wrap { align-items: center; display: flex; justify-content: center; min-height: 68vh; padding: 2rem; }
    .document-preview-image { border-radius: 7px; box-shadow: 0 8px 30px rgba(25, 35, 55, .18); max-height: 72vh; max-width: 100%; }
    .privacy-note { align-items: center; color: #718096; display: flex; font-size: .7rem; gap: .4rem; margin: .7rem .15rem 0; }
    .privacy-note i { align-items: center; display: inline-flex; flex: 0 0 20px; justify-content: center; width: 20px; }
    @media (max-width: 991px) { .preview-header-row { align-items: stretch !important; } .preview-actions { margin-top: 1rem; } }
    @media (max-width: 767px) { .preview-header .card-body { padding: 1.1rem; } .preview-actions { flex-direction: column; } .preview-actions .btn { width: 100%; } .document-meta { grid-template-columns: 1fr 1fr; } .document-preview-image-wrap { padding: 1rem; } }
    @media (max-width: 420px) { .document-meta { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="preview-page">
    <a href="{{ $backUrl }}" class="preview-back"><i class="mdi mdi-arrow-left"></i><span>Back to case</span></a>

    <section class="card preview-header mb-3" aria-labelledby="preview-title">
        <div class="card-body">
            <div class="preview-header-row d-flex flex-column flex-lg-row justify-content-between align-items-lg-start">
                <div class="preview-heading">
                    <span class="preview-file-icon"><i class="mdi {{ str_starts_with($mimeType, 'image/') ? 'mdi-file-image-outline' : 'mdi-file-pdf-box' }}"></i></span>
                    <div>
                        <p class="preview-kicker">Secure document · {{ $case->case_number }}</p>
                        <h2 id="preview-title" class="preview-title">{{ $title }}</h2>
                        <p class="preview-filename">{{ $filename }}</p>
                    </div>
                </div>
                <div class="preview-actions">
                    <a href="{{ $contentUrl }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="mdi mdi-open-in-new"></i><span>Open Full Size</span></a>
                </div>
            </div>

            <div class="document-meta">
                <div class="document-meta-item"><span class="document-meta-icon"><i class="mdi mdi-file-restore-outline"></i></span><span><span class="document-meta-label">Version</span><span class="document-meta-value">v{{ $versionNumber }}</span></span></div>
                <div class="document-meta-item"><span class="document-meta-icon"><i class="mdi mdi-progress-check"></i></span><span><span class="document-meta-label">Status</span><span class="document-meta-value">{{ str($status)->replace('_', ' ')->title() }}</span></span></div>
                <div class="document-meta-item"><span class="document-meta-icon"><i class="mdi mdi-file-code-outline"></i></span><span><span class="document-meta-label">File type</span><span class="document-meta-value">{{ $mimeType }}</span></span></div>
                <div class="document-meta-item"><span class="document-meta-icon"><i class="mdi mdi-database-outline"></i></span><span><span class="document-meta-label">File size</span><span class="document-meta-value">{{ number_format($sizeBytes / 1024, 1) }} KB</span></span></div>
                <div class="document-meta-item"><span class="document-meta-icon"><i class="mdi mdi-account-outline"></i></span><span><span class="document-meta-label">Uploaded by</span><span class="document-meta-value">{{ $uploadedBy ?? 'Unknown user' }}</span></span></div>
                <div class="document-meta-item"><span class="document-meta-icon"><i class="mdi mdi-calendar-clock-outline"></i></span><span><span class="document-meta-label">Uploaded</span><span class="document-meta-value">{{ $uploadedAt?->format('M d, Y · h:i A') ?? '—' }}</span></span></div>
            </div>
        </div>
    </section>

    <div class="document-preview-shell" aria-label="Preview of {{ $filename }}">
        @if(str_starts_with($mimeType, 'image/'))
            <div class="document-preview-image-wrap"><img src="{{ $contentUrl }}" class="document-preview-image" alt="Preview of {{ $title }}"></div>
        @else
            <iframe src="{{ $contentUrl }}#toolbar=1&navpanes=0" class="document-preview-frame" title="Preview of {{ $title }}"></iframe>
        @endif
    </div>
    <p class="privacy-note"><i class="mdi mdi-lock-outline"></i><span>This private document is available only to authorized users. Browser caching is disabled.</span></p>
</div>
@endsection
