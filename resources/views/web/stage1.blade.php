@extends('layouts.web-layout')

@section('content')
    <!-- Header START -->
<header class="navbar-light navbar-sticky">
	<!-- Logo Nav START -->
	<nav class="navbar navbar-expand-lg">
		<div class="container">
			<!-- Logo START -->
			<a class="navbar-brand me-0" href="index-2.html">
				<img class="light-mode-item navbar-brand-item" src="https://www.pachavellam.com/assets/images/home/logo.png" alt="logo">
				<img class="dark-mode-item navbar-brand-item" src="https://www.pachavellam.com/assets/images/home/logo-2.png" alt="logo">
			</a>
			<!-- Logo END -->

			<!-- Responsive navbar toggler -->
			<div class=" ms-auto" >
				
                <div class="navbar-toggler-animation">
                    <i class="bi fa-2x bi-youtube text-danger me-2"></i>
                    <i class="bi fa-2x bi-facebook text-info me-2"></i>
                    <i class="bi fa-2x bi-instagram text-danger me-2"></i>
                </div>
			</div>
            
			
			

		</div>
	</nav>
	<!-- Logo Nav END -->
</header>
<!-- Header END -->

<!-- =======================
Main part START -->

<section style="background-color: #d6d6d66b;">
	<div class="container">
		<div class="row g-5 justify-content-between">
			
				 
				
			<!-- Admission form START -->
			<div class="col-md-8 mx-auto">
				<div class="card">
					<div class="card-body">
						
						<!-- Form START -->
						<form class="row g-3" action="/stage1" method="POST" >
							@csrf
							<h5 class="mb-0">Personal information</h5>
							{{-- <div class="col-8"> --}}
								<!-- Type -->
								<div class="col-12">
									<div class="row g-xl-0 align-items-center">
										<div class="col-lg-4">
											<h6 class="mb-lg-0">Type <span class="text-danger">*</span></h6>
										</div>
										<div class="col-lg-8">
											<div class="d-flex">
												<div class="form-check radio-bg-light me-4">
													<input class="form-check-input" type="radio" value="Student" name="type" id="type1" checked>
													<label class="form-check-label" for="type1">
														Student/Professional

													</label>
												</div>
												<div class="form-check radio-bg-light me-4">
													<input class="form-check-input" type="radio" value="Teacher" name="type" id="type2">
													<label class="form-check-label" for="type2">
														Teacher
													</label>
												</div>
												<div class="form-check radio-bg-light me-4">
													<input class="form-check-input" type="radio" value="Institution" name="type" id="type3">
													<label class="form-check-label" for="type3">
														Company Institution
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- First name -->
								<div class="col-12">
									<div class="row g-xl-0 align-items-center">
										<div class="col-lg-4">
											<h6 class="mb-lg-0">Full Name <span class="text-danger">*</span></h6>
										</div>
										<div class="col-lg-8">
											<input type="text" class="form-control" id="fullname" name="fullname">
										</div>
									</div>
								</div>

								

								<!-- Gender -->
								<div class="col-12">
									<div class="row g-xl-0 align-items-center">
										<div class="col-lg-4">
											<h6 class="mb-lg-0">Gender <span class="text-danger">*</span></h6>
										</div>
										<div class="col-lg-8">
											<div class="d-flex">
												<div class="form-check radio-bg-light me-4">
													<input class="form-check-input" type="radio" value="Male" name="gender" id="gender1" checked>
													<label class="form-check-label" for="gender1">
														Male
													</label>
												</div>
												<div class="form-check radio-bg-light me-4">
													<input class="form-check-input" type="radio" value="Female" name="gender" id="gender2">
													<label class="form-check-label" for="gender2">
														Female
													</label>
												</div>
												<div class="form-check radio-bg-light me-4">
													<input class="form-check-input" type="radio" value="Other" name="gender" id="gender3">
													<label class="form-check-label" for="gender3">
														Other
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							{{-- </div>
							<div class="col-4">
									
							</div> --}}
							

							<!-- Date of birth -->
							<div class="col-12">
								<div class="row g-xl-0 align-items-center">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">Date of birth</h6>
									</div>
								
									<div class="col-lg-8">
										<div class="row g-2 g-sm-4">
											<div class="col-4">
												<select name="day" class="form-select js-choice z-index-9 border-0 bg-light" aria-label=".form-select-sm">
													<option value="">Date</option>
													<option>1</option>
													<option>2</option>
													<option>3</option>
													<option>4</option>
													<option>5</option>
													<option>6</option>
													<option>7</option>
													<option>8</option>
													<option>9</option>
													<option>10</option>
													<option>11</option>
													<option>12</option>
													<option>13</option>
													<option>14</option>
													<option>15</option>
													<option>16</option>
													<option>17</option>
													<option>18</option>
													<option>19</option>
													<option>20</option>
													<option>21</option>
													<option>22</option>
													<option>23</option>
													<option>24</option>
													<option>25</option>
													<option>26</option>
													<option>27</option>
													<option>28</option>
													<option>29</option>
													<option>30</option>
													<option>31</option>
												</select>
											</div>
											<div class="col-4">
												<select name="month" class="form-select js-choice z-index-9 border-0 bg-light" aria-label=".form-select-sm">
													<option value="">Month</option>
													<option value="01">Jan</option>
													<option value="02">Feb</option>
													<option value="03">Mar</option>
													<option value="04">Apr</option>
													<option value="05">May</option>
													<option value="06">Jun</option>
													<option value="07">Jul</option>
													<option value="08">Aug</option>
													<option value="09">Sep</option>
													<option value="10">Oct</option>
													<option value="11">Nov</option>
													<option value="12">Dec</option>
												</select>
											</div>
											<div class="col-4">
												<select name="year" class="form-select js-choice z-index-9 border-0 bg-light" aria-label=".form-select-sm">
													<option value="">Year</option>
													@for($j=1975;$j<2020;$j++)
													<option>{{$j}}</option>
													@endfor
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>

							

							<!-- Phone number -->
							<div class="col-12">
								<div class="row g-xl-0 align-items-center">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">Mobile number <span class="text-danger">*</span></h6>
									</div>
									<div class="col-lg-8">
										<input type="text" class="form-control" id="mobilenumber" name="mobile" value="+91">
									</div>
								</div>
							</div>

							<!-- Home address -->
							<div class="col-12">
								<div class="row g-xl-0">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">Address <span class="text-danger">*</span></h6>
									</div>
									<div class="col-lg-8">
										<textarea class="form-control" rows="3" name="address" placeholder=""></textarea>
									</div>
								</div>
							</div>

							<!-- City -->
							<div class="col-12">
								<div class="row g-xl-0 align-items-center">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">City/State/Pincode <span class="text-danger">*</span></h6>
									</div>
									<div class="col-lg-8 ">
										<div class="row">
											<div class="col-lg-4 m-2">
												<input type="text" class="form-control" id="city" placeholder="city">
											</div>
											<div class="col-lg-4 m-2">
												<input type="text" class="form-control" id="state" placeholder="state">									
											</div>
											<div class="col-lg-4 m-2">
												<input type="number" class="form-control" name="pincode" id="pincode" maxlength="6" placeholder="pincode">								
											</div>
										</div>
											
									</div>
								</div>
							</div>

							

							<!-- Password -->
							<div class="col-12">
								<div class="row g-xl-0 align-items-center">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">Password <span class="text-danger">*</span></h6>
									</div>
									<div class="col-lg-8">
										<input type="password" class="form-control" name="password" id="password">
									</div>
								</div>
							</div>

							<!-- Password -->
							<div class="col-12">
								<div class="row g-xl-0 align-items-center">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">Cofirm Password <span class="text-danger">*</span></h6>
									</div>
									<div class="col-lg-8">
										<input type="text" class="form-control" name="confirm-password" id="confirm">
									</div>
								</div>
							</div>
							<!-- Password -->
							<div class="col-12">
								<div class="row g-xl-0 align-items-center">
									<div class="col-lg-4">
										<h6 class="mb-lg-0">Where you heard about pachavellam?</h6>
									</div>
									<div class="col-lg-8">
										<div class="d-flex">
											<div class="form-check radio-bg-light me-4">
												<input class="form-check-input" value="social media" type="radio" name="heard_about" id="social_media" checked>
												<label class="form-check-label" for="social_media">
													Social Media
												</label>
											</div>
											<div class="form-check radio-bg-light me-4">
												<input class="form-check-input" value="friends" type="radio" name="heard_about" id="heard_about_frnds">
												<label class="form-check-label" for="heard_about_frnds">
													Friends Refferal
												</label>
											</div>
											<div class="form-check radio-bg-light me-4">
												<input class="form-check-input" value="tv/newspaper" type="radio" name="heard_about" id="heard_about_ads">
												<label class="form-check-label" for="heard_about_ads">
													Tv/News Paper Ads
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							
							<!-- Button -->
							<div class="col-12 text-sm-end">
								<button class="btn btn-primary mb-0">Next <i class="fas fa-arrow-right"></i></button>
							</div>
						</form>
						<!-- Form END -->
					</div>
					<!-- Admission form END -->	
			</div>
		</div>
		</div>
	</div>
</section>
<!-- =======================
Main part END -->

@endsection