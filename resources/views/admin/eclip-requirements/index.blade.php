@extends('layouts.skydash-h')
@section('title', 'E-CLIP Document Requirements')
@section('heading', 'E-CLIP Document Requirements')

@section('content')
<div class="row">
    <div class="col-lg-5 mb-4"><div class="card"><div class="card-body">
        <h4>Add Official Requirement</h4><p class="text-muted">Only add document types confirmed by the approved E-CLIP process.</p>
        <form method="POST" action="{{ route('admin.eclip.requirements.store') }}">@csrf
            <div class="form-group"><label for="code">Code</label><input id="code" name="code" value="{{ old('code') }}" pattern="[A-Z0-9_]+" maxlength="80" class="form-control @error('code') is-invalid @enderror" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="name">Document name</label><input id="name" name="name" value="{{ old('name') }}" maxlength="255" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" maxlength="2000" class="form-control">{{ old('description') }}</textarea></div>
            <div class="form-group"><label for="sort_order">Display order</label><input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="10000" class="form-control" required></div>
            <input type="hidden" name="is_required" value="0"><div class="form-check mb-3"><input id="is_required" type="checkbox" name="is_required" value="1" class="form-check-input" @checked(old('is_required', true))><label for="is_required" class="form-check-label">Required before certification</label></div>
            <button class="btn btn-primary">Add Requirement</button>
        </form>
    </div></div></div>
    <div class="col-lg-7"><div class="card"><div class="card-body"><h4>Configured Checklist</h4><div class="table-responsive"><table class="table">
        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($requirements as $requirement)<tr><td>{{ $requirement->code }}</td><td>{{ $requirement->name }}</td><td>{{ $requirement->is_required ? 'Required' : 'Optional' }}</td><td>{{ $requirement->is_active ? 'Active' : 'Inactive' }}</td><td><form method="POST" action="{{ route('admin.eclip.requirements.toggle', $requirement) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $requirement->is_active ? 'Deactivate' : 'Activate' }}</button></form></td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted">No requirements configured. Document uploads remain unavailable.</td></tr>@endforelse
        </tbody></table></div></div></div></div>
</div>
@endsection
