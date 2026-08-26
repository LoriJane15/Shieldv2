@extends('layouts.skydash-v')
@section('title', 'FEA Processing')
@section('heading', 'FEA Processing')

@section('content')
@php($routePrefix = request()->user()->role.'.eclip-fea')
<a class="btn btn-link px-0 mb-3" href="{{ route($routePrefix.'.index') }}">← Back to queue</a>
@can('viewWorkflow', $case)<a href="{{ route('eclip.workflow.show', $case) }}" class="btn btn-outline-primary btn-sm float-right"><i class="mdi mdi-timeline-check-outline"></i> Open Full Workflow</a>@endcan
@if(request()->user()->hasRole('pnp'))
    <x-shield.module-header
        eyebrow="FEA case processing"
        :title="$case->case_number"
        description="Review the assigned beneficiary context and add only authorized FEA processing documents."
        icon="mdi-shield-check-outline"
        role="PNP"
    />
@endif
<div class="row">
    <div class="col-lg-5 mb-4"><div class="card"><div class="card-body">
        <h3>{{ $case->case_number }}</h3>
        <p class="mb-1"><strong>Beneficiary ID:</strong> {{ $case->formerRebel->classified_id }}</p>
        <p class="mb-0"><strong>Municipality:</strong> {{ $case->formerRebel->municipality?->name }}</p>
    </div></div></div>
    <div class="col-lg-7 mb-4"><div class="card"><div class="card-body">
        <h3>Upload FEA Record</h3>
        <p class="text-muted">Use PTIS, TIR, CVIF, or Other only for authorized FEA processing records.</p>
        <form method="POST" action="{{ route($routePrefix.'.documents.store', $case) }}" enctype="multipart/form-data">@csrf
            <div class="form-group"><label for="document_type">Document type</label><select id="document_type" name="document_type" class="form-control" required>@foreach(['ptis' => 'Property Turn-In Slip (PTIS)', 'tir' => 'Technical Inspection Report (TIR)', 'cvif' => 'Cost Valuation of Inventory Firearms (CVIF)', 'other' => 'Other authorized FEA record'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div class="form-group"><label for="document">Document</label><input id="document" type="file" name="document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required><small class="form-text text-muted">PDF, JPG, or PNG; maximum 10 MB.</small></div>
            <button class="btn btn-primary">Upload secure document</button>
        </form>
    </div></div></div>
</div>
<div class="card"><div class="card-body"><h3>Uploaded FEA Documents</h3><div class="table-responsive"><table class="table"><thead><tr><th>Type</th><th>File</th><th>Uploaded by</th><th>Date</th><th></th></tr></thead><tbody>@forelse($case->feaDocuments->sortByDesc('created_at') as $document)<tr><td>{{ str($document->document_type)->upper() }}</td><td>{{ $document->original_name }}</td><td>{{ $document->uploader->name }}</td><td>{{ $document->created_at->format('M d, Y h:i A') }}</td><td><a href="{{ route('eclip-fea.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">View</a></td></tr>@empty<tr><td colspan="5" class="text-muted">No FEA documents uploaded.</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
