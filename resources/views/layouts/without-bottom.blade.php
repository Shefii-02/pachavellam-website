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
   
    <!-- Stylesheet-->
    <link rel="stylesheet" href="/psc/style.css?v=1.1.1">
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
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
         crossorigin="anonymous"></script>
   
    <!--     <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>-->

    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'G-TBNJK6JTCL');
    </script>
    
  </head>
  <body oncopy="return false" oncut="return false"  onselectstart="return false">
    
    @yield('header-tab')

   
    @yield('content')
 
    
      <!-- All JavaScript Files-->
      <script src="/psc/js/jquery.min.js"></script>
      <script src="/psc/js/popper.min.js"></script>
      <script src="/psc/js/bootstrap.min.js"></script>
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
            @stack('custom-scripts')
    </body>
  
  </html>