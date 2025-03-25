@php 
    $title = "Login/Signup";
@endphp

@extends('web.layout')
@section('title', $title ?? "KPSC")

@section('styles')

<style>

.frame {
    min-height: 200px;
    /*width: 420px;*/
       background-color:#e9ecefa8;
    margin-left: auto;
    margin-right: auto;
    border-top: solid 1px rgba(255, 255, 255, .5);
    border-radius: 15px;
    box-shadow: 0px 2px 7px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transition: all .5s ease
      text-shadow: 0 0 3px #FF0000;
}
</style>
	<link rel="stylesheet" type="text/css" 
	href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('content')
  <div class="preloader" id="preloader" style="display:none">
      <div class="spinner-grow text-secondary" role="status">
        <div class="sr-only">Loading...</div>
      </div>
    </div>
<div class="page-content-wrapper">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
    	<div class="container">
            <div class="d-flex justify-content-center mt-6">
                <div class="col-lg-4 mb-5">
                    
                  <div class="frame" style="height: 262px">
                    	<!-- Social buttons and divider -->
    							<div class="row">
    								<!-- Divider with text -->
    								<div class="col-12  text-center">
    								    <h2 class="mt-3">Welcome Back</h2>
    									<p class=" text-center text-dark font-weight-bold mt-2">Login with single click</p>
    									<hr>
    								</div>
    
    								<!-- Social btn -->
    								<div class=" d-flex justify-content-center">
    									<a style="    border: 1px solid #e6e6e6;" href="{{ route('auth.google') }}" class="btn btn-light mb-xxl-0">
    										<img src="https://colorlib.com/etc/lf/Login_v11/images/icons/icon-google.png"></i>
    										&nbsp; 
    										Continue with your Email Id
    									</a>
    								</div>
    								
    							{{--	<!-- Social btn -->
    								<div class=" d-flex justify-content-center mt-3">
    									<a style="    border: 1px solid #e6e6e6;"  data-bs-toggle="modal" data-bs-target="#otpModal" href="#" class="btn btn-light mb-xxl-0 p-3">
    										<i class="bi bi-telephone"></i>
    										&nbsp; 
    										Continue on Mobile Number
    									</a>
    								</div>
    								
    								<!-- Social btn -->
    					
    								<div class="col-6 d-grid text-center">
    									<a style="    border: 1px solid #e6e6e6;" href="{{ route('auth.facebook') }}" class="btn text-dark bg-light ">
    										<img width="30px" src="https://images.g2crowd.com/uploads/vendor/image/492/large_detail_0f38b80ca04c245e0c4383c048a59bd2.png"></img>
    										&nbsp; 
    										Facebook
    									</a>
    								</div>--}}
    							</div>
                   
                       
                    </div>
                </div>
           
               
            </div> 
             <!-- Ads -->
            {{-- single_ads_show() --}}
        </div>
    	</div>
    </div>
  
  <!-- Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="text-center">
                        Account verification
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12 mx-auto">
                                    <div class="card">
                                        
                                       <div class="alert alert-danger" id="error" style="display: none;"></div>

                                        <div class="card numberCard">
                                            <div class="card-body">
                                                <div class="alert alert-success" id="sentSuccess" style="display: none;"></div>
                                                <form>
                                                    <label>Enter Phone Number:</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">+91</span>
                                                        <input type="text" autocomplete="off" id="number" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" class="form-control"  placeholder="********">
                                                    </div>
                                                    <div id="recaptcha-container"></div>
                                                    <button type="button" class="btn btn-success  mt-2 text-center phoneSendAuth" >Send Code</button>
                                                </form>
                                            </div>
                                        </div>
                                    
                                        <div class="card codeCard" style=" display:none">
                                           
                                            <div class="card-body">
                                                <div class="alert alert-success" id="successRegister" style="display: none;"></div>
                                                <form>
                                                      <label>Enter Verification code:</label>
                                                    <input type="text" autocomplete="off"  id="verificationCode" class="form-control" placeholder="Enter verification code">
                                                    <button type="button" class="btn btn-success mt-2 text-center codeVerify" >Verify code</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>

   <script src="http://code.jquery.com/jquery-3.5.1.js"></script>
   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/8.0.1/firebase.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
      @if(auth()->check() == false)
        	toastr.options =
        	{
        		"closeButton" : true,
        		"progressBar" : true
        	}
    // 			toastr.info("Login Expired");
    	@endif
    </script>
    <script>
    
    const firebaseConfig = {
              apiKey: "{{ env('FIREBASE_API_KEY') }}",
              authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
              databaseURL: "{{ env('FIREBASE_DATABASEURL') }}",
              projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
              storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
              messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
              appId: "{{ env('FIREBASE_APP_ID') }}",
              measurementId: "{{ env('FIREBASE_MEASUREMENT_ID') }}"
            };


        firebase.initializeApp(firebaseConfig);
    </script>
    <script>
    (function($) {
    function renderRecaptcha() {
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container');
        recaptchaVerifier.render();
    }

    function phoneSendAuth() {
        var number = '+91'+$("#number").val();
        renderRecaptcha();

        firebase.auth().signInWithPhoneNumber(number, window.recaptchaVerifier)
            .then(function(confirmationResult) {
                window.confirmationResult = confirmationResult;
                toastr.info("Message Sent Successfully");
                $(".codeCard").show();
                $(".numberCard").hide();
            })
            .catch(function(error) {
                toastr.info(error.message);
            });
    }

    async function codeVerify() {
        var code = $("#verificationCode").val();

        try {
            var result = await window.confirmationResult.confirm(code);
            var user = result.user;
            console.log(user);

            var person = {
                user: user,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            var data = await $.ajax({
                url: '{{ url("mobile-number-verification") }}',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify(person)
            });

            if (data.status === 'success') {
                toastr.info(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                toastr.error(data.message);
            }
            
            $('#target').html(data.msg);
            toastr.info("Your verification was successful.");
        } catch (error) {
            toastr.error(error.message);
        }
    }

    $(document).ready(function() {
        $('body').on('click', '.phoneSendAuth', phoneSendAuth);
        $('body').on('click', '.codeVerify', codeVerify);
    });
})(jQuery);

    </script>
@endsection