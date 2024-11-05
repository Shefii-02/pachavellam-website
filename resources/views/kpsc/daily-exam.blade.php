@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Daily Exam List-" )
@php $title = "KPSC-Daily Exam List"; @endphp
@section('styles')
    <style>
    body{
          background: linear-gradient(to bottom right, #38A3A5, #57CC99, #80ED99);
          min-height:100vh;
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
    color:#fff;
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
h6{
    color:#fff;
    font-weight:800;
}
    </style>

@endsection

            

@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      <!-- Button trigger modal -->


      <div class="container mb-2 mt-2 text-right">
          @auth
          @else
              
              <a href="{{url('sign-in')}}" class="btn btn-info"><i class="fa fa-user"></i> Login/Register</a>
              
          @endauth
          
         <button type="button" class="btn btn-primary " data-toggle="modal" data-target="#exampleModalScrollable">
                  <i class="fa fa-info-circle" > </i>   Exam Instructions
            </button>
        </div>
         <div class="container">
        @php
        date_default_timezone_set("Asia/Kolkata"); 
            $date_upcoming  = $date_list->where('exam_date','>=',date('Y-m-d'))
            ->where('started_at','>',date('Y-m-d H:i:s'));
            $k=0;
        @endphp
      
        @if($date_upcoming->count() > 0)
          <h6 class="text-light">
              Up Coming Exam
          </h6>
           @foreach($date_upcoming as $key => $list_date)
           @php $k=$k+1; @endphp
           @if($k == 1)
           <div class="row mb-2" id="{{$list_date->exam_date}}" >
               
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
            @endif
             
          <!-- Single Trending Post-->
            <div class="single-trending-post bg-dark  d-flex">
                <div class="post-thumbnail text-right mt-4">
                    <img src="{{url('psc/img/test.png')}}" alt="">
                </div>
                <div class="post-content">
                    <a class="post-title text-light text-capitalize">
                    {{$list_date->examtitle}} <br>
                    {{$list_date->subject_title}}
                    </a>
                    <div class="d-flex mb-1 mt-2">
                        <div class="post-meta align-items-center mr-3">
                            <a  class="bg-danger">
                                <i class="fa fa-calendar mr-1"></i>  
                                {{date('d-M-Y',strtotime($list_date->exam_date))}} 
                            </a>
                          </div>
                          <div class="post-meta align-items-center mr-3 ">
                            <a   class="bg-warning">
                                <i class="fa fa-clock-o"></i>  
                                {{date('h:i a',strtotime($list_date->started_at))}}           
                            </a>
                          </div>
                    
                    </div>
                  
                </div>
            </div>
             @if($k == 1)
            @section('scripts')
                
                <script>
                
                var dateVal = "{{date('M d, Y',strtotime($list_date->exam_date))}}";
                var timeVal = "{{date('h:i a',strtotime($list_date->started_at))}}";
                
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
                //   document.getElementById("{{$list_date->exam_date}}").innerHTML =  + hours + "h "
                //   + minutes + "m " + seconds + "s ";
                    var date_id = "{{$list_date->exam_date}}";
                    $('#'+date_id+' .hour').html(hours+"h");
                    $('#'+date_id+' .minutes').html(minutes+"m");
                    $('#'+date_id+' .seconds').html(seconds+"s");

                    
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
         
            $date_ongoing  = $date_list->where('exam_date',date('Y-m-d'))
            ->where('started_at','<=',date('Y-m-d H:i:s'))
            ->where('ended_at','>=',date('Y-m-d H:i:s'))
        @endphp
 
        @if($date_ongoing->count() > 0)
          <h6>
              Ongoing Exam
          </h6>
           @foreach($date_ongoing as $list_date)
        
          <!-- Single Trending Post-->
            <div class="single-trending-post bg-dark  d-flex">
                <div class="post-thumbnail text-right mt-4">
                    <img src="{{url('psc/img/test.png')}}" alt="">
                </div>
                <div class="post-content">
                    <a class="post-title text-light text-capitalize" href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id)}}">
                    {{$list_date->examtitle}} <br>
                    {{$list_date->subject_title}}
                    </a>
                    <div class="d-flex mb-1 mt-2">
                        <div class="post-meta align-items-center mr-3">
                            <a  href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id)}}" class="bg-danger">
                                <i class="fa fa-calendar mr-1"></i>  
                             {{date('d-M-Y',strtotime($list_date->exam_date))}} 
                            </a>
                          </div>
                          <div class="post-meta align-items-center mr-3 ">
                            <a  href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id)}}" class="bg-warning">
                                <i class="fa fa-clock-o"></i>  
                                {{date('h:i a',strtotime($list_date->started_at))}}           
                            </a>
                          </div>
                    
                    </div>
                  
                </div>
            </div>
             
          @endforeach
         @endif
         
          <h6>
              Previous Exams
          </h6>
          
          @foreach($date_list->where('exam_date','<=',date('Y-m-d'))->where('ended_at','<',date('Y-m-d H:i:s'))->sortByDesc('exam_date') as $key => $list_date)
          <!-- Single Trending Post-->
          <div class="single-trending-post bg-dark  d-flex">
            <div class="post-thumbnail text-right mt-4">
                <img src="{{url('psc/img/test.png')}}" alt="">
            </div>
            <div class="post-content">
                <div class="post-meta text-right">
                <a  href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id)}}" class="bg-danger">
                    <i class="fa fa-calendar mr-1"></i>  
                  {{date('d-M-Y',strtotime($list_date->exam_date))}} 
      
                </a>
                          </div>
                <a class="post-title text-light text-capitalize" href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id)}}">
                {{$list_date->examtitle}}<br>
                    {{$list_date->subject_title}}
                </a>
                
                    <div class="mb-1 mt-2 d-inline-flex">
                        <div class="post-meta mr-2 mt-2">
                        
                            <a  href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id.'/discussion')}}" class="bg-info p-2">
                                <i class="fa fa-comments"></i>  
                              <span>Discussion</span>            
                            </a>
                        </div>
                        <div class="post-meta  mt-2">
                        
                            <a  href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id.'/leaderboard')}}" class="bg-success p-2">
                            <i class="fa fa-bar-chart-o"></i>
                            <span>Leaderboard</span>
                                      
                            </a>
                        </div>
                    </div>
       
                    <a href="{{url('kpsc/daily-exam/'.$list_date->exam_date.'/'.$list_date->id)}}"  class=" mt-4 badge badge-pill badge-primary">Attend for exam</a>
                    
            </div>
          </div>
          {!!small_ads_space_sport($key)!!}
          @endforeach
          
     
      </div>
      
    </div>
            <!-- Ads -->
            {!! single_ads_show() !!}
             <!-- Ads -->
            {!! single_ads_show() !!}
  </div>
