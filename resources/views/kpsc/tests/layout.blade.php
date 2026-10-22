{{-- Full-screen page for taking a test and reading its solutions: no site header, footer or bottom bar. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title') :: Pachavellam</title>
    <link rel="icon" href="{{ url('assets/images/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @include('partials.theme')
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans Malayalam", sans-serif; -webkit-text-size-adjust: 100%; }
        button { font: inherit; cursor: pointer; }
        .t-top { position: sticky; top: 0; z-index: 20; background: var(--pv-surface); border-bottom: 1px solid var(--pv-border); display: flex; align-items: center; gap: 10px; padding: 10px 14px; }
        .t-ibtn { width: 40px; height: 40px; border-radius: 10px; border: 1px solid var(--pv-border); background: var(--pv-surface); color: var(--pv-text); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; flex: none; text-decoration: none; }
        .t-ibtn.brand { background: var(--pv-green); color: var(--pv-on-brand); border-color: transparent; }
        .t-btn { border: 1px solid var(--pv-border); background: var(--pv-surface); color: var(--pv-text); border-radius: 10px; padding: 11px 16px; font-weight: 700; font-size: .92rem; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; }
        .t-btn.pri { background: var(--pv-green); color: var(--pv-on-brand); border-color: transparent; }
        .t-btn.dark { background: var(--pv-text); color: var(--pv-surface); border-color: transparent; }
        .t-btn:disabled { opacity: .5; cursor: not-allowed; }
        .t-sheet-bg { position: fixed; inset: 0; background: rgba(5, 12, 6, .55); z-index: 50; display: flex; align-items: flex-end; justify-content: center; }
        .t-sheet-bg[hidden] { display: none; }
        .t-sheet { background: var(--pv-surface); color: var(--pv-text); width: 100%; max-width: 640px; max-height: 88vh; border-radius: 20px 20px 0 0; display: flex; flex-direction: column; overflow: hidden; }
        .t-sheet.center { border-radius: 16px; margin: auto 16px; align-self: center; }
        .t-sheet-hd { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--pv-border); font-weight: 800; }
        .t-sheet-bd { padding: 14px 18px; overflow: auto; }
        .t-sheet-ft { padding: 12px 18px; border-top: 1px solid var(--pv-border); display: flex; gap: 10px; }
        .t-sheet-ft > * { flex: 1; }
        .t-tabs { display: flex; overflow-x: auto; scrollbar-width: none; border-bottom: 1px solid var(--pv-border); }
        .t-tabs::-webkit-scrollbar { display: none; }
        .t-tab { flex: none; border: 0; background: none; padding: 12px 16px; color: var(--pv-text); font-weight: 600; border-bottom: 3px solid transparent; white-space: nowrap; }
        .t-tab[aria-selected="true"] { color: var(--pv-brand); border-bottom-color: var(--pv-brand); }
        .t-tab:disabled { color: var(--pv-muted); opacity: .6; }
        .t-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(48px, 1fr)); gap: 10px; }
        .t-num { height: 46px; border-radius: 10px; border: 1.5px solid var(--pv-border); background: var(--pv-surface); color: var(--pv-text); font-weight: 700; position: relative; }
        .t-num.cur { border-color: var(--pv-brand); box-shadow: 0 0 0 2px color-mix(in srgb, var(--pv-green) 50%, transparent); }
        .t-num.ans { background: #2f80ed; border-color: #2f80ed; color: #fff; }
        .t-num.skip { background: #c9cfd6; border-color: #c9cfd6; color: #fff; }
        .t-num.mark { background: #f5c451; border-color: #f5c451; color: #4a3500; }
        .t-num.markans { background: #f5c451; border-color: #f5c451; color: #4a3500; }
        .t-num.markans::after { content: ''; position: absolute; right: 4px; bottom: 4px; width: 9px; height: 9px; border-radius: 50%; background: #2f80ed; border: 1.5px solid #fff; }
        .t-num.ok { background: #1aa260; border-color: #1aa260; color: #fff; }
        .t-num.bad { background: #e5484d; border-color: #e5484d; color: #fff; }
        [data-theme="dark"] .t-num.skip { background: #4a5560; border-color: #4a5560; }
        .t-legend { display: flex; flex-wrap: wrap; gap: 10px 16px; font-size: .8rem; color: var(--pv-text); }
        .t-legend span { display: inline-flex; align-items: center; gap: 6px; } .t-legend i { width: 18px; height: 18px; border-radius: 50%; display: inline-block; border: 1.5px solid var(--pv-border); }
        .t-q { padding: 16px; max-width: 900px; margin: 0 auto; width: 100%; }
        .t-qtext { font-size: 1.02rem; line-height: 1.6; color: var(--pv-text); word-wrap: break-word; }
        .t-qtext img, .t-opt img { max-width: 100%; height: auto; border-radius: 6px; }
        .t-qtext table { border-collapse: collapse; } .t-qtext td, .t-qtext th { border: 1px solid var(--pv-border); padding: 4px 8px; }
        .t-opt { width: 100%; display: flex; align-items: flex-start; gap: 14px; text-align: left; border: 1.5px solid transparent; background: var(--pv-surface-2); color: var(--pv-text); border-radius: 12px; padding: 14px; margin-top: 12px; font-size: .98rem; line-height: 1.5; }
        .t-opt .l { width: 30px; height: 30px; flex: none; border-radius: 50%; background: var(--pv-surface); border: 1px solid var(--pv-border); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
        .t-opt .x { flex: 1; min-width: 0; padding-top: 4px; }
        .t-opt.sel { border-color: #2f80ed; background: color-mix(in srgb, #2f80ed 10%, var(--pv-surface)); } .t-opt.sel .l { background: #2f80ed; color: #fff; border-color: #2f80ed; }
        .t-opt.ok { border-color: #1aa260; background: color-mix(in srgb, #1aa260 12%, var(--pv-surface)); } .t-opt.ok .l { background: #1aa260; color: #fff; border-color: #1aa260; }
        .t-opt.bad { border-color: #e5484d; background: color-mix(in srgb, #e5484d 10%, var(--pv-surface)); } .t-opt.bad .l { background: #e5484d; color: #fff; border-color: #e5484d; }
        .t-pill { display: inline-flex; align-items: center; gap: 4px; border-radius: 8px; padding: 3px 10px; font-size: .78rem; font-weight: 700; background: var(--pv-surface-2); color: var(--pv-text); }
        .t-pill.g { background: color-mix(in srgb, #1aa260 14%, var(--pv-surface)); color: #1aa260; } .t-pill.r { background: color-mix(in srgb, #e5484d 12%, var(--pv-surface)); color: #e5484d; }
        .t-toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: var(--pv-text); color: var(--pv-surface); padding: 10px 18px; border-radius: 22px; font-size: .86rem; font-weight: 600; z-index: 90; max-width: 90vw; }
        .t-table { width: 100%; border-collapse: collapse; font-size: .86rem; min-width: 460px; } .t-table th, .t-table td { padding: 10px 8px; border-bottom: 1px solid var(--pv-border); text-align: left; white-space: nowrap; }
        .t-table th { background: var(--pv-surface-2); font-weight: 700; } .t-table tr:last-child td { font-weight: 800; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')
    @yield('scripts')
</body>
</html>
