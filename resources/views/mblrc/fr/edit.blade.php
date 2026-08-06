@extends('layouts.skydash-v')
@section('title', 'Edit FR')
@section('heading', 'Edit Former Rebel')

@push('styles')
<style>
    .fr-editor{--primary:#2f6fed;--navy:#172b4d}.editor-back{align-items:center;color:#64748b;display:inline-flex;font-size:.8rem;font-weight:600;margin-bottom:1.1rem;text-decoration:none!important}.editor-back i{margin-right:.4rem}.editor-header{align-items:center;background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:15px;color:#fff;display:flex;justify-content:space-between;margin-bottom:1.2rem;overflow:hidden;padding:1.3rem 1.4rem;position:relative}.editor-header::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:160px;position:absolute;right:-35px;top:-85px;width:160px}.editor-heading{align-items:center;display:flex;position:relative;z-index:1}.editor-icon{align-items:center;background:rgba(255,255,255,.17);border-radius:10px;display:flex;flex:0 0 42px;font-size:1.2rem;height:42px;justify-content:center;margin-right:.9rem;width:42px}.editor-header h2{color:#fff;font-size:1.15rem;font-weight:700;margin:0 0 .15rem}.editor-header p{font-size:.7rem;margin:0;opacity:.82}.profile-badge{background:rgba(255,255,255,.17);border:1px solid rgba(255,255,255,.25);border-radius:14px;font-size:.68rem;font-weight:700;padding:.35rem .65rem;position:relative;z-index:1}.bottom-actions{display:flex;justify-content:flex-end;margin-top:1rem}.save-button{border-radius:9px;font-size:.74rem;font-weight:600;padding:.65rem 1rem}.save-button i{margin-right:.35rem}@media(max-width:767px){.editor-header{align-items:flex-start;flex-direction:column}.profile-badge{margin-top:.8rem}.bottom-actions .save-button{width:100%}}
</style>
@endpush

@section('content')
<div class="fr-editor"><a href="{{ route('mblrc.fr.show', $fr) }}" class="editor-back"><i class="mdi mdi-arrow-left"></i>Back to profile</a><form method="POST" action="{{ route('mblrc.fr.update', $fr) }}" data-fr-form>
    @csrf
    @method('PUT')
    <header class="editor-header"><div class="editor-heading"><div class="editor-icon"><i class="mdi mdi-account-edit"></i></div><div><h2>Edit Former Rebel Profile</h2><p>Update authorized profile and background information.</p></div></div><span class="profile-badge">{{ $fr->classified_id }}</span></header>

    @include('mblrc.fr._form')

    <div class="bottom-actions"><button type="submit" class="btn btn-primary save-button" data-submit-button><i class="mdi mdi-content-save"></i>Save Changes</button></div>
</form></div>
@endsection

@push('scripts')
<script>
(function () {
    const muni = document.querySelector('[data-barangay-source]');
    if (!muni) return;
    const target = document.querySelector(muni.dataset.barangayTarget);
    if (!target) return;
    muni.addEventListener('change', async () => {
        target.innerHTML = '<option value="">Loading…</option>';
        if (!muni.value) {
            target.innerHTML = '<option value="">Select barangay</option>';
            return;
        }
        const url = `${muni.dataset.barangaySource}?municipality_id=${muni.value}`;
        const rows = await fetch(url, { headers: { Accept: 'application/json' } }).then((r) => r.json());
        target.innerHTML = '<option value="">Select barangay</option>'
            + rows.map((b) => `<option value="${b.id}">${b.name}</option>`).join('');
    });
})();
const frForm=document.querySelector('[data-fr-form]');if(frForm){frForm.addEventListener('submit',function(){const button=frForm.querySelector('[data-submit-button]');button.disabled=true;button.innerHTML='<i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...';});}
</script>
@endpush
