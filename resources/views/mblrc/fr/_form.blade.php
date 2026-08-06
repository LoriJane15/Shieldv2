@php
    $fr = $fr ?? null;
    $val = fn ($field, $default = '') => old($field, $fr->$field ?? $default);
    $selBarangays = $barangays ?? collect();
@endphp

@once
@push('styles')
<style>
    .fr-form-card{border:1px solid #e7ecf3;border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045)}.fr-form-card .card-body{padding:1.5rem}.form-section{border-bottom:1px solid #edf1f6;margin-bottom:1.4rem;padding-bottom:1.4rem}.form-section:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0}.form-section-title{align-items:center;color:#172b4d!important;display:flex;font-size:.9rem!important;font-weight:700!important;letter-spacing:.03em;margin-bottom:1.2rem!important;text-transform:uppercase}.form-section-title i{align-items:center;background:#eaf1ff;border-radius:9px;color:#2f6fed;display:flex;font-size:1rem;height:34px;justify-content:center;margin-right:.7rem;width:34px}.fr-form-card small.text-muted{color:#64748b!important;font-size:.68rem;font-weight:700;letter-spacing:.03em}.fr-form-card .form-control,.fr-form-card .form-select{border:1px solid #dfe5ee;border-radius:8px;min-height:42px}.fr-form-card .form-control:focus,.fr-form-card .form-select:focus{border-color:#80a6ee;box-shadow:0 0 0 3px rgba(47,111,237,.1)}.fr-form-card textarea.form-control{min-height:92px}.fr-form-card .text-danger.small{font-size:.67rem}
    @media(max-width:767px){.fr-form-card .card-body{padding:1.1rem}.form-section{margin-bottom:1.15rem;padding-bottom:1.15rem}}
</style>
@endpush
@endonce

<div class="card fr-form-card">
    <div class="card-body">
        {{-- PROFILE INFORMATION --}}
        <div class="form-section">
            <h4 class="form-section-title"><i class="mdi mdi-account-outline"></i>Profile Information</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">FIRST NAME <span class="text-danger">*</span></small>
                    <input type="text" name="firstname" value="{{ $val('firstname') }}" placeholder="Enter First Name" required class="form-control">
                    @error('firstname') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">MIDDLE NAME</small>
                    <input type="text" name="middlename" value="{{ $val('middlename') }}" placeholder="Enter Middle Name" class="form-control">
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">LAST NAME <span class="text-danger">*</span></small>
                    <input type="text" name="lastname" value="{{ $val('lastname') }}" placeholder="Enter Last Name" required class="form-control">
                    @error('lastname') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">ALIAS/NICKNAME</small>
                    <input type="text" name="nickname" value="{{ $val('nickname') }}" placeholder="Enter Alias" class="form-control">
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">SUFFIX</small>
                    <select name="suffix" class="form-select">
                        <option value="">Select Suffix</option>
                        @foreach (['Jr.', 'Sr.', 'II', 'III'] as $s)
                            <option value="{{ $s }}" @selected($val('suffix') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">GENDER</small>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        @foreach (['Male', 'Female'] as $g)
                            <option value="{{ $g }}" @selected($val('gender') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">AGE</small>
                    <input type="number" name="age" min="0" max="120" value="{{ $val('age') }}" placeholder="Enter Age" class="form-control">
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">CIVIL STATUS</small>
                    <select name="civil_status" class="form-select">
                        <option value="">Select Civil Status</option>
                        @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $c)
                            <option value="{{ $c }}" @selected($val('civil_status') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">BIRTHDAY</small>
                    <input type="date" name="birthdate" value="{{ $val('birthdate') ? \Illuminate\Support\Carbon::parse($val('birthdate'))->toDateString() : '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">CONTACT NUMBER</small>
                    <input type="text" name="contact_num" value="{{ $val('contact_num') }}" placeholder="09XXXXXXXXX" class="form-control">
                </div>
            </div>
        </div>

        {{-- ADDRESS INFORMATION --}}
        <div class="form-section">
            <h4 class="form-section-title"><i class="mdi mdi-map-marker-outline"></i>Address Information</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">PROVINCE</small>
                    <input type="text" name="province" value="{{ $val('province', 'Davao del Sur') }}" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">MUNICIPALITY/CITY <span class="text-danger">*</span></small>
                    <select name="municipality_id" required class="form-select"
                            data-barangay-source="{{ route('mblrc.barangays') }}" data-barangay-target="#barangaySelect">
                        <option value="">Select Municipal</option>
                        @foreach ($municipalities as $m)
                            <option value="{{ $m->id }}" @selected((int) $val('municipality_id') === $m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                    @error('municipality_id') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">BARANGAY <span class="text-danger">*</span></small>
                    <select name="barangay_id" id="barangaySelect" required class="form-select" data-selected="{{ $val('barangay_id') }}">
                        <option value="">Select Barangay</option>
                        @foreach ($selBarangays as $b)
                            <option value="{{ $b->id }}" @selected((int) $val('barangay_id') === $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('barangay_id') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">ZIP CODE</small>
                    <select name="zipcode" class="form-select">
                        <option value="">Enter Zipcode</option>
                        @foreach (range(8001, 8010) as $z)
                            <option value="{{ $z }}" @selected((string) $val('zipcode') === (string) $z)>{{ $z }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <small class="text-muted d-block mb-2">RESIDENTIAL ADDRESS</small>
                    <input type="text" name="residential_address" value="{{ $val('residential_address') }}" placeholder="Enter Current Address" class="form-control">
                </div>
            </div>
        </div>

        {{-- BACKGROUND INFORMATION --}}
        <div class="form-section">
            <h4 class="form-section-title"><i class="mdi mdi-clipboard-text-outline"></i>Background Information</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">DATE OF SURRENDER</small>
                    <input type="date" name="surrender_date" value="{{ $val('surrender_date') ? \Illuminate\Support\Carbon::parse($val('surrender_date'))->toDateString() : '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">BATCH YEAR</small>
                    <select name="batch_year" class="form-select">
                        <option value="">Enter Batch Year</option>
                        @foreach (['2025', '2024', '2023', '2022'] as $y)
                            <option value="{{ $y }}" @selected((string) $val('batch_year') === $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">BATCH SECTION</small>
                    <select name="batch_section" class="form-select">
                        <option value="">Select Section</option>
                        @foreach (['1', '2'] as $b)
                            <option value="{{ $b }}" @selected((string) $val('batch_section') === $b)>Section {{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                @isset($statuses)
                    <div class="col-md-4">
                        <small class="text-muted d-block mb-2">STATUS</small>
                        <select name="status" class="form-select">
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" @selected($val('status', 'Active') === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-12">
                    <small class="text-muted d-block mb-2">REASON OF SURRENDER</small>
                    <textarea name="surrender_reason" rows="3" class="form-control" placeholder="Enter reason of surrender">{{ $val('surrender_reason') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
