@extends('layouts.skydash-v')
@section('title', 'Financial Monitoring')
@section('heading', 'E-CLIP Financial Monitoring')

@push('styles')
<style>
    .finance-workspace{--purple:#401595;--ink:#263246;--muted:#68758a;--line:#dfe5ee;max-width:1320px;margin:0 auto 2rem}.finance-hero{background:linear-gradient(120deg,#280274,#401595 70%,#5825ad);border-radius:16px;box-shadow:0 12px 28px rgba(40,2,116,.18);color:#fff;overflow:hidden;padding:1.35rem 1.5rem;position:relative}.finance-hero:after{background:radial-gradient(circle,rgba(255,255,255,.16) 0 2px,transparent 2.5px);background-size:17px 17px;content:"";height:210px;position:absolute;right:-35px;top:-90px;width:300px}.finance-hero>*{position:relative;z-index:1}.finance-hero h1{color:#fff;font-size:1.5rem;font-weight:850;margin:.2rem 0}.finance-hero p{font-size:.84rem;margin:0;opacity:.88}.finance-actions{display:flex;flex-wrap:wrap;gap:.6rem;margin:1rem 0}.finance-card{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 5px 18px rgba(31,42,61,.05);margin-top:1rem;overflow:hidden}.finance-card-header{background:linear-gradient(105deg,#f5f0fb,#fff 70%,#fff8f2);border-bottom:1px solid #e8e1ee;border-left:4px solid #e85d18;padding:1rem 1.15rem}.finance-card-header h2{color:var(--ink);font-size:1.05rem;font-weight:850;margin:0}.finance-card-header p{color:var(--muted);font-size:.75rem;margin:.2rem 0 0}.finance-card-body{padding:1rem}.record-list{display:grid;gap:.8rem}.record{border:1px solid var(--line);border-radius:11px;padding:.85rem}.record-head{align-items:flex-start;display:flex;gap:.7rem;justify-content:space-between}.record-head h3{color:var(--ink);font-size:.88rem;font-weight:800;margin:0}.record-meta{color:var(--muted);font-size:.7rem;margin-top:.2rem}.status-pill{background:#fff3cd;border-radius:99px;color:#745800;font-size:.66rem;font-weight:850;padding:.3rem .55rem}.status-pill.accepted{background:#e3f5ed;color:#116849}.status-pill.returned{background:#ffebed;color:#a82635}.record form,.create-form{border-top:1px solid #edf0f4;display:grid;gap:.65rem;grid-template-columns:repeat(3,minmax(0,1fr));margin-top:.75rem;padding-top:.75rem}.field-wide{grid-column:span 2}.field-full{grid-column:1/-1}.record label,.create-form label{color:#45536a;font-size:.7rem;font-weight:750}.record .form-control,.create-form .form-control{font-size:.8rem;min-height:44px}.form-submit{align-items:end;display:flex}.empty-record{color:var(--muted);font-size:.8rem;padding:1rem;text-align:center}@media(max-width:900px){.record form,.create-form{grid-template-columns:1fr}.field-wide,.field-full{grid-column:auto}.record-head{display:block}.status-pill{display:inline-block;margin-top:.5rem}}
</style>
@endpush

@section('content')
<div class="finance-workspace">
    <header class="finance-hero">
        <small>SETTLEMENT AND LIQUIDATION</small>
        <h1>{{ $case->case_number }} · {{ $case->formerRebel->classified_id }}</h1>
        <p>Track corrections, resubmissions, acceptance dates, and reporting evidence without overwriting prior audit snapshots.</p>
    </header>
    <div class="finance-actions">
        <a class="btn btn-light" href="{{ route('dilg_reviewer.financial-monitoring.index') }}"><i class="mdi mdi-arrow-left"></i> Back to monitoring</a>
        <a class="btn btn-primary" href="{{ route('eclip.workflow.show', $case) }}"><i class="mdi mdi-timeline-clock-outline"></i> Open full workflow</a>
    </div>

    @if(auth()->user()->hasRole('dilg_provincial_focal'))
    <section class="finance-card">
        <header class="finance-card-header"><h2>Step 8A — Liquidation Requirements</h2><p>Every return needs a reason. The official step can only be completed after all recorded requirements are accepted.</p></header>
        <div class="finance-card-body">
            <div class="record-list">
                @forelse($case->liquidationRequirements->sortByDesc('updated_at') as $requirement)
                <article class="record">
                    <div class="record-head"><div><h3>{{ $requirement->requirement_name }}</h3><div class="record-meta">{{ $requirement->assistance_category }} · Last updated by {{ $requirement->updater?->name ?? 'Unknown' }} on {{ $requirement->updated_at->format('M d, Y · h:i A') }}</div></div><span class="status-pill {{ $requirement->status }}">{{ str($requirement->status)->replace('_',' ')->title() }}</span></div>
                    <form method="POST" action="{{ route('dilg_reviewer.financial-monitoring.liquidations.update', $requirement) }}" data-confirm-financial>@csrf @method('PUT')
                        @include('dilg_reviewer.financial-monitoring._liquidation-fields', ['item' => $requirement])
                        <div class="form-submit"><button class="btn btn-primary btn-block"><i class="mdi mdi-content-save-check-outline"></i> Save correction</button></div>
                    </form>
                </article>
                @empty<div class="empty-record">No liquidation requirements have been recorded.</div>@endforelse
            </div>
            <form class="create-form" method="POST" action="{{ route('dilg_reviewer.financial-monitoring.liquidations.store', $case) }}" data-confirm-financial>@csrf
                <div class="field-full"><strong>Add a liquidation requirement</strong></div>
                @include('dilg_reviewer.financial-monitoring._liquidation-fields', ['item' => null])
                <div class="form-submit"><button class="btn btn-primary btn-block"><i class="mdi mdi-plus"></i> Add requirement</button></div>
            </form>
        </div>
    </section>
    @endif

    @if(auth()->user()->hasRole('dilg_regional'))
    <section class="finance-card">
        <header class="finance-card-header"><h2>Step 9 — Regional Disbursement Reports</h2><p>Maintain the report’s submission, correction, copy-furnished, and acceptance history.</p></header>
        <div class="finance-card-body">
            <div class="record-list">
                @forelse($case->regionalDisbursementReports->sortByDesc('reporting_month') as $report)
                <article class="record">
                    <div class="record-head"><div><h3>{{ $report->reporting_month->format('F Y') }}</h3><div class="record-meta">{{ $report->form_11_reference ?: 'No Form 11 reference' }} · Last updated by {{ $report->updater?->name ?? 'Unknown' }}</div></div><span class="status-pill {{ $report->status }}">{{ str($report->status)->replace('_',' ')->title() }}</span></div>
                    <form method="POST" action="{{ route('dilg_reviewer.financial-monitoring.disbursement-reports.update', $report) }}" data-confirm-financial>@csrf @method('PUT')
                        @include('dilg_reviewer.financial-monitoring._report-fields', ['item' => $report])
                        <div class="form-submit"><button class="btn btn-primary btn-block"><i class="mdi mdi-content-save-check-outline"></i> Save correction</button></div>
                    </form>
                </article>
                @empty<div class="empty-record">No regional reports have been recorded.</div>@endforelse
            </div>
            <form class="create-form" method="POST" action="{{ route('dilg_reviewer.financial-monitoring.disbursement-reports.store', $case) }}" data-confirm-financial>@csrf
                <div class="field-full"><strong>Add a monthly report</strong></div>
                @include('dilg_reviewer.financial-monitoring._report-fields', ['item' => null])
                <div class="form-submit"><button class="btn btn-primary btn-block"><i class="mdi mdi-plus"></i> Add report</button></div>
            </form>
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>document.querySelectorAll('[data-confirm-financial]').forEach(function(form){form.addEventListener('submit',function(event){if(!window.confirm('Save this financial monitoring record? An audit snapshot will be retained.'))event.preventDefault();});});</script>
@endpush
