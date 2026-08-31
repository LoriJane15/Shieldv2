@extends('layouts.skydash-v')
@section('title', 'CDR Workspace')
@section('heading', 'Custodial Debriefing Report')

@section('content')
<div class="row justify-content-center"><div class="col-xl-10">
    @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    <div class="card shadow-sm mb-4"><div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start" style="gap:1rem">
            <div><p class="text-muted text-uppercase small mb-1">CDR Workspace</p><h2 class="h4 mb-2">{{ $cdr->surfacedFormerRebel->reference_number }}</h2><p class="mb-0">Status: <strong>{{ $cdr->status->value }}</strong></p></div>
            <div class="d-flex flex-wrap" style="gap:.5rem">
                @if ($cdr->status === \App\Enums\Ib39CdrStatus::Pending)
                    <form method="POST" action="{{ route('ib39.cdr.start', $cdr) }}">@csrf<button class="btn btn-primary" type="submit">Start processing</button></form>
                @else<a class="btn btn-primary" href="{{ route('ib39.cdr.edit', $cdr) }}">Edit draft</a>@endif
                <a class="btn btn-outline-primary" href="{{ route('ib39.cdr.preview', $cdr) }}">Preview draft</a>
                <a class="btn btn-outline-secondary" href="{{ route('ib39.cdr.print', $cdr) }}" target="_blank" rel="noopener">Print draft</a>
            </div>
        </div>
    </div></div>
    <div class="card shadow-sm mb-4"><div class="card-body"><h3 class="h5">Draft details</h3><dl class="row mb-0">
        <dt class="col-sm-4">Schema version</dt><dd class="col-sm-8">{{ $cdr->form?->schema_version ?? 1 }}</dd>
        <dt class="col-sm-4">Last saved</dt><dd class="col-sm-8">{{ $cdr->form?->updated_at?->format('F d, Y h:i A') ?? 'Not yet saved' }}</dd>
        <dt class="col-sm-4">Last editor</dt><dd class="col-sm-8">{{ $cdr->form?->lastEditor?->name ?? 'Not available' }}</dd>
    </dl></div></div>
    <a class="btn btn-light" href="{{ route('ib39.fr-profiles.show', $cdr->surfacedFormerRebel) }}">Back to FR profile</a>
</div></div>
@endsection
