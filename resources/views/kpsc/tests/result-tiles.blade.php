{{-- Score / time / rank / percentile / accuracy tiles of the result page (overall and per section). --}}
<div class="r-tile" style="background:color-mix(in srgb, #1aa260 10%, var(--pv-surface))">
    <span class="ring" style="color:#1aa260;box-shadow:inset 0 0 0 4px #1aa260"><i class="bi bi-check-lg"></i></span>
    <div><b>{{ $f($score) }} <span>| {{ $f($max) }}</span></b><small>Your Score</small></div>
    @if ($neg > 0)<span class="r-neg">Neg. Marks : - {{ $f($neg) }}</span>@endif
</div>
<div class="r-tile" style="background:color-mix(in srgb, #7c3aed 8%, var(--pv-surface));margin-top:10px">
    <span class="ring" style="color:#7c3aed;box-shadow:inset 0 0 0 4px #7c3aed"><i class="bi bi-clock"></i></span>
    <div><b>{{ $mmss((int) $time) }} @if ($timeMax)<span>| {{ $mmss((int) $timeMax) }}</span>@endif</b><small>Time Spent</small></div>
</div>
<div class="r-three">
    <div class="r-tile" style="background:color-mix(in srgb, #f5b301 10%, var(--pv-surface))"><span class="ring" style="color:#f5b301;box-shadow:inset 0 0 0 4px #f5b301"><i class="bi bi-star-fill"></i></span><div><b>{{ $rank }} <span>| {{ $tot }}</span></b><small>Your Rank</small></div></div>
    <div class="r-tile" style="background:color-mix(in srgb, #e5484d 8%, var(--pv-surface))"><span class="ring" style="color:#e5484d;box-shadow:inset 0 0 0 4px #e5484d"><i class="bi bi-percent"></i></span><div><b>{{ $f($pct) }} <span>| 100</span></b><small>Percentile</small></div></div>
    <div class="r-tile" style="background:color-mix(in srgb, #2f80ed 8%, var(--pv-surface))"><span class="ring" style="color:#2f80ed;box-shadow:inset 0 0 0 4px #2f80ed"><i class="bi bi-bullseye"></i></span><div><b>{{ $f($acc) }} <span>| 100</span></b><small>Accuracy</small></div></div>
</div>
