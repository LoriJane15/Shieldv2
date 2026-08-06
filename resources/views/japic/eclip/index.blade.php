@extends('layouts.skydash-v')
@section('title', 'E-CLIP Documents')
@section('heading', 'JAPIC Document Processing')

@push('styles')
<style>
    .japic-queue{--navy:#173b74;--primary:#2f6fed;--border:#e7ecf3}.queue-hero{align-items:center;background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;display:flex;justify-content:space-between;overflow:hidden;padding:1.5rem;position:relative}.queue-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:190px;position:absolute;right:-45px;top:-95px;width:190px}.queue-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;opacity:.75;text-transform:uppercase}.queue-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.queue-hero p{font-size:.8rem;opacity:.86}.queue-summary{align-items:center;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);border-radius:12px;display:flex;gap:.7rem;padding:.65rem .85rem;position:relative;z-index:1}.queue-summary i{font-size:1.35rem}.queue-summary small,.queue-summary strong{display:block}.queue-summary small{font-size:.62rem;opacity:.75;text-transform:uppercase}.queue-summary strong{font-size:.9rem}.queue-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045);overflow:hidden}.queue-card .card-body{padding:0}.queue-table{margin:0}.queue-table thead th{background:#f7f9fc;border:0;color:#718096;font-size:.68rem;font-weight:700;letter-spacing:.05em;padding:.9rem 1rem;text-transform:uppercase;white-space:nowrap}.queue-table tbody td{border-color:#edf1f6;color:#52616f;font-size:.8rem;padding:1rem;vertical-align:middle}.case-number{color:#244a83;font-weight:700}.beneficiary{align-items:center;display:inline-flex}.beneficiary i{color:#8a9bb2;margin-right:.4rem}.status-badge{border-radius:14px;display:inline-flex;font-size:.66rem;font-weight:700;padding:.32rem .62rem}.status-document-processing{background:#eaf1ff;color:#2f6fed}.status-documents-incomplete{background:#fff5df;color:#a66b00}.status-documents-certified{background:#e8f8f1;color:#16845e}.updated-at{white-space:nowrap}.updated-at i{color:#94a3b8;margin-right:.3rem}.review-button{align-items:center;border-radius:8px;display:inline-flex;font-weight:600;gap:.35rem}.queue-pagination{border-top:1px solid #edf1f6;padding:1rem 1.25rem}.empty-queue{color:#8492a6;padding:3.5rem 1rem;text-align:center}.empty-icon{align-items:center;background:#f1f5f9;border-radius:50%;color:#9aa9bc;display:flex;font-size:1.8rem;height:58px;justify-content:center;margin:0 auto .8rem;width:58px}.empty-queue strong{color:#334155;display:block;margin-bottom:.25rem}
    @media(max-width:767px){.queue-hero{align-items:flex-start;flex-direction:column;padding:1.2rem}.queue-hero h2{font-size:1.3rem}.queue-summary{margin-top:1rem}.queue-table thead{display:none}.queue-table,.queue-table tbody,.queue-table tr,.queue-table td{display:block;width:100%}.queue-table tr{border-bottom:1px solid var(--border);padding:.7rem 0}.queue-table tbody td{border:0;padding:.32rem 1rem}.queue-table td:last-child{text-align:left!important;padding-top:.7rem}.review-button{justify-content:center;width:100%}}
</style>
@endpush

@section('content')
<div class="japic-queue">
    <section class="queue-hero mb-4" aria-labelledby="queue-title">
        <div class="position-relative" style="z-index:1">
            <div class="queue-eyebrow mb-1">Secure document workspace</div>
            <h2 id="queue-title" class="mb-1">Document Review Queue</h2>
            <p class="mb-0"><i class="mdi mdi-shield-check mr-1"></i>Authenticate, validate, and certify submitted E-CLIP documents.</p>
        </div>
        <div class="queue-summary"><i class="mdi mdi-clipboard-text-outline"></i><div><small>Cases on this page</small><strong>{{ $cases->count() }}</strong></div></div>
    </section>

    <section class="card queue-card" aria-label="JAPIC document review cases">
        <div class="card-body"><div class="table-responsive"><table class="table queue-table">
            <thead><tr><th>Case Number</th><th>Beneficiary ID</th><th>Document Status</th><th>Last Updated</th><th class="text-right">Action</th></tr></thead>
            <tbody>
            @forelse($cases as $case)
                <tr>
                    <td><a class="case-number" href="{{ route('japic.eclip.show', $case) }}">{{ $case->case_number }}</a></td>
                    <td><span class="beneficiary"><i class="mdi mdi-account-key-outline"></i>{{ $case->formerRebel->classified_id }}</span></td>
                    <td><span class="status-badge status-{{ str($case->status->value)->replace('_', '-') }}">{{ $case->status->label() }}</span></td>
                    <td><span class="updated-at"><i class="mdi mdi-clock-outline"></i>{{ $case->updated_at->format('M d, Y · h:i A') }}</span></td>
                    <td class="text-right"><a href="{{ route('japic.eclip.show', $case) }}" class="btn btn-sm btn-outline-primary review-button">Review Documents <i class="mdi mdi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty-queue"><div class="empty-icon"><i class="mdi mdi-clipboard-check-outline"></i></div><strong>Document queue is clear</strong><span>No E-CLIP cases currently require JAPIC document processing.</span></div></td></tr>
            @endforelse
            </tbody>
        </table></div></div>
        @if($cases->hasPages())<div class="queue-pagination">{{ $cases->links() }}</div>@endif
    </section>
</div>
@endsection
