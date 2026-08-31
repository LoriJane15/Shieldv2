@extends('layouts.skydash-v')
@section('title', 'FEA Record')
@section('heading', 'FEA Record')

@push('styles')
<style>
    .fea-workspace{--border:#e2e8f0}.fea-workspace-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;color:#fff;padding:1.5rem}.fea-workspace-hero h2{color:#fff;font-size:1.5rem;font-weight:700}.fea-panel{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.05)}.fea-panel .card-body{padding:1.25rem}.fea-title{border-bottom:1px solid #edf1f6;color:#172b4d;font-size:.95rem;font-weight:700;margin-bottom:1rem;padding-bottom:.65rem}.fea-summary{display:grid;gap:.85rem;grid-template-columns:repeat(3,minmax(0,1fr))}.fea-detail small{color:#718096;display:block;font-size:.65rem;font-weight:700;text-transform:uppercase}.fea-detail strong{color:#334155;font-size:.8rem}.pswdo-warning{background:#fff6e8;border:1px solid #f3d6a7;border-left:4px solid #d79228;border-radius:10px;color:#68481d;font-size:.78rem;padding:1rem}.requirement{align-items:center;border:1px solid #e8edf4;border-radius:10px;display:flex;gap:1rem;justify-content:space-between;margin-bottom:.65rem;padding:.85rem}.requirement-name{color:#334155;font-size:.78rem;font-weight:700}.requirement-meta{color:#718096;font-size:.68rem}.pending-badge{background:#f1f5f9;border-radius:14px;color:#52616f;font-size:.65rem;font-weight:700;padding:.3rem .55rem}.disabled-actions{background:#f7f9fc;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;padding:1rem}.disabled-actions button{cursor:not-allowed}.overall-badge{background:#fff5df;border-radius:16px;color:#8a6200;font-size:.7rem;font-weight:700;padding:.4rem .65rem}@media(max-width:767px){.fea-summary{grid-template-columns:1fr}.requirement{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
@php($record = $fea->surfacedFormerRebel)
<div class="fea-workspace">
    <header class="fea-workspace-hero mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap:1rem">
            <div><h2 class="mb-1">FEA Record {{ $record->reference_number }}</h2><p class="mb-0">Preliminary read-only workspace</p></div>
            <span class="overall-badge">{{ $fea->overallStatus()->value }}</span>
        </div>
    </header>

    <div class="pswdo-warning mb-4" role="alert"><strong>PSWDO dependency:</strong> {{ \App\Models\Ib39FeaProcessing::PSWDO_ACCESS_MESSAGE }}</div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <section class="card fea-panel h-100"><div class="card-body">
                <h3 class="fea-title">FR Summary</h3>
                <div class="fea-summary">
                    <div class="fea-detail"><small>Reference</small><strong>{{ $record->reference_number }}</strong></div>
                    <div class="fea-detail"><small>Category</small><strong>{{ $record->category->value }}</strong></div>
                    <div class="fea-detail"><small>Firearms</small><strong>{{ $record->possessed_firearms ? 'Yes' : 'No' }}</strong></div>
                    <div class="fea-detail"><small>Surfacing date</small><strong>{{ $record->surfaced_at->format('F d, Y') }}</strong></div>
                    <div class="fea-detail"><small>Municipality</small><strong>{{ $record->municipality->name }}</strong></div>
                    <div class="fea-detail"><small>Barangay</small><strong>{{ $record->barangay?->name ?? 'Not provided' }}</strong></div>
                </div>
                <h3 class="fea-title mt-4">Final Actions</h3>
                <div class="disabled-actions">
                    <p class="mb-2">Completion, finalization, final-copy upload, printing, and downloads are unavailable in this preliminary stage and blocked without a secure PSWDO link.</p>
                    <button class="btn btn-secondary btn-sm" type="button" disabled>Final actions unavailable</button>
                </div>
            </div></section>
        </div>
        <div class="col-lg-7 mb-4">
            <section class="card fea-panel h-100"><div class="card-body">
                <h3 class="fea-title">Document Requirements</h3>
                @foreach($fea->documents as $document)
                    <div class="requirement">
                        <div><div class="requirement-name">{{ $document->document_type->label() }}</div><div class="requirement-meta">{{ $document->is_required ? 'Required' : 'Optional' }} · Compliance: {{ $document->compliance_status->value }}</div></div>
                        <span class="pending-badge">{{ $document->status->value }}</span>
                    </div>
                @endforeach
            </div></section>
        </div>
    </div>
    <a class="btn btn-light" href="{{ route('ib39.fea.index') }}">Back to FEA Processing</a>
</div>
@endsection
