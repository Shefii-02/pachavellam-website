@extends('admin.student.section')
@section('content2')
@php
    [$stLabel, $stColor] = \App\Services\StudentFilter::activityStatus($lastActivity);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Student Profile</h3>
    <a href="{{ student_cms('student-list') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-xl me-3">
                        <img class="avatar-img rounded-circle" src="{{ $user->image ? asset('storage/users/' . $user->image) : asset('images/favicon.png') }}" alt="" onerror="this.style.display='none'">
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $user->name ?: '(no name)' }}</h4>
                        <span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span>
                        <span class="badge bg-secondary">{{ $user->type }}</span>
                    </div>
                </div>
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted fw-normal">ID</th><td>#{{ $user->id }}</td></tr>
                    <tr><th class="text-muted fw-normal">Email</th><td>{{ $user->email ?: '—' }}</td></tr>
                    <tr><th class="text-muted fw-normal">Mobile</th>
                        <td>{{ $user->mobile ?: '—' }}
                            @if ($user->mobile)
                                <span class="badge bg-{{ $user->mobile_verified_at ? 'success' : 'warning text-dark' }}">{{ $user->mobile_verified_at ? 'verified' : 'not verified' }}</span>
                            @endif
                        </td></tr>
                    <tr><th class="text-muted fw-normal">Signup</th><td>{{ \App\Services\StudentFilter::signupMethod($user) }}</td></tr>
                    <tr><th class="text-muted fw-normal">Gender / DOB</th><td>{{ $user->gender ?: '—' }} / {{ $user->dob ?: '—' }}</td></tr>
                    <tr><th class="text-muted fw-normal">Location</th><td>{{ collect([$user->city, $user->state, $user->pincode])->filter()->implode(', ') ?: '—' }}</td></tr>
                    <tr><th class="text-muted fw-normal">Joined</th><td>{{ $user->created_at?->format('d M Y, h:i A') }} ({{ $user->created_at?->diffForHumans() }})</td></tr>
                    <tr><th class="text-muted fw-normal">Last activity</th><td>{{ $lastActivity ? \Carbon\Carbon::parse($lastActivity)->format('d M Y, h:i A') . ' (' . \Carbon\Carbon::parse($lastActivity)->diffForHumans() . ')' : 'never' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Usage by area</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Area</th><th>Records</th><th>Last used</th></tr></thead>
                    <tbody>
                        @foreach ($areas as $a)
                            <tr class="{{ $a['count'] ? '' : 'text-muted' }}">
                                <td>{{ $a['label'] }}</td>
                                <td>{{ number_format($a['count']) }}</td>
                                <td>{{ $a['last'] ? \Carbon\Carbon::parse($a['last'])->diffForHumans() : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- coins, rank and activity analytics --}}
@php $dur = fn ($x) => \App\Services\StudentProgress::duration((int) $x); @endphp
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center"><h5 class="mb-0"><i class="bi bi-coin me-1 text-warning"></i>Coins, rank &amp; activity</h5>
        <span class="badge bg-secondary">{{ $progress['level']['name'] }}</span></div>
    <div class="card-body">
        <div class="row g-3 text-center">
            @foreach ([['Coins', number_format($progress['coins'])], ['Overall rank', $progress['rank'] ? '#' . number_format($progress['rank']) . ' of ' . number_format($progress['ranked']) : '-'], ['Tests finished', $progress['attempts']], ['Accuracy', $progress['accuracy'] !== null ? $progress['accuracy'] . '%' : '-'],
                ['Time on tests', $dur($progress['secs'])], ['Watching classes', $dur($activity['classMinutes'] * 60)], ['Streak (best)', $progress['streak'] . ' (' . $progress['bestStreak'] . ')'], ['Visits, 30 days', $activity['visits']]] as [$l, $v])
                <div class="col-6 col-md-3"><div class="border rounded-3 py-2"><div class="fs-5 fw-bold">{{ $v }}</div><div class="small text-muted">{{ $l }}</div></div></div>
            @endforeach
        </div>
        <div class="small text-muted mt-2">Coins: {{ number_format($progress['testCoins']) }} from tests + {{ number_format($progress['bonusCoins']) }} from referrals and bonuses.</div>
        <div style="position:relative;height:220px" class="mt-3"><canvas id="stChart"></canvas></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
        (function () { var d = @json($trend), go = function () { if (!window.Chart) return setTimeout(go, 100);
            new Chart(document.getElementById('stChart'), { type: 'bar', data: { labels: d.labels, datasets: [
                { label: 'Tests', data: d.tests, backgroundColor: '#0a8f5b', yAxisID: 'y' },
                { label: 'Test minutes', data: d.testMin, type: 'line', borderColor: '#3b82f6', backgroundColor: '#3b82f6', tension: .3, yAxisID: 'y1' },
                { label: 'Class minutes', data: d.videoMin, type: 'line', borderColor: '#e5484d', backgroundColor: '#e5484d', tension: .3, yAxisID: 'y1' }] },
              options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } } } } }); }; go(); })();
        </script>
    </div>
