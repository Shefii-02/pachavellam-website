<style>
    .pg-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin-bottom:14px; } .pg-chips::-webkit-scrollbar { display:none; }
    .pg-chip { flex:none; border:1px solid var(--pv-border, #d5dbe6); background:var(--pv-chip, #fff); border-radius:20px; padding:5px 16px; font-size:.85rem; font-weight:600; cursor:pointer; color:var(--pv-text, #1c2b14); } .pg-chip.on { background:var(--pv-text, #1c2b14); color:var(--pv-surface, #fff); border-color:var(--pv-text, #1c2b14); }
    .cc-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    @media (min-width:768px) { .cc-grid { grid-template-columns:repeat(3, 1fr); } } @media (min-width:1100px) { .cc-grid { grid-template-columns:repeat(4, 1fr); } }
    .cc-card { display:block; background:var(--pv-surface, #fff); border-radius:14px; overflow:hidden; text-decoration:none !important; color:var(--pv-text, #1c2b14); box-shadow:var(--pv-shadow, 0 2px 10px rgba(0,0,0,.09)); height:100%; transition:transform .15s; } .cc-card:hover { transform:translateY(-3px); color:var(--pv-text, #1c2b14); }
    .cc-th { aspect-ratio:16/10; background:linear-gradient(135deg,#1c2b14,#4f7d1b) center/cover; position:relative; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.7); font-size:2.4rem; }
    .cc-ex { position:absolute; left:8px; top:8px; color:#fff; font-size:.66rem; font-weight:700; padding:2px 9px; border-radius:10px; }
    .cc-pin { position:absolute; right:8px; top:8px; background:#fff; color:#f59e0b; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:.8rem; }
    .cc-bd { padding:9px 11px 11px; } .cc-dt { font-size:.72rem; color:var(--pv-muted, #6b7a90); margin-bottom:3px; } .cc-tt { font-weight:700; font-size:.9rem; line-height:1.25; height:3.75em; overflow:hidden; }
</style>
