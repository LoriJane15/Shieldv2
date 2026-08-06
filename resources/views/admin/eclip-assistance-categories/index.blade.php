@extends('layouts.skydash-h')
@section('title', 'E-CLIP Assistance Categories')
@section('heading', 'E-CLIP Assistance Categories')
@section('content')
<div class="row"><div class="col-lg-5 mb-4"><div class="card"><div class="card-body"><h4>Add Official Category</h4><p class="text-muted">Only add categories confirmed by the approved E-CLIP process.</p>
<form method="POST" action="{{ route('admin.eclip.assistance-categories.store') }}">@csrf
<div class="form-group"><label>Code</label><input name="code" value="{{ old('code') }}" pattern="[A-Z0-9_]+" maxlength="80" class="form-control @error('code') is-invalid @enderror" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-group"><label>Name</label><input name="name" value="{{ old('name') }}" maxlength="255" class="form-control" required></div>
<div class="form-group"><label>Description</label><textarea name="description" maxlength="2000" class="form-control">{{ old('description') }}</textarea></div>
<div class="form-group"><label>Display order</label><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="10000" class="form-control" required></div><button class="btn btn-primary">Add Category</button></form>
</div></div></div><div class="col-lg-7"><div class="card"><div class="card-body"><h4>Configured Categories</h4><div class="table-responsive"><table class="table"><thead><tr><th>Code</th><th>Name</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($categories as $category)<tr><td>{{ $category->code }}</td><td>{{ $category->name }}</td><td>{{ $category->is_active ? 'Active' : 'Inactive' }}</td><td><form method="POST" action="{{ route('admin.eclip.assistance-categories.toggle', $category) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button></form></td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No categories configured.</td></tr>@endforelse
</tbody></table></div></div></div></div></div>
@endsection