</div>

{{-- refer & earn --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="bi bi-gift me-1 text-success"></i>Refer &amp; earn</h5></div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="small text-muted">Own code</div><div class="fw-bold" style="letter-spacing:2px">{{ $referral['code'] ?: 'not made yet' }}</div>
                <div class="small text-muted mt-2">Joined through</div>
                @if ($referral['invitedBy'])
                    <div><a href="{{ student_cms('student-list/' . $referral['invitedBy']->uid) }}">{{ $referral['invitedBy']->name ?: '#' . $referral['invitedBy']->uid }}</a> <span class="text-muted small">{{ $referral['invitedBy']->mobile }}</span>
                        <span class="badge bg-{{ $referral['invitedBy']->status === 'rewarded' ? 'success' : 'warning text-dark' }}">{{ $referral['invitedBy']->status === 'rewarded' ? 'rewarded ' . $referral['invitedBy']->referred_coins . ' coins' : 'waiting' }}</span>
                        <span class="badge bg-secondary">{{ ['web' => 'Website', 'android' => 'Android', 'ios' => 'iPhone'][$referral['invitedBy']->platform] ?? $referral['invitedBy']->platform }}</span></div>
                @else<div class="text-muted">Not through a referral link</div>@endif
            </div>
            <div class="col-md-6">
                <div class="row g-2 text-center">
                    @foreach ([['Friends joined', $referral['friends']->count()], ['Coins earned', number_format($referral['earned'])], ['Their tests', $referral['tests']], ['They spent', '₹' . number_format($referral['spent'])]] as [$l, $v])
                        <div class="col-6"><div class="border rounded-3 py-2"><div class="fw-bold">{{ $v }}</div><div class="small text-muted">{{ $l }}</div></div></div>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($referral['friends']->count())
            <div class="table-responsive"><table class="table table-sm align-middle mb-3">
                <thead><tr><th>Student invited</th><th>Joined</th><th>Device</th><th>Status</th><th class="text-center">Tests</th><th>Last active</th><th class="text-center">Courses</th><th class="text-end">Spent</th><th class="text-end">Their coins</th><th class="text-end">You earned</th></tr></thead>
                <tbody>@foreach ($referral['friends'] as $f)
                    <tr><td><a href="{{ student_cms('student-list/' . $f->uid) }}">{{ $f->name ?: '#' . $f->uid }}</a><div class="small text-muted">{{ $f->mobile ?: $f->email }}</div></td>
                        <td class="small">{{ \Illuminate\Support\Carbon::parse($f->created_at)->format('d M Y') }}</td>
                        <td>{{ ['web' => 'Website', 'android' => 'Android', 'ios' => 'iPhone'][$f->platform] ?? $f->platform }}</td>
                        <td><span class="badge bg-{{ ['joined' => 'warning text-dark', 'rewarded' => 'success', 'rejected' => 'secondary'][$f->status] ?? 'secondary' }}">{{ ['joined' => 'Waiting', 'rewarded' => 'Rewarded', 'rejected' => 'Rejected'][$f->status] ?? $f->status }}</span></td>
                        <td class="text-center">{{ $f->tests }}</td>
                        <td class="small">{{ $f->last_active ? \Illuminate\Support\Carbon::parse($f->last_active)->diffForHumans() : '-' }}</td>
                        <td class="text-center">{{ $f->courses }}</td><td class="text-end">₹{{ number_format($f->spent) }}</td>
                        <td class="text-end">{{ number_format($f->coins_total) }}</td><td class="text-end">{{ $f->referrer_coins }}</td></tr>
                @endforeach</tbody></table></div>
        @else<div class="text-muted small mb-3">This student has not brought anyone yet.</div>@endif

        <div class="row g-3">
            <div class="col-md-5">
                <form method="POST" action="{{ student_cms('student-list/' . $user->id . '/coins') }}" class="border rounded-3 p-3">@csrf
                    <div class="fw-semibold mb-2">Give or take coins</div>
                    <div class="input-group input-group-sm mb-2"><input type="number" name="coins" class="form-control" placeholder="e.g. 50 or -20" required><span class="input-group-text"><i class="bi bi-coin"></i></span></div>
                    <input name="note" class="form-control form-control-sm mb-2" maxlength="150" placeholder="Reason (shown in the list)">
                    <button class="btn btn-sm btn-success mb-0">Save</button>
                </form>
            </div>
            <div class="col-md-7">
                <div class="fw-semibold mb-1">Referral &amp; bonus coins</div>
                <table class="table table-sm mb-0"><tbody>
                    @forelse ($referral['ledger'] as $l)<tr><td class="small text-muted">{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('d M Y') }}</td><td>{{ $l->note ?: $l->type }}</td><td class="text-end fw-bold {{ $l->coins < 0 ? 'text-danger' : 'text-success' }}">{{ $l->coins > 0 ? '+' : '' }}{{ $l->coins }}</td></tr>@empty<tr><td class="text-muted small">No referral or bonus coins yet.</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
    </div>
</div>

{{-- platforms / devices --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Platforms &amp; devices</h5></div>
    <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Platform</th><th>App version</th><th>Device</th><th>Last active</th><th>First seen</th></tr></thead>
            <tbody>
                @forelse ($devices as $dv)
                    <tr>
                        <td><span class="badge bg-{{ ['ios' => 'dark', 'android' => 'success', 'web' => 'primary'][$dv->platform] ?? 'secondary' }}">{{ \App\Services\ClientInfo::label($dv->platform) }}</span></td>
                        <td>{{ $dv->app_version ? $dv->app_version . ($dv->app_build ? ' (' . $dv->app_build . ')' : '') : '—' }}</td>
                        <td>{{ $dv->device_model ?: '—' }}@if ($dv->os_version) <span class="text-muted small">· {{ $dv->os_version }}</span>@endif</td>
                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($dv->last_active_at)->diffForHumans() }} <span class="small text-muted">({{ \Carbon\Carbon::parse($dv->last_active_at)->format('d M Y, h:i A') }})</span></td>
                        <td class="small">{{ $dv->first_seen_at ? \Carbon\Carbon::parse($dv->first_seen_at)->format('d M Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted small p-3">No platform recorded yet. It is captured when the student next opens the app or website.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- exam interests --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Exam interests</h5></div>
    <div class="card-body">
        <form action="{{ student_cms('student-list/' . $user->id . '/interests') }}" method="POST" class="d-flex flex-wrap align-items-center gap-3">
            @csrf
            @foreach ($allExams as $ex)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="exam_ids[]" value="{{ $ex->id }}" id="ex{{ $ex->id }}" {{ in_array($ex->id, $interestIds) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ex{{ $ex->id }}"><span class="badge text-white" style="background:{{ $ex->color ?: '#6c757d' }}">{{ $ex->short_name ?: $ex->name }}</span>@unless ($ex->status) <small class="text-muted">(hidden)</small>@endunless</label>
                </div>
            @endforeach
            <button class="btn btn-sm btn-primary mb-0">Save interests</button>
        </form>
        @if (!$interestIds)<div class="small text-muted mt-2">This student has not chosen any exam yet.</div>@endif
    </div>
</div>

{{-- performance in each interested exam --}}
@foreach ($performance as $p)
    @php $d = $p['data']; $ex = $p['exam']; @endphp
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><span class="badge text-white" style="background:{{ $ex->color ?: '#6c757d' }}">{{ $ex->short_name ?: $ex->name }}</span> Performance</h5>
            @if ($d)<span class="badge bg-{{ ['Improving' => 'success', 'Declining' => 'danger', 'Steady' => 'info', 'New activity' => 'primary'][$d['trend']] ?? 'secondary' }}">{{ $d['trend'] }}</span>@endif
        </div>
        <div class="card-body">
            @if (!$d)
                <div class="text-muted small">No graded exams are connected to {{ $ex->name }} yet.</div>
            @elseif (!$d['all'])
                <div class="text-muted small">No finished {{ strtolower($d['label']) }} yet.</div>
            @else
                @php $all = $d['all']; $m = $d['month']; $maxW = max(1, collect($d['weekly'])->max('attempts')); @endphp
                <div class="row g-3 mb-3">
                    @foreach ([
                        ['Rank (all time)', $d['standing'] ? '#' . $d['standing']['rank'] . ' of ' . number_format($d['standing']['of']) : '—'],
                        ['Rank (30 days)', $d['standing_month'] ? '#' . $d['standing_month']['rank'] . ' of ' . number_format($d['standing_month']['of']) : '—'],
                        ['Attempts', number_format($all->attempts) . ($m ? ' (' . $m->attempts . ' in 30d)' : '')],
                        ['Accuracy', $all->accuracy . '%' . ($m ? ' (' . $m->accuracy . '% in 30d)' : '')],
                        ['Total marks', $all->marks],
                        ['Average / best', $all->avg_marks . ' / ' . $all->best],
                    ] as [$label, $value])
                        <div class="col-6 col-lg-2"><div class="border rounded p-2"><div class="small text-muted">{{ $label }}</div><div class="fw-bold">{{ $value }}</div></div></div>
                    @endforeach
                </div>
                <div class="small text-muted mb-1">Weekly attempts and accuracy, last 8 weeks ({{ $d['label'] }})</div>
                <div class="d-flex align-items-end" style="gap:8px;height:100px">
                    @foreach ($d['weekly'] as $w)
                        <div class="flex-fill text-center" title="{{ $w['label'] }}: {{ $w['attempts'] }} attempts, {{ $w['accuracy'] }}% accuracy, {{ $w['marks'] }} marks">
                            <div class="small text-muted" style="font-size:10px">{{ $w['attempts'] ? $w['accuracy'] . '%' : '' }}</div>
                            <div class="bg-primary" style="height:{{ round($w['attempts'] / $maxW * 60) }}px;min-height:{{ $w['attempts'] ? 3 : 0 }}px"></div>
                            <div class="small text-muted" style="font-size:10px">{{ $w['label'] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="small text-muted mt-2">Rank is among students interested in {{ $ex->short_name ?: $ex->name }}, by total marks.</div>
            @endif
        </div>
    </div>
@endforeach

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Latest daily exams</h5></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Exam</th><th>Right</th><th>Wrong</th><th>Skipped</th><th>Marks</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse ($dailyExams as $e)
                            <tr><td>#{{ $e->exam_id }}</td><td class="text-success">{{ $e->right }}</td><td class="text-danger">{{ $e->wrong }}</td><td>{{ $e->skipped }}</td><td>{{ $e->total_mark }}</td><td class="text-nowrap">{{ \Carbon\Carbon::parse($e->created_at)->format('d M Y') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted text-center py-3">No daily exam attempts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Latest model exams</h5></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Exam</th><th>Right</th><th>Wrong</th><th>Not attended</th><th>Marks</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse ($modelExams as $e)
                            <tr><td>#{{ $e->exam_id }}</td><td class="text-success">{{ $e->right_answer }}</td><td class="text-danger">{{ $e->wrong_answer }}</td><td>{{ $e->not_attended }}</td><td>{{ $e->total_mark }}</td><td class="text-nowrap">{{ \Carbon\Carbon::parse($e->created_at)->format('d M Y') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted text-center py-3">No model exam attempts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Latest comments</h5></div>
            <ul class="list-group list-group-flush">
                @forelse ($comments as $c)
                    <li class="list-group-item small">{{ \Illuminate\Support\Str::limit($c->comment, 140) }}
                        <div class="text-muted">{{ \Carbon\Carbon::parse($c->created_at)->diffForHumans() }}</div></li>
                @empty
                    <li class="list-group-item text-muted small">No comments.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
                <h5 class="mb-0">Latest app requests</h5>
                <a class="small" href="{{ admin_general('dev-tools/api-logs') }}?user={{ $user->id }}">All →</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($requests as $r)
                    <li class="list-group-item small">
                        <span class="badge bg-secondary">{{ $r->method }}</span> {{ $r->path }}
                        <span class="badge bg-{{ $r->status >= 500 ? 'danger' : ($r->status >= 400 ? 'warning text-dark' : 'success') }}">{{ $r->status }}</span>
                        <div class="text-muted">{{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">No app requests logged yet (logging started recently).</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@if (\App\Support\StaffAccess::isAdmin(auth()->user()))
@php
    $chatBase = config('chat.server_url');
    $chatToken = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser(auth()->user());
@endphp
<div class="card bg-transparent border rounded-3 mb-4" id="stuChat">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-chat-dots me-1"></i>Conversations</h5>
        <a class="btn btn-sm btn-success mb-0" href="{{ url('admin/chat') }}?student={{ $user->id }}"><i class="bi bi-chat-left-text me-1"></i>Message this student</a>
    </div>
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-md-4 border-end" id="stuChatList" style="max-height:420px;overflow:auto"><div class="p-3 text-muted small">Loading…</div></div>
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2 small"><span id="stuChatTitle" class="fw-semibold text-muted">Pick a conversation</span><a href="#" id="stuChatOpen" class="d-none">Open in chat console <i class="bi bi-box-arrow-up-right"></i></a></div>
                <div id="stuChatMsgs" style="height:360px;overflow:auto;padding:12px;background:var(--bs-tertiary-bg)"></div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var SERVER = @json($chatBase), TOKEN = @json($chatToken), SID = {{ $user->id }}, PAGE = @json(url('admin/chat'));
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function api(path) { return fetch(SERVER + '/api' + path, { headers: { Authorization: 'Bearer ' + TOKEN } }).then(function (r) { return r.json().then(function (j) { if (!r.ok) throw new Error(j.message || 'Failed'); return j; }); }); }
    var list = document.getElementById('stuChatList');
    api('/console/people/' + SID).then(function (d) {
        if (!d.conversations.length) { list.innerHTML = '<div class="p-3 text-muted small">This student has no conversations yet.</div>'; return; }
        list.innerHTML = d.conversations.map(function (c) {
            return '<div class="px-3 py-2 border-bottom" role="button" data-id="' + c.id + '" data-name="' + esc(c.name) + '"><div class="fw-semibold text-truncate">' + esc(c.name) + ' <span class="badge ' + (c.type === 'group' ? 'bg-success' : 'bg-secondary') + '">' + (c.sub_type === 'course_group' ? 'course group' : c.type) + '</span></div><div class="small text-muted text-truncate">' + esc(c.last_message_preview || '—') + '</div></div>';
        }).join('');
    }).catch(function () { list.innerHTML = '<div class="p-3 text-danger small">The chat server is not reachable.</div>'; });
    list.addEventListener('click', function (e) {
        var r = e.target.closest('[data-id]'); if (!r) return;
        document.getElementById('stuChatTitle').textContent = r.dataset.name;
        var o = document.getElementById('stuChatOpen'); o.href = PAGE + '?conv=student:' + r.dataset.id; o.classList.remove('d-none');
        var box = document.getElementById('stuChatMsgs'); box.innerHTML = '<div class="text-muted small">Loading…</div>';
        api('/console/student-chats/' + r.dataset.id + '/messages?limit=80').then(function (ms) {
            box.innerHTML = ms.map(function (m) {
                var mine = m.sender_id === SID;
                return '<div class="d-flex ' + (mine ? 'justify-content-end' : '') + ' mb-1"><div class="px-2 py-1 rounded border ' + (mine ? 'bg-success-subtle' : 'bg-body') + '" style="max-width:75%"><div class="small fw-semibold" style="color:#6f42c1">' + esc(m.sender_name || '') + '</div>' + (m.deleted ? '<i class="text-muted">deleted</i>' : (m.attachment ? '<div class="small">📎 ' + esc(m.attachment.name || 'file') + '</div>' : '') + '<span style="white-space:pre-wrap">' + esc(m.body || '') + '</span>') + '<div class="text-end text-muted" style="font-size:.68rem">' + new Date(m.created_at).toLocaleString() + '</div></div></div>';
            }).join('') || '<div class="text-muted small">No messages.</div>';
            box.scrollTop = box.scrollHeight;
        }).catch(function (er) { box.innerHTML = '<div class="text-danger small">' + esc(er.message) + '</div>'; });
    });
})();
</script>
@endif
@endsection
