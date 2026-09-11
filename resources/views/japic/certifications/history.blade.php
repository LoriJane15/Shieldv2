@extends('layouts.skydash-v')
@section('title', 'Certification Draft History')
@section('heading', 'JAPIC Certification')

@section('content')
<a href="{{ route('japic.certifications.show', $processing) }}" class="d-inline-block mb-3">&larr; Back to certification profile</a>
<div class="row">
    <div class="col-lg-4 mb-4"><section class="card h-100"><div class="card-header"><h2 class="h5 mb-0">View History</h2></div><div class="list-group list-group-flush">
        @forelse($revisions as $item)
            <a class="list-group-item list-group-item-action {{ ($selectedRevision['revision'] ?? null) === $item['revision'] ? 'active' : '' }}" href="{{ route('japic.certifications.history', [$processing, 'revision' => $item['revision']]) }}"><strong>Revision {{ $item['revision'] }}</strong><br><small>{{ $item['saved_at']->format('M d, Y h:i A') }} · {{ $item['saved_by'] }}</small><div class="mt-1">{{ $item['summary'] }}</div></a>
        @empty
            <div class="list-group-item text-muted">No draft revisions have been saved.</div>
        @endforelse
    </div></section></div>
    <div class="col-lg-8 mb-4"><section class="card h-100"><div class="card-header"><h2 class="h5 mb-0">@if($selectedRevision)Revision {{ $selectedRevision['revision'] }} Changes @else Revision Changes @endif</h2></div><div class="card-body">
        @if($selectedRevision)
            @if($selectedRevision['initial'])<p><strong>Initial draft</strong></p>@endif
            <div class="table-responsive"><table class="table"><thead><tr><th>Field name</th>@unless($selectedRevision['initial'])<th>Previous value</th>@endunless<th>{{ $selectedRevision['initial'] ? 'Initial value' : 'New value' }}</th></tr></thead><tbody>
                @forelse($selectedRevision['changes'] as $change)<tr><th>{{ $change['field'] }}</th>@unless($selectedRevision['initial'])<td>{{ $change['previous_value'] }}</td>@endunless<td>{{ $change['new_value'] }}</td></tr>@empty<tr><td colspan="3" class="text-muted">No approved certification fields changed.</td></tr>@endforelse
            </tbody></table></div>
        @else
            <p class="text-muted mb-0">Select a revision to view its changes.</p>
        @endif
    </div></section></div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4"><section class="card h-100"><div class="card-header"><h2 class="h5 mb-0">Certification Workflow History</h2></div><div class="card-body">
        @forelse($workflowEvents as $event)
            <div class="border-bottom pb-3 mb-3"><strong>{{ $event['event'] }}</strong><div class="text-muted small">{{ $event['from_status'] ?? 'Intake' }} &rarr; {{ $event['to_status'] }} · {{ $event['occurred_at']->format('M d, Y h:i A') }} · {{ $event['actor'] }}</div>@if($event['completion_path'])<div class="mt-1">{{ $event['completion_path'] }}</div>@endif</div>
        @empty
            <p class="text-muted mb-0">No certification workflow events have been recorded.</p>
        @endforelse
    </div></section></div>
    <div class="col-lg-5 mb-4"><section class="card h-100"><div class="card-header"><h2 class="h5 mb-0">Final Certification Versions</h2></div><div class="card-body">
        @forelse($documentVersions as $version)
            <div class="border-bottom pb-3 mb-3"><strong>Version {{ $version['version_number'] }}</strong>@if($version['replaces_version_number'])<span class="badge badge-secondary ml-1">Replaces v{{ $version['replaces_version_number'] }}</span>@endif<div>{{ $version['original_filename'] }}</div><div class="text-muted small">{{ $version['mime_type'] }} · {{ number_format($version['size_bytes']) }} bytes · {{ $version['uploaded_at']->format('M d, Y h:i A') }} · {{ $version['uploaded_by'] }}</div><div class="text-muted small text-break">SHA-256: {{ $version['sha256'] }}</div></div>
        @empty
            <p class="text-muted mb-0">No final certification has been uploaded.</p>
        @endforelse
    </div></section></div>
</div>
@endsection
