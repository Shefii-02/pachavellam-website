{{-- Loaded at the end of every admin page: the media library window + "choose from library / upload" on every file field. --}}
@if (auth()->check() && request()->is('admin*') && ! request()->is('admin/chat*'))
<style>
    .pvm { display:flex; flex-direction:column; height:100%; min-height:420px; font-size:.88rem; }
    .pvm-bar { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; padding:10px 14px; border-bottom:1px solid rgba(127,127,127,.25); }
    .pvm-crumbs a { font-weight:700; text-decoration:none; } .pvm-crumbs small { color:#8b95a1; margin-left:8px; } .pvm-crumbs i { font-size:.7rem; opacity:.6; }
    .pvm-tools { display:flex; gap:8px; align-items:center; flex-wrap:wrap; } .pvm-tools input[type=search], .pvm-tools select, .pvm-side input { border:1px solid rgba(127,127,127,.35); border-radius:8px; padding:6px 10px; background:transparent; color:inherit; font-size:.85rem; }
    .pvm-btn { border:1px solid rgba(127,127,127,.4); background:transparent; color:inherit; border-radius:8px; padding:6px 12px; font-size:.82rem; font-weight:600; cursor:pointer; white-space:nowrap; }
    .pvm-btn.pri { background:#0a8f5b; border-color:#0a8f5b; color:#fff; } .pvm-btn.red { color:#d93036; border-color:rgba(217,48,54,.4); } .pvm-btn:disabled { opacity:.6; }
    .pvm-main { display:flex; flex:1; min-height:0; }
    .pvm-gridwrap { flex:1; min-width:0; overflow-y:auto; padding:12px 14px; position:relative; max-height:68vh; }
    .pvm-drop { display:none; position:absolute; inset:0; z-index:5; background:rgba(10,143,91,.12); border:3px dashed #0a8f5b; align-items:center; justify-content:center; flex-direction:column; color:#0a8f5b; font-size:1rem; pointer-events:none; } .pvm-drop i { font-size:2.6rem; }
    .pvm-gridwrap.drag .pvm-drop { display:flex; }
    .pvm-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:10px; } .pvm-grid.busy { opacity:.5; }
    .pvm-tile { position:relative; border:2px solid transparent; border-radius:10px; background:rgba(127,127,127,.1); cursor:pointer; overflow:hidden; display:flex; flex-direction:column; }
    .pvm-tile img, .pvm-tile .ic { width:100%; aspect-ratio:1; object-fit:cover; background:rgba(127,127,127,.15); display:block; }
    .pvm-tile .ic { display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:2rem; color:#6b7a88; } .pvm-tile .ic em { font-style:normal; font-size:.7rem; font-weight:800; }
    .pvm-tile b { padding:5px 8px 0; font-size:.78rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; } .pvm-tile small { padding:0 8px 6px; color:#8b95a1; font-size:.7rem; }
    .pvm-tile.sel { border-color:#0a8f5b; box-shadow:0 0 0 2px rgba(10,143,91,.25); } .pvm-tile .chk { position:absolute; right:6px; top:6px; width:22px; height:22px; border-radius:50%; background:#0a8f5b; color:#fff; display:none; align-items:center; justify-content:center; } .pvm-tile.sel .chk { display:flex; }
    .pvm-tile.folder { padding:14px 10px; align-items:flex-start; gap:2px; min-height:92px; } .pvm-tile.folder > i { font-size:2rem; color:#f5b301; } .pvm-tile.folder b, .pvm-tile.folder small { padding:0; }
    .pvm-fm { position:absolute; right:6px; top:6px; padding:2px 8px; border-radius:8px; } .pvm-fm:hover { background:rgba(127,127,127,.3); }
    .pvm-side { width:290px; flex:none; border-left:1px solid rgba(127,127,127,.25); padding:12px; overflow-y:auto; max-height:68vh; display:flex; flex-direction:column; gap:8px; }
    .pvm-prev { border-radius:10px; overflow:hidden; background:rgba(127,127,127,.12); text-align:center; } .pvm-prev img, .pvm-prev video { max-width:100%; max-height:200px; display:block; margin:0 auto; } .pvm-prev audio { width:100%; margin:14px 0; } .pvm-prev .ic.big { font-size:3rem; padding:26px; color:#6b7a88; }
    .pvm-meta div { color:#8b95a1; font-size:.76rem; } .pvm-meta b { word-break:break-all; }
    .pvm-side label { display:block; font-size:.74rem; font-weight:700; color:#8b95a1; } .pvm-side label input { width:100%; margin-top:3px; font-weight:400; color:inherit; } .pvm-url { display:flex; gap:4px; margin-top:3px; } .pvm-url input { margin:0 !important; min-width:0; }
    .pvm-act { display:flex; flex-wrap:wrap; gap:6px; margin-top:4px; }
    .pvm-fit { border-radius:8px; padding:7px 9px; font-size:.78rem; display:flex; gap:6px; } .pvm-fit.ok, .pvm-ffit.ok { color:#0a7a4f; background:rgba(10,143,91,.12); } .pvm-fit.warn, .pvm-ffit.warn { color:#a15c00; background:rgba(245,158,11,.15); }
    .pvm-spec { font-size:.76rem; color:#8b95a1; } .pvm-note { font-weight:700; color:#0a8f5b; }
    .pvm-empty { color:#8b95a1; text-align:center; padding:18px 6px; } .pvm-empty.big { grid-column:1/-1; padding:60px 10px; } .pvm-empty.big i { display:block; font-size:2.6rem; margin-bottom:6px; }
    .pvm-more { text-align:center; padding:12px; } .pvm-prog { margin-bottom:8px; } .pvm-up-row .bar { height:6px; background:rgba(127,127,127,.25); border-radius:4px; overflow:hidden; } .pvm-up-row .bar i { display:block; height:100%; background:#0a8f5b; transition:width .2s; } .pvm-up-row span { font-size:.78rem; color:#8b95a1; }
    .pvm-err { color:#d93036; font-size:.8rem; margin-top:4px; }
    .pvm-modal .modal-content { min-height:70vh; } .pvm-modal .pvm-host { height:100%; }
    @media (max-width: 760px) { .pvm-main { flex-direction:column; } .pvm-side { width:auto; border-left:0; border-top:1px solid rgba(127,127,127,.25); max-height:none; } .pvm-gridwrap { max-height:46vh; } }

    /* "where to?" window and the folder menu */
    .pvm-pathwin { position:fixed; inset:0; z-index:20000; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; padding:14px; }
    .pvm-pathwin .box { background:var(--bs-body-bg, #fff); color:var(--bs-body-color, #1b2a41); border-radius:14px; width:min(520px, 100%); max-height:88vh; display:flex; flex-direction:column; padding:16px; gap:10px; box-shadow:0 20px 50px rgba(0,0,0,.35); }
    .pvm-pathwin h6 { margin:0; font-weight:800; word-break:break-word; }
    .pvm-pathbar { display:flex; gap:8px; align-items:center; background:rgba(127,127,127,.12); border-radius:8px; padding:6px 10px; font-size:.8rem; } .pvm-pathbar .lbl { font-weight:800; color:#8b95a1; } .pvm-pathbar code { color:inherit; word-break:break-word; }
    .pvm-pathwin .tree { overflow-y:auto; min-height:180px; max-height:42vh; border:1px solid rgba(127,127,127,.3); border-radius:10px; padding:4px; }
    .pvm-pathwin .row { display:flex; align-items:center; gap:7px; padding:6px 10px; border-radius:8px; cursor:pointer; margin:0; font-size:.88rem; } .pvm-pathwin .row:hover { background:rgba(127,127,127,.12); } .pvm-pathwin .row.on { background:rgba(10,143,91,.18); font-weight:700; } .pvm-pathwin .row.off { opacity:.4; cursor:not-allowed; }
    .pvm-pathwin .row .bi-folder-fill { color:#f5b301; } .pvm-pathwin .row .tg { width:14px; font-size:.7rem; opacity:.7; } .pvm-pathwin .row em { font-size:.72rem; opacity:.7; }
    .pvm-pathwin .nm { display:block; font-size:.78rem; font-weight:700; color:#8b95a1; } .pvm-pathwin .nm input { width:100%; margin-top:3px; border:1px solid rgba(127,127,127,.4); border-radius:8px; padding:7px 10px; background:transparent; color:inherit; font-weight:400; font-size:.9rem; }
    .pvm-pathwin .err { color:#d93036; font-size:.8rem; min-height:1em; } .pvm-pathwin .act { display:flex; justify-content:flex-end; gap:8px; }
    .pvm-pop { position:fixed; z-index:20001; background:var(--bs-body-bg, #fff); color:var(--bs-body-color, #1b2a41); border:1px solid rgba(127,127,127,.35); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,.25); padding:6px; width:230px; }
    .pvm-pop button { display:flex; align-items:center; gap:9px; width:100%; border:0; background:transparent; color:inherit; padding:7px 10px; border-radius:7px; font-size:.84rem; text-align:left; } .pvm-pop button:hover { background:rgba(127,127,127,.14); } .pvm-pop button.red { color:#d93036; } .pvm-pop hr { margin:4px 0; opacity:.2; }
    .pvm-ok { background:rgba(10,143,91,.14); color:#0a7a4f; border-radius:8px; padding:7px 10px; font-size:.82rem; margin-bottom:6px; }

    /* styled dialogs (replace the browser's prompt / confirm / alert) */
    .pvm-dlg { position:fixed; inset:0; z-index:30000; background:rgba(0,0,0,.55); display:flex; align-items:center; justify-content:center; padding:14px; }
    .pvm-dlg .box { background:var(--bs-body-bg, #fff); color:var(--bs-body-color, #1b2a41); border-radius:16px; width:min(460px, 100%); padding:20px; box-shadow:0 24px 60px rgba(0,0,0,.4); display:flex; flex-direction:column; gap:12px; animation:pvmpop .15s ease-out; }
    @keyframes pvmpop { from { transform:scale(.96); opacity:0; } }
    .pvm-dlg .hd { display:flex; align-items:center; gap:10px; } .pvm-dlg .hd i { font-size:1.5rem; color:#0a8f5b; } .pvm-dlg .hd i.red { color:#d93036; } .pvm-dlg h6 { margin:0; font-weight:800; font-size:1.05rem; word-break:break-word; }
    .pvm-dlg .msg { font-size:.9rem; color:#6b7684; word-break:break-word; }
    .pvm-dlg label { display:block; font-size:.78rem; font-weight:700; color:#8b95a1; } .pvm-dlg label input[type=text] { width:100%; margin-top:4px; border:1px solid rgba(127,127,127,.45); border-radius:10px; padding:9px 12px; background:transparent; color:inherit; font-size:.95rem; font-weight:400; }
    .pvm-dlg label input[type=text]:focus { outline:2px solid #0a8f5b; border-color:transparent; }
    .pvm-dlg .opt { display:flex; gap:10px; align-items:flex-start; border:1px solid rgba(127,127,127,.35); border-radius:12px; padding:10px 12px; margin:0 0 8px; cursor:pointer; color:inherit; font-weight:400; } .pvm-dlg .opt:has(input:checked) { border-color:#0a8f5b; background:rgba(10,143,91,.08); } .pvm-dlg .opt.dg:has(input:checked) { border-color:#d93036; background:rgba(217,48,54,.08); }
    .pvm-dlg .opt b { display:block; font-size:.9rem; color:inherit; } .pvm-dlg .opt small { display:block; color:#8b95a1; font-size:.78rem; margin-top:2px; font-weight:400; }
    .pvm-dlg .tc { background:rgba(217,48,54,.07); border:1px solid rgba(217,48,54,.3); border-radius:12px; padding:10px 12px; } .pvm-dlg .tc label { color:#d93036; } .pvm-dlg .tc b { letter-spacing:1px; }
    .pvm-dlg .err { color:#d93036; font-size:.8rem; min-height:0; } .pvm-dlg .err:empty { display:none; }
    .pvm-dlg .act { display:flex; justify-content:flex-end; gap:8px; margin-top:4px; } .pvm-dlg .pvm-btn { padding:8px 16px; font-size:.88rem; }
    .pvm-btn.redfill { background:#d93036; border-color:#d93036; color:#fff; } .pvm-btn.redfill:disabled, .pvm-btn.pri:disabled { opacity:.45; cursor:not-allowed; }
    .pvm-btn.on { background:rgba(217,48,54,.12); border-color:#d93036; color:#d93036; } .pvm-btn .cnt { font-size:.72rem; opacity:.8; }
    .pvm-crumbs .red { color:#d93036; } .pvm-tile.trashed { background:rgba(127,127,127,.08); } .pvm-tile.trashed > i { color:#9aa3ad; } .pvm-tile small { word-break:break-word; }

    /* a library field inside a form */
    .pvm-field { display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap; }
    .pvm-fprev { width:150px; flex:none; } .pvm-fprev img { width:100%; border-radius:10px; border:1px solid rgba(127,127,127,.3); display:block; } .pvm-fprev .ph { aspect-ratio:16/9; border:2px dashed rgba(127,127,127,.4); border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#8b95a1; font-size:1.6rem; text-align:center; padding:6px; } .pvm-fprev .ph span { font-size:.7rem; word-break:break-all; }
    .pvm-fbody { flex:1; min-width:220px; } .pvm-fhint { font-size:.76rem; color:#8b95a1; margin-top:6px; } .pvm-ffit { font-size:.78rem; margin-top:6px; border-radius:8px; padding:0; } .pvm-ffit.ok, .pvm-ffit.warn { padding:6px 9px; }
    .pvm-auto { display:flex; flex-direction:column; gap:6px; align-items:flex-start; } .pvm-chosen { display:flex; flex-wrap:wrap; gap:8px; } .pvm-none { color:#8b95a1; font-size:.85rem; }
    .pvm-chip { display:flex; align-items:center; gap:8px; border:1px solid rgba(127,127,127,.3); border-radius:10px; padding:4px 10px 4px 4px; font-size:.8rem; } .pvm-chip img { width:54px; height:40px; object-fit:cover; border-radius:6px; } .pvm-chip > i { font-size:1.4rem; padding:0 6px; }
</style>
<script>window.PVMEDIA = { admin: @json(\App\Support\StaffAccess::isAdmin(auth()->user())), base: @json(url('admin/media')), csrf: @json(csrf_token()), specs: @json(config('media.specs')) };</script>
<script src="{{ asset('js/pv-media.js') }}?v=1"></script>
@endif
