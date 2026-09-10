@extends('layouts.skydash-v')
@section('title', 'FEA Processing Documents')
@section('heading', 'JAPIC Certification')

@section('content')
@php($fea = $processing->surfacedFormerRebel->feaProcessing)
<a href="{{ route('japic.certifications.show', $processing) }}" class="d-inline-block mb-3">&larr; Back to certification profile</a>
<section class="card"><div class="card-header"><h2 class="h5 mb-0">FEA Processing Documents</h2></div><div class="card-body">
    @forelse($documents as $document)
        <section class="border-bottom pb-3 mb-3"><h3 class="h6 font-weight-bold">{{ $document->document_type->label() }}</h3>
            @foreach(['currentDraftVersion', 'currentSupportingPhotoVersion', 'currentSurrenderedPhotoVersion'] as $relation)
                @if($version = $document->{$relation})
                    <div class="d-flex align-items-center justify-content-between flex-wrap py-2"><span>{{ $version->slot->label() }}</span><span><a class="btn btn-sm btn-outline-primary" href="{{ route('japic.fea.documents.versions.preview', [$fea, $document, $version]) }}">Secure preview</a> <a class="btn btn-sm btn-outline-secondary" href="{{ route('japic.fea.documents.versions.download', [$fea, $document, $version]) }}">Secure download</a></span></div>
                @endif
            @endforeach
        </section>
    @empty
        <p class="text-muted mb-0">No FEA processing documents are currently available for preview.</p>
    @endforelse
</div></section>
@endsection
