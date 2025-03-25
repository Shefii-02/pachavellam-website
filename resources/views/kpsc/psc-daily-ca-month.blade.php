@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-".$month)
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Daily Current Affairs-{{$year}}</h5>
            </div>
        </div>
        <div class="container">
            <div class=" col-lg-12">
            <p class="text-dark mob_text" style="line-height:2;">
                വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക്
                {{$year}} ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!
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
                            
                            @foreach ($daily_ca_month->Sortby('month_number') as $key => $list)
                                <div class="col-12 col-md-3 text-center">
                                    <a href="{{url('/kpsc/psc-daily-current-affairs/'.Str::slug($year).'/'.Str::slug($list->month))}}" class="btn-btn-info  text-light">
                                        <div class="card text-white {{div_background()}}  min-height-100  bg1 mb-3 w-100" >
                                        <h3 class="card-body text-light h5 card-title mt-4">{{$list->month}}</h3>
                                        
                                        </div>
                                    </a>
                                </div>
                                
                                   {!! ads_space_sport($key) !!}
                                
                                
                            @endforeach
                            
                            
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </div>
@endsection