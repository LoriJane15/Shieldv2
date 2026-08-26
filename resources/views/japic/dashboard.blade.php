@extends('layouts.skydash-v')
@section('title', 'JAPIC Dashboard')
@section('heading', 'JAPIC Dashboard')

@push('styles')
<style>
    .japic-dashboard { --japic-primary:#2f6fed; --japic-navy:#172b4d; --japic-border:#e5eaf2; margin:0 auto; max-width:1500px; }
    .japic-hero { background:linear-gradient(125deg,#173b74,#2f6fed); border-radius:15px; color:#fff; overflow:hidden; padding:1.45rem 1.55rem; position:relative; }
    .japic-hero::after { background:rgba(255,255,255,.08); border-radius:50%; content:''; height:220px; position:absolute; right:-55px; top:-110px; width:220px; }
    .japic-hero h2 { color:#fff; font-size:1.5rem; font-weight:750; }
    .japic-hero p { font-size:.82rem; opacity:.88; }
    .japic-summary { display:grid; gap:.8rem; grid-template-columns:repeat(4,1fr); margin:1rem 0; }
    .japic-stat { border:1px solid var(--japic-border); border-radius:12px; box-shadow:0 4px 14px rgba(23,43,77,.04); }
    .japic-stat .card-body { align-items:center; display:flex; gap:.8rem; padding:1rem; }
    .stat-icon { align-items:center; background:#eaf1ff; border-radius:10px; color:var(--japic-primary); display:flex; flex:0 0 42px; font-size:1.1rem; height:42px; justify-content:center; }
    .japic-stat strong { color:var(--japic-navy); display:block; font-size:1.25rem; line-height:1; }
    .japic-stat small { color:#78879b; display:block; font-size:.68rem; margin-top:.3rem; }
    .japic-workspace { border:1px solid var(--japic-border); border-radius:12px; box-shadow:0 4px 14px rgba(23,43,77,.04); overflow:hidden; }
    .workspace-heading { align-items:center; border-bottom:1px solid #edf1f6; display:flex; justify-content:space-between; padding:1rem 1.1rem; }
    .workspace-heading h3 { color:var(--japic-navy); font-size:.92rem; font-weight:750; margin:0; }
    .workspace-heading p { color:#8492a6; font-size:.7rem; margin:.18rem 0 0; }
    .request-table { margin:0; }
    .request-table th { background:#f7f9fc; border:0; color:#718096; font-size:.66rem; letter-spacing:.04em; text-transform:uppercase; }
    .request-table td { border-color:#edf1f6; color:#52616f; font-size:.76rem; vertical-align:middle; }
    .request-status { background:#eaf1ff; border-radius:999px; color:#2f6fed; display:inline-flex; font-size:.64rem; font-weight:700; padding:.3rem .55rem; }
    .empty-state { color:#8492a6; font-size:.75rem; padding:2.5rem 1rem; text-align:center; }
    @media(max-width:991.98px){.japic-summary{grid-template-columns:repeat(2,1fr)}.japic-hero>div{align-items:flex-start!important;flex-direction:column}}
    @media(max-width:575.98px){.japic-summary{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="japic-dashboard">
    <header class="japic-hero"><div class="d-flex align-items-center position-relative" style="z-index:1"><div><div class="shield-module-eyebrow">Official Workflow · Step 4A</div><h2 class="mb-1">JAPIC Authentication and Certification</h2><p class="mb-0"><i class="mdi mdi-shield-check-outline mr-1" aria-hidden="true"></i>Start assigned reviews, record explicit decisions, and maintain certification references within the official deadline.</p></div></div></header>

    <section class="japic-summary" aria-label="JAPIC assigned workload summary">
        @foreach([
            ['Pending authentication', $summary['pending'], 'mdi-shield-outline'],
            ['Under review', $summary['under_review'], 'mdi-progress-clock'],
            ['Overdue', $summary['overdue'], 'mdi-clock-alert-outline'],
            ['Document cases', $summary['document_cases'], 'mdi-file-document-box-check-outline'],
        ] as [$label,$value,$icon])
            <div class="card japic-stat"><div class="card-body"><span class="stat-icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></span><div><strong>{{ number_format($value) }}</strong><small>{{ $label }}</small></div></div></div>
        @endforeach
    </section>

    <section class="card japic-workspace" aria-labelledby="recent-authentication-title"><div class="workspace-heading"><div><h3 id="recent-authentication-title">Recent Authentication Assignments</h3><p>Only requests explicitly assigned to your JAPIC account are shown.</p></div><a href="{{ route('japic.authentication.index') }}" class="btn btn-sm btn-outline-primary">View all</a></div>
        @if($recentRequests->isNotEmpty())<div class="table-responsive"><table class="table request-table"><thead><tr><th>Case</th><th>Beneficiary ID</th><th>Requested</th><th>Due</th><th>Status</th><th class="text-right">Action</th></tr></thead><tbody>@foreach($recentRequests as $authentication)<tr><td>{{ $authentication->eclipCase->case_number }}</td><td>{{ $authentication->eclipCase->formerRebel->classified_id }}</td><td>{{ $authentication->requested_at->format('M d, Y') }}</td><td>{{ $authentication->due_at?->format('M d, Y') ?? 'No deadline' }}</td><td><span class="request-status">{{ str($authentication->status)->replace('_',' ')->title() }}</span></td><td class="text-right"><a href="{{ route('japic.authentication.index') }}" class="btn btn-sm btn-outline-primary">Open Queue</a></td></tr>@endforeach</tbody></table></div>@else<div class="empty-state"><i class="mdi mdi-shield-check-outline d-block mb-1" aria-hidden="true"></i>No authentication requests are assigned to you.</div>@endif
    </section>
</div>
@endsection
