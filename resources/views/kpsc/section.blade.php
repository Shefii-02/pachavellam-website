@section('header-tab')
    <!-- Header Area-->
    <div class="header-area" id="headerArea">
        <div class="container h-100 d-flex align-items-center justify-content-between">
            <!-- Back Button-->
            <div class="logo-wrapper"><a href="/"><img src="{{ url('images/logo.png') }}" alt=""></a></div>
            <!-- Navbar Toggler-->
            <!-- Page Title-->
            <div class="page-heading">
            </div>
            <!-- Search Form-->
            <div class="search-form">
                <!-- Navbar Toggler-->
                <div class="navbar--toggler" id="newstenNavbarToggler">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidenav Black Overlay-->
    <div class="sidenav-black-overlay"></div>
    <!-- Side Nav Wrapper-->
    <div class="sidenav-wrapper" id="sidenavWrapper">

        <!-- Sidenav Nav-->
        <ul class="sidenav-nav">
            <li><a href="{{ url('/') }}"><i class="lni lni-arrow-left text-dark"></i> Home</a></li>
            <li><a href="{{ url('kpsc/home') }}"><i class="lni lni-home text-dark"></i>Kpsc Home</a></li>
            <!--<li><a href="{{ url('kpsc/daily-bucket') }}"><i class="lni lni-home text-dark"></i>Roaring 40's</a></li>-->

            <li><a href="{{ url('kpsc/daily-exam') }}"><i class="lni lni-question-circle text-dark"></i>Daily Exam</a></li>
            <!--<li><a href="{{ url('kpsc/explore?type=free-class') }}"><i class="lni lni-star-filled text-warning"></i>Free Class<span class="ml-2 badge badge-warning">{{ $category_free->count() }} +</span></a></li>-->
            <!--<li><a href="{{ url('kpsc/explore?type=paid-class') }}"><i class="lni lni-star-filled text-success"></i>Paid Class<span class="ml-2 badge badge-success">{{ $category_paid->count() }} +</span></a></li>  -->
            {{-- <li><a href="{{url('kpsc/psc-new-pattern')}}"><i class="lni lni-files text-dark"></i>New Model Q&A<span class="ml-2 badge badge-danger">New</span></a></li> --}}
            <li><a href="{{ url('kpsc/psc-daily-current-affairs') }}"><i class="lni lni-add-files text-dark"></i>Daily C A
                    <span class="red-circle ml-2 flashing-effect"></span></a></li>

            {{-- <li><a href="{{url('kpsc/psc-our-videos')}}"><i class="lni lni-video text-dark"></i>Our Videos</a></li> --}}
            <li><a href="{{ url('kpsc/psc-syllabus') }}"><i class="lni lni-files text-dark"></i>Syllabus</a></li>
            <!--<li><a href="{{ url('kpsc/psc-material') }}"><i class="lni lni-bolt text-dark"></i>Material</a></li>-->
            <li><a href="{{ url('kpsc/psc-bulletin') }}"><i class="lni lni-book text-dark"></i>Bulletin</a></li>
            <li><a href="{{ url('kpsc/prev-qstn-ans') }}"><i class="lni lni-folder text-dark"></i>Prev Qstn & Ans</a></li>
            <li><a href="{{ url('kpsc/psc-syllabus/exam-calendar') }}"><i class="lni lni-target text-dark"></i>Exam
                    Calendar</a></li>
            <li><a href="{{ url('kpsc/psc-results') }}"><i class="lni lni-book text-dark"></i>Results</a></li>
            <!--<li><a href="{{ url('kpsc/notify') }}"><i class="lni lni-alarm text-dark"></i>Notify</a></li>-->
            <li><a href="{{ url('kpsc/news-all') }}"><i class="lni lni-paperclip text-dark"></i>News</a></li>

            {{-- <li><a href="{{url('kpsc/live-chat')}}"><i class="lni lni-wechat"></i>Live Chat <span class="green-circle ml-2 flashing-effect"></span></a></li>
      <li><a href="{{url('kpsc/about-us')}}"><i class="lni lni-android-original"></i>About us</a></li>
      <li><a href="{{url('info/terms-and-conditions')}}"><i class="lni lni-files"></i>Terms & Conditions</a></li>
      <li><a href="{{url('info/privacy-policy')}}"><i class="lni lni-files"></i>Privacy Policy</a></li> --}}


        </ul>
        <!-- Go Back Button-->
        <div class="go-home-btn" id="goHomeBtn"><i class="lni lni-arrow-left"></i></div>
    </div>
@endsection
