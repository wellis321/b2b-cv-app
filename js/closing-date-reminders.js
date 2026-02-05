/**
 * Job application closing date reminders – browser notifications.
 * Run on dashboard, content-editor, job-applications. Fetches upcoming closing dates
 * and shows a notification for each (once per day per job via localStorage).
 */
(function () {
    const STORAGE_PREFIX = 'closing_reminder_';
    const TODAY = new Date().toISOString().slice(0, 10);

    function storageKey(applicationId) {
        return STORAGE_PREFIX + applicationId + '_' + TODAY;
    }

    function wasNotifiedToday(applicationId) {
        try {
            return localStorage.getItem(storageKey(applicationId)) === '1';
        } catch (e) {
            return false;
        }
    }

    function markNotifiedToday(applicationId) {
        try {
            localStorage.setItem(storageKey(applicationId), '1');
        } catch (e) {}
    }

    function requestPermission() {
        if (!('Notification' in window)) return false;
        if (Notification.permission === 'granted') return true;
        if (Notification.permission === 'denied') return false;
        return null; // default – will request
    }

    function showNotification(reminder) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        var title = 'Closing date soon';
        var body = reminder.days_until === 0
            ? 'Today: ' + (reminder.job_title || 'Application') + ' at ' + (reminder.company_name || '')
            : (reminder.days_until === 1 ? 'Tomorrow' : reminder.days_until + ' days') + ': ' + (reminder.job_title || 'Application') + ' at ' + (reminder.company_name || '');
        try {
            var n = new Notification(title, { body: body, icon: '/static/favicon.ico' });
            n.onclick = function () {
                n.close();
                window.focus();
                if (window.location.pathname.indexOf('/job-applications') !== -1) {
                    return;
                }
                window.location.href = '/content-editor.php#jobs';
            };
        } catch (e) {}
    }

    function run() {
        var perm = requestPermission();
        if (perm === false) return;
        if (perm === null) {
            Notification.requestPermission().then(function (p) {
                if (p === 'granted') fetchAndNotify();
            });
            return;
        }
        fetchAndNotify();
    }

    function fetchAndNotify() {
        fetch('/api/upcoming-closing-dates.php', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.enabled || !data.reminders || !data.reminders.length) return;
                data.reminders.forEach(function (reminder) {
                    if (wasNotifiedToday(reminder.id)) return;
                    showNotification(reminder);
                    markNotifiedToday(reminder.id);
                });
            })
            .catch(function () {});
    }

    function shouldRun() {
        var path = window.location.pathname || '';
        return path === '/dashboard.php' || path === '/content-editor.php' || path.indexOf('/job-applications') !== -1;
    }

    if (!shouldRun()) return;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
