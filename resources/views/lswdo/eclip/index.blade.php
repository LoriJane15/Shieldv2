@extends('layouts.skydash-v')
@section('title', 'E-CLIP Eligibility')
@section('heading', 'LSWDO Eligibility Review')

@push('styles')
<style>
    .eligibility-queue { --queue-primary: #6554c0; --queue-blue: #316fd6; --queue-navy: #172b4d; --queue-border: #e5eaf2; --queue-muted: #718096; margin: 0 auto; max-width: 1500px; }
    .queue-hero { background: linear-gradient(120deg, #5546ad 0%, #6554c0 48%, #3476d9 100%); border-radius: 14px; box-shadow: 0 8px 22px rgba(79, 70, 174, .16); color: #fff; overflow: hidden; padding: 1.35rem 1.5rem; position: relative; }
    .queue-hero::after { background: rgba(255, 255, 255, .07); border-radius: 50%; content: ''; height: 210px; position: absolute; right: -55px; top: -105px; width: 210px; }
    .queue-hero-content { align-items: center; display: flex; gap: 1.25rem; justify-content: space-between; position: relative; z-index: 1; }
    .queue-eyebrow { font-size: .68rem; font-weight: 700; letter-spacing: .1em; opacity: .72; text-transform: uppercase; }
    .queue-hero h2 { color: #fff; font-size: 1.45rem; font-weight: 700; letter-spacing: -.01em; line-height: 1.25; }
    .queue-description { font-size: .8rem; line-height: 1.5; opacity: .9; }
    .queue-scope { align-items: center; display: flex; font-size: .7rem; gap: .4rem; margin-top: .55rem; opacity: .76; }
    .queue-scope i { font-size: .85rem; }
    .queue-summary { display: grid; flex: 0 0 auto; gap: .45rem; grid-template-columns: repeat(4, minmax(90px, 1fr)); }
    .summary-chip { background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .22); border-radius: 9px; min-width: 95px; padding: .65rem .7rem; }
    .summary-chip strong { color: #fff; display: block; font-size: 1.1rem; line-height: 1; }
    .summary-chip span { display: block; font-size: .6rem; font-weight: 600; line-height: 1.25; margin-top: .28rem; opacity: .8; text-transform: uppercase; }

    .queue-card { border: 1px solid var(--queue-border); border-radius: 12px; box-shadow: 0 3px 14px rgba(23, 43, 77, .04); overflow: hidden; }
    .queue-toolbar { background: #fff; border-bottom: 1px solid var(--queue-border); padding: 1rem; }
    .filter-grid { align-items: end; display: grid; gap: .65rem; grid-template-columns: minmax(260px, 1fr) minmax(180px, .42fr) minmax(135px, .3fr) minmax(140px, .3fr) auto; }
    .filter-field { min-width: 0; }
    .filter-label { color: #52616f; display: block; font-size: .66rem; font-weight: 700; margin: 0 0 .35rem; }
    .search-control { position: relative; }
    .search-control i { color: #8b9bb0; font-size: .9rem; left: .8rem; position: absolute; top: 50%; transform: translateY(-50%); }
    .queue-toolbar .form-control { background-color: #fbfcfe; border: 1px solid #dbe2eb; border-radius: 8px; color: #42526b; font-size: .75rem; height: 40px; }
    .queue-toolbar .form-control:focus { background-color: #fff; border-color: #8c80d5; box-shadow: 0 0 0 3px rgba(101, 84, 192, .1); }
    .search-control .form-control { padding-left: 2.25rem; }
    .filter-actions { display: flex; gap: .4rem; }
    .filter-button, .filter-clear { align-items: center; border-radius: 8px; display: inline-flex; font-size: .72rem; font-weight: 650; gap: .35rem; height: 40px; justify-content: center; padding: 0 .8rem; white-space: nowrap; }
    .filter-button { background: var(--queue-primary); border: 1px solid var(--queue-primary); color: #fff; }
    .filter-button:hover, .filter-button:focus { background: #5545ad; border-color: #5545ad; color: #fff; box-shadow: 0 0 0 3px rgba(101, 84, 192, .14); outline: 0; }
    .filter-clear { border: 1px solid #d8e0ea; color: #64748b; }
    .filter-clear:hover, .filter-clear:focus { background: #f5f7fa; color: #34445a; outline: 0; }

    .queue-table { margin: 0; table-layout: fixed; }
    .queue-table thead th { background: #f7f9fc; border: 0; color: #718096; font-size: .65rem; font-weight: 750; letter-spacing: .045em; padding: .78rem 1rem; text-transform: uppercase; white-space: nowrap; }
    .queue-table tbody td { border-color: #edf1f6; color: #52616f; font-size: .76rem; padding: .9rem 1rem; vertical-align: middle; }
    .queue-table tbody tr { transition: background-color .15s ease; }
    .queue-table tbody tr:hover { background: #fbfcff; }
    .queue-table th:nth-child(1), .queue-table td:nth-child(1) { width: 34%; }
    .queue-table th:nth-child(2), .queue-table td:nth-child(2) { width: 22%; }
    .queue-table th:nth-child(3), .queue-table td:nth-child(3) { width: 18%; }
    .queue-table th:nth-child(4), .queue-table td:nth-child(4) { width: 26%; }
    .case-cell { align-items: center; display: flex; gap: .7rem; min-width: 0; }
    .case-icon { align-items: center; background: #f0edff; border-radius: 8px; color: var(--queue-primary); display: flex; flex: 0 0 34px; font-size: .9rem; height: 34px; justify-content: center; width: 34px; }
    .case-identity { min-width: 0; }
    .case-link { color: #294970; display: inline-block; font-size: .78rem; font-weight: 700; max-width: 100%; overflow: hidden; text-decoration: underline; text-decoration-color: #b8c7da; text-underline-offset: 3px; text-overflow: ellipsis; white-space: nowrap; }
    .case-link:hover, .case-link:focus { color: var(--queue-primary); text-decoration-color: var(--queue-primary); }
    .beneficiary-name { color: #334155; display: block; font-size: .76rem; font-weight: 650; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .beneficiary-id { color: #8492a6; display: block; font-size: .67rem; margin-top: .18rem; }
    .eclip-status { align-items: center; border-radius: 14px; display: inline-flex; font-size: .64rem; font-weight: 750; line-height: 1.25; min-height: 25px; padding: .3rem .6rem; }
    .eclip-status-submitted { background: #e8f2ff; color: #2764a8; }
    .eclip-status-eligible { background: #e7f7ef; color: #187a56; }
    .eclip-status-certified { background: #f0ebff; color: #6646ad; }
    .eclip-status-revision { background: #fff3dc; color: #986000; }
    .eclip-status-ineligible { background: #ffebee; color: #b33e4b; }
    .eclip-status-neutral { background: #edf2f8; color: #52657d; }
    .waiting-time { color: #7d8ca1; display: block; font-size: .64rem; margin-top: .3rem; }
    .submitted-date { align-items: center; display: flex; gap: .55rem; }
    .submitted-date > i { color: #8b9bb0; font-size: .85rem; }
    .submitted-date time { color: #45566d; display: block; font-size: .73rem; font-weight: 600; }
    .submitted-date small { color: #8a99ac; display: block; font-size: .65rem; margin-top: .12rem; }
    .case-action { align-items: center; border-radius: 8px; display: inline-flex; font-size: .69rem; font-weight: 700; gap: .35rem; justify-content: center; min-height: 38px; padding: .45rem .7rem; white-space: nowrap; width: 132px; }
    .case-action-primary { background: var(--queue-primary); border: 1px solid var(--queue-primary); color: #fff; }
    .case-action-primary:hover, .case-action-primary:focus { background: #5545ad; border-color: #5545ad; color: #fff; box-shadow: 0 0 0 3px rgba(101, 84, 192, .13); outline: 0; }
    .case-action-secondary { background: #fff; border: 1px solid #cfd8e5; color: #50627b; }
    .case-action-secondary:hover, .case-action-secondary:focus { background: #f5f3ff; border-color: #9e92db; color: #5746af; outline: 0; }

    .queue-footer { align-items: center; background: #fff; border-top: 1px solid var(--queue-border); display: flex; gap: 1rem; justify-content: space-between; padding: .85rem 1rem; }
    .pagination-summary { color: #7b8a9e; font-size: .68rem; }
    .pagination-summary strong { color: #42526b; font-weight: 650; }
    .queue-footer nav { margin-left: auto; }
    .queue-footer .pagination { margin: 0; }
    .queue-footer .page-link { align-items: center; border-color: #dfe5ed; color: #596b83; display: flex; font-size: .68rem; justify-content: center; min-height: 34px; min-width: 34px; }
    .queue-footer .page-item.active .page-link { background: var(--queue-primary); border-color: var(--queue-primary); }
    .queue-footer .page-link:focus { box-shadow: 0 0 0 3px rgba(101, 84, 192, .12); }
    .empty-queue { color: #8492a6; padding: 3rem 1rem; text-align: center; }
    .empty-icon { align-items: center; background: #f1effb; border-radius: 50%; color: #7667c2; display: flex; font-size: 1.25rem; height: 52px; justify-content: center; margin: 0 auto .7rem; width: 52px; }
    .empty-queue strong { color: #405169; display: block; font-size: .8rem; margin-bottom: .2rem; }
    .empty-queue span { font-size: .7rem; }

    /* Page-scoped refinements for the existing LSWDO sidebar. */
    .sidebar .nav .nav-item .nav-link { border-radius: 0 8px 8px 0; min-height: 44px; }
    .sidebar .nav .nav-item .nav-link .menu-icon { flex: 0 0 22px; font-size: 1rem; line-height: 1; margin-right: .7rem; text-align: center; }
    .sidebar .nav .nav-item.active { position: relative; }
    .sidebar .nav .nav-item.active::before { background: #8b78df; border-radius: 0 3px 3px 0; bottom: 8px; content: ''; left: 0; position: absolute; top: 8px; width: 3px; z-index: 2; }
    .sidebar .nav .nav-item .nav-link:hover, .sidebar .nav .nav-item .nav-link:focus { background: #f1effb; outline: 0; }

    /* Readability scale for users who benefit from larger interface text. */
    .queue-eyebrow { font-size: .76rem; }
    .queue-hero h2 { font-size: 1.6rem; }
    .queue-description { font-size: .9rem; }
    .queue-scope { font-size: .8rem; }
    .summary-chip strong { font-size: 1.25rem; }
    .summary-chip span { font-size: .7rem; }
    .filter-label { font-size: .76rem; }
    .queue-toolbar .form-control { font-size: .84rem; }
    .filter-button, .filter-clear { font-size: .8rem; }
    .queue-table thead th { font-size: .73rem; }
    .queue-table tbody td { font-size: .84rem; }
    .case-link { font-size: .87rem; }
    .beneficiary-name { font-size: .85rem; }
    .beneficiary-id { font-size: .76rem; }
    .eclip-status { font-size: .73rem; }
    .waiting-time { font-size: .73rem; }
    .submitted-date time { font-size: .82rem; }
    .submitted-date small { font-size: .74rem; }
    .case-action { font-size: .77rem; }
    .pagination-summary, .queue-footer .page-link { font-size: .76rem; }
    .empty-queue strong { font-size: .9rem; }
    .empty-queue span { font-size: .8rem; }
    .sidebar .nav .nav-item .nav-link .menu-title { font-size: .84rem; }

    @media (max-width: 1199px) {
        .queue-hero-content { align-items: flex-start; flex-direction: column; }
        .queue-summary { width: 100%; }
        .filter-grid { grid-template-columns: minmax(240px, 1fr) repeat(3, minmax(130px, .45fr)); }
        .filter-actions { grid-column: 1 / -1; justify-content: flex-end; }
    }
    @media (max-width: 767px) {
        .queue-hero { padding: 1.15rem; }
        .queue-hero h2 { font-size: 1.4rem; }
        .queue-summary { gap: .4rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .summary-chip { min-width: 0; }
        .filter-grid { grid-template-columns: 1fr; }
        .filter-actions { grid-column: auto; }
        .filter-button { flex: 1; }
        .queue-table, .queue-table tbody, .queue-table tr, .queue-table td { display: block; width: 100% !important; }
        .queue-table thead { display: none; }
        .queue-table tbody tr { border-bottom: 1px solid var(--queue-border); padding: .7rem 0; }
        .queue-table tbody tr:last-child { border-bottom: 0; }
        .queue-table tbody td { align-items: flex-start; border: 0; display: flex; gap: .75rem; padding: .38rem 1rem; }
        .queue-table tbody td::before { color: #8a99ac; content: attr(data-label); flex: 0 0 88px; font-size: .72rem; font-weight: 700; letter-spacing: .03em; padding-top: .28rem; text-transform: uppercase; }
        .queue-table tbody td:first-child { padding-bottom: .65rem; }
        .queue-table tbody td:last-child { padding-top: .7rem; }
        .queue-table tbody td:last-child .case-action { flex: 1; min-height: 42px; }
        .queue-table tbody td:last-child::before { padding-top: .7rem; }
        .case-cell { align-items: flex-start; }
        .queue-footer { align-items: flex-start; flex-direction: column; }
        .queue-footer nav { margin-left: 0; max-width: 100%; overflow-x: auto; }
    }
    @media (max-width: 420px) {
        .queue-summary { grid-template-columns: 1fr 1fr; }
        .queue-table tbody td { display: block; }
        .queue-table tbody td::before { display: block; margin-bottom: .25rem; }
        .queue-table tbody td:last-child .case-action { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="eligibility-queue">
    <header class="queue-hero mb-4" aria-labelledby="queue-title">
        <div class="queue-hero-content">
            <div>
                <div class="queue-eyebrow mb-1">LSWDO case management</div>
                <h2 id="queue-title" class="mb-2">Eligibility Review Queue</h2>
                <div class="queue-description">Review and verify submitted E-CLIP beneficiary applications.</div>
                <div class="queue-scope"><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i><span>Cases shown are limited to your assigned municipality.</span></div>
            </div>
            <div class="queue-summary" aria-label="Eligibility queue summary">
                <div class="summary-chip"><strong>{{ number_format($summary['total']) }}</strong><span>Total cases</span></div>
                <div class="summary-chip"><strong>{{ number_format($summary['awaiting']) }}</strong><span>Awaiting review</span></div>
                <div class="summary-chip"><strong>{{ number_format($summary['eligible']) }}</strong><span>Eligible</span></div>
                <div class="summary-chip"><strong>{{ number_format($summary['certified']) }}</strong><span>Documents certified</span></div>
            </div>
        </div>
    </header>

    <section class="card queue-card" aria-labelledby="cases-table-title">
        <form class="queue-toolbar" method="GET" action="{{ route('lswdo.eclip.index') }}" role="search">
            <div class="filter-grid">
                <div class="filter-field">
                    <label class="filter-label" for="queue-search">Search cases</label>
                    <div class="search-control"><i class="mdi mdi-magnify" aria-hidden="true"></i><input id="queue-search" type="search" name="search" value="{{ request('search') }}" maxlength="100" class="form-control" placeholder="Search case number or beneficiary ID" autocomplete="off"></div>
                </div>
                <div class="filter-field">
                    <label class="filter-label" for="queue-status">Status</label>
                    <select id="queue-status" name="status" class="form-control">
                        <option value="">All statuses</option>
                        @foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label" for="queue-date">Submitted</label>
                    <select id="queue-date" name="date" class="form-control">
                        <option value="">All dates</option>
                        <option value="today" @selected(request('date') === 'today')>Today</option>
                        <option value="week" @selected(request('date') === 'week')>This week</option>
                        <option value="month" @selected(request('date') === 'month')>This month</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label" for="queue-sort">Sort by</label>
                    <select id="queue-sort" name="sort" class="form-control">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest submitted</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest submitted</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-button"><i class="mdi mdi-filter-outline" aria-hidden="true"></i>Apply</button>
                    @if($hasActiveFilters || request('sort') === 'oldest')<a href="{{ route('lswdo.eclip.index') }}" class="filter-clear" aria-label="Clear eligibility queue filters"><i class="mdi mdi-close" aria-hidden="true"></i>Clear</a>@endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table queue-table">
                <caption id="cases-table-title" class="sr-only">Eligibility cases in your assigned municipality</caption>
                <thead><tr><th>Case / Beneficiary</th><th>Status</th><th>Submitted</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @forelse($cases as $case)
                        @php
                            $requiresAction = in_array($case->status, [
                                \App\Enums\EclipCaseStatus::SubmittedForEligibility,
                                \App\Enums\EclipCaseStatus::EligibilityReviewInProgress,
                                \App\Enums\EclipCaseStatus::DocumentProcessing,
                                \App\Enums\EclipCaseStatus::DocumentsIncomplete,
                            ], true);
                            $actionLabel = match ($case->status) {
                                \App\Enums\EclipCaseStatus::SubmittedForEligibility,
                                \App\Enums\EclipCaseStatus::EligibilityReviewInProgress => 'Review Case',
                                \App\Enums\EclipCaseStatus::Eligible => 'View Eligibility',
                                \App\Enums\EclipCaseStatus::DocumentProcessing => 'Manage Documents',
                                \App\Enums\EclipCaseStatus::DocumentsIncomplete => 'Review Documents',
                                default => 'View Case',
                            };
                            $isWaiting = in_array($case->status, [
                                \App\Enums\EclipCaseStatus::SubmittedForEligibility,
                                \App\Enums\EclipCaseStatus::EligibilityReviewInProgress,
                            ], true) && $case->submitted_at;
                        @endphp
                        <tr>
                            <td data-label="Case">
                                <div class="case-cell">
                                    <span class="case-icon"><i class="mdi mdi-folder-account-outline" aria-hidden="true"></i></span>
                                    <div class="case-identity">
                                        <a class="case-link" href="{{ route('lswdo.eclip.show', $case) }}">{{ $case->case_number }}</a>
                                        <span class="beneficiary-name">{{ $case->formerRebel->full_name }}</span>
                                        <span class="beneficiary-id">{{ $case->formerRebel->classified_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Status">
                                <x-eclip.status-badge :status="$case->status" />
                                @if($isWaiting)<span class="waiting-time">{{ $case->submitted_at->diffForHumans(now(), true) }} waiting</span>@endif
                            </td>
                            <td data-label="Submitted">
                                <div class="submitted-date"><i class="mdi mdi-calendar-outline" aria-hidden="true"></i><div>@if($case->submitted_at)<time datetime="{{ $case->submitted_at->toIso8601String() }}">{{ $case->submitted_at->format('M d, Y') }}</time><small>{{ $case->submitted_at->format('h:i A') }}</small>@else<span>Not recorded</span>@endif</div></div>
                            </td>
                            <td data-label="Action" class="text-right"><a href="{{ route('lswdo.eclip.show', $case) }}" class="case-action {{ $requiresAction ? 'case-action-primary' : 'case-action-secondary' }}"><span>{{ $actionLabel }}</span><i class="mdi mdi-arrow-right" aria-hidden="true"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-queue"><span class="empty-icon"><i class="mdi mdi-clipboard-search-outline" aria-hidden="true"></i></span>@if($hasActiveFilters)<strong>No cases match the selected filters.</strong><span>Try changing your search or filters.</span>@else<strong>No eligibility cases are currently available.</strong><span>Newly submitted cases from your municipality will appear here.</span>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cases->total() > 0)
            <footer class="queue-footer">
                <div class="pagination-summary">Showing <strong>{{ number_format($cases->firstItem()) }}–{{ number_format($cases->lastItem()) }}</strong> of <strong>{{ number_format($cases->total()) }}</strong> cases</div>
                @if($cases->hasPages()){{ $cases->onEachSide(1)->links() }}@endif
            </footer>
        @endif
    </section>
</div>
@endsection
