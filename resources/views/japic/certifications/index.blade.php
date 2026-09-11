@extends('layouts.skydash-v')
@section('title', 'FRs for Certification')
@section('heading', 'JAPIC Certification')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div><h2 class="h4 font-weight-bold mb-1">FRs for Certification</h2><p class="text-muted mb-0">Eligible completed CDR records received by JAPIC.</p></div>
</div>

<section class="card">
    <form method="GET" action="{{ route('japic.certifications.index') }}" class="card-body border-bottom">
        <div class="row">
            <div class="col-lg-7 mb-2"><label for="search" class="small font-weight-bold">Reference or name</label><input id="search" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}" class="form-control"></div>
            <div class="col-lg-3 mb-2"><label for="status" class="small font-weight-bold">JAPIC status</label><select id="status" name="status" class="form-control"><option value="">All</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->value }}</option>@endforeach</select></div>
            <div class="col-lg-2 mb-2 d-flex align-items-end"><button class="btn btn-primary mr-2" type="submit">Filter</button><a class="btn btn-light" href="{{ route('japic.certifications.index') }}">Clear</a></div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Reference / FR</th><th>Category</th><th>Surfacing</th><th>Area</th><th>Overall FR status</th><th>JAPIC certification status</th><th></th></tr></thead>
            <tbody>
            @forelse($records as $processing)
                @php($fr = $processing->surfacedFormerRebel)
                <tr>
                    <td><strong>{{ $fr->reference_number }}</strong><br><span class="text-muted">{{ $fr->display_name }}</span></td>
                    <td>{{ $fr->category->value }}</td>
                    <td>{{ $fr->surfaced_at->format('M d, Y') }}</td>
                    <td>{{ $fr->barangay?->name ? $fr->barangay->name.', ' : '' }}{{ $fr->municipality->name }}</td>
                    <td><span class="badge badge-primary">{{ $fr->overall_case_status }}</span></td>
                    <td>
                        @if($processing->delayed)
                            <span class="badge badge-danger" role="status" aria-label="JAPIC certification status: Delayed">Delayed</span>
                        @else
                            <span class="badge badge-info">{{ $processing->status->value }}</span>
                        @endif
                    </td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('japic.certifications.show', $processing) }}">View Profile</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No certification tasks match the approved filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())<div class="card-body border-top">{{ $records->onEachSide(1)->links() }}</div>@endif
</section>
@endsection
