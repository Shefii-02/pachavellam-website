@section('header-tab')
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
.btn-round{
  border-radius: 50%;
}

@-webkit-keyframes blinker {
  from {opacity: 1.0;}
  to {opacity: 0.0;}
}
.animation-blink {
	text-decoration: blink;
	-webkit-animation-name: blinker;
	-webkit-animation-duration: 0.6s;
	-webkit-animation-iteration-count:infinite;
	-webkit-animation-timing-function:ease-in-out;
	-webkit-animation-direction: alternate;
}
.dropdown-menu li{
  margin-left: 20px;
  border-bottom: 0.5px solid #b9b9b9!important;
 
}

</style>
 <!-- Header Area-->
 <div class="header-area " id="headerArea">
    <div class="container h-100 mt-2 mb-3 d-flex align-items-center justify-content-between">
      <!-- Navbar Toggler-->
      <div class="navbar--toggler" id="newstenNavbarToggler"><span></span><span></span><span></span><span></span></div>
      <!-- Logo-->
      <div class="logo-wrapper"><a href="/"><img src="{{url('images/logo.png')}}" alt=""></a></div>
      <!-- Search Form-->
      <div class="search-form"></div>
        <!-- Top bar right START -->
				{{-- <div class="ms-xl-auto">
            <!-- notification bell -->
            <div class="dropdown ">
                <!-- Notification button -->
							<a class="btn btn-light btn-round mb-0 data-toggle inbox" data-toggle="dropdown" href="#" role="button"  >
								<i class="lni lni-alarm fa-fw"></i>
                
							<!-- Notification dote -->
							<span class="notif-badge animation-blink"></span>
							</a>
              
             
              <ul class="dropdown-menu ml-3" role="menu">
               
                {{-- <li><a href="#"><span class="label label-default">4:00 AM</span>Favourites Snippet</a>
                </li>
                <li class="divider"></li>
                <li><a href="#"><span class="label label-warning">4:30 AM</span>Email marketing</a>
                </li>
                <li class="divider"></li>
                <li><a href="#"><span class="label label-warning">5:00 AM</span>Subscriber focused email blaaaa 
              blaaadddddddddddd dddddddddddddddddddddddddddddddddd ddblaaaddddddddddddddddddd dddddddddddddddddd ddddddddddd
                              design</a>
                </li>
                <li class="divider"></li>
                <li><a href="#" class="text-center">View All</a>
                </li> --}}
            {{--  </ul>
            </div>

            
					
				</div> --}}
      
    </div>
  </div>
  <!-- Sidenav Black Overlay-->
  <div class="sidenav-black-overlay"></div>
  <!-- Side Nav Wrapper-->
  <div class="sidenav-wrapper" id="sidenavWrapper">
   
    <!-- Sidenav Nav-->
    <ul class="sidenav-nav">
        
        <li><a href="{{url('/')}}"><i class="lni lni-arrow-left-circle"></i> Home</a></li> 
        <li><a href="{{ug_pg('/')}}"><i class="lni lni-home"></i>Ug Pg Home</a></li> 
        <!--<li><a href="{{ug_pg('university')}}"><i class="lni lni-bulb"></i>University</a></li>-->
        <li><a href="{{ug_pg('syllabus')}}"><i class="lni lni-folder"></i>Syllabus</a></li>
        <li><a href="{{ug_pg('materials')}}"><i class="lni lni-book"></i>Materials</a></li>
        <li><a href="{{ug_pg('question-paper')}}"><i class="lni lni-question-circle"></i>Question Papers</a></li>
        <li><a href="{{ug_pg('special-class')}}"><i class="lni lni-files"></i>Special Class<span class="ml-2 badge badge-danger">New</span></a></li>
        <li><a href="{{ug_pg('request-class')}}"><i class="lni lni-add-files"></i>Request Class<span class="red-circle ml-2 flashing-effect"></span></a></li>
        <li><a href="{{ug_pg('notify')}}"><i class="lni lni-alarm"></i>Notification</a></li>
        <li><a href="{{ug_pg('news-all')}}"><i class="lni lni-paperclip"></i>Latest News</a></li>
        <li><a href="{{url('info/terms-and-conditions')}}"><i class="lni lni-files"></i>Terms & Conditions</a></li>
        <li><a href="{{url('info/privacy-policy')}}"><i class="lni lni-files"></i>Privacy Policy</a></li> 
   
    </ul>
    <!-- Go Back Button-->
    <div class="go-home-btn" id="goHomeBtn"><i class="lni lni-arrow-left"></i></div>
  </div>

@endsection