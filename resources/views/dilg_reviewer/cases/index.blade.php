@extends('layouts.skydash-v')
@section('title', 'DILG E-CLIP Review')
@section('heading', 'DILG Review and Approval')

@push('styles')
<style>
    .review-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;overflow:hidden;padding:1.5rem;position:relative}.review-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:180px;position:absolute;right:-45px;top:-90px;width:180px}.review-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.review-hero p{font-size:.82rem;opacity:.85}.review-level{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.28);border-radius:12px;padding:.65rem 1rem;position:relative;z-index:1}.review-level small{display:block;font-size:.65rem;opacity:.78;text-transform:uppercase}.review-level strong{font-size:.9rem}.queue-card{border:1px solid #e7ecf3;border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045);overflow:hidden}.queue-card .card-body{padding:0}.queue-table{margin:0}.queue-table thead th{background:#f7f9fc;border:0;color:#718096;font-size:.7rem;font-weight:700;letter-spacing:.04em;padding:.9rem 1rem;text-transform:uppercase;white-space:nowrap}.queue-table tbody td{border-color:#edf1f6;color:#52616f;font-size:.82rem;padding:.9rem 1rem;vertical-align:middle}.case-link{color:#244a83;font-weight:700}.category{color:#42526b;font-weight:600}.amount{color:#173b74;font-weight:700;white-space:nowrap}.status-badge{background:#eaf1ff;border-radius:14px;color:#2f6fed;display:inline-flex;font-size:.68rem;font-weight:700;padding:.32rem .65rem}.review-btn{border-radius:8px;font-weight:600}.queue-pagination{border-top:1px solid #edf1f6;padding:1rem 1.25rem}.empty-queue{color:#8492a6;padding:3rem 1rem;text-align:center}.empty-queue i{color:#bcc7d6;display:block;font-size:2.3rem;margin-bottom:.55rem}
    @media(max-width:767px){.review-hero{padding:1.2rem}.review-hero h2{font-size:1.3rem}.review-level{margin-top:1rem}.queue-table thead{display:none}.queue-table,.queue-table tbody,.queue-table tr,.queue-table td{display:block;width:100%}.queue-table tr{border-bottom:1px solid #e7ecf3;padding:.65rem 0}.queue-table tbody td{border:0;padding:.35rem 1rem}.queue-table td:last-child{text-align:left!important}}
</style>
@endpush

@section('content')
<div class="review-hero mb-4"><div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between position-relative" style="z-index:1"><div><h2 class="mb-1">{{ $reviewLevel }} Review Queue</h2><p class="mb-0"><i class="mdi mdi-file-document-check-outline mr-1"></i>Assessments currently available at your assigned review level.</p></div><div class="review-level"><small>Review office</small><strong>DILG {{ $reviewLevel }}</strong></div></div></div>
<section class="card queue-card" aria-label="DILG review cases"><div class="card-body"><div class="table-responsive"><table class="table queue-table"><thead><tr><th>Case Number</th><th>Beneficiary ID</th><th>Assistance Category</th><th>Assessed Amount</th><th>Status</th><th class="text-right">Action</th></tr></thead><tbody>
@forelse($cases as $case)
    @php
        $revision = $case->assistanceRequest?->latestRevision;
    @endphp
    <tr><td><a class="case-link" href="{{ route('dilg_reviewer.cases.show',$case) }}">{{ $case->case_number }}</a></td><td><i class="mdi mdi-account-key-outline text-muted mr-1"></i>{{ $case->formerRebel->classified_id }}</td><td><span class="category">{{ $revision?->category?->name ?? 'Not recorded' }}</span></td><td><span class="amount">{{ $revision?->assessed_amount !== null ? '₱'.number_format((float)$revision->assessed_amount,2) : 'Pending' }}</span></td><td><span class="status-badge">{{ $case->status->label() }}</span></td><td class="text-right"><a href="{{ route('dilg_reviewer.cases.show',$case) }}" class="btn btn-sm btn-outline-primary review-btn">Review Case <i class="mdi mdi-arrow-right ml-1"></i></a></td></tr>
@empty<tr><td colspan="6"><div class="empty-queue"><i class="mdi mdi-clipboard-check-outline"></i><strong class="d-block text-dark mb-1">Review queue is clear</strong><span>No cases are currently available for {{ strtolower($reviewLevel) }} review.</span></div></td></tr>@endforelse
</tbody></table></div></div>@if($cases->hasPages())<div class="queue-pagination">{{ $cases->links() }}</div>@endif</section>
@endsection
