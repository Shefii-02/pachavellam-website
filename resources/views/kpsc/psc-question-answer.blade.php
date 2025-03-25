@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Question-Answer")
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title text-capitalize">{{ $title }}</h5>
            </div>
        </div>
        <div class="container">
            <div class=" col-lg-12">
                                <p class="text-dark mob_text" style="line-height:2;">
                                എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!
                                 നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
                            </div>
                            
                                
                                <div class='col-lg-12'>
                                    <p>Share This </p>
                                    <div class="social-share ">
                                        <a href="https://wa.me/?text={{url('/kpsc/prev-qstn-ans/')}} 
                                        എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!
             " target='_blank' class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
                                        <a href="https://www.facebook.com/sharer.php?u={{url('/kpsc/prev-qstn-ans/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
                                        <a href="https://twitter.com/intent/tweet?text= 
                                        എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!
             &url={{url('/kpsc/prev-qstn-ans/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
                                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{url('/kpsc/prev-qstn-ans/')}}&title= 
                                        എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!
             &summary=&source={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
                                    </div><br>
                                    
                                </div>
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                            
                            @foreach ($prev_type as $list)
                                <div class="col-lg-4">
                                    <a href="{{url('kpsc/prev-qstn-ans/'.Str::slug($list->category))}}" class="btn-btn-info text-light">
                                        <div class="card text-white  {{div_background()}} mb-3  text-center" style="max-width: 100%;">
                                        <div class="card-header">{{$list->category}}</div>
                                        <div class="card-body">
                                            <h6 class="card-title text-light align-self-center">
                                                <a class="text-light" href="{{url('kpsc/prev-qstn-ans/'.Str::slug($list->category))}}">
                                                   Open
                                                </a>
                                            </h5>
                                        </div>
                                        </div>
                                    </a>
                                </div>  
                            @endforeach
                           
                            
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
            {!! bottom_double_ads() !!}
        </div>
    </div>
  </div>
@endsection