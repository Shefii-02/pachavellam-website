@extends('admin.main-layout')
@section('main')
<style>
    .wa { --wa-green:#00a884; --wa-bubble-me:#d9fdd3; --wa-bubble-in:#fff; --wa-bg:#efeae2; display:flex; height: calc(100vh - 150px); min-height: 480px; border:1px solid var(--bs-border-color); border-radius:.75rem; overflow:hidden; background: var(--bs-body-bg); position:relative; }
    [data-bs-theme="dark"] .wa { --wa-bubble-me:#005c4b; --wa-bubble-in:#202c33; --wa-bg:#0b141a; }
    .wa-side { width:380px; max-width:42%; border-right:1px solid var(--bs-border-color); display:flex; flex-direction:column; background: var(--bs-body-bg); }
    .wa-main { flex:1; display:flex; flex-direction:column; min-width:0; position:relative; }
    .wa-top { display:flex; align-items:center; gap:8px; padding:8px 10px; background: var(--bs-tertiary-bg); border-bottom:1px solid var(--bs-border-color); }
    .wa-tabs { display:flex; gap:6px; padding:6px 8px; overflow-x:auto; border-bottom:1px solid var(--bs-border-color-translucent); }
    .wa-tab { border:0; background: var(--bs-tertiary-bg); color: var(--bs-body-color); border-radius:20px; padding:3px 12px; font-size:.82rem; white-space:nowrap; cursor:pointer; }
    .wa-tab.active { background: rgba(0,168,132,.18); color: var(--wa-green); font-weight:600; }
    .wa-tab .n { background: var(--wa-green); color:#fff; border-radius:10px; font-size:.68rem; padding:0 6px; margin-left:4px; }
    .wa-list { overflow:auto; flex:1; }
    .wa-item { display:flex; gap:12px; padding:9px 12px; cursor:pointer; border-bottom:1px solid var(--bs-border-color-translucent); align-items:center; }
    .wa-item:hover { background: var(--bs-tertiary-bg); } .wa-item.active { background: rgba(0,168,132,.12); }
    .wa-av { width:46px; height:46px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; flex:none; position:relative; background:#6b7c93; font-size:1.05rem; }
    .wa-av.sm { width:34px; height:34px; font-size:.85rem; }
    .wa-av .dot { position:absolute; right:0; bottom:0; width:12px; height:12px; border-radius:50%; background:#25d366; border:2px solid var(--bs-body-bg); }
    .wa-name { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .wa-prev { font-size:.84rem; color: var(--bs-secondary-color); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .wa-time { font-size:.72rem; color: var(--bs-secondary-color); } .wa-time.unread { color: var(--wa-green); font-weight:600; }
    .wa-badge { background: var(--wa-green); color:#fff; border-radius:12px; font-size:.7rem; min-width:20px; text-align:center; padding:1px 6px; }
    .wa-src { font-size:.62rem; border-radius:4px; padding:0 4px; vertical-align:middle; margin-left:4px; color:#fff; }
    .wa-src.student { background:#d97706; } .wa-src.staff { background:#2563eb; } .wa-src.group { background:#059669; }
    .wa-msgs { flex:1; overflow:auto; padding:14px 6%; background: var(--wa-bg); }
    .wa-msgs.drag { outline:3px dashed var(--wa-green); outline-offset:-8px; }
    .wa-row { display:flex; margin-bottom:2px; position:relative; } .wa-row.me { justify-content:flex-end; }
    .wa-b { max-width:70%; min-width:70px; padding:5px 8px 4px; border-radius:8px; background: var(--wa-bubble-in); box-shadow:0 1px .5px rgba(11,20,26,.13); word-wrap:break-word; overflow-wrap:anywhere; position:relative; }
    .wa-row.me .wa-b { background: var(--wa-bubble-me); }
    .wa-row.first .wa-b { margin-top:6px; }
    .wa-who { font-size:.76rem; font-weight:600; color:#a14ccf; }
    .wa-text { white-space:pre-wrap; }
    .wa-foot { float:right; margin:6px 0 0 10px; font-size:.68rem; color: var(--bs-secondary-color); display:flex; gap:3px; align-items:center; line-height:1; }
    .wa-tick { font-size:.95rem; } .wa-tick.read { color:#53bdeb; }
    .wa-chev { position:absolute; top:2px; right:4px; opacity:0; cursor:pointer; background: inherit; border-radius:50%; width:20px; text-align:center; line-height:20px; font-size:.8rem; color: var(--bs-secondary-color); }
    .wa-b:hover .wa-chev { opacity:1; }
    .wa-quote { border-left:4px solid var(--wa-green); background: rgba(127,127,127,.12); border-radius:5px; padding:3px 8px; margin-bottom:4px; font-size:.82rem; cursor:pointer; }
    .wa-quote b { color: var(--wa-green); display:block; font-size:.76rem; }
    .wa-day { text-align:center; margin:10px 0; } .wa-day span { background: var(--bs-body-bg); border-radius:7px; padding:3px 10px; font-size:.75rem; color: var(--bs-secondary-color); box-shadow:0 1px .5px rgba(11,20,26,.13); }
    .wa-sys { text-align:center; margin:6px 0; } .wa-sys span { background: rgba(255,243,196,.9); color:#54656f; border-radius:7px; padding:3px 10px; font-size:.75rem; }
    .wa-fwd { font-size:.74rem; font-style:italic; color: var(--bs-secondary-color); }
    .wa-reacts { margin-top:2px; } .wa-react { display:inline-block; background: var(--bs-body-bg); border:1px solid var(--bs-border-color); border-radius:12px; padding:0 6px; font-size:.78rem; margin-right:3px; cursor:pointer; }
    .wa-react.mine { border-color: var(--wa-green); }
    .wa-img { max-width:280px; max-height:300px; border-radius:6px; display:block; cursor:zoom-in; margin-bottom:3px; }
    .wa-file { display:flex; align-items:center; gap:8px; background: rgba(127,127,127,.13); border-radius:6px; padding:8px 10px; margin-bottom:3px; text-decoration:none; color:inherit; }
    .wa-star { color:#f5a623; }
    .wa-hl { animation: wahl 1.6s ease; } @keyframes wahl { 0%,60% { background: rgba(255,214,0,.35); } 100% { background:transparent; } }
    .wa-compose { background: var(--bs-tertiary-bg); padding:8px 10px; display:flex; gap:8px; align-items:flex-end; border-top:1px solid var(--bs-border-color); position:relative; }
    .wa-compose textarea { resize:none; max-height:130px; border-radius:20px; padding:8px 14px; }
    .wa-icon { border:0; background:transparent; font-size:1.3rem; color: var(--bs-secondary-color); padding:4px 6px; cursor:pointer; } .wa-icon:hover { color: var(--wa-green); }
    .wa-send { background: var(--wa-green); color:#fff; border:0; border-radius:50%; width:42px; height:42px; flex:none; }
    .wa-menu { position:fixed; z-index:2000; background: var(--bs-body-bg); border:1px solid var(--bs-border-color); border-radius:10px; box-shadow:0 6px 24px rgba(0,0,0,.2); padding:4px 0; min-width:190px; display:none; }
    .wa-menu a { display:block; padding:6px 16px; color: var(--bs-body-color); text-decoration:none; font-size:.9rem; cursor:pointer; } .wa-menu a:hover { background: var(--bs-tertiary-bg); }
    .wa-menu .emo { display:flex; justify-content:space-around; padding:4px 8px; border-bottom:1px solid var(--bs-border-color); } .wa-menu .emo span { font-size:1.3rem; cursor:pointer; padding:0 3px; }
    .wa-pop { position:absolute; bottom:62px; left:10px; background: var(--bs-body-bg); border:1px solid var(--bs-border-color); border-radius:10px; box-shadow:0 6px 24px rgba(0,0,0,.2); padding:6px; display:none; z-index:50; }
    .wa-emojis { width:300px; max-height:200px; overflow:auto; font-size:1.35rem; } .wa-emojis span { cursor:pointer; padding:2px 4px; display:inline-block; }
    .wa-quick a { display:block; padding:5px 10px; cursor:pointer; color: var(--bs-body-color); text-decoration:none; font-size:.88rem; } .wa-quick a:hover { background: var(--bs-tertiary-bg); }
    .wa-bar { padding:6px 12px; background: var(--bs-body-bg); border-bottom:1px solid var(--bs-border-color); display:none; }
    .wa-pinned { cursor:pointer; background: var(--bs-body-bg); border-bottom:1px solid var(--bs-border-color); padding:4px 12px; font-size:.84rem; display:none; }
    .wa-ro { text-align:center; font-size:.85rem; padding:10px; background: var(--bs-tertiary-bg); border-top:1px solid var(--bs-border-color); color: var(--bs-secondary-color); }
    .wa-empty { flex:1; display:flex; align-items:center; justify-content:center; text-align:center; color: var(--bs-secondary-color); background: var(--bs-tertiary-bg); }
    .wa-rec { color:#dc3545; animation: wablink 1s infinite; } @keyframes wablink { 50% { opacity:.3; } }
    @media (max-width: 768px) { .wa-side { width:100%; max-width:100%; } .wa.chat-open .wa-side { display:none; } .wa:not(.chat-open) .wa-main { display:none; } .wa-msgs { padding:10px 8px; } .wa-b { max-width:88%; } }
</style>

<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div><h3 class="mb-0">Chats</h3><div class="small text-muted">@if ($me['is_admin'])As an Admin you can see every staff and student conversation, and message anyone. @else Chats with your colleagues. @endif<span id="connState" class="badge bg-secondary ms-1">connecting…</span></div></div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary mb-0" id="btnStarred"><i class="bi bi-star me-1"></i>Starred</button>
            <button class="btn btn-sm btn-outline-secondary mb-0" id="btnQuick"><i class="bi bi-lightning me-1"></i>Quick replies</button>
        </div>
    </div>

    <div class="wa" id="wa">
        {{-- left: list --}}
        <div class="wa-side">
            <div class="wa-top">
                <div class="wa-av sm" style="background:var(--wa-green)">{{ strtoupper(mb_substr($me['name'], 0, 1)) }}</div>
                <div class="flex-grow-1 fw-semibold text-truncate">{{ $me['name'] }}</div>
                <button class="wa-icon" id="btnNewChat" title="New chat"><i class="bi bi-chat-left-text"></i></button>
                <button class="wa-icon" id="btnNewGroup" title="New group"><i class="bi bi-people"></i></button>
            </div>
            <div class="p-2 pb-1"><input type="text" id="chatSearch" class="form-control form-control-sm rounded-pill" placeholder="Search chats, or type 2+ letters to search messages…"></div>
            <div class="wa-tabs" id="waTabs"></div>
            <div class="wa-list" id="chatList"><div class="text-center text-muted small py-4">Loading…</div></div>
        </div>

        {{-- right: conversation --}}
        <div class="wa-main">
            <div id="emptyState" class="wa-empty"><div><i class="bi bi-chat-dots display-3"></i><h5 class="mt-2">Chats</h5><div>Pick a conversation, or start a new chat.</div></div></div>
            <div id="chatPane" style="display:none;flex-direction:column;height:100%">
                <div class="wa-top">
                    <button class="wa-icon d-md-none" id="btnBack"><i class="bi bi-arrow-left"></i></button>
                    <div class="wa-av sm" id="paneAvatar">?</div>
                    <div class="flex-grow-1 min-w-0" id="paneHead" style="cursor:pointer"><div class="fw-semibold text-truncate" id="paneName"></div><div class="small text-muted text-truncate" id="paneSub"></div></div>
                    <span class="badge bg-warning text-dark d-none" id="oversightBadge"></span>
                    <button class="btn btn-sm btn-success mb-0 d-none" id="btnJoin">Join as admin</button>
                    <button class="wa-icon" id="btnSearchIn" title="Search in chat"><i class="bi bi-search"></i></button>
                    <button class="wa-icon" id="btnMenu" title="More"><i class="bi bi-three-dots-vertical"></i></button>
                </div>
                <div class="wa-bar" id="searchBar"><div class="d-flex gap-2"><input type="text" id="inSearch" class="form-control form-control-sm" placeholder="Search in this chat…"><button class="btn btn-sm btn-light mb-0" id="inSearchClose"><i class="bi bi-x-lg"></i></button></div><div id="inSearchRes" class="small mt-1" style="max-height:140px;overflow:auto"></div></div>
                <div class="wa-pinned" id="pinnedBar"></div>
                <div class="wa-msgs" id="msgs"></div>
                <div class="small text-muted px-3" id="typing" style="min-height:18px"></div>
                <div id="replyBar" class="px-3 py-1 border-top small justify-content-between" style="display:none"><span class="text-truncate" id="replyText"></span><a href="#" id="replyCancel" class="ms-2">cancel</a></div>
                <div id="fileChipBar" class="px-3 py-1 border-top small justify-content-between" style="display:none"><span id="fileChip"></span><a href="#" id="fileCancel" class="ms-2">remove</a></div>
                <div id="roNote" class="wa-ro" style="display:none"></div>
                <form id="composer" class="wa-compose">
                    <div class="wa-pop" id="emojiPop"><div class="wa-emojis" id="emojiGrid"></div></div>
                    <div class="wa-pop wa-quick" id="quickPop" style="min-width:260px;max-height:200px;overflow:auto"></div>
                    <button type="button" class="wa-icon" id="btnEmoji" title="Emoji"><i class="bi bi-emoji-smile"></i></button>
                    <label class="wa-icon mb-0" title="Attach a file"><i class="bi bi-paperclip"></i><input type="file" id="fileInput" class="d-none"></label>
                    <textarea id="msgInput" rows="1" class="form-control" placeholder="Type a message   ( / for quick replies · Enter to send )" maxlength="4000"></textarea>
                    <button type="button" class="wa-icon" id="btnMic" title="Record a voice note"><i class="bi bi-mic"></i></button>
                    <button class="wa-send" id="sendBtn" title="Send"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="wa-menu" id="waMenu"></div>

{{-- modals --}}
<div class="modal fade" id="mNew" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">New chat</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        @if ($me['is_admin'])
            <div class="d-flex gap-1 mb-2 flex-wrap" id="newTypes"><button class="wa-tab active" data-t="all">All</button><button class="wa-tab" data-t="staff">All staff</button><button class="wa-tab" data-t="Admin">Admin</button><button class="wa-tab" data-t="Admin Staff">Admin Staff</button><button class="wa-tab" data-t="Student">Students</button></div>
        @endif
        <input type="text" id="newChatQ" class="form-control mb-2" placeholder="Search by name, email or mobile…">
        <div class="list-group" id="newChatList" style="max-height:340px;overflow:auto"></div>
    </div>
</div></div></div>

<div class="modal fade" id="mGroup" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">New group</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="row g-2 mb-2"><div class="col-md-6"><label class="form-label small mb-0">Group name</label><input type="text" id="gName" class="form-control" maxlength="150"></div>
            <div class="col-md-6"><label class="form-label small mb-0">Description <span class="text-muted">(optional)</span></label><input type="text" id="gDesc" class="form-control" maxlength="500"></div></div>
        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="gOnlyAdmins"><label class="form-check-label small" for="gOnlyAdmins">Only group admins can send messages</label></div>
        <div class="alert alert-info py-2 small">You become the owner. Admin accounts are added automatically with every permission. Pick the members below; untick "can send" for read-only members.</div>
        <input type="text" id="gSearch" class="form-control form-control-sm mb-2" placeholder="Search staff…">
        <div id="gStaff" style="max-height:260px;overflow:auto" class="border rounded"></div>
        <div id="gError" class="text-danger small mt-2"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-success mb-0" id="gCreate">Create group</button></div>
</div></div></div>

<div class="modal fade" id="mMembers" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="mmTitle">Members</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div id="mmInfo" class="mb-3"></div>
        <div id="mmAdd" class="mb-3 d-none"><div class="input-group input-group-sm"><input type="text" id="mmAddQ" class="form-control" placeholder="Add a staff member — search by name…"></div><div class="list-group mt-1" id="mmAddList" style="max-height:180px;overflow:auto"></div></div>
        <div id="mmList"></div>
        <div id="mmError" class="text-danger small mt-2"></div>
    </div>
    <div class="modal-footer justify-content-between"><button class="btn btn-outline-danger btn-sm mb-0 d-none" id="mmLeave">Leave group</button><span></span></div>
</div></div></div>

<div class="modal fade" id="mForward" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Forward to…</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div id="fwList" style="max-height:340px;overflow:auto" class="border rounded"></div></div>
    <div class="modal-footer"><button class="btn btn-success mb-0" id="fwSend">Forward</button></div>
</div></div></div>

<div class="modal fade" id="mInfo" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="infoTitle">Message info</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="infoBody"></div>
</div></div></div>

<div class="modal fade" id="mStarred" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Starred messages</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="starBody"></div>
</div></div></div>

<div class="modal fade" id="mQuick" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Quick replies</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="small text-muted mb-2">Type <code>/shortcut</code> in the message box to insert a reply.</div>
        <div class="row g-2 mb-2"><div class="col-4"><input id="qrShort" class="form-control form-control-sm" placeholder="shortcut" maxlength="30"></div><div class="col-8"><input id="qrBody" class="form-control form-control-sm" placeholder="reply text" maxlength="2000"></div></div>
        @if ($me['is_admin'])<div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="qrShared"><label for="qrShared" class="form-check-label small">Share with all staff</label></div>@endif
        <button class="btn btn-sm btn-primary mb-2" id="qrSave">Save</button><div id="qrError" class="text-danger small"></div>
        <div id="qrList"></div>
    </div>
</div></div></div>

<div class="modal fade" id="mImg" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content bg-transparent border-0"><img id="imgBig" class="img-fluid rounded mx-auto" style="max-height:88vh"></div></div></div>

<form id="impersonateForm" method="POST" class="d-none">@csrf</form>
@endsection

@section('scripts')
<script src="{{ $chatServer }}/socket.io/socket.io.js"></script>
<script>
(function () {
    var SERVER = @json($chatServer), ME = @json($me), TOKEN = @json($token), TOKEN_URL = @json(url('admin/chat/token')), IMPERSONATE = @json(url('admin/impersonate')), CAN_IMPERSONATE = {{ $me['is_admin'] && !$viewingAs ? 'true' : 'false' }};
    var QS = new URLSearchParams(location.search);
    var TABS = ME.is_admin ? [['all', 'All chats'], ['staff', 'From staff'], ['student', 'From student'], ['groups', 'Group messages']] : [['mine', 'All'], ['direct', 'Direct'], ['groups', 'Groups'], ['unread', 'Unread'], ['archived', 'Archived']];
    var state = { tab: TABS[0][0], chats: [], chat: null, msgs: [], reply: null, file: null, typingTimer: null, search: '', online: {}, quick: [], menuMsg: null, hasMore: true, loadingOlder: false };
    var $ = function (id) { return document.getElementById(id); };
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function initial(n) { return (n || '?').trim().charAt(0).toUpperCase(); }
    var COLORS = ['#6b7c93', '#0f766e', '#7c3aed', '#c2410c', '#be185d', '#1d4ed8', '#4d7c0f', '#b45309'];
    function colorOf(k) { var h = 0, s = String(k); for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0; return COLORS[h % COLORS.length]; }
    function clock(t) { return new Date(t).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }
    function when(t) { if (!t) return ''; var d = new Date(t), n = new Date(), y = new Date(); y.setDate(n.getDate() - 1); if (d.toDateString() === n.toDateString()) return clock(t); if (d.toDateString() === y.toDateString()) return 'Yesterday'; return d.toLocaleDateString([], { day: 'numeric', month: 'short' }); }
    function dayLabel(t) { var d = new Date(t), n = new Date(), y = new Date(); y.setDate(n.getDate() - 1); if (d.toDateString() === n.toDateString()) return 'Today'; if (d.toDateString() === y.toDateString()) return 'Yesterday'; return d.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
    function linkify(s) { return esc(s).replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>'); }
    function size(n) { return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; }
    function toast(m) { var t = document.createElement('div'); t.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-4 px-3 py-2 rounded bg-dark text-white small'; t.style.zIndex = 3000; t.textContent = m; document.body.appendChild(t); setTimeout(function () { t.remove(); }, 2600); }

    // ---------------------------------------------------------------- api
    function refreshToken() { return fetch(TOKEN_URL, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }).then(function (j) { TOKEN = j.token; return TOKEN; }); }
    setInterval(refreshToken, 40 * 60 * 1000);
    function api(method, path, body, form, retried) {
        var opts = { method: method, headers: { 'Authorization': 'Bearer ' + TOKEN } };
        if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        if (form) { opts.body = form; }
        return fetch(SERVER + '/api' + path, opts).then(function (r) {
            if (r.status === 401 && !retried) { return refreshToken().then(function () { return api(method, path, body, form, true); }); }
            return r.json().catch(function () { return {}; }).then(function (j) { if (!r.ok) { var e = new Error(j.message || 'Something went wrong'); e.status = r.status; throw e; } return j; });
        });
    }
    function isStudent(c) { return c && c.source === 'student'; }
    function base(c) { return isStudent(c) ? '/console/student-chats/' + c.id : '/chats/' + c.id; }
    function keyOf(c) { return (c.source || 'staff') + ':' + c.id; }

    // ---------------------------------------------------------------- tabs + list
    function renderTabs() {
        var counts = {};
        $('waTabs').innerHTML = TABS.map(function (t) { return '<button type="button" class="wa-tab ' + (state.tab === t[0] ? 'active' : '') + '" data-tab="' + t[0] + '">' + t[1] + '</button>'; }).join('');
    }
    $('waTabs').addEventListener('click', function (e) { var b = e.target.closest('[data-tab]'); if (!b) return; state.tab = b.dataset.tab; renderTabs(); loadChats(); });

    var searchHits = null;
    function loadChats() {
        var q = state.search, p;
        if (ME.is_admin) p = api('GET', '/console/chats?tab=' + state.tab + '&q=' + encodeURIComponent(q));
        else p = api('GET', '/chats?q=' + encodeURIComponent(q) + (state.tab === 'archived' ? '&archived=1' : '')).then(function (l) {
            l.forEach(function (c) { c.source = 'staff'; });
            if (state.tab === 'direct') l = l.filter(function (c) { return c.type === 'direct'; });
            if (state.tab === 'groups') l = l.filter(function (c) { return c.type === 'group'; });
            if (state.tab === 'unread') l = l.filter(function (c) { return c.unread > 0; });
            return l;
        });
        return p.then(function (list) { state.chats = list; renderList(); });
    }
    function avatarOf(c, cls) {
        var online = c.type === 'direct' && c.other_user_id && state.online[c.other_user_id];
        return '<div class="wa-av ' + (cls || '') + '" style="background:' + (c.type === 'group' ? '#059669' : colorOf(c.name)) + '">' + (c.type === 'group' ? '<i class="bi bi-people-fill"></i>' : esc(initial(c.name))) + (online ? '<span class="dot"></span>' : '') + '</div>';
    }
    function srcTag(c) { return ME.is_admin ? '<span class="wa-src ' + (isStudent(c) ? 'student' : 'staff') + '">' + (isStudent(c) ? (c.sub_type === 'course_group' ? 'course' : 'student') : 'staff') + '</span>' : ''; }
    function renderList() {
        var box = $('chatList'), cur = state.chat ? keyOf(state.chat) : null;
        var html = '';
        if (searchHits && searchHits.length) {
            html += '<div class="px-3 py-1 small text-muted bg-body-tertiary">Messages</div>' + searchHits.map(function (h) { return '<div class="wa-item" data-hit="' + h.chat_id + '" data-mid="' + h.id + '"><div class="flex-grow-1 min-w-0"><div class="wa-name">' + esc(h.type === 'direct' ? (h.sender_name || 'Chat') : h.chat_name) + '</div><div class="wa-prev">' + esc(h.sender_name || '') + ': ' + esc(h.body) + '</div></div><div class="wa-time">' + when(h.created_at) + '</div></div>'; }).join('') + '<div class="px-3 py-1 small text-muted bg-body-tertiary">Chats</div>';
        }
        if (!state.chats.length) html += '<div class="text-center text-muted small py-4">Nothing here yet.</div>';
        html += state.chats.map(function (c) {
            return '<div class="wa-item ' + (cur === keyOf(c) ? 'active' : '') + '" data-key="' + keyOf(c) + '">' + avatarOf(c) +
                '<div class="flex-grow-1 min-w-0"><div class="d-flex justify-content-between gap-2"><div class="wa-name">' + esc(c.name || 'Chat') + srcTag(c) + '</div><div class="wa-time ' + (c.unread ? 'unread' : '') + '">' + when(c.last_message_at) + '</div></div>' +
                '<div class="d-flex justify-content-between gap-2"><div class="wa-prev">' + esc(c.last_message_preview || (c.type === 'group' ? (c.member_count + ' members') : '')) + '</div><div class="d-flex gap-1 align-items-center">' + (c.muted ? '<i class="bi bi-bell-slash small text-muted"></i>' : '') + (c.unread ? '<span class="wa-badge">' + c.unread + '</span>' : '') + '</div></div></div></div>';
        }).join('');
        box.innerHTML = html;
    }
    $('chatList').addEventListener('click', function (e) {
        var h = e.target.closest('[data-hit]');
        if (h) { return openKey('staff:' + h.dataset.hit).then(function () { jumpTo(Number(h.dataset.mid)); }); }
        var el = e.target.closest('[data-key]'); if (el) openKey(el.dataset.key);
    });
    $('chatList').addEventListener('contextmenu', function (e) {
        var el = e.target.closest('[data-key]'); if (!el) return; e.preventDefault();
        var c = state.chats.find(function (x) { return keyOf(x) === el.dataset.key; }); if (!c || isStudent(c) || !c.member_of) return;
        showMenu(e.clientX, e.clientY, [['Mark as ' + (c.unread ? 'read' : 'unread'), function () { api('POST', '/chats/' + c.id + (c.unread ? '/read' : '/unread'), c.unread ? {} : { on: true }).then(loadChats); }], [c.archived ? 'Unarchive' : 'Archive', function () { api('POST', '/chats/' + c.id + '/archive', { on: !c.archived }).then(loadChats); }], [c.muted ? 'Unmute' : 'Mute', function () { api('POST', '/chats/' + c.id + '/mute', { mute: !c.muted }).then(loadChats); }]]);
    });
    var st; $('chatSearch').addEventListener('input', function () {
        clearTimeout(st); var v = this.value;
        st = setTimeout(function () {
            state.search = v; searchHits = null;
            var p = v.trim().length >= 2 ? api('GET', '/search?q=' + encodeURIComponent(v.trim())).then(function (h) { searchHits = h; }).catch(function () {}) : Promise.resolve();
            p.then(loadChats);
        }, 280);
    });

    // ---------------------------------------------------------------- open a conversation
    function openKey(key) {
        var parts = key.split(':'), src = parts[0], id = Number(parts[1]);
        return api('GET', (src === 'student' ? '/console/student-chats/' : '/chats/') + id).then(function (d) {
            d.source = src; state.chat = d; state.reply = null; state.hasMore = true; clearFile(); closeInSearch();
            $('replyBar').style.display = 'none';
            $('wa').classList.add('chat-open'); $('emptyState').style.display = 'none'; $('chatPane').style.display = 'flex';
            $('paneName').textContent = d.name || 'Chat'; $('paneAvatar').style.background = d.type === 'group' ? '#059669' : colorOf(d.name); $('paneAvatar').innerHTML = d.type === 'group' ? '<i class="bi bi-people-fill"></i>' : esc(initial(d.name));
            paintHeader();
            var ob = $('oversightBadge');
            if (isStudent(d)) { ob.textContent = d.member_of ? 'student chat' : 'viewing (not a member)'; ob.classList.remove('d-none'); }
            else if (d.oversight) { ob.textContent = 'viewing as Admin'; ob.classList.remove('d-none'); } else ob.classList.add('d-none');
            $('btnJoin').classList.toggle('d-none', !(isStudent(d) && d.can_join));
            var canWrite = isStudent(d) ? d.can_send : d.can_send;
            $('composer').style.display = canWrite ? 'flex' : 'none';
            var ro = $('roNote'); ro.style.display = canWrite ? 'none' : 'block';
            ro.textContent = isStudent(d) ? (d.read_only_reason || 'Read only') : (d.only_admins_can_send && d.type === 'group' ? 'Only group admins can send messages in this group.' : 'You cannot send messages here.');
            var pin = $('pinnedBar'); if (d.pinned) { pin.style.display = 'block'; pin.dataset.mid = d.pinned.id; pin.innerHTML = '<i class="bi bi-pin-angle-fill me-1"></i>' + esc(d.pinned.body || (d.pinned.attachment ? '📎 ' + d.pinned.attachment.name : '')); } else pin.style.display = 'none';
            renderList();
            return api('GET', base(d) + '/messages?limit=50');
        }).then(function (list) { state.msgs = list; state.hasMore = list.length >= 50; renderMsgs('bottom'); markRead(); $('msgInput').focus(); }).catch(function (er) { toast(er.message); });
    }
    function paintHeader() {
        var d = state.chat; if (!d) return; var sub;
        if (d.type === 'group') sub = d.members.map(function (m) { return m.user_id === ME.id ? 'You' : m.name; }).join(', ');
        else { var o = d.members.find(function (m) { return m.user_id !== ME.id; }); var uid = o && o.user_id; sub = isStudent(d) ? (d.members.map(function (m) { return m.user_type + (m.mobile ? ' · ' + m.mobile : ''); }).join(' / ')) : (state.online[uid] ? 'online' : (state.lastSeen && state.lastSeen[uid] ? 'last seen ' + when(state.lastSeen[uid]) : 'offline')); }
        $('paneSub').textContent = sub || '';
    }
    $('btnBack').addEventListener('click', function () { $('wa').classList.remove('chat-open'); });
    $('btnJoin').addEventListener('click', function () { api('POST', '/console/student-chats/' + state.chat.id + '/join').then(function () { return openKey('student:' + state.chat.id); }).then(loadChats).catch(function (er) { toast(er.message); }); });

    // ---------------------------------------------------------------- messages
    function readByAll(m) {
        var d = state.chat; if (!d || isStudent(d)) return false;
        var others = d.members.filter(function (x) { return x.user_id !== ME.id; });
        return others.length > 0 && others.every(function (x) { return (x.last_read_message_id || 0) >= m.id; });
    }
    function attachUrl(a) { return a.public_url || (SERVER + a.url + '?token=' + encodeURIComponent(TOKEN)); }
    function msgHtml(m, prev) {
        if (m.type === 'system') return '<div class="wa-sys" data-mid="' + m.id + '"><span>' + esc(m.body) + '</span></div>';
        var me = m.sender_id === ME.id, first = !prev || prev.sender_id !== m.sender_id || prev.type === 'system';
        var group = state.chat.type === 'group' || (isStudent(state.chat));
        var h = '<div class="wa-row ' + (me ? 'me' : '') + (first ? ' first' : '') + '" data-mid="' + m.id + '"><div class="wa-b">';
        if (m.deleted) { h += '<span class="text-muted fst-italic"><i class="bi bi-slash-circle me-1"></i>This message was deleted</span>'; }
        else {
            h += '<span class="wa-chev" data-menu="' + m.id + '"><i class="bi bi-chevron-down"></i></span>';
            if (!me && group && first) h += '<div class="wa-who" style="color:' + colorOf(m.sender_name) + '">' + esc(m.sender_name || 'Unknown') + '</div>';
            if (m.forwarded) h += '<div class="wa-fwd"><i class="bi bi-arrow-90deg-right me-1"></i>Forwarded</div>';
            if (m.reply_to) h += '<div class="wa-quote" data-jump="' + m.reply_to.id + '"><b>' + esc(m.reply_to.sender_name || '') + '</b>' + (m.reply_to.deleted ? '<i>deleted message</i>' : esc(m.reply_to.body || '📎 attachment')) + '</div>';
            if (m.attachment) {
                var a = m.attachment, u = attachUrl(a), mime = a.mime || '';
                if (/^image\//.test(mime)) h += '<img class="wa-img" src="' + esc(u) + '" data-big="' + esc(u) + '" loading="lazy">';
                else if (/^audio\//.test(mime)) h += '<audio controls preload="none" src="' + esc(u) + '" style="max-width:260px"></audio>';
                else if (/^video\//.test(mime)) h += '<video controls preload="none" src="' + esc(u) + '" style="max-width:300px;border-radius:6px"></video>';
                else h += '<a class="wa-file" href="' + esc(u) + '" target="_blank"><i class="bi bi-file-earmark-text fs-4"></i><div><div class="text-truncate" style="max-width:200px">' + esc(a.name) + '</div><div class="small text-muted">' + size(a.size || 0) + '</div></div></a>';
            }
            if (m.body) h += '<span class="wa-text">' + linkify(m.body) + '</span>';
            h += '<span class="wa-foot">' + (m.starred ? '<i class="bi bi-star-fill wa-star"></i>' : '') + (m.edited ? 'edited ' : '') + clock(m.created_at) + (me && !isStudent(state.chat) ? '<i class="bi bi-check2-all wa-tick ' + (readByAll(m) ? 'read' : '') + '"></i>' : '') + '</span><div style="clear:both"></div>';
            if (m.reactions && m.reactions.length) h += '<div class="wa-reacts">' + m.reactions.map(function (r) { return '<span class="wa-react ' + (r.users.indexOf(ME.id) >= 0 ? 'mine' : '') + '" data-react="' + m.id + '" data-emoji="' + esc(r.emoji) + '">' + esc(r.emoji) + ' ' + r.count + '</span>'; }).join('') + '</div>';
        }
        return h + '</div></div>';
    }
    function renderMsgs(scroll) {
        var box = $('msgs'), keepFromBottom = box.scrollHeight - box.scrollTop, atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 80;
        var html = '', prev = null, lastDay = '';
        state.msgs.forEach(function (m) {
            var day = new Date(m.created_at).toDateString();
            if (day !== lastDay) { html += '<div class="wa-day"><span>' + dayLabel(m.created_at) + '</span></div>'; lastDay = day; prev = null; }
            html += msgHtml(m, prev); prev = m;
        });
        box.innerHTML = (state.hasMore && state.msgs.length ? '<div class="text-center small text-muted" id="olderHint">scroll up for older messages</div>' : '') + (html || '<div class="wa-sys"><span>No messages yet. Say hello 👋</span></div>');
        if (scroll === 'bottom' || (scroll !== 'keep' && atBottom)) box.scrollTop = box.scrollHeight;
        else if (scroll === 'keep') box.scrollTop = box.scrollHeight - keepFromBottom;
    }
    $('msgs').addEventListener('scroll', function () {
        if (this.scrollTop > 30 || !state.hasMore || state.loadingOlder || !state.chat || !state.msgs.length) return;
        state.loadingOlder = true;
        api('GET', base(state.chat) + '/messages?limit=50&before=' + state.msgs[0].id).then(function (older) { state.hasMore = older.length >= 50; state.msgs = older.concat(state.msgs); renderMsgs('keep'); }).catch(function () {}).then(function () { state.loadingOlder = false; });
    });
    function jumpTo(id) {
        var el = $('msgs').querySelector('[data-mid="' + id + '"]');
        if (!el) { toast('That message is older than what is loaded. Scroll up to load more.'); return; }
        el.scrollIntoView({ block: 'center' }); el.classList.remove('wa-hl'); void el.offsetWidth; el.classList.add('wa-hl');
    }
    $('pinnedBar').addEventListener('click', function () { jumpTo(Number(this.dataset.mid)); });
    function findMsg(id) { return state.msgs.find(function (x) { return x.id === id; }); }
    function markRead() {
        if (state.chat && !isStudent(state.chat) && state.chat.member_of) api('POST', '/chats/' + state.chat.id + '/read', {}).then(function () { var c = state.chats.find(function (x) { return keyOf(x) === keyOf(state.chat); }); if (c) { c.unread = 0; c.marked_unread = false; renderList(); } }).catch(function () {});
    }

    // ---------------------------------------------------------------- message actions (menu)
    var menu = $('waMenu');
    function showMenu(x, y, items, emojiRow) {
        var h = (emojiRow ? '<div class="emo">' + ['👍', '❤️', '😂', '😮', '🙏', '✅'].map(function (e) { return '<span data-emo="' + e + '">' + e + '</span>'; }).join('') + '</div>' : '') + items.map(function (it, i) { return '<a data-i="' + i + '">' + it[0] + '</a>'; }).join('');
        menu.innerHTML = h; menu.style.display = 'block';
        var w = menu.offsetWidth, hh = menu.offsetHeight;
        menu.style.left = Math.max(4, Math.min(x, innerWidth - w - 8)) + 'px'; menu.style.top = Math.max(4, Math.min(y, innerHeight - hh - 8)) + 'px';
        menu._items = items;
    }
    menu.addEventListener('click', function (e) {
        var a = e.target.closest('[data-i]'), em = e.target.closest('[data-emo]');
        if (em && state.menuMsg) { api('POST', '/chats/' + state.chat.id + '/messages/' + state.menuMsg.id + '/react', { emoji: em.dataset.emo }).catch(function (er) { toast(er.message); }); }
        else if (a) menu._items[Number(a.dataset.i)][1]();
        menu.style.display = 'none';
    });
    document.addEventListener('click', function (e) { if (!e.target.closest('#waMenu') && !e.target.closest('[data-menu]') && !e.target.closest('#btnMenu')) menu.style.display = 'none'; if (!e.target.closest('#emojiPop') && !e.target.closest('#btnEmoji')) $('emojiPop').style.display = 'none'; });
    $('msgs').addEventListener('click', function (e) {
        var t = e.target;
        var big = t.closest('[data-big]'); if (big) { $('imgBig').src = big.dataset.big; new bootstrap.Modal($('mImg')).show(); return; }
        var j = t.closest('[data-jump]'); if (j) { jumpTo(Number(j.dataset.jump)); return; }
        var r = t.closest('[data-react]'); if (r && !isStudent(state.chat)) { api('POST', '/chats/' + state.chat.id + '/messages/' + r.dataset.react + '/react', { emoji: r.dataset.emoji }).catch(function () {}); return; }
        var c = t.closest('[data-menu]'); if (!c) return;
        var m = findMsg(Number(c.dataset.menu)); if (!m) return; state.menuMsg = m;
        var rect = c.getBoundingClientRect(), st = !isStudent(state.chat), mine = m.sender_id === ME.id, d = state.chat, items = [];
        items.push(['Reply', function () { setReply(m); }]);
        if (m.body) items.push(['Copy', function () { navigator.clipboard && navigator.clipboard.writeText(m.body).then(function () { toast('Copied'); }); }]);
        if (st) {
            items.push(['Forward', function () { openForward(m); }]);
            items.push([m.starred ? 'Unstar' : 'Star', function () { api('POST', '/chats/' + d.id + '/messages/' + m.id + '/star', { on: !m.starred }).then(function (nm) { Object.assign(m, nm); renderMsgs(false); }).catch(function (er) { toast(er.message); }); }]);
            if (d.type === 'group' ? d.my_permissions && d.my_permissions.can_edit_info : true) items.push([d.pinned && d.pinned.id === m.id ? 'Unpin' : 'Pin', function () { api('POST', '/chats/' + d.id + '/pin', { message_id: d.pinned && d.pinned.id === m.id ? null : m.id }).then(function () { return openKey(keyOf(d)); }).catch(function (er) { toast(er.message); }); }]);
            if (mine) items.push(['Message info', function () { openInfo(m); }]);
            if (mine && m.body && !m.attachment) items.push(['Edit', function () { var nb = prompt('Edit your message', m.body); if (nb !== null && nb.trim()) api('PATCH', '/chats/' + d.id + '/messages/' + m.id, { body: nb }).catch(function (er) { toast(er.message); }); }]);
            if (mine || (d.my_permissions && d.my_permissions.can_delete_messages)) items.push(['Delete', function () { if (confirm('Delete this message for everyone?')) api('DELETE', '/chats/' + d.id + '/messages/' + m.id).catch(function (er) { toast(er.message); }); }]);
        }
        showMenu(rect.left - 150, rect.bottom + 4, items, st && d.can_send);
    });
    function setReply(m) { state.reply = m; $('replyText').textContent = (m.sender_name || '') + ': ' + (m.body || '📎 attachment'); $('replyBar').style.display = 'flex'; $('msgInput').focus(); }
    $('replyCancel').addEventListener('click', function (e) { e.preventDefault(); state.reply = null; $('replyBar').style.display = 'none'; });

    // forward
    var mForward = new bootstrap.Modal($('mForward')), fwMsg = null;
    function openForward(m) {
        fwMsg = m;
        $('fwList').innerHTML = state.chats.filter(function (c) { return !isStudent(c) && c.id !== state.chat.id && (c.member_of || ME.is_admin); }).map(function (c) { return '<label class="d-flex align-items-center gap-2 px-2 py-2 border-bottom mb-0"><input type="checkbox" class="form-check-input fw-pick" value="' + c.id + '"> ' + esc(c.name) + '</label>'; }).join('') || '<div class="p-3 text-muted small">No other chats yet.</div>';
        mForward.show();
    }
    $('fwSend').addEventListener('click', function () {
        var ids = [].map.call($('fwList').querySelectorAll('.fw-pick:checked'), function (x) { return Number(x.value); }); if (!ids.length) return;
        api('POST', '/chats/' + state.chat.id + '/messages/' + fwMsg.id + '/forward', { chat_ids: ids }).then(function (r) { mForward.hide(); toast('Forwarded to ' + r.forwarded_to + ' chat(s)'); loadChats(); }).catch(function (er) { toast(er.message); });
    });
    // info
    var mInfo = new bootstrap.Modal($('mInfo'));
    function openInfo(m) {
        api('GET', '/chats/' + state.chat.id + '/messages/' + m.id + '/info').then(function (i) {
            $('infoBody').innerHTML = '<div class="mb-2 text-muted small">Sent ' + new Date(i.sent_at).toLocaleString() + '</div><h6><i class="bi bi-check2-all wa-tick read"></i> Read by</h6>' + (i.read_by.map(function (u) { return '<div>' + esc(u.name) + '</div>'; }).join('') || '<div class="text-muted small">nobody yet</div>') + '<h6 class="mt-3"><i class="bi bi-check2-all"></i> Not read yet</h6>' + (i.not_yet.map(function (u) { return '<div>' + esc(u.name) + '</div>'; }).join('') || '<div class="text-muted small">everyone has read it</div>');
            mInfo.show();
        });
    }
    // starred
    var mStar = new bootstrap.Modal($('mStarred'));
    $('btnStarred').addEventListener('click', function () {
        api('GET', '/starred').then(function (l) {
            $('starBody').innerHTML = l.map(function (m) { return '<div class="border-bottom py-2" role="button" data-star="' + m.chat_id + '" data-mid="' + m.id + '"><div class="small text-muted">' + esc(m.chat_name) + ' · ' + esc(m.sender_name || '') + ' · ' + new Date(m.created_at).toLocaleString() + '</div><div>' + esc(m.body || '📎 ' + (m.attachment ? m.attachment.name : '')) + '</div></div>'; }).join('') || '<div class="text-muted">No starred messages. Use the ⌄ menu on a message to star it.</div>';
            mStar.show();
        });
    });
    $('starBody').addEventListener('click', function (e) { var r = e.target.closest('[data-star]'); if (!r) return; mStar.hide(); openKey('staff:' + r.dataset.star).then(function () { jumpTo(Number(r.dataset.mid)); }); });
    // header menu
    $('btnMenu').addEventListener('click', function (e) {
        var d = state.chat; if (!d) return; var r = this.getBoundingClientRect(), items = [];
        items.push([d.type === 'group' ? 'Group info & members' : 'Contact info', function () { openMembers(); }]);
        if (!isStudent(d)) {
            items.push(['Export chat (.txt)', function () { fetch(SERVER + '/api/chats/' + d.id + '/export', { headers: { Authorization: 'Bearer ' + TOKEN } }).then(function (x) { return x.blob(); }).then(function (b) { var a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = (d.name || 'chat') + '.txt'; a.click(); }); }]);
            if (d.member_of) {
                items.push([d.muted ? 'Unmute notifications' : 'Mute notifications', function () { api('POST', '/chats/' + d.id + '/mute', { mute: !d.muted }).then(function () { return openKey(keyOf(d)); }).then(loadChats); }]);
                items.push([d.archived ? 'Unarchive chat' : 'Archive chat', function () { api('POST', '/chats/' + d.id + '/archive', { on: !d.archived }).then(function () { toast(d.archived ? 'Unarchived' : 'Archived'); return loadChats(); }); }]);
                items.push(['Mark as unread', function () { api('POST', '/chats/' + d.id + '/unread', { on: true }).then(loadChats); }]);
            }
        }
        showMenu(r.right - 210, r.bottom + 4, items);
    });
    $('paneHead').addEventListener('click', function () { openMembers(); });

    // in-chat search
    function closeInSearch() { $('searchBar').style.display = 'none'; $('inSearch').value = ''; $('inSearchRes').innerHTML = ''; }
    $('btnSearchIn').addEventListener('click', function () { var b = $('searchBar'); b.style.display = b.style.display === 'block' ? 'none' : 'block'; if (b.style.display === 'block') $('inSearch').focus(); });
    $('inSearchClose').addEventListener('click', closeInSearch);
    var it; $('inSearch').addEventListener('input', function () {
        clearTimeout(it); var v = this.value.trim(), d = state.chat;
        it = setTimeout(function () {
            if (v.length < 2) { $('inSearchRes').innerHTML = ''; return; }
            var p = isStudent(d) ? Promise.resolve(state.msgs.filter(function (m) { return (m.body || '').toLowerCase().indexOf(v.toLowerCase()) >= 0; }).reverse()) : api('GET', '/chats/' + d.id + '/search?q=' + encodeURIComponent(v));
            p.then(function (l) { $('inSearchRes').innerHTML = l.map(function (m) { return '<div role="button" class="py-1 border-bottom" data-hit2="' + m.id + '"><span class="text-muted">' + new Date(m.created_at).toLocaleDateString() + ' · ' + esc(m.sender_name || '') + ':</span> ' + esc(m.body) + '</div>'; }).join('') || '<span class="text-muted">No matches.</span>'; });
        }, 250);
    });
    $('inSearchRes').addEventListener('click', function (e) { var r = e.target.closest('[data-hit2]'); if (r) jumpTo(Number(r.dataset.hit2)); });

    // ---------------------------------------------------------------- composer
    function clearFile() { state.file = null; $('fileInput').value = ''; $('fileChipBar').style.display = 'none'; }
    function setFile(f) { if (!f) return; state.file = f; $('fileChip').textContent = '📎 ' + f.name + ' (' + size(f.size) + ')'; $('fileChipBar').style.display = 'flex'; }
    $('fileInput').addEventListener('change', function () { setFile(this.files[0]); });
    $('fileCancel').addEventListener('click', function (e) { e.preventDefault(); clearFile(); });
    var msgsBox = $('msgs');
    ['dragover', 'dragenter'].forEach(function (ev) { msgsBox.addEventListener(ev, function (e) { e.preventDefault(); msgsBox.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { msgsBox.addEventListener(ev, function (e) { e.preventDefault(); msgsBox.classList.remove('drag'); }); });
    msgsBox.addEventListener('drop', function (e) { if (e.dataTransfer.files[0] && state.chat && state.chat.can_send) setFile(e.dataTransfer.files[0]); });
    $('msgInput').addEventListener('paste', function (e) { var f = e.clipboardData && e.clipboardData.files[0]; if (f) { e.preventDefault(); setFile(f); } });
    $('composer').addEventListener('submit', function (e) {
        e.preventDefault();
        var body = $('msgInput').value.trim(); if ((!body && !state.file) || !state.chat) return;
        var form = new FormData(); if (body) form.append('body', body); if (state.reply) form.append('reply_to_id', state.reply.id); if (state.file) form.append('file', state.file);
        $('sendBtn').disabled = true;
        api('POST', base(state.chat) + '/messages', null, form).then(function (m) {
            $('msgInput').value = ''; autoGrow(); state.reply = null; $('replyBar').style.display = 'none'; clearFile();
            if (isStudent(state.chat)) return refreshStudent(true);
        }).catch(function (er) { toast(er.message); }).then(function () { $('sendBtn').disabled = false; $('msgInput').focus(); });
    });
    function autoGrow() { var t = $('msgInput'); t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight, 130) + 'px'; }
    $('msgInput').addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if ($('quickPop').style.display === 'block' && $('quickPop').firstElementChild) { $('quickPop').firstElementChild.click(); return; } $('composer').requestSubmit(); } });
    $('msgInput').addEventListener('input', function () {
        autoGrow(); if (state.chat && !isStudent(state.chat) && socket && socket.connected) socket.emit('typing', { chat_id: state.chat.id });
        var v = this.value, q = $('quickPop');
        if (/^\/[a-z0-9_-]*$/i.test(v)) { var s = v.slice(1).toLowerCase(), l = state.quick.filter(function (x) { return x.shortcut.indexOf(s) === 0; }); q.innerHTML = l.map(function (x) { return '<a data-body="' + esc(x.body) + '"><b>/' + esc(x.shortcut) + '</b> ' + esc(x.body.slice(0, 70)) + '</a>'; }).join(''); q.style.display = l.length ? 'block' : 'none'; } else q.style.display = 'none';
    });
    $('quickPop').addEventListener('click', function (e) { var a = e.target.closest('[data-body]'); if (!a) return; $('msgInput').value = a.getAttribute('data-body'); this.style.display = 'none'; autoGrow(); $('msgInput').focus(); });
    // emoji
    var EMOJIS = '😀 😁 😂 🤣 😊 😍 😘 😎 🤩 🥳 😇 🙂 😉 😌 🤔 😐 😴 😢 😭 😡 😱 🤗 🙏 👍 👎 👏 🙌 💪 👌 ✌️ 🤝 👋 ❤️ 💙 💚 💛 🔥 ⭐ ✨ 🎉 🎓 📚 📝 ✅ ❌ ⚠️ 💯 📞 📎 📅 ⏰ 💰'.split(' ');
    $('emojiGrid').innerHTML = EMOJIS.map(function (e) { return '<span>' + e + '</span>'; }).join('');
    $('btnEmoji').addEventListener('click', function () { var p = $('emojiPop'); p.style.display = p.style.display === 'block' ? 'none' : 'block'; });
    $('emojiGrid').addEventListener('click', function (e) { if (e.target.tagName === 'SPAN') { var t = $('msgInput'); t.value += e.target.textContent; t.focus(); autoGrow(); } });
    // voice note
    var rec = null, recChunks = [], recTimer = null;
    $('btnMic').addEventListener('click', function () {
        if (rec) { rec.stop(); return; }
        if (!navigator.mediaDevices || !window.MediaRecorder) { toast('Your browser cannot record audio.'); return; }
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
            recChunks = []; rec = new MediaRecorder(stream); var started = Date.now();
            rec.ondataavailable = function (e) { recChunks.push(e.data); };
            rec.onstop = function () {
                stream.getTracks().forEach(function (t) { t.stop(); }); clearInterval(recTimer);
                $('btnMic').innerHTML = '<i class="bi bi-mic"></i>'; $('btnMic').classList.remove('wa-rec');
                var blob = new Blob(recChunks, { type: rec.mimeType || 'audio/webm' }); rec = null;
                if (blob.size > 800) { setFile(new File([blob], 'voice-note-' + Date.now() + '.webm', { type: (blob.type || 'audio/webm').split(';')[0] })); $('composer').requestSubmit(); }
            };
            rec.start(); $('btnMic').classList.add('wa-rec');
            recTimer = setInterval(function () { $('btnMic').innerHTML = '<i class="bi bi-stop-circle-fill"></i> ' + Math.round((Date.now() - started) / 1000) + 's'; }, 500);
        }).catch(function () { toast('Allow the microphone to record.'); });
    });
    // quick replies
    function loadQuick() { return api('GET', '/quick-replies').then(function (l) { state.quick = l; renderQuick(); }).catch(function () {}); }
    function renderQuick() { $('qrList').innerHTML = state.quick.map(function (q) { return '<div class="d-flex gap-2 border-bottom py-1 align-items-start"><div class="flex-grow-1"><b>/' + esc(q.shortcut) + '</b>' + (q.user_id === null ? ' <span class="badge bg-info">shared</span>' : '') + '<div class="small text-muted">' + esc(q.body) + '</div></div>' + (q.user_id === null && !ME.is_admin ? '' : '<a href="#" class="text-danger" data-qdel="' + q.id + '"><i class="bi bi-trash"></i></a>') + '</div>'; }).join('') || '<div class="text-muted small">None yet.</div>'; }
    var mQuick = new bootstrap.Modal($('mQuick'));
    $('btnQuick').addEventListener('click', function () { $('qrError').textContent = ''; renderQuick(); mQuick.show(); });
    $('qrSave').addEventListener('click', function () { api('POST', '/quick-replies', { shortcut: $('qrShort').value, body: $('qrBody').value, shared: $('qrShared') ? $('qrShared').checked : false }).then(function (l) { state.quick = l; $('qrShort').value = ''; $('qrBody').value = ''; $('qrError').textContent = ''; renderQuick(); }).catch(function (er) { $('qrError').textContent = er.message; }); });
    $('qrList').addEventListener('click', function (e) { var d = e.target.closest('[data-qdel]'); if (!d) return; e.preventDefault(); api('DELETE', '/quick-replies/' + d.dataset.qdel).then(function (l) { state.quick = l; renderQuick(); }); });

    // ---------------------------------------------------------------- realtime
    var socket = io(SERVER, { auth: function (cb) { cb({ token: TOKEN }); }, transports: ['websocket', 'polling'] });
    socket.on('connect', function () { $('connState').className = 'badge bg-success ms-1'; $('connState').textContent = 'live'; api('GET', '/presence').then(function (p) { p.online.forEach(function (id) { state.online[id] = true; }); renderList(); paintHeader(); }).catch(function () {}); });
    socket.on('disconnect', function () { $('connState').className = 'badge bg-secondary ms-1'; $('connState').textContent = 'reconnecting…'; });
    socket.on('connect_error', function (e) { $('connState').className = 'badge bg-danger ms-1'; $('connState').textContent = e.message === 'unauthorized' ? 'sign in again' : 'offline'; if (e.message === 'unauthorized') refreshToken(); });
    socket.on('presence', function (p) { state.online[p.user_id] = p.online; state.lastSeen = state.lastSeen || {}; if (!p.online) state.lastSeen[p.user_id] = p.last_seen; renderList(); paintHeader(); });
    function ding() { try { var c = new (window.AudioContext || window.webkitAudioContext)(), o = c.createOscillator(), g = c.createGain(); o.connect(g); g.connect(c.destination); o.frequency.value = 880; g.gain.value = 0.05; o.start(); o.stop(c.currentTime + 0.12); } catch (e) {} }
    socket.on('message:new', function (m) {
        var cur = state.chat && !isStudent(state.chat) && m.chat_id === state.chat.id;
        if (cur) { if (!state.msgs.some(function (x) { return x.id === m.id; })) state.msgs.push(m); renderMsgs(false); if (m.sender_id !== ME.id) markRead(); }
        if (m.sender_id !== ME.id && (!cur || document.hidden)) {
            var c = state.chats.find(function (x) { return !isStudent(x) && x.id === m.chat_id; });
            if (!c || !c.muted) { ding(); if (window.Notification && Notification.permission === 'granted' && document.hidden) { try { new Notification(m.sender_name || 'New message', { body: m.body || '📎 attachment' }); } catch (e) {} } }
        }
        loadChats();
    });
    socket.on('message:updated', function (m) { if (state.chat && !isStudent(state.chat) && m.chat_id === state.chat.id) { var i = state.msgs.findIndex(function (x) { return x.id === m.id; }); if (i >= 0) state.msgs[i] = m; renderMsgs(false); } loadChats(); });
    socket.on('chat:new', function () { loadChats(); });
    socket.on('chat:updated', function (p) { loadChats(); if (state.chat && !isStudent(state.chat) && p.chat_id === state.chat.id) { if (p.removed_user_id === ME.id) { state.chat = null; $('chatPane').style.display = 'none'; $('emptyState').style.display = 'flex'; } else api('GET', '/chats/' + p.chat_id).then(function (d) { d.source = 'staff'; state.chat = d; paintHeader(); renderMsgs(false); }); } });
    socket.on('typing', function (p) {
        if (!state.chat || isStudent(state.chat) || p.chat_id !== state.chat.id || p.user_id === ME.id) return;
        $('typing').textContent = p.name + ' is typing…'; clearTimeout(state.typingTimer); state.typingTimer = setTimeout(function () { $('typing').textContent = ''; }, 2500);
    });
    if (window.Notification && Notification.permission === 'default') document.addEventListener('click', function once() { Notification.requestPermission(); document.removeEventListener('click', once); });
    // students' chats live in another service: refresh them every few seconds
    function refreshStudent(force) {
        if (!ME.is_admin || (document.hidden && !force)) return Promise.resolve();
        var p = [loadChats()];
        if (state.chat && isStudent(state.chat)) p.push(api('GET', base(state.chat) + '/messages?limit=50').then(function (l) { var last = state.msgs.length ? state.msgs[state.msgs.length - 1].id : 0; if (l.length && l[l.length - 1].id !== last || l.length !== Math.min(state.msgs.length, 50)) { var older = state.msgs.filter(function (x) { return !l.some(function (y) { return y.id === x.id; }) && x.id < (l[0] ? l[0].id : 0); }); state.msgs = older.concat(l); renderMsgs(false); } }));
        return Promise.all(p).catch(function () {});
    }
    setInterval(refreshStudent, 8000);
    setInterval(function () { if (state.chat && !isStudent(state.chat) && !document.hidden) api('GET', '/chats/' + state.chat.id).then(function (d) { d.source = 'staff'; state.chat = d; renderMsgs(false); }).catch(function () {}); }, 12000);

    // ---------------------------------------------------------------- new chat
    var mNew = new bootstrap.Modal($('mNew')), newType = 'all';
    function people(q) { return ME.is_admin ? api('GET', '/console/users?q=' + encodeURIComponent(q || '') + '&type=' + encodeURIComponent(newType)) : api('GET', '/staff?q=' + encodeURIComponent(q || '')); }
    function staffList(q) { return api('GET', '/staff?q=' + encodeURIComponent(q || '')).then(function (l) { return l.filter(function (u) { return u.id !== ME.id; }); }); }
    $('btnNewChat').addEventListener('click', function () { $('newChatQ').value = ''; fillNew(''); mNew.show(); setTimeout(function () { $('newChatQ').focus(); }, 300); });
    function fillNew(q) {
        people(q).then(function (l) {
            $('newChatList').innerHTML = l.filter(function (u) { return u.id !== ME.id; }).map(function (u) {
                return '<a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-2" data-uid="' + u.id + '"><div class="wa-av sm" style="background:' + colorOf(u.name) + '">' + esc(initial(u.name)) + '</div><div class="flex-grow-1"><strong>' + esc(u.name) + '</strong> <span class="badge ' + (u.type === 'Student' ? 'bg-warning text-dark' : u.type === 'Admin' ? 'bg-dark' : 'bg-primary') + '">' + esc(u.type) + '</span><div class="small text-muted">' + esc([u.email, u.mobile].filter(Boolean).join(' · ')) + '</div></div></a>';
            }).join('') || '<div class="p-3 text-muted small">No one found.</div>';
        });
    }
    var nt; $('newChatQ').addEventListener('input', function () { clearTimeout(nt); var v = this.value; nt = setTimeout(function () { fillNew(v); }, 250); });
    if ($('newTypes')) $('newTypes').addEventListener('click', function (e) { var b = e.target.closest('[data-t]'); if (!b) return; newType = b.dataset.t; [].forEach.call(this.children, function (x) { x.classList.toggle('active', x === b); }); fillNew($('newChatQ').value); });
    function startWith(uid) {
        return (ME.is_admin ? api('POST', '/console/direct', { user_id: uid }) : api('POST', '/chats/direct', { user_id: uid }).then(function (r) { return { key: 'staff:' + r.id }; }))
            .then(function (r) { mNew.hide(); if (ME.is_admin) { state.tab = 'all'; renderTabs(); } return loadChats().then(function () { return openKey(r.key); }); });
    }
    $('newChatList').addEventListener('click', function (e) { var a = e.target.closest('[data-uid]'); if (!a) return; e.preventDefault(); startWith(Number(a.dataset.uid)).catch(function (er) { toast(er.message); }); });

    // ---------------------------------------------------------------- new group
    var mGroup = new bootstrap.Modal($('mGroup')), gPicked = {};
    $('btnNewGroup').addEventListener('click', function () { gPicked = {}; $('gName').value = ''; $('gDesc').value = ''; $('gOnlyAdmins').checked = false; $('gError').textContent = ''; $('gSearch').value = ''; fillGroup(''); mGroup.show(); });
    function fillGroup(q) {
        staffList(q).then(function (l) {
            $('gStaff').innerHTML = l.map(function (u) {
                var p = gPicked[u.id];
                return '<div class="d-flex align-items-center gap-2 px-2 py-1 border-bottom"><input type="checkbox" class="form-check-input g-pick" data-uid="' + u.id + '" ' + (p ? 'checked' : '') + '><span class="flex-grow-1">' + esc(u.name) + ' <span class="small text-muted">' + esc(u.email) + '</span>' + (u.type === 'Admin' ? ' <span class="badge bg-dark">Admin: always added</span>' : '') + '</span>' +
                    '<label class="small mb-0"><input type="checkbox" class="form-check-input g-send" data-uid="' + u.id + '" ' + (!p || p.can_send ? 'checked' : '') + '> can send</label></div>';
            }).join('') || '<div class="p-3 text-muted small">No staff found.</div>';
        });
    }
    var gt; $('gSearch').addEventListener('input', function () { clearTimeout(gt); var v = this.value; gt = setTimeout(function () { fillGroup(v); }, 250); });
    $('gStaff').addEventListener('change', function (e) {
        var uid = Number(e.target.dataset.uid); if (!uid) return;
        if (e.target.classList.contains('g-pick')) { if (e.target.checked) gPicked[uid] = { can_send: true }; else delete gPicked[uid]; }
        if (e.target.classList.contains('g-send')) { gPicked[uid] = gPicked[uid] || {}; gPicked[uid].can_send = e.target.checked; }
    });
    $('gCreate').addEventListener('click', function () {
        var members = Object.keys(gPicked).map(function (id) { return { user_id: Number(id), permissions: { can_send: gPicked[id].can_send !== false } }; });
        api('POST', '/groups', { name: $('gName').value, description: $('gDesc').value, only_admins_can_send: $('gOnlyAdmins').checked, members: members })
            .then(function (r) { mGroup.hide(); return loadChats().then(function () { return openKey('staff:' + r.id); }); }).catch(function (er) { $('gError').textContent = er.message; });
    });

    // ---------------------------------------------------------------- members / info
    var mMembers = new bootstrap.Modal($('mMembers'));
    var PERM_LABELS = { can_send: 'Send messages', can_add_members: 'Add members', can_remove_members: 'Remove members', can_edit_info: 'Edit group info', can_manage_roles: 'Manage roles', can_delete_messages: 'Delete messages' };
    function openMembers() { if (!state.chat) return; renderMembers(); mMembers.show(); }
    function renderMembers() {
        var c = state.chat; if (!c) return; $('mmError').textContent = '';
        $('mmTitle').textContent = (c.name || 'Chat') + ' — ' + c.members.length + ' member(s)';
        var p = c.my_permissions || {}, isGroup = c.type === 'group', stu = isStudent(c);
        $('mmInfo').innerHTML = stu ? '<div class="text-muted small">' + (c.sub_type === 'course_group' ? 'Course group' : c.type === 'group' ? 'Student group' : 'Student conversation') + (c.description ? ' · ' + esc(c.description) : '') + '</div>' : isGroup ? (p.can_edit_info ? '<div class="row g-2"><div class="col-md-5"><input id="giName" class="form-control form-control-sm" value="' + esc(c.name) + '" maxlength="150"></div><div class="col-md-5"><input id="giDesc" class="form-control form-control-sm" value="' + esc(c.description || '') + '" placeholder="Description" maxlength="500"></div><div class="col-md-2"><button class="btn btn-sm btn-primary w-100 mb-0" id="giSave">Save</button></div></div>' +
            (c.my_role !== 'member' || c.oversight ? '<div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="giOnly" ' + (c.only_admins_can_send ? 'checked' : '') + '><label class="form-check-label small" for="giOnly">Only group admins can send messages</label></div>' : '') : '<div class="text-muted small">' + esc(c.description || '') + (c.only_admins_can_send ? ' · Only group admins can send.' : '') + '</div>') : '<div class="text-muted small">Direct chat.</div>';
        $('mmAdd').classList.toggle('d-none', stu || !(isGroup && p.can_add_members));
        $('mmLeave').classList.toggle('d-none', stu || !(isGroup && c.member_of));
        $('mmList').innerHTML = c.members.map(function (m) {
            var canManage = !stu && isGroup && p.can_manage_roles && m.role !== 'owner' && !m.is_system_admin, canRemove = !stu && isGroup && (m.user_id === ME.id ? false : p.can_remove_members) && m.role !== 'owner' && (!m.is_system_admin || ME.is_admin);
            var permBoxes = canManage && m.role === 'member' ? '<div class="small mt-1">' + Object.keys(PERM_LABELS).map(function (k) { return '<label class="me-3"><input type="checkbox" class="form-check-input m-perm" data-uid="' + m.user_id + '" data-perm="' + k + '" ' + (m.permissions[k] ? 'checked' : '') + '> ' + PERM_LABELS[k] + '</label>'; }).join('') + '</div>' : '';
            return '<div class="border-bottom py-2"><div class="d-flex align-items-center gap-2"><div class="wa-av sm" style="background:' + colorOf(m.name) + '">' + esc(initial(m.name)) + '</div><div class="flex-grow-1"><strong>' + esc(m.name) + '</strong> <span class="badge bg-' + (m.role === 'owner' ? 'warning text-dark' : m.role === 'admin' ? 'info' : 'light text-dark border') + '">' + (m.is_system_admin ? 'Admin' : m.role) + '</span>' + (stu ? ' <span class="badge ' + (m.user_type === 'Student' ? 'bg-warning text-dark' : 'bg-primary') + '">' + esc(m.user_type) + '</span>' : (state.online[m.user_id] ? ' <span class="badge bg-success">online</span>' : '')) + '<div class="small text-muted">' + esc([m.email, m.mobile].filter(Boolean).join(' · ')) + '</div></div>' +
                (m.user_type === 'Student' ? '<a class="btn btn-sm btn-outline-secondary mb-0" href="' + @json(url('admin/students/student-list')) + '/' + m.user_id + '" target="_blank">Profile</a><button class="btn btn-sm btn-outline-primary mb-0 m-chat" data-uid="' + m.user_id + '">Message</button>' : '') +
                (canManage ? '<select class="form-select form-select-sm w-auto m-role" data-uid="' + m.user_id + '"><option value="member" ' + (m.role === 'member' ? 'selected' : '') + '>Member</option><option value="admin" ' + (m.role === 'admin' ? 'selected' : '') + '>Group admin (all permissions)</option></select>' : '') +
                (CAN_IMPERSONATE && !stu && m.user_id !== ME.id && !m.is_system_admin ? '<button class="btn btn-sm btn-warning mb-0 m-login" data-uid="' + m.user_id + '" title="Open the panel as this staff member">Login as</button>' : '') +
                (canRemove ? '<button class="btn btn-sm btn-outline-danger mb-0 m-remove" data-uid="' + m.user_id + '"><i class="bi bi-x-lg"></i></button>' : '') + '</div>' + permBoxes + '</div>';
        }).join('');
    }
    $('mMembers').addEventListener('click', function (e) {
        var t = e.target;
        if (t.id === 'giSave') { api('PATCH', '/groups/' + state.chat.id, { name: $('giName').value, description: $('giDesc').value }).then(function () { return openKey(keyOf(state.chat)); }).then(renderMembers).catch(function (er) { $('mmError').textContent = er.message; }); }
        if (t.closest('.m-chat')) { var cu = Number(t.closest('.m-chat').dataset.uid); mMembers.hide(); startWith(cu).catch(function (er) { toast(er.message); }); }
        if (t.closest('.m-remove')) { var uid = Number(t.closest('.m-remove').dataset.uid); if (confirm('Remove this person from the group?')) api('DELETE', '/groups/' + state.chat.id + '/members/' + uid).then(function () { return openKey(keyOf(state.chat)); }).then(renderMembers).catch(function (er) { $('mmError').textContent = er.message; }); }
        if (t.closest('.m-login')) { var u = Number(t.closest('.m-login').dataset.uid); if (confirm('Open the panel as this staff member? Signing out brings you back to your Admin dashboard.')) { var f = $('impersonateForm'); f.action = IMPERSONATE + '/' + u; f.submit(); } }
        if (t.id === 'mmLeave' && confirm('Leave this group?')) api('POST', '/groups/' + state.chat.id + '/leave').then(function () { mMembers.hide(); state.chat = null; $('chatPane').style.display = 'none'; $('emptyState').style.display = 'flex'; return loadChats(); }).catch(function (er) { $('mmError').textContent = er.message; });
    });
    $('mMembers').addEventListener('change', function (e) {
        var t = e.target;
        if (t.id === 'giOnly') api('PATCH', '/groups/' + state.chat.id, { only_admins_can_send: t.checked }).then(function () { return openKey(keyOf(state.chat)); }).catch(function (er) { $('mmError').textContent = er.message; });
        if (t.classList.contains('m-role')) api('PATCH', '/groups/' + state.chat.id + '/members/' + t.dataset.uid, { role: t.value }).then(function () { return openKey(keyOf(state.chat)); }).then(renderMembers).catch(function (er) { $('mmError').textContent = er.message; });
        if (t.classList.contains('m-perm')) { var perms = {}; perms[t.dataset.perm] = t.checked; api('PATCH', '/groups/' + state.chat.id + '/members/' + t.dataset.uid, { permissions: perms }).then(function () { return openKey(keyOf(state.chat)); }).catch(function (er) { $('mmError').textContent = er.message; t.checked = !t.checked; }); }
    });
    var at; $('mmAddQ').addEventListener('input', function () {
        clearTimeout(at); var v = this.value;
        at = setTimeout(function () { staffList(v).then(function (l) { var inGroup = state.chat.members.map(function (m) { return m.user_id; }); $('mmAddList').innerHTML = l.filter(function (u) { return inGroup.indexOf(u.id) < 0; }).map(function (u) { return '<a href="#" class="list-group-item list-group-item-action py-1" data-add="' + u.id + '">' + esc(u.name) + ' <span class="small text-muted">' + esc(u.email) + '</span></a>'; }).join(''); }); }, 250);
    });
    $('mmAddList').addEventListener('click', function (e) { var a = e.target.closest('[data-add]'); if (!a) return; e.preventDefault(); api('POST', '/groups/' + state.chat.id + '/members', { members: [{ user_id: Number(a.dataset.add) }] }).then(function () { $('mmAddQ').value = ''; $('mmAddList').innerHTML = ''; return openKey(keyOf(state.chat)); }).then(renderMembers).catch(function (er) { $('mmError').textContent = er.message; }); });

    // ---------------------------------------------------------------- start
    renderTabs();
    loadQuick();
    loadChats().then(function () {
        if (QS.get('conv')) return openKey(QS.get('conv'));
        if (QS.get('student') && ME.is_admin) return startWith(Number(QS.get('student')));
    }).catch(function (er) { $('chatList').innerHTML = '<div class="text-center text-danger small py-4">' + esc(er.message) + '</div>'; });
})();
</script>
@endsection
