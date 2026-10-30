{{-- My analytics + question & answer summary after the exam: needs $summary (McqExam::summary or TestEngine::summary) --}}
            @php
                $S = $summary;
                $clock = fn ($sec) => $sec === null ? '-' : floor($sec / 60) . 'm ' . ($sec % 60) . 's';
            @endphp
            <style>
                .xs-card { --xs-ok:#0a8f5b; --xs-bad:#d93025; --xs-skip:#8a929a; background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:16px; padding:16px; margin-top:16px; color:var(--pv-text,#111); }
                [data-theme="dark"] .xs-card { --xs-ok:#6fd49b; --xs-bad:#ff8a80; --xs-skip:#9db09c; }
                .xs-card h5 { font-weight:800; margin:0 0 10px; font-size:1.05rem; color:var(--pv-text,#111); }
                .xs-card h5 small { color:var(--pv-muted,#667) !important; }
                .xs-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(110px,1fr)); gap:10px; } .xs-grid div { background:var(--pv-chip,#eef0f3); border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; padding:10px; text-align:center; color:var(--pv-text,#111); } .xs-grid b { display:block; font-size:1.2rem; color:var(--pv-text,#111); } .xs-grid small { color:var(--pv-muted,#667); font-weight:700; }
                .xs-tbl { width:100%; font-size:.85rem; margin-top:12px; color:var(--pv-text,#111); } .xs-tbl th { color:var(--pv-muted,#667); font-weight:700; } .xs-tbl td, .xs-tbl th { padding:6px 8px; border-bottom:1px solid var(--pv-border,#e5e7eb); background:transparent; color:inherit; } .xs-tbl th { color:var(--pv-muted,#667); }
                .xs-tbl a { color:var(--pv-brand,#0a8f5b); font-weight:700; }
                .xs-f { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; } .xs-f button { border:1px solid var(--pv-border,#e5e7eb); border-radius:18px; padding:5px 14px; font-size:.8rem; font-weight:800; background:var(--pv-chip,#eef0f3); color:var(--pv-text,#111); } .xs-f button.on { background:#0a8f5b; border-color:#0a8f5b; color:#fff; }
                .xs-q { border:1px solid var(--pv-border,#e5e7eb); border-left-width:4px; border-radius:12px; padding:12px; margin-bottom:10px; font-size:.9rem; color:var(--pv-text,#111); background:transparent; }
                .xs-q.correct { border-left-color:var(--xs-ok); } .xs-q.wrong { border-left-color:var(--xs-bad); } .xs-q.skipped { border-left-color:var(--xs-skip); }
                .xs-q * { color:inherit; } .xs-q img { max-width:100%; height:auto; }
                .xs-ans { margin:6px 0 0; } .xs-ans .xs-bad, .xs-ans .xs-bad * { color:var(--xs-bad); font-weight:700; } .xs-ans .xs-ok, .xs-ans .xs-ok * { color:var(--xs-ok); font-weight:700; } .xs-ans .xs-skip { color:var(--pv-muted,#667); }
                .xs-meta { font-size:.78rem; color:var(--pv-muted,#667); margin-top:6px; } .xs-note { margin-top:8px; padding:8px 10px; background:var(--pv-surface-2,#eef0f3); color:var(--pv-text,#111); border-radius:10px; font-size:.85rem; }
            </style>
            <div class="xs-card">
                <h5>My analytics</h5>
                <div class="xs-grid">
                    <div><b>{{ $S['count'] }}</b><small>Attempts</small></div>
                    <div><b>{{ $S['best'] }}</b><small>Best mark</small></div>
                    <div><b>{{ $S['avg'] }}</b><small>Average mark</small></div>
                    <div><b>{{ $S['accuracy'] }}%</b><small>Accuracy</small></div>
                    <div><b>{{ $clock($S['avgSec']) }}</b><small>Avg time taken</small></div>
                    <div><b>{{ $clock($S['perQ']) }}</b><small>Avg time / question</small></div>
                    @if ($S['rank'])<div><b>{{ $S['rank'] }}<small style="display:inline"> / {{ $S['participants'] }}</small></b><small>Rank (1st attempt)</small></div>@endif
                    <div><b>{{ $S['average'] }}</b><small>Class average</small></div>
                </div>
                <table class="xs-tbl">
                    <thead><tr><th>Attempt</th><th>Marks</th><th>Right</th><th>Wrong</th><th>Skipped</th><th>Time</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($S['attempts'] as $a)
                        <tr><td>#{{ $a['no'] }}<div class="xs-meta">{{ $a['at'] }}</div></td><td><b>{{ $a['mark'] }}</b></td><td>{{ $a['right'] }}</td><td>{{ $a['wrong'] }}</td><td>{{ $a['skipped'] }}</td><td>{{ $clock($a['sec']) }}</td><td><a href="{{ $a['url'] }}">Result</a></td></tr>
                    @endforeach
                    </tbody>
                </table>
                <p class="xs-meta mb-0">Only your first attempt counts on the leaderboard. Start again below to practise.</p>
            </div>

            <div class="xs-card" id="xsReview">
                <h5>Question &amp; answer summary <small class="text-muted" style="font-weight:600">(attempt #{{ $S['latest'] }})</small></h5>
                <div class="xs-f">
                    <button type="button" class="on" data-f="all">All {{ $S['total'] }}</button>
                    <button type="button" data-f="wrong">Wrong {{ collect($S['review'])->where('status', 'wrong')->count() }}</button>
                    <button type="button" data-f="correct">Correct {{ collect($S['review'])->where('status', 'correct')->count() }}</button>
                    <button type="button" data-f="skipped">Skipped {{ collect($S['review'])->where('status', 'skipped')->count() }}</button>
                </div>
                @foreach ($S['review'] as $q)
                    <div class="xs-q {{ $q['status'] }}" data-s="{{ $q['status'] }}">
                        <div><b>{{ $q['n'] }}.</b> {!! $q['q'] !!}</div>
                        <div class="xs-ans">
                            @if ($q['status'] === 'wrong')
                                <div class="xs-bad">Your answer: {!! $q['mineHtml'] ?? '-' !!}</div>
                                <div class="xs-ok">Correct answer: {!! $q['ansHtml'] ?? '-' !!}</div>
                            @elseif ($q['status'] === 'correct')
                                <div class="xs-ok">Your answer is correct: {!! $q['ansHtml'] ?? '-' !!}</div>
                            @else
                                <div class="xs-skip">Not answered</div>
                                <div class="xs-ok">Correct answer: {!! $q['ansHtml'] ?? '-' !!}</div>
                            @endif
                        </div>
                        @if (trim(strip_tags((string) $q['notes'])) !== '')<div class="xs-note"><b>Summary:</b> {!! $q['notes'] !!}</div>@endif
                        <div class="xs-meta">Your time {{ $q['sec'] }}s @if ($q['avgSec'] !== null)· average time {{ $q['avgSec'] }}s @endif @if ($q['pct'] !== null)· {{ $q['pct'] }}% of students got it right @endif</div>
                    </div>
                @endforeach
            </div>
            <script>
                document.querySelectorAll('#xsReview .xs-f button').forEach(function (b) {
                    b.addEventListener('click', function () {
                        document.querySelectorAll('#xsReview .xs-f button').forEach(function (x) { x.classList.toggle('on', x === b); });
                        document.querySelectorAll('#xsReview .xs-q').forEach(function (q) { q.style.display = b.dataset.f === 'all' || q.dataset.s === b.dataset.f ? '' : 'none'; });
                    });
                });
            </script>
        
