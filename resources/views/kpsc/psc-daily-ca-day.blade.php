@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-".$month.'-'.$year)
@section('content')


<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title text-capitalize">Daily Current Affairs-{{$year}}-{{$month}}</h5>
            </div>
        </div>
        <div class="container">
            
            <div class=" col-lg-12">
                                <p class="text-dark mob_text" style="line-height:2;">
                                    വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് {{$year}} ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!
                                    <br>
                                 നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
                            </div>
                            
                                
                                <div class='col-lg-12'>
                                    <p>Share This </p>
                                    <div class="social-share ">
                                        <a href="https://wa.me/?text={{url()->current()}} 
                                        വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് {{$year}} ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!"
             target='_blank' class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
                                        <a href="https://www.facebook.com/sharer.php?u={{url()->current()}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
                                        <a href="https://twitter.com/intent/tweet?text= 
                                        വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് {{$year}} ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!
                                        &url={{url()->current()}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
                                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{url()->current()}}&title= 
                                        വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് {{$year}} ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!
             &summary=&source={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
                                    </div><br>
                                    
                                </div>
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                        @if($daily_ca_day->where('day','<>',null)->count()>0) 
                                @foreach($daily_ca_day->where('day','=',null) as $key => $list)
                                       
                                      <!-- Single Accordian-->
                                      <div class="card">
                                        <div class="card-header p-0" id="heading{{$list->id}}">
                                            <h2 class="btn text-left mb-0 text-capitalize" type="button" >
                                                @if($list->type == 'Pdf')
                                                    <i class="fa fa-file"> </i>  
                                                @else
                                                    <i class="fa fa-video-camera"> </i>  
                                                @endif
                                               <a target="_new" @if($list->type == 'Pdf') download="{!! $list->title !!}.pdf" href="{{ Storage::url('files/'.$list->file_name) }}" @else  href="{!! $list->file_name  !!}" @endif>{!! $list->title !!}</a>
                                            </h2>
                                        </div>
                                      </div>
                                        
                                        {!! ads_space_sport($key) !!}
                                        
                                @endforeach
                                @foreach ($daily_ca_day->where('day','<>',null) as $key => $list)
                                    <div class="col-12 col-md-3 text-center">
                                        
                                        <a href="{{url('kpsc/psc-daily-current-affairs/'.Str::slug($year).'/'.Str::slug($month).'/'.Str::slug($list->day))}}" class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 {{div_background()}} mb-3 w-100" >
                                            <h3 class="card-body text-light h5 card-title mt-4 text-capitalize">{{ucfirst($list->day)}}</h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                                  
                                     {!! ads_space_sport($key) !!}
                                     
                                @endforeach
                            
                        @else
                      
                            <div class="accordion w-100" id="accordionExample">

                                @foreach($daily_ca_day as $key => $list)
                                      <!-- Single Accordian-->
                                      <div class="card">
                                        <div class="card-header p-0" id="heading{{$list->id}}">
                                            <h2 class="btn text-left mb-0 text-capitalize" type="button" >
                                                @if($list->type == 'Pdf')
                                                    <i class="fa fa-file"> </i>  
                                                @else
                                                    <i class="fa fa-video-camera"> </i>  
                                                @endif
                                               <a target="_new" @if($list->type == 'Pdf') download="{!! $list->title !!}.pdf" href="{{ Storage::url('files/'.$list->file_name) }}" @else  href="{!! $list->file_name  !!}" @endif>{!! $list->title !!}</a>
                                            </h2>
                                        </div>
                                      </div>
                                        
                                        {!! ads_space_sport($key) !!}
                                        
                                @endforeach
                            </div>
                        
                        @endif
                            
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </div>
@endsection