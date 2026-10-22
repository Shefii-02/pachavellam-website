{{-- The staff notification bell: unread count, the latest events, refreshed every minute. --}}
@php $bellId = 'bell' . substr(md5(uniqid('', true)), 0, 6); @endphp
<li class="nav-item dropdown ms-1 ms-lg-2" id="{{ $bellId }}">
    <a class="nav-notification btn icon-md p-0 position-relative" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
        <i class="bi bi-bell fs-5"></i>
        <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle bell-count" style="display:none;font-size:.62rem">0</span>
    </a>
    <div class="dropdown-menu dropdown-animation dropdown-menu-end shadow-lg py-0 p-0" style="width:340px;max-width:92vw">
        <div class="card bg-transparent border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom py-2">
                <h6 class="m-0">Notifications <span class="badge bg-danger bg-opacity-10 text-danger ms-1 bell-count-text">0 new</span></h6>
                <a class="small bell-read-all" href="#">Mark all read</a>
            </div>
            <div class="card-body p-0" style="max-height:380px;overflow:auto"><ul class="list-group list-group-flush list-unstyled bell-list"><li class="text-center text-muted small py-4">Loading…</li></ul></div>
            <div class="card-footer bg-transparent text-center py-2 border-top"><a href="{{ url('admin/notifications') }}" class="small">See all notifications</a></div>
        </div>
    </div>
</li>
<script>
(function () {
    var root = document.getElementById(@json($bellId)); if (!root) return;
    var feed = @json(url('admin/notifications/feed')), readUrl = @json(url('admin/notifications/read')), openBase = @json(url('admin/notifications')), csrf = @json(csrf_token());
    var count = root.querySelector('.bell-count'), text = root.querySelector('.bell-count-text'), list = root.querySelector('.bell-list');
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function render(d) {
        count.style.display = d.unread > 0 ? '' : 'none'; count.textContent = d.unread > 99 ? '99+' : d.unread; text.textContent = d.unread + ' new';
        if (!d.items.length) { list.innerHTML = '<li class="text-center text-muted small py-4">Nothing yet.</li>'; return; }
        list.innerHTML = d.items.map(function (n) {
            return '<li><a href="' + openBase + '/' + n.id + '/open" class="list-group-item list-group-item-action border-0 d-flex gap-2 py-2 ' + (n.unread ? 'bg-primary bg-opacity-10' : '') + '">' +
                '<span class="text-' + esc(n.color) + ' fs-5"><i class="bi ' + esc(n.icon) + '"></i></span><span class="flex-grow-1"><span class="d-block small fw-semibold">' + esc(n.title) + '</span>' +
                (n.body ? '<span class="d-block small text-muted">' + esc(n.body) + '</span>' : '') + '<span class="d-block text-muted" style="font-size:.7rem">' + esc(n.when) + '</span></span></a></li>';
        }).join('');
    }
    function load() { fetch(feed, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.ok ? r.json() : null; }).then(function (d) { if (d) render(d); }).catch(function () {}); }
    root.querySelector('.bell-read-all').addEventListener('click', function (e) {
        e.preventDefault();
        fetch(readUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin' }).then(load);
    });
    load(); setInterval(load, 60000);
})();
</script>
