@extends('layouts.skydash-v')
@section('title', 'Assistance Release')
@section('heading', 'Local E-CLIP Committee')

@push('styles')
<style>
    .release-page { --release-border: #e4e4e7; --release-ink: #27272a; --release-muted: #71717a; margin: 0 auto; max-width: 1500px; }
    .page-actions { align-items: center; display: flex; justify-content: space-between; margin-bottom: .9rem; }
    .release-back { align-items: center; color: #71717a; display: inline-flex; font-size: .72rem; font-weight: 700; gap: .35rem; }
    .release-back:hover { color: #401595; text-decoration: none; }
    .workflow-button { align-items: center; border-color: #d9cce8; border-radius: 7px; color: #401595; display: inline-flex; font-size: .69rem; font-weight: 700; gap: .35rem; }
    .workflow-button:hover, .workflow-button:focus { background: #401595; border-color: #401595; color: #fff; }
    .case-hero { margin-bottom: 1rem; min-height: 118px; padding: 1.25rem 1.5rem; }
    .case-hero-layout { align-items: center; display: flex; gap: 1.25rem; justify-content: space-between; position: relative; z-index: 1; }
    .case-meta { display: flex; flex-wrap: wrap; gap: .55rem 1.2rem; }
    .case-meta span { align-items: center; color: #bac7e5; display: inline-flex; font-size: .72rem; gap: .35rem; }
    .case-status { align-items: center; border: 1px solid rgba(255, 255, 255, .13); border-radius: 999px; display: inline-flex; flex: 0 0 auto; font-size: .66rem; font-weight: 750; gap: .35rem; padding: .4rem .72rem; }
    .money-card, .release-card { border: 1px solid var(--release-border); border-radius: 11px; box-shadow: none; }
    .money-card { overflow: hidden; position: relative; }
    .money-card::before { background: var(--money-color); content: ''; height: 3px; left: 0; position: absolute; right: 0; top: 0; }
    .money-card .card-body { align-items: center; display: flex; gap: .75rem; padding: 1rem; }
    .money-icon { align-items: center; background: var(--money-bg); border-radius: 9px; color: var(--money-color); display: flex; flex: 0 0 40px; font-size: 1.05rem; height: 40px; justify-content: center; }
    .money-card small { color: #8a7e95; display: block; font-size: .61rem; font-weight: 750; letter-spacing: .035em; text-transform: uppercase; }
    .money-card strong { color: var(--release-ink); display: block; font-size: 1.12rem; font-variant-numeric: tabular-nums; margin-top: .12rem; }
    .money-transferred { --money-bg: #eff6ff; --money-color: #2563a9; }
    .money-released { --money-bg: #e7f8f3; --money-color: #087f5b; }
    .money-remaining { --money-bg: #fff7e6; --money-color: #a96500; }
    .progress-label { color: var(--release-muted); font-size: .68rem; font-weight: 650; }
    .release-progress { background: #e4e4e7; border-radius: 999px; height: 5px; overflow: hidden; }
    .release-progress span { background: linear-gradient(90deg, #401595, #0ca678); display: block; height: 100%; }
    .release-card .card-body { padding: 1.15rem; }
    .release-card .shield-card-heading { border-bottom: 1px solid #f0f0f2; margin: -.1rem 0 1rem; padding-bottom: .9rem; }
    .field-label { color: #52525b; font-size: .69rem; font-weight: 700; }
    .optional { color: #a1a1aa; font-size: .62rem; font-weight: 500; }
    .release-card .form-control { background: #fafafa; border: 1px solid #e4e4e7; border-radius: 7px; color: #3f3f46; font-size: .73rem; }
    .currency-input { position: relative; }
    .currency-symbol { align-items: center; color: #71717a; display: flex; height: 100%; justify-content: center; left: 0; pointer-events: none; position: absolute; top: 0; width: 2rem; z-index: 2; }
    .currency-input input { padding-left: 2rem; }
    .ack-upload { background: #fafafa; border: 1px dashed #c8bed4; border-radius: 9px; padding: .8rem; }
    .ack-upload:focus-within { background: #f9f6fc; border-color: #7550a4; box-shadow: 0 0 0 3px rgba(64, 21, 149, .08); }
    .ack-picker { align-items: center; cursor: pointer; display: flex; gap: .65rem; margin: 0; min-width: 0; }
    .ack-icon { align-items: center; background: #f1ebfa; border-radius: 8px; color: #401595; display: flex; flex: 0 0 38px; font-size: 1rem; height: 38px; justify-content: center; }
    .ack-title { color: #3f3f46; display: block; font-size: .72rem; font-weight: 700; }
    .ack-help, .ack-name { color: #8a8a93; display: block; font-size: .62rem; }
    .ack-name { color: #684b86; margin-top: .1rem; overflow-wrap: anywhere; }
    .ack-upload input[type=file] { clip: rect(0, 0, 0, 0); height: 1px; overflow: hidden; position: absolute; white-space: nowrap; width: 1px; }
    .record-button { border-radius: 7px; font-size: .7rem; font-weight: 700; padding: .55rem .8rem; white-space: nowrap; }
    .security-note { align-items: flex-start; background: #f6f2fb; border: 1px solid #ece3f7; border-radius: 8px; color: #684b86; display: flex; font-size: .65rem; gap: .45rem; padding: .65rem .7rem; }
    .security-note i { flex: 0 0 auto; font-size: .82rem; }
    .history-table { margin: 0; }
    .history-table thead th { background: #f8f6fa; border: 0; color: #857a90; font-size: .61rem; font-weight: 750; letter-spacing: .045em; padding: .7rem; text-transform: uppercase; white-space: nowrap; }
    .history-table tbody td { border-color: #f0f0f2; color: #52525b; font-size: .7rem; padding: .72rem; vertical-align: middle; }
    .reference { color: #401595; font-weight: 750; }
    .amount { color: var(--release-ink); font-variant-numeric: tabular-nums; font-weight: 750; white-space: nowrap; }
    .ack-link { align-items: center; background: #f1ebfa; border-radius: 7px; color: #401595; display: inline-flex; font-size: .64rem; font-weight: 700; gap: .3rem; padding: .35rem .5rem; }
    .ack-link:hover { background: #401595; color: #fff; text-decoration: none; }
    .empty-state { color: var(--release-muted); padding: 2.5rem .75rem; text-align: center; }
    .empty-state i { color: #a78bca; display: block; font-size: 1.7rem; margin-bottom: .35rem; }
    @media (max-width: 991.98px) { .release-history-column { margin-top: 0; } }
    @media (max-width: 767.98px) {
        .page-actions { align-items: flex-start; flex-direction: column; gap: .65rem; }
        .workflow-button { justify-content: center; width: 100%; }
        .case-hero { min-height: 0; padding: 1.1rem; }
        .case-hero-layout { align-items: flex-start; flex-direction: column; }
        .case-status { margin-left: 54px; }
        .release-card .card-body { padding: 1rem; }
        .ack-upload .d-flex { align-items: stretch!important; }
        .record-button { margin-top: .75rem; width: 100%; }
        .history-table thead { display: none; }
        .history-table, .history-table tbody, .history-table tr, .history-table td { display: block; width: 100%; }
        .history-table tr { border-bottom: 1px solid var(--release-border); padding: .55rem 0; }
        .history-table tbody td { align-items: center; border: 0; display: flex; justify-content: space-between; padding: .3rem .75rem; text-align: right; }
        .history-table tbody td::before { color: #8a7e95; content: attr(data-label); flex: 0 0 90px; font-size: .61rem; font-weight: 750; letter-spacing: .035em; text-align: left; text-transform: uppercase; }
    }
</style>
@endpush

@section('content')
@php
    $transferred = (float) $case->fundTransactions->where('type', 'transfer')->sum('amount');
    $released = (float) $case->assistanceReleases->sum('amount');
    $remaining = max(0, $transferred - $released);
    $releasePercentage = $transferred > 0 ? min(100, ($released / $transferred) * 100) : 0;
    $canRelease = auth()->user()->can('releaseAssistance', $case);
@endphp
<div class="release-page">
    <div class="page-actions">
        <a href="{{ route('local_eclip.cases.index') }}" class="release-back"><i class="mdi mdi-arrow-left" aria-hidden="true"></i>Back to assistance release queue</a>
        @can('viewWorkflow', $case)<a href="{{ route('eclip.workflow.show', $case) }}" class="btn btn-sm btn-outline-primary workflow-button"><i class="mdi mdi-timeline-check-outline" aria-hidden="true"></i>Open Full Workflow</a>@endcan
    </div>

    <section class="case-hero" aria-labelledby="release-case-number">
        <div class="case-hero-layout">
            <div class="shield-hero-primary">
                <span class="shield-title-icon"><i class="mdi mdi-cash-usd" aria-hidden="true"></i></span>
                <div>
                    <div class="case-eyebrow">Assistance release</div>
                    <h2 id="release-case-number">{{ $case->case_number }}</h2>
                    <div class="case-meta"><span><i class="mdi mdi-account-key-outline" aria-hidden="true"></i>{{ $case->formerRebel->classified_id }}</span><span><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</span></div>
                </div>
            </div>
            <span class="case-status"><i class="mdi mdi-progress-check" aria-hidden="true"></i>{{ $case->status->label() }}</span>
        </div>
    </section>

    <div class="row">
        <div class="col-md-4 mb-3"><div class="card money-card money-transferred h-100"><div class="card-body"><span class="money-icon"><i class="mdi mdi-bank-transfer-in" aria-hidden="true"></i></span><div><small>Transferred</small><strong>₱{{ number_format($transferred, 2) }}</strong></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card money-card money-released h-100"><div class="card-body"><span class="money-icon"><i class="mdi mdi-cash-multiple" aria-hidden="true"></i></span><div><small>Released</small><strong>₱{{ number_format($released, 2) }}</strong></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card money-card money-remaining h-100"><div class="card-body"><span class="money-icon"><i class="mdi mdi-wallet-outline" aria-hidden="true"></i></span><div><small>Remaining</small><strong>₱{{ number_format($remaining, 2) }}</strong></div></div></div></div>
    </div>
    <div class="d-flex justify-content-between progress-label mb-1"><span>Release progress</span><span>{{ number_format($releasePercentage, 1) }}%</span></div>
    <div class="release-progress mb-4" role="progressbar" aria-label="Release progress" aria-valuenow="{{ $releasePercentage }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $releasePercentage }}%"></span></div>

    <div class="row">
        <div class="{{ $canRelease ? 'col-xl-7' : 'd-none' }}">
            @can('releaseAssistance', $case)
                <section class="card release-card mb-4" aria-labelledby="record-release-title"><div class="card-body">
                    <div class="shield-card-heading"><span class="shield-card-heading-icon"><i class="mdi mdi-cash-multiple" aria-hidden="true"></i></span><div><h3 id="record-release-title">Record Assistance Release</h3><p>Enter the official release details and attach the signed acknowledgment.</p></div></div>
                    <form method="POST" action="{{ route('local_eclip.releases.store', $case) }}" enctype="multipart/form-data" data-release-form>
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4"><label for="amount" class="field-label">Released Amount</label><div class="currency-input"><span class="currency-symbol" aria-hidden="true">₱</span><input id="amount" type="number" name="amount" value="{{ old('amount') }}" min="0.01" max="{{ number_format($remaining, 2, '.', '') }}" step="0.01" class="form-control @error('amount') is-invalid @enderror" required></div>@error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Maximum ₱{{ number_format($remaining, 2) }}</small></div>
                            <div class="form-group col-md-4"><label for="release_reference" class="field-label">Release Reference</label><input id="release_reference" name="release_reference" value="{{ old('release_reference') }}" maxlength="150" class="form-control @error('release_reference') is-invalid @enderror" placeholder="Official reference number" required>@error('release_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="form-group col-md-4"><label for="released_at" class="field-label">Release Date</label><input id="released_at" type="date" name="released_at" value="{{ old('released_at') }}" max="{{ now()->toDateString() }}" class="form-control @error('released_at') is-invalid @enderror" required>@error('released_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div>
                        <div class="form-group"><label for="recipient" class="field-label">Recipient</label><input id="recipient" name="recipient" value="{{ old('recipient') }}" maxlength="255" class="form-control @error('recipient') is-invalid @enderror" placeholder="Authorized recipient recorded on the acknowledgment" required>@error('recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="form-group"><label for="remarks" class="field-label">Remarks <span class="optional">(optional)</span></label><textarea id="remarks" name="remarks" rows="3" maxlength="5000" class="form-control @error('remarks') is-invalid @enderror" placeholder="Add release notes or conditions...">{{ old('remarks') }}</textarea>@error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="form-group">
                            <label class="field-label" for="acknowledgment">Acknowledgment Document</label>
                            <div class="ack-upload"><input id="acknowledgment" type="file" name="acknowledgment" accept=".pdf,.jpg,.jpeg,.png" required data-file-input><div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between"><label for="acknowledgment" class="ack-picker"><span class="ack-icon"><i class="mdi mdi-cloud-upload-outline" aria-hidden="true"></i></span><span><span class="ack-title">Choose signed acknowledgment</span><span class="ack-help">PDF, JPG, or PNG · Maximum 10 MB</span><span class="ack-name" data-file-name>No file selected</span></span></label><button type="submit" class="btn btn-primary record-button" data-submit-button><i class="mdi mdi-content-save mr-1" aria-hidden="true"></i>Record Release</button></div></div>
                            @error('acknowledgment')<div class="text-danger small mt-2" role="alert">{{ $message }}</div>@enderror
                        </div>
                        @error('status')<div class="alert alert-danger py-2" role="alert">{{ $message }}</div>@enderror
                    </form>
                    <div class="security-note"><i class="mdi mdi-shield-lock-outline" aria-hidden="true"></i><span>The acknowledgment is stored privately. Recording a release creates an immutable financial history entry.</span></div>
                </div></section>
            @endcan
        </div>

        <div class="{{ $canRelease ? 'col-xl-5' : 'col-12' }} release-history-column">
            <section class="card release-card mb-4" aria-labelledby="history-title"><div class="card-body">
                <div class="shield-card-heading"><span class="shield-card-heading-icon"><i class="mdi mdi-history" aria-hidden="true"></i></span><div><h3 id="history-title">Release History</h3><p>Recorded releases and their private acknowledgment documents.</p></div></div>
                <div class="table-responsive"><table class="table history-table"><caption class="sr-only">Immutable assistance release history</caption><thead><tr><th>Reference</th><th>Recipient</th><th>Amount</th><th>Date</th><th>Receipt</th><th>Proof</th></tr></thead><tbody>
                    @forelse($case->assistanceReleases->sortByDesc('created_at') as $release)
                        <tr><td data-label="Reference"><span class="reference">{{ $release->release_reference }}</span></td><td data-label="Recipient">{{ $release->recipient }}</td><td data-label="Amount"><span class="amount">₱{{ number_format((float) $release->amount, 2) }}</span></td><td data-label="Date">{{ $release->released_at->format('M d, Y') }}</td><td data-label="Receipt">{{ $release->received_confirmed_at ? 'Confirmed by LSWDO' : 'Awaiting confirmation' }}</td><td data-label="Proof"><a class="ack-link" target="_blank" rel="noopener" href="{{ route('local_eclip.releases.acknowledgment', $release) }}"><i class="mdi mdi-file-eye-outline" aria-hidden="true"></i>View</a></td></tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><i class="mdi mdi-clock-outline" aria-hidden="true"></i>No assistance releases have been recorded.</div></td></tr>
                    @endforelse
                </tbody></table></div>
            </div></section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var releaseForm = document.querySelector('[data-release-form]');
if (releaseForm) {
    var input = releaseForm.querySelector('[data-file-input]');
    var name = releaseForm.querySelector('[data-file-name]');
    var button = releaseForm.querySelector('[data-submit-button]');
    input.addEventListener('change', function () { name.textContent = input.files.length ? input.files[0].name : 'No file selected'; });
    releaseForm.addEventListener('submit', function () { button.disabled = true; button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1" aria-hidden="true"></i>Recording...'; });
}
</script>
@endpush
