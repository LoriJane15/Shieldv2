@extends('layouts.skydash-v')
@section('title', 'Financial Monitoring')
@section('heading', 'E-CLIP Financial Monitoring')
@section('content')
<div class="card"><div class="card-body"><h3>Liquidation and Disbursement Monitoring</h3><table class="table"><thead><tr><th>Case</th><th>Beneficiary ID</th><th>Municipality</th><th></th></tr></thead><tbody>@forelse($cases as $case)<tr><td>{{ $case->case_number }}</td><td>{{ $case->formerRebel->classified_id }}</td><td>{{ $case->formerRebel->municipality?->name }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('dilg_reviewer.financial-monitoring.show', $case) }}">Open</a></td></tr>@empty<tr><td colspan="4" class="text-muted">No cases available.</td></tr>@endforelse</tbody></table>{{ $cases->links() }}</div></div>
@endsection
