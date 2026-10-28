@extends('layouts.web-layout')
@section('content')
    <!-- **************** MAIN CONTENT START **************** -->


    <!-- Sidebar START -->
    <nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

        <!-- Navbar brand for xl START -->
        <div class="d-flex align-items-center">
            <a class="navbar-brand" href="{{ url('admin/dashboard') }}">
                <img class="navbar-brand-item" src="{{ url('images/logo-2.png') }}" alt="">
            </a>
        </div>
        <!-- Navbar brand for xl END -->

        <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" style="    overflow-y: auto;"
            data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
            <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

                <!-- Sidebar menu START -->
                <ul class="navbar-nav flex-column" id="navbar-sidebar">
                    @php
                        $staffUser = auth()->user();
                        $canContent = \App\Support\StaffAccess::can($staffUser, 'Content manage');
                        $heading = fn ($t) => '<li class="nav-item mt-3 mb-1 px-3 small text-uppercase text-white-50" style="letter-spacing:.06em;font-size:.7rem">' . $t . '</li>';
                        $link = fn ($href, $icon, $label, $active) => '<li class="nav-item"><a class="nav-link ' . ($active ? 'active' : '') . '" href="' . e($href) . '"><i class="bi ' . $icon . ' fa-fw me-2"></i>' . $label . '</a></li>';
                        $kind = \App\Models\ContentItem::KINDS;
                    @endphp

                    {{-- Overview --}}
                    {!! $heading('Overview') !!}
                    {!! $link(url('admin/dashboard'), 'bi-house-door-fill', 'Dashboard', active_module('dashboard') ? true : false) !!}

                    {{-- Free for everyone: promo classes (YouTube live / recorded) and articles, each for chosen exam types --}}
                    @if ($canContent)
                        {!! $heading('Free content') !!}
                        @foreach (['free-classes', 'articles'] as $seg)
                            {!! $link(url('admin/content/' . $seg), $kind[$seg]['icon'], $kind[$seg]['label'], request()->is('admin/content/' . $seg . '*')) !!}
                        @endforeach
                    @endif

                    {{-- one tab per exam type (General > Exam Types); a new exam type shows up here by itself --}}
                    @can('kpsc Tab')
                        {!! $heading('Exam types') !!}
                        @foreach ($examTabs as $tab)
                            {!! $link($tab->homeUrl(), 'bi-' . ($loop->iteration <= 9 ? $loop->iteration . '-circle-fill' : 'record-circle-fill'), e($tab->short_name ?: $tab->name), request()->is(ltrim(parse_url($tab->homeUrl(), PHP_URL_PATH), '/') . '*')) !!}
                        @endforeach
                    @endcan

                    {{-- Sales (all exam types; filter by exam type inside) --}}
                    @php $canSales = \App\Support\StaffAccess::can($staffUser, 'Admissions view') || \App\Support\StaffAccess::can($staffUser, 'Revenue view'); $isAdminUser = $staffUser && $staffUser->type === 'Admin'; @endphp
                    @if ($canSales || $isAdminUser)
                        {!! $heading('Sales') !!}
                        @if ($canSales){!! $link(url('admin/revenue'), 'bi-cash-coin', 'Admissions &amp; Revenue', request()->is('admin/revenue*', 'admin/admissions*', 'admin/transactions*', 'admin/payment-links*')) !!}@endif
                        @if ($isAdminUser){!! $link(url('admin/coupons'), 'bi-ticket-perforated', 'Coupons', request()->is('admin/coupons*')) !!}@endif
                    @endif

                    {{-- what students see on the home page --}}
                    @php $showHome = $canContent || auth()->user()->can('Capsule list') || auth()->user()->can('Banner Slider list'); @endphp
                    @if ($showHome)
                        {!! $heading('Home page content') !!}
                        @can('Capsule list'){!! $link(url('admin/capsule'), 'bi-file-image-fill', 'Capsule', request()->is('admin/capsule*')) !!}@endcan
                        @can('Banner Slider list'){!! $link(url('admin/stories'), 'bi-card-image', 'Stories', request()->is('admin/stories*')) !!}@endcan
                        @if ($canContent)
                            @foreach (['banners', 'whats-new', 'announcements', 'notifications'] as $seg)
                                {!! $link(url('admin/content/' . $seg), $kind[$seg]['icon'], $kind[$seg]['label'], request()->is('admin/content/' . $seg . '*')) !!}
                            @endforeach
                            {!! $link(url('admin/push'), 'bi-send', 'Push notifications', request()->is('admin/push*')) !!}
                            {!! $link(url('admin/pages'), 'bi-file-earmark-richtext', 'Pages (custom &amp; prebuilt)', request()->is('admin/pages*')) !!}
                            {!! $link(url('admin/menu'), 'bi-list-nested', 'Menu setup', request()->is('admin/menu*')) !!}
                        @endif
                    @endif

                    {{-- the website itself --}}
                    @if ($canContent)
                        {!! $heading('Website content') !!}
                        @foreach (['site-stats', 'key-points', 'founder', 'team', 'partners', 'faqs', 'footer-links'] as $seg)
                            {!! $link(url('admin/content/' . $seg), $kind[$seg]['icon'], $kind[$seg]['label'], request()->is('admin/content/' . $seg . '*')) !!}
                        @endforeach
                        {!! $link(url('admin/home-exams'), 'bi-house-heart', 'Exams on home page', request()->is('admin/home-exams*')) !!}
                        {!! $link(url('admin/media'), 'bi-images', 'Media library', request()->is('admin/media*')) !!}
                        {!! $link(url('admin/devices'), 'bi-phone', 'Devices', request()->is('admin/devices*')) !!}
                        {!! $link(url('admin/security'), 'bi-shield-lock', 'Security (CAPTCHA &amp; limits)', request()->is('admin/security*')) !!}
                        {!! $link(url('admin/ads'), 'bi-badge-ad', 'Ads (Google)', request()->is('admin/ads*')) !!}
                        {!! $link(url('admin/branding'), 'bi-brush', 'Logos (light / dark)', request()->is('admin/branding*')) !!}
                        {!! $link(url('admin/seo'), 'bi-search', 'SEO (title, tags, words)', request()->is('admin/seo*')) !!}
                        @php $newCounselling = \Illuminate\Support\Facades\Schema::hasTable('counselling_requests') ? \App\Models\CounsellingRequest::where('status', 'new')->count() : 0; @endphp
                        {!! $link(url('admin/counselling'), 'bi-headset', 'Counselling requests' . ($newCounselling ? ' <span class="badge bg-danger ms-1">' . $newCounselling . '</span>' : ''), request()->is('admin/counselling*')) !!}
                    @endif

                    {{-- Team --}}
                    @php $canChat = \App\Support\StaffAccess::can($staffUser, 'Staff chat use'); $canJoins = \App\Support\StaffAccess::can($staffUser, 'New joins view'); $canLog = \App\Support\StaffAccess::can($staffUser, 'Activity log view'); $canAccess = \App\Support\StaffAccess::can($staffUser, 'Staff access manage'); $canTrash = \App\Support\StaffAccess::can($staffUser, 'Trash manage'); @endphp
                    @if ($canChat || $canJoins)
                        {!! $heading('Communication') !!}
                        @if ($canChat){!! $link(url('admin/chat'), 'bi-chat-dots', 'Chats', request()->is('admin/chat*')) !!}@endif
                        @if ($canChat){!! $link(url('admin/class-chats'), 'bi-chat-square-text', 'Video chats', request()->is('admin/class-chats*')) !!}@endif
                        @if ($canJoins){!! $link(url('admin/new-joins'), 'bi-person-plus', 'New joins', request()->is('admin/new-joins*')) !!}@endif
                    @endif
                    @can('Students Tab')
                        {!! $heading('People') !!}
                        {!! $link(url('admin/students'), 'bi-people-fill', 'Students', request()->is('admin/students*')) !!}
                    @endcan

                    {{-- Administration --}}
                    @php $canGeneral = auth()->user()->can('General Setting Tab'); @endphp
                    @if ($canLog || $canAccess || $canTrash || $canGeneral)
                        {!! $heading('Administration') !!}
                        @if ($canLog){!! $link(url('admin/activity-logs'), 'bi-clock-history', 'Staff activity', request()->is('admin/activity-logs*')) !!}@endif
                        @if ($canAccess){!! $link(url('admin/staff-access'), 'bi-shield-lock', 'Staff access', request()->is('admin/staff-access*')) !!}@endif
                        @if ($canTrash){!! $link(url('admin/trash'), 'bi-trash3', 'Trash', request()->is('admin/trash*')) !!}@endif
                        @if ($canGeneral){!! $link(url('admin/general'), 'bi-gear-fill', 'General settings', request()->is('admin/general*')) !!}@endif
                    @endif
                </ul>
                <!-- Sidebar menu end -->

                <!-- Sidebar footer START -->
                <div class="px-3 mt-auto pt-3">
                    <div class="d-flex align-items-center justify-content-between text-primary-hover">

                        <a class="h5 mb-0 text-body" href="{{ url('admin') }}" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Home">
                            <i class="bi bi-house"></i>
                        </a>

                        @can('General Setting Tab')
                            <a class="h5 mb-0 text-body" href="{{ url('admin/general') }}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Settings">
                                <i class="bi bi-gear-fill"></i>
                            </a>
                        @endcan
                        <a class="h5 mb-0 text-body" href="{{ url('sign-out') }}" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Sign out">
                            <i class="bi bi-power"></i>
                        </a>
                    </div>
                </div>
                <!-- Sidebar footer END -->

            </div>
        </div>
    </nav>
    <!-- Sidebar END -->

    <!-- Page content START -->
    <div class="page-content">

        <!-- Top bar START -->
        <nav class="top-bar navbar-light border-bottom py-0 py-xl-3">
            <div class="container-fluid p-0">
                <div class="d-flex align-items-center w-100">

                    <!-- Logo START -->
                    <div class="d-flex align-items-center d-xl-none">
                        <a class="navbar-brand" href="index-2.html">
                            <img class="light-mode-item navbar-brand-item h-60px" src="{{ url('images/logo.png') }}"
                                alt="">
                            <img class="dark-mode-item navbar-brand-item h-60px" src="{{ url('images/logo-2.png') }}"
                                alt="">
                        </a>
                    </div>
                    <!-- Logo END -->

                    <!-- Toggler for sidebar START -->
                    <div class="navbar-expand-xl sidebar-offcanvas-menu">
                        <button class="navbar-toggler me-auto" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false"
                            aria-label="Toggle navigation" data-bs-auto-close="outside">
                            <i class="bi bi-text-right fa-fw h2 lh-0 mb-0 rtl-flip" data-bs-target="#offcanvasMenu"> </i>
                        </button>
                    </div>
                    <!-- Toggler for sidebar END -->

                    <!-- Top bar left -->
                    <div class="navbar-expand-lg ms-auto ms-xl-0  d-none d-md-block">

                        <!-- Toggler for menubar START -->
                        <button class="navbar-toggler ms-auto " type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarTopContent" aria-controls="navbarTopContent" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-animation">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                        <!-- Toggler for menubar END -->

                        <!-- Topbar menu START -->
                        <div class="collapse navbar-collapse w-100" id="navbarTopContent">
                            <!-- Top search START -->
                            <div class="nav my-3 my-xl-0 flex-nowrap align-items-center">
                                <div class="nav-item w-100">

                                </div>
                            </div>
                            <!-- Top search END -->
                        </div>
                        <!-- Topbar menu END -->
                    </div>
                    <!-- Top bar left END -->

                    <!-- Top bar right START -->
                    <div class="ms-xl-auto">
                        <ul class="navbar-nav flex-row align-items-center">

                            <!-- Notification dropdown START -->
                            @include('admin.partials.staff-bell')
                            <!-- Notification dropdown END -->

                            <!-- Profile dropdown START -->
                            <li class="nav-item ms-2 ms-md-3 dropdown">
                                <!-- Avatar -->
                                <a class="avatar avatar-sm p-0" href="#" id="profileDropdown" role="button"
                                    data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <img class="avatar-img rounded-circle"
                                        src="{{ Auth::user()->photoUrl() ?: asset('assets/images/user.png') }}" alt="avatar">
                                </a>

                                <!-- Profile dropdown START -->
                                <ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3"
                                    aria-labelledby="profileDropdown">
                                    <!-- Profile info -->
                                    <li class="px-3">
                                        <div class="d-flex align-items-center">
                                            <!-- Avatar -->
                                            <div class="avatar me-3">
                                                <img class="avatar-img rounded-circle shadow"
                                                    src="{{ Auth::user()->photoUrl() ?: asset('assets/images/user.png') }}"
                                                    alt="avatar">
                                            </div>
                                            <div>
                                                <a class="h6 mt-2 mt-sm-0" href="#">{{ Auth::user()->name }}</a>
                                                <p class="small m-0">{{ Auth::user()->email }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                    </li>

                                    <!-- Links -->
                                    <li><a class="dropdown-item" href="{{ url('admin/profile') }}"><i
                                                class="bi bi-person fa-fw me-2"></i>Edit Profile</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-info-circle fa-fw me-2"></i>Help</a></li>
                                    <li><a class="dropdown-item bg-danger-soft-hover" href="{{ url('sign-out') }}"><i
                                                class="bi bi-power fa-fw me-2"></i>Sign Out</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <!-- Dark mode switch START -->
                                    <li>
                                        <div class="modeswitch-wrap" id="darkModeSwitch">
                                            <div class="modeswitch-item">
                                                <div class="modeswitch-icon"></div>
                                            </div>
                                            <span>Dark mode</span>
                                        </div>
                                    </li>
                                    <!-- Dark mode switch END -->
                                </ul>
                                <!-- Profile dropdown END -->
                            </li>
                            <!-- Profile dropdown END -->
                        </ul>
                    </div>
                    <!-- Top bar right END -->
                </div>
            </div>
        </nav>
        <!-- Top bar END -->
        @yield('main')
        <!-- Top listed Cards START -->


        {{-- //////////end --}}
    </div>
    <!-- Page main content END -->
    </div>
@endsection
