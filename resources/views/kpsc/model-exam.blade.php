@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? 'KPSC-Model Exam List-')
@php $title = "KPSC-Model Exam List"; @endphp
@section('styles')
    <style>
        body {
            background: linear-gradient(to bottom right, #38A3A5, #57CC99, #80ED99);
            min-height: 100vh;
        }

        .base-timer {
            position: relative;
            width: 75px;
            height: 75px;
        }

        .base-timer__label {
            position: absolute;
            width: 100%;
            height: 100%;
            color: #fff;
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

        h6 {
            color: #fff;
            font-weight: 800;
        }
    </style>

@endsection



@section('content')
    <div class="page-content-wrapper">
        <!-- Trending News Wrapper-->
        <div class="trending-news-wrapper">
            <!-- Button trigger modal -->


            <div class="container mb-2 mt-2 text-right">


                <button type="button" class="btn btn-primary " data-toggle="modal" data-target="#exampleModalScrollable">
                    <i class="fa fa-info-circle"> </i> Exam Instructions
                </button>
            </div>


            <div class="container mt-5">
                @php
                    date_default_timezone_set('Asia/Kolkata');
                    $date_upcoming = $date_list
                        ->where('exam_date', '>=', date('Y-m-d'))
                        ->where('started_at', '>', date('Y-m-d H:i:s'));
                    $k = 0;
                @endphp

                @if ($date_upcoming->count() > 0)
                    <h6 class="text-light text-center">
                        Up Coming Exam
                    </h6>
                    @foreach ($date_upcoming as $key => $list_date)
                        @php $k=$k+1; @endphp
                        @if ($k == 1)
                            <div class="row mb-2" id="{{ $list_date->exam_date }}">

                                <div class=" col-4 d-flex justify-content-center">
                                    <div class="base-timer">
                                        <svg class="base-timer__svg" viewBox="0 0 100 100"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g class="base-timer__circle">
                                                <circle class="base-timer__path-elapsed" cx="50" cy="50"
                                                    r="45"></circle>
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
                                                <circle class="base-timer__path-elapsed" cx="50" cy="50"
                                                    r="45"></circle>
                                                <path id="base-timer-path-remaining" stroke-dasharray="-14 283"
                                                    class="base-timer__path-remaining red" d="
                                      M 50, 50
                                      m -45, 0
                                      a 45,45 0 1,0 90,0
                                      a 45,45 0 1,0 -90,0
                                    "></path>
                                            </g>
                                        </svg>
                                        <span id="base-timer-label" class="base-timer__label minutes">0:00</span>
                                    </div>
                                </div>
                                <div class=" col-4 d-flex justify-content-center">
                                    <div class="base-timer">
                                        <svg class="base-timer__svg" viewBox="0 0 100 100"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g class="base-timer__circle">
                                                <circle class="base-timer__path-elapsed" cx="50" cy="50"
                                                    r="45"></circle>
                                                <path id="base-timer-path-remaining" stroke-dasharray="-14 283"
                                                    class="base-timer__path-remaining red" d="
                                      M 50, 50
                                      m -45, 0
                                      a 45,45 0 1,0 90,0
                                      a 45,45 0 1,0 -90,0
                                    "></path>
                                            </g>
                                        </svg>
                                        <span id="base-timer-label" class="base-timer__label seconds">0:00</span>
                                    </div>
                                </div>

                            </div>
                        @endif

                        <!-- Single Trending Post-->
                        <div class="single-trending-post bg-dark  d-flex">
                            <div class="post-thumbnail text-right mt-4">
                                <img src="{{ url('psc/img/test.png') }}" alt="">
                            </div>
                            <div class="post-content">
                                <a class="post-title text-light text-capitalize">
                                    {{ $list_date->examtitle }}
                                </a>
                                <div class="d-flex mb-1 mt-2">
                                    <div class="post-meta align-items-center mr-3">
                                        <a class="bg-danger">
                                            <i class="fa fa-calendar mr-1"></i>
                                            {{ date('d-M-Y', strtotime($list_date->exam_date)) }}
                                        </a>
                                    </div>
                                    <div class="post-meta align-items-center mr-3 ">
                                        <a class="bg-warning">
                                            <i class="fa fa-clock-o"></i>
                                            {{ date('h:i a', strtotime($list_date->started_at)) }}
                                        </a>
                                    </div>

                                </div>

                            </div>
                        </div>
                        @if ($k == 1)
                            @section('scripts')
                                <script>
                                    var dateVal = "{{ date('M d, Y', strtotime($list_date->exam_date)) }}";
                                    var timeVal = "{{ date('h:i a', strtotime($list_date->started_at)) }}";

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
                                        //   document.getElementById("{{ $list_date->exam_date }}").innerHTML =  + hours + "h "
                                        //   + minutes + "m " + seconds + "s ";
                                        var date_id = "{{ $list_date->exam_date }}";
                                        $('#' + date_id + ' .hour').html(hours + "h");
                                        $('#' + date_id + ' .minutes').html(minutes + "m");
                                        $('#' + date_id + ' .seconds').html(seconds + "s");


                                        // If the count down is over, write some text 
                                        if (distance < 0) {
                                            clearInterval(x);
                                            window.location = "";
                                        }
                                    }, 1000);
                                </script>
                            @endsection
                        @endif
                    @endforeach
                @endif
                @php

                    $date_ongoing = $date_list
                        ->where('exam_date', date('Y-m-d'))
                        ->where('started_at', '<=', date('Y-m-d H:i:s'))
                        ->where('ended_at', '>=', date('Y-m-d H:i:s'));
                @endphp

                @if ($date_ongoing->count() > 0)
                    <h6 class="text-center">
                        Ongoing Exam
                    </h6>
                    @foreach ($date_ongoing as $list_date)
                        <!-- Single Trending Post-->
                        <div class="single-trending-post bg-dark  d-flex">
                            <div class="post-thumbnail text-right mt-4">
                                <img src="{{ url('psc/img/test.png') }}" alt="">
                            </div>
                            <div class="post-content">
                                <div class="post-meta text-right d-inline float-right">
                                    <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}"
                                        class="bg-warning">
                                        <i class="fa fa-clock-o"></i>
                                        {{ date('h:i a', strtotime($list_date->started_at)) }}
                                    </a>
                                </div>
                                <div class="post-meta text-right d-inline float-right">
                                    <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}"
                                        class="bg-danger">
                                        <i class="fa fa-calendar mr-1"></i>
                                        {{ date('d-M-Y', strtotime($list_date->exam_date)) }}

                                    </a>
                                </div>


                                <a class="post-title text-light text-capitalize mb-2"
                                    href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}">
                                    {{ $list_date->examtitle }}
                                </a>

                                <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}"
                                    class="  py-2 badge badge-pill badge-primary mb-2">Attend for exam</a>

                                <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id . '/answerkey') }}"
                                    class=" py-2 badge badge-pill badge-info mb-2">Answer Key</a>



                            </div>
                        </div>
                    @endforeach
                @endif

                <h6 class="text-center">
                    Previous Exams
                </h6>

                @foreach ($date_list->where('exam_date', '<=', date('Y-m-d'))->where('ended_at', '<',
                        date('Y-m-d H:i:s'))->sortByDesc('exam_date') as $key => $list_date)
                        <!-- Single Trending Post-->
                        <div class="single-trending-post bg-dark  d-flex">
                            <div class="post-thumbnail text-right mt-4">
                                <img src="{{ url('psc/img/test.png') }}" alt="">
                            </div>
                            <div class="post-content">
                                <div class="post-meta text-right">
                                    <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}"
                                        class="bg-danger">
                                        <i class="fa fa-calendar mr-1"></i>
                                        {{ date('d-M-Y', strtotime($list_date->exam_date)) }}

                                    </a>
                                </div>
                                <a class="post-title text-light text-capitalize"
                                    href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}">
                                    {{ $list_date->examtitle }}
                                </a>

                                <div class="mb-1 mt-2 d-inline-flex">

                                    <div class="post-meta  mt-2 mb-2">

                                        <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id . '/leaderboard') }}"
                                            class="bg-success p-2">
                                            <i class="fa fa-bar-chart-o"></i>
                                            <span>Leaderboard</span>

                                        </a>
                                    </div>
                                </div>
                                <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id) }}"
                                    class="  py-2 badge badge-pill badge-primary mb-2">Attend for exam</a>
                                <a href="{{ url('kpsc/model-exam/' . $list_date->exam_date . '/' . $list_date->id . '/answerkey') }}"
                                    class=" py-2 badge badge-pill badge-info mb-2">Answer Key</a>
                                <!-- Button trigger modal -->
                                <button type="button" class=" py-2 badge badge-pill badge-success mb-2 upload-mark"
                                    data-id="{{ $list_date->id }}">
                                    Upload your marks
                                </button>

                            </div>
                        </div>
                        {!! small_ads_space_sport($key) !!}
                @endforeach


            </div>

        </div>

        <!-- Ads -->
        {!! single_ads_show() !!}
        <!-- Ads -->
        {!! single_ads_show() !!}
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle">Exam Instructions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4>Read the Instructions Carefully</h4>
                    <ol class="p-3">
                        <li> ✅ പരീക്ഷയുടെ ദൈർഘ്യം ഒരു മണിക്കൂർ 15 മിനിറ്റ്</li>

                        <li> ✅ഓരോ ശരിയുത്തരങ്ങൾക്കും ഓരോ മാർക്ക് വീതം</li>

                        <li> ✅ ഉത്തരം തെറ്റിയാൽ 1/3 നെഗറ്റീവ് മാർക്ക് ഉണ്ടായിരിക്കുന്നതാണ്</li>

                        <li> ✅ അറ്റൻഡ് ചെയ്യാത്ത ചോദ്യങ്ങൾക്ക് മാർക്ക് നഷ്ടപ്പെടുന്നതല്ല</li>

                        <li> ✅ ഇംഗ്ലീഷ് മലയാളം മാത്തമാറ്റിക് ജികെ എന്നീ വിഭാഗങ്ങളുടെ മാർക്ക് എടുത്ത് OMR ഷീറ്റിൽ
                            എഴുതേണ്ടതാണ്</li>

                        <li>✅ പരീക്ഷയെഴുതിയ ശേഷം നിങ്ങൾക്ക് ലഭിച്ച ആകെ ശരിയുത്തരങ്ങൾ, എത്ര ചോദ്യമാണ് തെറ്റിയത് ന്നിവ ഇവിടെ
                            അപ്‌ലോഡ് ചെയ്യുക</li>

                        <li>✅ നിശ്ചിത സമയത്തിനുള്ളിൽ ലീഡർ ബോർഡ് നിങ്ങൾക്ക് ലഭ്യമാകുന്നതാണ്</li>

                    </ol>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fromModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Upload your exam mark details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body upload-form">

                </div>

            </div>
        </div>
    </div>

@endsection

@push('custom-scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            @if (\Session::has('success'))
                Swal.fire({
                    title: "Congrats!",
                    text: "{{ \Session::get('success') }}",
                    icon: "success"
                });
            @endif

            $('body').on('click', '.upload-mark', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: "GET",
                    url: `{{ url('kpsc/model-exam-mark-form') }}`,
                    data: {
                        'id': id
                    },
                    cache: false,
                    success: function(data) {
                        $(".upload-form").html(data);
                        $('#fromModal').modal('show')
                    }
                });
            });
        });
    </script>
@endpush
