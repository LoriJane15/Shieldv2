@extends('layouts.skydash-v')
@section('title', 'Notifications')
@section('heading', 'Notifications')

@push('styles')
<style>
    .notifications-page{--primary:#2f6fed;--navy:#172b4d;--border:#e7ecf3}.notifications-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;overflow:hidden;padding:1.5rem;position:relative}.notifications-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:180px;position:absolute;right:-45px;top:-90px;width:180px}.notifications-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.notifications-hero p{font-size:.82rem;opacity:.85}.live-indicator{align-items:center;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);border-radius:16px;display:inline-flex;font-size:.7rem;font-weight:700;gap:.4rem;padding:.35rem .7rem;position:relative;z-index:1}.live-dot{background:#71efb6;border-radius:50%;box-shadow:0 0 0 4px rgba(113,239,182,.16);height:7px;width:7px}
    .notification-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045);overflow:hidden}.notification-toolbar{align-items:center;background:#fff;border-bottom:1px solid #edf1f6;display:flex;flex-wrap:wrap;gap:.8rem;justify-content:space-between;padding:1rem 1.25rem}.filter-group{background:#f1f4f8;border-radius:9px;display:inline-flex;padding:.25rem}.filter-button{background:transparent;border:0;border-radius:7px;color:#64748b;font-size:.74rem;font-weight:600;padding:.4rem .75rem}.filter-button.active{background:#fff;box-shadow:0 1px 4px rgba(23,43,77,.12);color:#2f6fed}.mark-all{background:transparent;border:0;color:#2f6fed;font-size:.74rem;font-weight:600;padding:.4rem}.mark-all:hover{color:#194fae;text-decoration:underline}.mark-all:disabled{color:#aeb8c7;text-decoration:none}
    .notification-list{background:#fff}.notification-form{margin:0}.notification-item{align-items:flex-start;background:#fff;border:0;border-bottom:1px solid #edf1f6;color:inherit;display:flex;gap:.9rem;padding:1rem 1.25rem;text-align:left;transition:background-color .15s;width:100%}.notification-item:hover{background:#f8fbff}.notification-form:last-child .notification-item{border-bottom:0}.notification-item.unread{background:#f4f8ff}.notification-item.unread:hover{background:#edf4ff}.notification-icon{align-items:center;background:#eaf1ff;border-radius:11px;color:#2f6fed;display:flex;flex:0 0 42px;font-size:1.15rem;height:42px;justify-content:center}.notification-content{min-width:0;flex:1}.notification-top{align-items:flex-start;display:flex;gap:.75rem;justify-content:space-between}.notification-case{color:#263a59;font-size:.84rem;font-weight:700}.notification-message{color:#64748b;font-size:.78rem;line-height:1.45;margin:.25rem 0 .4rem}.notification-meta{align-items:center;color:#94a3b8;display:flex;flex-wrap:wrap;font-size:.68rem;gap:.35rem .8rem}.unread-label{align-items:center;color:#2f6fed;display:inline-flex;font-weight:700;gap:.3rem}.unread-label::before{background:#2f6fed;border-radius:50%;content:'';height:6px;width:6px}.open-icon{color:#a3afbf;font-size:1.1rem;margin-top:.15rem}.notification-item:hover .open-icon{color:#2f6fed}.empty-state{color:#8492a6;padding:4rem 1rem;text-align:center}.empty-state i{color:#bdc8d7;display:block;font-size:2.6rem;margin-bottom:.6rem}.notification-pagination{border-top:1px solid #edf1f6;padding:1rem 1.25rem}.refresh-note{color:#8492a6;font-size:.68rem}.refresh-note i{color:#20a779}.new-alert{align-items:center;background:#e8f8f1;border:1px solid #bce5d3;border-radius:10px;color:#176b50;display:none;font-size:.76rem;justify-content:space-between;margin-bottom:1rem;padding:.7rem .9rem}.new-alert.show{display:flex}.new-alert button{background:transparent;border:0;color:#176b50;font-weight:700}
    @media(max-width:767px){.notifications-hero{padding:1.2rem}.notifications-hero h2{font-size:1.3rem}.live-indicator{margin-top:.8rem}.notification-toolbar{align-items:flex-start;flex-direction:column}.notification-item{padding:.9rem}.notification-icon{flex-basis:36px;height:36px}.notification-top{display:block}.open-icon{display:none}}
</style>
@endpush

@section('content')
@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $latestNotificationId = $notifications->first()?->id;
@endphp
<div class="notifications-page" data-notifications-page data-latest-notification-id="{{ $latestNotificationId }}">
    <section class="notifications-hero mb-4"><div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between position-relative" style="z-index:1"><div><h2 class="mb-1">Your Notifications</h2><p class="mb-0">Secure workflow updates and actions assigned to your account.</p></div><div class="live-indicator"><span class="live-dot"></span><span data-live-status>Live updates active</span></div></div></section>

    <div class="new-alert" data-new-alert role="status"><span><i class="mdi mdi-bell-ring-outline mr-1"></i>New workflow notifications are available.</span><button type="button" data-refresh-notifications>Refresh list</button></div>

    <section class="card notification-card" aria-label="Notification inbox">
        <div class="notification-toolbar">
            <div class="filter-group" role="group" aria-label="Filter notifications"><button type="button" class="filter-button active" data-notification-filter="all">All</button><button type="button" class="filter-button" data-notification-filter="unread">Unread <span data-unread-filter-count>({{ $unreadCount }})</span></button></div>
            <div class="d-flex align-items-center flex-wrap">
                <span class="refresh-note mr-3"><i class="mdi mdi-circle-medium"></i>Checked automatically every 15 seconds</span>
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="mark-all" type="submit" @disabled($unreadCount === 0) data-mark-all><i class="mdi mdi-check-all mr-1"></i>Mark all as read</button></form>
            </div>
        </div>

        <div class="notification-list" data-notification-list>
            @forelse($notifications as $notification)
                @php
                    $isUnread = $notification->read_at === null;
                    $routeName = $notification->data['route'] ?? '';
                    $icon = match ($routeName) {
                        'eclip_funding.cases.show' => 'mdi-bank-transfer',
                        'local_eclip.cases.show' => 'mdi-hand-coin-outline',
                        'eclip_assessor.cases.show' => 'mdi-clipboard-edit-outline',
                        default => 'mdi-file-document-check-outline',
                    };
                @endphp
                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="notification-form" data-notification-row data-notification-id="{{ $notification->id }}" data-read-state="{{ $isUnread ? 'unread' : 'read' }}">
                    @csrf
                    <button class="notification-item {{ $isUnread ? 'unread' : '' }}" type="submit" aria-label="Open notification for {{ $notification->data['case_number'] ?? 'E-CLIP case' }}">
                        <span class="notification-icon"><i class="mdi {{ $icon }}"></i></span>
                        <span class="notification-content">
                            <span class="notification-top"><span class="notification-case">{{ $notification->data['case_number'] ?? 'E-CLIP case' }}</span>@if($isUnread)<span class="unread-label">New</span>@endif</span>
                            <span class="notification-message">{{ $notification->data['message'] ?? 'An E-CLIP case was updated.' }}</span>
                            <span class="notification-meta"><span><i class="mdi mdi-clock-outline"></i> <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('M d, Y h:i A') }}">{{ $notification->created_at->diffForHumans() }}</time></span>@if(isset($notification->data['status']))<span><i class="mdi mdi-progress-check"></i> {{ str($notification->data['status'])->replace('_', ' ')->title() }}</span>@endif</span>
                        </span>
                        <i class="mdi mdi-chevron-right open-icon"></i>
                    </button>
                </form>
            @empty
                <div class="empty-state" data-empty-state><i class="mdi mdi-bell-check-outline"></i><strong class="d-block text-dark mb-1">You're all caught up</strong><span>No workflow notifications have been recorded for your account.</span></div>
            @endforelse
            <div class="empty-state d-none" data-filter-empty><i class="mdi mdi-email-check-outline"></i><strong class="d-block text-dark mb-1">No unread notifications</strong><span>All visible notifications have been read.</span></div>
        </div>
        @if($notifications->hasPages())<div class="notification-pagination">{{ $notifications->links() }}</div>@endif
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var page = document.querySelector('[data-notifications-page]');
    if (!page) return;
    var initialLatestId = page.dataset.latestNotificationId || null;
    var newAlert = page.querySelector('[data-new-alert]');
    var filterEmpty = page.querySelector('[data-filter-empty]');
    var markAll = page.querySelector('[data-mark-all]');

    page.querySelectorAll('[data-notification-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            page.querySelectorAll('[data-notification-filter]').forEach(function (item) { item.classList.remove('active'); });
            button.classList.add('active');
            var unreadOnly = button.dataset.notificationFilter === 'unread';
            var visible = 0;
            page.querySelectorAll('[data-notification-row]').forEach(function (row) {
                var show = !unreadOnly || row.dataset.readState === 'unread';
                row.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            filterEmpty.classList.toggle('d-none', !unreadOnly || visible > 0);
        });
    });

    document.addEventListener('notifications:updated', function (event) {
        var data = event.detail;
        var countLabel = page.querySelector('[data-unread-filter-count]');
        countLabel.textContent = '(' + Number(data.unread_count || 0) + ')';
        if (markAll) markAll.disabled = Number(data.unread_count || 0) === 0;
        var unreadIds = new Set(data.unread_ids || []);
        page.querySelectorAll('[data-notification-row]').forEach(function (row) {
            var isUnread = unreadIds.has(row.dataset.notificationId);
            row.dataset.readState = isUnread ? 'unread' : 'read';
            row.querySelector('.notification-item').classList.toggle('unread', isUnread);
            var label = row.querySelector('.unread-label');
            if (!isUnread && label) label.remove();
        });
        if (initialLatestId && data.latest_id && data.latest_id !== initialLatestId) newAlert.classList.add('show');
        if (!initialLatestId && data.latest_id) newAlert.classList.add('show');
    });

    page.querySelector('[data-refresh-notifications]').addEventListener('click', function () { window.location.reload(); });
})();
</script>
@endpush
