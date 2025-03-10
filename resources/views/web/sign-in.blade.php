@extends('web.layout')
@section('content')
<!-- **************** MAIN CONTENT START **************** -->
<main>
	<section class="p-0 d-flex align-items-center position-relative overflow-hidden">
	
		<div class="container-fluid">
			<div class="row">
				<!-- left -->
				<div class="col-12 col-lg-6 d-md-flex align-items-center justify-content-center bg-primary bg-opacity-10 vh-lg-100">
					<div class="p-3 p-lg-5 d-none d-lg-block">
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
							<span class="mb-0 fs-1">👋</span>
							<h1 class="fs-2">Login into Pachavellam!</h1>
							<p class="lead mb-4">Nice to see you! Please log in with your account.</p>

							<!-- Form START -->
							<form action="{{url('sign-in')}}" method="POST" >
								@csrf
								@if(session()->has('message'))
									<div class="alert alert-danger">{{session('message')}}</div>
								@endif    
								<!-- Email -->
								<div class="mb-4">
									<label for="user_email" class="form-label">Email*</label>
									<div class="input-group input-group-lg">
										<span class="input-group-text bg-light rounded-start border-0 text-secondary px-3"><i class="bi bi-envelope-fill"></i></span>
										<input type="email" name="email" required autocomplete="off" class="form-control border-0 bg-light rounded-end ps-1" placeholder="E-mail" id="user_email">
									</div>
								</div>
								<!-- Password -->
								<div class="mb-4">
									<label for="user_password" class="form-label">Password*</label>
									<div class="input-group input-group-lg">
										<span class="input-group-text bg-light rounded-start border-0 text-secondary px-3"><i class="fas fa-lock"></i></span>
										<input type="password" name="password" required class="form-control border-0 bg-light rounded-end ps-1" placeholder="password" id="user_password">
									</div>
									<div id="passwordHelpBlock" class="form-text">
										 
									</div>
								</div>
								<!-- Check box -->
								<div class="mb-4 d-flex justify-content-between mb-4">
									<div class="form-check">
										<input type="checkbox" name="remember_me" class="form-check-input" id="exampleCheck1">
										<label class="form-check-label" for="exampleCheck1">Remember me</label>
									</div>
									
								</div>
								<!-- Button -->
								<div class="align-items-center mt-0">
									<div class="d-grid">
										<button  class="btn btn-primary mb-0" type="submit">Login</button>
									</div>
								</div>
							</form>
							<!-- Form END -->

							<!-- Social buttons and divider -->
							<div class="row">
								<!-- Divider with text -->
								<div class="position-relative my-4">
									<hr>
									<p class="small position-absolute top-50 start-50 translate-middle bg-body px-5">Or login with</p>
								</div>

								<!-- Social btn -->
								<div class="col-12 d-grid">
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
								<span>Don't have an account? <a href="sign-up">Signup here</a></span>
							</div>
						</div>
					</div> <!-- Row END -->
				</div>
			</div> <!-- Row END -->
		</div>
	</section>
</main>


 

  <!-- Modal End -->
   
   
<!-- **************** MAIN CONTENT END **************** -->
@endsection
