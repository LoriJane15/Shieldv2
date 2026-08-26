@extends('layouts.skydash-v')
@section('title', 'Surfaced FR/FVE')
@section('heading', 'Local E-CLIP Committee')

@push('styles')
<style>
    .surfaced-register { --register-primary:#401595; --register-border:#e5e2e9; --register-ink:#27272a; margin:0 auto; max-width:1500px; }
    .register-hero { margin-bottom:1rem; }
    .register-count { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.13); border-radius:10px; color:#d9e2f1; min-width:116px; padding:.65rem .8rem; text-align:center; }
    .register-count strong { color:#fff; display:block; font-size:1.2rem; line-height:1; }
    .register-count small { display:block; font-size:.6rem; font-weight:700; margin-top:.3rem; text-transform:uppercase; }
    .register-card { border:1px solid var(--register-border); border-radius:12px; box-shadow:0 4px 16px rgba(39,39,42,.04); overflow:hidden; }
    .register-filters { align-items:end; background:#fff; border-bottom:1px solid #efedf1; display:grid; gap:.7rem; grid-template-columns:minmax(240px,1fr) minmax(210px,.45fr) auto; padding:1rem; }
    .register-filters label { color:#62596a; display:block; font-size:.68rem; font-weight:700; margin-bottom:.3rem; }
    .register-filters .form-control { border:1px solid #ddd8e2; border-radius:8px; font-size:.76rem; height:40px; }
    .filter-actions { display:flex; gap:.4rem; }
    .filter-actions .btn { border-radius:8px; font-size:.72rem; font-weight:700; height:40px; }
    .register-table { margin:0; }
    .register-table th { background:#f8f6fa; border:0; color:#857a90; font-size:.65rem; letter-spacing:.045em; text-transform:uppercase; white-space:nowrap; }
    .register-table td { border-color:#f0edf2; color:#52525b; font-size:.75rem; vertical-align:middle; }
    .beneficiary-id,.case-number { color:var(--register-primary); font-weight:750; }
    .case-status { background:#f1ebfa; border-radius:999px; color:#401595; display:inline-flex; font-size:.63rem; font-weight:700; padding:.3rem .55rem; }
    .workflow-progress { color:#71717a; font-size:.68rem; white-space:nowrap; }
    .workflow-progress span { background:#e4e4e7; border-radius:999px; display:block; height:4px; margin-top:.35rem; overflow:hidden; width:90px; }
    .workflow-progress i { background:#7550a4; display:block; height:100%; }
    .open-workflow { border-color:#d9cce8; border-radius:7px; color:#401595; font-size:.68rem; font-weight:700; white-space:nowrap; }
    .empty-register { color:#7b7280; padding:3rem 1rem; text-align:center; }
    .pagination-wrap { border-top:1px solid #efedf1; padding:.9rem 1rem; }
    @media(max-width:767.98px){.register-filters{grid-template-columns:1fr}.register-table thead{display:none}.register-table,.register-table tbody,.register-table tr,.register-table td{display:block;width:100%}.register-table tr{border-bottom:1px solid var(--register-border);padding:.65rem 0}.register-table td{border:0;padding:.3rem 1rem}.open-workflow{width:100%}}
</style>
@endpush

@section('content')
<div class="surfaced-register">
    <header class="shield-module-header register-hero" aria-labelledby="surfaced-title"><div class="shield-module-title"><span class="shield-module-icon"><i class="mdi mdi-account-group" aria-hidden="true"></i></span><div class="shield-module-copy"><div class="shield-module-eyebrow">Local E-CLIP Committee</div><h2 id="surfaced-title">Surfaced FR/FVE Register</h2><p><i class="mdi mdi-map-marker-radius mr-1" aria-hidden="true"></i>Municipality-scoped surfacing information and E-CLIP case status.</p></div></div><div class="shield-module-aside"><div class="register-count"><strong>{{ number_format($records->total()) }}</strong><small>Visible records</small></div></div></header>

    <section class="card register-card" aria-labelledby="register-table-title">
        <form method="GET" action="{{ route('local_eclip.surfaced.index') }}" class="register-filters" role="search"><div><label for="surfaced-search">Search</label><input id="surfaced-search" name="search" value="{{ request('search') }}" maxlength="100" class="form-control" placeholder="Beneficiary ID or case number"></div><div><label for="case-status">E-CLIP case status</label><select id="case-status" name="case_status" class="form-control"><option value="">All statuses</option>@foreach($statusOptions as $value=>$label)<option value="{{ $value }}" @selected(request('case_status')===$value)>{{ $label }}</option>@endforeach</select></div><div class="filter-actions"><button class="btn btn-primary"><i class="mdi mdi-filter-outline mr-1" aria-hidden="true"></i>Apply</button>@if(request()->hasAny(['search','case_status']))<a href="{{ route('local_eclip.surfaced.index') }}" class="btn btn-outline-secondary">Clear</a>@endif</div></form>
        <div class="table-responsive"><table class="table register-table"><caption id="register-table-title" class="sr-only">Surfaced FR/FVE records within the committee municipality</caption><thead><tr><th>Beneficiary ID</th><th>Surfaced On</th><th>Surfacing Location</th><th>E-CLIP Case</th><th>Case Status</th><th>Workflow Progress</th><th class="text-right">Action</th></tr></thead><tbody>
            @forelse($records as $record)
                @php($percentage = $record['total_steps'] > 0 ? min(100, ($record['completed_steps'] / $record['total_steps']) * 100) : 0)
                <tr><td><span class="beneficiary-id">{{ $record['beneficiary']->classified_id }}</span></td><td>{{ $record['surfaced_at'] ? \Illuminate\Support\Carbon::parse($record['surfaced_at'])->format('M d, Y') : 'Not recorded' }}</td><td>{{ $record['surfaced_location'] ?: 'Not recorded' }}</td><td>@if($record['case'])<span class="case-number">{{ $record['case']->case_number }}</span>@else<span class="text-muted">Not initialized</span>@endif</td><td>@if($record['case'])<span class="case-status">{{ $record['case']->status->label() }}</span>@else<span class="text-muted">Awaiting case</span>@endif</td><td><div class="workflow-progress">{{ $record['completed_steps'] }} of {{ $record['total_steps'] }} steps<span><i style="width:{{ $percentage }}%"></i></span></div></td><td class="text-right">@if($record['case'] && $record['can_open_workflow'])<a href="{{ route('eclip.workflow.show',$record['case']) }}" class="btn btn-sm btn-outline-primary open-workflow">View Workflow</a>@else<span class="text-muted small">Status view only</span>@endif</td></tr>
            @empty
                <tr><td colspan="7"><div class="empty-register"><i class="mdi mdi-account-search-outline d-block mb-1" aria-hidden="true"></i>No surfaced FR/FVE records match this municipality and filter.</div></td></tr>
            @endforelse
        </tbody></table></div>
        @if($records->hasPages())<div class="pagination-wrap">{{ $records->links() }}</div>@endif
    </section>
</div>
@endsection
