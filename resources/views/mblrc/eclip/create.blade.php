@extends('layouts.skydash-v')
@section('title', 'Create E-CLIP Case')
@section('heading', 'E-CLIP Case Management')

@push('styles')
<style>
    .create-case{--navy:#172b4d;--primary:#2f6fed;--border:#e7ecf3;max-width:820px;margin:0 auto}.case-back{align-items:center;color:#64748b;display:inline-flex;font-size:.8rem;font-weight:600;margin-bottom:1.2rem;text-decoration:none!important}.case-back i{margin-right:.4rem}.case-back:hover{color:var(--primary)}.create-card{border:1px solid var(--border);border-radius:16px;box-shadow:0 8px 24px rgba(23,43,77,.07);overflow:hidden}.create-header{background:linear-gradient(125deg,#173b74,#2f6fed);color:#fff;overflow:hidden;padding:1.5rem;position:relative}.create-header::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:170px;position:absolute;right:-40px;top:-90px;width:170px}.header-content{align-items:center;display:flex;position:relative;z-index:1}.header-icon{align-items:center;background:rgba(255,255,255,.17);border:1px solid rgba(255,255,255,.25);border-radius:12px;display:flex;flex:0 0 46px;font-size:1.3rem;height:46px;justify-content:center;margin-right:1rem;width:46px}.create-header h2{color:#fff;font-size:1.25rem;font-weight:700;margin:0 0 .2rem}.create-header p{font-size:.75rem;line-height:1.45;margin:0;opacity:.84}.create-body{padding:1.5rem}.draft-notice{align-items:flex-start;background:#eef5ff;border:1px solid #ccddfb;border-radius:10px;color:#476180;display:flex;font-size:.71rem;line-height:1.5;margin-bottom:1.25rem;padding:.8rem}.draft-notice i{color:#2f6fed;flex:0 0 auto;font-size:1rem;margin-right:.5rem;margin-top:.05rem}.field-label{color:#42526b;font-size:.76rem;font-weight:700}.field-help{color:#8492a6;display:block;font-size:.67rem;font-weight:400;margin-top:.15rem}.form-control{border-color:#dfe5ee;border-radius:9px;height:44px}.form-control:focus{border-color:#80a6ee;box-shadow:0 0 0 3px rgba(47,111,237,.1)}.form-actions{align-items:center;border-top:1px solid #edf1f6;display:flex;justify-content:flex-end;margin-top:1.4rem;padding-top:1.2rem}.cancel-button,.create-draft-button{border-radius:9px;font-size:.75rem;font-weight:600;padding:.62rem .9rem}.cancel-button{margin-right:.6rem}.create-draft-button i{margin-right:.35rem}.empty-beneficiaries{align-items:flex-start;background:#fff9e9;border:1px solid #f0dca6;border-radius:10px;color:#80601a;display:flex;font-size:.72rem;padding:.85rem}.empty-beneficiaries i{font-size:1rem;margin-right:.5rem}
    @media(max-width:767px){.create-header,.create-body{padding:1.15rem}.header-icon{margin-right:.8rem}.form-actions{align-items:stretch;flex-direction:column-reverse}.cancel-button,.create-draft-button{margin:0;width:100%}.cancel-button{margin-top:.6rem}}
</style>
@endpush

@section('content')
<div class="create-case">
    <a href="{{ route('mblrc.eclip.index') }}" class="case-back"><i class="mdi mdi-arrow-left"></i>Back to E-CLIP cases</a>
    <section class="card create-card" aria-labelledby="create-case-title">
        <header class="create-header"><div class="header-content"><div class="header-icon"><i class="mdi mdi-clipboard-plus"></i></div><div><h2 id="create-case-title">Create E-CLIP Case</h2><p>Select an eligible existing beneficiary to begin a new case.</p></div></div></header>
        <div class="create-body">
            <div class="draft-notice"><i class="mdi mdi-information-outline"></i><span>The new case will be saved as a draft. Review its information before submitting it to LSWDO for eligibility assessment.</span></div>
            @if($formerRebels->isEmpty())
                <div class="empty-beneficiaries"><i class="mdi mdi-alert-outline"></i><span>No beneficiaries are currently available for a new E-CLIP case. Beneficiaries with an active case are excluded.</span></div>
            @else
                <form method="POST" action="{{ route('mblrc.eclip.store') }}" data-create-form>@csrf
                    <div class="form-group mb-0"><label for="former_rebel_id" class="field-label">Beneficiary <span class="field-help">Only beneficiaries without another active E-CLIP case are listed.</span></label><select id="former_rebel_id" name="former_rebel_id" class="form-control @error('former_rebel_id') is-invalid @enderror" required><option value="">Select a beneficiary</option>@foreach($formerRebels as $fr)<option value="{{ $fr->id }}" @selected(old('former_rebel_id') == $fr->id)>{{ $fr->classified_id }} — {{ $fr->lastname }}, {{ $fr->firstname }}</option>@endforeach</select>@error('former_rebel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-actions"><a href="{{ route('mblrc.eclip.index') }}" class="btn btn-light cancel-button">Cancel</a><button class="btn btn-primary create-draft-button" data-submit-button><i class="mdi mdi-content-save"></i>Create Draft Case</button></div>
                </form>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
var createForm=document.querySelector('[data-create-form]');if(createForm){createForm.addEventListener('submit',function(){var button=createForm.querySelector('[data-submit-button]');button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Creating Draft...';});}
</script>
@endpush
