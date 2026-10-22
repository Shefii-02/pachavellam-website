@extends('admin.main-layout')
@section('main')
@php $base = url('admin/class-chats/' . $source . '/' . $v->id); @endphp
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <a href="{{ url('admin/class-chats') }}" class="small"><i class="bi bi-arrow-left"></i> Video chats</a>
            <h4 class="mb-0">{{ $v->title }}</h4>
            @if ($v->uid)<a class="small" target="_blank" href="{{ url('player/' . $v->uid) }}"><i class="bi bi-play-circle me-1"></i>Open the player</a>@endif
        </div>
        <form action="{{ $base . '/mode' }}" method="POST" class="d-flex gap-2 align-items-center">@csrf
            <label class="small mb-0">Chat</label>
            <select name="chat_mode" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="off" {{ ($v->chat_mode ?? 'off') === 'off' ? 'selected' : '' }}>Off</option>
                @if ($source === 'item')<option value="live" {{ $v->chat_mode === 'live' ? 'selected' : '' }}>On - only during the live class</option>@endif
                <option value="always" {{ ($v->chat_mode ?? '') === 'always' ? 'selected' : '' }}>On - always</option>
            </select>
        </form>
    </div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif

    <div class="card bg-transparent border rounded-3" style="max-width:820px">
        <div id="cList" class="p-3 d-flex flex-column gap-2" style="height:60vh;overflow-y:auto"><div class="text-muted text-center my-auto">Loading…</div></div>
        <form id="cForm" class="d-flex gap-2 p-2 border-top" autocomplete="off">
            <input id="cInp" class="form-control" maxlength="400" placeholder="Reply as {{ auth()->user()->name }} (staff)…">
            <button class="btn btn-success mb-0"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
</div>
<style>.cm{max-width:80%;padding:6px 10px;border-radius:12px;background:rgba(127,127,127,.15);word-break:break-word}.cm.me{align-self:flex-end;background:rgba(10,143,91,.25)}.cm .n{font-size:.75rem;font-weight:700;color:#0a8f5b}.cm .x{font-size:.7rem;opacity:.6}.cm .a{opacity:0;margin-left:6px;cursor:pointer}.cm:hover .a{opacity:1}@media(hover:none){.cm .a{opacity:.7}}</style>
<script>
(function () {
    var base = @json($base), csrf = @json(csrf_token()), list = document.getElementById('cList'), last = 0, busy = false;
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function post(url, body) { return fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin', body: JSON.stringify(body || {}) }); }
    function draw(j) {
        (j.deleted || []).forEach(function (id) { var e = document.getElementById('m' + id); if (e) e.remove(); });
        if (!j.messages.length) { if (!last) list.innerHTML = '<div class="text-muted text-center my-auto">No messages yet.</div>'; return; }
        if (!last) list.innerHTML = '';
        var stick = !last || list.scrollTop + list.clientHeight >= list.scrollHeight - 60;
        j.messages.forEach(function (m) {
            last = Math.max(last, m.id);
            list.insertAdjacentHTML('beforeend', '<div class="cm' + (m.mine ? ' me' : '') + '" id="m' + m.id + '"><div class="n">' + esc(m.mine ? 'You (staff)' : m.name) + (m.mobile && !m.mine ? ' <span class="text-muted fw-normal">' + esc(m.mobile) + '</span>' : '') + '</div>' + esc(m.body) +
                '<div class="x">' + m.t + '<span class="a text-danger" title="Delete message" data-del="' + m.id + '"><i class="bi bi-trash"></i></span>' + (m.mine ? '' : '<span class="a text-danger" title="Delete all messages of this student" data-user="' + m.uid + '"><i class="bi bi-person-x"></i></span>') + '</div></div>');
        });
        if (stick) list.scrollTop = list.scrollHeight;
    }
    function poll() { if (busy || document.hidden) return; busy = true; fetch(base + '/messages?after=' + last, { headers: { Accept: 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(draw).catch(function () {}).finally(function () { busy = false; }); }
    poll(); setInterval(poll, 3000);
    list.addEventListener('click', function (e) {
        var d = e.target.closest('[data-del]'), u = e.target.closest('[data-user]');
        if (d && confirm('Delete this message?')) post(base + '/messages/' + d.dataset.del + '/delete').then(function () { var el = document.getElementById('m' + d.dataset.del); if (el) el.remove(); });
        if (u && confirm('Delete every message of this student in this chat?')) post(base + '/users/' + u.dataset.user + '/delete').then(function () { location.reload(); });
    });
    document.getElementById('cForm').addEventListener('submit', function (e) {
        e.preventDefault(); var i = document.getElementById('cInp'), v = i.value.trim(); if (!v) return; i.value = '';
        post(base + '/send', { body: v }).then(function () { poll(); });
    });
})();
</script>
@endsection
