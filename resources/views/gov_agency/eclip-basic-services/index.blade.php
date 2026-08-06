@extends('layouts.skydash-v')
@section('title', 'E-CLIP Referrals')
@section('heading', 'Assigned E-CLIP Basic-Service Referrals')

@section('content')
<div class="alert alert-info">Only referrals assigned to your agency are shown. Do not include unnecessary personal information in remarks or uploaded files.</div>
@forelse($services as $service)
<div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between"><div><h4>{{ $types[$service->service_type] ?? str($service->service_type)->headline() }}</h4><p class="text-muted mb-2">Case {{ $service->eclipCase->case_number }} · {{ $service->eclipCase->municipality?->name }}</p></div><span class="badge badge-info">{{ str($service->status)->replace('_', ' ')->title() }}</span></div>
    <form method="POST" action="{{ route('gov_agency.eclip.basic-services.update', $service) }}">@csrf @method('PUT')<div class="row"><div class="col-md-3 form-group"><label>Status</label><select name="status" class="form-control">@foreach(['pending', 'referred', 'in_progress', 'completed', 'not_applicable'] as $status)<option value="{{ $status }}" @selected($service->status === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>@endforeach</select></div><div class="col-md-9 form-group"><label>Delivery Remarks</label><textarea name="remarks" maxlength="5000" class="form-control">{{ $service->remarks }}</textarea></div></div><button class="btn btn-sm btn-primary">Update Delivery Status</button></form>
    <hr><form method="POST" action="{{ route('gov_agency.eclip.basic-services.documents.store', $service) }}" enctype="multipart/form-data" class="form-inline">@csrf<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="form-control-file mr-2"><button class="btn btn-sm btn-outline-primary">Upload Supporting Document</button></form>
    <div class="mt-2">@foreach($service->documents as $document)<a target="_blank" rel="noopener" class="mr-2" href="{{ route('gov_agency.eclip.basic-services.documents.download', $document) }}">View document v{{ $document->version_number }}</a>@endforeach</div>
</div></div>
@empty<div class="card"><div class="card-body text-muted">No E-CLIP referrals are assigned to your agency.</div></div>@endforelse
{{ $services->links() }}
@endsection
