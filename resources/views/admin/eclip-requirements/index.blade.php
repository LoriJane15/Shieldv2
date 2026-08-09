@extends('layouts.skydash-h')
@section('title', 'E-CLIP Documents')
@section('heading', 'E-CLIP Documents')

@push('styles')
    @include('admin.partials.eclip-config-styles')
@endpush

@section('content')
<div class="eclip-config">
    <section class="config-hero mb-4" aria-labelledby="documents-page-title">
        <div class="config-hero-copy">
            <div class="config-eyebrow mb-1">E-CLIP configuration</div>
            <h2 id="documents-page-title" class="mb-2">Document Requirements</h2>
            <p class="mb-0">Maintain the official document checklist used during E-CLIP case processing and certification.</p>
        </div>
        <div class="config-total" aria-label="{{ $requirements->count() }} configured requirements">
            <i class="mdi mdi-file-document-multiple-outline" aria-hidden="true"></i>
            <div><strong>{{ number_format($requirements->count()) }}</strong><span>Configured</span></div>
        </div>
    </section>

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <section class="card config-card" aria-labelledby="add-requirement-title">
                <div class="config-card-header">
                    <span class="config-card-icon"><i class="mdi mdi-file-plus-outline" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="add-requirement-title" class="config-title">Add requirement</h3>
                        <p class="config-subtitle">Add only document types confirmed by the approved E-CLIP process.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.eclip.requirements.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="code">Requirement code <span class="required-mark" aria-hidden="true">*</span></label>
                            <input id="code" name="code" value="{{ old('code') }}" pattern="[A-Z0-9_]+" maxlength="80" placeholder="e.g. VALID_ID" autocomplete="off" class="form-control @error('code') is-invalid @enderror" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="field-hint">Use uppercase letters, numbers, and underscores only.</small>
                        </div>
                        <div class="form-group">
                            <label for="name">Document name <span class="required-mark" aria-hidden="true">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" maxlength="255" placeholder="Official document name" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" maxlength="2000" placeholder="Briefly explain when this document is used." class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Display order <span class="required-mark" aria-hidden="true">*</span></label>
                            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="10000" class="form-control @error('sort_order') is-invalid @enderror" required>
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="field-hint">Lower numbers appear first in the checklist.</small>
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="is_required" value="0">
                            <div class="config-check">
                                <input id="is_required" type="checkbox" name="is_required" value="1" @checked(old('is_required', true))>
                                <label for="is_required">Required before certification<small>Cases must provide this document before certification can proceed.</small></label>
                            </div>
                            @error('is_required')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-block config-submit">
                            <i class="mdi mdi-plus-circle-outline mr-1" aria-hidden="true"></i> Add requirement
                        </button>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-xl-8 col-lg-7 mb-4">
            <section class="card config-card" aria-labelledby="requirements-list-title">
                <div class="config-card-header">
                    <span class="config-card-icon"><i class="mdi mdi-format-list-checks" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="requirements-list-title" class="config-title">Configured checklist</h3>
                        <p class="config-subtitle">Inactive items remain in history but are not available for new document submissions.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table config-table">
                        <thead><tr><th>Order</th><th>Requirement</th><th>Code</th><th>Type</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                        <tbody>
                            @forelse($requirements as $requirement)
                                <tr>
                                    <td data-label="Order"><strong>{{ number_format($requirement->sort_order) }}</strong></td>
                                    <td data-label="Requirement">
                                        <span class="config-name">{{ $requirement->name }}</span>
                                        <span class="config-description">{{ $requirement->description ?: 'No description provided.' }}</span>
                                    </td>
                                    <td data-label="Code"><span class="config-code">{{ $requirement->code }}</span></td>
                                    <td data-label="Type"><span class="config-type"><i class="mdi {{ $requirement->is_required ? 'mdi-alert-circle-outline' : 'mdi-checkbox-blank-circle-outline' }} mr-1" aria-hidden="true"></i>{{ $requirement->is_required ? 'Required' : 'Optional' }}</span></td>
                                    <td data-label="Status"><span class="config-badge config-badge-{{ $requirement->is_active ? 'active' : 'inactive' }}">{{ $requirement->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td data-label="Action" class="text-right">
                                        <form method="POST" action="{{ route('admin.eclip.requirements.toggle', $requirement) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn config-action {{ $requirement->is_active ? 'btn-outline-secondary' : 'btn-outline-primary' }}" @if($requirement->is_active) onclick="return confirm('Deactivate this document requirement? It will no longer be available for new submissions.')" @endif>
                                                {{ $requirement->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="config-empty"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i><strong>No requirements configured</strong>Document uploads remain unavailable until an official requirement is added.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
