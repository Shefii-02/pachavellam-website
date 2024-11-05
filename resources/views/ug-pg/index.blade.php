@extends('layouts.ug-pg-app')
@extends('ug-pg.section')
@section('title', $title ?? 'University-Exam-Helper')
@section('content')

<style>
  .h8{
    font-size: 15px !important;
  }
  .card-shadow{
    box-shadow: 0 12px 28px 0 rgba(0, 0, 0, 0.2), 0 2px 4px 0 rgba(0, 0, 0, 0.1), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    min-height: 85px;
    display: flex;
    border-radius: 9px;
  }
  .card-shadow:hover {
    transform: scale(1.1);
    box-shadow: 0px 10px 20px 2px rgb(164 205 79 / 95%);
  }
  .whats-new-img{
    margin-left: 5px;
    
  }
  .whats-new-img img{
    object-fit: cover;
  }

</style>

  <div class="page-content-wrapper">

      <!-- News Today Wrapper-->
      <div class="news-today-wrapper">
        <div class="container">
         
          <!-- Hero Slides-->
          <!-- Hero Slides-->
          <div class="hero-slides owl-carousel">
            @foreach ($banner as $banner_list)
              <!-- Single Hero Slide-->
              <a href="#">
                <div class="single-hero-slide" style="background-image: url('{{ Storage::url($banner_list->image) }}')">
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
      
            <div class="container">
                <div class="row">
                
                    <div class="col-lg-6">
          <div class="d-flex justify-content-center">
  
            <div class="col-lg-12">
                <div style="padding-top:0px;height:50px;text-align:center;background-image:url({{url('images/notice_top.gif')}}); background-size: 100% 100%; background-repeat:no-repeat">
                        <div style="background: url({{url('images/col_box_title.gif')}}) no-repeat center bottom;padding: 12px 5px 4px;color: rgb(12, 84, 124);background-image:url({{url('images/notice_title.gif')}});background-repeat:no-repeat;font-weight: normal;text-align: center;font-family: Georgia, Arial, Helvetica, sans-serif;font-size: 19px;line-height: 35px;">
                            Latest Updates
                        </div>
                </div>
                <div class="news" style="background-size: 100% 100%; background-image:url({{url('images/notice_fill_content.gif')}});background-repeat:repeat-y">
                        <!--DWLayoutTable-->                                 
                        <div style="overflow:auto;padding-left:0px;height:300px;font-size:11px; font-family: verdana,Helvetica, sans-serif;width: 95%;background-color: white;margin-left: 5px;" align="left" behavior="scroll" direction="up" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="2" scrolldelay="1">
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
                    <div class="col-lg-6">
                        <div class="trending-news-wrapper">
                            <div class="container">
                              <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0 pl-1 newsten-title">Join us </h5>
                              </div>
                            </div>
                            <div class="container ">
                                                  <!-- Single Trending Post-->
                                <div class="col-md-12 single-trending-post d-flex" style="background:#346564;color:#ffffff">
                                  <div class="post-thumbnail"> 
                                    <img src="{{url('images/whatsapp-icon.png')}}" alt="6th Semester BA Economics" class="w-100">
                                  </div> 
                                  <div class="post-content">
                                    <a class="post-title" style="color:#ffffff" href="https://chat.whatsapp.com/Lc6P3rVrjRDKuCSnRXfZdU">  
                                     6th Semester BA Economics
                                    </a>
                                  </div>
                                </div>
                                                  <!-- Single Trending Post-->
                                <div class="col-md-12 single-trending-post d-flex" style="background:#436f7a;color:#ffffff">
                                  <div class="post-thumbnail"> 
                                    <img src="{{url('images/whatsapp-icon.png')}}" alt="6th Sem BCA/BSc " class="w-100">
                                  </div> 
                                  <div class="post-content">
                                    <a class="post-title" style="color:#ffffff" href="https://chat.whatsapp.com/Ikj8AkUdhTcILTH6UYT1mc">  
                                      6th Sem BCA/BSc  
                                    </a>
                                  </div>
                                </div>
                                                  <!-- Single Trending Post-->
                                <div class="col-md-12 single-trending-post d-flex" style="background:#3e767a;color:#ffffff">
                                  <div class="post-thumbnail"> 
                                    <img src="{{url('images/whatsapp-icon.png')}}" alt="New Model Q &amp; A;" class="w-100">
                                  </div> 
                                  <div class="post-content">
                                    <a class="post-title" style="color:#ffffff" href="https://chat.whatsapp.com/Lp4YkdJs08rIyU3WAPLQpj">  
                                     6th Bcom/BBA- Pachavellam 
                                    </a>
                                  </div>
                                </div>
                                                   <!--Single Trending Post-->
                                <div class="col-md-12 single-trending-post d-flex" style="background:#407477;color:#ffffff">
                                  <div class="post-thumbnail"> 
                                    <img src="{{url('storage/4qVfKnc6Rls42usrhe5G8voi0ANCJnwIMmwEY8t2.png')}}" alt="Unlimited Poll Questions" class="w-100">
                                  </div> 
                                  <div class="post-content">
                                    <a class="post-title" target="_new" style="color:#ffffff" href="https://t.me/pachavellamcu">  
                                      Telegram Group    
                                    </a>
                                  </div>
                                </div>
                                              
                            </div>
                          </div>
                    </div>
                </div>
            </div>
      <div class="col-lg-12">
        <div class="container">
          <div class="row">
            
            
            <div class="col-lg-12">
              <!-- Trending News Wrapper-->
              <div class="trending-news-wrapper">
                <div class="container">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 pl-1 newsten-title">What's New</h5>
                  </div>
                </div>
                <div class="container ">
                  
                  <div class="row">
                    <div class="col-md-3 col-12 mb-3">
                      <a href="{{ug_pg('syllabus')}}">
                        <div class="card-shadow d-flex">
                          <div class="whats-new-img align-self-center">
                            <img src="{{url('images/note.png')}}" class="rounded-circle border" width="50px" alt="">
                          </div>
                          <div class="whats-new-content ml-2 align-self-center">
                            <h1 class="h8">Syllabus</h1>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-3 col-12  mb-3">
                      <a href="{{ug_pg('materials')}}">
                        <div class="card-shadow d-flex">
                            <div class="whats-new-img align-self-center">
                              <img src="{{url('images/studying.png')}}" class="rounded-circle border" width="50px" alt="">
                            </div>
                            <div class="whats-new-content ml-2 align-self-center">
                              <h1 class="h8">Materials</h1>
                            </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-3 col-12  mb-3">
                      <a href="{{ug_pg('question-paper')}}">
                        <div class="card-shadow d-flex">
                          <div class="whats-new-img align-self-center">
                            <img src="{{url('images/questionnaire.png')}}" class="rounded-circle border" width="50px" alt="">
                          </div>
                          <div class="whats-new-content ml-2 align-self-center">
                            <h1 class="h8">Question Papers</h1>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-3 col-12  mb-3">
                      <a href="{{ug_pg('request-class')}}">
                        <div class="card-shadow d-flex">
                          <div class="whats-new-img align-self-center">
                            <img src="{{url('assets/images/element/04.svg')}}" class="rounded-circle border" width="50px" alt="">
                          </div>
                          <div class="whats-new-content ml-2 align-self-center">
                            <h1 class="h8">Request Class</h1>
                          </div>
                        </div>
                      </a>
                    </div>
                    
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
            <h4 class="newsten-title text-dark"> </h4>
          </div>
        </div>
        <div class="container">
          <!-- Catagory Slides-->
          <div class="row">
            <div class="col-md-3  col-6">
              <a href="#">
                <div class=" text-center position-relative">
                  <div class="px-4">
                    <!-- Image -->
                    <div class="icon-xxl  mb-3">
                      <img src="/assets/images/element/18.svg" alt="" style="width: 50px;">
                    </div>
                    <!-- Title -->
                    <h5>University</h5>
                  </div>	
                </div>
              </a>
            </div>
            <div class="col-md-3  col-6">
              <a href="#">
                <div class=" text-center position-relative">
                  <div class="px-4">
                    <!-- Image -->
                    <div class="icon-xxl  mb-3">
                      <img src="/assets/images/element/17.svg" alt="" style="width: 33px;">
                    </div>
                    <!-- Title -->
                    <h5>Category</h5>
                  </div>	
                </div>
              </a>
            </div>
            <div class="col-md-3  col-6">
              <a href="#">
                <div class=" text-center position-relative">
                  <div class="px-4">
                    <!-- Image -->
                    <div class="icon-xxl  mb-3">
                      <img src="/assets/images/element/29.svg" alt="" style="width: 70px;">
                    </div>
                    <!-- Title -->
                    <h5>Course</h5>
                  </div>	
                </div>
              </a>
            </div>
            <div class="col-md-3  col-6">
              <a href="#">
                <div class=" text-center position-relative">
                  <div class="px-4">
                    <!-- Image -->
                    <div class="icon-xxl  mb-3">
                      <img src="/assets/images/element/instructor-course.svg" alt="" style="width: 50px;">
                    </div>
                    <!-- Title -->
                    <h5>Subjects</h5>
                  </div>	
                </div>
              </a>
            </div>
           
          </div>
        </div>
      </div>
     
   
      <div class="for-you-news-wrapper">
        <div class="container">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 pl-1 newsten-title">Special Youtube  Video's</h5>
            <a class="btn btn-primary btn-sm" href="{{ug_pg('special-videos')}}">View All</a>
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
            <a class="btn btn-primary btn-sm" href="{{ug_pg('news-all')}}">View All</a>
          </div>
        </div>
        <div class="container">
         
         <!-- Editorial Choice News Slide-->
         <div class="editorial-choice-news-slide owl-carousel">
          @foreach($news_list as $news)
          <!-- Single Slide-->
          <div class="single-editorial-slide ">
            <div class="card catagory-card">
                <a href="{{ug_pg('news/'.$news->id)}}">
              <img src="{{ Storage::url($news->image) }}" alt="">
              <h6 class="text_limit">{{$news->title}}</h6></a>
            </div>
          </div>
          @endforeach
        </div>
        </div>
      </div>
     
      
    </div>
@endsection