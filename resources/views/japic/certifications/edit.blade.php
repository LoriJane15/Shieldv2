@extends('layouts.skydash-v')
@section('title', 'Edit JAPIC Certification Draft')
@section('heading', 'Certification Draft Editor')

@push('styles')
<style>
    .fixed-narrative{font-family:Georgia,"Times New Roman",serif;font-size:1rem;line-height:2;text-align:justify}.fixed-narrative input{display:inline-block;border:0;border-bottom:1px solid #334155;border-radius:0;background:#fff;padding:.15rem .3rem;height:auto}.personnel-row{padding:1rem;border:1px solid #e2e8f0;border-radius:.5rem;background:#f8fafc;margin-bottom:.75rem}
</style>
@endpush

@section('content')
@php
    $source = $payload['source_snapshot'];
    $certificate = $payload['certificate'];
    $narrative = $certificate['narrative_values'];
    $prepared = old('certificate.prepared_by', $certificate['prepared_by'] ?: [['full_name' => '', 'rank' => '']]);
    $attested = old('certificate.attested_by', $certificate['attested_by'] ?: [['full_name' => '', 'rank' => '']]);
@endphp
<a href="{{ route('japic.certifications.show', $processing) }}" class="d-inline-block mb-3">← Back to certification profile</a>
<div class="alert alert-info" role="status">Authoritative source fields and certification wording are server-owned. Every meaningful save creates an encrypted immutable revision.</div>
@if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Private certification photograph</h2>
    @if($processing->currentPhotoVersion)<p>Selected version {{ $processing->currentPhotoVersion->version_number }} · {{ $processing->currentPhotoVersion->width }}×{{ $processing->currentPhotoVersion->height }} · <a href="{{ route('japic.certifications.photos.show', [$processing, $processing->currentPhotoVersion]) }}">View securely</a></p>@else<p class="text-muted">No JAPIC certification photograph has been selected. The frozen CDR photograph will be used when available.</p>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ route('japic.certifications.photos.store', $processing) }}">@csrf
        <input type="hidden" name="revision" value="{{ $processing->draft?->revision ?? 0 }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
        <div class="form-group"><label for="certification-photo">JPEG or PNG photograph</label><input id="certification-photo" class="form-control-file" type="file" name="photo" accept="image/jpeg,image/png,.jpg,.jpeg,.png" required><small class="form-text text-muted">Maximum 5 MiB, 100×100 minimum, 8000×8000 maximum, and 16 million pixels.</small></div>
        <button class="btn btn-outline-primary" type="submit">Upload and select photograph</button>
    </form>
</div></div>

