@section('header-tab')
    <!-- Header Area-->
    <div class="header-area" id="headerArea">
        <div class="container h-100 d-flex align-items-center justify-content-between">
            <!-- Back Button-->
            <div class="logo-wrapper"><a href="/"><img src="{{ url('images/logo.png') }}" alt=""></a></div>
            <!-- Navbar Toggler-->
            <!-- Page Title-->
            <div class="page-heading">
            </div>
            <!-- Light / dark switch -->
            <button type="button" class="pv-theme-btn ms-auto me-2" data-theme-toggle aria-label="Switch light or dark mode"><i class="bi bi-moon-stars-fill"></i><i class="bi bi-sun-fill"></i></button>
            <!-- Notification bell -->
            <div class="position-relative me-3" id="bellWrap">
                <button type="button" id="bellBtn" class="border-0 bg-transparent p-1 position-relative" aria-label="Notifications" style="font-size:1.35rem;color:#1b2a41"><i class="bi bi-bell-fill"></i><span id="bellDot" class="position-absolute rounded-circle bg-danger" style="display:none;width:10px;height:10px;top:2px;right:2px;border:2px solid #fff"></span></button>
                <div id="bellPanel" style="display:none;position:absolute;right:-60px;top:42px;width:min(340px,92vw);background:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.22);z-index:2000;overflow:hidden">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"><b>Notifications</b><a href="{{ url('announcements') }}" class="small">View all</a></div>
                    <div id="bellList" style="max-height:60vh;overflow:auto"><div class="p-3 text-muted small">Loading…</div></div>
                </div>
            </div>
            <!-- Search Form-->
            <div class="search-form">
                <!-- Navbar Toggler-->
                <div class="navbar--toggler" id="newstenNavbarToggler">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    @include('kpsc.partials.drawer')
    <script>
        (function () {
            var btn = document.getElementById('bellBtn'), panel = document.getElementById('bellPanel'), list = document.getElementById('bellList'), dot = document.getElementById('bellDot'), loaded = false, latest = 0;
            function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
            function load() {
                return fetch('{{ url('notifications-feed') }}', { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (j) {
                    latest = j.latest || 0; loaded = true;
                    var seen = 0; try { seen = Number(localStorage.getItem('bellSeen') || 0); } catch (e) {}
                    dot.style.display = latest > seen ? 'block' : 'none';
                    list.innerHTML = j.items.map(function (n) {
                        return '<a href="' + esc(n.url) + '" class="d-flex gap-2 px-3 py-2 text-decoration-none text-dark border-bottom">' +
                            '<div style="width:46px;height:46px;border-radius:8px;flex:none;background:#1b2a41 center/cover' + (n.thumb ? ' url(' + esc(n.thumb) + ')' : '') + ';color:#fff;display:flex;align-items:center;justify-content:center">' + (n.thumb ? '' : '<i class=\'bi bi-megaphone\'></i>') + '</div>' +
                            '<div style="min-width:0"><div class="fw-semibold" style="font-size:.86rem;line-height:1.2">' + esc(n.title) + '</div><div class="small text-muted">' + esc(n.date || '') + '</div></div></a>';
                    }).join('') || '<div class="p-3 text-muted small">Nothing new.</div>';
                }).catch(function () { list.innerHTML = '<div class="p-3 text-muted small">Could not load.</div>'; });
            }
            load();
            btn.addEventListener('click', function (e) {
                e.stopPropagation(); var open = panel.style.display === 'block'; panel.style.display = open ? 'none' : 'block';
                if (!open) { try { localStorage.setItem('bellSeen', String(latest)); } catch (x) {} dot.style.display = 'none'; }
            });
            document.addEventListener('click', function (e) { if (!e.target.closest('#bellWrap')) panel.style.display = 'none'; });
        })();
    </script>
@endsection
