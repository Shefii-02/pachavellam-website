<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pachavellam Education :: @yield('title')</title>
    <link rel="icon" href="{{url('assets/images/favicon.png')}}" type="image/png" sizes="20x20"/>
    <!-- Favicon -->
	<link rel="shortcut icon" href="{{url('assets/images/favicon.png')}}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta property="og:image" content="{{url('assets/images/favicon.png')}}" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:width" content="400" />
    <meta property="og:image:height" content="400" /> 
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">   
    <meta charset="utf-8">
    <meta name="author" content="pachavellam.com">
    <meta charset="UTF-8" />
    <meta name="viewport"  content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description"
         content="മത്സര പരീക്ഷകൾക്ക് തയ്യാറെടുക്കുന്ന ഉദ്യോഗാർത്ഥികൾക്ക് പഠിച്ചിരിക്കേണ്ട അറിവുകൾ, ആവശ്യമായ നിർദ്ദേശങ്ങൾ, വാർത്തകൾ, സിലബസുകൾ ഇതെല്ലാം നിങ്ങൾക്കിനി പച്ചവെള്ളംപോലെ.." />
     <meta name="keywords"  content="Top Education,PSC Class Free,Free Class,Psc Class " />
     <meta name="p:domain_verify" content="598bc9f4edd54f0eba823b41efe4ac49"/>
     <link rel="canonical" href="https://www.pachavellam.com/" />
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- Stylesheet-->
    <link rel="stylesheet" href="/psc/style.css?v=1.1.3">
     <link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="/mobile2.css?v=1.1" />
    @yield('styles')
    <style>
      /* width */
		::-webkit-scrollbar {
			width: 5px;
		}
		
		/* Track */
		::-webkit-scrollbar-track {
			box-shadow: inset 0 0 5px grey; 
			border-radius: 10px;
		}
		
		/* Handle */
		::-webkit-scrollbar-thumb {
			background-color: #FFF;
			background-image: -webkit-linear-gradient(top,
														#e4f5fc 0%,
														#bfe8f9 50%,
														#9fd8ef 51%,
														#2aed75 100%);
			border-radius: 10px;
		}
		
		/* Handle on hover */
		::-webkit-scrollbar-thumb:hover {
			background: #0089b3; 
		}
        .min-height-100{
          min-height: 100px
        }
        
        .social-share a {
                padding: 0 .2em;
            }
        .linked-list{
            color: #0e76a8;
        }
        .twitter{
            color: #00acee;
        }
    
    </style>
     
        <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TBNJK6JTCL"></script>
    <script async  src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
         crossorigin="anonymous" onerror="adLoadFailed();"></script>
   
    <!--     <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>-->

    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'G-TBNJK6JTCL');
    </script>
    
  
  </head>
  <body oncopy="return false" oncut="return false"  onselectstart="return false">
    <div class="header-area " id="headerArea">
    <div class="container h-100 mt-2 mb-3 d-flex align-items-center justify-content-between">
     
      <!-- Logo-->
      <div class="logo-wrapper"><a href="/"><img src="{{url('images/logo.png')}}" alt=""></a></div>
      <!-- Search Form-->
      <div class="search-form">
      
      </div>
    </div>
  </div>
    @yield('header-tab')



    @yield('content')
    
    
    <div id="mobileB">  
          <!-- mobile-bottom-ads -->
        <ins class="adsbygoogle"
             style="display:inline-block;width:320px;height:50px"
             data-ad-client="ca-pub-2428477024574809"
             data-ad-slot="9817390780"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
  
    <!-- Footer Nav-->
    <div class="footer-nav-area" id="footerNav">
        <div class="newsten-footer-nav h-100">
          <ul class="h-100 d-flex align-items-center justify-content-between">
            <li class="active"><a href="{{url('/')}}"><i class="lni lni-home text-dark"></i>  <small>Home</small></a></li>
            <li><a href="{{url('kpsc')}}"><i class="lni lni-heart text-dark"></i><small>Kerala Psc</small></a> </li>
            
            <li><a href="{{url('ug-pg')}}"><i class="lni lni-book text-dark"></i> <small>Degree Materials</small></a> </li>
          
            
          </ul>
        </div>
    </div>
     
      
  
      
       <!-- Modal -->
  <div class="modal fade" id="MyModal" tabindex="-1" role="dialog" aria-labelledby="MyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <i data-dismiss="modal" class="fa fa-times text-danger" style="
                    font-size: 39px;
                    position: absolute;
                    font-weight: 800;
                    left: 90%;
                "></i>
                <img src="/images/roaring-40.jpeg" class="img-responsive">
            </div>
        </div>
    </div>
</div>



      <!-- All JavaScript Files-->
      <script src="/psc/js/jquery.min.js"></script>
      <script src="/psc/js/popper.min.js"></script>
      <!--<script src="/psc/js/bootstrap.min.js"></script>-->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  </body>
      <script src="/psc/js/waypoints.min.js"></script>
      <script src="/psc/js/jquery.easing.min.js"></script>
      <script src="/psc/js/owl.carousel.min.js"></script>
      <script src="/psc/js/jquery.animatedheadline.min.js"></script>
      <script src="/psc/js/jquery.counterup.min.js"></script>
      <script src="/psc/js/wow.min.js"></script>
      <script src="/psc/js/default/date-clock.js"></script>
      <script src="/psc/js/default/dark-mode-switch.js"></script>
      <script src="/psc/js/default/active.js?v=1.1.2"></script>
      <script>
        $.ajaxSetup({

            headers: {
            
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            
            }
        
        });
       
          
      </script>


  
      @yield('scripts')



    </body>
  
  </html>