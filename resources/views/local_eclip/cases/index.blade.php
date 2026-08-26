@extends('layouts.skydash-v')
@section('title', 'Assistance Release')
@section('heading', 'Local E-CLIP Committee')

@push('styles')
<style>
    .release-queue { --release-border: #e4e4e7; --release-ink: #27272a; --release-muted: #71717a; margin: 0 auto; max-width: 1500px; }
    .release-hero { margin-bottom: 1rem; }
    .release-hero .shield-module-copy p { align-items: center; display: flex; gap: .4rem; }
    .release-count { background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .13); border-radius: 10px; color: #d9e2f1; min-width: 112px; padding: .65rem .8rem; text-align: center; }
    .release-count strong { color: #fff; display: block; font-size: 1.2rem; font-weight: 800; line-height: 1; }
    .release-count small { display: block; font-size: .6rem; font-weight: 700; letter-spacing: .04em; margin-top: .3rem; text-transform: uppercase; }
    .queue-card { border: 1px solid var(--release-border); border-radius: 11px; box-shadow: none; overflow: hidden; }
    .queue-card-header { align-items: center; background: #fff; border-bottom: 1px solid #f0f0f2; display: flex; justify-content: space-between; padding: 1rem 1.1rem; }
    .queue-card-header .shield-card-heading { margin: 0; }
    .queue-scope { align-items: center; background: #f6f2fb; border: 1px solid #ece3f7; border-radius: 999px; color: #684b86; display: inline-flex; font-size: .66rem; font-weight: 700; gap: .35rem; padding: .35rem .65rem; }
    .queue-table { margin: 0; }
    .queue-table caption { caption-side: top; }
    .queue-table thead th { background: #f8f6fa; border: 0; color: #857a90; font-size: .66rem; font-weight: 750; letter-spacing: .055em; padding: .8rem 1rem; text-transform: uppercase; white-space: nowrap; }
    .queue-table tbody tr:hover { background: #fdfbff; }
    .queue-table tbody td { border-color: #f0f0f2; color: #52525b; font-size: .77rem; padding: .9rem 1rem; vertical-align: middle; }
    .case-link { color: #401595; font-weight: 750; }
    .case-link:hover { color: #280274; }
    .beneficiary-detail { align-items: center; display: inline-flex; gap: .4rem; }
    .beneficiary-detail i { color: #a1a1aa; }
    .money-column { text-align: right; }
    .money { color: var(--release-ink); font-variant-numeric: tabular-nums; font-weight: 750; white-space: nowrap; }
    .released-cell { min-width: 130px; }
    .release-progress { background: #e4e4e7; border-radius: 999px; height: 4px; margin-left: auto; margin-top: .42rem; overflow: hidden; width: 100px; }
    .release-progress span { background: #0ca678; display: block; height: 100%; }
    .status-badge { align-items: center; background: #f1ebfa; border-radius: 999px; color: #401595; display: inline-flex; font-size: .63rem; font-weight: 750; gap: .3rem; padding: .32rem .6rem; white-space: nowrap; }
    .status-badge.is-complete { background: #e7f8f3; color: #087f5b; }
    .open-btn { align-items: center; border-color: #d9cce8; border-radius: 7px; color: #401595; display: inline-flex; font-size: .69rem; font-weight: 700; gap: .35rem; white-space: nowrap; }
    .open-btn:hover, .open-btn:focus { background: #401595; border-color: #401595; color: #fff; }
    .queue-pagination { border-top: 1px solid #f0f0f2; padding: .9rem 1.1rem; }
    .empty-queue { color: var(--release-muted); padding: 3.25rem 1rem; text-align: center; }
    .empty-icon { align-items: center; background: #f6f2fb; border-radius: 50%; color: #684b86; display: flex; font-size: 1.55rem; height: 54px; justify-content: center; margin: 0 auto .8rem; width: 54px; }
    .empty-queue strong { color: var(--release-ink); display: block; margin-bottom: .25rem; }
    @media (max-width: 767.98px) {
        .release-hero .shield-module-aside { width: 100%; }
        .release-count { text-align: left; width: 100%; }
        .queue-card-header { align-items: flex-start; flex-direction: column; gap: .75rem; }
        .queue-table thead { display: none; }
        .queue-table, .queue-table tbody, .queue-table tr, .queue-table td { display: block; width: 100%; }
        .queue-table tbody tr { border-bottom: 1px solid var(--release-border); padding: .65rem 0; }
        .queue-table tbody tr:last-child { border-bottom: 0; }
        .queue-table tbody td { align-items: center; border: 0; display: flex; justify-content: space-between; padding: .35rem 1rem; text-align: right; }
        .queue-table tbody td::before { color: #8a7e95; content: attr(data-label); flex: 0 0 105px; font-size: .63rem; font-weight: 750; letter-spacing: .04em; text-align: left; text-transform: uppercase; }
        .money-column { text-align: right; }
        .released-cell > div { align-items: flex-end; display: flex; flex-direction: column; }
        .release-progress { margin-left: 0; }
        .queue-table tbody td:last-child { padding-top: .7rem; }
        .open-btn { justify-content: center; width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="release-queue">
    <header class="shield-module-header release-hero" aria-labelledby="release-queue-title">
        <div class="shield-module-title">
            <span class="shield-module-icon"><i class="mdi mdi-cash-multiple" aria-hidden="true"></i></span>
            <div class="shield-module-copy">
                <div class="shield-module-eyebrow">Local E-CLIP Committee</div>
                <h2 id="release-queue-title">Assistance Release Queue</h2>
                <p><i class="mdi mdi-shield-account-outline" aria-hidden="true"></i><span>Review transferred assistance and record authorized beneficiary releases.</span></p>
            </div>
        </div>
        <div class="shield-module-aside">
            <div class="release-count"><strong>{{ number_format($cases->total()) }}</strong><small>Municipal cases</small></div>
        </div>
    </header>

    <section class="card queue-card" aria-labelledby="release-cases-title">
        <div class="queue-card-header">
            <div class="shield-card-heading">
                <span class="shield-card-heading-icon"><i class="mdi mdi-bank-transfer-in" aria-hidden="true"></i></span>
                <div><h3 id="release-cases-title">Transferred Assistance</h3><p>Open a case to record a release or review its acknowledgment history.</p></div>
            </div>
            <span class="queue-scope"><i class="mdi mdi-map-marker-radius" aria-hidden="true"></i>Assigned municipality only</span>
        </div>
        <div class="table-responsive">
            <table class="table queue-table">
                <caption class="sr-only">Assistance release cases within your assigned municipality</caption>
                <thead><tr><th>Case Number</th><th>Beneficiary ID</th><th class="money-column">Transferred</th><th class="money-column">Released</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @forelse($cases as $case)
                        @php
                            $transferred = (float) $case->fundTransactions->where('type', 'transfer')->sum('amount');
                            $released = (float) $case->assistanceReleases->sum('amount');
                            $percentage = $transferred > 0 ? min(100, ($released / $transferred) * 100) : 0;
                        @endphp
                        <tr>
                            <td data-label="Case"><a class="case-link" href="{{ route('local_eclip.cases.show', $case) }}">{{ $case->case_number }}</a></td>
                            <td data-label="Beneficiary"><span class="beneficiary-detail"><i class="mdi mdi-account-key-outline" aria-hidden="true"></i>{{ $case->formerRebel->classified_id }}</span></td>
                            <td data-label="Transferred" class="money-column"><span class="money">₱{{ number_format($transferred, 2) }}</span></td>
                            <td data-label="Released" class="money-column released-cell"><div><span class="money">₱{{ number_format($released, 2) }}</span><div class="release-progress" title="{{ number_format($percentage, 1) }}% released" role="progressbar" aria-label="Release progress" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $percentage }}%"></span></div></div></td>
                            <td data-label="Status"><span class="status-badge {{ $case->status === \App\Enums\EclipCaseStatus::Completed ? 'is-complete' : '' }}"><i class="mdi {{ $case->status === \App\Enums\EclipCaseStatus::Completed ? 'mdi-check-circle-outline' : 'mdi-clock-outline' }}" aria-hidden="true"></i>{{ $case->status->label() }}</span></td>
                            <td data-label="Action" class="text-right"><a href="{{ route('local_eclip.cases.show', $case) }}" class="btn btn-sm btn-outline-primary open-btn">Open Release <i class="mdi mdi-arrow-right" aria-hidden="true"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-queue"><span class="empty-icon"><i class="mdi mdi-cash-usd" aria-hidden="true"></i></span><strong>Release queue is clear</strong><span>No transferred cases are currently available in your municipality.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cases->hasPages())<div class="queue-pagination">{{ $cases->links() }}</div>@endif
    </section>
</div>
@endsection
