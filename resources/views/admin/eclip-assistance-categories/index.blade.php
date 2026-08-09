@extends('layouts.skydash-h')
@section('title', 'E-CLIP Assistance')
@section('heading', 'E-CLIP Assistance')

@push('styles')
    @include('admin.partials.eclip-config-styles')
@endpush

@section('content')
<div class="eclip-config">
    <section class="config-hero mb-4" aria-labelledby="assistance-page-title">
        <div class="config-hero-copy">
            <div class="config-eyebrow mb-1">E-CLIP configuration</div>
            <h2 id="assistance-page-title" class="mb-2">Assistance Categories</h2>
            <p class="mb-0">Maintain the official assistance categories available during E-CLIP assessment and processing.</p>
        </div>
        <div class="config-total" aria-label="{{ $categories->count() }} configured categories">
            <i class="mdi mdi-hand-heart-outline" aria-hidden="true"></i>
            <div><strong>{{ number_format($categories->count()) }}</strong><span>Configured</span></div>
        </div>
    </section>

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <section class="card config-card" aria-labelledby="add-category-title">
                <div class="config-card-header">
                    <span class="config-card-icon"><i class="mdi mdi-plus-box-outline" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="add-category-title" class="config-title">Add category</h3>
                        <p class="config-subtitle">Add only assistance categories confirmed by the approved E-CLIP process.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.eclip.assistance-categories.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="code">Category code <span class="required-mark" aria-hidden="true">*</span></label>
                            <input id="code" name="code" value="{{ old('code') }}" pattern="[A-Z0-9_]+" maxlength="80" placeholder="e.g. LIVELIHOOD" autocomplete="off" class="form-control @error('code') is-invalid @enderror" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="field-hint">Use uppercase letters, numbers, and underscores only.</small>
                        </div>
                        <div class="form-group">
                            <label for="name">Category name <span class="required-mark" aria-hidden="true">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" maxlength="255" placeholder="Official assistance category" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" maxlength="2000" placeholder="Briefly explain the assistance covered by this category." class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Display order <span class="required-mark" aria-hidden="true">*</span></label>
                            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="10000" class="form-control @error('sort_order') is-invalid @enderror" required>
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="field-hint">Lower numbers appear first during assessment.</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block config-submit">
                            <i class="mdi mdi-plus-circle-outline mr-1" aria-hidden="true"></i> Add category
                        </button>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-xl-8 col-lg-7 mb-4">
            <section class="card config-card" aria-labelledby="categories-list-title">
                <div class="config-card-header">
                    <span class="config-card-icon"><i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i></span>
                    <div>
                        <h3 id="categories-list-title" class="config-title">Configured categories</h3>
                        <p class="config-subtitle">Inactive categories remain in history but cannot be selected for new assessments.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table config-table">
                        <thead><tr><th>Order</th><th>Category</th><th>Code</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td data-label="Order"><strong>{{ number_format($category->sort_order) }}</strong></td>
                                    <td data-label="Category">
                                        <span class="config-name">{{ $category->name }}</span>
                                        <span class="config-description">{{ $category->description ?: 'No description provided.' }}</span>
                                    </td>
                                    <td data-label="Code"><span class="config-code">{{ $category->code }}</span></td>
                                    <td data-label="Status"><span class="config-badge config-badge-{{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td data-label="Action" class="text-right">
                                        <form method="POST" action="{{ route('admin.eclip.assistance-categories.toggle', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn config-action {{ $category->is_active ? 'btn-outline-secondary' : 'btn-outline-primary' }}" @if($category->is_active) onclick="return confirm('Deactivate this assistance category? It will no longer be available for new assessments.')" @endif>
                                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="config-empty"><i class="mdi mdi-hand-heart-outline" aria-hidden="true"></i><strong>No categories configured</strong>Add an official category to make it available during E-CLIP assessment.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
