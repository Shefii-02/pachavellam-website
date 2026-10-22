{{-- Rank list card for public pages. Needs $rankRows (StudentProgress::publicBoard); optional $rankTitle, $rankSub. Shows 10, "Show top 20" adds the rest. --}}
@if (($rankRows ?? collect())->count())
@php $rkId = 'rk' . substr(md5(($rankTitle ?? '') . microtime()), 0, 6); $meId = auth()->id(); @endphp
<section class="rk-card" id="{{ $rkId }}">
    <div class="rk-h"><div><b><i class="bi bi-trophy-fill" style="color:#d97706"></i> {{ $rankTitle ?? 'Top learners' }}</b><div class="rk-s">{{ $rankSub ?? '5 coins for every test + 1 for every right answer' }}</div></div>
        <a href="{{ auth()->check() ? url('my-account') . '#pf-referral' : url('sign-in') }}" class="rk-me">{{ auth()->check() ? 'My rank' : 'Sign in to see my rank' }} <i class="bi bi-chevron-right"></i></a></div>
    <ol class="rk-l">
        @foreach ($rankRows as $r)
            <li class="{{ $meId && $r->uid === $meId ? 'me' : '' }} {{ $loop->iteration > 10 ? 'more' : '' }}" @if ($loop->iteration > 10) hidden @endif>
                <span class="p p{{ $r->pos <= 3 ? $r->pos : '' }}">{{ $r->pos <= 3 ? ['🥇', '🥈', '🥉'][$r->pos - 1] : $r->pos }}</span>
                <span class="n">{{ $r->name }}@if ($meId && $r->uid === $meId) <em>(you)</em>@endif<small>{{ $r->attempts }} tests</small></span>
                <span class="c">{{ number_format($r->coins) }} <i class="bi bi-coin"></i></span>
            </li>
        @endforeach
    </ol>
    @if ($rankRows->count() > 10)<button type="button" class="rk-t" data-more>Show top {{ $rankRows->count() }}</button>@endif
</section>
<style>
    .rk-card{background:var(--pv-surface,#fff);border:1px solid var(--pv-border,#e5e7eb);border-radius:18px;padding:14px;margin:6px 0 18px}
    .rk-h{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:8px}.rk-h b{font-size:1.05rem;color:var(--pv-text,#1b2a41)}.rk-s{font-size:.76rem;color:var(--pv-muted,#6b7280)}
    .rk-me{font-size:.8rem;font-weight:700;color:var(--pv-brand,#0a8f5b);text-decoration:none;white-space:nowrap}
    .rk-l{list-style:none;margin:0;padding:0;display:grid;gap:2px}
    @media(min-width:768px){.rk-l{grid-template-columns:1fr 1fr;column-gap:22px}}
    .rk-l li{display:flex;align-items:center;gap:10px;padding:8px 6px;border-bottom:1px solid var(--pv-border,#eef0f3);border-radius:10px}
    .rk-l li[hidden]{display:none}.rk-l li.me{background:color-mix(in srgb,#0a8f5b 14%,transparent);font-weight:700}
    .rk-l .p{width:30px;text-align:center;font-weight:800;color:var(--pv-muted,#6b7280)}.rk-l .p1,.rk-l .p2,.rk-l .p3{font-size:1.2rem}
    .rk-l .n{flex:1;min-width:0;color:var(--pv-text,#1b2a41);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.rk-l .n em{font-style:normal;color:#0a8f5b;font-size:.78rem}
    .rk-l .n small{display:block;color:var(--pv-muted,#6b7280);font-size:.72rem;font-weight:400}
    .rk-l .c{font-weight:800;color:var(--pv-text,#1b2a41);white-space:nowrap}.rk-l .c i{color:#d97706}
    .rk-t{display:block;margin:10px auto 0;border:1px solid var(--pv-border,#e5e7eb);background:transparent;color:var(--pv-text,#1b2a41);border-radius:99px;padding:6px 18px;font-weight:700;font-size:.82rem}
</style>
<script>
    (function () { var root = document.getElementById(@json($rkId)), b = root.querySelector('[data-more]'); if (!b) return;
        b.onclick = function () { var open = b.dataset.open !== '1'; b.dataset.open = open ? '1' : ''; [].forEach.call(root.querySelectorAll('li.more'), function (li) { li.hidden = !open; }); b.textContent = open ? 'Show less' : 'Show top {{ $rankRows->count() }}'; }; })();
</script>
@endif
