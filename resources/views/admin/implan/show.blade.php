@extends('layouts.skydash-h')
@section('title', 'IMPLAN')
@section('heading', 'Implementation Plan')

@php
    $statusBadge = match ($implan->status) {
        'verified' => 'badge badge-success', 'for verification' => 'badge badge-warning',
        'ongoing' => 'badge badge-info', default => 'badge badge-secondary',
    };
    $hasRejected = $implan->taggings->contains('status', 'Rejected');
    $tagByAgency = $implan->taggings->keyBy('gov_agency_id');
@endphp

@push('styles')
    <style>
        .verify-implan-scroll-lock,
        .verify-implan-scroll-lock body {
            height: 100%;
            overflow: hidden;
        }

        .verify-implan-scroll-lock .container-scroller,
        .verify-implan-scroll-lock .page-body-wrapper {
            max-height: 100vh;
            overflow: hidden;
        }

        #verifyImplanModal {
            overflow: hidden;
        }

        #verifyImplanModal .modal-dialog {
            display: flex;
            min-height: calc(100dvh - 3.5rem);
            max-width: 430px;
            align-items: center;
            margin: 1.75rem auto;
            pointer-events: none;
        }

        #verifyImplanModal .modal-content {
            pointer-events: auto;
        }

        #verifyImplanModal.fade .modal-dialog {
            transform: translateY(18px) scale(0.92);
            transition: transform 0.22s cubic-bezier(0.2, 1.12, 0.42, 1), opacity 0.18s ease-out;
        }

        #verifyImplanModal.show .modal-dialog {
            transform: translateY(0) scale(1);
        }

        .verify-implan-content {
            overflow: hidden;
            border: 0;
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.26);
        }

        .verify-implan-body {
            padding: 1.75rem 1.75rem 1rem;
            text-align: center;
        }

        .verify-implan-icon {
            display: grid;
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            place-items: center;
            border-radius: 50%;
            background: #e8f7ee;
            color: #1f9d55;
            font-size: 1.85rem;
        }

        .verify-implan-title {
            margin-bottom: 0.4rem;
            color: #172033;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .verify-implan-text {
            max-width: 320px;
            margin: 0 auto 1rem;
            color: #64748b;
            line-height: 1.5;
        }

        .verify-implan-record {
            padding: 0.85rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            text-align: left;
        }

        .verify-implan-record-title {
            overflow-wrap: anywhere;
            color: #1e293b;
            font-weight: 600;
        }

        .verify-implan-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            padding: 1rem 1.75rem 1.75rem;
            border-top: 0;
        }

        .verify-implan-actions .btn {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 600;
        }

        @media (max-width: 575.98px) {
            #verifyImplanModal .modal-dialog {
                min-height: calc(100dvh - 1rem);
                margin: 0.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-8 col-xl-8 mb-3 mb-xl-0">
            <h3 class="font-weight-bold">Implementation Plan</h3>
            <h6 class="mb-0" style="color: rgba(156, 156, 156, 1); font-weight: 300;">
                <span style="color: #280274; font-weight: bold;">RCSP implementation details</span> — information, agenda files, and documentation.
            </h6>
        </div>
        <div class="col-4 col-xl-4">
            <div class="justify-content-end d-flex align-items-center gap-2">
                <span class="{{ $statusBadge }}">{{ $implan->status }}</span>
                @if ($implan->status === 'for verification')
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#verifyImplanModal">
                        <i class="mdi mdi-check-decagram"></i> Verify
                    </button>
                @endif
                @if ($hasRejected)
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reassignModal">
                        <i class="mdi mdi-autorenew"></i> Reassign
                    </button>
                @endif
                <a href="{{ route('admin.implan.index') }}" class="btn btn-sm btn-light bg-white">Back</a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT: summary cards --}}
        <div class="col-md-4 grid-margin">
            <div class="card">
                <div class="card-body p-3">
                    <div class="mb-3">
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">IMPLAN #{{ $implan->id }}</p>
                    </div>

                    <div class="card mb-3" style="background-color: #ffebe6; border: none; border-radius: 12px;">
                        <div class="card-body p-3">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Target Beneficiary</p>
                            <h1 style="color: #d63300; font-weight: 600; font-size: 1.8rem;">{{ $implan->beneficiaries ?: '—' }}</h1>
                        </div>
                    </div>

                    <div class="card mb-3" style="background-color: #e6ffe6; border: none; border-radius: 12px;">
                        <div class="card-body p-3">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Resources Needed</p>
                            <h1 style="color: #008000; font-weight: 600; font-size: 1.8rem;">{{ $implan->resources ?: '—' }}</h1>
                        </div>
                    </div>

                    <div class="card mb-4" style="background-color: #e6e6ff; border: none; border-radius: 12px;">
                        <div class="card-body p-3">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Target Area</p>
                            @forelse ($areaNames as $name)
                                <h4 style="color: #000080; font-weight: 500; font-size: 1.4rem;">{{ $name }}</h4>
                            @empty
                                <h4 style="color: #000080; font-weight: 500; font-size: 1.4rem;">No Areas Defined</h4>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">Other Responsible Agency</p>
                        @forelse ($assignedAgencies as $agency)
                            @php $tag = $tagByAgency[$agency->id] ?? null; @endphp
                            <div class="d-inline-flex flex-column align-items-center me-3 mb-2 text-center">
                                @if ($agency->profile)
                                    <img src="{{ $agency->profile_url }}" alt="{{ $agency->acronym }}"
                                         style="width: 50px; height: 50px; border-radius: 50%; object-fit: contain; background:#f4f5f7;">
                                @else
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:50px;height:50px;">
                                        <i class="mdi mdi-office-building text-muted"></i>
                                    </span>
                                @endif
                                <small class="fw-medium mt-1">{{ $agency->acronym }}</small>
                                @if ($tag)
                                    <span class="badge {{ $tag->status === 'Accepted' ? 'badge-success' : ($tag->status === 'Rejected' ? 'badge-danger' : 'badge-secondary') }}">{{ $tag->status }}</span>
                                @endif
                            </div>
                        @empty
                            <div>No Agencies Defined</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: tabbed detail --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="implanTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Agenda File</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#monitoring" type="button" role="tab">Documentation</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="implanTabContent">
                        {{-- Information --}}
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <div class="header-section" style="background: linear-gradient(to right, #6b5b95, #b8860b); padding: 15px; border-radius: 10px; display: flex; align-items: center; margin-bottom: 20px;">
                                <img src="{{ asset('assets/img/agencies/dilg.png') }}" alt="Cluster Logo" style="width: 80px; height: 80px; margin-right: 15px;">
                                <div style="color: white;">
                                    <h3 style="margin: 0;">IMPLEMENTATION PLAN</h3>
                                    <p style="margin: 0;">Refocus Implementation Plan</p>
                                </div>
                            </div>

                            <div class="content-section" style="padding: 0 10px;">
                                <div class="info-group mb-3">
                                    <label style="color: #666; font-size: 0.9em;">Issues or Concern</label>
                                    <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $implan->issues ?: '—' }}</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label style="color: #666; font-size: 0.9em;">Program/Project/Activity</label>
                                    <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $implan->program ?: '—' }}</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label style="color: #666; font-size: 0.9em;">Expected Results/Outcome</label>
                                    <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $implan->outcome ?: '—' }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-group mb-3">
                                            <label style="color: #666; font-size: 0.9em;">Responsible Agency</label>
                                            <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $assignedAgencies->pluck('acronym')->implode(', ') ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group mb-3">
                                            <label style="color: #666; font-size: 0.9em;">Type of Government Agency</label>
                                            <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $implan->type_gov ?: '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-group mb-3">
                                            <label style="color: #666; font-size: 0.9em;">Source</label>
                                            <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $implan->sources ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group mb-3">
                                            <label style="color: #666; font-size: 0.9em;">Remarks</label>
                                            <div style="font-size: 1.1em; padding: 8px 0; border-bottom: 1px solid #eee;">{{ $implan->remarks ?: '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Agenda File --}}
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <div class="p-2">
                                @forelse ($implan->files as $file)
                                    <div class="border rounded p-3 mb-2">
                                        <a href="{{ $file->pdf ? Storage::url($file->pdf) : '#' }}" target="_blank" class="fw-medium">
                                            {{ $file->file_name }}
                                        </a>
                                        <div class="text-muted small">{{ $file->pdf }}</div>
                                        <p class="text-muted small mb-0">{{ $file->description }}</p>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No agenda files uploaded.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Documentation --}}
                        <div class="tab-pane fade" id="monitoring" role="tabpanel">
                            <div class="p-2">
                                @if ($implan->photos->isEmpty())
                                    <p class="text-muted mb-0">No documentation photos.</p>
                                @else
                                    <div class="row g-2">
                                        @foreach ($implan->photos as $photo)
                                            <div class="col-4 col-sm-3">
                                                <a href="{{ Storage::url($photo->image) }}" target="_blank">
                                                    <img src="{{ Storage::url($photo->image) }}" class="img-fluid rounded"
                                                         style="height:100px;width:100%;object-fit:cover;" alt="doc">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($implan->status === 'for verification')
        <div class="modal fade" id="verifyImplanModal" tabindex="-1" aria-labelledby="verifyImplanModalTitle" aria-describedby="verifyImplanModalText">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content verify-implan-content">
                    <form method="POST" action="{{ route('admin.implan.verify', $implan) }}">
                        @csrf
                        <div class="verify-implan-body">
                            <div class="verify-implan-icon" aria-hidden="true">
                                <i class="mdi mdi-check-decagram"></i>
                            </div>
                            <h5 class="verify-implan-title" id="verifyImplanModalTitle">Confirm verification</h5>
                            <p id="verifyImplanModalText" class="verify-implan-text">
                                Mark this implementation plan as verified?
                            </p>
                            <div class="verify-implan-record">
                                <p class="text-muted mb-1" style="font-size: 0.85rem;">IMPLAN #{{ $implan->id }}</p>
                                <p class="mb-0 verify-implan-record-title">{{ $implan->program ?: $implan->issues ?: 'Implementation plan' }}</p>
                            </div>
                        </div>
                        <div class="verify-implan-actions">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="mdi mdi-check-decagram"></i> Verify
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @if ($hasRejected)
        <div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reassign to Agencies</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.implan.reassign', $implan) }}">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted">Select the agencies to reassign this plan to.</p>
                            <div class="border rounded p-2" style="max-height:14rem;overflow-y:auto;">
                                @foreach ($allAgencies as $agency)
                                    <div class="form-check">
                                        <input type="checkbox" name="agencies[]" value="{{ $agency->id }}" class="form-check-input" id="agency-{{ $agency->id }}">
                                        <label class="form-check-label" for="agency-{{ $agency->id }}">{{ $agency->acronym }} — {{ $agency->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Reassign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            (() => {
                const modal = document.getElementById('verifyImplanModal');

                if (!modal) {
                    return;
                }

                const lockPageScroll = () => {
                    document.documentElement.classList.add('verify-implan-scroll-lock');
                    document.body.classList.add('verify-implan-scroll-lock');
                };

                const unlockPageScroll = () => {
                    document.documentElement.classList.remove('verify-implan-scroll-lock');
                    document.body.classList.remove('verify-implan-scroll-lock');
                };

                modal.addEventListener('show.bs.modal', lockPageScroll);
                modal.addEventListener('hidden.bs.modal', unlockPageScroll);
            })();
        </script>
    @endpush
@endsection
