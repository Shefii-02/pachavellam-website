{{-- The side drawer of the website (profile card, exam type circles, the menu of the exam type you are in, My space, More). Shared by every page with a header. --}}
    <!-- Sidenav Black Overlay-->
    <div class="sidenav-black-overlay"></div>
    <!-- Side Nav Wrapper-->
    <div class="sidenav-wrapper" id="sidenavWrapper">

        @php
            $drUser = auth()->user();
            $drExams = \Illuminate\Support\Facades\Cache::remember('drawer_exam_types', 600, fn () => \App\Models\CompetitiveExam::where('status', 1)->orderBy('position')->get(['id', 'name', 'short_name', 'slug', 'color', 'image']));
            $drIs = fn (...$p) => request()->is(...$p) ? 'on' : '';
            // which exam type is the visitor in? (/kpsc, /rrb, /kpsc/daily-exam ... or the old /exam/rrb)
            $drSeg = request()->segment(1) === 'exam' ? request()->segment(2) : request()->segment(1);
            $drCur = $drSeg ? $drExams->firstWhere('slug', $drSeg) : null;
            $drApp = array_filter(['Android' => config('app_links.android'), 'iPhone' => config('app_links.ios')]);
        @endphp
        <style>
            .sidenav-wrapper { overflow-y:auto; -webkit-overflow-scrolling:touch; padding-bottom:90px; }
            .dr-user { display:flex; align-items:center; gap:12px; padding:18px 16px 14px; margin-bottom:6px; background:var(--pv-brand-soft, #e7f6ef); }
            .dr-av { width:46px; height:46px; border-radius:50%;
                /* background:var(--pv-green, #0a8f5b);  */
                color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.2rem; overflow:hidden; flex:none; } .dr-av img { width:100%; height:100%; object-fit:cover; }
            .dr-user b { display:block; color:var(--pv-text, #1b2a41); line-height:1.2; } .dr-user small { color:var(--pv-muted, #6b7280); }
            .dr-user a.dr-btn { margin-left:auto; background:var(--pv-green, #0a8f5b); color:#fff !important; border-radius:20px; padding:6px 16px; font-weight:700; font-size:.82rem; text-decoration:none; }
            .dr-h { text-transform:uppercase; letter-spacing:.06em; font-size:.68rem; font-weight:800; color:var(--pv-muted, #6b7280); padding:12px 18px 4px; }
            .dr-l { list-style:none; margin:0; padding:0 8px; } .dr-l li a { display:flex; align-items:center; gap:12px; padding:9px 10px; border-radius:12px; color:var(--pv-text, #1b2a41) !important; font-weight:600; font-size:.92rem; text-decoration:none !important; }
            .dr-l li a i { width:22px; text-align:center; font-size:1.05rem; color:var(--pv-brand, #0a8f5b); } .dr-l li a.on { background:var(--pv-brand-soft, #e7f6ef); }
            .dr-l li a .dr-dot { width:9px; height:9px; border-radius:50%; flex:none; } .dr-l li a .bd { margin-left:auto; font-size:.66rem; font-weight:800; background:#e5484d; color:#fff; border-radius:10px; padding:1px 8px; }
            .dr-l li a.out i { color:#e5484d; }
            details.dr-g > summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:12px; padding:9px 18px; font-weight:700; font-size:.92rem; color:var(--pv-text, #1b2a41); } details.dr-g > summary::-webkit-details-marker { display:none; }
            details.dr-g > summary i:first-child { width:22px; text-align:center; color:var(--pv-brand, #0a8f5b); } details.dr-g > summary .ch { margin-left:auto; transition:transform .2s; } details.dr-g[open] > summary .ch { transform:rotate(90deg); }
            details.dr-g .dr-l { padding-left:20px; }
            .dr-circs { display:flex; gap:12px; overflow-x:auto; scrollbar-width:none; padding:2px 16px 8px; } .dr-circs::-webkit-scrollbar { display:none; }
            .dr-circ { flex:none; width:58px; text-align:center; text-decoration:none !important; color:var(--pv-text, #1b2a41) !important; }
            .dr-circ span { width:50px; height:50px; margin:0 auto 4px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.95rem; color:#fff; overflow:hidden; border:3px solid transparent; box-shadow:0 0 0 1px var(--pv-border, #e1e9d6); }
            .dr-circ span img { width:100%; height:100%; object-fit:cover; } .dr-circ small { display:block; font-size:.68rem; font-weight:700; line-height:1.15; max-height:2.3em; overflow:hidden; }
            .dr-circ.on span { border-color:var(--pv-surface, #fff); box-shadow:0 0 0 3px var(--pv-green, #0a8f5b); }
            .dr-circ.home span { background:var(--pv-brand-soft, #e7f6ef); color:var(--pv-brand, #0a8f5b); font-size:1.35rem; }
        </style>

        <div class="dr-user">
            @if ($drUser)
                <div class="dr-av">@if ($drUser->photoUrl())<img src="{{ $drUser->photoUrl() }}" alt="">@else{{ strtoupper(mb_substr($drUser->name ?: 'U', 0, 1)) }}@endif</div>
                <div style="min-width:0"><b class="text-truncate">{{ $drUser->name ?: 'Student' }}</b><small>{{ $drUser->mobile ?: $drUser->email }}</small></div>
                <a class="dr-btn" href="{{ url('my-account') }}">Profile</a>
            @else
                <div class="dr-av"><i class="bi bi-person-fill"></i></div>
                <div><b>Welcome</b><small>Sign in to track your rank</small></div>
                <a class="dr-btn" href="{{ url('sign-in') }}">Sign in</a>
            @endif
        </div>

        {{-- jump between the main home and every exam type --}}
        <div class="dr-h">{{ $drCur ? 'Switch exam type' : 'Exam types' }}</div>
        <div class="dr-circs">
            <a class="dr-circ home {{ !$drCur && request()->is('/') ? 'on' : '' }}" href="{{ url('/') }}"><span><i class="bi bi-house-door-fill"></i></span><small>Main home</small></a>
            @foreach ($drExams as $de)
                <a class="dr-circ {{ $drCur && $drCur->slug === $de->slug ? 'on' : '' }}" href="{{ url($de->slug) }}"><span style="background:{{ $de->color ?: '#6c757d' }}">@if ($de->image)<img src="{{ \Illuminate\Support\Facades\Storage::url($de->image) }}" alt="" onerror="this.remove()">@endif{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($de->short_name ?: $de->name, 0, 2)) }}</span><small>{{ $de->short_name ?: $de->name }}</small></a>
            @endforeach
        </div>

        @if ($drCur)
            {{-- inside an exam type: that exam type's own menu --}}
            <div class="dr-h">{{ $drCur->short_name ?: $drCur->name }} menu</div>
            <ul class="dr-l">
                <li><a class="{{ $drIs($drCur->slug) }}" href="{{ url($drCur->slug) }}"><i class="bi bi-house-door"></i>{{ $drCur->short_name ?: $drCur->name }} home</a></li>
                <li><a class="{{ $drIs($drCur->slug . '/daily-exams*', $drCur->slug . '/daily-exam*') }}" href="{{ url($drCur->slug . '/daily-exams') }}"><i class="bi bi-calendar-check"></i>Daily exams</a></li>
                <li><a class="{{ $drIs($drCur->slug . '/omr-exams*', $drCur->slug . '/model-exam*') }}" href="{{ url($drCur->slug . '/omr-exams') }}"><i class="bi bi-ui-checks-grid"></i>OMR exams</a></li>
                <li><a class="{{ $drIs($drCur->slug . '/gk-exams*') }}" href="{{ url($drCur->slug . '/gk-exams') }}"><i class="bi bi-lightbulb"></i>GK exam series</a></li>
                <li><a class="{{ $drIs($drCur->slug . '/ca-exams*') }}" href="{{ url($drCur->slug . '/ca-exams') }}"><i class="bi bi-newspaper"></i>CA exam series</a></li>
                <li><a class="{{ $drIs($drCur->slug . '/special-exams*') }}" href="{{ url($drCur->slug . '/special-exams') }}"><i class="bi bi-stars"></i>Special exams</a></li>
                @if ($drCur->slug === 'kpsc')
                    <li><a class="{{ $drIs('kpsc/psc-daily-current-affairs*') }}" href="{{ url('kpsc/psc-daily-current-affairs') }}"><i class="bi bi-file-earmark-plus"></i>Daily C A<span class="dr-dot" style="background:#e5484d;margin-left:auto"></span></a></li>
                    <li><a class="{{ $drIs('kpsc/psc-syllabus*') }}" href="{{ url('kpsc/psc-syllabus') }}"><i class="bi bi-files"></i>Syllabus</a></li>
                    <li><a class="{{ $drIs('kpsc/psc-bulletin*') }}" href="{{ url('kpsc/psc-bulletin') }}"><i class="bi bi-book"></i>Bulletin</a></li>
                    <li><a class="{{ $drIs('kpsc/prev-qstn-ans*') }}" href="{{ url('kpsc/prev-qstn-ans') }}"><i class="bi bi-folder"></i>PYQ &amp; Ans</a></li>
                    <li><a href="{{ url('kpsc/psc-syllabus/exam-calendar') }}"><i class="bi bi-calendar-event"></i>Exam Calendar</a></li>
                    <li><a class="{{ $drIs('kpsc/psc-results*') }}" href="{{ url('kpsc/psc-results') }}"><i class="bi bi-clipboard-check"></i>Results</a></li>
                    <li><a class="{{ $drIs('kpsc/news-all*') }}" href="{{ url('kpsc/news-all') }}"><i class="bi bi-paperclip"></i>News</a></li>
                @endif
                <li><a class="{{ $drIs('free-classes*') }}" href="{{ url('free-classes') }}"><i class="bi bi-play-btn"></i>Free classes</a></li>
                <li><a class="{{ $drIs('articles*') }}" href="{{ url('articles') }}"><i class="bi bi-newspaper"></i>Articles</a></li>
                <li><a class="{{ $drIs('announcements*') }}" href="{{ url('announcements') }}"><i class="bi bi-megaphone"></i>Announcements</a></li>
                <li><a class="{{ $drIs('courses*') }}" href="{{ url('courses') }}"><i class="bi bi-collection-play"></i>Courses</a></li>
            </ul>
        @else
        <div class="dr-h">Explore</div>
        <ul class="dr-l">
            <li><a class="{{ $drIs('/') }}" href="{{ url('/') }}"><i class="bi bi-house-door"></i>Home</a></li>
            <li><a class="{{ $drIs('exams*') }}" href="{{ url('exams') }}"><i class="bi bi-bullseye"></i>Exams</a></li>
            <li><a class="{{ $drIs('courses*') }}" href="{{ url('courses') }}"><i class="bi bi-collection-play"></i>Courses</a></li>
            <li><a class="{{ $drIs('learn*') }}" href="{{ url('learn') }}"><i class="bi bi-journal-bookmark"></i>Learn</a></li>
            <li><a class="{{ $drIs('free-classes*') }}" href="{{ url('free-classes') }}"><i class="bi bi-play-btn"></i>Free classes</a></li>
            <li><a class="{{ $drIs('articles*') }}" href="{{ url('articles') }}"><i class="bi bi-newspaper"></i>Articles</a></li>
            <li><a class="{{ $drIs('announcements*') }}" href="{{ url('announcements') }}"><i class="bi bi-megaphone"></i>Announcements</a></li>
            <li><a class="{{ $drIs('stories*') }}" href="{{ url('stories') }}"><i class="bi bi-stars"></i>Stories &amp; achievements</a></li>
        </ul>

        @endif

        @php $drMenu = \Illuminate\Support\Facades\Schema::hasTable('menu_items') ? \Illuminate\Support\Facades\Cache::remember('drawer_menu', 60, fn () => \App\Models\MenuItem::tree()) : collect(); @endphp
        @if ($drMenu->count())
            <div class="dr-h">Menu</div>
            <ul class="dr-l">
                @foreach ($drMenu as $mi)
                    @if ($mi->children->count())
                        <li><details class="dr-g"><summary><i class="bi {{ $mi->icon ?: 'bi-list' }}"></i>{{ $mi->label }}<i class="bi bi-chevron-down ch"></i></summary>
                            <ul class="dr-l">@foreach ($mi->children as $ch)@if ($ch->url())<li><a href="{{ $ch->url() }}" @if ($ch->new_tab) target="_blank" rel="noopener" @endif><i class="bi {{ $ch->icon ?: 'bi-dot' }}"></i>{{ $ch->label }}</a></li>@endif @endforeach</ul></details></li>
                    @elseif ($mi->url())
                        <li><a href="{{ $mi->url() }}" @if ($mi->new_tab) target="_blank" rel="noopener" @endif><i class="bi {{ $mi->icon ?: 'bi-list' }}"></i>{{ $mi->label }}</a></li>
                    @endif
                @endforeach
            </ul>
        @endif

        <div class="dr-h">My space</div>
        <ul class="dr-l">
            <li><a class="{{ $drIs('my-account') }}" href="{{ $drUser ? url('my-account') : url('sign-in') }}"><i class="bi bi-person-circle"></i>My account</a></li>
            <li><a href="{{ $drUser ? url('my-account') . '#pf-referral' : url('sign-in') }}"><i class="bi bi-gift"></i>Refer &amp; earn<span class="bd" style="background:#d97706">Coins</span></a></li>
            <li><a href="{{ $drUser ? url('my-account') . '#pf-ranking' : url('/') . '#ranking' }}"><i class="bi bi-trophy"></i>Overall ranking</a></li>
            <li><a href="{{ url('learn') }}"><i class="bi bi-calendar-check"></i>My classes &amp; tests</a></li>
            @if ($drUser)<li><a href="{{ url('devices') }}"><i class="bi bi-phone"></i>My devices</a></li>@endif
        </ul>

        <div class="dr-h">More</div>
        <ul class="dr-l">
            @foreach ($drApp as $dn => $dl)
                <li><a href="{{ $dl }}" target="_blank" rel="noopener"><i class="bi {{ $dn === 'Android' ? 'bi-android2' : 'bi-apple' }}"></i>Get the {{ $dn }} app</a></li>
            @endforeach
            <li><a href="{{ url('/') }}#counselling"><i class="bi bi-headset"></i>Talk to a counsellor</a></li>
            <li><a href="{{ url('contact-us') }}"><i class="bi bi-life-preserver"></i>Help &amp; support</a></li>
            <li><a href="{{ url('info/terms-and-conditions') }}"><i class="bi bi-file-earmark-text"></i>Terms &amp; privacy</a></li>
            @if ($drUser)<li><a class="out" href="{{ url('sign-out') }}"><i class="bi bi-box-arrow-right"></i>Sign out</a></li>@endif
        </ul>
        <!-- Go Back Button-->
        <div class="go-home-btn" id="goHomeBtn"><i class="lni lni-arrow-left"></i></div>
    </div>
