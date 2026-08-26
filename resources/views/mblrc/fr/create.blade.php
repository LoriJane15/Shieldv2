@extends('layouts.skydash-v')
@section('title', 'Register FR/FVE Beneficiary')
@section('heading', 'Register FR/FVE Beneficiary')

@php
    $addressFields = ['municipality_id', 'barangay_id', 'residential_address'];
    $backgroundFields = ['surrender_date', 'surrender_reason', 'batch_year', 'batch_section', 'status'];
    $initialStep = collect($backgroundFields)->contains(fn ($field) => $errors->has($field))
        ? 3
        : (collect($addressFields)->contains(fn ($field) => $errors->has($field)) ? 2 : 1);
@endphp

@push('styles')
<style>
    .registration-workflow{--shield-purple:#401595;--shield-purple-dark:#280274;--shield-orange:#f26a21;--shield-green:#16845e;--shield-ink:#202a3b;--shield-muted:#637086;--shield-border:#dfe4ec;margin:0 auto;max-width:1320px;padding-bottom:1rem}.registration-back{align-items:center;color:#4c5a70;display:inline-flex;font-size:.94rem;font-weight:700;gap:.45rem;margin-bottom:1rem;min-height:44px;text-decoration:none!important}.registration-back:hover,.registration-back:focus{color:var(--shield-purple)}.registration-hero{align-items:center;background:linear-gradient(115deg,#280274 0%,#401595 62%,#351080 100%);border-radius:16px;box-shadow:0 12px 30px rgba(40,2,116,.16);color:#fff;display:flex;gap:1.2rem;isolation:isolate;justify-content:space-between;margin-bottom:1.1rem;overflow:hidden;padding:1.55rem 1.8rem;position:relative}.registration-hero::before{background-image:linear-gradient(135deg,rgba(255,255,255,.065) 25%,transparent 25%),linear-gradient(315deg,rgba(255,255,255,.045) 25%,transparent 25%);background-position:0 0,28px 28px;background-size:56px 56px;content:"";inset:0;mask-image:linear-gradient(90deg,transparent 15%,#000 72%);pointer-events:none;position:absolute;z-index:-1}.registration-hero::after{background:radial-gradient(circle,rgba(255,255,255,.16) 0 2px,transparent 3px),rgba(255,255,255,.045);background-size:18px 18px,auto;border:1px solid rgba(255,255,255,.08);border-radius:50%;content:"";height:250px;position:absolute;right:-62px;top:-128px;width:250px;z-index:-1}.registration-hero-main{align-items:center;display:flex;gap:1rem;min-width:0;position:relative;z-index:1}.registration-hero-icon{align-items:center;background:linear-gradient(145deg,rgba(255,255,255,.2),rgba(255,255,255,.08));border:1px solid rgba(255,255,255,.22);border-radius:12px;box-shadow:inset 0 1px 0 rgba(255,255,255,.16);display:flex;flex:0 0 56px;font-size:1.7rem;height:56px;justify-content:center;width:56px}.registration-hero h1{color:#fff;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;line-height:1.2;margin:0}.registration-hero p{color:rgba(255,255,255,.88);font-size:.94rem;line-height:1.45;margin:.35rem 0 0}.security-indicator{align-items:center;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);border-radius:999px;color:#fff;display:flex;flex:0 0 auto;font-size:.84rem;font-weight:750;gap:.4rem;min-height:42px;padding:.55rem .85rem;position:relative;z-index:1}.security-indicator i{color:#75e3bc;font-size:1.05rem}.required-note{align-items:center;background:#fff9f1;border:1px solid #f4d6b8;border-radius:10px;color:#5b493a;display:flex;font-size:.86rem;gap:.55rem;margin-bottom:1rem;padding:.75rem .9rem}.required-note i{color:var(--shield-orange);font-size:1.05rem}.required-mark{color:#c62828;font-weight:850}.registration-progress{background:#fff;border:1px solid var(--shield-border);border-radius:13px;box-shadow:0 4px 15px rgba(31,42,61,.045);display:grid;gap:.55rem;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:1.1rem;padding:.7rem}.progress-step{align-items:center;background:#f8f9fb;border:1px solid transparent;border-radius:9px;color:#6c788b;display:flex;gap:.65rem;min-height:58px;padding:.55rem .65rem;text-align:left;width:100%}.progress-step:disabled{cursor:default;opacity:1}.progress-number{align-items:center;background:#e8ebf0;border-radius:50%;color:#5f6b7c;display:flex;flex:0 0 34px;font-size:.74rem;font-weight:850;height:34px;justify-content:center;width:34px}.progress-copy strong{display:block;font-size:.78rem;font-weight:800;line-height:1.2}.progress-copy small{display:block;font-size:.62rem;margin-top:.12rem}.progress-step.is-current{background:#f4effb;border-color:#cdb9e7;color:var(--shield-purple-dark)}.progress-step.is-current .progress-number{background:var(--shield-purple);color:#fff;box-shadow:0 0 0 4px rgba(64,21,149,.11)}.progress-step.is-complete{background:#eff9f5;border-color:#c5e8d9;color:#12694e;cursor:pointer}.progress-step.is-complete .progress-number{background:var(--shield-green);color:#fff}.registration-alert{background:#fff1f2;border:1px solid #efb7bd;border-radius:11px;color:#8c2430;margin-bottom:1rem;padding:1rem}.registration-alert strong{display:block;font-size:.92rem;margin-bottom:.35rem}.registration-alert ul{font-size:.83rem;margin:0;padding-left:1.2rem}.registration-section{background:#fff;border:1px solid var(--shield-border);border-radius:15px;box-shadow:0 8px 24px rgba(31,42,61,.055);padding:1.6rem}.registration-section[hidden]{display:none!important}.registration-section-header{align-items:center;background:linear-gradient(105deg,#f5f0fb 0%,#fbf9fd 62%,#fff8f2 100%);border:1px solid #e5d9ef;border-left:4px solid var(--shield-orange);border-radius:12px;display:flex;gap:.9rem;isolation:isolate;margin-bottom:1.5rem;overflow:hidden;padding:.9rem 1rem;position:relative}.registration-section-header::after{background:radial-gradient(circle,rgba(64,21,149,.11) 0 2px,transparent 2.5px);background-size:14px 14px;content:"";height:110px;opacity:.58;pointer-events:none;position:absolute;right:-12px;top:-30px;transform:rotate(-8deg);width:210px;z-index:-1}.registration-section-header>div{min-width:0;position:relative;z-index:1}.registration-section-number{align-items:center;background:linear-gradient(145deg,#5420af,var(--shield-purple-dark));border:1px solid rgba(255,255,255,.42);border-radius:10px;box-shadow:0 5px 12px rgba(64,21,149,.2);color:#fff;display:flex;flex:0 0 48px;font-size:1rem;font-weight:850;height:48px;justify-content:center;position:relative;z-index:1;width:48px}.registration-section-kicker{color:#b84b12;display:block;font-size:.72rem;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.registration-section-header h2{color:var(--shield-ink);font-size:1.12rem;font-weight:780;line-height:1.3;margin:.14rem 0 0}.registration-field-group+.registration-field-group{border-top:1px solid #e8ebf0;margin-top:1.6rem;padding-top:1.5rem}.registration-field-group>h3{color:#354258;font-size:1rem;font-weight:780;margin:0 0 1.1rem}.registration-field label{align-items:center;color:#273247;display:flex;font-size:.94rem;font-weight:760;gap:.28rem;line-height:1.3;margin-bottom:.48rem;min-height:24px}.field-annotation{color:#718096;font-size:.68rem;font-weight:650;margin-left:auto}.registration-field input,.registration-field select,.registration-field textarea{background:#fff;border:1px solid #cfd6e1;border-radius:9px;color:#202a3b;font-size:1rem;min-height:60px;outline:0;padding:.75rem .9rem;width:100%}.registration-field textarea{line-height:1.55;min-height:145px;resize:vertical}.registration-field input::placeholder,.registration-field textarea::placeholder{color:#8a95a5}.registration-field input:focus,.registration-field select:focus,.registration-field textarea:focus{border-color:var(--shield-purple);box-shadow:0 0 0 4px rgba(64,21,149,.11)}.registration-field [aria-invalid="true"]{border-color:#c93646;box-shadow:0 0 0 3px rgba(201,54,70,.09)}.registration-field select{appearance:none;padding-right:2.8rem}.select-control,.date-control,.readonly-control{position:relative}.select-control>i{color:var(--shield-purple);font-size:1.25rem;pointer-events:none;position:absolute;right:.9rem;top:50%;transform:translateY(-50%)}.date-control>i,.readonly-control>i{color:var(--shield-purple);font-size:1.2rem;left:.9rem;pointer-events:none;position:absolute;top:50%;transform:translateY(-50%);z-index:1}.date-control input,.readonly-control input{padding-left:2.7rem}.readonly-control input,.readonly-output{background:#f1f3f6!important;border-color:#d8dde5!important;color:#4d596c!important}.readonly-output{align-items:center;border:1px solid;border-radius:9px;display:flex;font-size:1rem;font-weight:750;min-height:60px;padding:.75rem .9rem}.field-instruction{color:#657287;display:block;font-size:.76rem;line-height:1.4;margin-top:.42rem}.registration-error{align-items:flex-start;color:#b42333;display:flex;font-size:.78rem;font-weight:650;gap:.3rem;line-height:1.4;margin:.38rem 0 0}.registration-error i{font-size:.9rem}.field-help{display:inline-flex;position:relative}.field-help button{align-items:center;background:transparent;border:0;border-radius:50%;color:var(--shield-purple);display:flex;font-size:1.05rem;height:30px;justify-content:center;padding:0;width:30px}.field-help button:focus{box-shadow:0 0 0 3px rgba(64,21,149,.14);outline:0}.field-help [role="tooltip"]{background:#283549;border-radius:7px;bottom:calc(100% + 7px);color:#fff;font-size:.72rem;font-weight:500;left:50%;line-height:1.4;opacity:0;padding:.55rem .65rem;pointer-events:none;position:absolute;transform:translate(-50%,4px);transition:opacity .15s ease,transform .15s ease;width:230px;z-index:10}.field-help:hover [role="tooltip"],.field-help:focus-within [role="tooltip"]{opacity:1;transform:translate(-50%,0)}.geographic-flow{align-items:center;background:#f6f2fb;border:1px solid #e4daef;border-radius:9px;color:#4c356a;display:flex;font-size:.8rem;font-weight:750;gap:.55rem;margin-bottom:1.35rem;padding:.7rem .85rem}.geographic-flow span{align-items:center;display:flex;gap:.3rem}.geographic-flow>i{color:#9b8aaf}.status-control select{color:#176e52;font-weight:750}.review-notice{align-items:flex-start;background:#f0f8f5;border:1px solid #c4e3d7;border-radius:10px;color:#285d4d;display:flex;font-size:.86rem;gap:.65rem;line-height:1.5;margin-bottom:1.2rem;padding:.85rem .95rem}.review-notice i{font-size:1.3rem}.review-groups{display:grid;gap:1rem}.review-group{border:1px solid #dfe4ec;border-radius:11px;overflow:hidden}.review-group header{align-items:center;background:linear-gradient(100deg,#f6f2fb,#fbf9fd 70%,#fff8f2);border-bottom:1px solid #e5dfed;display:flex;gap:.5rem;padding:.75rem .9rem}.review-group header i{color:var(--shield-purple);font-size:1.1rem}.review-group h3{color:#2c374b;font-size:.94rem;font-weight:800;margin:0}.review-group dl{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));margin:0}.review-group dl>div{border-right:1px solid #edf0f4;padding:.8rem .9rem}.review-group dl>div:last-child{border-right:0}.review-group .review-wide{grid-column:span 2}.review-group dt{color:#768397;font-size:.68rem;font-weight:800;letter-spacing:.03em;text-transform:uppercase}.review-group dd{color:#28354a;font-size:.9rem;font-weight:680;line-height:1.45;margin:.2rem 0 0;overflow-wrap:anywhere}.registration-action-bar{align-items:center;background:rgba(255,255,255,.98);border:1px solid #dce2ea;border-radius:13px;bottom:.75rem;box-shadow:0 10px 32px rgba(24,33,49,.16);display:flex;gap:1rem;justify-content:space-between;margin-top:1.1rem;padding:.75rem;position:sticky;z-index:100}.draft-actions{align-items:center;display:flex;gap:.75rem;min-width:0}.draft-button,.workflow-secondary,.workflow-primary{align-items:center;border-radius:9px;display:inline-flex;font-size:1rem;font-weight:780;gap:.45rem;justify-content:center;min-height:54px;padding:.7rem 1rem}.draft-button,.workflow-secondary{background:#fff;border:1px solid #cbd3de;color:#46546a}.draft-button:hover,.draft-button:focus,.workflow-secondary:hover,.workflow-secondary:focus{background:#f6f7f9;border-color:#9faabb;color:#253247;outline:0}.draft-status{color:#5d6b7f;font-size:.76rem;line-height:1.3}.draft-status.is-saved{color:#167152}.draft-status.is-error{color:#b42333}.workflow-actions{align-items:center;display:flex;gap:.55rem}.workflow-primary{background:var(--shield-purple);border:1px solid var(--shield-purple);color:#fff;min-width:225px}.workflow-primary:hover,.workflow-primary:focus{background:var(--shield-purple-dark);border-color:var(--shield-purple-dark);box-shadow:0 0 0 4px rgba(64,21,149,.12);color:#fff;outline:0}.workflow-primary:disabled,.draft-button:disabled{cursor:not-allowed;opacity:.65}.workflow-primary.is-confirm{background:var(--shield-green);border-color:var(--shield-green)}.workflow-primary.is-confirm:hover,.workflow-primary.is-confirm:focus{background:#0f6548;border-color:#0f6548}@media(max-width:991px){.registration-progress{grid-template-columns:1fr 1fr}.review-group dl{grid-template-columns:1fr 1fr}.registration-action-bar{align-items:stretch}.draft-actions{align-items:flex-start;flex-direction:column;gap:.35rem}.workflow-primary{min-width:200px}}@media(max-width:767px){.registration-hero{align-items:flex-start;flex-direction:column;padding:1.25rem}.security-indicator{align-self:flex-start}.registration-progress{grid-template-columns:1fr}.progress-step{min-height:52px}.registration-section{padding:1.15rem}.registration-section-header{align-items:flex-start}.geographic-flow{align-items:flex-start;flex-direction:column}.geographic-flow>i{transform:rotate(90deg)}.review-group dl{grid-template-columns:1fr}.review-group .review-wide{grid-column:auto}.registration-action-bar{bottom:.35rem;flex-direction:column}.draft-actions,.workflow-actions{width:100%}.draft-actions{align-items:stretch}.draft-button,.workflow-secondary,.workflow-primary{flex:1}.draft-status{text-align:center}}@media(max-width:480px){.registration-hero-main{align-items:flex-start}.registration-hero-icon{display:none}.registration-hero h1{font-size:1.25rem}.registration-hero p{font-size:.85rem}.workflow-actions{flex-direction:column-reverse}.draft-button,.workflow-secondary,.workflow-primary{width:100%}}
</style>
@endpush

@section('content')
<div class="registration-workflow">
    <a href="{{ route('mblrc.fr.index') }}" class="registration-back"><i class="mdi mdi-arrow-left" aria-hidden="true"></i>Back to FR/FVE Registry</a>
    <header class="registration-hero"><div class="registration-hero-main"><span class="registration-hero-icon"><i class="mdi mdi-account-plus-outline" aria-hidden="true"></i></span><div><h1>Register FR/FVE Beneficiary</h1><p>Create a secure beneficiary profile for reintegration monitoring.</p></div></div><span class="security-indicator"><i class="mdi mdi-shield-check" aria-hidden="true"></i>Secure Registration</span></header>
    <div class="required-note"><i class="mdi mdi-information-outline" aria-hidden="true"></i><span><strong>Required fields are marked with *.</strong> Complete each step before reviewing the official record.</span></div>
    <nav class="registration-progress" aria-label="Registration progress">
        @foreach ([1 => ['Personal Information', 'Identity and contact'], 2 => ['Address', 'Current residence'], 3 => ['Background', 'Surrender record'], 4 => ['Review', 'Verify and confirm']] as $step => [$label, $description])
            <button type="button" class="progress-step {{ $step === $initialStep ? 'is-current' : '' }}" data-step-target="{{ $step }}" @if($step !== $initialStep) disabled @endif @if($step === $initialStep) aria-current="step" @endif><span class="progress-number">{{ str_pad((string) $step, 2, '0', STR_PAD_LEFT) }}</span><span class="progress-copy"><strong>{{ $label }}</strong><small>{{ $description }}</small></span></button>
        @endforeach
    </nav>
    @if($errors->any())<div class="registration-alert" role="alert"><strong><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i> Review the highlighted information.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('mblrc.fr.store') }}" data-registration-form data-draft-url="{{ route('mblrc.fr.draft.store') }}" data-initial-step="{{ $initialStep }}">
        @csrf
        @include('mblrc.fr._registration_form')
        <footer class="registration-action-bar" aria-label="Registration actions"><div class="draft-actions"><button type="button" class="draft-button" data-save-draft><i class="mdi mdi-content-save-outline" aria-hidden="true"></i><span>Save Draft</span></button><span class="draft-status {{ $draft ? 'is-saved' : '' }}" data-draft-status role="status" aria-live="polite">@if($draft)<i class="mdi mdi-check-circle-outline" aria-hidden="true"></i> Draft saved {{ $draft->saved_at?->diffForHumans() }}@else Your progress is securely autosaved. @endif</span></div><div class="workflow-actions"><button type="button" class="workflow-secondary" data-step-back hidden><i class="mdi mdi-arrow-left" aria-hidden="true"></i><span>Back</span></button><button type="button" class="workflow-primary" data-step-continue><span>Continue to Address</span><i class="mdi mdi-arrow-right" aria-hidden="true"></i></button></div></footer>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.querySelector('[data-registration-form]');
    if (!form) return;
    const panels = Array.from(form.querySelectorAll('[data-step-panel]'));
    const progressSteps = Array.from(document.querySelectorAll('[data-step-target]'));
    const continueButton = form.querySelector('[data-step-continue]');
    const continueLabel = continueButton.querySelector('span');
    const continueIcon = continueButton.querySelector('i');
    const backButton = form.querySelector('[data-step-back]');
    const backLabel = backButton.querySelector('span');
    const saveButton = form.querySelector('[data-save-draft]');
    const saveLabel = saveButton.querySelector('span');
    const draftStatus = form.querySelector('[data-draft-status]');
    const birthdate = form.elements.birthdate;
    const ageOutput = form.querySelector('[data-calculated-age]');
    const municipality = form.elements.municipality_id;
    const barangay = form.elements.barangay_id;
    let currentStep = Number(form.dataset.initialStep || 1);
    let highestVisitedStep = currentStep;
    let confirmed = false;
    let draftTimer = null;
    let draftSaveRunning = false;
    let draftSavePending = false;
    let draftDirty = false;

    function showStep(step, focusHeading) {
        currentStep = Math.max(1, Math.min(4, step));
        highestVisitedStep = Math.max(highestVisitedStep, currentStep);
        panels.forEach((panel) => { panel.hidden = Number(panel.dataset.stepPanel) !== currentStep; });
        progressSteps.forEach((button) => {
            const target = Number(button.dataset.stepTarget);
            const complete = target < currentStep;
            button.classList.toggle('is-current', target === currentStep);
            button.classList.toggle('is-complete', complete);
            button.disabled = target > currentStep;
            if (target === currentStep) button.setAttribute('aria-current', 'step'); else button.removeAttribute('aria-current');
            const number = button.querySelector('.progress-number');
            number.innerHTML = complete ? '<i class="mdi mdi-check" aria-hidden="true"></i>' : String(target).padStart(2, '0');
        });
        backButton.hidden = currentStep === 1;
        backLabel.textContent = currentStep === 4 ? 'Edit Information' : 'Back';
        continueButton.classList.toggle('is-confirm', currentStep === 4);
        continueLabel.textContent = currentStep === 1 ? 'Continue to Address' : currentStep === 2 ? 'Continue to Background' : currentStep === 3 ? 'Review & Continue' : 'Confirm & Register';
        continueIcon.className = currentStep === 4 ? 'mdi mdi-check-circle-outline' : 'mdi mdi-arrow-right';
        if (currentStep === 4) populateReview();
        if (focusHeading) {
            panels.find((panel) => Number(panel.dataset.stepPanel) === currentStep)?.querySelector('h2')?.focus({ preventScroll: true });
            window.scrollTo({ top: document.querySelector('.registration-progress').offsetTop - 90, behavior: 'smooth' });
        }
    }

    function validateStep(step) {
        const panel = panels.find((item) => Number(item.dataset.stepPanel) === step);
        if (!panel) return true;
        for (const field of panel.querySelectorAll('input, select, textarea')) {
            if (!field.checkValidity()) {
                if (currentStep !== step) showStep(step, true);
                field.reportValidity(); field.focus(); return false;
            }
        }
        return true;
    }

    function selectedText(name) {
        const field = form.elements[name];
        if (!field || !field.value) return 'Not provided';
        return field.options[field.selectedIndex]?.text.replace(/^●\s*/, '') || 'Not provided';
    }
    function fieldValue(name) { return form.elements[name]?.value.trim() || 'Not provided'; }
    function formatDate(value) {
        if (!value) return 'Not provided';
        const parts = value.split('-').map(Number);
        return new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(parts[0], parts[1] - 1, parts[2])));
    }
    function calculatedAge() {
        if (!birthdate.value) return null;
        const birthday = new Date(birthdate.value + 'T00:00:00');
        const today = new Date();
        let age = today.getFullYear() - birthday.getFullYear();
        if (today.getMonth() < birthday.getMonth() || (today.getMonth() === birthday.getMonth() && today.getDate() < birthday.getDate())) age -= 1;
        return age >= 0 ? age : null;
    }
    function syncAge() {
        const age = calculatedAge();
        ageOutput.textContent = age === null ? 'Choose a birthday' : age + (age === 1 ? ' year old' : ' years old');
    }
    function setReview(key, value) {
        const target = form.querySelector('[data-review="' + key + '"]');
        if (target) target.textContent = value || 'Not provided';
    }
    function populateReview() {
        const fullName = ['firstname', 'middlename', 'lastname', 'suffix'].map((name) => form.elements[name]?.value.trim()).filter(Boolean).join(' ');
        const age = calculatedAge();
        setReview('full_name', fullName); setReview('nickname', fieldValue('nickname')); setReview('gender', selectedText('gender'));
        setReview('birthday_age', formatDate(birthdate.value) + (age === null ? '' : ' · ' + age + ' years old')); setReview('contact_num', fieldValue('contact_num'));
        setReview('province', fieldValue('province')); setReview('municipality', selectedText('municipality_id')); setReview('barangay', selectedText('barangay_id')); setReview('residential_address', fieldValue('residential_address'));
        setReview('surrender_date', formatDate(form.elements.surrender_date.value)); setReview('batch', selectedText('batch_year')); setReview('batch_section', selectedText('batch_section')); setReview('status', selectedText('status')); setReview('surrender_reason', fieldValue('surrender_reason'));
    }

    async function saveDraft(autosave) {
        if (draftSaveRunning) { draftSavePending = true; return; }
        draftSaveRunning = true; draftDirty = false;
        if (!autosave) { saveButton.disabled = true; saveLabel.textContent = 'Saving…'; }
        draftStatus.className = 'draft-status'; draftStatus.textContent = autosave ? 'Saving securely…' : 'Saving encrypted draft…';
        const payload = new FormData(form); payload.set('autosave', autosave ? '1' : '0');
        try {
            const response = await fetch(form.dataset.draftUrl, { method: 'POST', body: payload, headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error();
            draftStatus.className = 'draft-status is-saved'; draftStatus.innerHTML = '<i class="mdi mdi-check-circle-outline" aria-hidden="true"></i> Draft saved just now';
        } catch (error) {
            draftDirty = true; draftStatus.className = 'draft-status is-error'; draftStatus.textContent = autosave ? 'Autosave paused. Use Save Draft to retry.' : 'Draft could not be saved. Check the entered values and retry.';
        } finally {
            draftSaveRunning = false; saveButton.disabled = false; saveLabel.textContent = 'Save Draft';
            if (draftSavePending) { draftSavePending = false; saveDraft(true); }
        }
    }

    async function loadBarangays() {
        const selected = barangay.dataset.selected || '';
        barangay.disabled = true; barangay.replaceChildren(new Option(municipality.value ? 'Loading barangays…' : 'Select municipality first', ''));
        if (!municipality.value) { barangay.disabled = false; return; }
        try {
            const url = new URL(municipality.dataset.barangaySource, window.location.origin); url.searchParams.set('municipality_id', municipality.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' }); if (!response.ok) throw new Error();
            const rows = await response.json(); barangay.replaceChildren(new Option('Select barangay', ''));
            rows.forEach((row) => barangay.add(new Option(row.name, row.id, false, String(row.id) === String(selected)))); barangay.dataset.selected = '';
        } catch (error) { barangay.replaceChildren(new Option('Barangays unavailable — retry municipality', '')); }
        finally { barangay.disabled = false; }
    }

    continueButton.addEventListener('click', () => {
        if (currentStep < 4) { if (validateStep(currentStep)) showStep(currentStep + 1, true); return; }
        for (let step = 1; step <= 3; step += 1) { if (!validateStep(step)) return; }
        confirmed = true; form.requestSubmit();
    });
    backButton.addEventListener('click', () => showStep(currentStep === 4 ? 3 : currentStep - 1, true));
    progressSteps.forEach((button) => button.addEventListener('click', () => { const target = Number(button.dataset.stepTarget); if (target <= highestVisitedStep) showStep(target, true); }));
    saveButton.addEventListener('click', () => saveDraft(false)); birthdate.addEventListener('change', syncAge);
    municipality.addEventListener('change', () => { barangay.dataset.selected = ''; loadBarangays(); });
    form.addEventListener('input', () => { draftDirty = true; window.clearTimeout(draftTimer); draftTimer = window.setTimeout(() => saveDraft(true), 1800); });
    form.addEventListener('change', () => { draftDirty = true; window.clearTimeout(draftTimer); draftTimer = window.setTimeout(() => saveDraft(true), 700); });
    form.addEventListener('submit', (event) => { if (!confirmed) { event.preventDefault(); continueButton.click(); return; } continueButton.disabled = true; continueLabel.textContent = 'Registering securely…'; continueIcon.className = 'mdi mdi-loading mdi-spin'; });
    window.addEventListener('pagehide', () => { if (!draftDirty || confirmed || !navigator.sendBeacon) return; const payload = new FormData(form); payload.set('autosave', '1'); navigator.sendBeacon(form.dataset.draftUrl, payload); });
    syncAge(); showStep(currentStep, false);
})();
</script>
@endpush
