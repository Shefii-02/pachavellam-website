@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    <style>
        .pm-hero { position:relative; overflow:hidden; border-radius:22px; margin-top:14px; padding:30px 22px; color:#fff; background:radial-gradient(circle at 85% 20%, rgba(167,208,79,.35), transparent 42%), linear-gradient(135deg,#16300a,#2f5410 55%,#4f7d1b); }
        @media (min-width:768px) { .pm-hero { padding:44px 44px; } }
        .pm-logo { display:inline-flex; align-items:center; gap:6px; font-weight:800; font-size:1.1rem; color:#fff; }
        .pm-logo span { background:linear-gradient(90deg,#ffd166,#ffb703); color:#7a3e00; border-radius:4px; padding:1px 8px; transform:skewX(-8deg); }
        .pm-hero h1 { font-weight:800; font-size:1.9rem; line-height:1.15; margin:12px 0 10px; color:#fff; max-width:640px; }
        @media (min-width:768px) { .pm-hero h1 { font-size:2.6rem; } }
        .pm-hero p { color:rgba(255,255,255,.9); max-width:560px; margin-bottom:20px; }
        .pm-cta { display:inline-flex; align-items:center; gap:8px; background:#a7d04f; color:#1f3305 !important; font-weight:800; border-radius:24px; padding:10px 24px; text-decoration:none !important; }
        .pm-sec { margin:34px 0 8px; }
        .pm-sec h2 { font-weight:800; font-size:1.4rem; color:var(--pv-text); margin-bottom:14px; } .pm-sec h2 em { font-style:normal; color:var(--pv-brand); }
        .pm-feats { display:grid; gap:12px; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); }
        .pm-feat { display:flex; gap:12px; background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; padding:16px; }
        .pm-feat i { width:46px; height:46px; border-radius:12px; flex:none; background:var(--pv-brand-soft); color:var(--pv-brand); display:flex; align-items:center; justify-content:center; font-size:1.25rem; }
        .pm-feat b { display:block; color:var(--pv-text); } .pm-feat small { color:var(--pv-muted); }
        .pm-steps { display:grid; gap:12px; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); counter-reset:st; }
        .pm-step { background:var(--pv-surface-2); border-radius:16px; padding:16px; color:var(--pv-text); position:relative; padding-left:62px; }
        .pm-step::before { counter-increment:st; content:counter(st); position:absolute; left:16px; top:16px; width:34px; height:34px; border-radius:50%; background:var(--pv-text); color:var(--pv-surface); font-weight:800; display:flex; align-items:center; justify-content:center; }
        .pm-step b { display:block; } .pm-step small { color:var(--pv-muted); }
        .pm-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin-bottom:10px; } .pm-chips::-webkit-scrollbar { display:none; }
        .pm-chip { flex:none; border:1px solid var(--pv-border); background:var(--pv-chip); border-radius:20px; padding:6px 16px; font-size:.85rem; font-weight:600; color:var(--pv-text) !important; text-decoration:none !important; }
        .pm-chip.on { background:var(--pv-text); color:var(--pv-surface) !important; border-color:var(--pv-text); }
        .pm-chip.sm { padding:4px 12px; font-size:.78rem; }
        .pm-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); }
        .pm-card { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; overflow:hidden; display:flex; flex-direction:column; color:var(--pv-text); }
        .pm-card .cv { aspect-ratio:16/9; background:linear-gradient(135deg,#1c3a0c,#4f7d1b) center/cover; position:relative; }
        .pm-card .ex { position:absolute; left:10px; top:10px; color:#fff; font-size:.7rem; font-weight:700; padding:2px 10px; border-radius:10px; }
        .pm-card .ft { position:absolute; right:10px; top:10px; background:#a7d04f; color:#1f3305; font-size:.68rem; font-weight:800; padding:2px 8px; border-radius:10px; }
        .pm-card .bd { padding:14px; display:flex; flex-direction:column; gap:6px; flex:1; }
        .pm-card .tt { font-weight:700; font-size:1rem; line-height:1.3; }
        .pm-card .ds { font-size:.82rem; color:var(--pv-muted); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .pm-card .mt { font-size:.76rem; color:var(--pv-muted); }
        .pm-card .pr { font-weight:800; font-size:1.2rem; color:#0a8f5b; } .pm-card .pr s { color:var(--pv-muted); font-weight:400; font-size:.85rem; margin-left:6px; } .pm-card .pr small { color:var(--pv-danger); font-size:.75rem; margin-left:6px; }
        .pm-card .act { display:flex; gap:8px; margin-top:auto; padding-top:8px; }
        .pm-buy { flex:1; text-align:center; border-radius:22px; padding:9px 12px; font-weight:800; font-size:.88rem; color:var(--pv-on-brand) !important; background:linear-gradient(90deg,#c3e27a,#a7d04f 45%,#7fae2e); text-decoration:none !important; }
        .pm-more { border:1px solid var(--pv-border); border-radius:22px; padding:9px 14px; font-weight:700; font-size:.85rem; color:var(--pv-text) !important; text-decoration:none !important; }
        .pm-note { border-radius:12px; padding:10px 14px; font-size:.86rem; font-weight:600; margin:12px 0; }
        .pm-note.err { background:var(--pv-danger-soft); color:var(--pv-danger); } .pm-note.ok { background:color-mix(in srgb, #0a8f5b 14%, var(--pv-surface)); color:#0a8f5b; } .pm-note.info { background:var(--pv-surface-2); color:var(--pv-text); }
        .pm-empty { border:1px dashed var(--pv-border); border-radius:16px; padding:30px; text-align:center; color:var(--pv-muted); }
        .fq-i { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; margin-bottom:10px; overflow:hidden; }
        .fq-i.open { border-color:var(--pv-brand); }
        .fq-q { width:100%; text-align:left; border:0; background:none; padding:14px 18px; font-weight:700; display:flex; gap:12px; align-items:center; color:var(--pv-text); }
        .fq-q i { margin-left:auto; color:var(--pv-brand); transition:transform .2s; } .fq-i.open .fq-q i { transform:rotate(180deg); }
        .fq-a { display:none; padding:0 18px 16px; color:var(--pv-muted); white-space:pre-wrap; font-size:.9rem; } .fq-i.open .fq-a { display:block; }
    </style>
@endsection
@section('content')
@php
    $signedIn = auth()->check();
    $q = fn (array $p) => url('prime') . (($p = array_filter($p)) ? '?' . http_build_query($p) : '') . '#courses';
    $faqList = $faqs->count() ? $faqs->map(fn ($f) => [$f->title, $f->description]) : collect([
        ['Do I need an account to buy?', 'Yes. Press Buy now and sign in (or create a free account). You come straight back to finish the payment, and the course is added to your account.'],
        ['How do I pay?', 'UPI, debit / credit cards, net banking and wallets through our secure payment partner. You get a receipt as soon as the payment goes through.'],
        ['Where do I study after buying?', 'Open the Pachavellam app (or website) with the same account - your course, classes, tests and materials are unlocked there.'],
        ['How long can I use the course?', 'Each course shows its validity on the card. You can use everything in it until then.'],
        ['Can I get a refund?', 'Refunds follow our terms and conditions. Please contact support with your payment details if something went wrong.'],
    ]);
@endphp
<div class="page-content-wrapper">
    <div class="container pb-4">
        <div class="pm-hero">
            <div class="pm-logo">Pachavellam <span>Prime</span></div>
            <h1>One subscription. Every class, test and note you need.</h1>
            <p>Mock tests, live and recorded classes, study materials, a personal study plan and the Roaring study method - in one course for your exam.</p>
            <a href="#courses" class="pm-cta">Explore Prime courses <i class="bi bi-arrow-down"></i></a>
        </div>

        @if (session('prime_error'))<div class="pm-note err"><i class="bi bi-exclamation-circle me-1"></i>{{ session('prime_error') }}</div>@endif
        @if (session('message'))<div class="pm-note ok"><i class="bi bi-check-circle me-1"></i>{{ session('message') }}</div>@endif

        <section class="pm-sec">
            <h2>What you get with <em>Prime</em></h2>
            <div class="pm-feats">
                @foreach ($features as [$icon, $name, $text])
                    <div class="pm-feat"><i class="bi {{ $icon }}"></i><div><b>{{ $name }}</b><small>{{ $text }}</small></div></div>
                @endforeach
            </div>
        </section>

        <section class="pm-sec">
            <h2>How to <em>join</em></h2>
            <div class="pm-steps">
                <div class="pm-step"><b>Pick your course</b><small>Choose your exam and the course that fits you.</small></div>
                <div class="pm-step"><b>Sign in</b><small>Log in or create a free account - it is needed to buy.</small></div>
                <div class="pm-step"><b>Pay & start</b><small>Pay securely and start learning on the app or website.</small></div>
            </div>
        </section>

        <section class="pm-sec" id="courses">
            <h2>Prime <em>courses</em>{{ $exam ? ' for ' . $exam->name : '' }}{{ $category ? ' - ' . $category->name : '' }}</h2>
            <div class="pm-chips">
                <a class="pm-chip {{ !$exam ? 'on' : '' }}" href="{{ $q([]) }}">All exams</a>
                @foreach ($exams as $e)<a class="pm-chip {{ $exam && $exam->id === $e->id ? 'on' : '' }}" href="{{ $q(['exam' => $e->slug]) }}">{{ $e->short_name ?: $e->name }}</a>@endforeach
            </div>
            @if ($categories->count())
                <div class="pm-chips">
                    <a class="pm-chip sm {{ !$category ? 'on' : '' }}" href="{{ $q(['exam' => $exam->slug]) }}">All categories</a>
                    @foreach ($categories as $c)<a class="pm-chip sm {{ $category && $category->id === $c->id ? 'on' : '' }}" href="{{ $q(['exam' => $exam->slug, 'category' => $c->id]) }}">{{ $c->name }}</a>@endforeach
                </div>
            @endif
            @unless ($signedIn)
                <div class="pm-note info"><i class="bi bi-lock me-1"></i>You need to <a href="{{ url('sign-in') }}" style="color:var(--pv-brand)" class="fw-bold">sign in</a> to buy a course. Pressing Buy now takes you to sign in and back here.</div>
            @endunless

            @if ($courses->count())
                <div class="pm-grid">
                    @foreach ($courses as $c)
                        @php $price = $c->effectivePrice(); @endphp
                        <div class="pm-card">
                            <div class="cv" @if ($c->cover_image) style="background-image:url('{{ Storage::url($c->cover_image) }}')" @endif>
                                @if ($c->exam)<span class="ex" style="background:{{ $c->exam->color ?: '#2563eb' }}">{{ $c->exam->short_name ?: $c->exam->name }}</span>@endif
                                @if ($c->is_featured)<span class="ft">POPULAR</span>@endif
                            </div>
                            <div class="bd">
                                <div class="tt">{{ $c->title }}</div>
                                @if ($c->short_description)<div class="ds">{{ $c->short_description }}</div>@endif
                                <div class="mt"><i class="bi bi-clock-history"></i> {{ $c->validityLabel() }}@if ($c->duration_hours) · {{ $c->duration_hours }} hours @endif</div>
                                <div class="pr">
                                    @if ($c->isFree()) FREE
                                    @else ₹{{ number_format($price) }}@if ($price < $c->price)<s>₹{{ number_format($c->price) }}</s><small>{{ round(100 - $price / max($c->price, 1) * 100) }}% off</small>@endif
                                    @endif
                                </div>
                                <div class="act">
                                    <a class="pm-more" href="{{ url('courses/' . ($c->slug ?: $c->id)) }}">Details</a>
                                    <a class="pm-buy" href="{{ url('prime/buy/' . $c->id) }}">@if ($c->isFree())Join free @elseif ($signedIn)Buy now @else<i class="bi bi-lock-fill"></i> Login &amp; buy @endif</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="pm-empty"><i class="bi bi-journal-x d-block fs-2 mb-1"></i>No course here yet. <a href="{{ $q([]) }}" style="color:var(--pv-brand)" class="fw-bold">See all courses</a> or <a href="{{ url('/') }}#counselling" style="color:var(--pv-brand)" class="fw-bold">talk to a counsellor</a>.</div>
            @endif
        </section>

        <section class="pm-sec">
            <h2>Buying a course - <em>questions</em></h2>
            @foreach ($faqList as $n => [$fq, $fa])
                <div class="fq-i {{ $n === 0 ? 'open' : '' }}"><button type="button" class="fq-q" aria-expanded="{{ $n === 0 ? 'true' : 'false' }}">{{ $fq }}<i class="bi bi-chevron-down"></i></button><div class="fq-a">{{ $fa }}</div></div>
            @endforeach
            <p class="small mt-3" style="color:var(--pv-muted)">By buying a course you agree to our <a href="{{ url('info/terms-and-conditions') }}" style="color:var(--pv-brand)">Terms &amp; conditions</a> and <a href="{{ url('info/privacy-policy') }}" style="color:var(--pv-brand)">Privacy policy</a>.</p>
        </section>
    </div>
</div>
@endsection
@section('scripts')
<script>
    [].forEach.call(document.querySelectorAll('.fq-q'), function (q) {
        q.addEventListener('click', function () { var open = q.parentNode.classList.toggle('open'); q.setAttribute('aria-expanded', open ? 'true' : 'false'); });
    });
</script>
@endsection
