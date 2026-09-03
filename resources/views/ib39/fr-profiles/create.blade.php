@extends('layouts.skydash-v')
@section('title', 'Record Surfaced FR')
@section('heading', 'Record Surfaced FR')

@push('styles')
<style>
    .surfaced-fr-container{max-width:1120px;margin:0 auto;padding-bottom:2rem}
    .module-nav-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem}
    .module-back-link{display:inline-flex;align-items:center;gap:.45rem;color:#4a5568;font-size:.84rem;font-weight:700;text-decoration:none!important;transition:color .15s ease}
    .module-back-link:hover{color:#401595}
    .module-back-link i{font-size:1.1rem}
    .module-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#718096;margin:0;padding:0;list-style:none}
    .module-breadcrumb li a{color:#718096;text-decoration:none;font-weight:600}
    .module-breadcrumb li a:hover{color:#401595}
    .module-breadcrumb li.active{color:#2d3748;font-weight:700}
    .module-breadcrumb .separator{color:#cbd5e0;font-size:.7rem}
    .surfaced-hero{display:flex;align-items:center;justify-content:space-between;background:linear-gradient(115deg,#152a4d 0%,#172f57 58%,#123c4d 100%);border-radius:15px;color:#fff;padding:1.35rem 1.6rem;margin-bottom:1.25rem;min-height:110px;box-shadow:0 8px 24px rgba(21,42,77,.12);position:relative;overflow:hidden}
    .hero-main{display:flex;align-items:center;gap:1.1rem;min-width:0;position:relative;z-index:1}
    .hero-icon-box{display:flex;align-items:center;justify-content:center;width:54px;height:54px;flex:0 0 54px;border-radius:13px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.12);color:#45e0ba;font-size:1.55rem}
    .hero-eyebrow{color:#ff9a62;font-size:.62rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
    .surfaced-hero h1{color:#fff;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.2rem;line-height:1.2}
    .surfaced-hero p{color:#bac7e5;font-size:.8rem;margin:0}
    .hero-ref-badge{display:flex;align-items:center;gap:.6rem;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:11px;padding:.6rem .9rem;color:#d9e2f1;font-size:.74rem;position:relative;z-index:1}
    .hero-ref-badge i{color:#45e0ba;font-size:1.2rem}
    .surfaced-card{background:#fff;border:1px solid #e4e4e7;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.03);overflow:hidden}
    .surfaced-card-body{padding:1.8rem}
    .privacy-notice{display:flex;align-items:flex-start;gap:.75rem;background:#f4effa;border-left:4px solid #401595;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.8rem;color:#43286b;font-size:.8rem;line-height:1.45}
    .privacy-notice i{color:#401595;font-size:1.1rem;margin-top:.1rem}
    .form-segment{margin-bottom:2rem}
    .form-segment:last-of-type{margin-bottom:1rem}
    .segment-header{display:flex;align-items:center;gap:.65rem;padding-bottom:.65rem;border-bottom:1px solid #edf1f6;margin-bottom:1.25rem}
    .segment-num{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;background:#f1ebfa;color:#401595;font-size:.72rem;font-weight:800;border-radius:7px}
    .segment-title{color:#1e293b;font-size:.95rem;font-weight:750;margin:0}
    .segment-desc{color:#8a97aa;font-size:.72rem;margin:0 0 0 auto}
    .form-group label,.field-label{color:#334155;font-size:.75rem;font-weight:700;margin-bottom:.35rem}
    .required-mark{color:#dc2626;font-weight:700}
    .form-control{border:1px solid #d8e0ea;border-radius:9px;font-size:.82rem;color:#1e293b;padding:.65rem .85rem;min-height:42px;transition:all .15s ease}
    .form-control:focus{border-color:#401595;box-shadow:0 0 0 3px rgba(64,21,149,.11)}
    .form-control[readonly]{background-color:#f8fafc;color:#64748b;border-color:#e2e8f0}
    .radio-pills{display:flex;gap:.75rem}
    .radio-pill-label{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;border:1px solid #d8e0ea;border-radius:9px;background:#fff;color:#475569;font-size:.8rem;font-weight:650;cursor:pointer;transition:all .15s ease}
    .radio-pill-label:has(input:checked){background:#f1ebfa;border-color:#401595;color:#401595;box-shadow:0 0 0 1px #401595}
    .radio-pill-label input{accent-color:#401595}
    .conditional-field[hidden]{display:none!important}
    .invalid-feedback{display:block;font-size:.74rem;font-weight:600;color:#dc2626}
    .character-hint{color:#8492a6;font-size:.68rem;margin-top:.25rem}
    .form-footer-actions{display:flex;align-items:center;justify-content:flex-end;gap:.75rem;padding-top:1.25rem;border-top:1px solid #edf1f6;margin-top:1rem}
    .btn-submit-custom{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;background:#401595;border:1px solid #401595;color:#fff;font-size:.82rem;font-weight:750;padding:.65rem 1.35rem;border-radius:9px;box-shadow:0 4px 12px rgba(64,21,149,.2);transition:all .15s ease}
    .btn-submit-custom:hover,.btn-submit-custom:focus{background:#280274;border-color:#280274;color:#fff;box-shadow:0 6px 16px rgba(40,2,116,.25)}
    .btn-cancel-custom{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;background:#f8fafc;border:1px solid #d8e0ea;color:#475569;font-size:.82rem;font-weight:650;padding:.65rem 1.2rem;border-radius:9px;text-decoration:none!important;transition:all .15s ease}
    .btn-cancel-custom:hover,.btn-cancel-custom:focus{background:#f1f5f9;color:#1e293b}
    @media(max-width:767px){.surfaced-hero{flex-direction:column;align-items:flex-start;gap:1rem;padding:1.2rem}.hero-ref-badge{width:100%}.surfaced-card-body{padding:1.2rem}.segment-header{flex-wrap:wrap}.segment-desc{margin:0;width:100%}.form-footer-actions{flex-direction:column-reverse}.btn-submit-custom,.btn-cancel-custom{width:100%}}
</style>
@endpush

@section('content')
<div class="surfaced-fr-container">
    {{-- Top Navigation & History --}}
    <div class="module-nav-top">
        <a href="{{ route('ib39.fr-profiles.index') }}" class="module-back-link">
            <i class="mdi mdi-arrow-left"></i>
            <span>Back to FR Profiles</span>
        </a>
        <ol class="module-breadcrumb" aria-label="Breadcrumb">
            <li><a href="{{ route('ib39.dashboard') }}">Dashboard</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li><a href="{{ route('ib39.fr-profiles.index') }}">FR Profiles</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li class="active" aria-current="page">Record Surfaced FR</li>
        </ol>
    </div>

    {{-- Modern Hero Banner --}}
    <header class="surfaced-hero">
        <div class="hero-main">
            <div class="hero-icon-box">
                <i class="mdi mdi-account-plus-outline" aria-hidden="true"></i>
            </div>
            <div>
                <div class="hero-eyebrow mb-1">39th Infantry Battalion · Reintegration</div>
                <h1>Record Surfaced Former Rebel</h1>
                <p>Register approved surfaced FR details for authorized 39th IB monitoring.</p>
            </div>
        </div>
        <div class="hero-ref-badge" aria-label="FR reference number">
            <i class="mdi mdi-shield-check" aria-hidden="true"></i>
            <div>
                <strong class="d-block text-white">FR Reference Number</strong>
                <span>Generated automatically after saving</span>
            </div>
        </div>
    </header>

    {{-- Main Form Card --}}
    <form method="POST" action="{{ route('ib39.fr-profiles.store') }}" class="surfaced-card" data-surfaced-fr-form novalidate>
        @csrf
        <div class="surfaced-card-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Review the highlighted fields.</strong> The surfaced FR record was not saved.
                </div>
            @endif

            <div class="privacy-notice">
                <i class="mdi mdi-lock-outline" aria-hidden="true"></i>
                <div>
                    <strong>Confidentiality Notice:</strong> Only the approved first and last name may be entered. Do not include a middle name, alias, nickname, identity-document number, or other identifying information.
                </div>
            </div>

            {{-- Segment 1: Former Rebel Name --}}
            <section class="form-segment" aria-labelledby="name-heading">
                <div class="segment-header">
                    <span class="segment-num">1</span>
                    <h2 id="name-heading" class="segment-title">Former Rebel Identity</h2>
                    <span class="segment-desc">Enter only the approved first and last name</span>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="first_name">First name <span class="required-mark">*</span></label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" maxlength="100" class="form-control @error('first_name') is-invalid @enderror" placeholder="Enter first name" autocomplete="off" required>
                        @error('first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="last_name">Last name <span class="required-mark">*</span></label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" maxlength="100" class="form-control @error('last_name') is-invalid @enderror" placeholder="Enter last name" autocomplete="off" required>
                        @error('last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            {{-- Segment 2: Classification --}}
            <section class="form-segment" aria-labelledby="classification-heading">
                <div class="segment-header">
                    <span class="segment-num">2</span>
                    <h2 id="classification-heading" class="segment-title">Classification</h2>
                    <span class="segment-desc">Select official FR category</span>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="category">FR category <span class="required-mark">*</span></label>
                        <select id="category" name="category" class="form-control @error('category') is-invalid @enderror" required data-category>
                            <option value="">Select a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->value }}</option>
                            @endforeach
                        </select>
                        @error('category')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 form-group conditional-field" data-other-category-field hidden>
                        <label for="other_category_specification">Other category specification <span class="required-mark">*</span></label>
                        <input id="other_category_specification" name="other_category_specification" value="{{ old('other_category_specification') }}" maxlength="255" class="form-control @error('other_category_specification') is-invalid @enderror" placeholder="Specify category" disabled>
                        @error('other_category_specification')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            {{-- Segment 3: Surfacing Location --}}
            <section class="form-segment" aria-labelledby="location-heading">
                <div class="segment-header">
                    <span class="segment-num">3</span>
                    <h2 id="location-heading" class="segment-title">Surfacing Location & Date</h2>
                    <span class="segment-desc">Record authorized municipal and barangay location</span>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="province">Province <span class="required-mark">*</span></label>
                        <input id="province" name="province" value="{{ old('province', $province) }}" class="form-control @error('province') is-invalid @enderror" readonly required>
                        @error('province')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="municipality_id">City or municipality <span class="required-mark">*</span></label>
                        <select id="municipality_id" name="municipality_id" class="form-control @error('municipality_id') is-invalid @enderror" required data-municipality>
                            <option value="">Select a city or municipality</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality->id }}" @selected((string) old('municipality_id') === (string) $municipality->id)>{{ $municipality->name }}</option>
                            @endforeach
                        </select>
                        @error('municipality_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="barangay_id">Barangay <span class="text-muted font-weight-normal">(optional)</span></label>
                        <select id="barangay_id" name="barangay_id" class="form-control @error('barangay_id') is-invalid @enderror" data-barangay>
                            <option value="">No barangay selected</option>
                            @foreach ($municipalities as $municipality)
                                @foreach ($municipality->barangays as $barangay)
                                    <option value="{{ $barangay->id }}" data-municipality-id="{{ $municipality->id }}" @selected((string) old('barangay_id') === (string) $barangay->id)>{{ $barangay->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('barangay_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-8 form-group">
                        <label for="specific_location">Specific location <span class="text-muted font-weight-normal">(optional)</span></label>
                        <input id="specific_location" name="specific_location" value="{{ old('specific_location') }}" maxlength="500" class="form-control @error('specific_location') is-invalid @enderror" placeholder="Purok, landmark, or specific site details" autocomplete="off">
                        @error('specific_location')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="surfaced_at">Date of surfacing <span class="required-mark">*</span></label>
                        <input id="surfaced_at" name="surfaced_at" type="date" value="{{ old('surfaced_at') }}" max="{{ today()->toDateString() }}" class="form-control @error('surfaced_at') is-invalid @enderror" required>
                        @error('surfaced_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            {{-- Segment 4: Initial Details & Firearms --}}
            <section class="form-segment" aria-labelledby="details-heading">
                <div class="segment-header">
                    <span class="segment-num">4</span>
                    <h2 id="details-heading" class="segment-title">Firearms & Remarks</h2>
                    <span class="segment-desc">Record firearms custody and operational notes</span>
                </div>
                <div class="form-group mb-3">
                    <label class="field-label d-block">Possessed firearms <span class="required-mark">*</span></label>
                    <div class="radio-pills">
                        <label class="radio-pill-label">
                            <input type="radio" name="possessed_firearms" value="1" @checked((string) old('possessed_firearms') === '1')>
                            <span>Yes, possessed firearms</span>
                        </label>
                        <label class="radio-pill-label">
                            <input type="radio" name="possessed_firearms" value="0" @checked((string) old('possessed_firearms') === '0')>
                            <span>No firearms</span>
                        </label>
                    </div>
                    @error('possessed_firearms')<span class="invalid-feedback mt-1">{{ $message }}</span>@enderror
                </div>
                <div class="form-group mb-2">
                    <label for="initial_remarks">Initial remarks <span class="text-muted font-weight-normal">(optional)</span></label>
                    <textarea id="initial_remarks" name="initial_remarks" rows="4" maxlength="5000" class="form-control @error('initial_remarks') is-invalid @enderror" placeholder="Enter initial operational remarks...">{{ old('initial_remarks') }}</textarea>
                    <div class="character-hint">Maximum 5,000 characters. Do not include additional personal identifying information.</div>
                    @error('initial_remarks')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </section>

            {{-- Footer Action Buttons --}}
            <div class="form-footer-actions">
                <a href="{{ route('ib39.fr-profiles.index') }}" class="btn-cancel-custom">Cancel</a>
                <button type="submit" class="btn-submit-custom" data-submit-button>
                    <i class="mdi mdi-content-save-outline"></i>
                    <span>Record Surfaced FR</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('[data-surfaced-fr-form]');
    if (!form) return;

    var category = form.querySelector('[data-category]');
    var otherCategoryField = form.querySelector('[data-other-category-field]');
    var otherCategoryInput = otherCategoryField.querySelector('input');
    var municipality = form.querySelector('[data-municipality]');
    var barangay = form.querySelector('[data-barangay]');

    function setField(field, control, visible, clear) {
        field.hidden = !visible;
        control.disabled = !visible;
        control.required = visible;
        if (!visible && clear) control.value = '';
    }

    function syncCategory(clear) {
        setField(otherCategoryField, otherCategoryInput, category.value === 'Other', clear);
    }

    function syncBarangays(clearInvalid) {
        var municipalityId = municipality.value;
        var selectedOption = barangay.options[barangay.selectedIndex];
        if (clearInvalid && selectedOption && selectedOption.value && selectedOption.dataset.municipalityId !== municipalityId) {
            barangay.value = '';
        }
        Array.prototype.forEach.call(barangay.options, function (option) {
            if (!option.value) {
                option.disabled = false;
                option.hidden = false;
                return;
            }
            var matches = municipalityId !== '' && option.dataset.municipalityId === municipalityId;
            option.disabled = !matches;
            option.hidden = !matches;
        });
        barangay.disabled = municipalityId === '';
    }

    category.addEventListener('change', function () { syncCategory(true); });
    municipality.addEventListener('change', function () { syncBarangays(true); });
    form.addEventListener('submit', function () {
        var button = form.querySelector('[data-submit-button]');
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i><span>Saving...</span>';
    });

    syncCategory(false);
    syncBarangays(false);
}());
</script>
@endpush

