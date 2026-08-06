<script>
(function () {
    var link = document.querySelector('[data-notification-link]');
    if (!link) return;

    var badge = link.querySelector('[data-notification-badge]');
    var statusUrl = link.dataset.notificationStatusUrl;
    var latestId = null;

    function updateStatus(data) {
        var count = Number(data.unread_count || 0);
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.toggle('d-none', count === 0);
        link.setAttribute('aria-label', count ? 'Notifications, ' + count + ' unread' : 'Notifications, none unread');
        document.dispatchEvent(new CustomEvent('notifications:updated', { detail: data }));
        latestId = data.latest_id || null;
    }

    function checkNotifications() {
        if (document.hidden) return;
        fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (response) { if (!response.ok) throw new Error('Notification check failed'); return response.json(); })
            .then(updateStatus)
            .catch(function () { /* Keep the last confirmed count during temporary network errors. */ });
    }

    checkNotifications();
    window.setInterval(checkNotifications, 15000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) checkNotifications(); });
})();
</script>
