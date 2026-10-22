{{--
    Light / dark theme for the student website. Include in <head> after the page styles.
    The choice is kept in localStorage "theme" (the same key the old #darkSwitch used); with no choice the device setting is followed.
    Components use the --pv-* colours below, so they switch by themselves.
--}}
<script>
    (function () {
        var t = null;
        try { t = localStorage.getItem('theme'); } catch (e) {}
        if (t !== 'dark' && t !== 'light') t = window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
    })();
</script>
<style>
    :root {
        /* brand green #a7d04f: --pv-green is the fill, --pv-brand the readable text / accent shade, --pv-on-brand the text on a green fill */
        --pv-green: #a7d04f; --pv-green-dark: #3d6b12; --pv-on-brand: #1f3305;
        --pv-bg: #f6f9f1; --pv-surface: #ffffff; --pv-surface-2: #f0f5e8; --pv-text: #1c2b14; --pv-muted: #66755d; --pv-border: #e1e9d6;
        --pv-brand: #4f7d1b; --pv-brand-2: #a7d04f; --pv-brand-soft: #eef7dc; --pv-shadow: 0 2px 12px rgba(31, 51, 5, .08); --pv-chip: #ffffff;
        --pv-danger: #b42318; --pv-danger-soft: #fdecec;
        color-scheme: light;
    }
    [data-theme="dark"] {
        --pv-bg: #0e1710; --pv-surface: #16241a; --pv-surface-2: #1d3022; --pv-text: #e7f0e2; --pv-muted: #9db09c; --pv-border: #2a4030;
        --pv-brand: #a7d04f; --pv-brand-2: #a7d04f; --pv-brand-soft: rgba(167, 208, 79, .14); --pv-shadow: 0 2px 14px rgba(0, 0, 0, .5); --pv-chip: #1d3022;
        --pv-danger: #ff8a80; --pv-danger-soft: rgba(255, 138, 128, .14);
        color-scheme: dark;
    }
    html, body, html[data-theme="dark"] body { background-color: var(--pv-bg); color: var(--pv-text); }
    body { transition: background-color .2s, color .2s; }

    /* theme switch button (header) */
    .pv-theme-btn { width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--pv-border); background: var(--pv-surface); color: var(--pv-text); display: inline-flex; align-items: center; justify-content: center; font-size: 1.05rem; cursor: pointer; transition: transform .2s; }
    .pv-theme-btn:hover { transform: rotate(-15deg); }
    .pv-theme-btn .bi-sun-fill { display: none; color: var(--pv-green); }
    [data-theme="dark"] .pv-theme-btn .bi-sun-fill { display: inline; }
    [data-theme="dark"] .pv-theme-btn .bi-moon-stars-fill { display: none; }

    /* shared pieces of the website that use fixed light colours */
    [data-theme="dark"] .header-area, [data-theme="dark"] .sidenav-wrapper { background-color: var(--pv-surface) !important; }
    [data-theme="dark"] .sidenav-nav li a, [data-theme="dark"] .sidenav-nav li a i.text-dark { color: var(--pv-text) !important; }
    [data-theme="dark"] #bellBtn { color: var(--pv-text) !important; }
    [data-theme="dark"] #bellPanel { background: var(--pv-surface) !important; color: var(--pv-text); }
    [data-theme="dark"] #bellPanel a.text-dark { color: var(--pv-text) !important; }
    [data-theme="dark"] #bottomNav { background: var(--pv-surface); box-shadow: 0 -2px 14px rgba(0, 0, 0, .5); }
    [data-theme="dark"] #bottomNav a { color: var(--pv-muted); } [data-theme="dark"] #bottomNav a.on { color: var(--pv-brand); }
    [data-theme="dark"] .bg-white, [data-theme="dark"] .bg-light { background-color: var(--pv-surface) !important; }
    [data-theme="dark"] .text-dark { color: var(--pv-text) !important; }
    [data-theme="dark"] .text-muted { color: var(--pv-muted) !important; }
    [data-theme="dark"] .border, [data-theme="dark"] .border-bottom, [data-theme="dark"] .border-top { border-color: var(--pv-border) !important; }
    [data-theme="dark"] .card, [data-theme="dark"] .modal-content, [data-theme="dark"] .dropdown-menu, [data-theme="dark"] .list-group-item { background-color: var(--pv-surface); color: var(--pv-text); border-color: var(--pv-border); }
    [data-theme="dark"] .form-control, [data-theme="dark"] .form-select { background-color: var(--pv-surface-2); color: var(--pv-text); border-color: var(--pv-border); }
    [data-theme="dark"] .form-control::placeholder { color: var(--pv-muted); }
    [data-theme="dark"] .table { --bs-table-color: var(--pv-text); --bs-table-bg: transparent; border-color: var(--pv-border); }
    [data-theme="dark"] .btn-light { background: var(--pv-surface-2); border-color: var(--pv-border); color: var(--pv-text); }
    [data-theme="dark"] img.pv-invert-dark { filter: invert(1) hue-rotate(180deg); }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function set(t, keep) {
            document.documentElement.setAttribute('data-theme', t);
            if (keep) { try { localStorage.setItem('theme', t); } catch (e) {} }
            var sw = document.getElementById('darkSwitch'); if (sw) sw.checked = t === 'dark';
            document.querySelectorAll('[data-theme-toggle]').forEach(function (b) { b.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false'); b.title = t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'; });
        }
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
            b.addEventListener('click', function () { set(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark', true); });
        });
        set(document.documentElement.getAttribute('data-theme') || 'light', false);
    });
</script>
