@section('styles')
<style>

		.notif-badge {
            width: 10px;
            height: 10px;
            background: #d6293e;
            border-radius: 50%;
            position: absolute;
            top: -1px;
            right: -1px;
            z-index: 1;
        }
        .animation-blink {
            -webkit-animation: blink 2s infinite;
            animation: blink 2s infinite;
        }
</style>
@endsection
@section('header-tab')

 <!-- Header Area-->
 <div class="header-area " id="headerArea">
    <div class="container h-100 mt-2 mb-3 d-flex align-items-center justify-content-between">
     <div class="navbar--toggler text-end" id="newstenNavbarToggler">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
      </div>
      <!-- Logo-->
      <div class="logo-wrapper"><a href="/"><img src="{{url('images/logo.png')}}" alt=""></a></div> <!-- Navbar Toggler-->
      
      <!-- Search Form-->
      <div class="search-form">
      <a class="bg-white" hreff="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
							<i class="lni lni-alarm  fa-2x"></i>
						</a>
          <span class="notif-badge animation-blink"></span>
      </div>
    </div>
  </div>
  <!-- Sidenav Black Overlay-->
  <div class="sidenav-black-overlay"></div>
  <!-- Side Nav Wrapper-->
  <div class="sidenav-wrapper" id="sidenavWrapper">
 
    <!-- Sidenav Nav-->
    <ul class="sidenav-nav">
        <li><a href="{{url('/')}}"><i class="lni lni-arrow-left text-dark"></i> Home</a></li> 
      <li><a href="{{url('kpsc/home')}}"><i class="lni lni-home text-dark"></i>Kpsc Home</a></li> 
       <!--<li><a href="{{url('kpsc/daily-bucket')}}"><i class="lni lni-home text-dark"></i>Roaring 40's</a></li>-->
    
      <li><a href="{{url('kpsc/daily-exam')}}"><i class="lni lni-question-circle text-dark"></i>Daily Exam</a></li>
      <!--<li><a href="{{url('kpsc/explore?type=free-class')}}"><i class="lni lni-star-filled text-warning"></i>Free Class<span class="ml-2 badge badge-warning">{{$category_free->count()}} +</span></a></li>-->
      <!--<li><a href="{{url('kpsc/explore?type=paid-class')}}"><i class="lni lni-star-filled text-success"></i>Paid Class<span class="ml-2 badge badge-success">{{$category_paid->count()}} +</span></a></li>  -->
      <li><a href="{{url('kpsc/psc-new-pattern')}}"><i class="lni lni-files text-dark"></i>New Model Q&A<span class="ml-2 badge badge-danger">New</span></a></li>
      <!--<li><a href="{{url('kpsc/psc-daily-current-affairs')}}"><i class="lni lni-add-files text-dark"></i>Daily C A <span class="red-circle ml-2 flashing-effect"></span></a></li>-->
      <!--<li><a href="{{url('kpsc/feed')}}"><i class="lni lni-gallery"></i>Feed</a></li>-->
      <!--<li><a href="{{url('kpsc/psc-special-videos')}}"><i class="lni lni-video text-dark"></i>Special Videos</a></li>-->
      <li><a href="{{url('kpsc/psc-our-videos')}}"><i class="lni lni-video text-dark"></i>Our Videos</a></li>
      <li><a href="{{url('kpsc/psc-syllabus')}}"><i class="lni lni-files text-dark"></i>Syllabus</a></li>
      <!--<li><a href="{{url('kpsc/psc-material')}}"><i class="lni lni-bolt text-dark"></i>Material</a></li>-->
      <li><a href="{{url('kpsc/psc-bulletin')}}"><i class="lni lni-book text-dark"></i>Bulletin</a></li>
      <li><a href="{{url('kpsc/prev-qstn-ans')}}"><i class="lni lni-folder text-dark"></i>Prev Qstn & Ans</a></li>
      <li><a href="{{url('kpsc/time-table')}}"><i class="lni lni-target text-dark"></i>Time Table</a></li>
      <li><a href="{{url('kpsc/psc-results')}}"><i class="lni lni-book text-dark"></i>Results</a></li>
      <!--<li><a href="{{url('kpsc/notify')}}"><i class="lni lni-alarm text-dark"></i>Notify</a></li>-->
      <li><a href="{{url('kpsc/news-all')}}"><i class="lni lni-paperclip text-dark"></i>News</a></li>
      
      {{-- <li><a href="{{url('kpsc/live-chat')}}"><i class="lni lni-wechat"></i>Live Chat <span class="green-circle ml-2 flashing-effect"></span></a></li>
      <li><a href="{{url('kpsc/about-us')}}"><i class="lni lni-android-original"></i>About us</a></li>
      <li><a href="{{url('info/terms-and-conditions')}}"><i class="lni lni-files"></i>Terms & Conditions</a></li>
      <li><a href="{{url('info/privacy-policy')}}"><i class="lni lni-files"></i>Privacy Policy</a></li> --}}
      <!--<li><a href="{{url('sign-out')}}"><i class="lni lni-lock-alt"></i>Logout</a></li>-->

    </ul>
    <!-- Go Back Button-->
    <div class="go-home-btn" id="goHomeBtn"><i class="lni ">X</i></div>
  </div>
    
      <!-- Notification -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
        
            <div class="col-lg-12">
                <div style="padding-top:0px;height:50px;text-align:center;background-image:url({{url('images/notice_top.gif')}}); background-size: 100% 100%; background-repeat:no-repeat">
                        <div style="background: url({{url('images/col_box_title.gif')}}) no-repeat center bottom;padding: 12px 5px 4px;color: rgb(12, 84, 124);background-image:url({{url('images/notice_title.gif')}});background-repeat:no-repeat;font-weight: normal;text-align: center;font-family: Georgia, Arial, Helvetica, sans-serif;font-size: 19px;line-height: 35px;">
                            Latest Updates
                        </div>
                </div>
                <div class="news" style="background-size: 100% 100%; background-image:url({{url('images/notice_fill_content.gif')}});background-repeat:repeat-y">
                        <!--DWLayoutTable-->                                 
                        <div style="overflow:auto;padding-left:0px;height:75vh;font-size:11px; font-family: verdana,Helvetica, sans-serif;width: 95%;background-color: white;margin-left: 5px;" align="left" behavior="scroll" direction="up" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="2" scrolldelay="1">
                                <ul style="margin-left:0px;padding-left:25px;padding-right:20px">
                                  @foreach ($noftify as $notification_list)
                                    <li style="padding: 20px 0px 7px 0px;border-bottom: 1px dotted grey;border-top: none;">
                                      <span class="ml-2 badge badge-danger">New</span> <a href="{{$notification_list->redirection}}">
                                          {{$notification_list->title}} 
                                        </a>
                                    </li>
                                  @endforeach  
                                                                          
                                </ul>
                            </div>
                          <div style="width:100%;height:11px;margin-left: 5px;text-align:left;"></div>
    
                          <div style="background-size: 100% 100%; background-image:url('{{url('images/notice_bottom.gif')}}');"></div>
                  </div>
            </div>
        </div>
    </div>
        
@endsection