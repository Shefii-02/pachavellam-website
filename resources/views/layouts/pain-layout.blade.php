<!DOCTYPE html>
<html lang="en">
    <head>
    <title>Pachavellam Education Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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

	.loader {
	  border: 16px solid #000000;
	  border-radius: 50%;
	  border-top: 16px solid blue;
	  border-right: 16px solid green;
	  border-bottom: 16px solid red;
	  width: 120px;
	  height: 120px;
	  -webkit-animation: spin 2s linear infinite;
	  animation: spin 2s linear infinite;
	}
	
	@-webkit-keyframes spin {
	  0% { -webkit-transform: rotate(0deg); }
	  100% { -webkit-transform: rotate(360deg); }
	}
	
	@keyframes spin {
	  0% { transform: rotate(0deg); }
	  100% { transform: rotate(360deg); }
	}
	</style>    
</head>
    <body>

        <div class="p-2">
            @yield('content')
        </div>
        <div id="wait" style="width:69px;height:89px;position:fixed;z-index:99999;top:50%;left:50%;padding:2px;">
            <div class="loader"></div>
        </div>
    
    <script src="{{ asset('assets/jquery.min.js') }}"></script>
    <script>
            $(document).ready(function() {
                $("#wait").css("display", "none");;
            });
            $(document).ajaxStart(function(){
                $("#wait").css("display", "block");
            });
    
            $(document).ajaxComplete(function(){
                $("#wait").css("display", "none");
            });
    </script>
    </body>
</html>