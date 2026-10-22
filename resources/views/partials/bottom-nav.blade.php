{{-- Bottom navigation of the website: Home, Exams, Courses, Learn and Profile (Login when signed out). Floats above the bottom edge. --}}
@php
    $bn = fn (...$paths) => request()->is(...$paths) ? 'on' : '';
    $signedIn = auth()->check();
@endphp
<style>
    body { padding-bottom: calc(90px + env(safe-area-inset-bottom)); }
    #bottomNav { position:fixed; left:12px; right:12px; bottom:calc(15px + env(safe-area-inset-bottom)); z-index:1050; background:var(--pv-surface, #fff); border:1px solid var(--pv-border, #e1e9d6); border-radius:18px; box-shadow:0 6px 24px rgba(31,51,5,.16); display:flex; justify-content:space-around; padding:6px 4px; }
    #bottomNav a { flex:1; text-align:center; text-decoration:none !important; color:var(--pv-muted, #66755d); font-size:.68rem; font-weight:600; line-height:1.1; } #bottomNav a i { display:block; font-size:1.3rem; margin:0 auto 2px; width:46px; padding:2px 0; border-radius:12px; transition:background .15s; }
    #bottomNav a.on { color:var(--pv-brand, #4f7d1b); }
    #bottomNav a.on i { background:var(--pv-green, #a7d04f); color:var(--pv-on-brand, #1f3305); }
    @media (min-width:992px) { #bottomNav { max-width:560px; left:50%; right:auto; width:560px; transform:translateX(-50%); } }
</style>
<nav id="bottomNav">
    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'on' : '' }}"><i class="bi bi-house-door-fill"></i>Home</a>
    <a href="{{ url('exams') }}" class="{{ $bn('exams', 'exams/*', 'exam/*') }}"><i class="bi bi-bullseye"></i>Exams</a>
    <a href="{{ url('courses') }}" class="{{ $bn('courses', 'courses/*', 'prime*') }}"><i class="bi bi-mortarboard-fill"></i>Courses</a>
    <a href="{{ url('learn') }}" class="{{ $bn('learn', 'free-classes*', 'articles*', 'kpsc/daily-exam*') }}"><i class="bi bi-play-btn-fill"></i>Learn</a>
    @if ($signedIn)
        <a href="{{ url('my-account') }}" class="{{ $bn('my-account*') }}"><i class="bi bi-person-circle"></i>Profile</a>
    @else
        <a href="{{ url('sign-in') }}" class="{{ $bn('sign-in') }}"><i class="bi bi-box-arrow-in-right"></i>Login</a>
    @endif
</nav>
