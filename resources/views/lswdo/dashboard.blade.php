@extends('layouts.skydash-v')
@section('title', 'LSWDO Dashboard')
@section('heading', 'LSWDO Dashboard')

@push('styles')
<style>
    .lswdo-dashboard { --dash-navy: #172b4d; --dash-primary: #6554c0; --dash-border: #e5eaf2; margin: 0 auto; max-width: 1500px; }
    .dashboard-hero { background: linear-gradient(120deg, #5546ad, #6554c0 52%, #3476d9); border-radius: 15px; color: #fff; overflow: hidden; padding: 1.45rem 1.55rem; position: relative; }
    .dashboard-hero::after { background: rgba(255,255,255,.07); border-radius: 50%; content: ''; height: 220px; position: absolute; right: -55px; top: -110px; width: 220px; }
    .dashboard-hero h2 { color: #fff; font-size: 1.5rem; font-weight: 750; }
    .dashboard-hero p { font-size: .82rem; opacity: .88; }
    .hero-actions { display: flex; gap: .55rem; position: relative; z-index: 1; }
    .hero-actions .btn { border-radius: 8px; font-size: .74rem; font-weight: 700; }
    .hero-actions .btn-outline-light:hover { color: #5546ad; }
    .summary-grid { display: grid; gap: .8rem; grid-template-columns: repeat(4, 1fr); margin: 1rem 0; }
    .summary-card { border: 1px solid var(--dash-border); border-radius: 12px; box-shadow: 0 4px 14px rgba(23,43,77,.04); }
    .summary-card .card-body { align-items: center; display: flex; gap: .8rem; padding: 1rem; }
    .summary-icon { align-items: center; background: #f0edff; border-radius: 10px; color: var(--dash-primary); display: flex; flex: 0 0 42px; font-size: 1.1rem; height: 42px; justify-content: center; }
    .summary-card strong { color: var(--dash-navy); display: block; font-size: 1.25rem; line-height: 1; }
    .summary-card small { color: #78879b; display: block; font-size: .68rem; margin-top: .3rem; }
    .workspace-card { border: 1px solid var(--dash-border); border-radius: 12px; box-shadow: 0 4px 14px rgba(23,43,77,.04); height: 100%; overflow: hidden; }
    .workspace-heading { align-items: center; border-bottom: 1px solid #edf1f6; display: flex; justify-content: space-between; padding: 1rem 1.1rem; }
    .workspace-heading h3 { color: var(--dash-navy); font-size: .92rem; font-weight: 750; margin: 0; }
    .workspace-heading p { color: #8492a6; font-size: .7rem; margin: .18rem 0 0; }
    .workspace-heading a { font-size: .7rem; font-weight: 700; }
    .dashboard-list { list-style: none; margin: 0; padding: 0; }
    .dashboard-list li { align-items: center; border-bottom: 1px solid #edf1f6; display: grid; gap: .75rem; grid-template-columns: minmax(0,1fr) auto; padding: .85rem 1.1rem; }
    .dashboard-list li:last-child { border-bottom: 0; }
    .list-title { color: #334155; display: block; font-size: .78rem; font-weight: 700; }
    .list-meta { color: #8492a6; display: block; font-size: .67rem; margin-top: .18rem; }
    .list-status { background: #f0edff; border-radius: 999px; color: #5c49ad; font-size: .64rem; font-weight: 700; padding: .3rem .55rem; white-space: nowrap; }
    .empty-list { color: #8492a6; font-size: .75rem; padding: 2.4rem 1rem; text-align: center; }
    @media(max-width:991.98px){.summary-grid{grid-template-columns:repeat(2,1fr)}.dashboard-hero>div{align-items:flex-start!important;flex-direction:column}.hero-actions{margin-top:1rem}}
    @media(max-width:575.98px){.summary-grid{grid-template-columns:1fr}.hero-actions{flex-direction:column;width:100%}.hero-actions .btn{width:100%}}
</style>
@endpush

@section('content')
<div class="lswdo-dashboard">
    <header class="dashboard-hero">
        <div class="d-flex align-items-center justify-content-between position-relative" style="z-index:1">
            <div><div class="shield-module-eyebrow">LSWDO Case Workspace</div><h2 class="mb-1">Assigned Intake and E-CLIP Workload</h2><p class="mb-0"><i class="mdi mdi-account-key-outline mr-1" aria-hidden="true"></i>Counts and records are limited to referrals and cases explicitly assigned to your account.</p></div>
            <div class="hero-actions"><a href="{{ route('lswdo.referrals.index') }}" class="btn btn-light">Open Referrals</a><a href="{{ route('lswdo.eclip.index') }}" class="btn btn-outline-light">Open E-CLIP Cases</a></div>
        </div>
    </header>

    <section class="summary-grid" aria-label="Assigned workload summary">
        @foreach([
            ['Pending referrals', $summary['pending_referrals'], 'mdi-inbox-arrow-down'],
            ['Assigned cases', $summary['assigned_cases'], 'mdi-folder-account-outline'],
            ['Requiring action', $summary['requiring_action'], 'mdi-alert-circle-outline'],
            ['Completed cases', $summary['completed_cases'], 'mdi-check-decagram'],
        ] as [$label, $value, $icon])
            <div class="card summary-card"><div class="card-body"><span class="summary-icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></span><div><strong>{{ number_format($value) }}</strong><small>{{ $label }}</small></div></div></div>
        @endforeach
    </section>

    <div class="row">
        <div class="col-lg-6 mb-4"><section class="card workspace-card" aria-labelledby="recent-referrals-title">
            <div class="workspace-heading"><div><h3 id="recent-referrals-title">Recent MBLRC Referrals</h3><p>Assigned integration-completion referrals.</p></div><a href="{{ route('lswdo.referrals.index') }}">View all</a></div>
            @if($recentReferrals->isNotEmpty())<ul class="dashboard-list">@foreach($recentReferrals as $referral)<li><div><span class="list-title">{{ $referral->formerRebel->classified_id }} · {{ $referral->referral_number }}</span><span class="list-meta">Integration completed {{ $referral->enrollment->integration_completed_at?->format('M d, Y') ?? 'date unavailable' }}</span></div><span class="list-status">{{ str($referral->status)->title() }}</span></li>@endforeach</ul>@else<div class="empty-list"><i class="mdi mdi-inbox d-block mb-1" aria-hidden="true"></i>No referrals are currently assigned.</div>@endif
        </section></div>
        <div class="col-lg-6 mb-4"><section class="card workspace-card" aria-labelledby="action-cases-title">
            <div class="workspace-heading"><div><h3 id="action-cases-title">Cases Requiring Action</h3><p>Oldest assigned action items first.</p></div><a href="{{ route('lswdo.eclip.index') }}">View all</a></div>
            @if($actionCases->isNotEmpty())<ul class="dashboard-list">@foreach($actionCases as $case)<li><div><a class="list-title" href="{{ route('lswdo.eclip.show', $case) }}">{{ $case->case_number }} · {{ $case->formerRebel->classified_id }}</a><span class="list-meta">Updated {{ $case->updated_at->diffForHumans() }}</span></div><span class="list-status">{{ $case->status->label() }}</span></li>@endforeach</ul>@else<div class="empty-list"><i class="mdi mdi-check-circle-outline d-block mb-1" aria-hidden="true"></i>No assigned cases currently require action.</div>@endif
        </section></div>
    </div>
</div>
@endsection
