@extends('layouts.skydash-v')
@section('title', 'Record Surfaced FR')
@section('heading', 'Record Surfaced FR')

@push('styles')
<style>
    .surfaced-fr-page{--navy:#172b4d;--border:#e2e8f0}.surfaced-fr-header{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;padding:1.5rem}.surfaced-fr-header h2{color:#fff;font-size:1.55rem;font-weight:700}.surfaced-fr-header p{font-size:.82rem;opacity:.86}.reference-note{align-items:center;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);border-radius:10px;display:flex;font-size:.75rem;gap:.55rem;padding:.7rem .85rem}.surfaced-form-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.05)}.surfaced-form-card .card-body{padding:1.5rem}.form-section{border-bottom:1px solid #edf1f6;margin-bottom:1.4rem;padding-bottom:.6rem}.form-section h3{color:var(--navy);font-size:1rem;font-weight:700;margin:0}.form-section p{color:#8492a6;font-size:.72rem;margin:.2rem 0 0}.form-group label,.field-label{color:#42526b;font-size:.72rem;font-weight:700}.required-mark{color:#c53030}.form-control{border-color:#dbe3ee;border-radius:8px}.form-control:focus{border-color:#80a6ee;box-shadow:0 0 0 3px rgba(47,111,237,.1)}.choice-row{display:flex;flex-wrap:wrap;gap:1rem;padding-top:.35rem}.choice-option{align-items:center;color:#52616f;display:inline-flex;font-size:.8rem;gap:.35rem}.conditional-field[hidden]{display:none!important}.privacy-note{background:#f7f9fc;border-left:4px solid #2f6fed;border-radius:8px;color:#64748b;font-size:.73rem;padding:.75rem .9rem}.form-actions{align-items:center;display:flex;gap:.65rem;justify-content:flex-end}.invalid-feedback{display:block}.character-hint{color:#8492a6;font-size:.65rem;margin-top:.25rem}@media(max-width:767px){.surfaced-fr-header,.surfaced-form-card .card-body{padding:1.1rem}.form-actions{align-items:stretch;flex-direction:column-reverse}.form-actions .btn{width:100%}}
</style>
@endpush

@section('content')
<div class="surfaced-fr-page">
    <header class="surfaced-fr-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="mb-1">Record Surfaced Former Rebel</h2>
                <p class="mb-0">Record approved surfaced FR information for authorized 39th Infantry Battalion monitoring.</p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="reference-note" aria-label="FR reference number">
                    <i class="fa fa-shield" aria-hidden="true"></i>
                    <span><strong class="d-block">FR reference number</strong>Generated automatically after saving</span>
                </div>
            </div>
        </div>
    </header>

    <form method="POST" action="{{ route('ib39.fr-profiles.store') }}" class="card surfaced-form-card" data-surfaced-fr-form novalidate>
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Review the highlighted fields.</strong> The surfaced FR record was not saved.
                </div>
            @endif

            <div class="privacy-note mb-4">
                <i class="fa fa-lock mr-1" aria-hidden="true"></i>
                Only the approved first and last name may be entered. Do not include a middle name, alias, nickname, identity-document number, or other identifying information.
            </div>

            <section aria-labelledby="name-heading">
                <div class="form-section"><h3 id="name-heading">Former rebel name</h3><p>Enter only the approved first and last name.</p></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="first_name">First name <span class="required-mark">*</span></label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" maxlength="100" class="form-control @error('first_name') is-invalid @enderror" autocomplete="off" required>
                        @error('first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="last_name">Last name <span class="required-mark">*</span></label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" maxlength="100" class="form-control @error('last_name') is-invalid @enderror" autocomplete="off" required>
                        @error('last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section aria-labelledby="classification-heading">
                <div class="form-section"><h3 id="classification-heading">Classification</h3><p>Record the surfaced FR category.</p></div>
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
                        <input id="other_category_specification" name="other_category_specification" value="{{ old('other_category_specification') }}" maxlength="255" class="form-control @error('other_category_specification') is-invalid @enderror" disabled>
                        @error('other_category_specification')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section aria-labelledby="location-heading">
                <div class="form-section"><h3 id="location-heading">Surfacing location</h3><p>Select the authorized location and record optional site details.</p></div>
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
                        <label for="barangay_id">Barangay <span class="text-muted">(optional)</span></label>
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
                        <label for="specific_location">Specific location <span class="text-muted">(optional)</span></label>
                        <input id="specific_location" name="specific_location" value="{{ old('specific_location') }}" maxlength="500" class="form-control @error('specific_location') is-invalid @enderror" autocomplete="off">
                        @error('specific_location')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="surfaced_at">Date of surfacing <span class="required-mark">*</span></label>
                        <input id="surfaced_at" name="surfaced_at" type="date" value="{{ old('surfaced_at') }}" max="{{ today()->toDateString() }}" class="form-control @error('surfaced_at') is-invalid @enderror" required>
                        @error('surfaced_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section aria-labelledby="details-heading">
                <div class="form-section"><h3 id="details-heading">Initial details</h3><p>Record the firearms indicator and any limited operational remarks.</p></div>
                <fieldset class="form-group">
                    <legend class="field-label mb-1">Possessed firearms <span class="required-mark">*</span></legend>
                    <div class="choice-row">
                        <label class="choice-option"><input type="radio" name="possessed_firearms" value="1" @checked((string) old('possessed_firearms') === '1')> Yes</label>
                        <label class="choice-option"><input type="radio" name="possessed_firearms" value="0" @checked((string) old('possessed_firearms') === '0')> No</label>
                    </div>
                    @error('possessed_firearms')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </fieldset>
                <div class="form-group mb-4">
                    <label for="initial_remarks">Initial remarks <span class="text-muted">(optional)</span></label>
                    <textarea id="initial_remarks" name="initial_remarks" rows="4" maxlength="5000" class="form-control @error('initial_remarks') is-invalid @enderror">{{ old('initial_remarks') }}</textarea>
                    <div class="character-hint">Maximum 5,000 characters. Do not include additional identity information.</div>
                    @error('initial_remarks')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </section>

            <div class="form-actions">
                <a href="{{ route('ib39.dashboard') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary" data-submit-button><i class="fa fa-save mr-1" aria-hidden="true"></i>Record Surfaced FR</button>
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
        button.innerHTML = '<i class="fa fa-spinner fa-spin mr-1" aria-hidden="true"></i>Saving';
    });

    syncCategory(false);
    syncBarangays(false);
}());
</script>
@endpush
