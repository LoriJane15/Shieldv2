@extends('layouts.skydash-v')
@section('title', 'Assistance Release')
@section('heading', 'Local E-CLIP Committee')

@push('styles')
<style>
    .release-page{--primary:#2f6fed;--navy:#172b4d;--border:#e7ecf3}.release-back{align-items:center;color:#64748b;display:inline-flex;font-size:.82rem;font-weight:600;gap:.4rem;margin-bottom:1rem}.release-back:hover{color:#2f6fed;text-decoration:none}.case-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;overflow:hidden;padding:1.5rem;position:relative}.case-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:180px;position:absolute;right:-45px;top:-90px;width:180px}.case-eyebrow{font-size:.7rem;font-weight:700;letter-spacing:.1em;opacity:.75;text-transform:uppercase}.case-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.case-meta{display:flex;flex-wrap:wrap;gap:.7rem 1.5rem}.case-meta span{align-items:center;display:inline-flex;font-size:.82rem;gap:.4rem;opacity:.9}.case-status{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);border-radius:18px;font-size:.7rem;font-weight:700;padding:.4rem .75rem;position:relative;z-index:1}
    .money-card,.release-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045)}.money-card{overflow:hidden;position:relative}.money-card::before{background:var(--money-color);content:'';height:4px;left:0;position:absolute;right:0;top:0}.money-card .card-body{align-items:center;display:flex;gap:.85rem;padding:1.15rem}.money-icon{align-items:center;background:var(--money-bg);border-radius:10px;color:var(--money-color);display:flex;flex:0 0 42px;font-size:1.2rem;height:42px;justify-content:center}.money-card small{color:#718096;display:block;font-size:.68rem;font-weight:700;text-transform:uppercase}.money-card strong{color:var(--navy);display:block;font-size:1.2rem;margin-top:.15rem}.money-transferred{--money-bg:#eaf1ff;--money-color:#2f6fed}.money-released{--money-bg:#e8f8f1;--money-color:#20a779}.money-remaining{--money-bg:#fff5dc;--money-color:#d89400}.release-progress{background:#edf2f7;border-radius:7px;height:8px;overflow:hidden}.release-progress span{background:linear-gradient(90deg,#2f6fed,#20a779);display:block;height:100%}
    .release-card .card-body{padding:1.4rem}.section-icon{align-items:center;background:#eaf1ff;border-radius:10px;color:#2f6fed;display:flex;flex:0 0 40px;font-size:1.15rem;height:40px;justify-content:center;margin-right:1rem!important;width:40px}.section-title{color:var(--navy);font-size:1rem;font-weight:700;margin:0 0 .2rem}.section-subtitle{color:#8492a6;font-size:.76rem;margin:0}.field-label{color:#42526b;font-size:.77rem;font-weight:600}.optional{color:#94a3b8;font-size:.68rem;font-weight:400}.form-control{border-color:#dfe5ee;border-radius:8px}.form-control:focus{border-color:#80a6ee;box-shadow:0 0 0 3px rgba(47,111,237,.1)}.currency-input{position:relative}.currency-symbol{align-items:center;color:#64748b;display:flex;height:100%;justify-content:center;left:0;pointer-events:none;position:absolute;top:0;width:2rem;z-index:2}.currency-input input{padding-left:2rem}
    .ack-upload{background:#f8fafc;border:1px dashed #aebbd0;border-radius:11px;padding:.9rem;transition:.2s}.ack-upload:focus-within{background:#f2f6ff;border-color:#2f6fed;box-shadow:0 0 0 3px rgba(47,111,237,.1)}.ack-picker{align-items:center;cursor:pointer;display:flex;gap:.7rem;margin:0}.ack-icon{align-items:center;background:#eaf1ff;border-radius:9px;color:#2f6fed;display:flex;flex:0 0 40px;font-size:1.1rem;height:40px;justify-content:center}.ack-title{color:#334155;display:block;font-size:.8rem;font-weight:600}.ack-help,.ack-name{color:#8492a6;display:block;font-size:.69rem}.ack-name{color:#64748b;margin-top:.15rem;overflow-wrap:anywhere}.ack-upload input[type=file]{border:0;clip:rect(0,0,0,0);height:1px;overflow:hidden;padding:0;position:absolute;white-space:nowrap;width:1px}.record-button{border-radius:9px;font-weight:600;padding:.65rem 1rem}
    .release-back{gap:0}.release-back>i{margin-right:.4rem}.case-meta span{gap:0}.case-meta span>i{flex:0 0 auto;margin-right:.45rem}.money-card .card-body{gap:0}.money-icon{margin-right:.85rem}.ack-picker{gap:0}.ack-icon{margin-right:.7rem}.history-table{margin:0}.history-table thead th{background:#f7f9fc;border:0;color:#718096;font-size:.69rem;font-weight:700;letter-spacing:.04em;padding:.8rem;text-transform:uppercase;white-space:nowrap}.history-table tbody td{border-color:#edf1f6;color:#52616f;font-size:.78rem;padding:.8rem;vertical-align:middle}.reference{color:#244a83;font-weight:700}.amount{color:#173b74;font-weight:700;white-space:nowrap}.ack-link{align-items:center;background:#eef4ff;border-radius:8px;color:#2f6fed;display:inline-flex;font-size:.72rem;font-weight:600;gap:0;padding:.4rem .6rem}.ack-link>i{margin-right:.35rem}.ack-link:hover{background:#dfeaff;color:#1f55bd;text-decoration:none}.empty-state{color:#8492a6;padding:2.5rem 1rem;text-align:center}.empty-state i{display:block;font-size:2rem;margin-bottom:.4rem}.security-note{align-items:flex-start;background:#f7f9fc;border-radius:9px;color:#718096;display:flex;font-size:.71rem;gap:0;padding:.7rem .8rem}.security-note>i{flex:0 0 auto;margin-right:.5rem;margin-top:.05rem}
    @media(max-width:767px){.case-hero{padding:1.2rem}.case-hero h2{font-size:1.3rem}.case-status{margin-top:.8rem}.release-card .card-body{padding:1.1rem}.ack-upload .btn{margin-top:.8rem;width:100%}.history-table thead{display:none}.history-table,.history-table tbody,.history-table tr,.history-table td{display:block;width:100%}.history-table tr{border-bottom:1px solid #e7ecf3;padding:.65rem 0}.history-table tbody td{border:0;padding:.3rem 1rem}}
</style>
@endpush

@section('content')
@php
    $transferred = (float)$case->fundTransactions->where('type','transfer')->sum('amount');
    $released = (float)$case->assistanceReleases->sum('amount');
    $remaining = max(0, $transferred - $released);
    $releasePercentage = $transferred > 0 ? min(100, ($released / $transferred) * 100) : 0;
@endphp
<div class="release-page">
    <a href="{{ route('local_eclip.cases.index') }}" class="release-back"><i class="mdi mdi-arrow-left"></i> Back to assistance release queue</a>
    <section class="case-hero mb-4" aria-labelledby="release-case-number"><div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start position-relative" style="z-index:1"><div><div class="case-eyebrow mb-1">Assistance Release</div><h2 id="release-case-number" class="mb-3">{{ $case->case_number }}</h2><div class="case-meta"><span><i class="mdi mdi-account-key-outline"></i>{{ $case->formerRebel->classified_id }}</span><span><i class="mdi mdi-map-marker-outline"></i>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</span></div></div><span class="case-status"><i class="mdi mdi-progress-check mr-1"></i>{{ $case->status->label() }}</span></div></section>

    <div class="row">
        <div class="col-md-4 mb-3"><div class="card money-card money-transferred h-100"><div class="card-body"><div class="money-icon"><i class="mdi mdi-bank-transfer-in"></i></div><div><small>Transferred</small><strong>₱{{ number_format($transferred,2) }}</strong></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card money-card money-released h-100"><div class="card-body"><div class="money-icon"><i class="mdi mdi-cash-multiple"></i></div><div><small>Released</small><strong>₱{{ number_format($released,2) }}</strong></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card money-card money-remaining h-100"><div class="card-body"><div class="money-icon"><i class="mdi mdi-wallet-outline"></i></div><div><small>Remaining</small><strong>₱{{ number_format($remaining,2) }}</strong></div></div></div></div>
    </div>
    <div class="d-flex justify-content-between text-muted small mb-1"><span>Release progress</span><span>{{ number_format($releasePercentage,1) }}%</span></div><div class="release-progress mb-4" role="progressbar" aria-valuenow="{{ $releasePercentage }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $releasePercentage }}%"></span></div>

    <div class="row">
        <div class="col-xl-7">
            @can('releaseAssistance',$case)
                <section class="card release-card mb-4" aria-labelledby="record-release-title"><div class="card-body">
                    <div class="d-flex align-items-center mb-4"><div class="section-icon mr-3"><i class="mdi mdi-cash"></i></div><div><h3 id="record-release-title" class="section-title">Record Assistance Release</h3><p class="section-subtitle">Enter release details and attach the signed acknowledgment document.</p></div></div>
                    <form method="POST" action="{{ route('local_eclip.releases.store',$case) }}" enctype="multipart/form-data" data-release-form>@csrf
                        <div class="row">
                            <div class="form-group col-md-4"><label for="amount" class="field-label">Released Amount</label><div class="currency-input"><span class="currency-symbol" aria-hidden="true">₱</span><input id="amount" type="number" name="amount" value="{{ old('amount') }}" min="0.01" max="{{ number_format($remaining,2,'.','') }}" step="0.01" class="form-control @error('amount') is-invalid @enderror" required></div>@error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Maximum ₱{{ number_format($remaining,2) }}</small></div>
                            <div class="form-group col-md-4"><label for="release_reference" class="field-label">Release Reference</label><input id="release_reference" name="release_reference" value="{{ old('release_reference') }}" maxlength="150" class="form-control @error('release_reference') is-invalid @enderror" placeholder="Official reference number" required>@error('release_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="form-group col-md-4"><label for="released_at" class="field-label">Release Date</label><input id="released_at" type="date" name="released_at" value="{{ old('released_at') }}" max="{{ now()->toDateString() }}" class="form-control @error('released_at') is-invalid @enderror" required>@error('released_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div>
                        <div class="form-group"><label for="remarks" class="field-label">Remarks <span class="optional">(optional)</span></label><textarea id="remarks" name="remarks" rows="3" maxlength="5000" class="form-control @error('remarks') is-invalid @enderror" placeholder="Add release notes or conditions...">{{ old('remarks') }}</textarea>@error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="form-group"><label class="field-label">Acknowledgment Document</label><div class="ack-upload"><input id="acknowledgment" type="file" name="acknowledgment" accept=".pdf,.jpg,.jpeg,.png" required data-file-input><div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between"><label for="acknowledgment" class="ack-picker"><span class="ack-icon"><i class="mdi mdi-cloud-upload-outline"></i></span><span><span class="ack-title">Choose signed acknowledgment</span><span class="ack-help">PDF, JPG, or PNG · Maximum 10 MB</span><span class="ack-name" data-file-name>No file selected</span></span></label><button class="btn btn-primary record-button" data-submit-button><i class="mdi mdi-content-save mr-1"></i>Record Release</button></div></div>@error('acknowledgment')<div class="text-danger small mt-2" role="alert">{{ $message }}</div>@enderror</div>
                        @error('status')<div class="alert alert-danger py-2" role="alert">{{ $message }}</div>@enderror
                    </form>
                    <div class="security-note"><i class="mdi mdi-shield-lock-outline"></i><span>The acknowledgment is stored privately. Recording a release creates an immutable financial history entry.</span></div>
                </div></section>
            @endcan
        </div>

        <div class="col-xl-5">
            <section class="card release-card mb-4" aria-labelledby="history-title"><div class="card-body">
                <div class="d-flex align-items-center mb-4"><div class="section-icon mr-3"><i class="mdi mdi-history"></i></div><div><h3 id="history-title" class="section-title">Immutable Release History</h3><p class="section-subtitle">Recorded assistance releases and acknowledgments.</p></div></div>
                <div class="table-responsive"><table class="table history-table"><thead><tr><th>Reference</th><th>Amount</th><th>Date</th><th>Recorded By</th><th>Proof</th></tr></thead><tbody>
                    @forelse($case->assistanceReleases->sortByDesc('created_at') as $release)
                        <tr><td><span class="reference">{{ $release->release_reference }}</span></td><td><span class="amount">₱{{ number_format((float)$release->amount,2) }}</span></td><td>{{ $release->released_at->format('M d, Y') }}</td><td>{{ $release->releaser->name }}</td><td><a class="ack-link" target="_blank" rel="noopener" href="{{ route('local_eclip.releases.acknowledgment',$release) }}"><i class="mdi mdi-file-eye-outline"></i>View</a></td></tr>
                    @empty<tr><td colspan="5"><div class="empty-state"><i class="mdi mdi-clock-outline"></i>No assistance releases have been recorded.</div></td></tr>@endforelse
                </tbody></table></div>
            </div></section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var releaseForm=document.querySelector('[data-release-form]');
if(releaseForm){var input=releaseForm.querySelector('[data-file-input]');var name=releaseForm.querySelector('[data-file-name]');var button=releaseForm.querySelector('[data-submit-button]');input.addEventListener('change',function(){name.textContent=input.files.length?input.files[0].name:'No file selected';});releaseForm.addEventListener('submit',function(){button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Recording...';});}
</script>
@endpush
