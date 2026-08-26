@extends('layouts.skydash-v')
@section('title', 'Authentication Queue')
@section('heading', 'Authentication Queue')

@section('content')
<div class="japic-authentication-page">
    <x-shield.module-header
        eyebrow="Authentication"
        title="Assigned Authentication Requests"
        description="Review only requests explicitly assigned to your account and record an auditable decision."
        icon="mdi-shield-check-outline"
        role="JAPIC"
    />
<div class="card"><div class="card-body">
    <div class="shield-card-heading"><span class="shield-card-heading-icon"><i class="mdi mdi-format-list-checks" aria-hidden="true"></i></span><div><h4>Authentication Queue</h4><p>Only requests explicitly assigned to your account are displayed.</p></div></div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Case</th><th>Beneficiary ID</th><th>Requested</th><th>Due</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($requests as $authentication)
            <tr>
                <td>{{ $authentication->eclipCase->case_number }}</td>
                <td>{{ $authentication->eclipCase->formerRebel->classified_id }}</td>
                <td>{{ $authentication->requested_at->format('M j, Y') }}</td>
                <td>{{ $authentication->due_at?->format('M j, Y') ?? '—' }}</td>
                <td><span class="badge {{ $authentication->status === 'authenticated' ? 'bg-success' : ($authentication->status === 'under_review' ? 'bg-primary' : 'bg-warning text-dark') }}">{{ str($authentication->status)->replace('_', ' ')->title() }}</span></td>
                <td>
                    @if($authentication->status === 'pending')
                        <form method="POST" action="{{ route('japic.authentication.start', $authentication) }}">@csrf<button class="btn btn-sm btn-primary">Start review</button></form>
                    @elseif($authentication->status === 'under_review')
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#decision-{{ $authentication->id }}">Record decision</button>
                    @else
                        <span class="text-muted small">Decision recorded</span>
                    @endif
                </td>
            </tr>
            @if($authentication->status === 'under_review')
            <tr class="collapse" id="decision-{{ $authentication->id }}"><td colspan="6">
                <form method="POST" action="{{ route('japic.authentication.decide', $authentication) }}" class="row g-3 p-3 bg-light rounded">
                    @csrf
                    <div class="col-md-4"><label class="form-label">Decision</label><select name="decision" class="form-select" required><option value="authenticated">Authenticated</option><option value="returned">Returned</option><option value="not_authenticated">Not Authenticated</option></select></div>
                    <div class="col-md-4"><label class="form-label">Certification reference</label><input name="certification_reference" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
                    <div class="col-12 text-end"><button class="btn btn-success">Submit explicit decision</button></div>
                </form>
            </td></tr>
            @endif
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No authentication requests are assigned to you.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $requests->links() }}
</div></div>
</div>
@endsection
