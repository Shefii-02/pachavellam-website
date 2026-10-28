@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title ?? 'KPSC - Home Page')
@section('styles')
    @include('kpsc.partials.public-styles')
    <style>
        .hm-circles { display:flex; gap:16px; overflow-x:auto; scrollbar-width:none; padding:4px 2px 8px; } .hm-circles::-webkit-scrollbar { display:none; }
        .hm-circ { flex:none; width:84px; text-align:center; text-decoration:none !important; color:var(--pv-text, #1b2a41); font-size:.8rem; font-weight:700; }
        .hm-circ i { width:68px; height:68px; border-radius:50%; margin:0 auto 6px; display:flex; align-items:center; justify-content:center; color:#fff; font-style:normal; font-size:1.05rem; font-weight:800; box-shadow:0 3px 10px rgba(0,0,0,.18); }
        /* header scrolls away with the page on the home screen */
        .header-area { position: static !important; }
        .page-content-wrapper { margin-top: 0 !important; }

        .hm-sec { margin: 34px 0 6px; }
        .hm-head { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
        .hm-head h4, .hm-title { margin:0; font-weight:800; font-size:1.25rem; color:var(--pv-text); letter-spacing:-.01em; }
        @media (min-width:768px) { .hm-head h4, .hm-title { font-size:1.6rem; } }
        .hm-title em, .hm-head h4 em { font-style:normal; color:var(--pv-brand); }
        .hm-head small, .hm-sub { color:var(--pv-muted); }
        .hm-head a.small { color:var(--pv-brand); }
        .hm-pill { display:inline-block; background:var(--pv-brand-soft); color:var(--pv-brand); font-size:.72rem; font-weight:700; border-radius:20px; padding:3px 12px; margin-bottom:6px; }
        .hm-strip { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; padding:2px 2px 10px; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
        .hm-strip::-webkit-scrollbar { display:none; }
        .hm-strip > * { scroll-snap-align:start; flex:none; }
        .hm-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin-bottom:10px; } .hm-chips::-webkit-scrollbar { display:none; }
        .hm-chip { flex:none; border:1px solid var(--pv-border); background:var(--pv-chip); border-radius:20px; padding:5px 16px; font-size:.85rem; font-weight:600; color:var(--pv-text); cursor:pointer; } .hm-chip.on { background:var(--pv-text); color:var(--pv-surface); border-color:var(--pv-text); }
        .hm-btn { display:inline-flex; align-items:center; gap:6px; border:0; border-radius:24px; padding:9px 22px; font-weight:700; font-size:.88rem; color:var(--pv-on-brand) !important; text-decoration:none !important; background:linear-gradient(90deg,#c3e27a,#a7d04f 45%,#7fae2e); box-shadow:0 4px 14px rgba(111,163,43,.35); transition:transform .15s; }
        .hm-btn:hover { transform:translateY(-2px); }

        /* hero */
        .hm-hero { position:relative; margin-top:14px; }
        .hm-hero .track { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; scrollbar-width:none; border-radius:16px; }
        .hm-hero .track::-webkit-scrollbar { display:none; }
        .hm-hero .track a { flex:none; width:100%; scroll-snap-align:center; }
        @media (min-width:992px) { .hm-hero .track a { width:calc((100% - 24px) / 3); scroll-snap-align:start; } }
        .hm-hero img { border-radius:14px; width:100%; display:block; }
        .hm-hero .arr { position:absolute; top:50%; transform:translateY(-50%); width:36px; height:36px; border-radius:50%; border:0; background:var(--pv-surface); color:var(--pv-text); box-shadow:0 2px 8px rgba(0,0,0,.25); display:none; }
        .hm-hero .arr.l { left:-10px; } .hm-hero .arr.r { right:-10px; }
        @media (min-width:768px) { .hm-hero .arr { display:block; } }
        .hm-dots { display:flex; justify-content:center; gap:6px; margin-top:8px; } .hm-dots i { width:7px; height:7px; border-radius:50%; background:var(--pv-border); } .hm-dots i.on { width:20px; border-radius:6px; background:var(--pv-brand-2); }
        .st-strip { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; background:linear-gradient(135deg,#1c3a0c,#2f5410 60%,#3d6b12); border-radius:18px; padding:16px 12px; color:#fff; text-align:center; }
        @media (min-width:768px) { .st-strip { grid-template-columns:repeat(4,1fr); } }
        .st-strip b { display:block; font-size:1.5rem; font-weight:800; color:#a7d04f; } .st-strip span { font-size:.78rem; opacity:.9; }

        /* ---------- What are you preparing for? (exam type tabs, category cards) */
        .gp-tabs { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; position:relative; z-index:2; }
        @media (max-width:767px) { .gp-tabs { display:flex; overflow-x:auto; scrollbar-width:none; } .gp-tabs::-webkit-scrollbar { display:none; } .gp-tab { flex:0 0 165px; } }
        .gp-tab { --ex:#5b8a1f; display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left; border:1.5px solid var(--ex); background:var(--pv-surface); border-radius:14px; padding:10px 10px 10px 14px; min-height:84px; cursor:pointer; color:var(--pv-muted); font-weight:600; font-size:.92rem; line-height:1.2; transition:background .15s; }
        .gp-tab img, .gp-tab .ph { width:62px; height:62px; border-radius:12px; object-fit:cover; flex:none; background:var(--pv-surface-2); display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--ex); }
        .gp-tab[aria-selected="true"] { color:var(--ex); background:var(--pv-surface-2); background:color-mix(in srgb, var(--ex) 10%, var(--pv-surface)); border-bottom-color:transparent; border-radius:14px 14px 0 0; margin-bottom:-12px; padding-bottom:22px; font-weight:800; }
        @media (max-width:767px) { .gp-tab[aria-selected="true"] { margin-bottom:0; padding-bottom:10px; border-radius:14px; border-bottom-color:var(--ex); } }
        .gp-tab:focus-visible { outline:3px solid color-mix(in srgb, var(--ex) 45%, transparent); outline-offset:2px; }
        .gp-panel { --ex:#5b8a1f; border:1.5px solid var(--ex); border-radius:0 16px 16px 16px; background:var(--pv-surface-2); background:color-mix(in srgb, var(--ex) 10%, var(--pv-surface)); padding:18px; }
        @media (max-width:767px) { .gp-panel { border-radius:16px; margin-top:10px; padding:12px; } }
        .gp-panel[hidden] { display:none; }
        .gp-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
        .gp-card { grid-column:span 2; position:relative; overflow:hidden; background:var(--pv-surface); border-radius:14px; padding:16px; display:flex; flex-direction:column; min-height:150px; box-shadow:var(--pv-shadow); color:var(--pv-text); text-decoration:none !important; transition:transform .15s; }
        .gp-card:hover { transform:translateY(-3px); color:var(--pv-text); }
        .gp-card.big { grid-column:span 3; min-height:200px; padding-right:150px; }
        @media (max-width:991px) { .gp-card, .gp-card.big { grid-column:span 3; } }
        @media (max-width:575px) { .gp-card, .gp-card.big { grid-column:span 6; padding-right:16px; } .gp-card.big { padding-right:120px; } }
        .gp-card .hd { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
        .gp-card .ic { width:44px; height:44px; border-radius:10px; flex:none; background:color-mix(in srgb, var(--ex) 14%, var(--pv-surface)); color:var(--ex); display:flex; align-items:center; justify-content:center; font-size:1.25rem; overflow:hidden; }
        .gp-card .ic img { width:100%; height:100%; object-fit:cover; }
        .gp-card b { font-size:1.08rem; font-weight:700; } .gp-card.big b { font-size:1.3rem; }
        .gp-card .subs { font-size:.78rem; color:var(--pv-muted); line-height:1.5; margin-bottom:12px; }
        .gp-card .go { margin-top:auto; font-size:.8rem; font-weight:700; color:var(--pv-text); }
        .gp-card.big .go { align-self:flex-start; }
        .gp-card .pic { position:absolute; right:0; bottom:0; width:140px; height:85%; object-fit:contain; object-position:bottom right; }
        @media (max-width:575px) { .gp-card .pic { width:110px; } }

        /* ---------- Try out our live experience */
        .lx-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
        .lx-tab { border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-text); border-radius:24px; padding:7px 16px; font-weight:600; font-size:.88rem; display:inline-flex; align-items:center; gap:8px; cursor:pointer; }
        .lx-tab span { background:var(--pv-surface-2); color:var(--pv-muted); border-radius:10px; font-size:.7rem; padding:1px 7px; font-weight:700; }
        .lx-tab[aria-selected="true"] { background:var(--pv-text); color:var(--pv-surface); border-color:var(--pv-text); } .lx-tab[aria-selected="true"] span { background:var(--pv-green); color:var(--pv-on-brand); }
        .lx-filter { margin-left:auto; display:flex; align-items:center; gap:6px; color:var(--pv-muted); font-size:.85rem; }
        .lx-filter select { border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-text); border-radius:20px; padding:5px 12px; font-size:.85rem; }
        .lx-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:14px; }
        .lx-card { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; overflow:hidden; display:flex; flex-direction:column; color:var(--pv-text); text-decoration:none !important; transition:transform .15s, box-shadow .15s; }
        .lx-card:hover { transform:translateY(-3px); box-shadow:var(--pv-shadow); color:var(--pv-text); }
        .lx-card .th { aspect-ratio:16/9; background:linear-gradient(135deg,#1c3a0c,#4f7d1b) center/cover; position:relative; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.85); font-size:2.2rem; }
        .lx-card .th .play { width:46px; height:46px; border-radius:50%; background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; font-size:1.3rem; color:#fff; }
        .lx-card .bd { padding:12px 14px 14px; display:flex; flex-direction:column; gap:4px; flex:1; }
        .lx-card .tt { font-weight:700; font-size:.98rem; line-height:1.3; }
        .lx-card .by { font-size:.8rem; color:var(--pv-muted); }
        .lx-card .ft { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:auto; padding-top:10px; }
        .lx-tag { background:color-mix(in srgb, #2563eb 12%, var(--pv-surface)); color:#2563eb; font-size:.7rem; font-weight:600; padding:3px 10px; border-radius:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:55%; }
        [data-theme="dark"] .lx-tag { color:#8fb4ff; }
        .lx-when { font-size:.8rem; font-weight:700; color:var(--pv-brand); white-space:nowrap; font-variant-numeric:tabular-nums; }
        .lx-when.now { color:#0a8f5b; } .lx-when.now::before { content:''; display:inline-block; width:8px; height:8px; border-radius:50%; background:#0a8f5b; margin-right:5px; animation:lxPulse 1.2s infinite; }
        @keyframes lxPulse { 50% { opacity:.25; } }
        .lx-card[hidden], .lx-empty[hidden], [role="tabpanel"][hidden] { display:none !important; }
        .lx-empty { border:1px dashed var(--pv-border); border-radius:14px; padding:28px 16px; text-align:center; color:var(--pv-muted); }
        .lx-empty i { font-size:2rem; display:block; margin-bottom:6px; }

        /* ---------- capsules */
        .cp-card { width:200px; height:250px; border-radius:18px; overflow:hidden; position:relative; border:0; padding:0; text-align:left; cursor:pointer; background:var(--pv-surface); box-shadow:var(--pv-shadow); color:var(--pv-text); }
        @media (min-width:768px) { .cp-card { width:220px; height:280px; } }
        .cp-card img { width:100%; height:100%; object-fit:cover; display:block; }
        .cp-card .note { height:100%; padding:16px; display:flex; flex-direction:column; background:linear-gradient(160deg, var(--pv-brand-soft), var(--pv-surface) 70%); border:1px solid var(--pv-border); border-radius:18px; }
        .cp-card .note i { font-size:1.4rem; color:var(--pv-brand); }
        .cp-card .note p { margin:8px 0 0; font-size:.86rem; line-height:1.5; color:var(--pv-text); display:-webkit-box; -webkit-line-clamp:8; -webkit-box-orient:vertical; overflow:hidden; }
        .cp-card .tg { position:absolute; left:10px; bottom:10px; background:var(--pv-green); color:var(--pv-on-brand); font-size:.68rem; font-weight:700; padding:2px 10px; border-radius:10px; max-width:calc(100% - 20px); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .cp-card:hover { transform:translateY(-3px); transition:transform .15s; }
        .cp-card:focus-visible { outline:3px solid var(--pv-green); outline-offset:2px; }
        .cp-view { position:fixed; inset:0; z-index:3000; background:rgba(5,12,6,.82); display:flex; align-items:center; justify-content:center; padding:16px; }
        .cp-view[hidden] { display:none; }
        .cp-view .box { position:relative; max-width:min(520px,100%); max-height:88vh; overflow:auto; border-radius:18px; background:var(--pv-surface); color:var(--pv-text); }
        .cp-view .box img { width:100%; display:block; }
        .cp-view .box .tx { padding:22px; font-size:.95rem; line-height:1.6; }
        .cp-view .x { position:absolute; right:10px; top:10px; width:36px; height:36px; border-radius:50%; border:0; background:rgba(0,0,0,.55); color:#fff; font-size:1.1rem; z-index:2; }
        .cp-view .nav { position:absolute; top:50%; transform:translateY(-50%); width:40px; height:40px; border-radius:50%; border:0; background:var(--pv-green); color:var(--pv-on-brand); font-size:1.2rem; }
        .cp-view .nav.p { left:12px; } .cp-view .nav.n { right:12px; }
        @media (max-width:600px) { .cp-view .nav { top:auto; bottom:18px; transform:none; } }

        /* ---------- Prime */
        .pr-band { position:relative; overflow:hidden; border-radius:22px; background:var(--pv-surface); border:1px solid var(--pv-border); padding:26px 22px; display:grid; gap:20px; background-image:linear-gradient(var(--pv-border) 1px, transparent 1px), linear-gradient(90deg, var(--pv-border) 1px, transparent 1px); background-size:34px 34px; background-position:-1px -1px; }
        .pr-band::before { content:''; position:absolute; inset:0; background:linear-gradient(90deg, var(--pv-surface) 35%, color-mix(in srgb, var(--pv-surface) 70%, transparent)); pointer-events:none; }
        .pr-band > * { position:relative; }
        @media (min-width:992px) { .pr-band { grid-template-columns:1.1fr 1fr; align-items:center; padding:36px 40px; } }
        .pr-logo { display:inline-flex; align-items:center; gap:6px; font-weight:800; color:var(--pv-text); font-size:1.05rem; }
        .pr-logo span { background:linear-gradient(90deg,#ffd166,#ffb703); color:#7a3e00; border-radius:4px; padding:1px 8px; transform:skewX(-8deg); }
        .pr-band h3 { font-weight:800; font-size:1.7rem; line-height:1.15; margin:12px 0 16px; color:var(--pv-text); }
        @media (min-width:768px) { .pr-band h3 { font-size:2.2rem; } }
        .pr-band h3 em { font-style:normal; color:var(--pv-brand); }
        .pr-feats { display:grid; gap:12px; margin-bottom:20px; }
        @media (min-width:576px) { .pr-feats { grid-template-columns:1fr 1fr; } }
        .pr-feat { display:flex; gap:12px; align-items:flex-start; }
        .pr-feat i { width:42px; height:42px; border-radius:10px; flex:none; background:var(--pv-brand-soft); color:var(--pv-brand); display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
        .pr-feat b { display:block; font-size:.92rem; color:var(--pv-text); } .pr-feat small { color:var(--pv-muted); font-size:.76rem; line-height:1.35; display:block; }
        .pr-orbit { position:relative; width:min(360px,86vw); aspect-ratio:1; margin:0 auto; }
        .pr-orbit .ring { position:absolute; border-radius:50%; border:1.5px solid color-mix(in srgb, var(--pv-brand) 22%, transparent); }
        .pr-orbit .ring.r1 { inset:0; } .pr-orbit .ring.r2 { inset:16%; }
        .pr-orbit .core { position:absolute; inset:30%; border-radius:50%; background:linear-gradient(135deg,#a7d04f,#3d6b12); color:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; box-shadow:0 10px 30px rgba(79,125,27,.4); }
        .pr-orbit .core i { font-size:2.3rem; line-height:1; } .pr-orbit .core b { font-size:.85rem; margin-top:4px; }
        .pr-orbit .node { position:absolute; width:56px; height:56px; margin:-28px 0 0 -28px; border-radius:50%; background:var(--pv-surface); border:2px solid var(--pv-border); box-shadow:var(--pv-shadow); display:flex; align-items:center; justify-content:center; overflow:hidden; font-size:.7rem; font-weight:800; color:var(--pv-text); animation:prFloat 4s ease-in-out infinite; }
        .pr-orbit .node img { width:100%; height:100%; object-fit:cover; }
        .pr-orbit .badge-chip { position:absolute; background:#1c2b14; color:#fff; font-size:.7rem; font-weight:700; padding:4px 10px; border-radius:14px; white-space:nowrap; box-shadow:0 4px 10px rgba(0,0,0,.2); }
        @keyframes prFloat { 50% { transform:translateY(-6px); } }
        @media (prefers-reduced-motion: reduce) { .pr-orbit .node, .lx-when.now::before { animation:none; } }

        /* ---------- courses */
        .hm-course { width:250px; border-radius:16px; background:var(--pv-surface); box-shadow:var(--pv-shadow); overflow:hidden; text-decoration:none !important; color:var(--pv-text); display:block; }
        .hm-course:hover { color:var(--pv-text); }
        .hm-course .cv { height:130px; background-size:cover; background-position:center; position:relative; background-color:var(--pv-surface-2); }
        .hm-course .tag { position:absolute; left:8px; top:8px; background:var(--pv-green); color:var(--pv-on-brand); font-size:.68rem; font-weight:700; padding:2px 8px; border-radius:10px; }
        .hm-course .ex { position:absolute; right:8px; top:8px; color:#fff; font-size:.68rem; font-weight:700; padding:2px 8px; border-radius:10px; }
        .hm-course .bd { padding:10px 12px 12px; }
        .hm-course .tt { font-weight:700; font-size:.95rem; line-height:1.25; height:2.5em; overflow:hidden; }
        .hm-course .pr { margin-top:6px; font-weight:800; color:#0a8f5b; } .hm-course .pr s { color:var(--pv-muted); font-weight:400; font-size:.8rem; margin-left:4px; }
        .hm-course .mt { font-size:.74rem; color:var(--pv-muted); }

        /* ---------- key points */
        .kp-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
        @media (min-width:768px) { .kp-grid { grid-template-columns:repeat(3,1fr); } } @media (min-width:1100px) { .kp-grid { grid-template-columns:repeat(4,1fr); } }
        .kp-card { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; padding:16px; }
        .kp-card .ic { width:46px; height:46px; border-radius:12px; background:var(--pv-brand-soft); color:var(--pv-brand); display:flex; align-items:center; justify-content:center; font-size:1.3rem; margin-bottom:10px; overflow:hidden; }
        .kp-card .ic img { width:100%; height:100%; object-fit:cover; }
        .kp-card b { display:block; font-size:.95rem; margin-bottom:3px; color:var(--pv-text); } .kp-card p { font-size:.8rem; color:var(--pv-muted); margin:0; line-height:1.45; }

        /* ---------- get the app */
        .hm-getapp { position:relative; overflow:hidden; border-radius:20px; padding:24px 22px; color:#fff; background:linear-gradient(135deg,#4f7d1b 0%,#2f5410 60%,#16300a 100%); display:flex; align-items:center; gap:18px; flex-wrap:wrap; box-shadow:0 8px 24px rgba(47,84,16,.35); }
        .hm-getapp::before, .hm-getapp::after { content:''; position:absolute; border-radius:50%; background:rgba(255,255,255,.08); }
        .hm-getapp::before { width:260px; height:260px; right:-60px; top:-90px; } .hm-getapp::after { width:160px; height:160px; left:-40px; bottom:-70px; }
        .hm-getapp .tx { position:relative; flex:1 1 260px; } .hm-getapp h3 { font-weight:800; margin:0 0 4px; font-size:1.45rem; color:#fff; }
        .hm-getapp .perks { display:grid; grid-template-columns:1fr 1fr; gap:8px 14px; font-size:.82rem; margin:12px 0 14px; opacity:.95; }
        .hm-getapp .perks span { display:flex; gap:6px; align-items:center; } .hm-getapp .perks i { color:#a7d04f; }
        .hm-getapp .btns { display:flex; gap:10px; flex-wrap:wrap; position:relative; }
        .hm-store { display:flex; align-items:center; gap:10px; background:#000; color:#fff !important; border-radius:12px; padding:7px 16px 7px 12px; text-decoration:none !important; border:1px solid rgba(255,255,255,.35); transition:transform .15s; }
        .hm-store:hover { transform:translateY(-2px); } .hm-store i { font-size:1.7rem; } .hm-store small { display:block; font-size:.62rem; opacity:.8; line-height:1; } .hm-store b { font-size:1.02rem; line-height:1.1; }
        .hm-store.off { opacity:.55; pointer-events:none; }
        .qr-box { background:#fff; border-radius:14px; padding:10px; text-align:center; color:#1c2b14; font-size:.72rem; font-weight:600; position:relative; } .qr-box img { width:120px; height:120px; display:block; margin:0 auto 4px; }
        @media (max-width:560px) { .qr-box { display:none; } }

        /* ---------- stories, videos, subjects */
        .hm-story { width:132px; height:210px; border-radius:16px; background-size:cover; background-position:center; position:relative; box-shadow:0 3px 10px rgba(0,0,0,.18); border:3px solid var(--pv-surface); outline:2px solid var(--pv-green); display:block; }
        .hm-story::after { content:""; position:absolute; inset:0; border-radius:13px; background:linear-gradient(transparent 60%, rgba(0,0,0,.35)); }
        @media (min-width:768px) { .hm-story { width:170px; height:270px; } }
        .hm-video { width:240px; position:relative; display:block; }
        .hm-video img { width:100%; border-radius:14px; display:block; }
        .hm-video span { position:absolute; left:8px; bottom:8px; right:8px; font-size:.74rem; color:#fff; background:rgba(10,143,91,.88); padding:3px 10px; border-radius:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        /* ---------- counselling */
        .cs-box { border-radius:22px; overflow:hidden; display:grid; background:var(--pv-surface); border:1px solid var(--pv-border); box-shadow:var(--pv-shadow); }
        @media (min-width:992px) { .cs-box { grid-template-columns:1fr 1.05fr; } }
        .cs-side { background:linear-gradient(140deg,#16300a,#2f5410 60%,#4f7d1b); color:#fff; padding:26px 24px; position:relative; overflow:hidden; }
        .cs-side .bg-ic { position:absolute; right:-14px; bottom:-34px; font-size:10rem; opacity:.08; line-height:1; pointer-events:none; }
        .cs-side h3 { font-weight:800; color:#fff; font-size:1.5rem; margin:6px 0 8px; } .cs-side p { color:rgba(255,255,255,.85); font-size:.9rem; }
        .cs-side ul { list-style:none; padding:0; margin:14px 0 0; display:grid; gap:10px; } .cs-side li { display:flex; gap:10px; font-size:.88rem; } .cs-side li i { color:#a7d04f; font-size:1.05rem; }
        .cs-side .hm-pill { background:rgba(255,255,255,.15); color:#fff; }
        .cs-form { padding:24px; display:grid; gap:12px; grid-template-columns:1fr 1fr; align-content:start; }
        .cs-form .full { grid-column:1 / -1; }
        @media (max-width:575px) { .cs-form { grid-template-columns:1fr; } }
        .cs-form label { font-size:.78rem; font-weight:700; color:var(--pv-muted); margin-bottom:3px; display:block; }
        .cs-form input, .cs-form select, .cs-form textarea { width:100%; border:1px solid var(--pv-border); background:var(--pv-surface-2); color:var(--pv-text); border-radius:10px; padding:9px 12px; font-size:.9rem; }
        .cs-form input:focus, .cs-form select:focus, .cs-form textarea:focus { outline:2px solid color-mix(in srgb, var(--pv-brand) 40%, transparent); border-color:var(--pv-brand); }
        .cs-msg { border-radius:10px; padding:10px 12px; font-size:.86rem; font-weight:600; }
        .cs-msg.ok { background:color-mix(in srgb, #0a8f5b 14%, var(--pv-surface)); color:#0a8f5b; } .cs-msg.err { background:var(--pv-danger-soft); color:var(--pv-danger); }

        /* ---------- founder, team */
        .fd-card { display:flex; gap:16px; background:var(--pv-surface); border-radius:18px; padding:18px; box-shadow:var(--pv-shadow); align-items:flex-start; color:var(--pv-text); }
        .fd-card img { width:84px; height:84px; border-radius:50%; object-fit:cover; flex:none; } .fd-card q { display:block; font-size:.98rem; line-height:1.55; color:var(--pv-text); opacity:.9; }
        .tm-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; } @media (min-width:768px) { .tm-grid { grid-template-columns:repeat(4,1fr); } }
        .tm-card { background:var(--pv-surface); color:var(--pv-text); border-radius:16px; padding:14px 8px; text-align:center; box-shadow:var(--pv-shadow); } .tm-card img, .tm-card .ph { width:76px; height:76px; border-radius:50%; object-fit:cover; margin:0 auto 6px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#1c2b14,#4f7d1b); color:#fff; font-weight:800; font-size:1.6rem; }

        /* ---------- partners */
        .pt-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
        .pt-card { display:flex; align-items:center; justify-content:center; gap:8px; height:78px; background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; padding:10px 14px; color:var(--pv-text); text-decoration:none !important; font-weight:700; font-size:.85rem; text-align:center; transition:transform .15s, box-shadow .15s; }
        .pt-card:hover { transform:translateY(-3px); box-shadow:var(--pv-shadow); color:var(--pv-text); }
        .pt-card img { max-height:46px; max-width:100%; object-fit:contain; filter:grayscale(1); opacity:.85; transition:filter .2s, opacity .2s; }
        .pt-card:hover img { filter:none; opacity:1; }
        [data-theme="dark"] .pt-card img { background:#fff; border-radius:6px; padding:3px 6px; }

        /* ---------- FAQ */
        .fq-wrap { background:var(--pv-surface-2); border-radius:22px; padding:26px 16px; }
        @media (min-width:768px) { .fq-wrap { padding:34px 40px; } }
        .fq-wrap .hm-title { text-align:center; margin-bottom:18px; }
        .fq-tabs { display:flex; gap:10px; overflow-x:auto; scrollbar-width:none; justify-content:flex-start; margin-bottom:18px; padding:2px; }
        @media (min-width:768px) { .fq-tabs { justify-content:center; flex-wrap:wrap; } }
        .fq-tabs::-webkit-scrollbar { display:none; }
        .fq-tab { flex:none; border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-text); border-radius:24px; padding:8px 18px; font-size:.85rem; font-weight:700; cursor:pointer; }
        .fq-tab[aria-selected="true"] { background:var(--pv-text); color:var(--pv-surface); border-color:var(--pv-text); }
        .fq-list[hidden] { display:none; }
        .fq-i { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; margin-bottom:10px; overflow:hidden; transition:border-color .15s; }
        .fq-i.open { border-color:var(--pv-brand); }
        .fq-q { width:100%; text-align:left; border:0; background:none; padding:15px 18px; font-weight:700; font-size:.95rem; display:flex; gap:12px; align-items:center; color:var(--pv-text); }
        .fq-i.open .fq-q { color:var(--pv-brand); }
        .fq-q i { margin-left:auto; color:var(--pv-brand); transition:transform .2s; } .fq-i.open .fq-q i { transform:rotate(180deg); }
        .fq-a { display:none; padding:0 18px 16px; color:var(--pv-muted); white-space:pre-wrap; font-size:.9rem; line-height:1.55; } .fq-i.open .fq-a { display:block; }
    </style>
@endsection
@section('content')
@php
    $examColors = ['#5b8a1f', '#d97706', '#2563eb', '#0f766e', '#7c3aed', '#0369a1', '#4d7c0f'];
    $exColor = fn ($ex, $i) => $ex->color ?: $examColors[$i % count($examColors)];
    $catIcons = ['bi-bank', 'bi-train-front', 'bi-building', 'bi-mortarboard', 'bi-gear', 'bi-heart-pulse', 'bi-book', 'bi-shield-check'];

    $keyPointList = $keyPoints->count() ? $keyPoints->map(fn ($k) => ['img' => $k->image ? Storage::url('files/' . $k->image) : null, 'icon' => 'bi-check2-circle', 'title' => $k->title, 'text' => $k->description])
        : collect([
            ['img' => null, 'icon' => 'bi-translate', 'title' => 'Learn in Malayalam & English', 'text' => 'Classes, notes and tests in the language you think in.'],
            ['img' => null, 'icon' => 'bi-person-video3', 'title' => 'Expert faculty', 'text' => 'Teachers who have cracked and taught these exams for years.'],
            ['img' => null, 'icon' => 'bi-graph-up-arrow', 'title' => 'Rank & analysis', 'text' => 'Know where you stand after every test, topic by topic.'],
            ['img' => null, 'icon' => 'bi-calendar2-check', 'title' => 'Personal study plan', 'text' => 'A day-by-day plan that keeps you on track till exam day.'],
            ['img' => null, 'icon' => 'bi-fire', 'title' => 'Roaring study method', 'text' => 'Short daily targets and smart revision that make topics stick.'],
            ['img' => null, 'icon' => 'bi-cloud-download', 'title' => 'Learn offline', 'text' => 'Download classes and PDFs, study even on a slow network.'],
            ['img' => null, 'icon' => 'bi-bell', 'title' => 'Instant exam alerts', 'text' => 'Notifications, results and last dates the moment they are out.'],
            ['img' => null, 'icon' => 'bi-headset', 'title' => 'Mentor support', 'text' => 'Doubt clearing and free counselling whenever you need help.'],
        ]);

    // FAQ tabs: admin FAQs by group; a few starter answers until the team adds their own
    $faqTabs = collect(\App\Models\ContentItem::FAQ_GROUPS)->map(fn ($label, $key) => ['label' => $label, 'items' => ($faqGroups[$key] ?? collect())->map(fn ($f) => [$f->title, $f->description])->all()])->filter(fn ($t) => count($t['items']));
    if ($faqTabs->isEmpty()) {
        $faqTabs = collect([
            'general' => ['label' => 'General', 'items' => [
                ['What is Pachavellam?', 'Pachavellam is a learning platform for competitive exams in Kerala and India - Kerala PSC, SSC, RRB, KTET, UGC NET and more - with live classes, recorded classes, mock tests, study materials and current affairs.'],
                ['Is there free content?', 'Yes. Free live classes, daily exams, free articles, previous year papers and current affairs are open to everyone on the website and the app.'],
            ]],
            'services' => ['label' => 'Our services', 'items' => [
                ['What do I get with a Prime course?', 'Mock tests and daily exams, live and recorded classes, study materials, a study plan and the Roaring study method - all for the exam you are preparing for.'],
                ['Do you offer counselling?', 'Yes. Fill the "Talk to a counsellor" form on this page and our team will call you back at the time you choose.'],
            ]],
            'courses' => ['label' => 'Course purchase', 'items' => [
                ['How do I buy a course?', 'Open Explore Prime courses, pick your course and press Buy now. You need to sign in first; after paying, the course opens in your account on the app and the website.'],
                ['Which payment methods are accepted?', 'UPI, debit and credit cards, net banking and wallets through our secure payment partner.'],
            ]],
            'terms' => ['label' => 'Terms & policies', 'items' => [
                ['Can I share my account?', 'No. A course is for one student; sharing the account may block access as per our terms and conditions.'],
                ['Where can I read the full terms?', 'See Terms & conditions and Privacy policy in the footer of this page.'],
            ]],
        ]);
    }
    $examsWithLive = $examTypes->whereIn('id', $freeTests->flatMap(fn ($x) => $x->exams)->unique());
@endphp
<div class="page-content-wrapper">
    <div class="container pb-4">

        {{-- hero banner --}}
        @if ($specialtab->count())
            <div class="hm-hero" id="hmHero">
                <div class="track">
                    @foreach ($specialtab as $b)
                        <a href="{{ $b->landing_url ?: '#' }}" target="_blank" rel="noopener"><img src="{{ Storage::url('files/' . $b->image) }}" alt="banner" @if (!$loop->first) loading="lazy" @endif></a>
                    @endforeach
                </div>
                <button class="arr l" type="button" aria-label="Previous"><i class="bi bi-chevron-left"></i></button><button class="arr r" type="button" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
                <div class="hm-dots">@foreach ($specialtab as $b)<i class="{{ $loop->first ? 'on' : '' }}"></i>@endforeach</div>
            </div>
        @endif

        {{-- stories --}}
        @if ($banner->count() || \App\Models\ContentItem::ofKind('achievement')->live()->exists())
            <section class="hm-sec">
                <div class="hm-head"><h4>Stories</h4><a href="{{ url('stories') }}" class="small fw-semibold">Show all</a></div>
                <div class="hm-strip" id="homeStories"></div>
                @include('kpsc.partials.achievements', ['target' => 'homeStories', 'mode' => 'strip', 'per' => 10])
            </section>
        @endif

        {{-- first visit after sign in: ask which exams they are preparing for --}}
        @auth
            @if (!auth()->user()->examInterests()->exists())
                <div class="hm-sec" style="margin-top:16px"><div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:var(--pv-brand-soft);border:1px solid var(--pv-green);border-radius:16px;padding:14px 16px">
                    <i class="bi bi-bullseye" style="font-size:1.6rem;color:var(--pv-brand)"></i>
                    <div style="flex:1 1 220px;color:var(--pv-text)"><b>Which exams are you preparing for?</b><div style="font-size:.85rem;color:var(--pv-muted)">Pick them once - your courses, classes and tests are then shown first.</div></div>
                    <a class="hm-btn" href="{{ url('exams') }}">Choose exams <i class="bi bi-arrow-right"></i></a>
                </div></div>
            @endif
        @endauth

        @if ($stats->count())
            <div class="hm-sec"><div class="st-strip">@foreach ($stats as $st)<div><b>{{ $st->description }}</b><span>{{ $st->title }}</span></div>@endforeach</div></div>
        @endif

        {{-- ================= exam type icons: each opens that exam type's landing page ================= --}}
        @if ($examTypes->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Choose your exam</h4></div>
                <div class="hm-circles">
                    @foreach ($examTypes as $i => $ex)
                        <a class="hm-circ" href="{{ url($ex->slug) }}">
                            @if ($ex->image)<i style="background:#fff center/cover url('{{ Storage::url($ex->image) }}');border:2px solid {{ $exColor($ex, $i) }}"></i>
                            @else<i style="background:linear-gradient(135deg, {{ $exColor($ex, $i) }}, #1b2a41)">{{ \Illuminate\Support\Str::limit($ex->short_name ?: $ex->name, 5, '') }}</i>@endif
                            {{ $ex->short_name ?: $ex->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================= Free live tests (exam type and season based). The latest classes are on each exam type's own page. ================= --}}
        @if ($freeTests->count())
        <section class="hm-sec" aria-labelledby="lxTitle">
            <div class="hm-head"><h4 id="lxTitle">Free <em>Live Tests</em></h4></div>
            <div class="lx-bar">
                <div class="d-flex gap-2 flex-wrap"><span class="lx-tab" aria-selected="true">Free Live Tests <span>{{ $freeTests->count() }}</span></span></div>
                @if ($examsWithLive->count() > 1)
                    <label class="lx-filter">Exam :
                        <select id="lxExam"><option value="all">All</option>@foreach ($examsWithLive as $e)<option value="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</option>@endforeach</select>
                    </label>
                @endif
            </div>
            <div id="lxT">
                <div class="lx-grid">
                    @foreach ($freeTests as $r)
                        <a class="lx-card" href="{{ $r->url }}" data-x="{{ implode(' ', $r->exams) }}">
                            <div class="th"><i class="bi bi-ui-checks"></i></div>
                            <div class="bd">
                                <div class="tt">{{ $r->title }}</div>
                                @if ($r->by)<div class="by">{{ $r->by }}</div>@endif
                                <div class="ft">
                                    <span class="lx-tag">{{ $r->tag }}</span>
                                    <span class="lx-when" data-start="{{ $r->starts ? $r->starts->getTimestamp() * 1000 : '' }}" data-end="{{ $r->ends ? $r->ends->getTimestamp() * 1000 : '' }}" data-kind="T">Available now</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <div class="container">@include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'home'])</div>
        {{-- ================= What are you preparing for? : exam type tabs with their category cards ================= --}}
        @if ($examTypes->count())
            <section class="hm-sec" aria-labelledby="gpTitle">
                <div class="hm-head"><h4 id="gpTitle">What are you preparing for?</h4></div>
                <div class="gp-tabs" role="tablist" aria-label="Exam types">
                    @foreach ($examTypes as $i => $ex)
                        <button type="button" role="tab" class="gp-tab" id="gpTab{{ $ex->id }}" aria-controls="gpPanel{{ $ex->id }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? 0 : -1 }}" style="--ex:{{ $exColor($ex, $i) }}">
                            <span>{{ $ex->name }}</span>
                            @if ($ex->image)<img src="{{ Storage::url($ex->image) }}" alt="" loading="lazy">@else<span class="ph">{{ \Illuminate\Support\Str::limit($ex->short_name ?: $ex->name, 4, '') }}</span>@endif
                        </button>
                    @endforeach
                </div>
                @foreach ($examTypes as $i => $ex)
                    <div class="gp-panel" role="tabpanel" id="gpPanel{{ $ex->id }}" aria-labelledby="gpTab{{ $ex->id }}" style="--ex:{{ $exColor($ex, $i) }}" @unless ($loop->first) hidden @endunless>
                        <div class="gp-grid">
                            @forelse ($ex->categories as $k => $cat)
                                @php $big = $k < 2 && $ex->categories->count() > 2; @endphp
                                <a class="gp-card {{ $big ? 'big' : '' }}" href="{{ $cat->publicUrl() }}">
                                    <div class="hd"><span class="ic">@if (!$big && $cat->image)<img src="{{ Storage::url($cat->image) }}" alt="" loading="lazy">@else<i class="bi {{ $cat->icon && str_starts_with($cat->icon, 'bi-') ? $cat->icon : $catIcons[$k % count($catIcons)] }}"></i>@endif</span><b>{{ $cat->name }}</b></div>
                                    <div class="subs">{{ $cat->children->count() ? $cat->children->pluck('name')->take(8)->implode(' • ') : \Illuminate\Support\Str::limit((string) $cat->description, 90) }}</div>
                                    @if ($big)
                                        <span class="hm-btn go">Explore Exams</span>
                                        @if ($cat->image)<img class="pic" src="{{ Storage::url($cat->image) }}" alt="" loading="lazy">@endif
                                    @else
                                        <span class="go">Explore Exams <i class="bi bi-chevron-right"></i></span>
                                    @endif
                                </a>
                            @empty
                                <a class="gp-card big" href="{{ url($ex->slug) }}">
                                    <div class="hd"><span class="ic"><i class="bi bi-journal-bookmark"></i></span><b>{{ $ex->name }}</b></div>
                                    <div class="subs">{{ \Illuminate\Support\Str::limit((string) ($ex->description ?: 'Classes, mock tests, study materials and previous year papers for ' . $ex->name . '.'), 140) }}</div>
                                    <span class="hm-btn go">Explore Exams</span>
                                    @if ($ex->image)<img class="pic" src="{{ Storage::url($ex->image) }}" alt="" loading="lazy">@endif
                                </a>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- ================= capsules ================= --}}
        @if ($capsules->count())
            @php $capsuleExams = $examTypes->whereIn('id', $capsules->flatMap(fn ($c) => $c->exams->pluck('id'))->unique()); @endphp
            <section class="hm-sec" aria-labelledby="cpTitle">
                <div class="hm-head"><h4 id="cpTitle">Daily <em>capsules</em></h4><small>Quick facts to revise in a minute</small></div>
                @if ($capsuleExams->count() > 1)
                    <div class="hm-chips" data-for="#hmCaps"><span class="hm-chip on" data-x="all">All</span>@foreach ($capsuleExams as $e)<span class="hm-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
                @endif
                <div class="hm-strip" id="hmCaps">
                    @foreach ($capsules as $cp)
                        @php $cpText = trim(html_entity_decode(strip_tags((string) $cp->content))); @endphp
                        <a href="#" role="button" class="cp-card" data-x="{{ $cp->exams->pluck('id')->implode(' ') }}" data-cap="{{ $loop->index }}" aria-label="Open capsule {{ $cp->tag ?: $loop->iteration }}">
                            @if ($cp->type === 'Image' && $cp->image)
                                <img src="{{ Storage::url('files/' . $cp->image) }}" alt="{{ $cp->tag ?: 'Capsule' }}" loading="lazy">
                            @else
                                <div class="note"><i class="bi bi-capsule"></i><p>{{ \Illuminate\Support\Str::limit($cpText, 260) }}</p></div>
                            @endif
                            @if ($cp->tag)<span class="tg">{{ $cp->tag }}</span>@endif
                        </a>
                        <template id="capT{{ $loop->index }}">
                            @if ($cp->type === 'Image' && $cp->image)<img src="{{ Storage::url('files/' . $cp->image) }}" alt="{{ $cp->tag ?: 'Capsule' }}">@endif
                            @if ($cp->type !== 'Image')<div class="tx">@if ($cp->tag)<div class="hm-pill">{{ $cp->tag }}</div>@endif{!! preg_replace('/\s(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', strip_tags((string) $cp->content, '<p><br><b><strong><i><em><u><ul><ol><li><h3><h4><h5><span><table><tr><td><th><tbody><thead>')) !!}</div>@endif
                        </template>
                    @endforeach
                </div>
            </section>
            <div class="cp-view" id="cpView" role="dialog" aria-modal="true" aria-label="Capsule" hidden>
                <div class="box"><button type="button" class="x" aria-label="Close"><i class="bi bi-x-lg"></i></button><div id="cpBody"></div></div>
                <button type="button" class="nav p" aria-label="Previous capsule"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="nav n" aria-label="Next capsule"><i class="bi bi-chevron-right"></i></button>
            </div>
        @endif

        {{-- newly launched courses --}}
        @if ($newCourses->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Newly launched <em>courses</em></h4><a href="{{ url('courses') }}" class="small fw-semibold">View all</a></div>
                <div class="hm-chips" id="hmChips"><span class="hm-chip on" data-x="all">All</span>@foreach ($newCourses->pluck('exam')->filter()->unique('id') as $e)<span class="hm-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
                <div class="hm-strip" id="hmCourses">
                    @foreach ($newCourses as $c)
                        @php $price = $c->effectivePrice(); @endphp
                        <a class="hm-course" data-x="{{ $c->exam_id }}" href="{{ url('courses/' . ($c->slug ?: $c->id)) }}">
                            <div class="cv" style="{{ $c->cover_image ? "background-image:url('" . Storage::url($c->cover_image) . "')" : '' }}">
                                @if ($c->created_at && $c->created_at->gt(now()->subDays(30)))<span class="tag">NEW</span>@endif
                                @if ($c->exam)<span class="ex" style="background:{{ $c->exam->color ?: '#2563eb' }}">{{ $c->exam->short_name ?: $c->exam->name }}</span>@endif
                            </div>
                            <div class="bd">
                                <div class="tt">{{ $c->title }}</div>
                                <div class="mt">{{ $c->validityLabel() }}@if ($c->duration_hours) · {{ $c->duration_hours }} h @endif</div>
                                <div class="pr">
                                    @if ($c->isFree()) FREE
                                    @else ₹{{ number_format($price) }} @if ($price < $c->price)<s>₹{{ number_format($c->price) }}</s>@endif
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================= our key points ================= --}}
        <section class="hm-sec">
            <div class="hm-head"><div><span class="hm-pill">Why Pachavellam</span><h4>Built to get you <em>selected</em></h4></div></div>
            <div class="kp-grid">
                @foreach ($keyPointList as $kp)
                    <div class="kp-card"><div class="ic">@if ($kp['img'])<img src="{{ $kp['img'] }}" alt="" loading="lazy">@else<i class="bi {{ $kp['icon'] }}"></i>@endif</div><b>{{ $kp['title'] }}</b>@if ($kp['text'])<p>{{ $kp['text'] }}</p>@endif</div>
                @endforeach
            </div>
        </section>

        {{-- ================= get the app ================= --}}
        <section class="hm-sec">
            <div class="hm-head"><h4><em>Free resources</em> available on the app</h4></div>
            <div class="hm-getapp">
                <div class="tx">
                    <h3>Download the Pachavellam app</h3>
                    <div style="opacity:.9">Live classes, daily exams, notes and notifications - on your phone.</div>
                    <div class="perks"><span><i class="bi bi-broadcast"></i> Free live classes</span><span><i class="bi bi-pencil-square"></i> Practice tests daily</span><span><i class="bi bi-file-earmark-text"></i> Previous year papers</span><span><i class="bi bi-newspaper"></i> Daily current affairs</span><span><i class="bi bi-cloud-download"></i> Learn offline</span><span><i class="bi bi-bell"></i> Instant exam alerts</span></div>
                    <div class="btns">
                        @if (config('app_links.android'))<a class="hm-store" href="{{ config('app_links.android') }}" target="_blank" rel="noopener"><i class="bi bi-google-play"></i><span><small>GET IT ON</small><b>Google Play</b></span></a>@endif
                        @if (config('app_links.ios'))<a class="hm-store" href="{{ config('app_links.ios') }}" target="_blank" rel="noopener"><i class="bi bi-apple"></i><span><small>Download on the</small><b>App Store</b></span></a>
                        @else<span class="hm-store off"><i class="bi bi-apple"></i><span><small>COMING SOON</small><b>App Store</b></span></span>@endif
                    </div>
                </div>
                @if (config('app_links.android'))<div class="qr-box"><img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=0&data={{ urlencode(config('app_links.android')) }}" alt="Scan to download" loading="lazy">Scan to download</div>@endif
            </div>
        </section>

        {{-- ================= Prime ================= --}}
        <section class="hm-sec">
            <div class="pr-band">
                <div>
                    <div class="pr-logo">Pachavellam <span>Prime</span></div>
                    <h3><em>One Subscription</em>,<br>Everything You Need to Crack It</h3>
                    <div class="pr-feats">
                        @foreach ($primeFeatures as [$icon, $name, $text])
                            <div class="pr-feat"><i class="bi {{ $icon }}"></i><div><b>{{ $name }}</b><small>{{ $text }}</small></div></div>
                        @endforeach
                    </div>
                    <a href="{{ url('prime') }}" target="_blank" rel="noopener" class="hm-btn">Explore Prime courses <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
                <div class="pr-orbit" aria-hidden="true">
                    <div class="ring r1"></div><div class="ring r2"></div>
                    <div class="core"><i class="bi bi-mortarboard-fill"></i><b>Prime</b></div>
                    @php $spots = [[50, 0], [93, 25], [93, 75], [50, 100], [7, 75], [7, 25]]; @endphp
                    @foreach ($examTypes->take(6) as $i => $ex)
                        <div class="node" style="left:{{ $spots[$i][0] }}%;top:{{ $spots[$i][1] }}%;animation-delay:{{ $i * .4 }}s;border-color:{{ $exColor($ex, $i) }}">@if ($ex->image)<img src="{{ Storage::url($ex->image) }}" alt="">@else{{ \Illuminate\Support\Str::limit($ex->short_name ?: $ex->name, 5, '') }}@endif</div>
                    @endforeach
                    <span class="badge-chip" style="right:-4%;top:12%">{{ number_format($mockTests) }}+ mock tests</span>
                    <span class="badge-chip" style="left:-4%;bottom:14%">{{ $examTypes->count() }}+ exams covered</span>
                </div>
            </div>
        </section>

        {{-- announcements --}}
        @if ($announcements->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Announcements</h4><a href="{{ url('announcements') }}" class="small fw-semibold">Show all</a></div>
                <div class="hm-chips" data-for="#hmAnn"><span class="hm-chip on" data-x="all">All</span>@foreach ($examTypes->whereIn('id', $announcements->flatMap(fn ($n) => $n->exams->pluck('id'))->unique()) as $e)<span class="hm-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
                <div class="cc-grid" id="hmAnn">
                    @foreach ($announcements as $n)
                        @include('kpsc.partials.content-card', ['item' => $n, 'href' => url('announcements/' . $n->id), 'icon' => 'bi-megaphone'])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- free articles --}}
        @if ($psc_news->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Free articles</h4><a href="{{ url('articles') }}" class="small fw-semibold">Show all</a></div>
                <div class="hm-chips" data-for="#hmArt"><span class="hm-chip on" data-x="all">All</span>@foreach ($examTypes->whereIn('id', $psc_news->flatMap(fn ($n) => $n->exams->pluck('id'))->unique()) as $e)<span class="hm-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
                <div class="cc-grid" id="hmArt">
                    @foreach ($psc_news as $news)
                        @include('kpsc.partials.content-card', ['item' => $news, 'href' => url('articles/' . $news->id), 'icon' => 'bi-newspaper'])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- exam series ticked in admin/home-exams --}}
        @if (!empty($homeSeries))
            <section class="hm-sec" id="exams-home">
                <div class="hm-head"><h4>Exams to attempt</h4><a href="{{ url('exams') }}" class="small fw-semibold">All exam types</a></div>
                <div class="pv-grid">
                    @foreach ($homeSeries as $f)
                        @include('kpsc.partials.series-card', ['f' => $f, 'accent' => $f->accent])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- videos --}}
        @if ($specialvideos->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Latest YouTube classes</h4></div>
                <div class="hm-strip">
                    @foreach ($specialvideos as $v)
                        <a class="hm-video" href="{{ $v->url }}"><img src="https://img.youtube.com/vi/{{ $v->redirection }}/0.jpg" alt="" loading="lazy"><span>{{ $v->title }}</span></a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ranking: top 10 / 20 learners --}}
        @if (($rankRows ?? collect())->count())
            <section class="hm-sec" id="ranking">
                <div class="hm-head"><h4>Overall ranking</h4></div>
                @include('kpsc.partials.rank-list', ['rankRows' => $rankRows, 'rankTitle' => 'Top learners', 'rankSub' => 'All exam types · 5 coins for every test + 1 for every right answer, plus referral coins'])
            </section>
        @endif

        {{-- ================= edu counselling ================= --}}
        <section class="hm-sec" id="counselling">
            <div class="cs-box">
                <div class="cs-side">
                    <i class="bi bi-headset bg-ic" aria-hidden="true"></i>
                    <span class="hm-pill">Free edu counselling</span>
                    <h3>Not sure where to start?</h3>
                    <p>Talk to our counsellors - one call to plan your exam, pick the right course and build a study plan that fits your day.</p>
                    <ul>
                        <li><i class="bi bi-compass"></i><span>Choose the right exam for your qualification and age</span></li>
                        <li><i class="bi bi-calendar2-week"></i><span>Get a personal study plan till exam day</span></li>
                        <li><i class="bi bi-mortarboard"></i><span>Find the course that suits your level and budget</span></li>
                        <li><i class="bi bi-chat-heart"></i><span>Honest advice - no pressure to buy</span></li>
                    </ul>
                </div>
                <form class="cs-form" id="csForm" method="POST" action="{{ url('counselling') }}" novalidate>
                    @csrf
                    <div class="full"><b style="color:var(--pv-text);font-size:1.05rem">Request a free call back</b></div>
                    <div><label for="csName">Your name *</label><input id="csName" name="name" required maxlength="120" value="{{ auth()->user()->name ?? '' }}" autocomplete="name"></div>
                    <div><label for="csPhone">Mobile number *</label><input id="csPhone" name="phone" required inputmode="tel" maxlength="20" value="{{ auth()->user()->mobile ?? '' }}" autocomplete="tel" placeholder="10 digit number"></div>
                    <div><label for="csExam">Preparing for</label><select id="csExam" name="exam_id"><option value="">Not decided yet</option>@foreach ($examTypes as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select></div>
                    <div><label for="csTime">Best time to call</label><select id="csTime" name="preferred_time">@foreach (\App\Models\CounsellingRequest::TIMES as $t)<option>{{ $t }}</option>@endforeach</select></div>
                    <div class="full"><label for="csMsg">Anything you want to ask? (optional)</label><textarea id="csMsg" name="message" rows="3" maxlength="1000"></textarea></div>
                    <div class="full">@include('partials.captcha', ['form' => 'counselling'])</div>
                    <div class="full" id="csOut">@if (session('counselling'))<div class="cs-msg ok">{{ session('counselling') }}</div>@endif</div>
                    <div class="full"><button class="hm-btn" type="submit"><i class="bi bi-telephone-outbound"></i> Request call back</button></div>
                </form>
            </div>
        </section>

        {{-- founder note --}}
        @if ($founder)
            <section class="hm-sec">
                <span class="hm-pill">Our vision</span>
                <div class="fd-card mt-1">
                    @if ($founder->image)<img src="{{ Storage::url('files/' . $founder->image) }}" alt="{{ $founder->title }}">@endif
                    <div><q>{{ $founder->description }}</q><div class="fw-bold mt-2">{{ $founder->title }}</div></div>
                </div>
            </section>
        @endif

        {{-- team: head office / office --}}
        @if ($team->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Our team</h4></div>
                <div class="hm-chips" id="tmTabs"><span class="hm-chip on" data-x="all">All</span><span class="hm-chip" data-x="head">Head office</span><span class="hm-chip" data-x="office">Office</span></div>
                <div class="tm-grid" id="tmGrid">
                    @foreach ($team as $t)
                        <div class="tm-card" data-g="{{ $t->type ?: 'office' }}">
                            @if ($t->image)<img src="{{ Storage::url('files/' . $t->image) }}" alt="{{ $t->title }}" loading="lazy">@else<div class="ph">{{ strtoupper(mb_substr($t->title, 0, 1)) }}</div>@endif
                            <div class="fw-bold" style="font-size:.9rem">{{ $t->title }}</div><div class="small" style="color:var(--pv-muted)">{{ $t->description }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================= associate partners ================= --}}
        @if ($partners->count())
            <section class="hm-sec">
                <div class="hm-head"><h4>Our associate <em>partners</em></h4></div>
                <div class="pt-row">
                    @foreach ($partners as $p)
                        <a class="pt-card" href="{{ $p->link_url ?: '#' }}" @if ($p->link_url) target="_blank" rel="noopener" @endif title="{{ $p->title }}">
                            @if ($p->image)<img src="{{ Storage::url('files/' . $p->image) }}" alt="{{ $p->title }}" loading="lazy">@else{{ $p->title }}@endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================= FAQ ================= --}}
        @if ($faqTabs->count())
            <section class="hm-sec">
                <div class="fq-wrap">
                    <h4 class="hm-title">Frequently Asked <em>Questions</em></h4>
                    <div class="fq-tabs" role="tablist" aria-label="Question groups">
                        @foreach ($faqTabs as $key => $tab)
                            <button type="button" role="tab" class="fq-tab" id="fqTab{{ $key }}" aria-controls="fq{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? 0 : -1 }}">{{ $tab['label'] }}</button>
                        @endforeach
                    </div>
                    @foreach ($faqTabs as $key => $tab)
                        <div class="fq-list" role="tabpanel" id="fq{{ $key }}" aria-labelledby="fqTab{{ $key }}" @unless ($loop->first) hidden @endunless>
                            @foreach ($tab['items'] as $n => [$q, $a])
                                <div class="fq-i {{ $loop->parent->first && $n === 0 ? 'open' : '' }}"><button type="button" class="fq-q" aria-expanded="{{ $loop->parent->first && $n === 0 ? 'true' : 'false' }}">{{ $q }}<i class="bi bi-chevron-down"></i></button><div class="fq-a">{{ $a }}</div></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
@section('scripts')
<script>
    (function () {
        // hero carousel
        var h = document.getElementById('hmHero');
        if (h) {
            var tr = h.querySelector('.track'), dots = h.querySelectorAll('.hm-dots i'), slides = tr.children, i = 0, timer;
            function per() { return innerWidth >= 992 ? 3 : 1; }
            function stepW() { return slides[0].getBoundingClientRect().width + 12; }
            function last() { return Math.max(0, slides.length - per()); }
            function go(k) { i = k > last() ? 0 : (k < 0 ? last() : k); tr.scrollTo({ left: stepW() * i, behavior: 'smooth' }); }
            h.querySelector('.arr.l').onclick = function () { go(i - 1); }; h.querySelector('.arr.r').onclick = function () { go(i + 1); };
            tr.addEventListener('scroll', function () { i = Math.round(tr.scrollLeft / stepW()); [].forEach.call(dots, function (d, x) { d.classList.toggle('on', x === i); d.style.display = x <= last() ? '' : 'none'; }); });
            tr.dispatchEvent(new Event('scroll'));
            function auto() { clearInterval(timer); if (slides.length > per()) timer = setInterval(function () { go(i + 1); }, 4500); }
            h.addEventListener('touchstart', function () { clearInterval(timer); }); h.addEventListener('mouseenter', function () { clearInterval(timer); }); h.addEventListener('mouseleave', auto); auto();
        }

        // accessible tabs: click, arrow keys, Home / End
        function tabs(list, onChange) {
            if (!list) return;
            var all = [].slice.call(list.querySelectorAll('[role="tab"]'));
            function pick(t, focus) {
                all.forEach(function (x) {
                    var on = x === t; x.setAttribute('aria-selected', on ? 'true' : 'false'); x.tabIndex = on ? 0 : -1;
                    var p = document.getElementById(x.getAttribute('aria-controls')); if (p) p.hidden = !on;
                });
                if (focus) t.focus();
                if (onChange) onChange(t);
            }
            all.forEach(function (t, k) {
                t.addEventListener('click', function () { pick(t); });
                t.addEventListener('keydown', function (e) {
                    var n = { ArrowRight: k + 1, ArrowDown: k + 1, ArrowLeft: k - 1, ArrowUp: k - 1, Home: 0, End: all.length - 1 }[e.key];
                    if (n === undefined) return; e.preventDefault(); pick(all[(n + all.length) % all.length], true);
                });
            });
        }
        tabs(document.querySelector('.gp-tabs'), function (t) { t.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' }); });
        tabs(document.querySelector('.fq-tabs'));

        // live experience: exam filter and countdowns
        var sel = document.getElementById('lxExam');
        function filterLive() {
            var v = sel ? sel.value : 'all';
            ['lxT'].forEach(function (id) {
                var box = document.getElementById(id), shown = 0;
                if (!box) return;
                [].forEach.call(box.querySelectorAll('.lx-card'), function (c) {
                    var ok = !c.dataset.gone && (v === 'all' || (' ' + c.dataset.x + ' ').indexOf(' ' + v + ' ') >= 0);
                    c.hidden = !ok; if (ok) shown++;
                });
                var emp = box.querySelector('.lx-empty'); if (emp) emp.hidden = shown > 0;
            });
        }
        if (sel) sel.addEventListener('change', filterLive);
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function tick() {
            var now = Date.now(), changed = false;
            [].forEach.call(document.querySelectorAll('.lx-when'), function (el) {
                var s = Number(el.dataset.start) || 0, e = Number(el.dataset.end) || 0, txt, live = false;
                if (e && now > e) { var card = el.closest('.lx-card'); if (!card.dataset.gone) { card.dataset.gone = '1'; changed = true; } return; }
                if (!s || now >= s) { txt = s ? 'Live now' : 'Available now'; live = true; }
                else {
                    var d = Math.floor((s - now) / 1000), days = Math.floor(d / 86400);
                    txt = (el.dataset.kind === 'T' ? 'Starts in ' : 'Live in ') + (days >= 1 ? days + (days > 1 ? ' days' : ' day') : pad(Math.floor(d / 3600)) + ':' + pad(Math.floor(d % 3600 / 60)) + ':' + pad(d % 60));
                }
                el.textContent = txt; el.classList.toggle('now', live);
            });
            if (changed) filterLive();
        }
        tick(); filterLive(); setInterval(tick, 1000);

        // course chips, team chips, content chips
        var chips = document.getElementById('hmChips');
        if (chips) chips.addEventListener('click', function (e) {
            var c = e.target.closest('[data-x]'); if (!c) return;
            [].forEach.call(chips.children, function (x) { x.classList.toggle('on', x === c); });
            [].forEach.call(document.querySelectorAll('#hmCourses .hm-course'), function (a) { a.style.display = (c.dataset.x === 'all' || a.dataset.x === c.dataset.x) ? '' : 'none'; });
        });
        var tt = document.getElementById('tmTabs');
        if (tt) tt.addEventListener('click', function (e) {
            var c = e.target.closest('[data-x]'); if (!c) return;
            [].forEach.call(tt.children, function (x) { x.classList.toggle('on', x === c); });
            [].forEach.call(document.querySelectorAll('#tmGrid .tm-card'), function (a) { a.style.display = (c.dataset.x === 'all' || a.dataset.g === c.dataset.x) ? '' : 'none'; });
        });
        [].forEach.call(document.querySelectorAll('.hm-chips[data-for]'), function (box) {
            box.addEventListener('click', function (e) {
                var c = e.target.closest('[data-x]'); if (!c) return;
                [].forEach.call(box.children, function (x) { x.classList.toggle('on', x === c); });
                [].forEach.call(document.querySelectorAll(box.dataset.for + ' > a'), function (a) { a.style.display = (c.dataset.x === 'all' || (' ' + a.dataset.x + ' ').indexOf(' ' + c.dataset.x + ' ') >= 0) ? '' : 'none'; });
            });
        });

        // capsule viewer: tap a card to open it full size, arrows / keys for the next one
        var cv = document.getElementById('cpView');
        if (cv) {
            var cards = [].slice.call(document.querySelectorAll('#hmCaps .cp-card')), cur = 0, opener = null;
            function visible() { return cards.filter(function (c) { return c.style.display !== 'none'; }); }
            function show(card) {
                cur = visible().indexOf(card);
                var body = document.getElementById('cpBody'); body.innerHTML = '';
                body.appendChild(document.getElementById('capT' + card.dataset.cap).content.cloneNode(true));
                cv.hidden = false; document.body.style.overflow = 'hidden'; cv.querySelector('.x').focus();
            }
            function close() { cv.hidden = true; document.body.style.overflow = ''; if (opener) opener.focus(); }
            function step(d) { var v = visible(); if (v.length) show(v[(cur + d + v.length) % v.length]); }
            cards.forEach(function (c) { c.addEventListener('click', function (e) { e.preventDefault(); opener = c; show(c); }); });
            cv.querySelector('.x').addEventListener('click', close);
            cv.querySelector('.nav.p').addEventListener('click', function () { step(-1); });
            cv.querySelector('.nav.n').addEventListener('click', function () { step(1); });
            cv.addEventListener('click', function (e) { if (e.target === cv) close(); });
            document.addEventListener('keydown', function (e) {
                if (cv.hidden) return;
                if (e.key === 'Escape') close(); else if (e.key === 'ArrowRight') step(1); else if (e.key === 'ArrowLeft') step(-1);
            });
        }

        // FAQ accordion
        [].forEach.call(document.querySelectorAll('.fq-q'), function (q) {
            q.addEventListener('click', function () { var open = q.parentNode.classList.toggle('open'); q.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        });

        // counselling form: sent without leaving the page
        var f = document.getElementById('csForm');
        if (f && window.fetch) f.addEventListener('submit', function (e) {
            e.preventDefault();
            var out = document.getElementById('csOut'), btn = f.querySelector('button[type="submit"]');
            function say(cls, t) { out.innerHTML = ''; var d = document.createElement('div'); d.className = 'cs-msg ' + cls; d.textContent = t; out.appendChild(d); }
            var el = f.elements;
            if (!el['name'].value.trim() || !/^[0-9+\-\s]{10,20}$/.test(el['phone'].value.trim())) { say('err', 'Please enter your name and a valid mobile number.'); return; }
            btn.disabled = true;
            fetch(f.action, { method: 'POST', body: new FormData(f), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j, status: r.status }; }); })
                .then(function (x) {
                    if (x.ok) { say('ok', x.j.message); el['message'].value = ''; var cap = f.querySelector('.pv-cap'); if (cap && !cap.hidden) cap.querySelector('.pv-cap-r').click(); }
                    else if (x.status === 429) say('err', 'Too many requests. Please try again in a minute.');
                    else if (x.j && x.j.code === 'captcha') { if (window.PvCap) PvCap.again(x.j); say('err', x.j.message); }
                    else say('err', (x.j.errors && Object.values(x.j.errors)[0][0]) || x.j.message || 'Could not send. Please try again.');
                })
                .catch(function () { say('err', 'Could not send. Please check your connection and try again.'); })
                .finally(function () { btn.disabled = false; });
        });
    })();
</script>
@endsection
