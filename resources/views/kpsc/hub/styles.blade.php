{{-- Shared look of the bottom-bar tabs (Exams, Courses, Learn, Profile). Colours come from partials.theme. --}}
<style>
    .header-area { position: static !important; } .page-content-wrapper { margin-top: 0 !important; }
    .hb-wrap { max-width: 980px; margin: 0 auto; padding: 16px 0 10px; }
    .hb-top { display:flex; align-items:flex-end; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .hb-top h1 { font-size:1.5rem; font-weight:800; margin:0; color:var(--pv-text); } .hb-top p { margin:2px 0 0; color:var(--pv-muted); font-size:.88rem; }
    .hb-sec { margin:22px 0 8px; } .hb-sec > h2 { font-size:1.08rem; font-weight:800; color:var(--pv-text); margin:0 0 10px; display:flex; align-items:center; gap:8px; }
    .hb-sec > h2 .ct { background:var(--pv-surface-2); color:var(--pv-muted); font-size:.72rem; border-radius:10px; padding:1px 8px; }
    .hb-note { border-radius:14px; padding:12px 14px; font-size:.88rem; display:flex; gap:10px; align-items:center; flex-wrap:wrap; background:var(--pv-brand-soft); color:var(--pv-text); margin-bottom:12px; }
    .hb-note i { color:var(--pv-brand); font-size:1.2rem; } .hb-note .sp { flex:1 1 200px; }
    .hb-note.ok { background:color-mix(in srgb, #0a8f5b 14%, var(--pv-surface)); } .hb-note.ok i { color:#0a8f5b; }
    .hb-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; border:0; border-radius:22px; padding:8px 18px; font-weight:700; font-size:.85rem; cursor:pointer; text-decoration:none !important; background:var(--pv-green); color:var(--pv-on-brand) !important; white-space:nowrap; }
    .hb-btn.ghost { background:var(--pv-surface); border:1px solid var(--pv-border); color:var(--pv-text) !important; }
    .hb-btn.on { background:var(--pv-text); color:var(--pv-surface) !important; }
    .hb-btn.sm { padding:5px 12px; font-size:.78rem; }
    .hb-btn:disabled { opacity:.6; cursor:wait; }
    .hb-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin-bottom:10px; padding:1px; } .hb-chips::-webkit-scrollbar { display:none; }
    .hb-chip { flex:none; border:1px solid var(--pv-border); background:var(--pv-chip); border-radius:20px; padding:6px 15px; font-size:.84rem; font-weight:600; color:var(--pv-text) !important; text-decoration:none !important; cursor:pointer; }
    .hb-chip.on, .hb-chip[aria-selected="true"] { background:var(--pv-text); color:var(--pv-surface) !important; border-color:var(--pv-text); }
    .hb-chip .n { opacity:.7; font-size:.72rem; margin-left:4px; }
    .hb-empty { border:1px dashed var(--pv-border); border-radius:14px; padding:22px 14px; text-align:center; color:var(--pv-muted); font-size:.9rem; }
    .hb-empty i { display:block; font-size:1.8rem; margin-bottom:4px; }
    .hb-empty a { color:var(--pv-brand); font-weight:700; }
    [role="tabpanel"][hidden], .hb-hide { display:none !important; }

    /* course cards */
    .hb-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); }
    .hb-course { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; overflow:hidden; display:flex; flex-direction:column; color:var(--pv-text); }
    .hb-course .cv { aspect-ratio:16/9; background:linear-gradient(135deg,#1c3a0c,#4f7d1b) center/cover; position:relative; display:block; }
    .hb-course .ex { position:absolute; left:10px; top:10px; color:#fff; font-size:.68rem; font-weight:700; padding:2px 10px; border-radius:10px; }
    .hb-course .bdg { position:absolute; right:10px; top:10px; font-size:.68rem; font-weight:800; padding:2px 9px; border-radius:10px; background:var(--pv-green); color:var(--pv-on-brand); }
    .hb-course .bd { padding:12px 14px 14px; display:flex; flex-direction:column; gap:5px; flex:1; }
    .hb-course .tt { font-weight:700; line-height:1.3; color:var(--pv-text); text-decoration:none; }
    .hb-course .mt { font-size:.76rem; color:var(--pv-muted); }
    .hb-course .pr { font-weight:800; font-size:1.1rem; color:var(--pv-brand); } .hb-course .pr s { color:var(--pv-muted); font-weight:400; font-size:.82rem; margin-left:6px; }
    .hb-course .act { display:flex; gap:8px; margin-top:auto; padding-top:8px; } .hb-course .act .hb-btn { flex:1; }
    .hb-bar { height:6px; border-radius:4px; background:var(--pv-surface-2); overflow:hidden; } .hb-bar i { display:block; height:100%; background:var(--pv-green); }

    /* list rows (classes, tests, articles) */
    .hb-list { display:grid; gap:10px; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); }
    .hb-row { display:flex; gap:12px; align-items:center; background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; padding:10px; color:var(--pv-text); text-decoration:none !important; }
    .hb-row:hover { border-color:var(--pv-brand); color:var(--pv-text); }
    .hb-row .th { width:96px; aspect-ratio:16/10; border-radius:10px; flex:none; background:linear-gradient(135deg,#1c3a0c,#4f7d1b) center/cover; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.85); font-size:1.4rem; }
    .hb-row .tx { min-width:0; flex:1; } .hb-row b { display:block; font-size:.92rem; line-height:1.25; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .hb-row small { color:var(--pv-muted); font-size:.76rem; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .hb-row .when { font-size:.75rem; font-weight:700; color:var(--pv-brand); font-variant-numeric:tabular-nums; }
    .hb-row .when.now { color:#0a8f5b; }
    .hb-tag { display:inline-block; font-size:.66rem; font-weight:700; border-radius:8px; padding:1px 7px; background:var(--pv-brand-soft); color:var(--pv-brand); margin-right:4px; }

    .hb-toast { position:fixed; left:50%; bottom:100px; transform:translateX(-50%); background:var(--pv-text); color:var(--pv-surface); padding:10px 18px; border-radius:22px; font-size:.86rem; font-weight:600; z-index:2000; box-shadow:0 6px 20px rgba(0,0,0,.25); max-width:90vw; }
</style>
<script>
    /* small helpers shared by the tabs: POST with the CSRF token, and a toast */
    window.pvPost = function (url) {
        return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (r.status === 401) { location.href = url; throw new Error('login'); } if (!r.ok) throw new Error('fail'); return r.json(); });
    };
    window.pvToast = function (t) {
        var d = document.createElement('div'); d.className = 'hb-toast'; d.setAttribute('role', 'status'); d.textContent = t; document.body.appendChild(d);
        setTimeout(function () { d.remove(); }, 2600);
    };
</script>
