@extends('layouts.web-layout')
@section('content')
    
<!-- **************** MAIN CONTENT START **************** -->
<main>
	<section class="p-0 d-flex align-items-center position-relative overflow-hidden">
	
		<div class="container-fluid">
			<div class="row">
				<!-- left -->
				<div class="col-12 col-lg-6 d-md-flex align-items-center justify-content-center bg-primary bg-opacity-10 vh-lg-100">
					<div class="p-3 p-lg-5">
						<!-- Title -->
						<div class="text-center">
							<h2 class="fw-bold">Welcome to our largest community</h2>
							<p class="mb-0 h6 fw-light">Let's learn something new today!</p>
						</div>
						<!-- SVG Image -->
						<img src="assets/images/element/02.svg" class="mt-5" alt="">
						<!-- Info -->
						
					</div>
				</div>

				<!-- Right -->
				<div class="col-12 col-lg-6 m-auto">
					<div class="row my-5">
						<div class="col-sm-10 col-xl-8 m-auto">
							<!-- Title -->
							<img src="assets/images/element/03.svg" class="h-40px mb-2" alt="">
							<h2>Sign up for your account!</h2>
							<p class="lead mb-4">Nice to see you! Please Sign up with your account.</p>
						
							

							<!-- Social buttons and divider -->
							<div class="row">
					
								<!-- Social btn -->
								<div class="col-6 d-grid">
									<a style="    border: 1px solid #e6e6e6;" href="{{ route('auth.google') }}" class="btn bg-light text-dark mb-xxl-0">
										<img src="https://colorlib.com/etc/lf/Login_v11/images/icons/icon-google.png"></i>
										&nbsp; 
										Google
									</a>
								</div>
								<!-- Social btn -->
								<!--<div class="col-6 d-grid">-->
								<!--	<a style="    border: 1px solid #e6e6e6;" href="{{ route('auth.facebook') }}" class="btn text-dark bg-light ">-->
								<!--		<img width="30px" src="https://images.g2crowd.com/uploads/vendor/image/492/large_detail_0f38b80ca04c245e0c4383c048a59bd2.png"></img>-->
								<!--		&nbsp; -->
								<!--		Facebook-->
								<!--	</a>-->
								<!--</div>-->
							</div>

							<!-- Sign up link -->
							<div class="mt-4 text-center">
								<span>Already have an account?<a href="sign-in"> Sign in here</a></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>
<!-- **************** MAIN CONTENT END **************** -->

<!-- Back to top -->
<div class="back-top"><i class="bi bi-arrow-up-short position-absolute top-50 start-50 translate-middle"></i></div>

@endsection