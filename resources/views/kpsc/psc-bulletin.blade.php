@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Bulletin-ALL")
@section('content')
@section('styles')
<style>
.lds-spinner {
  color: official;
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.lds-spinner div {
  transform-origin: 40px 40px;
  animation: lds-spinner 1.2s linear infinite;
}
.lds-spinner div:after {
  content: " ";
  display: block;
  position: absolute;
  top: 3px;
  left: 37px;
  width: 6px;
  height: 18px;
  border-radius: 20%;
  background: #dfc;
}
.lds-spinner div:nth-child(1) {
  transform: rotate(0deg);
  animation-delay: -1.1s;
}
.lds-spinner div:nth-child(2) {
  transform: rotate(30deg);
  animation-delay: -1s;
}
.lds-spinner div:nth-child(3) {
  transform: rotate(60deg);
  animation-delay: -0.9s;
}
.lds-spinner div:nth-child(4) {
  transform: rotate(90deg);
  animation-delay: -0.8s;
}
.lds-spinner div:nth-child(5) {
  transform: rotate(120deg);
  animation-delay: -0.7s;
}
.lds-spinner div:nth-child(6) {
  transform: rotate(150deg);
  animation-delay: -0.6s;
}
.lds-spinner div:nth-child(7) {
  transform: rotate(180deg);
  animation-delay: -0.5s;
}
.lds-spinner div:nth-child(8) {
  transform: rotate(210deg);
  animation-delay: -0.4s;
}
.lds-spinner div:nth-child(9) {
  transform: rotate(240deg);
  animation-delay: -0.3s;
}
.lds-spinner div:nth-child(10) {
  transform: rotate(270deg);
  animation-delay: -0.2s;
}
.lds-spinner div:nth-child(11) {
  transform: rotate(300deg);
  animation-delay: -0.1s;
}
.lds-spinner div:nth-child(12) {
  transform: rotate(330deg);
  animation-delay: 0s;
}
@keyframes lds-spinner {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}
    label{
       cursor:pointer;
    }
    .section-datewaise{
        min-height:100px;
        border-radius:9px;
        background-color:#ffffff;
        margin:5px;
    }
    .label-date{
        margin-top:3px;
     float: left;   
    letter-spacing: .05em;
    border-radius: 60px;
    padding: 4px 12px 3px;
    font-weight: 500;
    background-color: #77ab09b8;
    color:white;
    font-size:10px;
    }
    .label-time{
        margin-top:3px;
     float: right;   
    letter-spacing: .05em;
    border-radius: 60px;
    padding: 4px 12px 3px;
    font-weight: 500;
    background-color: #007bffc2;
    color:white;
    font-size:10px;
    }
    .label-bottom{
     margin-top:3px;
     text-align:center;
    letter-spacing: .05em;
    border-radius: 60px;
    padding: 4px 12px 3px;
    font-weight: 500;
   
    color:white;
    font-size:10px;
    }
    .sec-date-text{
        font-size:14px;
        letter-spacing: .05em;
        line-height:1.2;
        margin-left:5px;
    }
    .fa{
        margin-top:3px;
    }
    .bg-green{
         background-color: #77ab09b8;
    }
    .bg-pacha-area {
    background-image: url(https://www.pachavellam.com/assets/images/bg-pacha-area.jpg);
    background-position: center center;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    position: relative;
    z-index: 1;
}
</style>
@endsection
<div class="page-content-wrapper bg-pacha-area">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper" style="min-height: 100vh">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title"> </h5>
        </div>
      </div>
      <div class="container">
          <div class=" col-lg-12">
                <p class="text-dark mob_text" style="line-height:2;">
                    മത്സര പരീക്ഷകളിലെ സുപ്രധാന ഭാഗമാണ് പിഎസ്സി ബുള്ളറ്റിൻ.. ഓരോ മാസത്തേയും ബുള്ളറ്റിൻ ഫ്രീ ആയി ഇവിടെ ലഭിക്കും.
                    <br>
                 നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
            </div>
            
                
                <div class='col-lg-12'>
                    <p>Share This </p>
                    <div class="social-share ">
                        <a href="https://wa.me/?text={{url()->current()}} 
                        മത്സര പരീക്ഷകളിലെ സുപ്രധാന ഭാഗമാണ് പിഎസ്സി ബുള്ളറ്റിൻ.. ഓരോ മാസത്തേയും ബുള്ളറ്റിൻ ഫ്രീ ആയി ഇവിടെ ലഭിക്കും.
target='_blank' class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
                        <a href="https://www.facebook.com/sharer.php?u={{url()->current()}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
                        <a href="https://twitter.com/intent/tweet?text= 
                        മത്സര പരീക്ഷകളിലെ സുപ്രധാന ഭാഗമാണ് പിഎസ്സി ബുള്ളറ്റിൻ.. ഓരോ മാസത്തേയും ബുള്ളറ്റിൻ ഫ്രീ ആയി ഇവിടെ ലഭിക്കും.
                        &url={{url()->current()}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{url()->current()}}&title= 
                        മത്സര പരീക്ഷകളിലെ സുപ്രധാന ഭാഗമാണ് പിഎസ്സി ബുള്ളറ്റിൻ.. ഓരോ മാസത്തേയും ബുള്ളറ്റിൻ ഫ്രീ ആയി ഇവിടെ ലഭിക്കും.
&summary=&source={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
                    </div><br>
                    
                </div>
                
        <div class="section section--slider-mobile">
            <div class="container">
                <div class="row ">
                    
                    <div class="col-lg-12">
                     
                         <div id="results">
                         
                         </div>
                       
                    </div> 
                    <div class="col-lg-12 ajax-loading text-center">
                        <div class="lds-spinner">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="container mb-5">
                      <div class="text-center mt-3"><a class="btn btn-primary load_more" href="#">Load More</a></div>
                    </div>
                    
                </div>
            </div>
        </div>
           
      </div>
       
    </div>
  </div>
@endsection


@section('scripts')
<script>
   var SITEURL = "{{ url('/') }}";
   var page = 1; //track user scroll as page number, right now page number is 1
   load_more(page); //initial content load

    $('body').on('click','.load_more',function(e){
        e.preventDefault()
             page++; //page number increment
      load_more(page); //load content  
    });
    
    $('body').on('click','.dwn_c',function(e){
        var dwn_id = $(this).data('id');
        $.ajax({
          url: "{{url('kpsc/psc-bulletin-downloading')}}",
          data: {dwn_id : dwn_id},
          cache: false,
          success: function(html){
              
          }
        });

    });
    
    
    
    
    function load_more(page){
        $.ajax({
           url: SITEURL + "/kpsc/psc-bulletin?page=" + page,
           type: "get",
           datatype: "html",
           beforeSend: function()
           {
              $('.ajax-loading').show();
            }
        })
        .done(function(data)
        {
           
            if(data.length == 0){
            console.log(data.length);
            //notify user if nothing to load
            $('.ajax-loading').html("No more records!");
            return;
          }
          $('.ajax-loading').hide(); //hide loading animation once data is received
          $("#results").append(data); //append data into #results element          
           console.log('data.length');
       })
       .fail(function(jqXHR, ajaxOptions, thrownError)
       {
          alert('No response from server');
       });
       
    }
</script>

@endsection
