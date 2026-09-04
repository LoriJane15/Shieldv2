@extends('layouts.skydash-v')
@section('title', $fr->full_name)
@section('heading', 'Former Rebel Profile')

@php
    $ps = $fr->programStatus;
    $softStatus = match ($fr->status) {
        'Active', 'Reintegrated', 'Completed' => 'badge-soft-success',
        'On hold', 'Under Review', 'Pending', 'Suspended' => 'badge-soft-warning',
        'Inactive', 'Disengaged', 'Deceased' => 'badge-soft-danger',
        default => 'badge-soft-info',
    };
    $fullName = trim($fr->firstname.' '.$fr->lastname.' '.$fr->suffix);
    $batch = 'Batch '.($fr->batch_section ? $fr->batch_section.' - ' : '').($fr->batch_year ?: '—');
    // [label, mdi icon, value, col class]
    $profileFields = [
        ['NAME', 'mdi-account-card-details', $fullName, 'col-md-8'],
        ['ALIAS', 'mdi-tag', $fr->nickname ?: '—', 'col-md-4'],
        ['GENDER', 'mdi-gender-male-female', $fr->gender ?: '—', 'col-md-4'],
        ['CIVIL STATUS', 'mdi-heart', $fr->civil_status ?: '—', 'col-md-4'],
        ['BIRTHDAY', 'mdi-calendar', $fr->birthdate?->format('F d, Y') ?: '—', 'col-md-4'],
        ['CONTACT NUMBER', 'mdi-phone', $fr->contact_num ?: 'No contact number available', 'col-md-8'],
        ['AGE', 'mdi-cake-variant', $fr->age ? $fr->age.' yrs old' : '—', 'col-md-4'],
        ['MUNICIPALITY', 'mdi-city', $fr->municipality?->name ?: '—', 'col-md-4'],
        ['BARANGAY', 'mdi-map-marker', $fr->barangay?->name ?: '—', 'col-md-4'],
        ['ZIP CODE', 'mdi-mailbox', $fr->zipcode ?: '—', 'col-md-4'],
        ['RESIDENTIAL ADDRESS', 'mdi-home', trim(($fr->residential_address ? $fr->residential_address.', ' : '').($fr->barangay?->name ? $fr->barangay->name.', ' : '').($fr->municipality?->name ?? '')), 'col-12'],
    ];
    $bgFields = [
        ['Date of Surrender', 'mdi-calendar', $fr->surrender_date?->format('F d, Y') ?: '—', 'col-md-8', 'accent'],
        ['Batch Year', 'mdi-account-group', $batch, 'col-md-4', 'accent'],
        ['Reason of Surrender', 'mdi-file-document', $fr->surrender_reason ?: '—', 'col-12', 'accent'],
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .fr-banner { height: 200px; background-image: url('{{ asset('assets/images/8.jpg') }}'); background-size: cover; background-position: center; }
    .fr-avatar { width: 120px; height: 120px; object-fit: cover; }
    .info-card { background: #fff; border: 1px solid rgba(0,0,0,.05); border-radius: 10px; padding: 16px 20px; height: 100%; transition: box-shadow .2s; }
    .info-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    .info-card small { color: #6c757d; font-size: .75rem; letter-spacing: .5px; }
    .icon-container { width: 40px; height: 40px; min-width: 40px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: #63479B; }
    .icon-container.accent { background: rgba(245,166,35,.1); color: #F5A623; }
    .icon-container.green { background: rgba(0,179,0,.1); color: #00b300; }
    .form-control-static { font-weight: 500; color: #2d2d2d; padding: .375rem 0; margin-bottom: 0; font-size: 1rem; line-height: 1.5; }
    .info-item { margin-top: 6px; }
    .badge-soft-danger { color: #dc3545; background-color: rgba(220,53,69,.1); }
    .badge-soft-success { color: #28a745; background-color: rgba(40,167,69,.1); }
    .badge-soft-info { color: #17a2b8; background-color: rgba(23,162,184,.1); }
    .badge-soft-warning { color: #d39e00; background-color: rgba(255,193,7,.15); }
    .nav-tabs-line { border-bottom: 2px solid #e9ecef; }
    .nav-tabs-line .nav-link { border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: 1rem 1.5rem; font-weight: 500; color: #6c757d; }
    .nav-tabs-line .nav-link.active { color: #007bff; border-bottom-color: #007bff; background: transparent; }
    .section-head h3 { font-size: 1.25rem; }
    #frLocationMap { height: 400px; width: 100%; border-radius: 6px; }
    #frProfile .me-1 { margin-right: .25rem !important; }
    #frProfile .me-2 { margin-right: .5rem !important; }
    #frProfile .me-3 { margin-right: 1rem !important; }
    #frProfile .gap-2 { gap: .5rem; }
    #frProfile .g-2 { margin-left: -.25rem; margin-right: -.25rem; row-gap: .5rem; }
    #frProfile .g-2 > [class*="col-"] { padding-left: .25rem; padding-right: .25rem; }
    #frProfile .g-3 { margin-left: -.5rem; margin-right: -.5rem; row-gap: 1rem; }
    #frProfile .g-3 > [class*="col-"] { padding-left: .5rem; padding-right: .5rem; }
    #frProfile .section-head > .d-flex > i,
    #frProfile .section-head > i { flex: 0 0 auto; margin-right: .65rem !important; }
    body.program-status-modal-open,
    body.program-status-modal-open .content-wrapper,
    body.program-status-modal-open .sidebar .nav { overflow: hidden !important; }
    .program-status-modal { overflow: hidden !important; }
    .program-status-modal .modal-dialog.modal-dialog-centered { width: min(100%, 532px); min-height: 100%; margin: 0 auto; padding: 1rem; }
    .program-status-modal .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 70px rgba(29, 0, 62, .24); }
    .program-status-modal .modal-body { padding: 2rem; text-align: center; }
    .program-status-modal-icon { width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(61, 0, 117, .1); color: #3D0075; font-size: 2rem; }
    .program-status-modal-icon.success { background: rgba(25, 135, 84, .12); color: #198754; }
    .program-status-modal-title { margin-bottom: .5rem; color: #2d1746; font-size: 1.35rem; font-weight: 700; }
    .program-status-modal-text { margin-bottom: 1.25rem; color: #6c6872; line-height: 1.6; }
    .program-status-summary { margin: 0; padding: 1rem; border: 1px solid #ece7f2; border-radius: 10px; background: #faf8fc; text-align: left; }
    .program-status-summary > div { display: flex; justify-content: space-between; gap: 1rem; }
    .program-status-summary > div + div { margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #ece7f2; }
    .program-status-summary dt { color: #77717d; font-size: .8rem; font-weight: 500; }
    .program-status-summary dd { margin: 0; color: #2d1746; font-weight: 700; text-align: right; }
    .program-status-modal .modal-footer { justify-content: center; gap: .5rem; border-top: 0; padding: 0 2rem 2rem; }
    .program-status-modal .modal-footer .btn { min-width: 120px; border-radius: 8px; font-weight: 600; }
    .program-status-confirm-button { border-color: #3D0075; background: #3D0075; color: #fff; }
    .program-status-confirm-button:hover,
    .program-status-confirm-button:focus { border-color: #2d0057; background: #2d0057; color: #fff; }
</style>
@endpush

@section('content')
<div id="frProfile"
     data-fr-id="{{ $fr->id }}"
     data-program-status="{{ route('mblrc.fr.program-status.update', $fr) }}"
     data-location-save="{{ route('mblrc.fr.location.save', $fr) }}"
     data-location-history="{{ route('mblrc.fr.location.history', $fr) }}"
     data-skills-store="{{ route('mblrc.fr.skills.store', $fr) }}"
     data-skills-suggest="{{ route('mblrc.fr.skills.suggestions') }}"
     data-assistance-store="{{ route('mblrc.fr.assistance.store', $fr) }}"
     data-education-store="{{ route('mblrc.fr.education.update', $fr) }}"
     data-lat="{{ $fr->latitude }}" data-lng="{{ $fr->longitude }}">

    {{-- Profile Header Card --}}
    <div class="card mb-4" style="border-radius:.75rem;overflow:hidden;">
        <div class="position-relative">
            <div class="fr-banner"></div>
            <div class="position-absolute" style="bottom:-50px;left:50px;">
                <img src="{{ asset('assets/img/fr-profile.jpg') }}" class="fr-avatar rounded-circle border border-4 border-white" alt="profile">
            </div>
        </div>
        <div class="card-body pt-5 pb-3" style="padding-left:50px;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div style="margin-top:20px;">
                    <h3 class="font-weight-bold mb-3">{{ $fullName }} <small class="text-muted">· {{ $fr->classified_id }}</small></h3>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge badge-soft-danger px-3 py-2"><i class="mdi mdi-shield-account me-1"></i> Former Rebel</span>
                        <span class="badge {{ $softStatus }} px-3 py-2"><i class="mdi mdi-check-circle me-1"></i> {{ $fr->status }}</span>
                        @if ($fr->surrender_date)
                            <span class="badge badge-soft-info px-3 py-2"><i class="mdi mdi-calendar me-1"></i> Surrendered in {{ $fr->surrender_date->format('F Y') }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2" style="margin-top:20px;">
                    <a href="{{ route('mblrc.fr.edit', $fr) }}" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-pencil"></i> Edit</a>
                    <a href="{{ route('mblrc.fr.index') }}" class="btn btn-light btn-sm">Back</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-profile" role="tab">PROFILE INFORMATION</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reintegration" role="tab">REINTEGRATION INFORMATION</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-geotag" role="tab">GEOTAG AND LOCATION INFORMATION</a></li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        {{-- PROFILE TAB --}}
        <div class="tab-pane fade show active" id="tab-profile" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 section-head">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-account-circle me-2" style="font-size:1.8rem;color:#63479B;"></i>
                            <h3 class="font-weight-bold mb-0" style="color:#63479B;">PROFILE INFORMATION</h3>
                        </div>
                        <a href="{{ route('mblrc.fr.edit', $fr) }}" class="btn btn-primary"><i class="mdi mdi-pencil me-1"></i>Edit Profile</a>
                    </div>
                    <div class="row g-3">
                        @foreach ($profileFields as [$label, $icon, $value, $col])
                            <div class="{{ $col }}">
                                <div class="info-card">
                                    <small class="text-muted text-uppercase">{{ $label }}</small>
                                    <div class="d-flex align-items-center info-item">
                                        <div class="icon-container"><i class="mdi {{ $icon }}"></i></div>
                                        <div class="form-control-static">{{ $value }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Background --}}
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4 section-head">
                        <i class="mdi mdi-history me-2" style="font-size:1.8rem;color:#F5A623;"></i>
                        <h3 class="font-weight-bold mb-0" style="color:#F5A623;">BACKGROUND</h3>
                    </div>
                    <div class="row g-3">
                        @foreach ($bgFields as [$label, $icon, $value, $col, $accent])
                            <div class="{{ $col }}">
                                <div class="info-card">
                                    <small class="text-muted">{{ $label }}</small>
                                    <div class="d-flex align-items-center info-item">
                                        <div class="icon-container {{ $accent }}"><i class="mdi {{ $icon }}"></i></div>
                                        <div class="form-control-static">{{ $value }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3-Months Program Status --}}
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 section-head">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-clipboard-text-outline me-2" style="font-size:1.8rem;color:#3D0075;"></i>
                            <h3 class="font-weight-bold mb-0" style="color:#3D0075;">3-MONTHS PROGRAM STATUS</h3>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editStatusForm">Edit Status</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-card">
                                <small class="text-muted">Reintegration Status</small>
                                <div class="d-flex align-items-center info-item">
                                    <div class="icon-container green"><i class="mdi mdi-check-circle"></i></div>
                                    <div class="form-control-static">{{ $ps?->reintegration_status ?? 'Not set' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <small class="text-muted">Date of Reintegration</small>
                                <div class="d-flex align-items-center info-item">
                                    <div class="icon-container green"><i class="mdi mdi-calendar-check"></i></div>
                                    <div class="form-control-static">{{ $ps?->reintegration_date?->format('F d, Y') ?? 'Not set' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse mt-3" id="editStatusForm">
                        <form data-program-form class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Reintegration Status</label>
                                <select name="reintegration_status" class="form-select" required>
                                    @foreach (['Not-Started', 'On-going', 'Completed'] as $s)
                                        <option value="{{ $s }}" @selected($ps?->reintegration_status === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date of Reintegration</label>
                                <input name="reintegration_date" type="date" value="{{ $ps?->reintegration_date?->toDateString() }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- REINTEGRATION TAB --}}
        <div class="tab-pane fade" id="tab-reintegration" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h4 class="font-weight-bold mb-4" style="color:#3F51B5;">SOCIO-ECONOMIC STATUS</h4>

                    {{-- Education and Work --}}
                    <div class="info-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">EDUCATION AND WORK INFORMATION</small>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#eduForm"><i class="mdi mdi-pencil"></i> Edit</button>
                        </div>
                        <div class="d-flex align-items-center info-item mb-2">
                            <div class="icon-container"><i class="mdi mdi-school"></i></div>
                            <div class="info-content">
                                <small class="text-muted d-block">Educational Level</small>
                                <div class="form-control-static">{{ $education?->educational_attainment ?: 'Not Specified' }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center info-item">
                            <div class="icon-container"><i class="mdi mdi-briefcase"></i></div>
                            <div class="info-content">
                                <small class="text-muted d-block">Current Work/Profession</small>
                                <div class="form-control-static">{{ $education?->occupation ?: ($fr->occupation ?: 'Not specified') }}</div>
                            </div>
                        </div>
                        <div class="collapse mt-3" id="eduForm">
                            <form data-education-form class="row g-2">
                                <div class="col-sm-6">
                                    <label class="form-label">Educational Level</label>
                                    <select name="educational_attainment" class="form-select">
                                        <option value="">Select Educational Level</option>
                                        @foreach (['Elementary Level','Elementary Graduate','High School Level','High School Graduate','College Level','College Graduate','Post Graduate','Vocational'] as $lvl)
                                            <option value="{{ $lvl }}" @selected($education?->educational_attainment === $lvl)>{{ $lvl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Current Work/Profession</label>
                                    <input name="occupation" placeholder="Enter current work/profession" class="form-control" value="{{ $education?->occupation ?? $fr->occupation }}">
                                </div>
                                <div class="col-12"><button class="btn btn-primary">Save Information</button></div>
                            </form>
                        </div>
                    </div>

                    {{-- Skill/Works Enhancement --}}
                    <div class="info-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">SKILL/WORKS ENHANCEMENT</small>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#skillForm"><i class="mdi mdi-plus"></i> Add Skills</button>
                        </div>
                        <ul data-skills-list class="list-unstyled mb-0">
                            @foreach ($fr->skills as $skill)
                                <li class="d-flex align-items-center info-item mb-3 p-2 border-bottom" data-skill-id="{{ $skill->id }}">
                                    <div class="icon-container me-3"><i class="mdi mdi-wrench text-primary"></i></div>
                                    <div class="info-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $skill->skill_name }}</h6>
                                                <small class="text-muted"><i class="mdi mdi-star me-1"></i>{{ $skill->proficiency_level }}</small>
                                            </div>
                                            <button type="button" data-skill-delete="{{ route('mblrc.fr.skills.destroy', $skill) }}" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete"></i></button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @if ($fr->skills->isEmpty())
                            <div class="d-flex align-items-center info-item p-3 bg-light rounded">
                                <div class="icon-container me-3"><i class="mdi mdi-information text-muted"></i></div>
                                <div class="text-muted">No skills added yet</div>
                            </div>
                        @endif
                        <div class="collapse mt-3" id="skillForm">
                            <form data-skill-form class="row g-2">
                                <div class="col-md-6">
                                    <input name="skill_name" list="skillSuggestions" placeholder="Skill name" class="form-control" required>
                                    <datalist id="skillSuggestions"></datalist>
                                </div>
                                <div class="col-md-4">
                                    <select name="proficiency_level" class="form-select">
                                        @foreach (['Beginner', 'Intermediate', 'Advanced'] as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
                            </form>
                        </div>
                    </div>

                    {{-- Government Assistance Received --}}
                    <div class="info-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">GOVERNMENT ASSISTANCE RECEIVED</small>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#assistForm"><i class="mdi mdi-plus"></i> Add New Assistance</button>
                        </div>
                        <ul data-assistance-list class="list-unstyled mb-0">
                            @foreach ($fr->assistances as $a)
                                <li class="d-flex align-items-center info-item mb-3 p-2 border-bottom">
                                    <div class="icon-container me-3"><i class="mdi mdi-heart text-primary"></i></div>
                                    <div class="info-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $a->assistance_type }} <span class="badge badge-secondary ms-1">{{ $a->status }}</span></h6>
                                                <small class="text-muted"><i class="mdi mdi-calendar me-1"></i>{{ $a->date_received?->format('F d, Y') ?? '—' }}</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($a->certificate_file)
                                                    <a href="{{ route('mblrc.fr.assistance.certificate', $a) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-file-document me-1"></i> Certificate</a>
                                                @endif
                                                @if ($a->status !== 'Completed' && ! $a->date_received && ! $a->certificate_file)
                                                    <button type="button" data-assistance-delete="{{ route('mblrc.fr.assistance.destroy', $a) }}" class="btn btn-outline-danger btn-sm" aria-label="Delete pending assistance"><i class="mdi mdi-delete"></i></button>
                                                @else
                                                    <span class="text-muted" title="Recorded assistance history is protected"><i class="mdi mdi-lock"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @if ($fr->assistances->isEmpty())
                            <div class="d-flex align-items-center info-item p-3 bg-light rounded">
                                <div class="icon-container me-3"><i class="mdi mdi-information text-muted"></i></div>
                                <div class="text-muted">No government assistance records yet</div>
                            </div>
                        @endif
                        <div class="collapse mt-3" id="assistForm">
                            <form data-assistance-form class="row g-2" enctype="multipart/form-data">
                                <div class="col-12"><input name="assistance_type" placeholder="Assistance type" class="form-control" required></div>
                                <div class="col-sm-6"><input name="date_received" type="date" class="form-control"></div>
                                <div class="col-sm-6">
                                    <select name="status" class="form-select">
                                        @foreach (['Pending', 'In Progress', 'Completed'] as $s)
                                            <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12"><input name="certificate" type="file" accept=".jpg,.jpeg,.png,.gif,.pdf" class="form-control form-control-sm"></div>
                                <div class="col-12"><button class="btn btn-primary w-100">Add assistance</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GEOTAG TAB --}}
        <div class="tab-pane fade" id="tab-geotag" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 section-head">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-map-marker-radius me-2" style="font-size:1.6rem;color:#3F51B5;"></i>
                            <h3 class="font-weight-bold mb-0" style="color:#3F51B5;">LOCATION INFORMATION</h3>
                        </div>
                        <span class="small text-muted">Click the map to set a point</span>
                    </div>
                    <div id="frLocationMap"></div>
                    <form data-location-form class="mt-3 d-flex flex-wrap align-items-end gap-2">
                        <div class="flex-grow-1">
                            <label class="form-label">Placement address</label>
                            <input name="placement_address" value="{{ $fr->placement_address }}" class="form-control" required>
                        </div>
                        <input name="latitude" type="hidden" value="{{ $fr->latitude }}">
                        <input name="longitude" type="hidden" value="{{ $fr->longitude }}">
                        <button class="btn btn-success">Save location</button>
                    </form>
                    <div data-location-history class="mt-3 small text-muted"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade program-status-modal" id="programStatusConfirmModal" tabindex="-1" aria-labelledby="programStatusConfirmTitle" aria-describedby="programStatusConfirmText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="program-status-modal-icon" aria-hidden="true"><i class="mdi mdi-alert-circle-outline"></i></div>
                <h4 class="program-status-modal-title" id="programStatusConfirmTitle">Confirm status update</h4>
                <p class="program-status-modal-text" id="programStatusConfirmText">Review the new 3-month program status before saving this official record.</p>
                <dl class="program-status-summary">
                    <div><dt>Reintegration status</dt><dd data-program-confirm-status>—</dd></div>
                    <div><dt>Date of reintegration</dt><dd data-program-confirm-date>—</dd></div>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn program-status-confirm-button" data-program-confirm-save>
                    <span data-program-confirm-label>Confirm update</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade program-status-modal" id="programStatusSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="programStatusSuccessTitle" aria-describedby="programStatusSuccessText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="program-status-modal-icon success" aria-hidden="true"><i class="mdi mdi-check-circle-outline"></i></div>
                <h4 class="program-status-modal-title" id="programStatusSuccessTitle">Status updated successfully</h4>
                <p class="program-status-modal-text mb-0" id="programStatusSuccessText" data-program-success-message>The FR/FVE program status has been saved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-program-success-close>View updated profile</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const DAVAO_SUR = [6.7497, 125.3572];
    const root = document.getElementById('frProfile');
    if (!root) return;

    function token() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    }

    async function postJson(url, body, method = 'POST') {
        const isForm = body instanceof FormData;
        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': token(),
                'Accept': 'application/json',
                ...(isForm ? {} : { 'Content-Type': 'application/json' }),
            },
            body: isForm ? body : JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            const validationMessage = Object.values(data.errors || {}).flat()[0];
            alert(validationMessage || data.message || 'The request could not be completed.');
        }

        return data;
    }

    // Program status confirmation and success feedback
    const programForm = root.querySelector('[data-program-form]');
    const programConfirmElement = document.getElementById('programStatusConfirmModal');
    const programSuccessElement = document.getElementById('programStatusSuccessModal');
    const programConfirmButton = document.querySelector('[data-program-confirm-save]');
    const programConfirmLabel = document.querySelector('[data-program-confirm-label]');
    const programConfirmModal = programConfirmElement && window.bootstrap
        ? new bootstrap.Modal(programConfirmElement)
        : null;
    const programSuccessModal = programSuccessElement && window.bootstrap
        ? new bootstrap.Modal(programSuccessElement)
        : null;
    let pendingProgramStatus = null;

    [programConfirmElement, programSuccessElement].filter(Boolean).forEach((modalElement) => {
        modalElement.addEventListener('show.bs.modal', () => {
            document.body.classList.add('program-status-modal-open');
        });
        modalElement.addEventListener('hidden.bs.modal', () => {
            requestAnimationFrame(() => {
                const modalIsOpen = programConfirmElement?.classList.contains('show')
                    || programSuccessElement?.classList.contains('show');
                document.body.classList.toggle('program-status-modal-open', modalIsOpen);
            });
        });
    });

    function formatProgramDate(value) {
        if (!value) return 'Not set';

        const [year, month, day] = value.split('-').map(Number);
        return new Date(year, month - 1, day).toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
    }

    async function saveProgramStatus() {
        if (!pendingProgramStatus || !programConfirmButton) return;

        programConfirmButton.disabled = true;
        programConfirmLabel.textContent = 'Saving...';

        try {
            const response = await postJson(root.dataset.programStatus, pendingProgramStatus, 'PUT');
            if (!response.success) return;

            const showSuccess = () => {
                const successMessage = document.querySelector('[data-program-success-message]');
                if (successMessage && response.message) successMessage.textContent = response.message;

                if (programSuccessModal) {
                    programSuccessModal.show();
                } else {
                    alert(response.message || 'The FR/FVE program status was updated successfully.');
                    location.reload();
                }
            };

            if (programConfirmModal) {
                programConfirmElement.addEventListener('hidden.bs.modal', showSuccess, { once: true });
                programConfirmModal.hide();
            } else {
                showSuccess();
            }
        } finally {
            programConfirmButton.disabled = false;
            programConfirmLabel.textContent = 'Confirm update';
        }
    }

    programForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const f = e.target;
        if (!f.reportValidity()) return;

        pendingProgramStatus = {
            reintegration_status: f.reintegration_status.value,
            reintegration_date: f.reintegration_date.value || null,
        };

        document.querySelector('[data-program-confirm-status]').textContent = pendingProgramStatus.reintegration_status;
        document.querySelector('[data-program-confirm-date]').textContent = formatProgramDate(pendingProgramStatus.reintegration_date);

        if (programConfirmModal) {
            programConfirmModal.show();
        } else if (confirm('Save this 3-month program status update?')) {
            await saveProgramStatus();
        }
    });

    programConfirmButton?.addEventListener('click', saveProgramStatus);
    document.querySelector('[data-program-success-close]')?.addEventListener('click', () => location.reload());

    initLocationMap(root);
    initSkills(root);
    initAssistance(root);

    // Education / work
    root.querySelector('[data-education-form]')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const f = e.target;
        const r = await postJson(root.dataset.educationStore, {
            educational_attainment: f.educational_attainment.value,
            occupation: f.occupation.value,
        });
        if (r.success) location.reload();
    });

    function initLocationMap(root) {
        const mapEl = document.getElementById('frLocationMap');
        if (!mapEl) return;
        const lat = parseFloat(root.dataset.lat) || DAVAO_SUR[0];
        const lng = parseFloat(root.dataset.lng) || DAVAO_SUR[1];
        const map = L.map(mapEl).setView([lat, lng], root.dataset.lat ? 14 : 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19,
        }).addTo(map);
        // Tab is hidden on load — fix tile sizing when shown.
        document.querySelector('a[href="#tab-geotag"]')?.addEventListener('shown.bs.tab', () => map.invalidateSize());

        const form = root.querySelector('[data-location-form]');
        let marker = root.dataset.lat ? L.marker([lat, lng]).addTo(map) : null;

        map.on('click', (e) => {
            const { lat, lng } = e.latlng;
            if (marker) marker.setLatLng(e.latlng);
            else marker = L.marker(e.latlng).addTo(map);
            form.latitude.value = lat.toFixed(8);
            form.longitude.value = lng.toFixed(8);
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!form.latitude.value) return alert('Click the map to set a location first.');
            const r = await postJson(root.dataset.locationSave, {
                placement_address: form.placement_address.value,
                latitude: form.latitude.value,
                longitude: form.longitude.value,
            });
            if (r.success) location.reload();
        });

        // History
        fetch(root.dataset.locationHistory, { headers: { Accept: 'application/json' } })
            .then((r) => r.json())
            .then((rows) => {
                const box = root.querySelector('[data-location-history]');
                if (!box) return;
                box.innerHTML = rows.length
                    ? '<p class="fw-semibold text-muted mb-1">History</p>' + rows.map((h) =>
                        `<p class="mb-0">• ${h.placement_address ?? ''} <span class="text-muted">(${h.updated_by ?? ''})</span></p>`).join('')
                    : '';
            });
    }

    function initSkills(root) {
        const list = root.querySelector('[data-skills-list]');

        // suggestions
        fetch(root.dataset.skillsSuggest, { headers: { Accept: 'application/json' } })
            .then((r) => r.json())
            .then((rows) => {
                const dl = document.getElementById('skillSuggestions');
                if (dl) dl.innerHTML = rows.map((s) => `<option value="${s}">`).join('');
            });

        root.querySelector('[data-skill-form]')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const f = e.target;
            const r = await postJson(root.dataset.skillsStore, {
                skill_name: f.skill_name.value,
                proficiency_level: f.proficiency_level.value,
            });
            if (r.success) location.reload();
        });

        list?.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-skill-delete]');
            if (!btn) return;
            const r = await postJson(btn.dataset.skillDelete, {}, 'DELETE');
            if (r.success) btn.closest('[data-skill-id]').remove();
        });
    }

    function initAssistance(root) {
        const list = root.querySelector('[data-assistance-list]');

        root.querySelector('[data-assistance-form]')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const r = await postJson(root.dataset.assistanceStore, new FormData(e.target));
            if (r.success) location.reload();
        });

        list?.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-assistance-delete]');
            if (!btn) return;
            const r = await postJson(btn.dataset.assistanceDelete, {}, 'DELETE');
            if (r.success) location.reload();
        });
    }
})();
</script>
@endpush
