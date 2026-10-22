{{-- One exam series card (home page): needs $f (name, icon, total, live, seasons, items, description, all, badge) and $accent. --}}
@once
<style>
    .pv-grid { display:grid; gap:12px; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); }
    .pv-card { background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:18px; padding:14px; display:flex; flex-direction:column; gap:8px; }
    .pv-card header { display:flex; align-items:center; gap:10px; }
    .pv-ic { width:40px; height:40px; border-radius:12px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex:none; }
    .pv-t { min-width:0; flex:1; } .pv-t b { display:block; color:var(--pv-text,#1b2a41); line-height:1.2; } .pv-t small { color:var(--pv-muted,#6b7280); font-size:.74rem; }
    .pv-live { display:inline-flex; align-items:center; gap:5px; background:#fde8e8; color:#c0262d; border-radius:20px; padding:2px 10px; font-size:.7rem; font-weight:800; } .pv-live i { width:7px; height:7px; border-radius:50%; background:#e5484d; animation:pvp 1.2s infinite; } @keyframes pvp { 50% { opacity:.25; } }
    .pv-season { border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; padding:2px 6px 6px; } .pv-season + .pv-season { margin-top:6px; }
    .pv-season summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:6px 4px; } .pv-season summary::-webkit-details-marker { display:none; }
    .pv-sn { flex:1; min-width:0; } .pv-sn b { display:block; font-size:.86rem; color:var(--pv-text,#1b2a41); } .pv-sn small { color:var(--pv-muted,#6b7280); font-size:.72rem; }
    .pv-season summary em.act { font-style:normal; font-size:.64rem; font-weight:800; background:#e7f6ef; color:#0a6b46; border-radius:10px; padding:2px 9px; } .pv-season summary > i { transition:transform .2s; color:var(--pv-muted,#6b7280); } .pv-season[open] summary > i { transform:rotate(180deg); }
    .pv-card ul { list-style:none; margin:0; padding:0; display:grid; gap:2px; } .pv-none { color:var(--pv-muted,#6b7280); font-size:.8rem; padding:6px 8px; }
    .pv-card li a { display:flex; align-items:center; gap:8px; padding:7px 8px; border-radius:10px; text-decoration:none; color:var(--pv-text,#1b2a41); background:var(--pv-surface-2,#f4f6f8); }
    .pv-i { min-width:0; flex:1; } .pv-i b { display:block; font-size:.84rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; } .pv-i small { color:var(--pv-muted,#6b7280); font-size:.72rem; }
    .pv-card li em { font-style:normal; font-size:.66rem; font-weight:800; border-radius:10px; padding:2px 9px; background:#e7f6ef; color:#0a6b46; flex:none; } .pv-card li em.hot { background:#e5484d; color:#fff; }
    .pv-card p { margin:0; color:var(--pv-muted,#6b7280); font-size:.86rem; }
    .pv-card footer { margin-top:auto; font-size:.82rem; font-weight:700; } .pv-card footer a { color:var(--pv-brand,#0a8f5b); text-decoration:none; } .pv-card footer span { color:var(--pv-muted,#6b7280); font-weight:600; }
</style>
@endonce
<section class="pv-card">
    <header>
        <span class="pv-ic" style="background:{{ $accent }}"><i class="bi {{ $f->icon }}"></i></span>
        <div class="pv-t"><b>{{ $f->name }}</b><small>@if (!empty($f->badge)){{ $f->badge }}@endif @if ($f->total) · {{ number_format($f->total) }} available @endif</small></div>
        @if ($f->live)<span class="pv-live"><i></i>On now</span>@endif
    </header>
    @if (count($f->seasons))
        @foreach ($f->seasons as $se)
            <details class="pv-season" @if ($loop->first) open @endif>
                <summary><span class="pv-sn"><b>{{ $se->title }}</b><small>{{ $se->sub }} · {{ $se->total }} exam{{ $se->total == 1 ? '' : 's' }}</small></span>@if (!empty($se->paid))<em class="act" style="background:#fff3d6;color:#8a5500">₹{{ $se->price }}</em>@endif<em class="act">Active</em><i class="bi bi-chevron-down"></i></summary>
                <ul>
                    @forelse ($se->items as $it)
                        <li><a href="{{ $it->url }}"><span class="pv-i"><b>@if (!empty($it->lock))<i class="bi bi-lock-fill"></i> @endif{{ \Illuminate\Support\Str::limit($it->title, 48) }}</b>@if ($it->sub)<small>{{ $it->sub }}</small>@endif</span><em class="{{ in_array($it->tag, ['Live now', 'Upcoming']) ? 'hot' : '' }}">{{ $it->tag }}</em></a></li>
                    @empty
                        <li class="pv-none">No exams in this season yet.</li>
                    @endforelse
                </ul>
            </details>
        @endforeach
    @elseif (count($f->items))
        <ul>
            @foreach ($f->items as $it)
                <li><a href="{{ $it->url }}"><span class="pv-i"><b>{{ \Illuminate\Support\Str::limit($it->title, 48) }}</b>@if ($it->sub)<small>{{ $it->sub }}</small>@endif</span><em class="{{ in_array($it->tag, ['Live now', 'Upcoming']) ? 'hot' : '' }}">{{ $it->tag }}</em></a></li>
            @endforeach
        </ul>
    @else
        <p>{{ $f->description ?: 'Included for this exam type.' }}</p>
    @endif
    <footer>@if ($f->all)<a href="{{ $f->all }}">See all <i class="bi bi-arrow-right"></i></a>@endif</footer>
</section>
