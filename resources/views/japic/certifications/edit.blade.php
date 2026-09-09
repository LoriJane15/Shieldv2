@extends('layouts.skydash-v')
@section('title', 'Edit JAPIC Certification Draft')
@section('heading', 'Certification Draft Editor')
@section('content')
@php $source=$payload['source_snapshot']; $certificate=$payload['certificate']; $signatories=$payload['signatories']; @endphp
<a href="{{ route('japic.certifications.show', $processing) }}" class="d-inline-block mb-3">← Back to certification profile</a>
<div class="alert alert-info">Authoritative FR and final-CDR fields are read-only. Every changed save creates an encrypted immutable revision.</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('japic.certifications.draft.update', $processing) }}">@csrf @method('PUT')
<input type="hidden" name="revision" value="{{ $processing->draft?->revision ?? 0 }}"><input type="hidden" name="lock_version" value="{{ $processing->lock_version }}">
<div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Official control information</h2><div class="form-row">
<div class="form-group col-md-6"><label>Control number</label><input class="form-control" name="control_number" maxlength="100" required value="{{ old('control_number', $processing->control_number) }}"></div>
<div class="form-group col-md-6"><label>Date issued</label><input class="form-control" type="date" name="certificate[date_issued]" value="{{ old('certificate.date_issued', $certificate['date_issued'] ?? '') }}"></div>
</div></div></div>
<div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Authoritative source snapshot</h2><dl class="row mb-0">
@foreach(['fr_reference'=>'FR reference','subject_name'=>'Subject name','alias'=>'Alias','gender'=>'Gender','classification'=>'Classification','residential_address'=>'Residential address','former_position'=>'Former position','former_organization'=>'Former organization','affiliation_period'=>'Affiliation / recruitment period','surfacing_date'=>'Surfacing date','surfacing_location'=>'Surfacing location'] as $key=>$label)<dt class="col-md-4">{{ $label }}</dt><dd class="col-md-8">{{ $source[$key] ?? 'Unavailable' }}</dd>@endforeach
<dt class="col-md-4">Operating areas</dt><dd class="col-md-8">{{ implode(', ', $source['operating_areas'] ?? []) ?: 'Unavailable' }}</dd></dl></div></div>
<div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Certification facts</h2><div class="form-row">
@foreach(['surrendering_unit'=>'Surrendering unit','surrender_location'=>'Confirmed surrender location','operating_area_supplement'=>'Optional operating-area supplement'] as $key=>$label)<div class="form-group col-md-6"><label>{{ $label }}</label><textarea class="form-control" name="certificate[{{ $key }}]" maxlength="{{ $key === 'operating_area_supplement' ? 4000 : ($key === 'surrender_location' ? 1000 : 255) }}">{{ old('certificate.'.$key, $certificate[$key] ?? '') }}</textarea></div>@endforeach
<div class="form-group col-md-6"><label>Confirmed surrender date</label><input class="form-control" type="date" name="certificate[surrender_date]" value="{{ old('certificate.surrender_date', $certificate['surrender_date'] ?? $source['surfacing_date'] ?? '') }}"></div>
</div><p class="text-muted mb-0">The official E-CLIP purpose statement is fixed document wording only and is not connected to an application module.</p></div></div>
<div class="card mb-4"><div class="card-body"><h2 class="h6 font-weight-bold">Printed signatories</h2><div class="row">@foreach($positions as $key=>$position)<div class="col-lg-6 mb-3"><h3 class="h6">{{ $position }}</h3><div class="form-row"><div class="form-group col-4"><label>Rank</label><input class="form-control" name="signatories[{{ $key }}][rank]" maxlength="100" value="{{ old('signatories.'.$key.'.rank', $signatories[$key]['rank'] ?? '') }}"></div><div class="form-group col-5"><label>Printed name</label><input class="form-control" name="signatories[{{ $key }}][name]" maxlength="255" value="{{ old('signatories.'.$key.'.name', $signatories[$key]['name'] ?? '') }}"></div><div class="form-group col-3"><label>Suffix</label><input class="form-control" name="signatories[{{ $key }}][suffix]" maxlength="100" value="{{ old('signatories.'.$key.'.suffix', $signatories[$key]['suffix'] ?? '') }}"></div></div></div>@endforeach</div></div></div>
@if($processing->delayed)<div class="form-group"><label>Delay reason</label><textarea class="form-control" name="delay_reason" maxlength="2000" required>{{ old('delay_reason') }}</textarea></div>@endif
<button class="btn btn-primary" type="submit">Save encrypted draft</button></form>
@endsection
