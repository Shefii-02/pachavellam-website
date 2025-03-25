@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- New Pattern-")
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">{{$title}}</h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                            <div class=" col-lg-12">
                                <p class="text-dark mob_text" style="line-height:2;">
                                     കേരള  പി എസ് സിയുടെ പുതിയ പാറ്റേൺ പ്രകാരമുള്ള സ്റ്റേറ്റ്മെൻറ്  തരത്തിലുള്ള ചോദ്യങ്ങൾ  ഇനി എല്ലാം  പച്ചവെള്ളം പോലെ.... 
             ഈ സ്റ്റേറ്റ് മെൻറ് ടൈപ്പ് ചോദ്യങ്ങൾ മോക്ക് ടെസ്റ്റിലൂടെ  പരിശീലിക്കാം.  ഈ ക്വിസ് നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
                            </div>
                            
                                
                                <div class='col-lg-12'>
                                    <p>Share This </p>
                                    <div class="social-share ">
                                        <a href="https://wa.me/?text={{url('/kpsc/psc-new-pattern/')}} കേരള  പി എസ് സിയുടെ പുതിയ പാറ്റേൺ പ്രകാരമുള്ള സ്റ്റേറ്റ്മെൻറ്  തരത്തിലുള്ള ചോദ്യങ്ങൾ  ഇനി എല്ലാം  പച്ചവെള്ളം പോലെ.... 
             ഈ സ്റ്റേറ്റ് മെൻറ് ടൈപ്പ് ചോദ്യങ്ങൾ മോക്ക് ടെസ്റ്റിലൂടെ  പരിശീലിക്കാം.  ഈ ക്വിസ് നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക" target='_blank' class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
                                        <a href="https://www.facebook.com/sharer.php?u={{url('/kpsc/psc-new-pattern/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
                                        <a href="https://twitter.com/intent/tweet?text= കേരള  പി എസ് സിയുടെ പുതിയ പാറ്റേൺ പ്രകാരമുള്ള സ്റ്റേറ്റ്മെൻറ്  തരത്തിലുള്ള ചോദ്യങ്ങൾ  ഇനി എല്ലാം  പച്ചവെള്ളം പോലെ.... 
             ഈ സ്റ്റേറ്റ് മെൻറ് ടൈപ്പ് ചോദ്യങ്ങൾ മോക്ക് ടെസ്റ്റിലൂടെ  പരിശീലിക്കാം.  ഈ ക്വിസ് നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക&url={{url('/kpsc/psc-new-pattern/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
                                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{url('/kpsc/psc-new-pattern/')}}&title= കേരള  പി എസ് സിയുടെ പുതിയ പാറ്റേൺ പ്രകാരമുള്ള സ്റ്റേറ്റ്മെൻറ്  തരത്തിലുള്ള ചോദ്യങ്ങൾ  ഇനി എല്ലാം  പച്ചവെള്ളം പോലെ.... 
             ഈ സ്റ്റേറ്റ് മെൻറ് ടൈപ്പ് ചോദ്യങ്ങൾ മോക്ക് ടെസ്റ്റിലൂടെ  പരിശീലിക്കാം.  ഈ ക്വിസ് നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക&summary=&source={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
                                    </div><br>
                                    
                                </div>
                                <div class="col-lg-4">
                                    <a href="{{url('kpsc/psc-new-pattern/'.$first)}}" class="btn-btn-info text-light">
                                        <div class="card text-white   {{div_background()}}  mb-3 w-100" >
                                        <div class="card-header text-center">Let's Start</div>
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-light">
                                                <a class="text-light" href="{{url('kpsc/psc-new-pattern/'.$first)}}">
                                                   <u >Start</u>
                                                </a>
                                            </h5>
                                        </div>
                                        </div>
                                    </a>
                                </div>  
                                <div class="col-lg-4">
                                    <a href="{{url('kpsc/psc-new-pattern/'.$latest)}}" class="btn-btn-info text-light">
                                        <div class="card text-white    {{div_background()}}  mb-3 w-100" >
                                        <div class="card-header text-center">Newly Added Questions</div>
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-light">
                                                <a class="text-light" href="{{url('kpsc/psc-new-pattern/'.$latest)}}">
                                                   <u >Start</u>
                                                </a>
                                            </h5>
                                        </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-4">
                                    <a href="{{url('kpsc/psc-new-pattern/all')}}" class="btn-btn-info text-light">
                                        <div class="card text-white   {{div_background()}}  mb-3 w-100" >
                                        <div class="card-header text-center">All Questions</div>
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-light">
                                                <a class="text-light" href="{{url('kpsc/psc-new-pattern/all')}}">
                                                   <u >Open</u>
                                                </a>
                                            </h5>
                                        </div>
                                        </div>
                                    </a>
                                </div>
                                
                                
                                
                            
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
            {!! bottom_double_ads() !!}
        </div>
    </div>
  </div>
@endsection