@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@php $title = "KPSC-Model Exam Answer key"; @endphp
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
    width: 100%;
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
          
            <div class="doc-cont">
                <div class="container-fluid p-0 documents-container d-flex align-items-center">
                    <div class="container m-auto position-relative">
                        <div class="position-absolute doc-actions">
                              <a target="_new" href="{{ url(Storage::url('model-exam/'.$examdetails->answer_file)) }}" class="btn share btn-dark  btn-lg add-doc button-1 text-white ctm-border-radius p-1 btn-sm  ctm-btn-p"> Download </a>
                            <a href="" class="btn share btn-lg  btn-info add-doc button-1 text-white ctm-border-radius p-1 btn-sm  ctm-btn-p"> Refresh Page </a>
                        </div>
                        <iframe src="https://docs.google.com/gview?url={{ url(Storage::url('model-exam/'.$examdetails->answer_file)) }}&embedded=true" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
    
        </div>
      </div>
    </div>
    <div class="col-lg-12">
        <div class="text-center">
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
                 
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-success upload-mark">
             Upload your marks
            </button>
            
            <!-- Modal -->
            <div class="modal fade" id="fromModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Upload your exam mark details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body ">
                      <form class="upload-form" action="{{url('kpsc/model-exam/store-result/'.$examdetails->id)}}" enctype="multipart/form-data" method="POST">
                          
                      </form>
                  </div>
                 
                </div>
              </div>
            </div>     
                 
                 
                 
           
        </div>
    </div>
   


   

    
                
                 
  
  @endsection
    @section('scripts')
                   
     <script>
                setTimeout(function(){
                    $(".preloader").hide();
                }, 10000);


                $('body').on('click', '.upload-mark', function() {
                    $.ajax({
                      type: "GET",
                      url: `{{url('kpsc/model-exam-mark-form')}}`,
                      cache: false,
                      success: function(data){
                         $(".upload-form").html(data);
                         $('#fromModal').modal('show')
                      }
                    });
                });
     </script>
    
@endsection

   
 
    
      
 