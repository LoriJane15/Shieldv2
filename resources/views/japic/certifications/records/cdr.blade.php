@extends('layouts.skydash-v')
@section('title', 'CDR Record')
@section('heading', 'JAPIC Certification')

@section('content')
@php
    $cdr = $processing->surfacedFormerRebel->cdrProcessing;
    $finalCdr = $cdr?->currentFinalVersion;
@endphp
<a href="{{ route('japic.certifications.show', $processing) }}" class="d-inline-block mb-3">&larr; Back to certification profile</a>
<section class="card"><div class="card-header"><h2 class="h5 mb-0">CDR</h2></div><div class="card-body">
    @if($cdr && $finalCdr)
        <p>This is the current authoritative final CDR for {{ $processing->surfacedFormerRebel->reference_number }}.</p>
        <a class="btn btn-outline-primary" href="{{ route('japic.cdr.documents.preview', [$cdr, $finalCdr]) }}">Secure preview</a>
        @if($finalCdr->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded)
            <a class="btn btn-outline-secondary" href="{{ route('japic.cdr.documents.download', [$cdr, $finalCdr]) }}">Secure download</a>
        @endif
    @else
        <p class="text-muted mb-0">No CDR document is currently available for preview.</p>
    @endif
</div></section>
@endsection
