@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title ?? 'KPSC - Home Page')
@section('styles')
    <style>
        div.parent {
            position: relative;
            width: 100%;
            height: 15vh;
            border: 1px solid black;
            border-radius: 9px;
            overflow: hidden;
            background-color: #000;
            margin-bottom: 10px;
        }

        .child img {
            border-radius: 9px;
        }

        div.child {
            position: absolute;
            bottom: -3px;
            right: -8px;
            width: 50%;
            height: 50%;
            box-shadow: 0 2px 4px 0 rgb(0 0 0 / 20%);
            transform: rotate(20deg);
        }

        p.text-child {
            color: #fff;
            padding: 5px;
            top: 0px;
            font-size: 19px;
            font-weight: 800;
            position: absolute;
            z-index: 9999;
        }

        @media only screen and (min-width: 600px) {
            div.parent {
                height: 20vh !important;
            }
        }

        @media only screen and (max-width: 600px) {
            .catagory-card img {
                min-height: 110px !important;
            }

            .news-bg {
                height: 100px !important;
            }

            .news-text {
                font-size: 10px;
            }
        }

        .base-timer {
            position: relative;
            width: 75px;
            height: 75px;
            margin-top: 10px;
        }

        .base-timer__label {
            position: absolute;
            width: 100%;
            height: 100%;
            color: #000;
            font-weight: 800;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .base-timer__svg {
            transform: scaleX(-1);
        }

        .base-timer__circle {
            fill: none;
            stroke: none;
        }

        .base-timer__path-elapsed {
            stroke-width: 7px;
            stroke: grey;
        }

        .base-timer__path-remaining {
            stroke-width: 7px;
            stroke-linecap: round;
            transform: rotate(90deg);
            transform-origin: center;
            transition: 1s linear all;
            fill-rule: nonzero;
            stroke: currentColor;
        }

        .base-timer__path-remaining.green {
            color: rgb(65, 184, 131);
        }

        .base-timer__path-remaining.orange {
            color: orange;
        }

        .base-timer__path-remaining.red {
            color: red;
        }

        .border-radius-9 {
            border-radius: 15px !important;
        }

        .catagory-card a::after {
            background: none !important;
        }
    </style>
@endsection
@section('content')

    <div class="page-content-wrapper">
        <div class="news-today-wrapper">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 pl-1 newsten-title"></h5>
                </div>
            </div>
            <div class="container">
                <!-- Hero Slides-->
                <div class="hero-slides owl-carousel">


                    @foreach ($specialtab as $list)
                        <!-- Single Hero Slide-->

                        <div style="">
                            <a href="{{ $list->redirection }}" target="_new"><img
                                    src="{{ Storage::url('files/' . $list->image) }}" class="w-100 rounded-5">
                            </a>
                            <!-- Background Shape-->
                            <a href="{{ $list->redirection }}" target="_new" class="btn   w-100 rounded-end">More
                                Details</a>


                        </div>
                    @endforeach

                </div>
            </div>
        </div>
        <!-- Editorial Choice News Wrapper-->
        <div class="editorial-choice-news-wrapper1">

            <div class="container">
                <div class="editorial-choice-title text-center mb-4">
                    <h4 class="newsten-title text-dark fw-bold">Latest Updates</h4>
                </div>
            </div>


            <div class="container">

                <!-- Editorial Choice News Slide-->
                <div class="editorial-choice-news-slide owl-carousel p-0">
                    @foreach ($psc_news as $news)
                        <!-- Single Slide-->
                        <div class="single-editorial-slide">
                            <div class="card catagory-card border-0">
                                <a href="{{ url('kpsc/news/' . $news->id) }}">
                                    <div class="news-bg"
                                        style="background:url('{{ Storage::url('files/' . $news->image) }}'); background-size:     cover;background-repeat:   no-repeat;background-position: center center;    height:200px">
                                    </div>

                                </a>
                                <h6 class="text_limit m-2 text-center text-light news-text">{{ $news->title }}</h6>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <div class="col-lg-12">
            <div class="container">
                @if ($next_exam)

                    @section('scripts')
                        <script>
                            var dateVal = "{{ date('M d, Y', strtotime($next_exam->exam_date)) }}";
                            var timeVal = "{{ date('h:i a', strtotime($next_exam->started_at)) }}";

                            // Set the date we're counting down to
                            var countDownDate = new Date(dateVal + ' ' + timeVal).getTime();

                            // Update the count down every 1 second
                            var x = setInterval(function() {

                                // Get today's date and time
                                var now = new Date().getTime();

                                // Find the distance between now and the count down date
                                var distance = countDownDate - now;

                                // Time calculations for days, hours, minutes and seconds
                                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                var hours = Math.floor((distance % (1000 * 60 * 60 * 24 * 24)) / (1000 * 60 * 60));
                                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                // Output the result in an element with id="demo"
                                //   document.getElementById("{{ $next_exam->exam_date }}").innerHTML =  + hours + "h "
                                //   + minutes + "m " + seconds + "s ";
                                var date_id = "{{ $next_exam->exam_date }}";
                                $('#' + date_id + ' .hour').html(hours + "h");
                                $('#' + date_id + ' .minutes').html(minutes + "m");
                                $('#' + date_id + ' .seconds').html(seconds + "s");


                                // If the count down is over, write some text 
                                if (distance < 0) {
                                    clearInterval(x);
                                    //window.location = "";
                                    $('#date_id').html('<h3>Exam Started..</h3>')
                                }
                            }, 1000);
                        </script>
                    @endsection
                @endif
                <div class="editorial-choice-title text-center mt-4 mb-4"><i class="lni lni-question-circle"></i>
                    <h4 class="newsten-title text-dark">Daily Exam Series </h4>
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex justify-content-center">

                        @if ($next_exam)
                            <div class="col-mg-6 col-lg-6 mb-3">
                                <a href="/kpsc/daily-exam">
                                    @if ($next_exam->started_at >= date('Y-m-d H:i:s'))
                                        <div class="row mb-2" id="{{ $next_exam->exam_date }}">
                                            <div class=" col-4 d-flex justify-content-center">
                                                <div class="base-timer">
                                                    <svg class="base-timer__svg" viewBox="0 0 100 100"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g class="base-timer__circle">
                                                            <circle class="base-timer__path-elapsed" cx="50"
                                                                cy="50" r="45"></circle>
                                                            <path id="base-timer-path-remaining" stroke-dasharray="-14 283"
                                                                class="base-timer__path-remaining red" d="
                                                                  M 50, 50
                                                                  m -45, 0
                                                                  a 45,45 0 1,0 90,0
                                                                  a 45,45 0 1,0 -90,0
                                                                "></path>
                                                        </g>
                                                    </svg>
                                                    <span id="base-timer-label" class="base-timer__label hour">0:00</span>
                                                </div>
                                            </div>
                                            <div class=" col-4 d-flex justify-content-center">
                                                <div class="base-timer">
                                                    <svg class="base-timer__svg" viewBox="0 0 100 100"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g class="base-timer__circle">
                                                            <circle class="base-timer__path-elapsed" cx="50"
                                                                cy="50" r="45"></circle>
                                                            <path id="base-timer-path-remaining" stroke-dasharray="-14 283"
                                                                class="base-timer__path-remaining red" d="
                                                              M 50, 50
                                                              m -45, 0
                                                              a 45,45 0 1,0 90,0
                                                              a 45,45 0 1,0 -90,0
                                                            "></path>
                                                        </g>
                                                    </svg>
                                                    <span id="base-timer-label"
                                                        class="base-timer__label minutes">0:00</span>
                                                </div>
                                            </div>
                                            <div class=" col-4 d-flex justify-content-center">
                                                <div class="base-timer">
                                                    <svg class="base-timer__svg" viewBox="0 0 100 100"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g class="base-timer__circle">
                                                            <circle class="base-timer__path-elapsed" cx="50"
                                                                cy="50" r="45"></circle>
                                                            <path id="base-timer-path-remaining" stroke-dasharray="-14 283"
                                                                class="base-timer__path-remaining red" d="
                                                              M 50, 50
                                                              m -45, 0
                                                              a 45,45 0 1,0 90,0
                                                              a 45,45 0 1,0 -90,0
                                                            "></path>
                                                        </g>
                                                    </svg>
                                                    <span id="base-timer-label"
                                                        class="base-timer__label seconds">0:00</span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-lg-12">
                                            <marquee style="font-family:Book Antiqua; color: #FFFFFF" bgcolor="#000080"
                                                scrollamount="5" loop="3">Exam started... Exam started... Exam
                                                started... Exam started... Exam started... Exam started... Exam started...
                                                Exam started... Exam started... Exam started... Exam started...</marquee>

                                        </div>
                                    @endif

                                    <!-- Single Trending Post-->

                                    <div class="single-trending-post bg-dark  d-flex p-0">
                                        <!--<div class="post-thumbnail text-right mt-4">-->
                                        <!--    <img src="{{ url('psc/img/test.png') }}" alt="">-->
                                        <!--</div>-->
                                        <div class="post-content text-center">
                                            <a href="/kpsc/daily-exam" class="post-title text-light text-capitalize mt-2">
                                                {{ $next_exam->examtitle }} <br>
                                                <!--{{ $next_exam->subject }}-->
                                            </a>
                                            <div class="col-lg-12 mb-1 mt-2">
                                                <div class="row">
                                                    <div class="col-6  col-lg-6 post-meta align-items-center mb-3">
                                                        <a href='/kpsc/daily-exam/' class="bg-light text-dark">
                                                            <i class="fa fa-calendar mr-1"></i>
                                                            {{ date('d-M-Y', strtotime($next_exam->exam_date)) }}
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-lg-6  post-meta align-items-center mb-3 ">
                                                        <a class="bg-light text-dark" href="/kpsc/daily-exam">
                                                            <i class="fa fa-clock-o"></i>
                                                            {{ date('h:i a', strtotime($next_exam->started_at)) }}
                                                        </a>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-12 d-flex justify-content-center">
                        <div class="col-md-6 ">
                            <div class="single-trending-post d-flex bg-dark rounded mb-3 p-3 border-radius-9">
                                <div class="post-thumbnail">
                                    <img src="{{ url('images/questionnaire.png') }}" alt="questionnaire" class="w-100">
                                </div>
                                <div class="post-content">
                                    <a class="post-title text-light" href="/kpsc/daily-exam">
                                        Attend for Previous Exam's
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="d-flex justify-content-center">
            <div class="col-lg-6">

                <div class="col-lg-12">
                    <div
                        style="padding-top:0px;height:50px;text-align:center;background-image:url({{ url('images/notice_top.gif') }}); background-size: 100% 100%; background-repeat:no-repeat">
                        <div
                            style="background: url({{ url('images/col_box_title.gif') }}) no-repeat center bottom;padding: 12px 5px 4px;color: rgb(12, 84, 124);background-image:url({{ url('images/notice_title.gif') }});background-repeat:no-repeat;font-weight: normal;text-align: center;font-family: Georgia, Arial, Helvetica, sans-serif;font-size: 19px;line-height: 35px;">
                            Latest Board
                        </div>
                    </div>
                    <div class="news"
                        style="background-size: 100% 100%; background-image:url({{ url('images/notice_fill_content.gif') }});background-repeat:repeat-y">
                        <!--DWLayoutTable-->
                        <div style="overflow:auto;padding-left:0px;height:300px;font-size:11px; font-family: verdana,Helvetica, sans-serif;width: 95%;background-color: white;margin-left: 5px;"
                            align="left" behavior="scroll" direction="up" onmouseover="this.stop()"
                            onmouseout="this.start()" scrollamount="2" scrolldelay="1">
                            <ul style="margin-left:0px;padding-left:25px;padding-right:20px">

                                @foreach ($noftify as $notification_list)
                                    <li style="padding: 20px 0px 7px 0px;border-bottom: 1px dotted grey;border-top: none;">
                                        <span class="ml-2 badge badge-danger">New</span> <a
                                            href="{{ $notification_list->redirection }}">
                                            {{ $notification_list->title }}
                                        </a>
                                    </li>
                                @endforeach


                            </ul>
                        </div>
                        <div style="width:100%;height:11px;margin-left: 5px;text-align:left;"></div>

                        <div
                            style="background-size: 100% 100%; background-image:url('{{ url('images/notice_bottom.gif') }}');">
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}


        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- For You News Wrapper-->
                    <div class="for-you-news-wrapper">
                        <div class="container">
                            <div class="editorial-choice-title text-center mb-4"><i class="lni lni-files"></i>
                                <h4 class="newsten-title text-dark">Special Tab's</h4>
                            </div>
                        </div>
                        <!-- <div class="video-icon"><i class="lni lni-play"></i></div> -->
                        <div class=" text-center ">
                            <div class="row d-flex justify-content-center">

                                <div class="row justify-content-center">
                                    @foreach ($whatsnew as $whatsnew_list)
                                        <!-- Single Trending Post-->
                                        <div class="col-lg-4 col-md-4 col-6 mb-2">
                                            <div
                                                style="background:{{ $whatsnew_list->bg_color }};color:{{ $whatsnew_list->text_color }};border-radius:9px;">
                                                <div class="single-trending-post1 p-2">
                                                    <div class="post-thumbnail1 text-center">
                                                        <a class="post-title1"
                                                            @if ($whatsnew_list->target == 1) target="_new" @endif
                                                            style="color:{{ $whatsnew_list->text_color }};font-size:1rem;"
                                                            href="{{ $whatsnew_list->redirection }}">
                                                            <img src="{{ Storage::url('files/' . $whatsnew_list->image) }}"
                                                                alt="{{ $whatsnew_list->title }}" class="mt-3 w-25">
                                                        </a>
                                                    </div>
                                                    <div class="post-content1 p-3 text-center">
                                                        <a class="post-title1"
                                                            @if ($whatsnew_list->target == 1) target="_new" @endif
                                                            style="color:{{ $whatsnew_list->text_color }};font-size:1rem;"
                                                            href="{{ $whatsnew_list->redirection }}">
                                                            {{ $whatsnew_list->title }}
                                                        </a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>


        <!-- Top Catagories Wrapper-->
        <div class="top-catagories-wrapper">
            <div class="bg-shapes">
                <div class="shape1"></div>
                <div class="shape2"></div>
                <div class="shape3"></div>
                <div class="shape4"></div>
                <div class="shape5"></div>
            </div>
            <div class="container">
                <div class="editorial-choice-title text-center mb-4">
                    <h4 class="newsten-title text-dark">Subject Categories </h4><i class="lni lni-protection mt-1"></i>
                </div>
            </div>
            <div class="container">
                <!-- Catagory Slides-->
                <div class="row">
                    @foreach ($category_free as $list)
                        <!-- Catagory Card-->
                        <div class="col-lg-3 col-sm-3 col-sm-6 col-6">
                            <a href="{{ url('kpsc/subject/' . $list->slug_name) }}">
                                <div class="parent ">

                                    <div class="child">
                                        <img width="100%" src="{{ Storage::url('files/' . $list->image) }}">
                                    </div>
                                    <p class="text-child">{{ $list->subject_title }}</p>

                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Our Stories Wrapper-->
        <div class="news-today-wrapper">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 pl-1 newsten-title">Our Stories</h5>
                </div>
            </div>
            <div class="container">
                <!-- Hero Slides-->
                <div class="hero-slides owl-carousel">
                    @foreach ($banner as $banner_list)
                        <!-- Single Hero Slide-->
                        <a href="{{ $banner_list->redirection }}">
                            <div class="single-hero-slide"
                                style="background-image: url('{{ Storage::url('files/' . $banner_list->image) }}')">
                                <!-- Background Shape-->
                                <div class="background-shape">
                                    <div class="circle2"></div>
                                    <div class="circle3"></div>
                                </div>
                            </div>
                        </a>
                    @endforeach

                </div>
            </div>
        </div>

        <!-- Editorial Choice News Wrapper-->
        <div class="editorial-choice-news-wrapper1">
            <!-- Background Shapes-->
            <div class="bg-shape1"></div>
            <div class="bg-shape2" style=""></div>
            <div class="container">
                <div class="editorial-choice-title text-center mb-4"><i class="lni lni-flickr"></i>
                    <h4 class="newsten-title text-dark fw-bolder">Latest youtube class's</h4>
                </div>
            </div>
            <div class="container">
                <div class="editorial-choice-news-slide owl-carousel">
                    {{-- @foreach ($ourvideos as $videos)
              <!-- Single Slide-->
                <div class="single-editorial-slide ">
                    <a target="_new" href="https://youtu.be/{{$videos->redirection}}">
                        <div style="background-image:url('https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg'); height:152px; background-size:100%;
                                     background-repeat:no-repeat;" class="card catagory-card">
                            <span   style="transition-duration: 500ms;
                                            font-size: 12px;margin-bottom: 0;
                                            position: absolute;left: 0.5rem;
                                            bottom: 0.5rem;z-index: 100;
                                            color: #ffffff;background-color: #19af64d1;
                                            padding: 0.25rem 0.625rem;border-radius: 2rem;" 
                                    class=" text_limit">{{$videos->title}}</span>
                        </div>
                    </a>
                </div>
              @endforeach --}}
                    @foreach ($specialvideos as $videos)
                        <div class="single-editorial-slide ">
                            <a target="_new" href="https://youtu.be/{{ $videos->redirection }}">
                                <div style=" min-height:152px; 
                                             "
                                    class="card catagory-card">

                                    <img src="https://img.youtube.com/vi/{{ $videos->redirection }}/0.jpg"
                                        class="w-100 rounded-4">
                                    <span
                                        style="transition-duration: 500ms;
                                                    font-size: 12px;margin-bottom: 0;
                                                    position: absolute;left: 0.5rem;
                                                    bottom: 0.5rem;z-index: 100;
                                                    color: #ffffff;background-color: #19af64d1;
                                                    padding: 0.25rem 0.625rem;border-radius: 2rem;"
                                        class=" text_limit">{{ $videos->title }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach

                </div>
                <!--<a class="btn btn-primary btn-sm float-right " href="{{ url('kpsc/psc-our-videos') }}">View All</a>-->


            </div>
        </div>
        @if (count($specialvideos) > 0)
            {{-- <div class="for-you-news-wrapper">
        <div class="container">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="">Latest youtube class's</h4>
          </div>
        </div>
        <div class="container">
         
         <!-- Editorial Choice News Slide-->
         <div class="newsten-owl-carousel-slides owl-carousel">
          @foreach ($specialvideos as $videos)
                <div class="single-editorial-slide ">
                    <a target="_new" href="https://youtu.be/{{$videos->redirection}}">
                        <div style="background-image:url('https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg'); height:152px; background-size:100%;
                                     background-repeat:no-repeat;" class="card catagory-card">
                            <span   style="transition-duration: 500ms;
                                            font-size: 12px;margin-bottom: 0;
                                            position: absolute;left: 0.5rem;
                                            bottom: 0.5rem;z-index: 100;
                                            color: #ffffff;background-color: #19af64d1;
                                            padding: 0.25rem 0.625rem;border-radius: 2rem;" 
                                    class=" text_limit">{{Str::limit(15,$videos->title)}}</span>
                        </div>
                    </a>
                </div>
         @endforeach
          
        </div>

        </div>
      </div> --}}
        @endif




    </div>

@endsection
