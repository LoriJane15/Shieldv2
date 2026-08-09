@extends('layouts.skydash-v')
@section('title', 'Assistance Assessment Queue')
@section('heading', 'E-CLIP Assistance Assessment')

@push('styles')
<style>
    .assessment-queue { --queue-blue: #2f6fed; --queue-purple: #6246c9; --queue-navy: #172b4d; --queue-border: #dfe6f0; color: #334155; font-size: .94rem; }
    .assessment-queue a:focus-visible, .assessment-queue button:focus-visible, .assessment-queue input:focus-visible, .assessment-queue select:focus-visible { box-shadow: 0 0 0 3px rgba(47, 111, 237, .22) !important; outline: 2px solid transparent; }
    .assessment-hero { background: linear-gradient(125deg, #173b74, #2f6fed); border-radius: 14px; box-shadow: 0 8px 22px rgba(47, 111, 237, .14); color: #fff; overflow: hidden; padding: 1.45rem 1.6rem; position: relative; }
    .assessment-hero::after { background: rgba(255, 255, 255, .08); border-radius: 50%; content: ''; height: 190px; position: absolute; right: -50px; top: -95px; width: 190px; }
    .assessment-eyebrow { font-size: .75rem; font-weight: 700; letter-spacing: .09em; opacity: .8; text-transform: uppercase; }
    .assessment-hero h2 { color: #fff; font-size: 1.65rem; font-weight: 700; margin: .2rem 0 .35rem; }
    .assessment-hero p { font-size: .88rem; margin: 0; opacity: .88; }
    .municipality-note { align-items: center; display: flex; font-size: .82rem; gap: .35rem; margin-top: .75rem; opacity: .82; }
    .summary-card { border: 1px solid var(--queue-border); border-radius: 11px; box-shadow: 0 3px 12px rgba(23, 43, 77, .04); height: 100%; }
    .summary-card .card-body { align-items: center; display: flex; gap: .7rem; padding: .85rem .9rem; }
    .summary-icon { align-items: center; background: var(--summary-bg); border-radius: 9px; color: var(--summary-color); display: flex; flex: 0 0 38px; font-size: 1.05rem; height: 38px; justify-content: center; width: 38px; }
    .summary-value { color: var(--queue-navy); display: block; font-size: 1.35rem; font-weight: 700; line-height: 1.05; }
    .summary-label { color: #64748b; display: block; font-size: .69rem; font-weight: 700; line-height: 1.25; margin-top: .15rem; text-transform: uppercase; }
    .summary-total { --summary-bg: #eaf1ff; --summary-color: #2f6fed; }
    .summary-needs { --summary-bg: #f1ebff; --summary-color: #7251bc; }
    .summary-progress { --summary-bg: #e9f4ff; --summary-color: #2875bf; }
    .summary-review { --summary-bg: #fff4d6; --summary-color: #946500; }
    .summary-approved { --summary-bg: #e6f7ef; --summary-color: #16845e; }
    .attention-line { color: #52657d; font-size: .87rem; margin: -.1rem 0 1.2rem; }
    .attention-line strong { color: var(--queue-navy); }
    .filter-card, .assessment-card { border: 1px solid var(--queue-border); border-radius: 12px; box-shadow: 0 3px 12px rgba(23, 43, 77, .04); }
    .filter-card .card-body { padding: .9rem 1rem; }
    .filter-grid { align-items: end; display: grid; gap: .75rem; grid-template-columns: minmax(260px, 1fr) minmax(190px, .34fr) minmax(175px, .28fr) auto; }
    .filter-label { color: #45566e; display: block; font-size: .75rem; font-weight: 700; margin-bottom: .35rem; }
    .search-wrap { position: relative; }
    .search-wrap i { color: #8998ac; left: .85rem; position: absolute; top: 50%; transform: translateY(-50%); }
    .search-wrap .form-control { padding-left: 2.4rem; }
    .assessment-queue .form-control { border-color: #d5deea; border-radius: 8px; font-size: .88rem; min-height: 42px; }
    .assessment-queue .form-control:focus { border-color: #80a6ee; box-shadow: 0 0 0 3px rgba(47, 111, 237, .1); }
    .assessment-queue .btn { align-items: center; border-radius: 8px; display: inline-flex; font-size: .84rem; font-weight: 600; gap: .35rem; justify-content: center; min-height: 40px; }
    .assessment-queue .btn-primary { background: var(--queue-purple); border-color: var(--queue-purple); }
    .assessment-card { overflow: hidden; }
    .assessment-card .card-body { padding: 0; }
    .assessment-table { margin: 0; table-layout: fixed; }
    .assessment-table thead th { background: #f7f9fc; border: 0; color: #66768b; font-size: .72rem; font-weight: 700; letter-spacing: .04em; padding: .85rem 1rem; text-transform: uppercase; }
    .assessment-table tbody td { border-color: #e9eef5; color: #52616f; font-size: .86rem; padding: 1rem; vertical-align: middle; }
    .assessment-table tbody tr.action-required { box-shadow: inset 3px 0 0 #7251bc; }
    .assessment-table tbody tr:hover { background: #fbfcfe; }
    .case-link { color: #244a83; display: inline-block; font-size: .91rem; font-weight: 700; }
    .case-link:hover { color: var(--queue-blue); }
    .beneficiary-id { align-items: center; color: #718096; display: flex; font-size: .78rem; gap: .3rem; margin-top: .25rem; }
    .assessment-status { align-items: center; border-radius: 999px; display: inline-flex; font-size: .71rem; font-weight: 700; gap: .25rem; line-height: 1.2; padding: .36rem .65rem; }
    .assessment-status-ready { background: #f1ebff; color: #6546a9; }
    .assessment-status-progress { background: #e9f4ff; color: #2467a8; }
    .assessment-status-review { background: #fff4d6; color: #835a00; }
    .assessment-status-revision { background: #fff0df; color: #a4530d; }
    .assessment-status-approved { background: #e6f7ef; color: #147553; }
    .assessment-status-rejected { background: #ffebed; color: #b63f49; }
    .assessment-status-neutral { background: #edf1f5; color: #5f6f82; }
    .status-context { color: #718096; display: block; font-size: .74rem; line-height: 1.35; margin-top: .35rem; }
    .status-context.action { color: #7251bc; font-weight: 600; }
    .amount { color: var(--queue-navy); display: block; font-size: .92rem; font-variant-numeric: tabular-nums; font-weight: 700; white-space: nowrap; }
    .amount-empty { color: #64748b; font-weight: 600; }
    .amount-help, .updated-time { color: #8492a6; display: block; font-size: .73rem; margin-top: .18rem; }
    .updated-date { color: #40536d; display: block; font-size: .84rem; font-weight: 600; white-space: nowrap; }
    .assessment-action { flex: 0 0 184px; max-width: 184px; min-width: 184px; padding-left: .75rem; padding-right: .75rem; white-space: nowrap; width: 184px; }
    .assessment-pagination { align-items: center; border-top: 1px solid #e9eef5; display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; padding: .85rem 1rem; }
    .pagination-copy { color: #718096; font-size: .78rem; }
    .empty-queue { color: #718096; padding: 3rem 1rem; text-align: center; }
    .empty-icon { align-items: center; background: #eef2f7; border-radius: 50%; color: #8797ab; display: flex; font-size: 1.7rem; height: 56px; justify-content: center; margin: 0 auto .75rem; width: 56px; }
    .empty-queue strong { color: #334155; display: block; font-size: .95rem; margin-bottom: .25rem; }
    @media (max-width: 1199px) { .filter-grid { grid-template-columns: minmax(240px, 1fr) 200px 180px; } .filter-actions { grid-column: 1 / -1; text-align: right; } }
    @media (max-width: 991px) { .assessment-table { min-width: 860px; } }
    @media (max-width: 767px) {
        .assessment-hero { padding: 1.2rem; } .assessment-hero h2 { font-size: 1.4rem; }
        .filter-grid { grid-template-columns: 1fr; } .filter-actions { display: flex; grid-column: auto; } .filter-actions .btn { flex: 1; }
        .assessment-card { background: transparent; border: 0; box-shadow: none; overflow: visible; }
        .assessment-card .table-responsive { overflow: visible; }
        .assessment-table { min-width: 0; table-layout: auto; }
        .assessment-table thead { display: none; }
        .assessment-table, .assessment-table tbody, .assessment-table tr, .assessment-table td { display: block; width: 100%; }
        .assessment-table tbody { display: grid; gap: .8rem; }
        .assessment-table tbody tr { background: #fff; border: 1px solid var(--queue-border); border-radius: 11px; box-shadow: 0 3px 12px rgba(23, 43, 77, .04); padding: .9rem; }
        .assessment-table tbody tr.action-required { box-shadow: inset 3px 0 0 #7251bc, 0 3px 12px rgba(23, 43, 77, .04); }
        .assessment-table tbody td { align-items: flex-start; border: 0; display: flex; justify-content: space-between; padding: .45rem 0; text-align: right; }
        .assessment-table tbody td::before { color: #7a889b; content: attr(data-label); flex: 0 0 36%; font-size: .7rem; font-weight: 700; letter-spacing: .025em; padding-top: .15rem; text-align: left; text-transform: uppercase; }
        .assessment-table tbody td.case-cell { border-bottom: 1px solid #edf1f6; display: block; padding: 0 0 .7rem; text-align: left; }
        .assessment-table tbody td.case-cell::before { display: none; }
        .assessment-table tbody td.action-cell { padding-top: .75rem; }
        .assessment-table tbody td.action-cell::before { display: none; }
        .assessment-action { width: 100%; }
        .assessment-pagination { background: #fff; border: 1px solid var(--queue-border); border-radius: 11px; margin-top: .8rem; }
    }
</style>
@endpush

@section('content')
<div class="assessment-queue">
    <header class="assessment-hero mb-3">
        <div class="position-relative" style="z-index: 1;">
            <div class="assessment-eyebrow">E-CLIP Assistance</div>
            <h2>Assistance Assessment Queue</h2>
            <p>Review and manage E-CLIP assistance assessments.</p>
            <div class="municipality-note"><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i><span>Cases are limited to your assigned municipality.</span></div>
        </div>
    </header>

    <div class="row mb-1">
        @foreach([
            ['summary-total', 'mdi-format-list-checks', $summary['total'], 'Total Cases'],
            ['summary-needs', 'mdi-clipboard-alert-outline', $summary['needs_assessment'], 'Needs Assessment'],
            ['summary-progress', 'mdi-progress-pencil', $summary['in_progress'], 'In Progress'],
            ['summary-review', 'mdi-account-clock-outline', $summary['under_review'], 'Under Review'],
            ['summary-approved', 'mdi-check-decagram-outline', $summary['approved'], 'Approved'],
        ] as [$class, $icon, $value, $label])
            <div class="col-6 col-lg mb-3"><div class="card summary-card {{ $class }}"><div class="card-body"><div class="summary-icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></div><div><span class="summary-value">{{ number_format($value) }}</span><span class="summary-label">{{ $label }}</span></div></div></div></div>
        @endforeach
    </div>

    @php
        $actionableCount = $summary['needs_assessment'] + $summary['in_progress'];
    @endphp
    @if($actionableCount > 0)
        <p class="attention-line"><strong>{{ $actionableCount }} {{ Str::plural('case', $actionableCount) }} currently {{ $actionableCount === 1 ? 'requires' : 'require' }} assessment action.</strong>@if($summary['under_review'] > 0) {{ $summary['under_review'] }} {{ $summary['under_review'] === 1 ? 'assessment is' : 'assessments are' }} waiting for provincial review.@endif</p>
    @elseif($summary['under_review'] > 0)
        <p class="attention-line"><strong>{{ $summary['under_review'] }} {{ $summary['under_review'] === 1 ? 'assessment is' : 'assessments are' }} waiting for provincial review.</strong></p>
    @endif

    <section class="card filter-card mb-3" aria-label="Assessment queue filters">
        <div class="card-body">
            <form method="GET" action="{{ route('eclip_assessor.cases.index') }}" class="filter-grid">
                <div><label for="assessment-search" class="filter-label">Search cases</label><div class="search-wrap"><i class="mdi mdi-magnify" aria-hidden="true"></i><input id="assessment-search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search case number or beneficiary ID"></div></div>
                <div><label for="assessment-status" class="filter-label">Status</label><select id="assessment-status" name="status" class="form-control"><option value="">All Statuses</option>@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="assessment-sort" class="filter-label">Sort by</label><select id="assessment-sort" name="sort" class="form-control"><option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest Updated</option><option value="oldest" @selected(request('sort') === 'oldest')>Oldest Updated</option></select></div>
                <div class="filter-actions"><button type="submit" class="btn btn-primary"><i class="mdi mdi-filter-outline" aria-hidden="true"></i> Apply</button>@if($hasActiveFilters)<a href="{{ route('eclip_assessor.cases.index') }}" class="btn btn-light ml-1">Clear</a>@endif</div>
            </form>
        </div>
    </section>

    <section class="card assessment-card" aria-labelledby="assessment-cases-heading">
        <h3 id="assessment-cases-heading" class="sr-only">Assistance assessment cases</h3>
        <div class="card-body">
            @if($cases->isEmpty())
                <div class="empty-queue"><span class="empty-icon"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i></span><strong>{{ $hasActiveFilters ? 'No cases match your current filters.' : 'No assessment cases available.' }}</strong><span>{{ $hasActiveFilters ? 'Try changing your search or status filter.' : 'Cases ready for E-CLIP assistance assessment will appear here.' }}</span>@if($hasActiveFilters)<div class="mt-3"><a href="{{ route('eclip_assessor.cases.index') }}" class="btn btn-outline-primary">Clear Filters</a></div>@endif</div>
            @else
                <div class="table-responsive">
                    <table class="table assessment-table">
                        <thead><tr><th style="width: 22%;">Case / Beneficiary</th><th style="width: 27%;">Assessment Status</th><th style="width: 16%;">Assessed Amount</th><th style="width: 15%;">Last Updated</th><th class="text-right" style="width: 20%;">Action</th></tr></thead>
                        <tbody>
                        @foreach($cases as $case)
                            @php
                                $canAssess = auth()->user()->can('assessAssistance', $case);
                                $latestRevision = $case->assistanceRequest?->latestRevision;
                                $lastUpdated = collect([$case->updated_at, $latestRevision?->created_at])->filter()->max();
                                $context = match ($case->status) {
                                    App\Enums\EclipCaseStatus::DocumentsCertified => 'Assessment not started · Action required',
                                    App\Enums\EclipCaseStatus::AssistanceAssessment => 'Draft assessment · Action required',
                                    App\Enums\EclipCaseStatus::SubmittedForDilgReview => 'Waiting for Provincial Reviewer',
                                    App\Enums\EclipCaseStatus::ReturnedForAssessmentRevision => 'Returned by reviewer · Action required',
                                    App\Enums\EclipCaseStatus::Approved => 'Assessment finalized',
                                    App\Enums\EclipCaseStatus::Rejected => 'Review completed',
                                    default => null,
                                };
                            @endphp
                            <tr class="{{ $canAssess ? 'action-required' : '' }}">
                                <td class="case-cell" data-label="Case / Beneficiary"><a class="case-link" href="{{ route('eclip_assessor.cases.show', $case) }}">{{ $case->case_number }}</a><span class="beneficiary-id"><i class="mdi mdi-account-key-outline" aria-hidden="true"></i>{{ $case->formerRebel->classified_id }}</span></td>
                                <td data-label="Assessment Status"><div><x-eclip.assessment-status-badge :status="$case->status" />@if($context)<span class="status-context {{ $canAssess ? 'action' : '' }}">{{ $context }}</span>@endif</div></td>
                                <td data-label="Assessed Amount">@if($latestRevision?->assessed_amount !== null)<span class="amount">₱{{ number_format((float) $latestRevision->assessed_amount, 2) }}</span>@else<span class="amount amount-empty">—</span><span class="amount-help">Not assessed yet</span>@endif</td>
                                <td data-label="Last Updated">@if($lastUpdated)<span class="updated-date">{{ $lastUpdated->format('M d, Y') }}</span><span class="updated-time">{{ $lastUpdated->format('g:i A') }}</span>@else<span class="updated-date">—</span>@endif</td>
                                <td class="action-cell text-right" data-label="Action"><x-eclip.assessment-action :case="$case" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($cases->total() > 0)
            <footer class="assessment-pagination"><span class="pagination-copy">Showing {{ number_format($cases->firstItem()) }}–{{ number_format($cases->lastItem()) }} of {{ number_format($cases->total()) }} cases</span>@if($cases->hasPages())<div>{{ $cases->onEachSide(1)->links() }}</div>@endif</footer>
        @endif
    </section>
</div>
@endsection
