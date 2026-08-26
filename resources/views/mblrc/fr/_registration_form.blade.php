@php
    $draftPayload = $draft?->payload ?? [];
    $val = fn (string $field, mixed $default = '') => old($field, $draftPayload[$field] ?? $default);
    $selectedBarangays = $barangays ?? collect();
@endphp

<section class="registration-section is-active" data-step-panel="1" aria-labelledby="personal-section-title">
    <header class="registration-section-header">
        <span class="registration-section-number">01</span>
        <div><span class="registration-section-kicker">Personal Information</span><h2 id="personal-section-title" tabindex="-1">Basic identity and contact information</h2></div>
    </header>

    <div class="registration-field-group">
        <h3>Basic Identity</h3>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="firstname">First Name <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                <input id="firstname" type="text" name="firstname" value="{{ $val('firstname') }}" placeholder="Enter first name" maxlength="255" autocomplete="given-name" required @error('firstname') aria-invalid="true" aria-describedby="firstname-error" @enderror>
                @error('firstname')<p class="registration-error" id="firstname-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="middlename">Middle Name</label>
                <input id="middlename" type="text" name="middlename" value="{{ $val('middlename') }}" placeholder="Enter middle name" maxlength="255" autocomplete="additional-name">
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="lastname">Last Name <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                <input id="lastname" type="text" name="lastname" value="{{ $val('lastname') }}" placeholder="Enter last name" maxlength="255" autocomplete="family-name" required @error('lastname') aria-invalid="true" aria-describedby="lastname-error" @enderror>
                @error('lastname')<p class="registration-error" id="lastname-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="nickname">Alias/Nickname</label>
                <input id="nickname" type="text" name="nickname" value="{{ $val('nickname') }}" placeholder="Enter known alias" maxlength="255">
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="suffix">Suffix</label>
                <div class="select-control"><select id="suffix" name="suffix"><option value="">Select suffix</option>@foreach (['Jr.', 'Sr.', 'II', 'III'] as $suffix)<option value="{{ $suffix }}" @selected($val('suffix') === $suffix)>{{ $suffix }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="gender">Gender</label>
                <div class="select-control"><select id="gender" name="gender"><option value="">Select gender</option>@foreach (['Male', 'Female'] as $gender)<option value="{{ $gender }}" @selected($val('gender') === $gender)>{{ $gender }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            </div>
        </div>
    </div>

    <div class="registration-field-group">
        <h3>Personal Details</h3>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 registration-field">
                <label for="birthdate">Birthday <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                <div class="date-control"><i class="mdi mdi-calendar-blank-outline" aria-hidden="true"></i><input id="birthdate" type="date" name="birthdate" value="{{ $val('birthdate') ? \Illuminate\Support\Carbon::parse($val('birthdate'))->toDateString() : '' }}" max="{{ now(config('app.display_timezone'))->toDateString() }}" autocomplete="bday" required @error('birthdate') aria-invalid="true" aria-describedby="birthdate-error" @enderror></div>
                @error('birthdate')<p class="registration-error" id="birthdate-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
            </div>
            <div class="col-lg-3 col-md-6 registration-field">
                <label for="calculated-age">Age <span class="field-annotation">Automatically calculated</span></label>
                <output id="calculated-age" class="readonly-output" data-calculated-age aria-live="polite">Choose a birthday</output>
            </div>
            <div class="col-lg-3 col-md-6 registration-field">
                <label for="civil_status">Civil Status</label>
                <div class="select-control"><select id="civil_status" name="civil_status"><option value="">Select civil status</option>@foreach (['Single', 'Married', 'Widowed', 'Separated'] as $civilStatus)<option value="{{ $civilStatus }}" @selected($val('civil_status') === $civilStatus)>{{ $civilStatus }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            </div>
            <div class="col-lg-3 col-md-6 registration-field">
                <label for="contact_num">Contact Number <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                <input id="contact_num" type="tel" name="contact_num" value="{{ $val('contact_num') }}" placeholder="09XXXXXXXXX" maxlength="15" inputmode="tel" autocomplete="tel" required @error('contact_num') aria-invalid="true" aria-describedby="contact-error" @enderror>
                <small class="field-instruction">Digits only; country code is accepted.</small>
                @error('contact_num')<p class="registration-error" id="contact-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</section>

<section class="registration-section" data-step-panel="2" aria-labelledby="address-section-title" hidden>
    <header class="registration-section-header">
        <span class="registration-section-number">02</span>
        <div><span class="registration-section-kicker">Address Information</span><h2 id="address-section-title" tabindex="-1">Current residential and geographic information</h2></div>
    </header>
    <div class="geographic-flow" aria-label="Geographic selection order"><span><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i>Province</span><i class="mdi mdi-chevron-right" aria-hidden="true"></i><span>Municipality/City</span><i class="mdi mdi-chevron-right" aria-hidden="true"></i><span>Barangay</span></div>
    <div class="row g-4">
        <div class="col-lg-4 col-md-6 registration-field">
            <label for="province">Province</label>
            <div class="readonly-control"><i class="mdi mdi-lock-outline" aria-hidden="true"></i><input id="province" type="text" name="province" value="{{ $val('province', 'Davao del Sur') }}" readonly aria-readonly="true"></div>
            <small class="field-instruction">Preselected for this registry workspace.</small>
        </div>
        <div class="col-lg-4 col-md-6 registration-field">
            <label for="municipality_id">Municipality/City <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
            <div class="select-control"><select id="municipality_id" name="municipality_id" required data-barangay-source="{{ route('mblrc.barangays') }}" data-barangay-target="#barangay_id" @error('municipality_id') aria-invalid="true" aria-describedby="municipality-error" @enderror><option value="">Select municipality or city</option>@foreach ($municipalities as $municipality)<option value="{{ $municipality->id }}" @selected((int) $val('municipality_id') === $municipality->id)>{{ $municipality->name }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            @error('municipality_id')<p class="registration-error" id="municipality-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
        </div>
        <div class="col-lg-4 col-md-6 registration-field">
            <label for="barangay_id">Barangay <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
            <div class="select-control"><select id="barangay_id" name="barangay_id" required data-selected="{{ $val('barangay_id') }}" @error('barangay_id') aria-invalid="true" aria-describedby="barangay-error" @enderror><option value="">Select barangay</option>@foreach ($selectedBarangays as $barangay)<option value="{{ $barangay->id }}" @selected((int) $val('barangay_id') === $barangay->id)>{{ $barangay->name }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            @error('barangay_id')<p class="registration-error" id="barangay-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
        </div>
        <div class="col-lg-4 col-md-6 registration-field">
            <label for="zipcode">ZIP Code</label>
            <input id="zipcode" type="text" name="zipcode" value="{{ $val('zipcode') }}" placeholder="Enter ZIP code" maxlength="10" inputmode="numeric">
        </div>
        <div class="col-lg-8 col-md-12 registration-field">
            <label for="residential_address">Residential Address <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
            <input id="residential_address" type="text" name="residential_address" value="{{ $val('residential_address') }}" placeholder="House number, street, sitio, or purok" maxlength="255" autocomplete="street-address" required @error('residential_address') aria-invalid="true" aria-describedby="address-error" @enderror>
            <small class="field-instruction">Enter the beneficiary’s current residential location.</small>
            @error('residential_address')<p class="registration-error" id="address-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
        </div>
    </div>
</section>

<section class="registration-section" data-step-panel="3" aria-labelledby="background-section-title" hidden>
    <header class="registration-section-header">
        <span class="registration-section-number">03</span>
        <div><span class="registration-section-kicker">Background Information</span><h2 id="background-section-title" tabindex="-1">Surrender and reintegration background</h2></div>
    </header>
    <div class="registration-field-group">
        <h3>Surrender Information</h3>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="surrender_date">Date of Surrender <span class="required-mark" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                <div class="date-control"><i class="mdi mdi-calendar-check-outline" aria-hidden="true"></i><input id="surrender_date" type="date" name="surrender_date" value="{{ $val('surrender_date') ? \Illuminate\Support\Carbon::parse($val('surrender_date'))->toDateString() : '' }}" max="{{ now(config('app.display_timezone'))->toDateString() }}" required @error('surrender_date') aria-invalid="true" aria-describedby="surrender-date-error" @enderror></div>
                @error('surrender_date')<p class="registration-error" id="surrender-date-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="batch_year">Batch Year</label>
                <div class="select-control"><select id="batch_year" name="batch_year"><option value="">Select batch year</option>@foreach (range((int) now()->format('Y'), 1980) as $year)<option value="{{ $year }}" @selected((string) $val('batch_year') === (string) $year)>{{ $year }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            </div>
            <div class="col-lg-4 col-md-6 registration-field">
                <label for="batch_section">Batch Section <span class="field-help"><button type="button" aria-label="What is Batch Section?" aria-describedby="batch-section-help"><i class="mdi mdi-information-outline" aria-hidden="true"></i></button><span id="batch-section-help" role="tooltip">Select the official processing section recorded for this batch.</span></span></label>
                <div class="select-control"><select id="batch_section" name="batch_section"><option value="">Select section</option>@foreach (['1', '2'] as $section)<option value="{{ $section }}" @selected((string) $val('batch_section') === $section)>Section {{ $section }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div>
            </div>
            <div class="col-12 registration-field">
                <label for="surrender_reason">Reason for Surrender</label>
                <textarea id="surrender_reason" name="surrender_reason" rows="5" maxlength="5000" placeholder="Enter the recorded reason for surrender and any relevant context">{{ $val('surrender_reason') }}</textarea>
                <small class="field-instruction">Use concise, factual wording from the authorized source record.</small>
                @error('surrender_reason')<p class="registration-error" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
    <div class="registration-field-group">
        <h3>Current Record Status</h3>
        <div class="row g-4"><div class="col-lg-4 col-md-6 registration-field"><label for="status">Status</label><div class="select-control status-control"><select id="status" name="status">@foreach ($statuses as $status)<option value="{{ $status }}" @selected($val('status', 'Active') === $status)>● {{ $status }}</option>@endforeach</select><i class="mdi mdi-chevron-down" aria-hidden="true"></i></div><small class="field-instruction">Choose the beneficiary’s current verified registry status.</small></div></div>
    </div>
</section>

<section class="registration-section registration-review" data-step-panel="4" aria-labelledby="review-section-title" hidden>
    <header class="registration-section-header">
        <span class="registration-section-number"><i class="mdi mdi-check" aria-hidden="true"></i></span>
        <div><span class="registration-section-kicker">Review Registration</span><h2 id="review-section-title" tabindex="-1">Verify information before creating the official profile</h2></div>
    </header>
    <div class="review-notice"><i class="mdi mdi-shield-check-outline" aria-hidden="true"></i><span>Confirm that the information below matches the authorized source records. No official profile has been created yet.</span></div>
    <div class="review-groups">
        <section class="review-group"><header><i class="mdi mdi-account-outline" aria-hidden="true"></i><h3>Personal Information</h3></header><dl><div><dt>Full Name</dt><dd data-review="full_name">—</dd></div><div><dt>Alias</dt><dd data-review="nickname">—</dd></div><div><dt>Gender</dt><dd data-review="gender">—</dd></div><div><dt>Birthday / Age</dt><dd data-review="birthday_age">—</dd></div><div><dt>Contact Number</dt><dd data-review="contact_num">—</dd></div></dl></section>
        <section class="review-group"><header><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i><h3>Address Information</h3></header><dl><div><dt>Province</dt><dd data-review="province">—</dd></div><div><dt>Municipality/City</dt><dd data-review="municipality">—</dd></div><div><dt>Barangay</dt><dd data-review="barangay">—</dd></div><div class="review-wide"><dt>Residential Address</dt><dd data-review="residential_address">—</dd></div></dl></section>
        <section class="review-group"><header><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i><h3>Background Information</h3></header><dl><div><dt>Date of Surrender</dt><dd data-review="surrender_date">—</dd></div><div><dt>Batch</dt><dd data-review="batch">—</dd></div><div><dt>Section</dt><dd data-review="batch_section">—</dd></div><div><dt>Status</dt><dd data-review="status">—</dd></div><div class="review-wide"><dt>Reason for Surrender</dt><dd data-review="surrender_reason">—</dd></div></dl></section>
    </div>
</section>