<!-- Modal -->
<div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
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
                <li>
                  ലോഗിൻ ചെയ്തതിനുശേഷം മാത്രമേ പരീക്ഷ എഴുതാൻ സാധിക്കുകയുള്ളൂ
                </li>
                 <li>
                    രജിസ്റ്റർ ചെയുന്ന Photo,Name ആയിരിക്കും Leaderboard യിൽ ഉണ്ടാവുക 
                </li>
                <li>
                  പരീക്ഷ ദൈർഘ്യം :30 മിനിറ്റ്
                </li>
                <li>
                  എത്ര പ്രാവശ്യം വേണമെങ്കിലും പരീക്ഷയെഴുതാം.. പക്ഷേ ആദ്യ പ്രാവശ്യം എഴുതിയത് മാത്രമേ ലീഡർ ബോർഡിൽ കണക്കാക്കുകയുള്ളൂ
                </li>
                <li>
                    ഓരോ ശരിയായ ചോദ്യങ്ങൾക്കും ഓരോ മാർക്ക്
                </li>
                <li>
                    ഓരോ തെറ്റിത്തരങ്ങൾക്കും. 33 കുറയ്ക്കുന്നതാണ്
                </li>
                <li>
                    Next അടിച്ചു പോയതിനുശേഷം previous  Question ലേക്ക് തിരിച്ചു പോവാൻ സാധിക്കില്ല 
                </li>
                <li>
                    Question choose ചെയ്യാതെ  next അടിച്ചാൽ mark നഷ്ടം ആവില്ല 
                </li>
                <li>
                    30 മിനിറ്റിനുള്ളിൽ അറ്റൻഡ് ചെയ്യുന്ന ആളുകൾ മാത്രമേ ലീഡർ ബോർഡിൽ വരികയുള്ളൂ
                </li>
                <li>
                    Exam time കഴിയുമ്പോൾ leaderboard ഉം exam discussion chat box ഉം ഉണ്ടായിരിക്കും 
                </li>
                <li>
                    പരീക്ഷയ്ക്കുശേഷം സംശയങ്ങൾ ക്ലിയർ ചെയ്യാനുള്ള ചാറ്റ് ബോക്സ്
                </li>
                <li>
                    ഒരേ മാർക്ക് ലഭിച്ച ആളുകൾ ഉണ്ടെങ്കിൽ സമയം അനുസരിച്ച് ആയിരിക്കും വിജയിയെ കണ്ടെത്തുക
                </li>
               
            </ol>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection


