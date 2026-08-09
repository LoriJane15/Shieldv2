@extends('layouts.skydash-v')
@section('title', 'Integration Enrollments')
@section('heading', 'Integration Enrollments')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title">Start enrollment</h4>
            <p class="text-muted small">Use the beneficiary’s classified identifier. The master record is reused across clusters.</p>
            <form method="POST" action="{{ route('mblrc.enrollments.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="former_rebel_id">FR/FVE identifier</label>
                    <select class="form-select" id="former_rebel_id" name="former_rebel_id" required>
                        <option value="">Select beneficiary</option>
                        @foreach ($availableBeneficiaries as $beneficiary)
                            <option value="{{ $beneficiary->id }}" @selected(old('former_rebel_id') == $beneficiary->id)>{{ $beneficiary->classified_id }}</option>
                        @endforeach
                    </select>
                    @error('former_rebel_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="integration_started_at">Integration start date</label>
                    <input class="form-control" type="date" id="integration_started_at" name="integration_started_at" value="{{ old('integration_started_at') }}" required>
                    @error('integration_started_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <button class="btn btn-primary w-100">Start three-month integration</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title">My assigned enrollments</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Identifier</th><th>Started</th><th>Status</th><th>Referral</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td class="fw-semibold">{{ $enrollment->formerRebel->classified_id }}</td>
                            <td>{{ $enrollment->integration_started_at?->format('M j, Y') ?? '—' }}</td>
                            <td><span class="badge {{ $enrollment->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">{{ str($enrollment->status)->replace('_', ' ')->title() }}</span></td>
                            <td>{{ $enrollment->referral?->referral_number ?? '—' }}</td>
                            <td>
                                @if ($enrollment->status === 'in_progress')
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#complete-{{ $enrollment->id }}">Complete</button>
                                @endif
                            </td>
                        </tr>
                        @if ($enrollment->status === 'in_progress')
                        <tr class="collapse" id="complete-{{ $enrollment->id }}"><td colspan="5">
                            <form method="POST" action="{{ route('mblrc.enrollments.complete', $enrollment) }}" class="row g-3 p-3 bg-light rounded">
                                @csrf
                                <div class="col-md-6"><label class="form-label">Completion date</label><input type="date" name="integration_completed_at" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Verified municipality</label><select name="verified_municipality_id" class="form-select" required><option value="">Select municipality</option>@foreach($municipalities as $municipality)<option value="{{ $municipality->id }}">{{ $municipality->name }}</option>@endforeach</select></div>
                                <div class="col-md-6"><label class="form-label">Intention source</label><input name="phase_one_evidence[intention_to_surface][source]" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Intention source record</label><input name="phase_one_evidence[intention_to_surface][source_record]" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Coordination source</label><input name="phase_one_evidence[receiving_unit_coordination][source]" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Coordination source record</label><input name="phase_one_evidence[receiving_unit_coordination][source_record]" class="form-control" required></div>
                                <div class="col-12"><label class="form-label">Location verification remarks</label><textarea name="location_verification_remarks" class="form-control" rows="2"></textarea></div>
                                <div class="col-12 text-end"><button class="btn btn-success">Verify completion and create referral</button></div>
                            </form>
                        </td></tr>
                        @endif
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No assigned integration enrollments.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $enrollments->links() }}
        </div></div>
    </div>
</div>
@endsection
