@extends('layouts.skydash-v')
@section('title', 'E-CLIP Eligibility')
@section('heading', 'LSWDO Eligibility Review')

@push('styles')
<style>
    .queue-hero { background: linear-gradient(125deg, #173b74, #2f6fed); border-radius: 16px; box-shadow: 0 10px 28px rgba(47,111,237,.16); color: #fff; overflow: hidden; padding: 1.5rem; position: relative; }
    .queue-hero::after { background: rgba(255,255,255,.08); border-radius: 50%; content: ''; height: 180px; position: absolute; right: -45px; top: -90px; width: 180px; }
    .queue-hero h2 { color: #fff; font-size: 1.55rem; font-weight: 700; line-height: 1.25; }
    .queue-hero p { align-items: center; display: flex; font-size: .82rem; gap: .4rem; opacity: .85; }
    .queue-hero p i { align-items: center; display: inline-flex; font-size: 1rem; justify-content: center; width: 18px; }
    .queue-count { background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28); border-radius: 12px; min-width: 100px; padding: .7rem 1rem; position: relative; text-align: center; z-index: 1; }
    .queue-count strong { display: block; font-size: 1.35rem; line-height: 1.1; }.queue-count small { font-size: .67rem; text-transform: uppercase; }
    .queue-card { border: 1px solid #e7ecf3; border-radius: 14px; box-shadow: 0 4px 16px rgba(23,43,77,.045); overflow: hidden; }
    .queue-card .card-body { padding: 0; }
    .queue-table { margin: 0; }
    .queue-table thead th { background: #f7f9fc; border: 0; color: #718096; font-size: .7rem; font-weight: 700; letter-spacing: .04em; padding: 1rem 1.15rem; text-transform: uppercase; white-space: nowrap; }
    .queue-table tbody td { border-color: #edf1f6; color: #52616f; font-size: .82rem; padding: 1rem 1.15rem; vertical-align: middle; }
    .queue-table tbody tr { transition: background-color .15s ease; }
    .queue-table tbody tr:hover { background: #fafcff; }
    .case-link { color: #244a83; font-weight: 700; }
    .detail-with-icon { align-items: center; display: inline-flex; gap: .5rem; line-height: 1.35; }
    .detail-with-icon > i { align-items: center; color: #7890b2; display: inline-flex; flex: 0 0 28px; font-size: .95rem; height: 28px; justify-content: center; width: 28px; }
    .status-badge { align-items: center; background: #eaf1ff; border-radius: 14px; color: #2f6fed; display: inline-flex; font-size: .68rem; font-weight: 700; line-height: 1; min-height: 26px; padding: .32rem .65rem; }
    .review-btn { align-items: center; border-radius: 8px; display: inline-flex; font-weight: 600; gap: .35rem; justify-content: center; min-height: 34px; }
    .queue-pagination { border-top: 1px solid #edf1f6; padding: 1rem 1.25rem; }
    .empty-queue { color: #8492a6; padding: 3rem 1rem; text-align: center; }.empty-queue i { color: #bcc7d6; display: block; font-size: 2.3rem; margin-bottom: .55rem; }
    @media(max-width:767px){.queue-hero{padding:1.2rem}.queue-hero h2{font-size:1.3rem}.queue-count{align-items:center;display:flex;gap:.55rem;margin-top:1rem;min-width:0;text-align:left}.queue-count strong{font-size:1.15rem}.queue-table thead{display:none}.queue-table,.queue-table tbody,.queue-table tr,.queue-table td{display:block;width:100%}.queue-table tr{border-bottom:1px solid #e7ecf3;padding:.75rem 0}.queue-table tbody td{align-items:flex-start;border:0;display:flex;gap:.75rem;padding:.38rem 1rem}.queue-table tbody td::before{color:#8492a6;content:attr(data-label);flex:0 0 92px;font-size:.65rem;font-weight:700;letter-spacing:.03em;padding-top:.3rem;text-transform:uppercase}.queue-table td:last-child{padding-top:.7rem;text-align:left!important}.queue-table td:last-child .review-btn{flex:1}.queue-table td:last-child::before{padding-top:.55rem}}
</style>
@endpush

@section('content')
<div class="queue-hero mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between position-relative" style="z-index:1">
        <div>
            <h2 class="mb-2">Eligibility Review Queue</h2>
            <p class="mb-0"><i class="mdi mdi-map-marker-outline"></i><span>Cases are limited to your assigned municipality.</span></p>
        </div>
        <div class="queue-count"><strong>{{ number_format($cases->total()) }}</strong><small>Total cases</small></div>
    </div>
</div>
<section class="card queue-card" aria-label="Eligibility cases">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table queue-table">
                <thead><tr><th>Case Number</th><th>Beneficiary ID</th><th>Status</th><th>Submitted</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @forelse($cases as $case)
                        <tr>
                            <td data-label="Case number"><a class="case-link" href="{{ route('lswdo.eclip.show', $case) }}">{{ $case->case_number }}</a></td>
                            <td data-label="Beneficiary"><span class="detail-with-icon"><i class="mdi mdi-account-key-outline"></i><span>{{ $case->formerRebel->classified_id }}</span></span></td>
                            <td data-label="Status"><span class="status-badge">{{ $case->status->label() }}</span></td>
                            <td data-label="Submitted"><span class="detail-with-icon"><i class="mdi mdi-calendar-outline"></i><span>{{ $case->submitted_at?->format('M d, Y · h:i A') ?? 'Not recorded' }}</span></span></td>
                            <td data-label="Action" class="text-right"><a href="{{ route('lswdo.eclip.show', $case) }}" class="btn btn-sm btn-outline-primary review-btn"><span>Review Case</span><i class="mdi mdi-arrow-right"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-queue"><i class="mdi mdi-clipboard-check-outline"></i><strong class="d-block text-dark mb-1">Review queue is clear</strong><span>No cases are currently awaiting eligibility review.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cases->hasPages())<div class="queue-pagination">{{ $cases->links() }}</div>@endif
</section>
@endsection
