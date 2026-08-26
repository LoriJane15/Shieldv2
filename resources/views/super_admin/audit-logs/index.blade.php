@extends('layouts.skydash-v')
@section('title', 'General Audit Logs')
@section('heading', 'General Audit Logs')

@push('styles')
<style>
    .audit-page { --audit-purple: #6554c0; --audit-border: #e4e9f1; --audit-muted: #718096; margin: 0 auto; max-width: 1540px; }
    .audit-heading { align-items: flex-start; display: flex; gap: 1rem; justify-content: space-between; margin-bottom: 1rem; }
    .audit-heading h2 { color: #253858; font-size: 1.45rem; font-weight: 700; letter-spacing: -.015em; margin: 0 0 .3rem; }
    .audit-heading p { color: #718096; font-size: .84rem; margin: 0; }
    .audit-notice { align-items: flex-start; background: #f6f8fc; border: 1px solid #e1e7f0; border-radius: 9px; color: #617087; display: flex; font-size: .76rem; gap: .55rem; line-height: 1.45; margin-bottom: 1rem; padding: .7rem .85rem; }
    .audit-notice i { color: #6554c0; flex: 0 0 auto; font-size: .95rem; margin-top: .05rem; }
    .audit-card { border: 1px solid var(--audit-border); border-radius: 12px; box-shadow: 0 3px 14px rgba(23, 43, 77, .045); overflow: hidden; }
    .audit-filter-bar { background: #fff; border-bottom: 1px solid var(--audit-border); padding: 1rem; }
    .audit-filter-grid { align-items: end; display: grid; gap: .7rem; grid-template-columns: minmax(260px, 1.5fr) repeat(3, minmax(145px, .55fr)); }
    .audit-filter-grid-secondary { align-items: end; display: grid; gap: .7rem; grid-template-columns: repeat(4, minmax(145px, .55fr)) auto; margin-top: .7rem; }
    .audit-field { min-width: 0; }
    .audit-label { color: #52616f; display: block; font-size: .72rem; font-weight: 700; margin: 0 0 .35rem; }
    .audit-search { position: relative; }
    .audit-search i { color: #8b9bb0; font-size: .95rem; left: .8rem; position: absolute; top: 50%; transform: translateY(-50%); }
    .audit-search .form-control { padding-left: 2.25rem; }
    .audit-filter-bar .form-control { background-color: #fbfcfe; border: 1px solid #d9e1eb; border-radius: 8px; color: #42526b; font-size: .78rem; height: 40px; }
    .audit-filter-bar .form-control:focus { background-color: #fff; border-color: #8c80d5; box-shadow: 0 0 0 3px rgba(101, 84, 192, .1); }
    .audit-filter-actions { align-items: center; display: flex; gap: .45rem; justify-content: flex-end; }
    .audit-filter-button, .audit-clear-button { align-items: center; border-radius: 8px; display: inline-flex; font-size: .76rem; font-weight: 650; gap: .35rem; height: 40px; justify-content: center; padding: 0 .9rem; white-space: nowrap; }
    .audit-filter-button { background: var(--audit-purple); border: 1px solid var(--audit-purple); color: #fff; }
    .audit-filter-button:hover, .audit-filter-button:focus { background: #5545ad; border-color: #5545ad; box-shadow: 0 0 0 3px rgba(101, 84, 192, .13); color: #fff; outline: 0; }
    .audit-clear-button { background: #fff; border: 1px solid #d5dde8; color: #64748b; }
    .audit-clear-button:hover, .audit-clear-button:focus { background: #f6f8fb; color: #34445a; outline: 0; }
    .audit-errors { background: #fff3f4; border: 1px solid #f4c7cc; border-radius: 8px; color: #a63c49; font-size: .76rem; margin-bottom: .8rem; padding: .65rem .8rem; }
    .audit-section-heading { align-items: center; background: #fff; border-bottom: 1px solid var(--audit-border); display: flex; justify-content: space-between; padding: .9rem 1rem; }
    .audit-section-heading h3 { color: #344563; font-size: .9rem; font-weight: 700; margin: 0; }
    .audit-result-count { color: #7b8a9e; font-size: .75rem; }
    .audit-result-count strong { color: #42526b; }
    .audit-table { margin: 0; table-layout: fixed; }
    .audit-table thead th { background: #f7f9fc; border: 0; color: #718096; font-size: .66rem; font-weight: 750; letter-spacing: .045em; padding: .8rem .9rem; text-transform: uppercase; white-space: nowrap; }
    .audit-table tbody td { border-color: #edf1f6; color: #52616f; font-size: .78rem; padding: .85rem .9rem; vertical-align: middle; }
    .audit-table tbody tr:hover { background: #fbfcff; }
    .audit-table th:nth-child(1), .audit-table td:nth-child(1) { width: 16%; }
    .audit-table th:nth-child(2), .audit-table td:nth-child(2) { width: 23%; }
    .audit-table th:nth-child(3), .audit-table td:nth-child(3) { width: 15%; }
    .audit-table th:nth-child(4), .audit-table td:nth-child(4) { width: 25%; }
    .audit-table th:nth-child(5), .audit-table td:nth-child(5) { width: 13%; }
    .audit-table th:nth-child(6), .audit-table td:nth-child(6) { width: 8%; text-align: right; }
    .audit-date { color: #3f5068; display: block; font-weight: 650; white-space: nowrap; }
    .audit-time { color: #8795a8; display: block; font-size: .69rem; margin-top: .15rem; white-space: nowrap; }
    .audit-primary { color: #344563; display: block; font-weight: 700; overflow-wrap: anywhere; }
    .audit-secondary { color: #8492a6; display: block; font-size: .69rem; line-height: 1.35; margin-top: .15rem; overflow-wrap: anywhere; }
    .audit-technical { color: #45566d; font-family: SFMono-Regular, Consolas, "Liberation Mono", monospace; font-size: .72rem; }
    .audit-action-badge { align-items: center; border-radius: 14px; display: inline-flex; font-size: .64rem; font-weight: 750; line-height: 1.2; padding: .34rem .62rem; }
    .audit-action-info { background: #e8f2ff; color: #2764a8; }
    .audit-action-purple { background: #f0ebff; color: #6646ad; }
    .audit-action-success { background: #e7f7ef; color: #187a56; }
    .audit-action-warning { background: #fff3dc; color: #986000; }
    .audit-action-danger { background: #ffebee; color: #b33e4b; }
    .audit-action-neutral { background: #edf2f8; color: #52657d; }
    .audit-view-button { align-items: center; background: #fff; border: 1px solid #d5dde8; border-radius: 7px; color: #5746af; display: inline-flex; font-size: .72rem; font-weight: 700; gap: .3rem; min-height: 34px; padding: .35rem .65rem; }
    .audit-view-button:hover, .audit-view-button:focus { background: #f5f3ff; border-color: #a69bdb; color: #4f3da5; outline: 0; }
    .audit-empty { color: #8492a6; padding: 2.5rem 1rem; text-align: center; }
    .audit-empty-icon { align-items: center; background: #f1effb; border-radius: 50%; color: #7667c2; display: flex; font-size: 1.35rem; height: 52px; justify-content: center; margin: 0 auto .7rem; width: 52px; }
    .audit-empty strong { color: #405169; display: block; font-size: .9rem; margin-bottom: .25rem; }
    .audit-empty p { font-size: .76rem; margin: 0 auto .8rem; max-width: 480px; }
    .audit-footer { align-items: center; background: #fff; border-top: 1px solid var(--audit-border); display: flex; gap: 1rem; justify-content: space-between; padding: .85rem 1rem; }
    .audit-pagination-summary { color: #7b8a9e; font-size: .72rem; }
    .audit-pagination-summary strong { color: #42526b; font-weight: 650; }
    .audit-footer nav { margin-left: auto; }
    .audit-footer .pagination { margin: 0; }
    .audit-footer .page-link { align-items: center; border-color: #dfe5ed; color: #596b83; display: flex; font-size: .72rem; justify-content: center; min-height: 34px; min-width: 34px; }
    .audit-footer .page-item.active .page-link { background: var(--audit-purple); border-color: var(--audit-purple); }
    .audit-details-modal .modal-dialog { margin: 0 0 0 auto; max-width: 470px; min-height: 100%; }
    .audit-details-modal .modal-content { border: 0; border-radius: 0; min-height: 100vh; }
    .audit-details-modal .modal-header { border-bottom: 1px solid var(--audit-border); padding: 1rem 1.2rem; }
    .audit-details-modal .modal-title { color: #253858; font-size: 1rem; font-weight: 700; }
    .audit-details-modal .modal-body { padding: 1.15rem 1.2rem; }
    .audit-details-modal .modal-footer { border-top: 1px solid var(--audit-border); padding: .8rem 1.2rem; }
    .audit-detail-grid { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr; margin-top: 1.1rem; }
    .audit-detail-item-wide { grid-column: 1 / -1; }
    .audit-detail-label { color: #8795a8; display: block; font-size: .65rem; font-weight: 750; letter-spacing: .04em; margin-bottom: .2rem; text-transform: uppercase; }
    .audit-detail-value { color: #344563; font-size: .8rem; font-weight: 650; line-height: 1.45; overflow-wrap: anywhere; }
    .audit-detail-note { background: #f6f8fc; border: 1px solid #e1e7f0; border-radius: 8px; color: #64748b; font-size: .72rem; line-height: 1.5; margin-top: 1.15rem; padding: .7rem .8rem; }
    .audit-close-button { background: #fff; border: 1px solid #d5dde8; border-radius: 7px; color: #52616f; font-size: .76rem; font-weight: 650; padding: .45rem .9rem; }
    [data-custom-dates][hidden] { display: none !important; }
    @media (max-width: 1199px) {
        .audit-filter-grid { grid-template-columns: minmax(240px, 1fr) repeat(2, minmax(150px, .55fr)); }
        .audit-filter-grid .audit-field:last-child { grid-column: 2 / 3; }
        .audit-filter-grid-secondary { grid-template-columns: repeat(3, minmax(145px, 1fr)); }
        .audit-filter-actions { grid-column: 1 / -1; }
    }
    @media (max-width: 767px) {
        .audit-heading { display: block; }
        .audit-filter-grid, .audit-filter-grid-secondary { grid-template-columns: 1fr; }
        .audit-filter-grid .audit-field:last-child, .audit-filter-actions { grid-column: auto; }
        .audit-filter-actions { justify-content: stretch; }
        .audit-filter-button { flex: 1; }
        .audit-table, .audit-table tbody, .audit-table tr, .audit-table td { display: block; width: 100% !important; }
        .audit-table thead { display: none; }
        .audit-table tbody tr { border-bottom: 1px solid var(--audit-border); padding: .65rem 0; }
        .audit-table tbody tr:last-child { border-bottom: 0; }
        .audit-table tbody td { align-items: flex-start; border: 0; display: flex; gap: .75rem; padding: .38rem 1rem; text-align: left !important; }
        .audit-table tbody td::before { color: #8a99ac; content: attr(data-label); flex: 0 0 92px; font-size: .67rem; font-weight: 700; letter-spacing: .03em; padding-top: .2rem; text-transform: uppercase; }
        .audit-table tbody td:last-child { padding-top: .65rem; }
        .audit-table tbody td:last-child .audit-view-button { flex: 1; justify-content: center; min-height: 40px; }
        .audit-footer { align-items: flex-start; flex-direction: column; }
        .audit-footer nav { margin-left: 0; max-width: 100%; overflow-x: auto; }
        .audit-details-modal .modal-dialog { max-width: 100%; width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="audit-page">
    <header class="audit-heading" aria-labelledby="audit-page-title">
        <div>
            <h2 id="audit-page-title">General Audit Logs</h2>
            <p>Review administrative and system activity across SHIELD.</p>
        </div>
    </header>

    <div class="audit-notice" role="note">
        <i class="mdi mdi-shield-lock-outline" aria-hidden="true"></i>
        <span>Technical activity metadata only. Sensitive previous and new values are not displayed.</span>
    </div>

    @if($errors->any())
        <div class="audit-errors" role="alert">Please correct the invalid audit filter values and try again.</div>
    @endif

    <section class="card audit-card" aria-labelledby="audit-activity-title">
        <form method="GET" action="{{ route('super_admin.audit-logs.index') }}" class="audit-filter-bar" role="search">
            <div class="audit-filter-grid">
                <div class="audit-field">
                    <label for="audit-search" class="audit-label">Search</label>
                    <div class="audit-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input id="audit-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="form-control" placeholder="Search actor, entity type, record ID, or event ID" autocomplete="off"></div>
                </div>
                <div class="audit-field">
                    <label for="audit-action" class="audit-label">Action</label>
                    <select id="audit-action" name="action" class="form-control"><option value="">All actions</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>@endforeach</select>
                </div>
                <div class="audit-field">
                    <label for="audit-module" class="audit-label">Module</label>
                    <select id="audit-module" name="module" class="form-control"><option value="">All modules</option>@foreach($modules as $entityType => $label)<option value="{{ $entityType }}" @selected(($filters['module'] ?? '') === $entityType)>{{ $label }}</option>@endforeach</select>
                </div>
                <div class="audit-field">
                    <label for="audit-date-range" class="audit-label">Date range</label>
                    <select id="audit-date-range" name="date_range" class="form-control" data-date-range>
                        <option value="all" @selected(($filters['date_range'] ?? 'all') === 'all')>All dates</option>
                        <option value="today" @selected(($filters['date_range'] ?? '') === 'today')>Today</option>
                        <option value="last_7_days" @selected(($filters['date_range'] ?? '') === 'last_7_days')>Last 7 days</option>
                        <option value="last_30_days" @selected(($filters['date_range'] ?? '') === 'last_30_days')>Last 30 days</option>
                        <option value="this_month" @selected(($filters['date_range'] ?? '') === 'this_month')>This month</option>
                        <option value="custom" @selected(($filters['date_range'] ?? '') === 'custom')>Custom range</option>
                    </select>
                </div>
            </div>
            <div class="audit-filter-grid-secondary">
                <div class="audit-field" data-custom-dates @if(($filters['date_range'] ?? 'all') !== 'custom') hidden @endif>
                    <label for="audit-date-from" class="audit-label">From</label>
                    <input id="audit-date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control" data-date-input>
                </div>
                <div class="audit-field" data-custom-dates @if(($filters['date_range'] ?? 'all') !== 'custom') hidden @endif>
                    <label for="audit-date-to" class="audit-label">To</label>
                    <input id="audit-date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control" data-date-input>
                </div>
                <div class="audit-field">
                    <label for="audit-role" class="audit-label">Actor role</label>
                    <select id="audit-role" name="role" class="form-control"><option value="">All roles</option>@foreach($roles as $role => $label)<option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $label }}</option>@endforeach</select>
                </div>
                <div class="audit-field">
                    <label for="audit-sort" class="audit-label">Sort</label>
                    <select id="audit-sort" name="sort" class="form-control"><option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest first</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest first</option></select>
                </div>
                <div class="audit-field">
                    <label for="audit-per-page" class="audit-label">Per page</label>
                    <select id="audit-per-page" name="per_page" class="form-control"><option value="25" @selected((int) ($filters['per_page'] ?? 25) === 25)>25 events</option><option value="50" @selected((int) ($filters['per_page'] ?? 25) === 50)>50 events</option><option value="100" @selected((int) ($filters['per_page'] ?? 25) === 100)>100 events</option></select>
                </div>
                <div class="audit-filter-actions">
                    @if($hasActiveFilters)<a href="{{ route('super_admin.audit-logs.index') }}" class="audit-clear-button"><i class="mdi mdi-close" aria-hidden="true"></i>Clear filters</a>@endif
                    <button type="submit" class="audit-filter-button"><i class="mdi mdi-filter-outline" aria-hidden="true"></i>Apply filters</button>
                </div>
            </div>
        </form>

        <div class="audit-section-heading">
            <h3 id="audit-activity-title">Audit Activity</h3>
            <div class="audit-result-count"><strong>{{ number_format($logs->total()) }}</strong> {{ Str::plural('result', $logs->total()) }}</div>
        </div>

        @if($logs->isNotEmpty())
            <div class="table-responsive">
                <table class="table audit-table">
                    <caption class="sr-only">Filtered SHIELD technical audit activity</caption>
                    <thead><tr><th>Date &amp; time</th><th>Actor</th><th>Action</th><th>Module / affected record</th><th>IP address</th><th>Details</th></tr></thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php($displayedAt = $log->created_at?->timezone(config('app.display_timezone')))
                            <tr>
                                <td data-label="Date & time"><time datetime="{{ $log->created_at?->toIso8601String() }}"><span class="audit-date">{{ $displayedAt?->format('M d, Y') }}</span><span class="audit-time">{{ $displayedAt?->format('h:i:s A') }}</span></time></td>
                                <td data-label="Actor"><span class="audit-primary">{{ $log->user?->name ?? 'System or unavailable actor' }}</span><span class="audit-secondary">{{ $log->actorRoleLabel() }}@if($log->actorContext()) · {{ $log->actorContext() }}@endif</span></td>
                                <td data-label="Action"><x-audit-action-badge :action="$log->action" /></td>
                                <td data-label="Module / record"><span class="audit-primary">{{ $log->moduleLabel() }}</span><span class="audit-secondary">{{ $log->entityLabel() }} · #{{ $log->entity_id }}</span></td>
                                <td data-label="IP address"><span class="audit-technical">{{ $log->ip_address ?: '—' }}</span></td>
                                <td data-label="Details"><button type="button" class="audit-view-button" data-bs-toggle="modal" data-bs-target="#audit-details-{{ $log->id }}" aria-label="View details for {{ $log->eventReference() }}"><i class="mdi mdi-eye-outline" aria-hidden="true"></i>View</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="audit-empty">
                <div class="audit-empty-icon"><i class="mdi mdi-clipboard-text-clock-outline" aria-hidden="true"></i></div>
                <strong>{{ $hasActiveFilters ? 'No audit activity found' : 'No audit activity recorded yet' }}</strong>
                @if($hasActiveFilters)
                    <p>No activity matches your current filters. Try changing the date range, action, module, actor role, or search criteria.</p>
                    <a href="{{ route('super_admin.audit-logs.index') }}" class="audit-clear-button">Clear filters</a>
                @else
                    <p>Technical audit events will appear here when recorded by the system.</p>
                @endif
            </div>
        @endif

        @if($logs->isNotEmpty())
            <footer class="audit-footer">
                <div class="audit-pagination-summary">Showing <strong>{{ number_format($logs->firstItem()) }}–{{ number_format($logs->lastItem()) }}</strong> of <strong>{{ number_format($logs->total()) }}</strong> events</div>
                @if($logs->hasPages()){{ $logs->onEachSide(1)->links() }}@endif
            </footer>
        @endif
    </section>
</div>

@foreach($logs as $log)
    @php($displayedAt = $log->created_at?->timezone(config('app.display_timezone')))
    <div class="modal fade audit-details-modal" id="audit-details-{{ $log->id }}" tabindex="-1" role="dialog" aria-labelledby="audit-details-title-{{ $log->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header"><h4 class="modal-title" id="audit-details-title-{{ $log->id }}">Audit Event Details</h4><button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body">
                    <x-audit-action-badge :action="$log->action" />
                    <dl class="audit-detail-grid">
                        <div><dt class="audit-detail-label">Event ID</dt><dd class="audit-detail-value audit-technical">{{ $log->eventReference() }}</dd></div>
                        <div><dt class="audit-detail-label">Date &amp; Time</dt><dd class="audit-detail-value"><time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $displayedAt?->format('M d, Y · h:i:s A') }}</time></dd></div>
                        <div class="audit-detail-item-wide"><dt class="audit-detail-label">Actor</dt><dd class="audit-detail-value">{{ $log->user?->name ?? 'System or unavailable actor' }}@if($log->user)<span class="audit-secondary">Username: {{ $log->user->username }} · User ID: {{ $log->user_id }}</span>@endif</dd></div>
                        <div><dt class="audit-detail-label">Role</dt><dd class="audit-detail-value">{{ $log->actorRoleLabel() }}</dd></div>
                        <div><dt class="audit-detail-label">Assignment</dt><dd class="audit-detail-value">{{ $log->actorContext() ?: 'System-wide or unavailable' }}</dd></div>
                        <div><dt class="audit-detail-label">Module</dt><dd class="audit-detail-value">{{ $log->moduleLabel() }}</dd></div>
                        <div><dt class="audit-detail-label">Affected Record</dt><dd class="audit-detail-value">{{ $log->entityLabel() }} <span class="audit-technical">#{{ $log->entity_id }}</span></dd></div>
                        <div><dt class="audit-detail-label">IP Address</dt><dd class="audit-detail-value audit-technical">{{ $log->ip_address ?: 'Not recorded' }}</dd></div>
                        <div class="audit-detail-item-wide"><dt class="audit-detail-label">User Agent</dt><dd class="audit-detail-value">{{ $log->user_agent ? Str::limit($log->user_agent, 500) : 'Not recorded' }}</dd></div>
                    </dl>
                    <div class="audit-detail-note"><i class="mdi mdi-shield-lock-outline" aria-hidden="true"></i> Sensitive previous and new values are intentionally hidden. Raw request payloads and protected record contents are not available in this interface.</div>
                </div>
                <div class="modal-footer"><button type="button" class="audit-close-button" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var range = document.querySelector('[data-date-range]');
    var customFields = document.querySelectorAll('[data-custom-dates]');
    var dateInputs = document.querySelectorAll('[data-date-input]');
    if (!range) return;
    var syncCustomDates = function () {
        var custom = range.value === 'custom';
        customFields.forEach(function (field) { field.hidden = !custom; });
        dateInputs.forEach(function (input) { input.required = custom; });
    };
    range.addEventListener('change', syncCustomDates);
    syncCustomDates();
});
</script>
@endpush
