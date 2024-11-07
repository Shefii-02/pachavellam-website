@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('styles')
<style>
    div.parent {
      position: relative;
      width: 100%;
      height: 15vh;
      border: 1px solid black;
      border-radius:9px;
      overflow: hidden;
      background-color:#000;
      margin-bottom:10px;
    } 

    div.child {
      position: absolute;
      bottom:-3px;
      right: -8px;
      width: 50%;
      height: 50%;
      box-shadow: 0 2px 4px 0 rgb(0 0 0 / 20%);
      transform: rotate(20deg);
    }
    p.text-child{
      color:#fff;
     padding:5px;
      top:0px;
      font-size:19px;
      font-weight:800;
      position: absolute;
      z-index:9999;
    }
    @media only screen and (min-width: 600px) {
        div.parent {
            
          height: 20vh !important;
        }
    }
                              
                            .base-timer {
                            position: relative;
                            width: 75px;
                            height: 75px;
                            margin-top:10px;
                        }
                        .base-timer__label {
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            color:#000;
                            font-weight:800;
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
</style>
@endsection
@section('title', $title ?? "KPSC - Home Page")
@section('content')
<div class="page-content-wrapper">
      <!-- News Today Wrapper-->
    <div class="news-today-wrapper">
    <div class="container">
      <!-- Hero Slides-->
      <div class="hero-slides owl-carousel">
        @foreach ($banner as $banner_list)
          <!-- Single Hero Slide-->
          <a href="{{$banner_list->redirection}}">
            <div class="single-hero-slide" style="background-image: url('{{ Storage::url('files/'.$banner_list->image) }}')">
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
    
    <div class="col-lg-12">
        <div class="container">
                    @if($next_exam)
                        
                        @section('scripts')
        
                            <script>
                            
                            var dateVal = "{{date('M d, Y',strtotime($next_exam->exam_date))}}";
                            var timeVal = "{{date('h:i a',strtotime($next_exam->started_at))}}";
                            
                            // Set the date we're counting down to
                            var countDownDate = new Date(dateVal+' '+timeVal).getTime();
                            
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
                            //   document.getElementById("{{$next_exam->exam_date}}").innerHTML =  + hours + "h "
                            //   + minutes + "m " + seconds + "s ";
                                var date_id = "{{$next_exam->exam_date}}";
                                $('#'+date_id+' .hour').html(hours+"h");
                                $('#'+date_id+' .minutes').html(minutes+"m");
                                $('#'+date_id+' .seconds').html(seconds+"s");
                    
                                
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
                            @if($next_exam)
                                <div class="col-mg-6 col-lg-6 mb-3">
                                        
                                    <a href="/kpsc/daily-exam">
                                        <div class="row mb-2" id="{{$next_exam->exam_date}}" >
                                           <div class=" col-4 d-flex justify-content-center">
                                               <div class="base-timer">
                                              <svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                                <g class="base-timer__circle">
                                                  <circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
                                                  <path id="base-timer-path-remaining" stroke-dasharray="-14 283" class="base-timer__path-remaining red" d="
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
                                              <svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                                <g class="base-timer__circle">
                                                  <circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
                                                  <path id="base-timer-path-remaining" stroke-dasharray="-14 283" class="base-timer__path-remaining red" d="
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
                                              <svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                                <g class="base-timer__circle">
                                                  <circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
                                                  <path id="base-timer-path-remaining" stroke-dasharray="-14 283" class="base-timer__path-remaining red" d="
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
                                   
                         
                                      <!-- Single Trending Post-->
                                      
                                        <div class="single-trending-post bg-dark  d-flex">
                                            <div class="post-thumbnail text-right mt-4">
                                                <img src="{{url('psc/img/test.png')}}" alt="">
                                            </div>
                                            <div class="post-content">
                                                <a  href="/kpsc/daily-exam"  class="post-title text-light text-capitalize">
                                                {{$next_exam->examtitle}} <br>
                                {{$next_exam->subject}}
                                                </a>
                                                <div class="d-flex1 text-center 
                                                mb-1 mt-2">
                                                    <div class="post-meta align-items-center mr-3">
                                                        <a href='/kpsc/daily-exam/' class="bg-danger">
                                                            <i class="fa fa-calendar mr-1"></i>  
                                                            {{date('d-M-Y',strtotime($next_exam->exam_date))}} 
                                                        </a>
                                                      </div>
                                                      <div class="post-meta align-items-center mr-3 ">
                                                        <a   class="bg-warning" href="kpsc/daily-exam">
                                                            <i class="fa fa-clock-o"></i>  
                                                            {{date('h:i a',strtotime($next_exam->started_at))}}           
                                                        </a>
                                                      </div>
                                                     <div class="post-meta align-items-center mr-3 ">
                                                        <a href="https://www.pachavellam.com/kpsc/daily-exam"  class="bg-info">
                                                            <i class="fa fa-info-circle"></i>  
                                                            Exam Instructions          
                                                        </a>
                                                      </div>
                                                </div>
                                              
                                            </div>
                                        </div>
                                        </a>
                                </div>
                            @endif
                            <div class="col-md-6 single-trending-post d-flex bg-success rounded mb-3">
                                  <div class="post-thumbnail"> 
                                    <img src="{{url('images/questionnaire.png')}}" alt="questionnaire" class="w-100">
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
            
    <div class="col-lg-12">
        
        <div class="container">
              <!-- Trending News Wrapper-->
              <div class="trending-news-wrapper">
                <div class="">
                  <div class=" mb-3">
                    <h5 class="mb-0 pl-1   text-center  newsten-title">What's New</h5>
                  </div>
                </div>
                <div class="row">
                  @foreach($whatsnew as $whatsnew_list)
                    <!-- Single Trending Post-->
                    <div class="col-lg-4 col-md-4 col-6 mb-2" >
                        <div  style="background:{{$whatsnew_list->bg_color}};color:{{$whatsnew_list->text_color}};border-radius:9px;">
                          <div class="single-trending-post1 p-2">
                              <div class="post-thumbnail1 text-center"> 
                                <img src="{{ Storage::url('files/'.$whatsnew_list->image) }}" alt="{{$whatsnew_list->title}}" class="w-25">
                              </div> 
                              <div class="post-content1 p-3 text-center">
                                <a class="post-title1" style="color:{{$whatsnew_list->text_color}};font-size:1rem;" href="{{$whatsnew_list->redirection}}">  
                                  {{$whatsnew_list->title}}     
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
         
    <div class="container">
    <div class="row">
        <div class="col-lg-12">
               <!-- For You News Wrapper-->
            <div class="for-you-news-wrapper">
                <div class="container">
                  <div class="editorial-choice-title text-center mb-4"><i class="lni lni-files"></i>
                    <h4 class="newsten-title text-dark">Special Material </h4>
                  </div>
                </div>
                <!-- <div class="video-icon"><i class="lni lni-play"></i></div> -->
                <div class="container text-center ">
                  <div class="row">
                    @foreach($specialtab as $list)
                    <!-- Single Recommended Post-->
          
                        
                        <div class="col-sm-12 col-md-6">
                            <div class=" mt-3">
                                <a class="post-title rounded" href="{{$list->redirection}}">
                                  <div class="post-thumbnail"><img src="{{ Storage::url('files/'.$list->image) }}" alt=""></div>
                                </a>
                            </div>
                        </div>
                   
                    @endforeach
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
            <h4 class="newsten-title text-dark">Subject Category </h4><i class="lni lni-protection"></i>
          </div>
        </div>
        <div class="container">
          <!-- Catagory Slides-->
          <div class="row">
            @foreach ($category_free as $list)
            <!-- Catagory Card-->
            <div class="col-lg-3 col-sm-3 col-sm-6 col-6">
                <div class="parent ">
                    <a href="{{url('kpsc/subject/'.$list->name_slug)}}">
                        <div class="child">
                          <img width="100%"  src="{{ Storage::url('files/'.$list->image) }}">
                        </div>
                        <p class="text-child">{{$list->category_name}}</p>
                    </a>
                </div>
            </div>

            @endforeach
          </div>
        </div>
      </div>
      
     
      <!-- Editorial Choice News Wrapper-->
    <div class="editorial-choice-news-wrapper">
        <!-- Background Shapes-->
        <div class="bg-shape1"></div>
        <div class="bg-shape2" style=""></div>
        <div class="container">
          <div class="editorial-choice-title text-center mb-4"><i class="lni lni-flickr"></i>
            <h4 class="newsten-title text-light">Our Videos</h4>
          </div>
        </div>
        <div class="container">
            <div class="editorial-choice-news-slide owl-carousel">
              @foreach($ourvideos as $videos)
              <!-- Single Slide-->
              <div class="single-editorial-slide ">
                <div class="card catagory-card"><a href="https://youtu.be/{{$videos->redirection}}">
                  <img src="https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg" alt="">
                  <h6 class="text_limit">{{$videos->title}}</h6></a></div>
              </div>
              @endforeach
            </div>
            <a class="btn btn-primary btn-sm float-right " href="{{url('kpsc/psc-our-videos')}}">View All</a>


        </div>
      </div>
      
    <div class="for-you-news-wrapper">
        <div class="container">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="">Special Class Videos</h4>
            <a class="btn btn-primary btn-sm" href="{{url('kpsc/psc-special-videos')}}">View All</a>
          </div>
        </div>
        <div class="container">
         
         <!-- Editorial Choice News Slide-->
         <div class="newsten-owl-carousel-slides owl-carousel">
          @foreach($specialvideos as $videos)
          <!-- Single Slide-->
          <div class="single-editorial-slide ">
            <div class="card catagory-card">
              <a href="https://youtu.be/{{$videos->redirection}}">
                <img src="https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg" alt="">
              <h6 class="text_limit">{{$videos->title}}</h6></a></div>
          </div>
         @endforeach
          
        </div>

        </div>
      </div>
    
       
     <div class="for-you-news-wrapper">
        <div class="container">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 pl-1 newsten-title">Latest News</h5>
            <a class="btn btn-primary btn-sm" href="{{url('kpsc/news-all')}}">View All</a>
          </div>
        </div>
        <div class="container">
         
         <!-- Editorial Choice News Slide-->
         <div class="editorial-choice-news-slide owl-carousel">
          @foreach($psc_news as $news)
          <!-- Single Slide-->
          <div class="single-editorial-slide ">
            <div class="card catagory-card">
                <a href="{{url('kpsc/news/'.$news->id)}}">
              <img src="{{ Storage::url('files/'.$news->image) }}" alt="">
              <h6 class="text_limit">{{$news->title}}</h6></a>
            </div>
          </div>
          @endforeach
        </div>
        </div>
      </div>
 
</div>
    
@endsection