@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .pf-card { background:linear-gradient(140deg,#16300a,#2f5410 60%,#4f7d1b); color:#fff; border-radius:20px; padding:20px; display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
        .pf-av { width:76px; height:76px; border-radius:50%; flex:none; overflow:hidden; background:#a7d04f; color:#1f3305; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:800; border:3px solid rgba(255,255,255,.35); }
        .pf-av img { width:100%; height:100%; object-fit:cover; }
        .pf-card h1 { font-size:1.35rem; font-weight:800; margin:0; color:#fff; } .pf-card .c { color:rgba(255,255,255,.8); font-size:.85rem; }
        .pf-card .chips { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; } .pf-card .chips span { background:rgba(255,255,255,.16); border-radius:10px; padding:2px 10px; font-size:.74rem; font-weight:600; }
        .pf-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:12px; }
        .pf-stats a { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; padding:12px 8px; text-align:center; color:var(--pv-text); text-decoration:none; }
        .pf-stats b { display:block; font-size:1.3rem; color:var(--pv-brand); } .pf-stats small { color:var(--pv-muted); font-size:.76rem; }
        .pf-menu { margin-top:16px; display:grid; gap:10px; }
        .pf-item { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; overflow:hidden; scroll-margin-top:12px; }
        .pf-item > summary { list-style:none; display:flex; align-items:center; gap:12px; padding:13px 14px; cursor:pointer; color:var(--pv-text); font-weight:700; }
        .pf-item > summary::-webkit-details-marker { display:none; }
        .pf-item > summary .ic { width:38px; height:38px; border-radius:11px; flex:none; background:var(--pv-brand-soft); color:var(--pv-brand); display:flex; align-items:center; justify-content:center; font-size:1.05rem; }
        .pf-item > summary small { display:block; color:var(--pv-muted); font-weight:500; font-size:.76rem; }
        .pf-item > summary .ch { margin-left:auto; color:var(--pv-muted); transition:transform .2s; } .pf-item[open] > summary .ch { transform:rotate(90deg); }
        .pf-body { padding:4px 14px 16px; border-top:1px solid var(--pv-border); }
        .pf-link { display:flex; align-items:center; gap:12px; padding:13px 14px; color:var(--pv-text) !important; text-decoration:none !important; font-weight:700; background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; }
        .pf-link .ic { width:38px; height:38px; border-radius:11px; flex:none; background:var(--pv-brand-soft); color:var(--pv-brand); display:flex; align-items:center; justify-content:center; }
        .pf-link.out .ic { background:color-mix(in srgb, #e5484d 12%, var(--pv-surface)); color:#e5484d; } .pf-link.out { color:#e5484d !important; }
        .pf-form { display:grid; gap:12px; grid-template-columns:1fr 1fr; padding-top:12px; }
        .pf-form .full { grid-column:1 / -1; } @media (max-width:575px) { .pf-form { grid-template-columns:1fr; } }
        .pf-form label { font-size:.78rem; font-weight:700; color:var(--pv-muted); margin-bottom:3px; display:block; }
        .pf-form input, .pf-form select { width:100%; border:1px solid var(--pv-border); background:var(--pv-surface-2); color:var(--pv-text); border-radius:10px; padding:9px 12px; font-size:.9rem; }
        .pf-form input:focus, .pf-form select:focus { outline:2px solid color-mix(in srgb, var(--pv-green) 60%, transparent); border-color:var(--pv-brand); }
        .pf-err { color:var(--pv-danger); font-size:.78rem; margin-top:3px; }
        .pf-table { width:100%; font-size:.85rem; border-collapse:collapse; margin-top:10px; } .pf-table td, .pf-table th { padding:8px 6px; border-bottom:1px solid var(--pv-border); text-align:left; color:var(--pv-text); } .pf-table th { color:var(--pv-muted); font-size:.74rem; font-weight:700; }
        .pf-st { font-size:.7rem; font-weight:700; border-radius:8px; padding:1px 8px; }
        .pf-theme { display:flex; gap:8px; margin-top:12px; }
    </style>
@endsection
@section('content')
@php
    $first = strtoupper(mb_substr($user->name ?: 'U', 0, 1));
    $err = fn ($f) => $errors->has($f) ? '<div class="pf-err">' . e($errors->first($f)) . '</div>' : '';
    $open = fn ($s) => $section === $s || ($s === 'profile' && ($errors->hasAny(['name', 'email', 'mobile', 'pincode', 'photo', 'dob']))) || ($s === 'password' && $errors->hasAny(['password', 'current_password']));
    $stColor = ['active' => '#0a8f5b', 'expired' => '#66755d', 'cancelled' => '#e5484d', 'pending' => '#d97706'];
@endphp
<div class="page-content-wrapper"><div class="container"><div class="hb-wrap" style="max-width:720px">
    @if (session('hub_msg'))<div class="hb-note ok"><i class="bi bi-check-circle-fill"></i><span class="sp">{{ session('hub_msg') }}</span></div>@endif

    <div class="pf-card">
        <div class="pf-av">@if ($user->photoUrl())<img src="{{ $user->photoUrl() }}" alt="">@else{{ $first }}@endif</div>
        <div style="flex:1;min-width:0">
            <h1>{{ $user->name ?: 'Student' }}</h1>
            <div class="c">{{ $user->mobile }}@if ($user->mobile && $user->email) · @endif{{ $user->email }}</div>
            @if ($interests->count())<div class="chips">@foreach ($interests as $e)<span>{{ $e->short_name ?: $e->name }}</span>@endforeach</div>@endif
        </div>
        <a href="{{ url('my-account?section=profile') }}#pf-profile" class="hb-btn sm"><i class="bi bi-pencil"></i> Edit</a>
    </div>

    <div class="pf-stats">
        <a href="#pf-courses"><b>{{ $admissions->count() }}</b><small>My courses</small></a>
        <a href="{{ url('exams') }}"><b>{{ $interests->count() }}</b><small>My exams</small></a>
        <a href="#pf-exams"><b>{{ $favs->count() }}</b><small>Favourites</small></a>
    </div>

    @php
        $dur = fn ($s) => \App\Services\StudentProgress::duration((int) $s);
        $lv = $progress['level'];
    @endphp
    <style>
        .pd-card{background:var(--pv-card,#fff);border:1px solid var(--pv-border,#e5e7eb);border-radius:14px;padding:14px;margin-bottom:12px}
        .pd-card h2{font-size:1rem;font-weight:700;margin:0 0 10px;display:flex;align-items:center;gap:6px}
        .pd-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
        @media(min-width:576px){.pd-grid{grid-template-columns:repeat(4,1fr)}}
        .pd-kpi{border:1px solid var(--pv-border,#e5e7eb);border-radius:12px;padding:10px}
        .pd-kpi b{display:block;font-size:1.25rem;line-height:1.2}.pd-kpi small{color:var(--pv-muted,#6b7280)}
        .pd-bar{height:8px;border-radius:99px;background:var(--pv-border,#e5e7eb);overflow:hidden}.pd-bar i{display:block;height:100%;background:#0a8f5b}
        .pd-tabs{display:inline-flex;gap:4px;margin-bottom:8px}.pd-tabs button{border:1px solid var(--pv-border,#e5e7eb);background:transparent;color:inherit;border-radius:99px;padding:3px 12px;font-size:.8rem}
        .pd-tabs button.on{background:#0a8f5b;color:#fff;border-color:#0a8f5b}
        .pd-tbl{width:100%;font-size:.85rem}.pd-tbl td,.pd-tbl th{padding:6px 4px;border-top:1px solid var(--pv-border,#e5e7eb)}.pd-tbl tr.me{background:rgba(10,143,91,.12);font-weight:700}
    </style>

    {{-- coins, level and rank --}}
    <div class="pd-card">
        <h2><i class="bi bi-coin" style="color:#d97706"></i> My progress <span class="ms-auto small" style="font-weight:600">{{ $lv['name'] }}</span></h2>
        <div class="pd-grid">
            <div class="pd-kpi"><b>{{ number_format($progress['coins']) }}</b><small>Coins collected</small></div>
            <div class="pd-kpi"><b>@if ($progress['rank'])#{{ number_format($progress['rank']) }}@else — @endif</b><small>Overall rank @if ($progress['ranked'])of {{ number_format($progress['ranked']) }}@endif</small></div>
            <div class="pd-kpi"><b>{{ $progress['attempts'] }}</b><small>Tests attempted</small></div>
            <div class="pd-kpi"><b>{{ $progress['accuracy'] !== null ? $progress['accuracy'] . '%' : '—' }}</b><small>Accuracy</small></div>
            <div class="pd-kpi"><b>{{ $dur($progress['secs']) }}</b><small>Time on tests</small></div>
            <div class="pd-kpi"><b>{{ $progress['streak'] }} day{{ $progress['streak'] == 1 ? '' : 's' }}</b><small>Current streak (best {{ $progress['bestStreak'] }})</small></div>
            <div class="pd-kpi"><b>{{ $progress['activeDays'] }}</b><small>Days practised</small></div>
            <div class="pd-kpi"><b>{{ $dur($activity['classMinutes'] * 60) }}</b><small>Watching classes</small></div>
        </div>
        <div class="mt-3 small">@if ($lv['next']) {{ number_format($lv['need']) }} more coins to reach <b>{{ $lv['next'] }}</b> @else You are at the top level — <b>{{ $lv['name'] }}</b> @endif
            @if ($progress['topPercent'])<span class="float-end">Top {{ $progress['topPercent'] }}%</span>@endif</div>
        <div class="pd-bar mt-1"><i style="width:{{ $lv['pct'] }}%"></i></div>
        <div class="small mt-2" style="color:var(--pv-muted)">Earn {{ \App\Services\StudentProgress::COINS_PER_ATTEMPT }} coins for every test you finish and {{ \App\Services\StudentProgress::COINS_PER_RIGHT }} for every correct answer.</div>
    </div>

    {{-- analytics graph --}}
    <div class="pd-card">
        <h2><i class="bi bi-graph-up-arrow"></i> My activity</h2>
        <div class="pd-tabs" id="pdTabs"><button type="button" data-p="day" class="on">Daily</button><button type="button" data-p="month">Monthly</button><button type="button" data-p="year">Yearly</button></div>
        <div style="position:relative;height:230px"><canvas id="pdChart"></canvas></div>
        <div class="small mt-2" style="color:var(--pv-muted)">Tests attempted, minutes spent on tests and on watching classes, and coins earned.</div>
        <div class="pd-grid mt-3">
            <div class="pd-kpi"><b>{{ $activity['visits'] }}</b><small>Visits, last 30 days</small></div>
            <div class="pd-kpi"><b>{{ $activity['classesWatched'] }}</b><small>Classes watched</small></div>
            <div class="pd-kpi"><b>{{ $activity['comments'] + $activity['discussions'] }}</b><small>Comments &amp; discussions</small></div>
            <div class="pd-kpi"><b>{{ $activity['likes'] }}</b><small>Likes</small></div>
        </div>
        @if ($activity['lastSeen'])<div class="small mt-2" style="color:var(--pv-muted)">Last active {{ \Illuminate\Support\Carbon::parse($activity['lastSeen'])->diffForHumans() }} on {{ $activity['platforms']->pluck('platform')->unique()->implode(', ') }}</div>@endif
    </div>

    {{-- by exam type of test --}}
    @if ($progress['byKind']->count())
    <div class="pd-card">
        <h2><i class="bi bi-pie-chart"></i> Where I practise</h2>
        @foreach ($progress['byKind'] as $kind => $k)
            @php $pct = $progress['attempts'] ? round($k->attempts / $progress['attempts'] * 100) : 0; @endphp
            <div class="mb-2"><div class="d-flex justify-content-between small"><span>{{ $kind }} · {{ $k->attempts }} tests · {{ $k->rights + $k->wrong ? round($k->rights / ($k->rights + $k->wrong) * 100) : 0 }}% accuracy @if ($k->secs) · {{ $dur($k->secs) }}@endif</span><span>{{ $pct }}%</span></div><div class="pd-bar"><i style="width:{{ $pct }}%"></i></div></div>
        @endforeach
    </div>
    @endif

    @include('kpsc.hub.referral')

    {{-- leaderboard --}}
    <div class="pd-card" id="pf-ranking">
        <h2><i class="bi bi-trophy" style="color:#d97706"></i> Overall ranking <span class="ms-auto small" style="font-weight:600">Top 10 &amp; my rank</span></h2>
        @if ($board['rows']->count())
        <table class="pd-tbl"><tbody>
            @foreach ($board['rows'] as $r)
                <tr class="{{ $r->me ? 'me' : '' }}"><td style="width:34px">{{ $r->pos <= 3 ? ['🥇','🥈','🥉'][$r->pos - 1] : $r->pos }}</td><td>{{ \Illuminate\Support\Str::limit($r->name, 26) }}@if ($r->me) (you)@endif</td><td class="text-end">{{ $r->attempts }} tests</td><td class="text-end"><b>{{ number_format($r->coins) }}</b> <i class="bi bi-coin" style="color:#d97706"></i></td></tr>
            @endforeach
            @if (!$board['meInTop'] && $progress['rank'])
                <tr class="me"><td>{{ $progress['rank'] }}</td><td>You</td><td class="text-end">{{ $progress['attempts'] }} tests</td><td class="text-end"><b>{{ number_format($progress['coins']) }}</b> <i class="bi bi-coin" style="color:#d97706"></i></td></tr>
            @endif
        </tbody></table>
        @else<div class="small" style="color:var(--pv-muted)">No one is on the board yet — finish a test to be the first.</div>@endif
    </div>

    {{-- attempt history --}}
    <div class="pd-card">
        <h2><i class="bi bi-journal-check"></i> My exam attempts</h2>
        @forelse ($progress['recent'] as $r)
            <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--pv-border,#e5e7eb)">
                <div style="min-width:0"><div class="fw-bold small text-truncate">{{ $r->title }}</div>
                    <div class="small" style="color:var(--pv-muted)">{{ $r->kind }} · {{ \Illuminate\Support\Carbon::parse($r->at)->format('d M Y') }}@if ($r->secs) · {{ $dur($r->secs) }}@endif</div></div>
                <div class="text-end small"><b style="color:#0a8f5b">{{ (int) $r->rights }}</b> / <span style="color:#e5484d">{{ (int) $r->wrong }}</span> / {{ (int) $r->skipped }}<div style="color:var(--pv-muted)">right / wrong / skipped</div></div>
            </div>
        @empty<div class="small" style="color:var(--pv-muted)">You have not finished a test yet.</div>@endforelse
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
    (function () {
        var data = @json($trend), chart, cv = document.getElementById('pdChart');
        function draw(d) {
            if (!window.Chart) return;
            var cfg = { type: 'bar', data: { labels: d.labels, datasets: [
                { label: 'Tests', data: d.tests, backgroundColor: '#0a8f5b', yAxisID: 'y', order: 2 },
                { label: 'Test minutes', data: d.testMin, type: 'line', borderColor: '#3b82f6', backgroundColor: '#3b82f6', tension: .3, yAxisID: 'y1', order: 1 },
                { label: 'Class minutes', data: d.videoMin, type: 'line', borderColor: '#e5484d', backgroundColor: '#e5484d', tension: .3, yAxisID: 'y1', order: 1 },
                { label: 'Coins', data: d.coins, type: 'line', borderColor: '#d97706', backgroundColor: '#d97706', borderDash: [5, 4], tension: .3, yAxisID: 'y1', order: 1, hidden: true }
            ] }, options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { labels: { boxWidth: 10, usePointStyle: true } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Tests' } }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Minutes / coins' } },
                    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } } } } };
            if (chart) chart.destroy();
            chart = new Chart(cv, cfg);
        }
        function ready(f) { if (window.Chart) f(); else setTimeout(function () { ready(f); }, 100); }
        ready(function () { draw(data); });
        document.getElementById('pdTabs').addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            this.querySelectorAll('button').forEach(function (x) { x.classList.toggle('on', x === b); });
            fetch('{{ url('my-account/trend') }}?period=' + b.dataset.p, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(draw);
        });
    })();
    </script>

    <div class="pf-menu">
        {{-- edit profile --}}
        <details class="pf-item" id="pf-profile" @if ($open('profile')) open @endif>
            <summary><span class="ic"><i class="bi bi-person"></i></span><span>Edit profile<small>Name, photo, contact and address</small></span><i class="bi bi-chevron-right ch"></i></summary>
            <div class="pf-body">
                <form class="pf-form" method="POST" action="{{ url('my-account/profile') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="full d-flex align-items-center gap-3">
                        <div class="pf-av" style="width:58px;height:58px;font-size:1.4rem;border-color:var(--pv-border)">@if ($user->photoUrl())<img src="{{ $user->photoUrl() }}" alt="">@else{{ $first }}@endif</div>
                        <div style="flex:1"><label for="pfPhoto">Profile photo</label><input id="pfPhoto" type="file" name="photo" accept="image/*">{!! $err('photo') !!}
                            @if ($user->image)<label class="mt-1" style="font-weight:500"><input type="checkbox" name="remove_photo" value="1" style="width:auto"> Remove photo</label>@endif</div>
                    </div>
                    <div><label for="pfName">Full name *</label><input id="pfName" name="name" required maxlength="120" value="{{ old('name', $user->name) }}" autocomplete="name">{!! $err('name') !!}</div>
                    <div><label for="pfMobile">Mobile</label><input id="pfMobile" name="mobile" inputmode="tel" maxlength="15" value="{{ old('mobile', $user->mobile) }}" autocomplete="tel">{!! $err('mobile') !!}</div>
                    <div><label for="pfEmail">Email</label><input id="pfEmail" type="email" name="email" maxlength="150" value="{{ old('email', $user->email) }}" autocomplete="email">{!! $err('email') !!}</div>
                    <div><label for="pfDob">Date of birth</label><input id="pfDob" type="date" name="dob" value="{{ old('dob', $user->dob ? \Illuminate\Support\Carbon::parse($user->dob)->format('Y-m-d') : '') }}">{!! $err('dob') !!}</div>
                    <div><label for="pfGender">Gender</label><select id="pfGender" name="gender"><option value="">Prefer not to say</option>@foreach (['Male', 'Female', 'Other'] as $g)<option @selected(old('gender', $user->gender) === $g)>{{ $g }}</option>@endforeach</select></div>
                    <div><label for="pfPin">Pincode</label><input id="pfPin" name="pincode" inputmode="numeric" maxlength="6" value="{{ old('pincode', $user->pincode) }}">{!! $err('pincode') !!}</div>
                    <div class="full"><label for="pfAddr">Address</label><input id="pfAddr" name="address" maxlength="255" value="{{ old('address', $user->address) }}" autocomplete="street-address"></div>
                    <div><label for="pfCity">City</label><input id="pfCity" name="city" maxlength="80" value="{{ old('city', $user->city) }}"></div>
                    <div><label for="pfState">State</label><input id="pfState" name="state" maxlength="80" value="{{ old('state', $user->state ?: 'Kerala') }}"></div>
                    <div class="full"><button class="hb-btn"><i class="bi bi-check-lg"></i> Save profile</button></div>
                </form>
            </div>
        </details>

        {{-- password --}}
        <details class="pf-item" id="pf-password" @if ($open('password')) open @endif>
            <summary><span class="ic"><i class="bi bi-shield-lock"></i></span><span>{{ $user->password ? 'Change password' : 'Set a password' }}<small>{{ $user->password ? 'Keep your account safe' : 'You signed in with Google - add a password to sign in with email too' }}</small></span><i class="bi bi-chevron-right ch"></i></summary>
            <div class="pf-body">
                <form class="pf-form" method="POST" action="{{ url('my-account/password') }}">
                    @csrf
                    @if ($user->password)<div class="full"><label for="pwCur">Current password</label><input id="pwCur" type="password" name="current_password" required autocomplete="current-password">{!! $err('current_password') !!}</div>@endif
                    <div><label for="pwNew">New password</label><input id="pwNew" type="password" name="password" required minlength="6" autocomplete="new-password">{!! $err('password') !!}</div>
                    <div><label for="pwRe">Repeat new password</label><input id="pwRe" type="password" name="password_confirmation" required minlength="6" autocomplete="new-password"></div>
                    <div class="full"><button class="hb-btn"><i class="bi bi-key"></i> Save password</button></div>
                </form>
            </div>
        </details>

        {{-- my courses --}}
        <details class="pf-item" id="pf-courses" @if ($open('courses')) open @endif>
            <summary><span class="ic"><i class="bi bi-mortarboard"></i></span><span>My courses<small>{{ $admissions->count() ? $admissions->count() . ' active' : 'You have not joined a course yet' }}</small></span><i class="bi bi-chevron-right ch"></i></summary>
            <div class="pf-body">
                @forelse ($admissions as $a)
                    <a class="hb-row mt-2" href="{{ url('my-courses/' . $a->course->id) }}">
                        <span class="th" @if ($a->course->cover_image) style="background-image:url('{{ Storage::url($a->course->cover_image) }}')" @endif>@unless ($a->course->cover_image)<i class="bi bi-mortarboard"></i>@endunless</span>
                        <span class="tx"><b>{{ $a->course->title }}</b><small>{{ $a->valid_until ? 'Valid till ' . $a->valid_until->format('d M Y') : 'Lifetime access' }}</small></span>
                    </a>
                @empty
                    <div class="hb-empty mt-2"><i class="bi bi-journal-plus"></i>No course yet. <a href="{{ url('courses') }}">Explore courses</a></div>
                @endforelse
            </div>
        </details>

        {{-- orders --}}
        <details class="pf-item" id="pf-orders" @if ($open('orders')) open @endif>
            <summary><span class="ic"><i class="bi bi-receipt"></i></span><span>Orders &amp; payments<small>Your purchases and their status</small></span><i class="bi bi-chevron-right ch"></i></summary>
            <div class="pf-body">
                @if ($orders->count())
                    <div style="overflow-x:auto"><table class="pf-table">
                        <thead><tr><th>Course</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>@foreach ($orders as $o)@php $st = $o->displayStatus(); @endphp
                            <tr><td>{{ $o->course->title ?? 'Course' }}</td><td>{{ $o->amount > 0 ? '₹' . number_format($o->amount, 2) : 'Free' }}</td><td style="white-space:nowrap">{{ optional($o->admitted_at ?? $o->created_at)->format('d M Y') }}</td>
                                <td><span class="pf-st" style="color:{{ $stColor[$st] ?? '#66755d' }};background:color-mix(in srgb, {{ $stColor[$st] ?? '#66755d' }} 12%, transparent)">{{ \App\Models\CourseAdmission::STATUSES[$st][0] ?? ucfirst($st) }}</span></td></tr>
                        @endforeach</tbody>
                    </table></div>
                @else
                    <div class="hb-empty mt-2"><i class="bi bi-receipt"></i>No orders yet.</div>
                @endif
            </div>
        </details>

        {{-- exams & favourites --}}
        <details class="pf-item" id="pf-exams" @if ($open('exams')) open @endif>
            <summary><span class="ic"><i class="bi bi-bullseye"></i></span><span>My exams &amp; favourites<small>{{ $interests->count() }} exams · {{ $favs->count() }} favourite categories</small></span><i class="bi bi-chevron-right ch"></i></summary>
            <div class="pf-body">
                <div class="hb-chips mt-2" style="flex-wrap:wrap">
                    @forelse ($interests as $e)<a class="hb-chip" href="{{ url('exams') }}#exam-{{ $e->id }}">{{ $e->short_name ?: $e->name }}</a>@empty<span class="small" style="color:var(--pv-muted)">No exam selected yet.</span>@endforelse
                </div>
                @if ($favs->count())
                    <div class="small fw-bold mt-2" style="color:var(--pv-muted)">Favourite categories</div>
                    <div class="hb-chips" style="flex-wrap:wrap">@foreach ($favs as $f)<a class="hb-chip" href="{{ url('prime?exam=' . ($f->exam->slug ?? '') . '&category=' . $f->id) }}#courses"><i class="bi bi-heart-fill" style="color:#e5484d"></i> {{ $f->name }}<span class="n">{{ $f->exam->short_name ?? '' }}</span></a>@endforeach</div>
                @endif
                <a class="hb-btn sm mt-2" href="{{ url('exams') }}"><i class="bi bi-sliders"></i> Manage exams</a>
            </div>
        </details>

        {{-- settings --}}
        <details class="pf-item" id="pf-settings" @if ($open('settings')) open @endif>
            <summary><span class="ic"><i class="bi bi-gear"></i></span><span>Settings<small>Theme and notifications</small></span><i class="bi bi-chevron-right ch"></i></summary>
            <div class="pf-body">
                <div class="small fw-bold mt-2" style="color:var(--pv-muted)">Theme</div>
                <div class="pf-theme" role="group" aria-label="Theme">
                    <button type="button" class="hb-btn sm ghost" data-set-theme="light"><i class="bi bi-sun"></i> Light</button>
                    <button type="button" class="hb-btn sm ghost" data-set-theme="dark"><i class="bi bi-moon-stars"></i> Dark</button>
                    <button type="button" class="hb-btn sm ghost" data-set-theme="auto"><i class="bi bi-phone"></i> Device</button>
                </div>
                <div class="small fw-bold mt-3" style="color:var(--pv-muted)">Notifications</div>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-1"><span class="small" id="pfPush" style="color:var(--pv-text)"></span><button type="button" class="hb-btn sm ghost" id="pfPushBtn" hidden><i class="bi bi-bell"></i> Turn on</button></div>
            </div>
        </details>

        <a class="pf-link" href="{{ url('my-tests') }}"><span class="ic"><i class="bi bi-ui-checks"></i></span>My tests<i class="bi bi-chevron-right ms-auto" style="color:var(--pv-muted)"></i></a>
        <a class="pf-link" href="{{ url('my-tests/analysis') }}"><span class="ic"><i class="bi bi-graph-up-arrow"></i></span>Test performance analysis<i class="bi bi-chevron-right ms-auto" style="color:var(--pv-muted)"></i></a>
        <a class="pf-link" href="{{ url('/') }}#counselling"><span class="ic"><i class="bi bi-headset"></i></span>Talk to a counsellor<i class="bi bi-chevron-right ms-auto" style="color:var(--pv-muted)"></i></a>
        <a class="pf-link" href="{{ url('contact-us') }}"><span class="ic"><i class="bi bi-life-preserver"></i></span>Help &amp; support<i class="bi bi-chevron-right ms-auto" style="color:var(--pv-muted)"></i></a>
        <a class="pf-link" href="{{ url('info/terms-and-conditions') }}"><span class="ic"><i class="bi bi-file-earmark-text"></i></span>Terms &amp; privacy<i class="bi bi-chevron-right ms-auto" style="color:var(--pv-muted)"></i></a>
        <a class="pf-link out" href="{{ url('sign-out') }}"><span class="ic"><i class="bi bi-box-arrow-right"></i></span>Sign out</a>
    </div>
</div></div></div>
@endsection
@section('scripts')
<script>
    (function () {
        // theme: light / dark / follow the device
        function mark() {
            var saved = null; try { saved = localStorage.getItem('theme'); } catch (e) {}
            [].forEach.call(document.querySelectorAll('[data-set-theme]'), function (b) { b.classList.toggle('on', b.dataset.setTheme === (saved || 'auto')); });
        }
        [].forEach.call(document.querySelectorAll('[data-set-theme]'), function (b) {
            b.addEventListener('click', function () {
                var t = b.dataset.setTheme;
                try { t === 'auto' ? localStorage.removeItem('theme') : localStorage.setItem('theme', t); } catch (e) {}
                document.documentElement.setAttribute('data-theme', t === 'auto' ? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : t);
                mark();
            });
        });
        mark();
        // notifications: show the browser's permission and offer to turn it on
        var lbl = document.getElementById('pfPush'), btn = document.getElementById('pfPushBtn');
        function show() {
            if (!('Notification' in window)) { lbl.textContent = 'This browser does not support notifications.'; return; }
            var p = Notification.permission;
            lbl.textContent = p === 'granted' ? 'On - you will get exam alerts here.' : (p === 'denied' ? 'Blocked - allow notifications for this site in your browser settings.' : 'Off');
            btn.hidden = p !== 'default';
        }
        btn.addEventListener('click', function () { Notification.requestPermission().then(function () { show(); if (window.pvWebPushInit) window.pvWebPushInit(); }); });
        show();
        // open the section named in the address (?section=... or #pf-...)
        var el = document.querySelector('details[open]') || (location.hash && document.querySelector(location.hash));
        if (el && el.tagName === 'DETAILS') { el.open = true; el.scrollIntoView({ block: 'start' }); }
    })();
</script>
@endsection
