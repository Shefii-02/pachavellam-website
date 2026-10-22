{{-- Browser push: asks once, then registers this browser as one more device of the signed-in person. Only when Firebase web keys are set. --}}
@if (auth()->check() && config('firebase.web.vapid_key') && config('firebase.web.config.apiKey'))
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js"></script>
<script>
    (function () {
        if (!('serviceWorker' in navigator) || !('Notification' in window)) return;
        try {
            firebase.initializeApp(@json(config('firebase.web.config')));
            var messaging = firebase.messaging();
            function register() {
                navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function (reg) {
                    return messaging.getToken({ vapidKey: @json(config('firebase.web.vapid_key')), serviceWorkerRegistration: reg });
                }).then(function (token) {
                    if (!token) return;
                    fetch('/push/web-token', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' }, body: JSON.stringify({ token: token }) });
                }).catch(function () {});
            }
            window.pvWebPushInit = function () { if (Notification.permission === 'granted') register(); };   // the Profile > Settings "Turn on" button
            if (Notification.permission === 'granted') register();
            else if (Notification.permission === 'default') document.addEventListener('click', function once() { document.removeEventListener('click', once); Notification.requestPermission().then(function (p) { if (p === 'granted') register(); }); });
            messaging.onMessage(function (m) { var n = m.notification || {}; new Notification(n.title || 'Pachavellam', { body: n.body, icon: '/favicon.ico' }); });
        } catch (e) {}
    })();
</script>
@endif
