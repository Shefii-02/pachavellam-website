@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@php $title = "KPSC-Model Exam"; @endphp
@section('title', $title ?? "KPSC-Model Exam" )
 @section('styles')
    	<style>
	 	    .container-fluid.documents-container {
    background: #d1d1d1;
    /*position: fixed;*/
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}i.fa.fa-fw.close-docs {
    position: absolute;
    top: 0;
    right: 0;
    color: #ffff;
    z-index: 99999;
    font-size: xxx-large;
}
iframe {
    width: 89vw;
    height: 100vh;
}.d-flex.align-items-center.w-100 {
    height: 100vh;
}.ndfHFb-c4YZDc-Wrql6b {
    display: none;
}div#carouselExampleControls a {
    height: 50%;
    top: 50%;
    transform: translate(0%, -50%);
}.position-absolute.doc-actions {
    position: absolute;
    top: 10px;
    right: 15px;
}
	</style>
    @endsection
@section('content')
   
    <div class="preloader" id="preloader" style="display:none">
        <div class="spinner-grow text-secondary" role="status">
            <div class="sr-only">Loading...</div>
        </div>
    </div>
   
    <div class="page-content-wrapper">
    @php
            date_default_timezone_set("Asia/Kolkata"); 
    @endphp
          
   
      
    <div class="trending-news-wrapper">
      
        <div class="container">
            <h4>
                Exam : <span class="text-info"> {{$examdetails->examtitle}}</span>
            </h4>
            <h6 class="d-none">
                Exam Stared at : <span class="text-info"> {{date('d-m-Y h:i a',strtotime($examdetails->started_at))}}</span>
            </h6>
             <h6 class="d-none">
                Exam End at : 
                <span class="text-danger"> {{date('d-m-Y h:i a',strtotime($examdetails->ended_at))}}   
                @if(date('Y-m-d H:i:s', strtotime($examdetails->ended_at))  > date('Y-m-d H:i:s'))
                       <div class=" col-4 d-flex justify-content-center">
                           <div class="base-timer">
                          <span id="hours_timer" class="base-timer__label hour">0h</span>
                        
                          <span id="mins" class="base-timer__label minutes">0m</span>
                        
                          <span id="seconds" class="base-timer__label seconds">0s</span>
                        </div>
                       </div>
                 @endif
                </span>
            </h6>
            <h6>
                Exam Instruction : <br><span class="text-warning">  Read the Instructions Carefully </span>
            </h6>
            <ul>
                        <li> ✅ പരീക്ഷയുടെ ദൈർഘ്യം ഒരു മണിക്കൂർ 15 മിനിറ്റ്</li>
    
               <li> ✅ഓരോ ശരിയുത്തരങ്ങൾക്കും ഓരോ മാർക്ക് വീതം</li>
                
               <li> ✅ ഉത്തരം തെറ്റിയാൽ 1/3 നെഗറ്റീവ് മാർക്ക് ഉണ്ടായിരിക്കുന്നതാണ്</li>
                
               <li> ✅ അറ്റൻഡ് ചെയ്യാത്ത ചോദ്യങ്ങൾക്ക് മാർക്ക് നഷ്ടപ്പെടുന്നതല്ല</li>
                
               <li> ✅ ഇംഗ്ലീഷ് മലയാളം മാത്തമാറ്റിക് ജികെ എന്നീ വിഭാഗങ്ങളുടെ മാർക്ക് എടുത്ത് OMR ഷീറ്റിൽ എഴുതേണ്ടതാണ്</li>
                
                <li>✅ പരീക്ഷയെഴുതിയ ശേഷം നിങ്ങൾക്ക് ലഭിച്ച ആകെ ശരിയുത്തരങ്ങൾ, എത്ര ചോദ്യമാണ് തെറ്റിയത് ന്നിവ ഇവിടെ അപ്‌ലോഡ് ചെയ്യുക</li>
                
                <li>✅ നിശ്ചിത സമയത്തിനുള്ളിൽ ലീഡർ ബോർഡ് നിങ്ങൾക്ക് ലഭ്യമാകുന്നതാണ്</li>
               
            </ul>
            
                                 
            <div class="doc-cont">
                <div class="container-fluid p-0 documents-container d-flex align-items-center">
                    <div class="container m-auto position-relative">
                        <div class="position-absolute doc-actions">
                            
                            <a  target="_new" href="{{ url(Storage::url('model-exam/'.$examdetails->qp_file)) }}" class="btn share btn-dark btn-lg add-doc button-1 text-white ctm-border-radius p-1 btn-sm  ctm-btn-p"> Download </a>
                            <a href="" class="btn share btn-info  btn-lg add-doc button-1 text-white ctm-border-radius p-1 btn-sm  ctm-btn-p"> Refresh Page </a>
                            
                        </div>
                        <iframe src="https://docs.google.com/gview?url={{ url(Storage::url('model-exam/'.$examdetails->qp_file)) }}&embedded=true" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
    
        </div>
      </div>
    </div>
   <div class="container">
      
        <div id="mobileB1">  
          <!-- mobile-bottom-ads -->
        <ins class="adsbygoogle"
             style="display:inline-block;width:320px;height:50px"
             data-ad-client="ca-pub-2428477024574809"
             data-ad-slot="9817390780"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
   </div>


   

                {!! single_ads_show() !!}
                 
                 
  
  @endsection
    @section('scripts')
                   
     <script>
                setTimeout(function(){
                    $(".preloader").hide();
                }, 10000);

     </script>
    
@endsection
