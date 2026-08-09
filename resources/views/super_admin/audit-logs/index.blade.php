@extends('layouts.skydash-v')
@section('title', 'General Audit Logs')
@section('heading', 'General Audit Logs')

@section('content')
<div class="card"><div class="card-body">
    <h4 class="card-title mb-1">General Audit Logs</h4>
    <p class="text-muted mb-4">Technical activity metadata only. Sensitive previous and new values are not displayed.</p>

    <form method="GET" action="{{ route('super_admin.audit-logs.index') }}" class="row g-2 mb-4">
        <div class="col-md-6"><label for="audit-search" class="form-label">Search actor or affected record</label><input id="audit-search" name="search" value="{{ request('search') }}" maxlength="100" class="form-control" placeholder="User, entity type, or record ID"></div>
        <div class="col-md-4"><label for="audit-action" class="form-label">Action</label><select id="audit-action" name="action" class="form-control"><option value="">All actions</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Apply filters</button></div>
    </form>

    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Date and time</th><th>Actor</th><th>Action</th><th>Affected record</th><th>IP address</th></tr></thead>
        <tbody>@forelse($logs as $log)<tr><td><time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->timezone(config('app.display_timezone'))->format('d M Y, g:i A') }}</time></td><td>{{ $log->user?->name ?? 'System' }}</td><td><span class="badge badge-info">{{ str($log->action)->replace('_', ' ')->title() }}</span></td><td>{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td><td>{{ $log->ip_address ?: '—' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">No audit activity matches the selected filters.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $logs->links() }}
</div></div>
@endsection
