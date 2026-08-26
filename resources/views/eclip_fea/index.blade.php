@extends('layouts.skydash-v')
@section('title', 'FEA Processing Queue')
@section('heading', 'FEA Processing Queue')

@section('content')
@if(request()->user()->hasRole('pnp'))
    <x-shield.module-header
        eyebrow="Case processing"
        title="FEA Processing Queue"
        description="Open assigned cases and upload authorized firearms, explosives, and ammunition records securely."
        icon="mdi-file-document-box-check-outline"
        role="PNP"
    />
@endif
<div class="card">
    <div class="card-body">
        <div class="shield-card-heading"><span class="shield-card-heading-icon"><i class="mdi mdi-format-list-checks" aria-hidden="true"></i></span><div><h3>Firearms, Explosives, and Ammunition Processing</h3><p>Only cases assigned to your office are shown. Uploads remain private and are available to the assigned LSWDO for monitoring.</p></div></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Case</th><th>Beneficiary ID</th><th>Municipality</th><th>Step status</th><th></th></tr></thead>
                <tbody>@forelse($cases as $case)
                    @php($activity = $case->workflowActivities->first())
                    <tr><td>{{ $case->case_number }}</td><td>{{ $case->formerRebel->classified_id }}</td><td>{{ $case->formerRebel->municipality?->name }}</td><td>{{ str($activity?->status ?? 'pending')->replace('_', ' ')->title() }}</td><td><a class="btn btn-sm btn-primary" href="{{ route(request()->user()->role.'.eclip-fea.show', $case) }}">Open</a></td></tr>
                @empty<tr><td colspan="5" class="text-center text-muted py-4">No assigned FEA cases require action.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $cases->links() }}
    </div>
</div>
@endsection
