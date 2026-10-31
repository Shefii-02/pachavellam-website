<style>
        .sr-xc { display:block; text-decoration:none !important; color:var(--pv-text) !important; } .sr-xc.lock { opacity:.9; }
        .omr-dr { border-top:1px solid var(--pv-border); padding:6px 0; } .omr-dr > summary { cursor:pointer; font-size:.82rem; font-weight:700; color:var(--pv-text); list-style:none; } .omr-dr > summary::-webkit-details-marker { display:none; } .omr-dr > summary::after { content:'\F282'; font-family:'bootstrap-icons'; float:right; transition:transform .2s; } .omr-dr[open] > summary::after { transform:rotate(180deg); }
        .omr-body { padding-top:6px; color:var(--pv-muted); } .omr-body .ol { display:flex; justify-content:space-between; gap:8px; padding:3px 0; border-bottom:1px dashed var(--pv-border); } .omr-body .ol b { color:var(--pv-text); }
        .omr-lb-btn { display:flex; align-items:center; gap:8px; width:100%; border:0; border-radius:12px; padding:9px 12px; font-size:.84rem; font-weight:800; color:#5b3b00; background:linear-gradient(135deg,#ffe29a,#ffc247); cursor:pointer; }
        a.omr-lb-btn { text-decoration:none !important; color:#5b3b00 !important; }
        #lbDrawer { --bs-offcanvas-width:420px; max-width:100vw; background:var(--pv-surface); color:var(--pv-text); }
        #lbDrawer .lb-head { background:linear-gradient(160deg,#1b2a41,#0a8f5b); color:#fff; padding:14px 16px 0; }
        #lbDrawer .lb-head h5 { font-size:1rem; font-weight:800; margin:0;color:#fff; } #lbDrawer .lb-head small { opacity:.8; }
        .lb-podium { display:flex; align-items:flex-end; justify-content:center; gap:8px; padding:16px 6px 0; min-height:190px; }
        .lb-pd { flex:1; max-width:120px; text-align:center; color:#fff; } .lb-pd .av { width:46px; height:46px; margin:0 auto 4px; border-radius:50%; background:#fff; color:#1b2a41; font-weight:800; display:flex; align-items:center; justify-content:center; border:3px solid var(--c); position:relative; }
        .lb-pd.p1 .av { width:58px; height:58px; font-size:1.2rem; } .lb-pd .crown { position:absolute; top:-18px; left:50%; transform:translateX(-50%); color:#ffd23f; font-size:1.2rem; }
        .lb-pd .nm { font-size:.76rem; font-weight:700; line-height:1.2; max-height:2.4em; overflow:hidden; } .lb-pd .mk { font-size:.72rem; opacity:.85; }
        .lb-pd .bar { margin-top:6px; border-radius:10px 10px 0 0; background:var(--c); color:#1b2a41; font-weight:900; font-size:1.3rem; display:flex; align-items:center; justify-content:center; } .lb-pd.p1 .bar { height:74px; } .lb-pd.p2 .bar { height:54px; } .lb-pd.p3 .bar { height:40px; }
        .lb-pd.p1 { --c:#ffc83d; } .lb-pd.p2 { --c:#cfd6df; } .lb-pd.p3 { --c:#e0a070; } .lb-pd.me .av { box-shadow:0 0 0 3px #22c55e; }
        #lbList { flex:1; overflow-y:auto; padding:8px 10px; }
        .lb-row { display:flex; align-items:center; gap:10px; padding:9px 10px; border-radius:12px; margin-bottom:5px; background:var(--pv-surface-2); }
        .lb-row .rk { width:30px; text-align:center; font-weight:800; color:var(--pv-muted); } .lb-row .nm { flex:1; min-width:0; font-weight:700; font-size:.88rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; } .lb-row small { color:var(--pv-muted); display:block; font-weight:500; font-size:.7rem; }
        .lb-row .mk { font-weight:800; color:#0a8f5b; }
        .lb-row.me { background:#e7f6ef; border:2px solid #22c55e; box-shadow:0 0 0 3px rgba(34,197,94,.25); } .lb-row.me .nm { color:#0a6b46; }
        #lbMe { position:sticky; bottom:0; z-index:3; box-shadow:0 -6px 16px rgba(0,0,0,.15); border-top:2px solid #22c55e; background:#e7f6ef; padding:10px 14px; display:none; } #lbMe .lb-row { margin:0; background:transparent; padding:0; } #lbMe .tag { font-size:.68rem; font-weight:800; color:#0a6b46; text-transform:uppercase; letter-spacing:.05em; }
        .lb-msg { text-align:center; color:var(--pv-muted); padding:24px 10px; font-size:.88rem; }
        .sr-pg { display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:.85rem; color:var(--pv-muted); }
        #lbSub{
            font-size:.78rem; color:var(--pv-surface); display:block; margin-top:2px;
        }
</style>