<form method="POST" action="{{ route('japic.certifications.draft.update', $processing) }}">@csrf @method('PUT')
    <input type="hidden" name="revision" value="{{ $processing->draft?->revision ?? 0 }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
    <div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Official control information</h2><div class="form-row">
        <div class="form-group col-md-6"><label for="control-number">Control number</label><input id="control-number" class="form-control" name="control_number" maxlength="100" required @if(filled($processing->control_number)) readonly @endif value="{{ old('control_number', $processing->control_number) }}"></div>
        <div class="form-group col-md-6"><label for="date-issued">Date issued</label><input id="date-issued" class="form-control" type="date" name="certificate[date_issued]" value="{{ old('certificate.date_issued', $certificate['date_issued'] ?? '') }}"></div>
    </div></div></div>

    <div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Authoritative source snapshot</h2><dl class="row mb-0">
        @foreach(['fr_reference'=>'FR reference','subject_name'=>'Subject name','alias'=>'Alias','gender'=>'Gender','classification'=>'Classification','residential_address'=>'Residential address','former_position'=>'Former position','former_organization'=>'Former organization','affiliation_period'=>'Affiliation / recruitment period','surfacing_date'=>'Surfacing date','surfacing_location'=>'Surfacing location'] as $key=>$label)<dt class="col-md-4">{{ $label }}</dt><dd class="col-md-8">{{ $source[$key] ?? 'Unavailable' }}</dd>@endforeach
        <dt class="col-md-4">Operating areas</dt><dd class="col-md-8">{{ implode(', ', $source['operating_areas'] ?? []) ?: 'Unavailable' }}</dd>
    </dl></div></div>

    <div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Structured certification narrative</h2>
        <p class="fixed-narrative"><strong>THIS IS TO CERTIFY THAT</strong>
            <input aria-label="FR name" name="certificate[narrative_values][fr_name]" maxlength="255" required value="{{ old('certificate.narrative_values.fr_name', $narrative['fr_name'] ?? '') }}">, residing in
            <input aria-label="Residence" name="certificate[narrative_values][residence]" maxlength="1000" required value="{{ old('certificate.narrative_values.residence', $narrative['residence'] ?? '') }}">, is a former
            <input aria-label="Former organization or category" name="certificate[narrative_values][former_organization_or_category]" maxlength="500" required value="{{ old('certificate.narrative_values.former_organization_or_category', $narrative['former_organization_or_category'] ?? '') }}">, operating in the area/s of
            <input aria-label="Areas of operation" name="certificate[narrative_values][areas_of_operation]" maxlength="2000" required value="{{ old('certificate.narrative_values.areas_of_operation', $narrative['areas_of_operation'] ?? '') }}">. She started her affiliation with the
            <input aria-label="Affiliated organization" name="certificate[narrative_values][affiliated_organization]" maxlength="500" required value="{{ old('certificate.narrative_values.affiliated_organization', $narrative['affiliated_organization'] ?? '') }}"> and surrendered to
            <input aria-label="Office or organization surrendered to" name="certificate[narrative_values][surrendered_to]" maxlength="500" required value="{{ old('certificate.narrative_values.surrendered_to', $narrative['surrendered_to'] ?? '') }}"> on
            <input aria-label="Date surrendered" type="date" name="certificate[narrative_values][surrendered_on]" required value="{{ old('certificate.narrative_values.surrendered_on', $narrative['surrendered_on'] ?? '') }}"> at
            <input aria-label="Place surrendered" name="certificate[narrative_values][surrendered_at]" maxlength="1000" required value="{{ old('certificate.narrative_values.surrendered_at', $narrative['surrendered_at'] ?? '') }}">.
        </p>
        <p class="fixed-narrative">This certification is being issued to attest her legitimacy as a former rebel to support her application for the Enhanced Comprehensive Local Integration Program(E-CLIP).</p>
    </div></div>

    @foreach(['prepared_by' => ['Prepared By',$prepared], 'attested_by' => ['Attested By',$attested]] as $section => [$heading,$rows])
        <div class="card mb-4"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><h2 class="h6 font-weight-bold">{{ $heading }}</h2><button type="button" class="btn btn-sm btn-outline-secondary add-personnel" data-section="{{ $section }}">Add person</button></div>
            <div id="{{ $section }}-rows" data-personnel-section="{{ $section }}">
                @foreach($rows as $index => $row)<div class="personnel-row" data-personnel-row><div class="form-row"><div class="form-group col-md-7"><label>Full name</label><input class="form-control" name="certificate[{{ $section }}][{{ $index }}][full_name]" maxlength="255" required value="{{ $row['full_name'] ?? '' }}"></div><div class="form-group col-md-4"><label>Rank</label><input class="form-control" name="certificate[{{ $section }}][{{ $index }}][rank]" maxlength="100" required value="{{ $row['rank'] ?? '' }}"></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger remove-personnel mb-3" aria-label="Remove person">×</button></div></div></div>@endforeach
            </div>
        </div></div>
    @endforeach

    @if($processing->delayed)<div class="form-group"><label for="delay-reason">Delay reason</label><textarea id="delay-reason" class="form-control" name="delay_reason" maxlength="2000" required>{{ old('delay_reason') }}</textarea></div>@endif
    <button class="btn btn-primary" type="submit">Save encrypted draft</button>
</form>

<template id="personnel-template"><div class="personnel-row" data-personnel-row><div class="form-row"><div class="form-group col-md-7"><label>Full name</label><input class="form-control" data-field="full_name" maxlength="255" required></div><div class="form-group col-md-4"><label>Rank</label><input class="form-control" data-field="rank" maxlength="100" required></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger remove-personnel mb-3" aria-label="Remove person">×</button></div></div></div></template>
<script>
(() => {
    const maximum = {{ $maxPersonnelRows }};
    const reindex = section => section.querySelectorAll('[data-personnel-row]').forEach((row, index) => row.querySelectorAll('[data-field], input[name]').forEach(input => {
        const field = input.dataset.field || input.name.match(/\[([^\]]+)]$/)?.[1];
        input.name = `certificate[${section.dataset.personnelSection}][${index}][${field}]`;
    }));
    document.querySelectorAll('[data-personnel-section]').forEach(section => {
        section.addEventListener('click', event => { if (!event.target.closest('.remove-personnel')) return; if (section.querySelectorAll('[data-personnel-row]').length > 1) { event.target.closest('[data-personnel-row]').remove(); reindex(section); } });
        reindex(section);
    });
    document.querySelectorAll('.add-personnel').forEach(button => button.addEventListener('click', () => {
        const section = document.querySelector(`[data-personnel-section="${button.dataset.section}"]`);
        if (section.querySelectorAll('[data-personnel-row]').length >= maximum) return;
        section.append(document.getElementById('personnel-template').content.cloneNode(true)); reindex(section);
    }));
})();
</script>
@endsection
