@extends('layouts.skydash-v')
@section('title', 'E-CLIP Cases')
@section('heading', 'E-CLIP Case Management')

@push('styles')
<style>
    .eclip-cases{--navy:#173b74;--primary:#2f6fed;--border:#e7ecf3}.cases-hero{align-items:center;background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;display:flex;justify-content:space-between;overflow:hidden;padding:1.5rem;position:relative}.cases-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:190px;position:absolute;right:-45px;top:-95px;width:190px}.hero-content{position:relative;z-index:1}.hero-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;opacity:.75;text-transform:uppercase}.cases-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.cases-hero p{font-size:.8rem;opacity:.86}.create-button{align-items:center;background:#fff;border:1px solid rgba(255,255,255,.8);border-radius:12px;box-shadow:0 8px 20px rgba(12,42,91,.24);color:#204f9e;display:inline-flex;font-size:.78rem;font-weight:700;letter-spacing:.01em;padding:.55rem .9rem .55rem .55rem;position:relative;transition:box-shadow .18s ease,transform .18s ease,background .18s ease;z-index:1}.create-button i{align-items:center;background:#e8f0ff;border-radius:9px;color:#2f6fed;display:flex;font-size:1.05rem;height:34px;justify-content:center;margin-right:.65rem;transition:background .18s ease,color .18s ease;width:34px}.create-button:hover{background:#f8fbff;box-shadow:0 11px 24px rgba(12,42,91,.3);color:#173f85;text-decoration:none;transform:translateY(-2px)}.create-button:hover i{background:#2f6fed;color:#fff}.create-button:focus{box-shadow:0 0 0 4px rgba(255,255,255,.28),0 8px 20px rgba(12,42,91,.24);outline:0}.create-button:active{box-shadow:0 4px 10px rgba(12,42,91,.2);transform:translateY(0)}.cases-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045);overflow:hidden}.cases-card .card-body{padding:0}.cases-table{margin:0}.cases-table thead th{background:#f7f9fc;border:0;color:#718096;font-size:.68rem;font-weight:700;letter-spacing:.05em;padding:.9rem 1rem;text-transform:uppercase;white-space:nowrap}.cases-table tbody td{border-color:#edf1f6;color:#52616f;font-size:.8rem;padding:1rem;vertical-align:middle}.case-number{color:#244a83;font-weight:700}.detail{align-items:center;display:inline-flex}.detail i{color:#8a9bb2;margin-right:.4rem}.status-badge{border-radius:14px;display:inline-flex;font-size:.64rem;font-weight:700;padding:.32rem .6rem}.status-draft{background:#f1f5f9;color:#64748b}.status-submitted-for-eligibility,.status-eligibility-review-in-progress,.status-document-processing,.status-assistance-assessment,.status-submitted-for-dilg-review{background:#eaf1ff;color:#2f6fed}.status-returned-for-correction,.status-documents-incomplete,.status-returned-for-assessment-revision{background:#fff5df;color:#9a6700}.status-eligible,.status-documents-certified,.status-approved,.status-funds-allocated,.status-funds-transferred,.status-assistance-released,.status-completed{background:#e8f8f1;color:#16845e}.status-ineligible,.status-rejected,.status-cancelled{background:#fff1f2;color:#c2414f}.submitted-at{white-space:nowrap}.submitted-at i{color:#94a3b8;margin-right:.3rem}.view-button{align-items:center;border-radius:8px;display:inline-flex;font-weight:600;gap:.35rem}.cases-pagination{border-top:1px solid #edf1f6;padding:1rem 1.25rem}.empty-cases{color:#8492a6;padding:3.5rem 1rem;text-align:center}.empty-icon{align-items:center;background:#f1f5f9;border-radius:50%;color:#9aa9bc;display:flex;font-size:1.8rem;height:58px;justify-content:center;margin:0 auto .8rem;width:58px}.empty-cases strong{color:#334155;display:block;margin-bottom:.25rem}.empty-cases .create-empty{font-weight:600;margin-top:.8rem}
    .create-button,.create-button:hover,.create-button:focus,.create-button:active{text-decoration:none!important}
    @media(max-width:767px){.cases-hero{align-items:stretch;flex-direction:column;padding:1.2rem}.cases-hero h2{font-size:1.3rem}.create-button{justify-content:center;margin-top:1rem;width:100%}.cases-table thead{display:none}.cases-table,.cases-table tbody,.cases-table tr,.cases-table td{display:block;width:100%}.cases-table tr{border-bottom:1px solid var(--border);padding:.7rem 0}.cases-table tbody td{border:0;padding:.32rem 1rem}.cases-table td:last-child{text-align:left!important;padding-top:.7rem}.view-button{justify-content:center;width:100%}}
</style>
@endpush

@section('content')
<div class="eclip-cases">
    <section class="cases-hero mb-4" aria-labelledby="cases-title">
        <div class="hero-content"><div class="hero-eyebrow mb-1">MBLRC case workspace</div><h2 id="cases-title" class="mb-1">E-CLIP Cases</h2><p class="mb-0"><i class="mdi mdi-shield-check mr-1"></i>Create and monitor beneficiary cases across the E-CLIP workflow.</p></div>
        <a href="{{ route('mblrc.eclip.create') }}" class="create-button"><i class="mdi mdi-plus-circle-outline"></i>Create E-CLIP Case</a>
    </section>

    <section class="card cases-card" aria-label="E-CLIP cases">
        <div class="card-body"><div class="table-responsive"><table class="table cases-table">
            <thead><tr><th>Case Number</th><th>Beneficiary ID</th><th>Municipality</th><th>Status</th><th>Submitted</th><th class="text-right">Action</th></tr></thead>
            <tbody>
            @forelse($cases as $case)
                <tr>
                    <td><a class="case-number" href="{{ route('mblrc.eclip.show', $case) }}">{{ $case->case_number }}</a></td>
                    <td><span class="detail"><i class="mdi mdi-account-key-outline"></i>{{ $case->formerRebel->classified_id }}</span></td>
                    <td><span class="detail"><i class="mdi mdi-map-marker-outline"></i>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</span></td>
                    <td><span class="status-badge status-{{ str($case->status->value)->replace('_', '-') }}">{{ $case->status->label() }}</span></td>
                    <td><span class="submitted-at"><i class="mdi mdi-clock-outline"></i>{{ $case->submitted_at?->format('M d, Y · h:i A') ?? 'Draft—not submitted' }}</span></td>
                    <td class="text-right"><a class="btn btn-sm btn-outline-primary view-button" href="{{ route('mblrc.eclip.show', $case) }}">View Case <i class="mdi mdi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-cases"><div class="empty-icon"><i class="mdi mdi-clipboard-text-outline"></i></div><strong>No E-CLIP cases yet</strong><span>Create a case to begin monitoring a beneficiary through the E-CLIP workflow.</span><div><a href="{{ route('mblrc.eclip.create') }}" class="btn btn-sm btn-outline-primary create-empty"><i class="mdi mdi-plus mr-1"></i>Create First Case</a></div></div></td></tr>
            @endforelse
            </tbody>
        </table></div></div>
        @if($cases->hasPages())<div class="cases-pagination">{{ $cases->links() }}</div>@endif
    </section>
</div>
@endsection
