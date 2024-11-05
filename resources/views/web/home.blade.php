
@extends('web.layout')
@section('content')

@section('styles')
<style>
   
.our-team {
    padding: 20px 15px 30px;
    background: #fff;
    border-radius: 15px;
    text-align: center;
}

.our-team .pic {
    display: inline-block;
    width: 100%;
    height: 100%;
    background: #fff;
    padding: 10px;
    margin-bottom: 25px;
    transition: all 0.5s ease 0s;
}

.our-team:hover .pic {
    background: #17bebb;
    border-radius: 50%;
}

.pic img {
    width: 100%;
    height: auto;
    border-radius: 50%;
}

.our-team .title {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #2e282a;
    margin: 0 0 7px 0;
}

.our-team .post {
    display: block;
    font-size: 15px;
    color: #17bebb;
    text-transform: capitalize;
    margin-bottom: 15px;
}

.our-team .social {
    padding: 0;
    margin: 0;
    list-style: none;
}

.our-team .social li {
    display: inline-block;
    margin-right: 5px;
}

.our-team .social li a {
    display: block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    border-radius: 50%;
    font-size: 15px;
    color: #17bebb;
    border: 1px solid #17bebb;
    transition: all 0.5s ease 0s;
}

.our-team:hover .social li a {
    background: #17bebb;
    color: #fff;
}

@media only screen and (max-width: 990px) {
    .our-team {
        margin-bottom: 30px;
    }
}


ins.adsbygoogle a {
    display: none !important;
}
ins.adsbygoogle[data-ad-status="unfilled"] a {
    display: block;
}
</style>


<!--<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"-->
<!--     crossorigin="anonymous"></script>-->

@endsection

<!-- **************** MAIN CONTENT START **************** -->
<main >

<!-- =======================
Main banner START -->
<section class="position-relative overflow-hidden pt-4 py-sm-1">
	<!-- Rocket image -->
	<div class="position-absolute bottom-0 start-0 rotate-74 d-none d-md-block">
		<img src="assets/images/element/rocket.svg" alt="">
	</div>

	<div class="container">
		<div class="row">
			<!-- Image -->
			<div class="col-xl-3 mt-0 pt-0 mt-xl-5 pt-xl-5 text-center position-relative d-none d-xl-block">
				<!-- SVG decoration -->
				<figure class="position-absolute bottom-0 start-50 translate-middle-x mt-4 mb-0">
					<svg width="290.2px" height="296.4px">
						<path class="fill-info" d="M287.8,112.4c0.7-5.5-17.6-26-41.7-47.9l0-0.1c-1.3-1.3-2.5-2.7-3.7-4.2c-0.7-0.9-2.3-1.9-4-3.2 c-1.8-1.4-3.6-3-5.4-4.5c-3.8-3.3-7.7-6.5-11.6-9.5c-3.8-2.9-8-5-11.6-8.5c-0.1-0.1-0.1-0.1-0.2-0.2c-4.5-3.4-9-6.6-13.4-9.7 c0,0-0.1,0-0.1,0c-0.1,0-0.1-0.1-0.2-0.2c-0.1-0.1-0.2-0.1-0.3-0.2c0,0,0,0,0,0c-0.2-0.2-0.4-0.4-0.7-0.6c-0.9-0.7-1.9-1.3-2.8-2 c-0.3-0.2-0.6-0.4-0.9-0.7c-0.3-0.2-0.7-0.4-0.9-0.5c-1.4-0.8-2.8-2.1-4.2-3c-0.2-0.1-0.5-0.2-0.7-0.4c-0.2-0.1-0.4-0.2-0.6-0.3 c-0.3,0-0.5-0.1-0.8-0.1c-14.8-9.1-28-14.9-37.1-14.7C140.6,2,131.1,6,120,12.6c-0.7,0.5-1.4,0.9-2.1,1.4c-0.7,0.4-1.3,0.9-1.9,1.1 c-6,3.7-12.3,8.1-18.8,13c0,0,0,0,0,0c-0.1,0.1-0.2,0.2-0.3,0.2c-0.1,0-0.1,0-0.2,0.1c-0.1,0.1-0.2,0.1-0.3,0.2c0,0-0.1,0-0.1,0.1 c0,0,0,0,0,0c0,0,0,0,0,0c0,0-0.1,0.1-0.1,0.1c0,0.1-0.1,0.2-0.1,0.2c-0.2,0.1-0.4,0.4-0.7,0.5C44.6,67.8-15.6,133,10.1,137.8 c0,0,10.4-0.7,27.2-1.8c-9.1,31.1-4.2,128.8,4.3,146.9c10.4,22,201.2,11.4,214.9,7.9c12.9-3.3,7.5-137.5,3.5-163.6 C295.7,126,286.5,122.7,287.8,112.4z"/>
					</svg>
				</figure>

				<!-- SVG decoration -->
				<figure class="position-absolute bottom-0 start-50 translate-middle-x mb-n4 ms-n1 z-index-9">
					<svg	width="377px" height="98px">
						<path class="fill-body" d="M44.383,-0.000 C44.383,-0.000 48.804,39.447 53.566,41.827 C53.566,41.827 61.388,46.248 82.134,51.008 C82.134,51.008 284.833,60.530 337.208,48.968 C337.208,48.968 342.990,46.248 344.350,36.726 C344.350,36.726 347.071,22.444 346.391,20.403 L377.000,27.545 L365.777,77.533 C365.777,77.533 104.921,116.639 42.342,86.714 C-20.236,56.789 5.272,15.303 4.591,13.262 C3.911,11.222 44.383,-0.000 44.383,-0.000 Z"/>
					</svg>
				</figure>
				<!-- Image -->
				<div class="position-relative mt-5 ms-3">
					<img src="assets/images/element/05.png" alt="">
				</div>
			</div>

			<!-- Content Start -->
			<div class="col-xl-6 text-center">
				<!-- Badge title -->
				<h6 class="mb-3 font-base  text-white py-2 px-4 rounded-2 d-inline-block">
                    
                    
                </h6>
				<!-- Title -->
				<h1 style="font-size:19px !important;">പച്ചവെള്ളം പോലെ പഠിക്കാം...!!</h1>
				<p class="mb-5">
മത്സര പരീക്ഷകൾക്ക് ആവശ്യമായ അറിവുകൾ, യൂണിവേഴ്സിറ്റി പരീക്ഷകളെ നിഷ്പ്രയാസം നേരിടാൻ ക്യാപ്സ്യൂൾ ക്ലാസുകൾ, പഠനത്തിൽ ബുദ്ധിമുട്ട് അനുഭവിക്കുന്ന കുട്ടികൾക്ക് ആവശ്യമായ കൗൺസിലിംഗ്  ക്ലാസുകൾ, ആനുകാലിക യുഗത്തിൽ ജോലി  സാധ്യതയുള്ള കോഴ്സുകൾ എന്നിവ വളരെ വേഗത്തിൽ അറിയാൻ ഞങ്ങളോടൊപ്പം ചേരൂ. ഇനിയെല്ലാം പച്ചവെള്ളം ആകും...
                </p>
				
				<!-- Counter item -->
				<div class="row g-4 justify-content-center">
					<!-- Counter item -->
					<div class="col-4 col-sm-6 col-md-4 col-xl-6 col-xxl-4">
						<div class="d-flex justify-content-center align-items-center">
							<!-- Icon -->
							<div class="icon-lg bg-body shadow rounded-circle text-info mb-0"><i class="fas fa-user-graduate fa-fw"></i></div>
							<div class="ms-3">
								<div class="d-flex text-dark">
									<h5 class="purecounter mb-0 fw-bold" data-purecounter-start="55" data-purecounter-end="120"	data-purecounter-delay="500">120</h5>
									<span class="mb-0 h5">K+</span>
								</div>
								<span class="mb-0">Total Students</span>
							</div>
						</div>
					</div>

					<!-- Counter item -->
					<div class="col-4 col-sm-6 col-md-4 col-xl-6 col-xxl-4">
						<div class="d-flex justify-content-center align-items-center">
							<!-- Icon -->
							<div class="icon-lg bg-body shadow rounded-circle text-purple mb-0"><i class="fas fa-chalkboard-teacher fa-fw"></i></div>
							<div class="ms-3 text-start">
								<h5 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="160"	data-purecounter-delay="300">0</h5>
								<span class="mb-0">Total Instructors</span>
							</div>
						</div>
					</div>

					<!-- Counter item -->
					<div class="col-4  col-sm-6 col-md-4 col-xl-6 col-xxl-4">
						<div class="d-flex justify-content-center align-items-center">
							<!-- Icon -->
							<div class="icon-lg bg-body shadow rounded-circle text-danger mb-0"><i class="fas fa-book-reader fa-fw"></i></div>
							<div class="ms-3">
								<div class="d-flex text-dark">
									<h5 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="2"	data-purecounter-delay="300">0</h5>
									<span class="mb-0 h5">K+</span>
								</div>
								<span class="mb-0">Total Activities</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Button and rating -->
				<div class="d-sm-flex justify-content-center align-items-center mt-5">
					<!-- Button -->
					<div> <a href="#contact-form" class="btn btn-lg btn-primary-soft mb-3 mb-sm-0">Contact now</a> </div>

					<!-- Rating -->
					<!--<div class="d-flex justify-content-center align-items-center text-start ms-0 ms-sm-4">-->
					<!--	<h2 class="me-2 mb-0">4.9</h2>-->
					<!--	<div>-->
					<!--		<ul class="list-inline mb-0">-->
					<!--			<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>-->
					<!--			<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>-->
					<!--			<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>-->
					<!--			<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>-->
					<!--			<li class="list-inline-item me-0 small"><i class="fas fa-star-half-alt text-warning"></i></li>-->
					<!--		</ul>-->
					<!--		<p class="mb-0 small">Students Love Us!</p>-->
					<!--	</div>-->
					<!--</div>-->
                    
				</div>
			</div>
			<!-- Content END -->

			<!-- Image -->
			<div class="col-xl-3 position-relative text-center d-none d-md-block">
				<!-- SVG decoration -->
				<div class="position-absolute bottom-0 start-0 mb-n5 d-none d-md-block">
					<svg enable-background="new 0 0 223 134" xml:space="preserve">
            <path class="fill-purple" d="m216.6 78.7c-1.8-0.3-4.1 0.1-5.9 0.3-2.4 0.2-4.8 0.7-7.1 0.9-1.6 0.1-3.1 0.2-4.6 0.3-1.8 0.1-3.7 0.1-5.6 0.1-1.3-0.1-2.6-0.1-3.9-0.1-2.5 0-4.9-0.3-7.3-0.4-2.3-0.1-4.5 0.5-6.8 0.5-4 0.1-8.6-0.3-12.2 1.6-0.2 0.1-0.2 0.5-0.1 0.6 0.6 1.1 2.6 1.6 3.7 2 0.3 0.1 0.7 0.3 1 0.3 0.9 0.6 1.8 1.2 2.8 1.8 0.7 0.5 1.5 0.8 2.2 1.2 0.1 0.1 0.3 0.2 0.4 0.3 0.3 0.2 1 0.4 1.2 0.7 0.3 0.5 0 1.6-0.1 2.1-0.3 1.3-0.6 2.5-0.8 3.8-0.3 2.1-1 4.1-1.5 6-0.1 0.1-0.3 0.3-0.4 0.5-0.3 0.4 0.1 0.8 0.5 0.7v0.2c0 0.2 0.4 0.1 0.5 0 0.2-0.1 0.3-0.3 0.5-0.4s0.3-0.2 0.5-0.3c2-0.8 4-1.5 5.8-2.6 0.7 1.4 1.4 2.9 2 4.3 0.5 1.1 1.4 3.1 2.9 3.2 1.8 0.1 3.8-2.6 5.1-3.7 2.1-1.6 4.4-3.1 6.5-4.7 3.9-2.8 7.7-5.7 11.4-8.8 0.9-0.8 1.4-1.8 2.3-2.6 1-1 2.2-1.6 3.3-2.4 0.9-0.7 1.5-1.5 2.2-2.3 0.7-0.7 1.6-1.5 2.1-2.5-0.2-0.3-0.3-0.6-0.6-0.6zm-46.5 7 0.6 0.3 0.9 0.6c-0.6-0.3-1.1-0.6-1.5-0.9zm2.4 0.6c-0.7-0.7-1.2-1-2.1-1.4-1.2-0.5-2.6-1-3.8-1.4h-0.1c-0.3-0.2-0.7-0.4-1-0.5-0.5-0.3-1.5-0.4-0.9-0.9 0.2-0.1 0.7-0.1 0.9-0.2 1.2-0.3 2.4-0.5 3.7-0.7 2.6-0.1 5.2 0.1 7.5-0.1 1.2-0.1 2.3-0.2 3.5-0.3h1.9 0.1c0.7 0 1.4 0.1 2.2 0.1 1.9 0.2 3.8 0.1 5.7 0.2 4.3 0.1 8.4 0.1 12.7-0.3 2.1-0.2 4.2-0.5 6.3-0.7 1.2-0.1 2.3-0.2 3.5-0.2-0.5 0.1-1 0.3-1.5 0.3-1.8 0.3-3.5 0.6-5.2 0.9-3.4 0.4-6.8 0.9-10.1 1.6-3.4 0.7-6.8 1.5-10.3 2.2-3.7 0.7-7 2.4-10.7 3.3-0.1-0.1-0.2-0.1-0.3-0.1-0.6-0.9-1.2-1.1-2-1.8zm1.5 9.8c0.1-0.5 0.3-0.9 0.4-1.3 0.3-1.1 0.4-2.3 0.6-3.5 0.1-0.8 0.4-1.6 0.4-2.5 0 0 0-0.1 0.1-0.1 0.3-0.2 1.8-0.3 2-0.3 1.4-0.4 2.9-1 4.3-1.5 1.2-0.5 2.5-0.9 3.8-1.2 3.3-0.7 6.5-1.3 9.8-2.2 3.5-0.9 7.1-1.2 10.7-1.7 1.6-0.2 3.3-0.5 4.9-0.8 1-0.2 2-0.5 3.1-0.7-0.6 0.3-1.2 0.7-1.9 1-1.2 0.5-2.4 0.8-3.6 1.2-2.5 1.1-4.8 2.5-7.1 3.8-1 0.4-2 0.8-3.1 1.2-2.2 0.9-4.3 1.8-6.4 2.7-1.5 0.6-3.1 1.1-4.6 1.8-1.1 0.5-2.2 1-3.3 1.5-0.2 0.1-0.5 0.2-0.7 0.3-0.5 0.2-1 0.4-1.4 0.5-1.1 0.3-2.4 0.6-3.3 1.1-0.1-0.1-0.3-0.1-0.4 0.1-1.3 0.9-2.6 2-3.8 2.9-0.3 0.3-0.7 0.5-1 0.8 0.1-0.8 0.3-1.9 0.5-3.1zm4.7 1.9c-1.4 0.8-3.1 1.4-4.6 2 0.1-0.2 0.3-0.3 0.3-0.4 0.3-0.2 0.5-0.3 0.7-0.5 1-0.9 2.1-1.8 3.2-2.6 0.2 0.5 0.4 0.9 0.6 1.4-0.1 0.1-0.2 0.1-0.2 0.1zm34.9-16.3c-0.5 0.4-0.9 0.9-1.3 1.2l-0.4 0.4c-0.2 0.1-0.3 0.3-0.5 0.4-1 0.7-2 1.4-2.9 2.2-0.5 0.5-0.9 1-1.2 1.5-1 0.9-1.9 1.8-2.9 2.6-0.8 0.6-1.6 1.2-2.3 1.8-1.6 1.4-3.4 2.7-5.2 4-3.3 2.4-6.7 4.6-9.8 7.3-0.7 0.5-1.2 1-1.9 1.6-0.7 0.4-1.3 0.5-2-0.1-0.2-0.3-0.3-0.7-0.4-0.9-0.5-1-1-2-1.4-3-0.7-1.6-1.2-3.3-2.3-4.6 1.4-0.8 3.4-1 4.8-1.6s2.8-1.5 4.3-2l9.6-3.6c2.9-1.2 5.4-2.7 8.2-4.1 0.1-0.1 0.3-0.1 0.4-0.1 1.7-0.7 3.3-1.6 5-2.3 0.7-0.3 1.4-0.5 2.1-0.8 0.1-0.1 0.3-0.2 0.5-0.3l-0.4 0.4z"></path>
            <path class="fill-purple" d="m89.1 0c-0.5 0-1 0.1-1.4 0.3-0.3 0.1-0.5 0.3-0.8 0.3-0.3 0.1-0.6 0-0.9 0.1-0.1 0-0.3 0.2-0.3 0.3v0.2c-0.1 0.5 0.7 0.8 0.9 0.3 0.4-0.1 0.7 0 1.1-0.2 0.5-0.3 0.9-0.4 1.4-0.4 0.6 0 0.6-0.9 0-0.9z"></path>
            <path class="fill-purple" d="m77.1 0.9c-0.7 0-1.4-0.1-2.2-0.1-0.6 0-1.4 0-1.8 0.5s0.3 1.1 0.7 0.7c0.3-0.3 1.2-0.2 1.6-0.2 0.5 0 1.1 0.1 1.6 0.1 0.7-0.1 0.7-1 0.1-1z"></path>
            <path class="fill-purple" d="m65.5 1.5h-2.9c-0.6 0-0.6 0.9 0 0.9h2.9c0.6 0 0.6-0.9 0-0.9z"></path>
            <path class="fill-purple" d="m55.7 2.1c-0.9-0.3-2.7-0.7-3.1 0.5-0.1 0.6 0.7 0.8 0.9 0.3 0.1-0.4 1.7 0 2 0.1 0.5 0.2 0.7-0.6 0.2-0.9z"></path>
            <path class="fill-purple" d="m46.1 2.7c-0.7 0-1.3-0.1-2 0s-1.2 0.4-1.8 0.4-0.6 0.9 0 0.9 1.1-0.2 1.6-0.3c0.7-0.2 1.4-0.1 2.2-0.1 0.6 0.1 0.6-0.9 0-0.9z"></path>
            <path class="fill-purple" d="m35 4.5c-1 0.4-2.3 0.6-3.1 1.4-0.5 0.4 0.2 1 0.7 0.7 0.7-0.7 1.8-0.9 2.7-1.2 0.5-0.3 0.3-1.1-0.3-0.9z"></path>
            <path class="fill-purple" d="m24 9c-0.6 0.5-1.6 0.6-1.8 1.4-0.1 0.6 0.7 0.8 0.9 0.3 0.1-0.3 0.5-0.4 0.7-0.5 0.3-0.2 0.7-0.3 0.9-0.5 0.4-0.4-0.3-1.1-0.7-0.7z"></path>
            <path class="fill-purple" d="m15.7 15c-0.3 0.5-0.5 0.9-0.7 1.4-0.2 0.6-0.5 1.1-0.7 1.7-0.1 0.6 0.8 0.9 0.9 0.3 0.1-0.5 0.4-1 0.6-1.5s0.3-0.9 0.7-1.4-0.4-1-0.8-0.5z"></path>
            <path class="fill-purple" d="m10.6 23.6c-0.3-0.1-0.5 0.1-0.6 0.3-0.3 0.7-0.5 1.3-0.5 2 0 0.6 0.9 0.6 0.9 0 0-0.7 0.1-1.2 0.4-1.8 0.2-0.1 0-0.4-0.2-0.5z"></path>
            <path class="fill-purple" d="m6.4 36.1v2.6c0 0.6 0.9 0.6 0.9 0v-2.6c0-0.6-0.9-0.6-0.9 0z"></path>
            <path class="fill-purple" d="m7.1 45.8c-0.1-0.3 0-0.7 0-1 0-0.6-0.9-0.6-0.9 0 0 0.3-0.1 0.7 0 1 0.1 0.4 0.3 0.7 0.2 1-0.1 0.6 0.9 0.6 0.9 0 0.1-0.3-0.1-0.6-0.2-1z"></path>
            <path class="fill-purple" d="m8.7 57.4c-0.4-0.7-0.6-1.6-1.1-2.2-0.4-0.5-1 0.2-0.7 0.7 0.5 0.6 0.7 1.4 1 2 0.2 0.5 1.1 0 0.8-0.5z"></path>
            <path class="fill-purple" d="m13.3 63.9c-0.8-0.8-1.4-1.6-2.4-2.2-0.5-0.3-1 0.5-0.5 0.8 0.9 0.5 1.5 1.3 2.2 2 0.5 0.4 1.1-0.2 0.7-0.6z"></path>
            <path class="fill-purple" d="m20.9 69.2c-0.5-0.1-1-0.4-1.5-0.7-0.5-0.2-0.9-0.5-1.2-0.9-0.4-0.4-1 0.3-0.7 0.7 0.4 0.4 0.9 0.8 1.4 1 0.6 0.3 1.1 0.7 1.8 0.7 0.5 0.2 0.7-0.7 0.2-0.8z"></path>
            <path class="fill-purple" d="m31.1 72c-0.2 0-0.3-0.1-0.5-0.1-0.2-0.1-0.4 0-0.6-0.1-0.5-0.1-1-0.3-1.3-0.5-0.5-0.4-1.1 0.3-0.7 0.7 0.5 0.4 1 0.6 1.5 0.7 0.3 0.1 0.5 0 0.8 0.1s0.5 0.2 0.8 0.2c0.6-0.2 0.6-1.1 0-1z"></path>
            <path class="fill-purple" d="m36.2 74.5c-0.1 0-0.3-0.2-0.3-0.3-0.1-0.1-0.3-0.3-0.4-0.5-0.4-0.4-1 0.2-0.7 0.7 0.4 0.4 0.7 0.9 1.4 1 0.3 0 0.5-0.2 0.5-0.5-0.1-0.2-0.3-0.4-0.5-0.4z"></path>
            <path class="fill-purple" d="m46.7 80.6c-0.3 0-0.7-0.5-0.9-0.7-0.4-0.3-0.9-0.4-1.3-0.7-0.5-0.4-1.1 0.3-0.7 0.7 0.5 0.4 1.1 0.5 1.6 0.9 0.4 0.3 0.7 0.7 1.2 0.7 0.7 0.1 0.7-0.8 0.1-0.9z"></path>
            <path class="fill-purple" d="m54.3 86.1c-0.4-0.4-0.7-0.8-1-1.2-0.3-0.5-1.2 0-0.8 0.5 0.3 0.4 0.5 0.8 0.9 1.1 0.2 0.2 0.7 0.5 0.7 0.7 0.1 0.6 1 0.6 0.9 0-0.1-0.5-0.3-0.8-0.7-1.1z"></path>
            <path class="fill-purple" d="m60.3 93.1c0.1 0 0-0.1-0.1-0.1-0.1-0.1-0.1-0.1-0.1-0.2-0.1-0.2-0.3-0.3-0.5-0.5-0.3-0.3-0.7-0.7-0.7-1.2-0.1-0.6-1-0.3-0.9 0.3s0.5 1.1 0.9 1.5c0.2 0.2 0.4 0.4 0.5 0.7s0.3 0.4 0.6 0.5c0.6 0 0.9-0.8 0.3-1z"></path>
            <path class="fill-purple" d="m64.9 100.9v-0.9c0-0.6-0.9-0.6-0.9 0 0 0.5-0.1 0.9 0.1 1.4 0.1 0.1 0.1 0.3 0.3 0.3h0.2c0.3 0.1 0.5-0.1 0.6-0.3 0-0.2-0.2-0.4-0.3-0.5z"></path>
            <path class="fill-purple" d="m66.5 110.5c-0.5-0.4 0.1-1.7 0.1-2.2 0.1-0.6-0.9-0.6-0.9 0-0.1 0.9-0.7 2.2 0.1 2.9 0.5 0.3 1.1-0.3 0.7-0.7z"></path>
            <path class="fill-purple" d="m64.2 116.7c-0.4 0.7-0.8 1.2-0.9 2-0.1 0.6 0.8 0.9 0.9 0.3 0.1-0.7 0.5-1.2 0.8-1.8 0.3-0.5-0.4-1-0.8-0.5z"></path>
            <path class="fill-purple" d="m59.6 126.6c-0.1-0.3-0.4-0.3-0.7-0.2s-0.6 0.3-0.9 0.4-0.4 0.3-0.3 0.6 0.3 0.3 0.6 0.3c0.4-0.1 0.8-0.3 1.2-0.5 0.2 0 0.2-0.4 0.1-0.6z"></path>
            <path class="fill-purple" d="m48.5 127.2c-0.8 0.1-1.6 0.2-2.3 0.2-0.6 0-0.6 0.9 0 0.9 0.8 0 1.6-0.1 2.3-0.2 0.6-0.1 0.6-1 0-0.9z"></path>
            <path class="fill-purple" d="m40.9 126.6c-0.3-0.3-0.5-0.5-0.8-0.7-0.1-0.1-0.3-0.1-0.5-0.2s-0.3-0.1-0.4-0.1c-0.4-0.5-1 0.2-0.7 0.7 0.3 0.3 0.6 0.3 0.9 0.4s0.6 0.4 0.8 0.7c0.4 0.3 1.1-0.3 0.7-0.8z"></path>
            <path class="fill-purple" d="m38.9 127.3c-0.3-0.5-0.7-0.9-1.1-1.4-0.4-0.4-1 0.2-0.7 0.7 0.4 0.4 0.7 0.7 1 1.2s1.1 0 0.8-0.5z"></path>
            <path class="fill-purple" d="m33.7 119.8c-0.1-0.1-0.3-0.3-0.3-0.5 0-0.6-0.9-0.6-0.9 0 0 0.5 0.3 0.9 0.6 1.2 0.4 0.3 1.1-0.3 0.6-0.7z"></path>
            <path class="fill-purple" d="m34.2 110c0 0.1-0.3 0.3-0.3 0.3-0.1 0.1-0.3 0.3-0.4 0.5-0.2 0.4-0.3 0.8-0.3 1.2-0.1 0.6 0.9 0.6 0.9 0 0.1-0.5 0.2-0.9 0.5-1.2s0.5-0.5 0.5-1c0.1-0.4-0.8-0.4-0.9 0.2z"></path>
            <path class="fill-purple" d="m40.8 103.2c-0.5 0.1-0.9 0.5-1.3 0.7-0.5 0.3-0.8 0.7-1 1.2-0.3 0.5 0.5 1 0.8 0.5 0.2-0.4 0.5-0.7 0.8-0.9 0.4-0.3 0.8-0.6 1.2-0.7 0.1 0 0.3-0.2 0.3-0.3s0-0.1 0.1-0.2c0.1-0.5-0.7-0.8-0.9-0.3z"></path>
            <path class="fill-purple" d="m49.4 100.3c-0.7-0.1-1.4 0.1-2 0.3-0.6 0.1-1 0.3-1.4 0.7-0.4 0.5 0.3 1.1 0.7 0.7s0.8-0.5 1.3-0.5c0.5-0.1 1-0.2 1.6-0.2 0.4-0.1 0.4-1-0.2-1z"></path>
            <path class="fill-purple" d="m57 100.4c-0.8-0.6-1.7-0.7-2.6-0.8-0.6 0-0.6 0.9 0 0.9 0.7 0 1.4 0.1 2 0.5 0.4 0.4 1-0.3 0.6-0.6z"></path>
            <path class="fill-purple" d="m66.5 103.9c-1.1-0.3-2.1-0.9-3.3-0.9-0.6 0.1-0.6 1 0 0.9 1.1-0.1 2 0.6 3 0.9 0.6 0 0.8-0.8 0.3-0.9z"></path>
            <path class="fill-purple" d="m79.7 109.4c-0.6-0.7-1.1-1.4-1.8-2-0.8-0.6-1.6-0.9-2.5-1.3-0.5-0.3-1 0.5-0.5 0.8 0.8 0.4 1.6 0.7 2.3 1.1 0.7 0.5 1.2 1.3 1.8 2 0.5 0.5 1.1-0.1 0.7-0.6z"></path>
            <path class="fill-purple" d="m87 114.4c-0.6-0.1-1.2-0.7-1.6-1.1s-1 0.2-0.7 0.7c0.6 0.6 1.3 1.3 2.2 1.4 0.7 0 0.7-1 0.1-1z"></path>
            <path class="fill-purple" d="m97.7 120.6c-0.8-0.8-1.6-1.4-2.6-1.8-0.9-0.4-1.6-0.9-2.6-0.9-0.6 0-0.6 0.9 0 0.9 1.6 0 3.5 1.4 4.5 2.4 0.5 0.5 1.1-0.1 0.7-0.6z"></path>
            <path class="fill-purple" d="m108.9 123.7c-0.5 0-1-0.3-1.5-0.4-0.4-0.1-1 0.1-1.4-0.1-0.5-0.4-1 0.3-0.7 0.7 0.5 0.5 1 0.4 1.6 0.4 0.7 0 1.2 0.5 1.9 0.4 0.7-0.2 0.8-1.1 0.1-1z"></path>
            <path class="fill-purple" d="m118.2 126c-0.5 0-1 0.1-1.5 0s-0.9-0.3-1.3-0.4c-0.5-0.2-0.8 0.7-0.3 0.9 1 0.4 2 0.5 3.1 0.5 0.6-0.1 0.6-1 0-1z"></path>
            <path class="fill-purple" d="m125.5 124c-0.5 0.5-1 0.8-1.7 0.9-0.6 0.1-0.3 1 0.3 0.9 0.8-0.2 1.5-0.6 2.1-1.2 0.4-0.4-0.3-1-0.7-0.6z"></path>
            <path class="fill-purple" d="m128.7 117c-0.5 0.5-0.4 1.2-0.4 1.8-0.1 0.8-0.6 1.4-0.6 2.3 0 0.6 0.9 0.6 0.9 0 0-0.7 0.4-1.2 0.5-1.8 0.1-0.5-0.1-1.3 0.2-1.6 0.4-0.4-0.2-1.1-0.6-0.7z"></path>
            <path class="fill-purple" d="m130.1 112c-0.3-0.5-0.1-1.2-0.3-1.8-0.1-0.3-0.1-0.7-0.3-0.9-0.1-0.3-0.3-0.6-0.4-1-0.1-0.6-1-0.3-0.9 0.3 0.1 0.3 0.3 0.6 0.4 0.9 0.1 0.4 0.3 0.8 0.3 1.2 0.2 0.7 0.1 1.2 0.4 1.8 0.2 0.6 1 0.1 0.8-0.5z"></path>
            <path class="fill-purple" d="m127.7 104.9c-0.2-0.5-0.4-1.1-0.3-1.6 0.1-0.6-0.9-0.6-0.9 0-0.1 0.7 0.2 1.5 0.5 2.2 0.2 0.4 1-0.1 0.7-0.6z"></path>
            <path class="fill-purple" d="m131 96.8c-0.3 0.3-0.7 0.3-0.9 0.5-0.4 0.2-0.7 0.5-1 0.9-0.4 0.4 0.3 1 0.7 0.7 0.3-0.3 0.6-0.6 0.9-0.8 0.3-0.1 0.7-0.3 1-0.5 0.4-0.5-0.3-1.2-0.7-0.8z"></path>
            <path class="fill-purple" d="m136.4 96c-0.6 0-0.6 0.9 0 0.9 0.5 0 0.9-0.1 1.4-0.1 0.3 0 0.6 0 0.9 0.1 0.1 0 0.2 0.1 0.3 0.1h0.1c0.1 0.5 0.9 0.5 0.9-0.1-0.1-1.4-2.9-0.9-3.6-0.9z"></path>
            <path class="fill-purple" d="m144.9 99.2c-0.5-0.3-1 0.5-0.5 0.8 0.7 0.5 2.5 1.1 2.3 2.2-0.1 0.6 0.8 0.9 0.9 0.3 0.3-1.7-1.6-2.5-2.7-3.3z"></path>
            <path class="fill-purple" d="m156.6 106c-1.5-0.1-2.9-0.5-4.3-1-0.5-0.2-0.8 0.7-0.3 0.9 1.4 0.5 2.9 1 4.6 1 0.6 0 0.6-0.9 0-0.9z"></path>
            <path class="fill-purple" d="m163 104.9c-0.4 0-0.7-0.1-1.1 0-0.3 0.1-0.7 0.3-1 0.2-0.6-0.1-0.6 0.9 0 0.9 0.3 0 0.7-0.1 1-0.2 0.4-0.1 0.8 0 1.1 0 0.6 0 0.6-0.9 0-0.9z"></path>
            <path class="fill-purple" d="m168.1 103.9c0.5-0.1 0.5-0.9-0.1-0.9-0.5 0-0.9 0.4-1.2 0.7-0.1 0.2-0.3 0.4-0.3 0.7s0.3 0.5 0.5 0.6c0.3 0.1 0.5-0.1 0.6-0.3 0-0.1 0-0.3-0.1-0.4l0.2-0.2c0.1-0.1 0.1-0.1 0.2-0.1 0.1-0.1 0.1-0.1 0.2-0.1z"></path>
          </svg>
				</div>
				<!-- SVG decoration -->
				<figure class="position-absolute bottom-0 start-50 translate-middle-x mb-xl-5 pb-0 pb-xl-5 d-none d-md-block">
					<svg width="290.2px" height="296.4px">
						<path class="fill-success" d="M287.8,112.4c0.7-5.5-17.6-26-41.7-47.9l0-0.1c-1.3-1.3-2.5-2.7-3.7-4.2c-0.7-0.9-2.3-1.9-4-3.2 c-1.8-1.4-3.6-3-5.4-4.5c-3.8-3.3-7.7-6.5-11.6-9.5c-3.8-2.9-8-5-11.6-8.5c-0.1-0.1-0.1-0.1-0.2-0.2c-4.5-3.4-9-6.6-13.4-9.7 c0,0-0.1,0-0.1,0c-0.1,0-0.1-0.1-0.2-0.2c-0.1-0.1-0.2-0.1-0.3-0.2c0,0,0,0,0,0c-0.2-0.2-0.4-0.4-0.7-0.6c-0.9-0.7-1.9-1.3-2.8-2 c-0.3-0.2-0.6-0.4-0.9-0.7c-0.3-0.2-0.7-0.4-0.9-0.5c-1.4-0.8-2.8-2.1-4.2-3c-0.2-0.1-0.5-0.2-0.7-0.4c-0.2-0.1-0.4-0.2-0.6-0.3 c-0.3,0-0.5-0.1-0.8-0.1c-14.8-9.1-28-14.9-37.1-14.7C140.6,2,131.1,6,120,12.6c-0.7,0.5-1.4,0.9-2.1,1.4c-0.7,0.4-1.3,0.9-1.9,1.1 c-6,3.7-12.3,8.1-18.8,13c0,0,0,0,0,0c-0.1,0.1-0.2,0.2-0.3,0.2c-0.1,0-0.1,0-0.2,0.1c-0.1,0.1-0.2,0.1-0.3,0.2c0,0-0.1,0-0.1,0.1 c0,0,0,0,0,0c0,0,0,0,0,0c0,0-0.1,0.1-0.1,0.1c0,0.1-0.1,0.2-0.1,0.2c-0.2,0.1-0.4,0.4-0.7,0.5C44.6,67.8-15.6,133,10.1,137.8 c0,0,10.4-0.7,27.2-1.8c-9.1,31.1-4.2,128.8,4.3,146.9c10.4,22,201.2,11.4,214.9,7.9c12.9-3.3,7.5-137.5,3.5-163.6 C295.7,126,286.5,122.7,287.8,112.4z"/>
					</svg>
				</figure>

				<!-- SVG decoration -->
				<figure class="position-absolute bottom-0 start-50 translate-middle-x ms-n1 mb-0 mb-xl-6 z-index-9">
					<svg	width="377px" height="98px">
						<path class="fill-body" d="M44.383,-0.000 C44.383,-0.000 48.804,39.447 53.566,41.827 C53.566,41.827 61.388,46.248 82.134,51.008 C82.134,51.008 284.833,60.530 337.208,48.968 C337.208,48.968 342.990,46.248 344.350,36.726 C344.350,36.726 347.071,22.444 346.391,20.403 L377.000,27.545 L365.777,77.533 C365.777,77.533 104.921,116.639 42.342,86.714 C-20.236,56.789 5.272,15.303 4.591,13.262 C3.911,11.222 44.383,-0.000 44.383,-0.000 Z"/>
					</svg>
				</figure>

				<div class="position-relative ms-5 mt-5  d-none d-md-block">
					<!-- SVG decoration -->
					<figure class="position-absolute start-0 top-0">
						<svg enable-background="new 0 0 56.2 55.9">
						<path class="fill-warning" d="m38.3 38.7c0.5 1.3 1 2.4 1.4 3.5 0.3 0.9 0.2 1.3-0.7 1.7-0.4 0.2-0.9 0.4-1.4 0.5-2.4 0.6-4.9 1.2-7.3 1.9-0.7 0.2-1.4 0.3-1.9 0.6s-0.9 0.8-1.2 1.2c-0.3 0.6 0.1 1.2 0.7 1.3 0.5 0.1 1.1 0 1.6-0.2 2.8-1 5.6-2 8.4-3 0.6-0.2 1.1-0.6 1.6-0.9 0.6-0.4 0.7-0.7 0.8-1.5 0.6 0.4 0.6 0.9 0.2 1.3-0.3 0.4-0.7 0.7-1.2 1-2.2 1.3-4.6 2.1-7 2.9-1.2 0.4-2.5 0.7-3.8 1.1-0.3 0.1-0.4 0.5-0.6 0.7 0 0.1 0.1 0.2 0.1 0.3 0.4 0 0.8 0.1 1.2 0 2.5-0.6 5.1-1.3 7.6-2 0.5-0.1 0.9-0.4 1.3-0.6 1.2-0.6 1.4-0.9 1.8-2.3 0.1 0.3 0.2 0.5 0.2 0.6 0 0.7 0 1.3 0.2 2 0.3 0.8-0.3 1.6-0.9 2.1-0.4 0.3-0.5 0.6-0.5 1.1 0 2.2-1.4 3.7-3.6 3.9-0.5 0.1-1 0-1.6 0-1.7-0.1-2.6-0.8-3.1-2.4-2.7-0.3-3.7-1.3-3.2-3.2-1.4-1.2-1.5-1.5-0.6-3.2 0.2-0.4 0.2-0.7 0-1.1-0.6-1.3-1.3-2.6-1.9-3.9-0.2-0.5-0.4-0.8-1-0.9-3-0.4-5.4-1.9-7.1-4.4-2.9-4.4-3.9-9.1-1.8-14.1 2-4.7 5.7-7.6 10.9-8.4 4.2-0.7 8 0.2 11.4 2.7s5.1 6 5.3 10.2c0.2 3.5-0.8 6.7-3 9.5-0.2 0.8-0.7 1.4-1.3 2zm-4.8-10.3c0.3-0.2 0.4-0.3 0.6-0.4 1.1-0.7 1.8-0.5 2.5 0.6 0.6 1 0.5 1.9 0 2.8-0.7 1.1-1.5 2.1-2.4 3.1-0.7 0.8-1.1 1.7-0.9 2.8 0.1 0.7 0.2 1.3 0.3 2 0.4 1.7 0.8 3.4 1.2 5.2 1.5-0.4 3.1-0.5 4.5-1.6-0.4-1.1-0.8-2.2-1.3-3.2-0.4-0.8-0.2-1.5 0.3-2.1 0.4-0.5 0.9-0.9 1.2-1.5 2.6-3.4 3.3-7.3 2.3-11.3-1.5-6.3-6.7-9.7-12.7-10.1-4.3-0.2-7.9 1.3-10.8 4.4-2 2.1-3.3 4.6-3.5 7.6-0.2 2.5 0.2 4.9 1.2 7.1 1.1 2.6 2.8 4.8 5.4 5.9 1.2 0.5 2.5 0.8 3.8 1.2 0.8 1.7 1.7 3.5 2.6 5.4 1.2-0.4 2.3-0.7 3.4-1-0.8-2.1-1.6-4.2-2.4-6.2-0.8-1.9-2.1-3.5-3.9-4.6-0.7-0.4-1.3-0.9-1.9-1.4-1.3-1.2-1.1-2.5 0.4-3.4 1.3-0.8 2.8-0.9 4.2-1.3 0.6-0.2 1.3 0.1 2 0.1 1.6-1.5 2.2-1.5 3.9-0.1zm0.8 16.2c-0.5-2.2-0.9-4.1-1.4-6s-0.2-3.6 1.3-5c0.6-0.6 1.2-1.4 1.7-2.2 0.5-0.7 0.7-1.5 0.4-2.4-0.3-0.7-0.8-1-1.4-0.8-0.7 0.2-0.9 0.7-0.8 1.5 0 0.2 0.2 0.4 0.1 0.5-0.2 0.3-0.4 0.6-0.6 0.8-0.1 0.1-0.6-0.3-0.7-0.5s-0.1-0.6 0-0.9c0.2-0.6 0-0.9-0.4-1.2-0.8-0.6-1.6-0.3-2 0.8 0.3 0.3 0.7 0.6 0.8 1 0.1 0.3 0.1 0.8-0.1 1.1s-0.7 0.4-1 0.4c-0.3-0.1-0.6-0.5-0.7-0.8-0.1-0.4-0.1-0.9-0.1-1.4-0.8-0.4-1.5-0.6-2.3-0.1 0.1 0.2 0.2 0.3 0.3 0.4 0.2 0.4 0.6 0.7 0.7 1.1 0.2 0.6 0 1.2-0.5 1.6s-1.2 0.6-1.7 0.3-0.6-0.8-0.6-1.3 0.1-1.1 0.1-1.7c-0.8 0-1.4 0.4-2 0.8-0.8 0.7-0.9 1.4-0.1 2.1 0.6 0.5 1.2 1 1.9 1.4 1.7 1.1 3.1 2.5 3.9 4.4 0.7 1.5 1.2 3 1.8 4.5 0.3 0.7 0.5 1.5 0.8 2.3 1.1-0.2 1.8-0.4 2.6-0.7zm-5.7 7.3c0.5 0.9 1.3 0.9 2.1 0.8 2.4-0.3 4.9-0.7 7.2-1.6 0.6-0.3 1.2-0.7 1.8-1.2 0.5-0.4 0.6-1 0.4-1.8-3.5 2.4-7.5 2.9-11.5 3.8zm3 1.6c0.1 0.9 0.6 1.3 1.2 1.6 1.5 0.7 3.9 0.3 5.1-0.9 0.7-0.7 1.1-1.5 0.9-2.4-2.5 0.5-4.8 1.1-7.2 1.7zm-5.2-23.6c-0.5 0.7-0.5 1.4-0.3 2.2 0.7 0.2 1.2 0 1.4-0.6s-0.2-1.2-1.1-1.6zm3.7 0c0 0.4-0.1 0.5-0.1 0.7 0.1 0.2 0.2 0.4 0.4 0.5 0 0 0.3-0.1 0.3-0.1 0-0.2 0-0.4-0.1-0.6 0-0.2-0.2-0.3-0.5-0.5z"/>
						<path class="fill-warning" d="m55.4 15c-1.7 1-3.2 1.8-4.6 2.7-1.5 0.9-2.6 2.3-4.3 3.2 1.5-2.5 6.9-6.1 8.9-5.9z"/>
						<path class="fill-warning" d="m23.9 0.7c0.5 3 1 5.9 1.4 8.7-0.6-0.5-0.7-1.2-0.9-1.9-0.4-1.7-0.7-3.4-1-5.2 0-0.5-0.1-1.1 0.5-1.6z"/>
						<path class="fill-warning" d="m46.9 30.5c1.3-0.1 6.6 1.2 7.4 1.8-0.5 0.3-0.5 0.3-2.4-0.3-0.8-0.2-1.7-0.5-2.5-0.7-0.9 0-1.8-0.2-2.5-0.8z"/>
						<path class="fill-warning" d="m7.2 24.7c-2.2-0.4-4.5-0.7-6.7-1.1 0-0.6 0.5-0.5 0.8-0.5 1.1 0.1 2.2 0.3 3.3 0.5 0.5 0.1 1 0.2 1.5 0.4 0.4 0.1 0.8 0.3 1.1 0.5v0.2z"/>
						<path class="fill-warning" d="m38.6 10.2c0.7-2.3 2.6-4.3 4.3-4.9 0 0.2 0 0.5-0.1 0.5-1.4 0.8-2.3 2-3.3 3.3-0.2 0.4-0.6 0.7-0.9 1.1z"/>
						<path class="fill-warning" d="M11.1,36c-1.1,1.9-3.5,4.4-4.3,4.6C7,39.8,9.5,37,11.1,36z"/>
						<path class="fill-warning" d="m8.9 10c1.5 0 4.4 2 5.1 3.6-1.6-1.1-3.3-2.3-5.1-3.6z"/>
						</svg>
					</figure>
					<!-- Image -->
					<img src="assets/images/element/04.png" class="me-5 mb-4 position-relative" alt="">
				</div>
			</div>
		</div>
	</div>
</section>
<!-- =======================
Main banner END -->

 <!--Waves Container-->
  
 <g><path fill="#fff"
    d="M250.4,0.8C112.7,0.8,1,112.4,1,250.2c0,137.7,111.7,249.4,249.4,249.4c137.7,0,249.4-111.7,249.4-249.4
    C499.8,112.4,388.1,0.8,250.4,0.8z M383.8,326.3c-62,0-101.4-14.1-117.6-46.3c-17.1-34.1-2.3-75.4,13.2-104.1
    c-22.4,3-38.4,9.2-47.8,18.3c-11.2,10.9-13.6,26.7-16.3,45c-3.1,20.8-6.6,44.4-25.3,62.4c-19.8,19.1-51.6,26.9-100.2,24.6l1.8-39.7		c35.9,1.6,59.7-2.9,70.8-13.6c8.9-8.6,11.1-22.9,13.5-39.6c6.3-42,14.8-99.4,141.4-99.4h41L333,166c-12.6,16-45.4,68.2-31.2,96.2	c9.2,18.3,41.5,25.6,91.2,24.2l1.1,39.8C390.5,326.2,387.1,326.3,383.8,326.3z" />
    </g>
    </svg>
 <div>
    <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
    viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
    <defs>
    <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
    </defs>
    <g class="parallax">
    <use xlink:href="#gentle-wave" x="48" y="0" fill="#e1fff4" />
    <use xlink:href="#gentle-wave" x="48" y="3" fill="#a6cf4f" />
    <use xlink:href="#gentle-wave" x="48" y="5" fill="#e1fff4" />
    <use xlink:href="#gentle-wave" x="48" y="7" fill="#e1fff4" />
    </g>
    </svg>
    </div>
    <!--Waves end-->
<!-- =======================
Action box START -->

 
<!-- =======================
Services START -->
<section class="pt-4 pt-lg-5 mt-3">
	<div class="container">
		<!-- Category START -->
		<div class="row g-4">
			<!-- Category item -->
			<div class="col-sm-6 col-lg-12 col-xl-3">
				<h2>Services that you need to know</h2>
				<p></p>
			</div>


			<!-- Category item -->
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="card card-body shadow h-100">
					<!-- Title and image -->
					<div class="d-flex align-items-center">
						<img src="assets/images/element/idea.svg" class="h-60px mb-2" alt="">
						<div class="ms-3">
							<h5 class="mb-0"><a >Competitive Exam Classes </a></h5>
							<!--<p class="mb-0 small">Exam Related </p>-->
						</div>
					</div>
					<!-- List -->
					<ul class="list-group list-group-borderless small mt-2">
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Free and Premium classes</li>
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Study Material,Prev Question Paper</li>
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Daily Current Affairs,Syllabus</li>
					</ul>
					<!--<div class="col-12 mt-3">-->
					<!--	<a href="{{url('kpsc')}}" class="btn btn-sm btn-primary mb-0">View <i class="fas fa-arrow-right"></i></a>-->
					<!--</div>-->
				</div>
			</div>
			
			
			<!-- Category item -->
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="card card-body shadow h-100">
					<!-- Title and image -->
					<div class="d-flex align-items-center">
						<img src="assets/images/element/18.svg" class="h-60px mb-2" alt="">
						<div class="ms-3">
							<h5 class="mb-0"><a >Capsule Course for University Exams</a></h5>
							<!--<p class="mb-0 small">UG,PG</p>-->
						</div>
					</div>
					<!-- List -->
					<ul class="list-group list-group-borderless small mt-2">
						
							<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Online and offline classess</li>
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Individual attention for students</li>
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>University exam preperation</li>
						
					</ul>
				</div>
			</div>
			
			
			<!-- Category item -->
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="card card-body shadow h-100">
					<!-- Title and image -->
					<div class="d-flex align-items-center">
						<img src="assets/images/element/10.svg" class="h-60px mb-2" alt="">
						<div class="ms-3">
							<h5 class="mb-0"><a h>Edu Counceling</a></h5>
							<!--<p class="mb-0 small">Exam Related </p>-->
						</div>
					</div>
					<!-- List -->
					<ul class="list-group list-group-borderless small mt-2">
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>
						Edu Counselling provides advice and assistance to students for choosing their Career path by selecting the education plans and suitable courses in which institutes. This Counselling improves Students education skills and enhance ability to focus on their syllabus.Moreover we provide assistance to special education such as learning disabilities
						</li>
					</ul>
					<!--<div class="col-12 mt-3">-->
					<!--	<a href="{{url('kpsc')}}" class="btn btn-sm btn-primary mb-0">View <i class="fas fa-arrow-right"></i></a>-->
					<!--</div>-->
				</div>
			</div>
			
			<div class="col-sm-6 col-lg-4 col-xl-3"></div>
			<!-- Category item -->
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="card card-body shadow h-100">
					<!-- Title and image -->
					<div class="d-flex align-items-center">
						<img src="assets/images/element/19.svg" class="h-60px mb-2" alt="">
						<div class="ms-3">
							<h5 class="mb-0"><a >A to Z classes for UG to PG</a></h5>
							<!--<p class="mb-0 small">A to Z Classes for KG to PG</p>-->
						</div>
					</div>
					<!-- List -->
					<ul class="list-group list-group-borderless small mt-2">
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Online and offline classess</li>
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>Individual attention for students</li>
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>University exam preperation</li>
					</ul>
					<!--<div class="col-12 mt-3"><br>-->
					<!--	<a href="{{url('/')}}" class="btn btn-sm btn-primary mb-0">Comming soon.. <i class="fas fa-arrow-right"></i></a>-->
					<!--</div>-->
				</div>
			</div>

		
			<!-- Category item -->
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="card card-body shadow h-100">
					<!-- Title and image -->
					<div class="d-flex align-items-center">
						<img src="assets/images/element/17.svg" class="h-60px mb-2" alt="">
						<div class="ms-3">
							<h5 class="mb-0"><a >Medical Coding Courses</a></h5>
						</div>
					</div>
					<!-- List -->
					<ul class="list-group list-group-borderless small mt-2">
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>
						Medical coding is the transformation of healthcare diagnosis, procedures, medical services, and equipment into universal medical alphanumeric codes
						</li>
					</ul>
					<!--<div class="col-12 mt-3">-->
					<!--	<a href="{{url('kpsc')}}" class="btn btn-sm btn-primary mb-0">View <i class="fas fa-arrow-right"></i></a>-->
					<!--</div>-->
				</div>
			</div>
			
			<!-- Category item -->
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="card card-body shadow h-100">
					<!-- Title and image -->
					<div class="d-flex align-items-center">
						<img src="assets/images/element/data-science.svg" class="h-60px mb-2" alt="">
						<div class="ms-3">
							<h5 class="mb-0"><a >Ethical Hacking Courses</a></h5>
						</div>
					</div>
					<!-- List -->
					<ul class="list-group list-group-borderless small mt-2">
						<li class="list-group-item text-body pb-0"> <i class="bi bi-patch-check-fill text-success"></i>
						Ethical hacking involves an authorized attempt to gain unauthorized access to a computer system, application, or data. Carrying out an ethical hack involves duplicating strategies and actions of malicious attackers.
						</li>
					</ul>
					<!--<div class="col-12 mt-3">-->
					<!--	<a href="{{url('kpsc')}}" class="btn btn-sm btn-primary mb-0">View <i class="fas fa-arrow-right"></i></a>-->
					<!--</div>-->
				</div>
			</div>
			
			
			
		</div>
		<!-- Category END -->
	</div>
</section>
<!-- =======================
Services END -->
<!-- =======================
About START -->
<section class="mt-3">
	<div class="container">
		<div class="row g-4 align-items-center">
			<div class="col-lg-5">
				<!-- Title -->
				<h2>Find Out More About us, <span class="text-success">Pachavellam</span> insides.</h2>
				<!-- Image -->
				<img src="assets/images/about/03.jpg" class="rounded-2" alt="">
			</div>
			<div class="col-lg-7">
				<div class="row g-4">
					<!-- Item -->
					<div class="col-sm-6">
						<div class="icon-lg bg-orange bg-opacity-10 text-orange rounded-2"><i class="fas fa-user-tie fs-5"></i></div>
						<h5 class="mt-2">Learn with Experts</h5>
						<p class="mb-0">Learning with Experts is an online learning experience with a difference. We believe the best learning comes from learning together and learning by doing.</p>
					</div>
					<!-- Item -->
					<div class="col-sm-6">
						<div class="icon-lg bg-info bg-opacity-10 text-info rounded-2"><i class="fas fa-book fs-5"></i></div>
						<h5 class="mt-2">Learn Anything</h5>
						<p class="mb-0">Learn Anything, the platform for knowledge discovery that helps you understand any topic through the most efficient paths, as voted by the community.</p>
					</div>
					<!-- Item -->
					<div class="col-sm-6">
						<div class="icon-lg bg-purple bg-opacity-10 text-purple  rounded-2"><i class="fas fa-university fs-5"></i></div>
						<h5 class="mt-2">Flexible learning space</h5>
						<p class="mb-0">a student focused approach to teaching a unit of work. The learning space is divided into smaller task focused spaces that relate directly to the learning behaviours student should display when working in each area.</p>
					</div>
					<!-- Item -->
					<div class="col-sm-6">
						<div class="icon-lg bg-success bg-opacity-10 text-success rounded-2"><i class="bi bi-alarm    fs-5"></i></div>
						<h5 class="mt-2">Learn Anywhere Anytime</h5>
						<p class="mb-0">Learn Anywhere is an easy-to-use and affordable collaboration solution that brings students into the classroom - even when that is not physically possible.</p>
					</div>		
				</div>
			</div>
		</div>
	</div>
</section>
<!-- =======================
About END -->

<!-- =======================
Steps START -->
<section class="position-relative pt-0 pt-lg-5 mt-3">
	<!-- SVG decoration -->
	<figure class="position-absolute top-0 start-0 mt-5 ms-1 d-none d-xxl-block">
		<svg enable-background="new 0 0 84.6 98">
			<path class="fill-success" d="m54.4 73.8c1.1 4.1 2.2 8.2 3.3 12.4 6.2-1.3 12.4-2.4 18.9-2.8 0.3 1.3 0.5 2.7 0.8 4-1.1-0.9-0.9-2.3-1.5-3.4-6.5 0.6-12.9 1.6-19.3 3.2-0.1 0.2-0.1 0.4-0.2 0.6-0.1 0-0.2 0.1-0.3 0.1-0.5-0.5-1.2-0.3-1.7-0.2-3.8 1-7.5 1.9-11.3 2.9-1.7 0.4-3.4 0.9-5.1 1.3 0.3 1.2 0.6 2.4 0.9 3.7-0.9-1-1.3-2.1-1.5-3.4-0.3-0.1-0.5-0.2-1-0.3 0.2-0.2 0.3-0.3 0.5-0.4 2.3-0.6 4.6-1.3 6.9-1.8 3.5-0.9 6.9-1.8 10.4-2.6 0.3-0.1 0.6-0.2 0.8-0.3-0.7-2.1-1.6-4.1-2.2-6.1s-1-4.1-1.6-6.2c-0.2 0-0.5-0.1-0.7-0.1-10.2 1.2-19.5-1.3-27.6-7.6-5.3-4.1-9.1-9.3-11.3-15.7-1.3-3.7-2-7.6-2.1-11.5-0.1-3.3 0.4-6.6 1.3-9.8 1.1-4.1 2.8-8 5.2-11.5 0.2-0.3 0.3-0.5 0.4-0.8-1-1.3-2.1-2.5-3.2-3.9-1.4 1.3-2.5 2.7-3.5 4.2-3.2 4.5-5.1 9.6-5.8 15.1-1 7.5-0.6 14.8 1.7 22 2.1 6.4 5.4 12.1 10.6 16.5 3.8 3.2 8.1 5.6 12.9 7.1 2.8 0.9 5.6 1.5 8.4 1.8 2.4 0.2 4.8 0.2 7.1 0.2 1.9 0 3.9-0.3 5.8-0.5 0.3 0 0.6 0.1 1 0.1v0.3c-1.5 0.2-3.1 0.6-4.6 0.7-2.9 0.1-5.8 0.2-8.8 0-2.2-0.1-4.4-0.6-6.5-1.1-4-0.9-7.8-2.4-11.2-4.6-6-3.7-10.6-8.8-13.5-15.3-1.6-3.6-2.8-7.4-3.4-11.4-0.7-3.7-1-7.2-0.8-10.7 0.3-7.9 2.3-15.2 7.1-21.5 0.9-1.2 1.9-2.3 2.9-3.6-0.8-0.9-1.7-1.8-2.5-2.7l0.2-0.2c1.2 0.4 1.8 1.6 2.8 2.5 1.9-1.6 3.8-3.2 6.5-3.6 0 0.8-0.5 0.7-0.8 0.8-1.9 0.6-3.4 1.8-4.9 3.1-0.1 0.1-0.1 0.2-0.3 0.4 0.9 1.2 1.9 2.5 2.9 3.9 0.4-0.5 0.9-1 1.3-1.5 3.5-4.4 7.7-7.7 12.8-10.1 6.5-3 13.3-3.4 20.2-2.1 0.2 0 0.4 0.2 0.7 0.4-0.4 0.5-0.8 0.3-1.1 0.3-2.7-0.7-5.5-0.8-8.3-0.7-2.7 0.1-5.3 0.6-7.8 1.4-5.1 1.6-9.5 4.4-13.2 8.1-3.2 3.2-5.8 6.9-7.7 11.2-1.5 3.3-2.5 6.8-3 10.4-0.6 3.8-0.4 7.6 0.3 11.3 0.5 3.2 1.6 6.3 3 9.1 2.1 4.1 4.9 7.7 8.4 10.6 4 3.4 8.4 5.8 13.4 7.2 7.6 2.2 15.1 2 22.5-0.7 5.4-2 10.1-5 14.1-9.1 1-1 1.9-2.2 2.8-3.3-1.5 0.6-3 1.4-4.9 0.5 0.7-0.1 1.2-0.2 1.6-0.2 1.7 0 3-0.8 3.9-2.1 2-2.6 2.7-7.2 0.8-10.3-1-1.7-3.1-2.3-5.1-1.4-0.6 0.3-0.9 0.8-0.9 1.5 0 1.4-0.7 2.4-1.6 3.3-0.9 0.8-1.7 1.7-2.6 2.5-0.6 0.5-0.9 1.2-0.9 2-0.1 1 0.2 1.5 1.3 1.6 0.7 0.1 1.4 0 2.1 0.1 1 0.1 1.7 0.7 2.1 1.6 0.1 0.2 0 0.4-0.1 0.6l-0.4-0.2c-0.5-1.2-1.5-1.5-2.7-1.5-0.6 0-1.2 0-1.7-0.2-1-0.3-1.3-0.8-1.3-1.8 0-1.1 0.3-2.1 1.2-2.8 0.8-0.7 1.6-1.5 2.4-2.3 0.9-0.8 1.5-1.7 1.6-2.9 0.1-1.5 0.8-2.1 2.3-2.5 3.1-0.7 4.9 1.2 5.6 3.2s0.8 4.1 0.2 6.1c-0.1 0.3-0.1 0.6-0.2 1.1 5.1-7.5 7.1-21.4 1-32.7-5.8-10.7-14.7-17.1-27-19.3 0.9-0.4 1.5-0.3 2.1-0.2 4.5 0.7 8.5 2.5 12.3 4.9 5.2 3.4 9.5 7.6 12.6 13 2.3 4 3.6 8.4 4.1 13 0.3 3 0.3 6-0.1 9-0.6 4.7-2.2 9.1-4.7 13.1-3.9 6.4-9.2 11.3-15.9 14.5-2.2 1.1-4.5 1.8-6.8 2.6-0.2 0.2-0.5 0.3-0.8 0.4zm-2.4 1.1c0.9 4.2 1.7 8.1 3.9 11.7l1.2-0.3c-1.1-4-2.1-7.9-3.2-11.8-0.8 0.2-1.3 0.3-1.9 0.4z"/>
			<path class="fill-success" d="m43.6 15.3c0.7 0 1.1 0 1.5-0.1 2.4-0.6 4.8-1.3 7.3-2 3.6-1 7.4-1.5 11.1-1.3 0.6 0 1.3 0.2 1.9 0.5 0.9 0.4 1.3 1.1 1.2 2 0 0.8-0.6 1-1.3 1.3-0.5 0.2-0.9 0.6-1.3 1-0.5 0.6-1 1.2-1.5 1.9-0.9 1.2-1.3 2.4-0.8 4 0.6 1.9-0.1 3.2-2 4-0.8 0.3-1.7 0.5-2.5 0.8-0.6 0.2-1.2 0.5-1.8 0.8s-0.8 0.9-0.7 1.5c0 0.6 0.4 0.9 1 1.1 0.4 0.1 0.8 0.2 1.1 0.3 2.4 0.6 3.9 2.7 3.6 5.1-0.1 1.1-0.4 2.3-0.8 3.3-0.4 1.1-0.3 1.9 0.5 2.7 0.5 0.5 1.1 1 1.6 1.5 0.4 0.4 0.9 0.8 1.3 1.3 0.2 0.3 0.4 0.7 0.4 1.1 0.1 1.1-0.7 1.6-1.6 1-0.6-0.4-1.1-0.9-1.6-1.4-1.1-1.1-2.2-2.3-3.4-3.3-1.4-1.2-3-1.7-4.9-1.8-1.6 0-3.2-0.1-4.8-0.3-2.5-0.3-4.5-1.4-6.1-3.4-1.1-1.5-2.5-2.7-4.3-3.4-1.6-0.5-3.2-0.8-4.8-0.5-3 0.4-6 0.9-9 1.3h-0.9c-1.4-0.1-2.2-0.7-2.5-1.8s0.2-2.4 1.2-3c0.7-0.4 1.5-0.7 2.2-1.1s1.5-0.7 2.1-1.1c1-0.7 1.3-1.8 1-2.8-0.2-0.7-0.6-0.9-1.2-0.6s-1.2 0.8-1.8 1.1c-0.7 0.4-1.3 0.7-2 1-0.9 0.3-1.6-0.2-1.7-1.1 0-0.6 0.1-1.2 0.4-1.8 1.1-2.1 2.7-3.6 4.9-4.6 1.2-0.6 2.5-0.7 3.7-0.2 3 1.2 5.4 0.3 7.5-1.9 1.5-1.5 3.1-2.9 4.8-4.2 0.7-0.5 1.7-0.7 2.6-0.8 1.4-0.1 1.8 0.5 1.2 1.8-0.1 0.6-0.4 1.2-0.8 2.1zm19.1 31.5c0.3-1.1-0.3-1.6-0.9-2.1-0.7-0.6-1.4-1.2-2.1-1.9-1.1-1-1.5-2.2-0.9-3.7 0.4-1 0.7-2.1 0.8-3.2 0.2-2.2-1.1-3.8-3.3-4.3-0.6-0.1-1.2-0.4-1.7-0.6-0.6-0.3-0.8-0.9-0.8-1.6 0.1-0.9 0.4-1.6 1.3-2 0.7-0.3 1.4-0.6 2.1-0.8 0.9-0.3 1.8-0.6 2.6-1 1.2-0.6 1.5-1.3 1.2-2.6-0.2-1.1-0.5-2.2 0-3.3 1.1-1.9 2-3.9 4.2-4.9 0.7-0.3 0.8-0.8 0.4-1.4s-1.1-0.9-1.8-0.9c-1.6 0-3.3-0.1-4.9 0.1-3.2 0.3-6.3 1.2-9.4 2-1.5 0.4-3.1 0.9-4.6 1.3-0.6 0.1-1.2 0.2-1.6-0.3-0.5-0.5-0.2-1.1 0-1.6 0.2-0.6 0.5-1.1 0.8-2-0.9 0.1-1.5 0.1-2.1 0.2-1.1 0.2-1.9 0.9-2.7 1.6-1.4 1.3-2.7 2.6-4 3.8-1.7 1.6-3.7 2.3-6.1 1.5-0.4-0.1-0.8-0.3-1.3-0.3-0.8-0.1-1.6-0.2-2.4 0-2.4 0.7-4.2 2.3-5.4 4.6-0.3 0.6-0.4 1.3 0.1 2.2 0.6-0.3 1.2-0.5 1.8-0.8 0.8-0.5 1.6-1 2.4-1.4 1.1-0.5 1.8-0.2 2.1 1s0.2 2.4-0.9 3.3c-0.6 0.5-1.3 1.1-2 1.2-1 0.2-1.7 0.9-2.6 1.3-0.8 0.4-1.3 1.6-1.1 2.4s1 1.3 2.1 1.3h0.5c1.5-0.2 3-0.5 4.4-0.7 1.9-0.3 3.8-0.6 5.7-0.7 2.1-0.1 4.1 0.3 5.9 1.5 1.2 0.8 2.2 1.9 3.1 3 1.3 1.6 3 2.5 5 2.7 1.3 0.2 2.6 0.4 4 0.3 3.5-0.1 6.2 1.2 8.5 3.9 0.7 0.8 1.4 1.5 2.1 2.2 0.6 0.2 1 0.4 1.5 0.7z"/>
			<path class="fill-success" d="m25.1 38.7c1.3-0.9 2.8-1 4.2-0.6 0.5 0.1 1 0.3 1.5 0.5 1 0.4 2.1 0.3 3.1 0.1 0.7-0.1 1.5-0.3 2.2-0.4 1.6-0.2 2.7 0.5 3.4 1.8 0.5 1.1 1 2.2 1.5 3.2 0.2 0.5 0.5 1 0.8 1.5 0.6 0.9 1.4 1.4 2.5 1 0.9-0.2 1.6 0.1 2.1 0.8s0.4 1.5 0.1 2.1c-0.5 0.8-1.1 1.6-1.6 2.4-0.3 0.4-0.5 0.7-0.8 1-0.9 1-1.2 2.2-1 3.6 0.1 0.6 0.1 1.2 0 1.8-0.1 0.8-0.4 1.5-1.1 2-0.8 0.6-1.1 1.4-1.1 2.3l-0.3 2.1c-0.2 1.3-1.1 2-2.4 2.1-1.7 0.1-3.1-0.7-4.2-1.9-1.5-1.7-2.2-3.7-2.8-5.8-0.4-1.3-0.7-2.6-1.1-3.8-0.7-2-1.9-2.7-4-2.6-0.8 0-1.6 0.1-2.4 0.1-3.5 0.1-5.8-3.5-5.3-6.6 0.1-0.6 0.5-1.2 0.8-1.7 0.8-1.1 1.5-2.2 1.9-3.5 0.2-0.8 0.8-1.1 1.6-1.2 0.1 0 0.3 0.1 0.8 0.2-1.5 0.5-1.8 1.5-2.2 2.6-0.3 0.7-0.8 1.4-1.3 2-1.2 1.5-1.2 3.1-0.4 4.7 0.7 1.6 2 2.6 3.8 2.8 0.7 0.1 1.3 0 2 0 0.5 0 1-0.1 1.5-0.1 2.1 0.1 3.4 1 4.1 3 0.6 1.7 1 3.5 1.6 5.2 0.6 1.8 1.3 3.5 2.8 4.7 0.9 0.8 2 1.2 3.2 1.1 1-0.1 1.5-0.5 1.7-1.4 0.1-0.3 0.1-0.7 0.2-1 0.1-1.7 0.6-3.1 1.9-4.3 0.4-0.4 0.4-1.3 0.6-2 0.1-0.3-0.1-0.6-0.1-0.9-0.2-1.5 0-2.8 1.1-3.9 0.8-0.8 1.4-1.8 2-2.8 0.2-0.4 0.4-0.8 0.5-1.2 0.1-0.8-0.4-1.3-1.2-1.3h-0.3c-2 0.1-2.9-0.3-3.8-2.1l-1.5-3.3c-0.9-1.9-1.7-2.4-3.7-2.1-0.4 0.1-0.8 0.1-1.2 0.2-1.4 0.4-2.7 0.4-4-0.1-1.4-0.5-2.8-0.7-4.3-0.4-0.7 0.2-1.1 0.1-1.4 0.1z"/>
			<path class="fill-success" d="m40.6 96.6c0 0.3-0.1 0.6-0.1 1.2-0.4-1.5-1.5-0.8-2.2-1v-0.3c0.4-0.2 0.7-0.4 1.2-0.6 0.1-1.1-0.5-2.3 0-3.7 0.8 1.2 0.1 2.6 1.1 3.6 2-0.4 4-0.9 6.1-1.3 2.1-0.5 4.2-0.9 6.3-1.4s4.1-1 6.2-1.5 4.1-0.9 6.2-1.4 4.2-1 6.3-1.4c2-0.4 4-1.1 6.1-0.9-3 1-6.1 1.5-9.2 2.2s-6.3 1.4-9.4 2.2l-9.3 2.1c-3.1 0.8-6.2 1.5-9.3 2.2z"/>
			<path class="fill-success" d="m66.5 74.2c2-1.3 3.9-2.6 5.9-3.9-0.5 1-0.9 1.4-2.6 2.5-1 0.6-2 1.3-3 2 0.5 0.9 1 1.8 1.5 2.8-1 0-1-0.7-1.3-1-0.3-0.4-0.6-0.9-1-1.3-3.1 1.5-6.2 3-9.7 3.8 0.1-0.8 0.7-0.8 1.1-0.9 2.1-0.9 4.3-1.7 6.4-2.6 0.6-0.2 1.1-0.6 1.7-0.9-0.6-1-1.2-2-1.7-2.9 1.3 0.3 1.7 1.4 2.7 2.4z"/>
			<path class="fill-success" d="m69.8 38.3c0.9 0 1.7 0.3 2.4 0.8 0.6 0.4 0.9 1 0.9 1.7 0 0.3-0.4 0.8-0.7 0.9-1.3 0.5-2.7 0.8-4.1 0.3-0.4-0.1-0.7-0.3-1-0.6-0.8-0.9-0.7-1.8 0.3-2.4 0.7-0.3 1.5-0.4 2.2-0.7zm2.9 2.7c-0.7-1.8-2.5-2.4-4.3-1.6-0.5 0.2-1 0.4-0.9 1 0.1 0.4 0.5 0.8 0.9 1 0.3 0.2 0.7 0.2 1 0.2 1.1 0.1 2.2-0.1 3.3-0.6z"/>
			<path class="fill-success" d="m16.5 27.4c-0.3 0.1-0.6 0.2-0.9 0.4-0.5 0.4-1 0.7-1.4 1.1-0.5 0.6-0.3 1.5 0.2 1.8 0.7 0.4 1.7 0 1.9-0.8 0.1-0.4 0.2-0.8 0.3-1.3 0.5 0.9 0.4 1.5 0 2.2-0.5 0.7-1.3 0.9-2.1 0.7-0.6-0.2-1.1-0.8-1.2-1.6-0.1-1.5 1.6-3.1 3.2-2.5z"/>
		</svg>
	</figure>

	<!-- SVG decoration -->
	<figure class="position-absolute top-50 start-0 translate-middle-y ms-5 d-none d-md-block">
		<svg enable-background="new 0 0 44.1 76.2">
			<path class="fill-orange" d="m25 32c-0.7-0.4-1.4-1-1.1-1.8 0.3-0.9 0.7-1.9 1.8-2.2 1-0.3 2-0.6 3-0.6 1.8-0.1 3.7-0.7 5.5-0.1 0.2 0.1 0.5-0.1 0.8-0.1 0.7 0 1.4 0 2 0.1 1.5 0.4 2 2 0.9 3-0.5 0.5-0.5 1-0.5 1.6 0.2 2.3 0.4 4.6 0.7 6.9 0.3 3.5 0.7 7 1 10.5l0.6 6.9c0.2 2.3 0.5 4.7 0.7 7 0.1 1.8 0.2 3.7 0.3 5.6 0 0.2-0.1 0.4-0.2 0.6-0.9-7-1.5-14.1-2.1-21.3-1.9 0.6-3.6 1-5.5 1-1.7 0-3.5 0.1-5.4 0.2v1.6c0.3 3.4 0.6 6.8 0.9 10.3 0.2 2.2 0.2 4.3 0.6 6.5 0.2 1.6 0.7 3.1 1.3 4.7 0.6 1.8 2.2 2.7 4 3.1 2.8 0.6 5.2-0.3 6.2-2.6 0.4-1 0.7-2.1 0.9-3.1 0.2-0.9 0.1-1.9 0.2-2.8 0-0.4 0.1-0.8 0.4-1.3 0.1 0.4 0.3 0.8 0.3 1.2 0.1 2.1-0.1 4.2-1.1 6.2-1.4 2.9-4 3.7-7 3.1-2.2-0.5-3.6-1.8-4.7-3.9-1.2-2.2-1-4.5-1.2-6.8-0.3-2.5-0.4-5-0.6-7.5s-0.4-5-0.6-7.6c-0.3-3.5-0.6-6.9-0.9-10.4-0.8-2.7-1-5.3-1.2-8zm2 15.3c3.5-2.1 7.1-2.1 11-1.3-0.4-4.8-0.9-9.5-1.3-14-3.7 0.1-7.2 0.2-11 0.3 0.4 5 0.8 9.9 1.3 15zm0.4 1c3.2 0.5 6.2 0.2 9.2-0.6 0.4-0.1 0.7-0.4 1.2-0.6-2.5-1.7-9-0.9-10.4 1.2zm-2-19.3c2.2 1.4 9.6 1 11-0.6-0.5-0.5-1-0.7-1.7-0.5-0.4 0.1-0.9 0.2-1.3 0.1-2.1-0.5-4.1 0.1-6.2 0.3-0.6 0-1.3 0.1-1.8 0.7zm-0.8 0.9c0 0.1-0.1 0.2-0.1 0.4 0.7 0.3 0.8 1.2 1.8 1.2 3.3-0.1 6.7-0.1 9.8-0.2 0.7-0.8 1.2-1.5 1.7-2.2-1.6 0.2-3 0.8-4.4 1.1s-3 0.3-4.4 0.2c-1.5 0-2.9-0.3-4.4-0.5z"/>
			<path class="fill-orange" d="m13.1 62.4c0.9 2.3 1.8 4.6 2.7 6.9 0.1 0.3 0.3 0.6 0.5 0.9 1.3 1.9 3.4 2.6 5.6 1.8 1.4-0.5 2.2-1.5 2.3-2.9 0.1-1.1 0-2.2-0.1-3.4 0-0.6-0.2-1.3-0.3-1.9 0.1 0 0.2-0.1 0.3-0.1 0.2 0.4 0.4 0.8 0.5 1.3 0.4 1.4 0.5 2.8 0.3 4.2-0.2 1.5-1 2.6-2.4 3.2-2.3 1.1-4.8 0.5-6.5-1.3-0.9-1-1.4-2.1-1.8-3.3-2.2-6.6-4.5-13.1-6.7-19.7-0.5-1.5-1-3.1-1.5-4.6-0.1-0.6-0.3-1.1-1-1.5-0.9-0.5-0.6-2.3 0.5-2.9 0.7-0.4 1.5-0.8 2.3-1 1.7-0.5 3.3-1 5-1.4 1.3-0.3 1.9 0 2.2 0.9 0.1 0.3 0.1 0.6 0 0.8-0.6 0.8-0.2 1.6 0.1 2.3 1.6 4.6 3.1 9.1 4.7 13.7 1.3 3.7 2.5 7.3 3.8 11 0.2 0.5 0.3 1.1 0.2 1.7-1.4-2.2-1.8-4.8-2.9-7.1-2.4 1.3-4.9 2.1-7.8 2.4zm-6.6-20c2.1 6.2 4.2 12.4 6.4 18.7 2.1-2.4 4.8-2.7 7.5-3.2-2.1-6-4.1-11.9-6.1-17.8-2.7 0.8-5.2 1.5-7.8 2.3zm14 16.5c-2.6-0.3-5.9 1-7 2.7 1.9-0.1 3.5-0.7 5.1-1.4 0.6-0.2 1.5-0.3 1.9-1.3zm-15-19.1c2.5 0.5 6.8-0.9 7.7-2.5-2.7 0.5-5.2 1.1-7.7 2.5zm9-1.9c-1.5 0.7-2.9 1.6-4.3 2.1-1.5 0.5-3.1 0.7-5 1.1 0.6 0.2 0.9 0.4 1.3 0.4 0.5 0 1.1-0.1 1.6-0.3 1.9-0.5 3.8-1.1 5.7-1.6 0.2-0.6 0.5-1.1 0.7-1.7z"/>
			<path class="fill-orange" d="m33.1 15c-1.4 0-2.6-1.2-2.5-2.5 0.1-1.6 1.8-3 3.1-2.9 1.2 0.1 2.6 1.4 2.5 2.5-0.1 1.6-1.6 2.9-3.1 2.9zm0.7-4.7c-1.4-0.1-2.5 1.2-2.6 2.2-0.1 0.9 0.8 1.7 1.9 1.7 1.2 0 2.3-1 2.3-2.1 0-1-0.7-1.7-1.6-1.8z"/>
			<path class="fill-orange" d="m22.6 1c1.8 0 2.6 0.7 2.6 2.3 0 1.7-1 2.8-2.7 2.8-1.5 0-2.6-1-2.6-2.5 0-1.6 1-2.6 2.7-2.6zm0.3 0.2c-0.4 0.2-0.8 0.3-1.1 0.5-0.9 0.4-1.4 1.4-1.1 2.5 0.2 0.8 1 1.3 2 1.2s1.8-0.8 1.9-1.8c0.1-1.2-0.6-1.9-1.7-2.4z"/>
			<path class="fill-orange" d="m2.9 28.6c-1.4 0-2.4-0.7-2.7-1.8-0.3-0.9 0.1-1.9 0.9-2.6 0.1-0.1 0.3-0.3 0.4-0.3 1.1 0 2.4-0.1 3.3 0.7 0.7 0.6 0.7 1.4 0.5 2.1-0.3 0.9-0.9 1.5-1.9 1.8-0.2 0-0.4 0-0.5 0.1zm-0.2-0.7c1.1 0 1.9-0.7 2-1.7 0.1-0.8-0.8-1.7-1.9-1.8s-2.1 0.8-2.2 1.8c0 0.8 1 1.6 2.1 1.7z"/>
			<path class="fill-orange" d="m28.9 21.3c-0.9 0-1.5-0.7-1.5-1.7s0.7-1.6 1.8-1.5c1.2 0 1.8 0.5 1.8 1.4 0.1 0.8-1.1 1.8-2.1 1.8zm1.5-2.1c-0.2-0.1-0.4-0.5-0.7-0.6-0.7-0.2-1.5 0.4-1.5 1 0 0.5 0.2 0.9 0.7 1 0.5 0 1.4-0.6 1.5-1.4z"/>
			<path class="fill-orange" d="m26.8 9.4c1.3 0.2 1.7 0.8 1.2 1.8-0.4 0.9-1.6 1.4-2.4 1s-1.3-1.4-0.9-2.3c0.3-0.8 0.7-0.9 1.6-0.4-1.1 0.7-1.3 1-0.8 1.6 0.4 0.5 1 0.6 1.5 0.1 0.6-0.5 0.6-0.9-0.2-1.8z"/>
			<path class="fill-orange" d="m10 29.4c-0.8 0-1.6-0.7-1.6-1.5 0-0.7 0.7-1.4 1.4-1.4 0.9 0 1.9 0.7 1.9 1.4-0.1 0.8-0.9 1.5-1.7 1.5zm-0.6-2.1c-0.2 1-0.1 1.4 0.4 1.5 0.5 0 0.9-0.4 1-1.3-0.4-0.1-0.9-0.1-1.4-0.2z"/>
			<path class="fill-orange" d="m32.4 20.3c0.7-0.4 1.1-0.8 1-1.7 0.7 0.7 1.4 1 2.5 0.8-0.7 0.6-1.6 0.9-1.4 2-0.3-0.3-0.6-0.6-0.8-0.9-0.4 0-0.8 0-1.1 0.1-0.1-0.1-0.2-0.2-0.2-0.3z"/>
			<path class="fill-orange" d="m8.1 33.5c0 0.7-0.6 1.3-1.2 1.3-0.5 0-0.9-0.4-0.8-1 0-0.7 0.6-1.3 1.1-1.3 0.5 0.1 0.9 0.5 0.9 1z"/>
			<path class="fill-orange" d="m7.1 21.6c-0.7 0-1.1-0.5-1-1.1 0-0.7 0.5-1.1 1.1-1.1 0.7 0 1.1 0.5 1.1 1.1 0 0.7-0.5 1.1-1.2 1.1zm0.4-1.8c-0.3 0.3-0.5 0.6-0.6 0.8 0.1 0.1 0.3 0.3 0.4 0.3 0.3-0.1 0.6-0.3 0.2-1.1z"/>
			<path class="fill-orange" d="m30.2 24.7c-0.1 0.7-0.5 1-1.1 1-0.3 0-0.7-0.3-0.8-0.6-0.1-0.4 0.1-0.8 0.3-1.2 0-0.1 0.5-0.1 0.7 0 0.3 0.3 0.6 0.6 0.9 0.8z"/>
			<path class="fill-orange" d="m25 14.6c0.2 0.2 0.3 0.4 0.5 0.6 0.2 0.1 0.4 0.2 0.6 0.4-0.3 0.4-0.6 0.8-0.9 1.2-0.4-0.3-0.7-0.6-1-0.9 0.2-0.4 0.5-0.8 0.8-1.3z"/>
			<path class="fill-orange" d="m32.4 3.8c-1.3 0.6-2.3 0.5-3.4 0.3 0.7-0.7 1.5-0.7 3.4-0.3z"/>
		</svg>
	</figure>

	<!-- SVG decoration -->
	<figure class="position-absolute top-0 end-0 me-5">
		<svg  width="38.4px" height="42.4px">
			<path class="fill-info" d="M23.5,32.8c2.7-1.6,5.1-3.1,7.4-4.5c0.6-0.4,1.2-0.3,1.5,0.3c1.8,4.1,3.7,8.1,4.7,12.5c0,0.1,0,0.1,0,0.2 c-0.6,0.8-1.4,0.7-2.1,0.2c-2.8-1.8-5.5-3.8-8.4-5.4c-4-2.3-6.6-6-9.5-9.4c-2.7-3.2-5.4-6.6-8-9.9c-0.8-1-1.5-1.9-1.7-3.2 C12.7,20,17.9,26.6,23.5,32.8z M30.9,29.3c-2.3,1.3-4.5,2.4-6.4,4.3c2.3,1.5,4.4,2.9,6.5,4.3c0.9-0.9,1.7-1.7,2.6-2.4 C32.7,33.4,31.9,31.5,30.9,29.3z M35,40.5c0-1.6-0.5-2.8-1.2-4.1c-0.7,0.8-1.2,1.4-1.9,2.2C33,39.3,33.9,39.8,35,40.5z"/>
			<path class="fill-info" d="M8,13c1.6-2,4.1-3,6-5c-0.4-0.4-0.8-0.8-1.1-1.1C11,8.3,9.3,9.4,7.6,10.7c-0.9,0.7-1.8,1.4-1.4,3.1 c-2.6-2.1-4.8-4-5.1-7.2C0.9,4.7,1.7,3.1,3.3,2c1.6-1.2,3.4-1.4,5.2-0.5c2.1,1.1,3.7,2.9,5.2,4.6c0.5,0.6,0.8,1.4,1.8,1.4 c0.2,0,0.4,0.3,0.5,0.4c4.8,6,9.7,12,13.6,18.6c0.2,0.3,0.3,0.7,0.5,1c-0.1,0.1-0.2,0.1-0.3,0.2c-0.4-0.5-0.9-0.9-1.2-1.4 c-1.9-2.6-3.6-5.4-5.5-7.9c-2.3-3-4.6-5.9-7-8.8c-1-1.2-1.1-1.2-2.4-0.1c-1.5,1.2-3.1,2.4-4.7,3.6c-0.2,0.2-0.5,0.2-0.8,0.3 C8.1,13.2,8,13.1,8,13z M12.1,5.8C10.7,4,9.5,3.1,8.2,2.4C6.5,1.6,4.9,1.7,3.4,3.1C2.2,4.2,1.7,5.6,2.2,7.3c0.5,1.8,1.5,3.2,3.1,4.3 C7.3,9.4,10.2,8.3,12.1,5.8z"/>
		</svg>
	</figure>

	<!-- SVG decoration -->
	<figure class="position-absolute top-50 end-0 translate-middle-y me-5 d-none d-lg-block">
		<svg width="81.3px" height="106.2px">
			<path class="fill-danger" d="M29.9,41.1c-1.7-0.8-2.4-1.5-2.7-2.8c-0.4-1.4-1.7-2.5-2.5-3.7c-4.4-6.7-6.9-14-6.6-22.1 c0.2-4.2,0.9-7.3,2.8-9.6c-0.3,1.9-0.5,3.8-0.9,5.6c-1.5,7.5,0.1,14.6,3.6,21.3c1.5,2.9,3.4,5.6,5.3,8.6 c9.3-4.1,14.3-11.3,16.7-20.6c0.7,2,0.3,3.7-0.3,5.4C42.8,29.9,38.3,35,32,38.6c-0.5,0.3-0.9,0.6-1.4,0.8c0.4,1.5,1.2,2,2.5,1.1 c2.5-1.8,5.1-3.6,7.5-5.5c3.2-2.4,6.5-4.3,10.6-4.8c5.9-0.7,10.7,1.2,15,5c4.7,4.2,7.4,9.7,9.3,15.5c3.2,9.7,3.2,19.6,0.7,29.5 c-0.5,1.8-1.3,3.5-2.1,5.2c-1.6,3.5-5,4.8-8.3,5.8c-1.3,0.4-3-0.1-4.4,0.2c-3.1,0.7-5.6,2.1-6.5,5.5c-0.4,1.7-1.9,2.8-3.4,3.6 c-5.3,2.7-10.9,2.9-16.4,1.1C23.5,98,14,91.3,7,81.1c-6.1-9-6.2-20.8-0.1-29.8c3.6-5.2,8.5-8.4,15-8.8c1.9-0.1,3.7-0.3,5.6-0.6 C28.2,41.9,28.7,41.6,29.9,41.1z M32.5,43.4c-1.5-0.1-2.6-0.3-3.8-0.3c-2.2,0.2-4.3,0.6-6.4,0.7c-5.4,0.4-9.8,2.6-13.1,6.8 c-6.7,8.5-7.3,20.1-1,29.5c5.7,8.6,13.6,14.5,23,18.5c5.4,2.3,10.9,3.6,16.7,1.8c2.8-0.9,5.5-1.9,6.3-5.3c0.5-1.8,1.8-3.1,3.5-3.8 c2.3-0.9,4.6-2,6.9-2.2c2.7-0.3,4.5-1.4,5.8-3.4c1.2-1.8,2.4-3.6,3.2-5.6c2.9-7.4,3.3-15,2-22.8c-1.2-7.1-3.7-13.6-8.4-19.1 c-7.1-8.2-17.4-8.7-24.7-2.8c-2.9,2.3-5.9,4.3-8.9,6.5C33.1,42.4,32.8,43.1,32.5,43.4z"/>
			<path class="fill-danger" d="M41.7,2.6c6.6,11.8,1.7,27-10.7,31.8c0.9-0.8,1.8-1.6,2.7-2.4c3.3-2.8,6.3-6,8-10c2.4-5.4,2-11.6-0.4-17 c-0.3,0.1-0.6,0-0.8,0.2c-5.8,4.6-11.1,9.5-13.4,16.9c-0.9,2.9-0.6,5.7,0.8,8.5c0.3,0.6,0.5,1.3,0.9,2.4c-2-0.8-2.5-2.1-2.9-3.4 c-1.3-3.7-0.9-7.3,0.9-10.6c1.2-2.4,2.7-4.7,4.4-6.9C34.1,8.5,37.7,5.5,41.7,2.6z"/>
		</svg>
	</figure>

	<div class="container">

		<!-- Title -->
		<div class="row">
			<!-- Title -->
			<div class="col-md-6">
				<h2>How we care for our students and build their confidence</h2>
			</div>
			<!-- Content and button -->
			<div class="col-md-6">
				<p>Confident students are more likely to speak in class, ask for help when needed. They absorb material faster and are more excited to learn. </p>
				<a href="#contact-form" class="btn btn-warning">Contact Us</a>
			</div>
		</div>

		<!-- Steps START -->
		<div class="row g-lg-5 mt-3">
			<!-- Item -->
			<div class="col-md-6 col-lg-4 text-center position-relative">
				<!-- SVG decoration -->
				<figure class="position-absolute top-0 start-100 translate-middle d-none d-lg-block">
					<svg width="182.9px" height="86px" viewBox="0 0 182.9 86">
						<path class="fill-secondary" d="M127.3,19.8c0.9,0.7,1.8,1.5,2.8,2c9.3,4.5,17.1,11.1,24.4,18.3c6.5,6.4,11.9,13.7,15.8,22 c1.5,3.2,2.7,6.6,4.2,10.2c2.5-4.2,4.1-8.9,8.6-11.5c-2.2,3.9-4.7,7.7-6.5,11.9c-1.7,4.1-2.6,8.6-3.9,13.4 c-4.1-6.1-7-13.2-14.9-15.6c3.8-1.4,6.2,0.5,14.1,10.3c1-2.2,1.8-4.1,1.1-6.5c-3.8-13.6-11.4-24.8-21.4-34.6 c-5.8-5.7-12.3-10.6-19.2-14.9c-7-4.3-14.4-8-22.2-10.9c-10.7-3.8-21.5-6.6-32.8-7.7C63.9,5,50.7,5.9,38,10.4 c-14.1,5-26,13.2-35,25.4c-0.5,0.7-1.2,1.4-1.8,2.1C1.1,38,0.8,38,0.4,38.1c-0.9-0.9-0.2-1.7,0.3-2.4c4.7-6.7,10.5-12.4,17.2-17.1 C31.7,8.9,47.2,4.7,63.8,4C77.1,3.5,90,5.8,102.7,9.3c2.2,0.6,4.3,1.8,6.5,2.6c0.9,0.4,2,0.5,2.9,0.7 C117.1,15,122.2,17.4,127.3,19.8z"/>
					</svg>
				</figure>

				<div class="px-4">
					<!-- Image -->
					<div class="icon-xxl bg-body shadow mx-auto rounded-circle mb-3">
						<img src="assets/images/element/child.svg" alt="">
					</div>
					<!-- Title -->
					<h5>We care about kids</h5>
					<p class="text-truncate-2 pb-0">
					    Children who receive quality early childhood education are better prepared to learn, more likely to read by the fourth grade, graduate school, 
					</p>
				</div>	
			</div>

			<!-- Item -->
			<div class="col-md-6 col-lg-4 text-center pt-0 pt-lg-5 position-relative">
				<!-- SVG decoration -->
				<figure class="position-absolute top-100 start-100 translate-middle mt-n3 d-none d-lg-block">
					<svg width="182.9px" height="86px" viewBox="0 0 182.9 86">
						<path class="fill-secondary" d="M127.3,70.2c0.9-0.7,1.8-1.5,2.8-2c9.3-4.5,17.1-11.1,24.4-18.3c6.5-6.4,11.9-13.7,15.8-22 c1.5-3.2,2.7-6.6,4.2-10.2c2.5,4.2,4.1,8.9,8.6,11.5c-2.2-3.9-4.7-7.7-6.5-11.9c-1.7-4.1-2.6-8.6-3.9-13.4 c-4.1,6.1-7,13.2-14.9,15.6c3.8,1.4,6.2-0.5,14.1-10.3c1,2.2,1.8,4.1,1.1,6.5c-3.8,13.6-11.4,24.8-21.4,34.6 c-5.8,5.7-12.3,10.6-19.2,14.9c-7,4.3-14.4,8-22.2,10.9c-10.7,3.8-21.5,6.6-32.8,7.7C63.9,85,50.7,84.1,38,79.6 c-14.1-5-26-13.2-35-25.4c-0.5-0.7-1.2-1.4-1.8-2.1c-0.1-0.1-0.4-0.1-0.8-0.2c-0.9,0.9-0.2,1.7,0.3,2.4c4.7,6.7,10.5,12.4,17.2,17.1 c13.7,9.7,29.2,14,45.9,14.6c13.3,0.5,26.2-1.8,38.8-5.3c2.2-0.6,4.3-1.8,6.5-2.6c0.9-0.4,2-0.5,2.9-0.7 C117.1,74.9,122.2,72.6,127.3,70.2z"/>
					</svg>
				</figure>

				<div class="px-4">
					<!-- Image -->
					<div class="icon-xxl bg-body shadow mx-auto rounded-circle mb-3">
						<img src="assets/images/element/idea.svg" alt="">
					</div>
					<!-- Title -->
					<h5>Building life-long learners</h5>
					<p class="text-truncate-2 pb-0">Lifelong learning is a form of self-initiated education that is focused on personal development.</p>
				</div>
			</div>

			<!-- Item -->
			<div class="col-md-6 col-lg-4 text-center">
				<div class="px-4">
					<!-- Image -->
					<div class="icon-xxl bg-body shadow mx-auto rounded-circle mb-3">
						<img src="assets/images/element/help.svg" alt="">
					</div>
					<!-- Title -->
					<h5>Helping struggling students</h5>
					<p class="text-truncate-2 pb-0">
When it comes to getting academic or behavioral help for students, nearly all schools have academic policies and guidelines for obtaining assistance beyond the classroom..</p>
				</div>	
			</div>
		</div>
		<!-- Steps END -->
	</div>
</section>
<!-- =======================
Steps END -->

<!-- =======================
Action box START -->
<section class="py-0 pt-lg-5">
	<div class="container">
		<div class="row">
			<div class="col-12 position-relative z-index-1">
				<!-- Image -->
				<div class="d-none d-lg-block position-absolute bottom-0 start-0 ms-3 ms-xl-5">
					<img src="assets/images/element/01.png" alt="">
				</div>
				<!-- Pencil and cap SVG -->
				<div class="position-absolute top-0 end-0 mt-n4 me-5">
					<img src="assets/images/client/pencil.svg" alt="">	
				</div>
				<div class="position-absolute bottom-0 start-50 mb-n4">
					<img src="assets/images/client/graduated.svg" class="rotate-74" alt="">	
				</div>

				<div class="bg-grad-pink p-4 p-sm-5 rounded position-relative z-index-n1 overflow-hidden">
					<!-- SVG decoration -->
					<figure class="position-absolute top-0 start-0 mt-3 ms-n3 opacity-5">
						<svg width="818.6px" height="235.1px" viewBox="0 0 818.6 235.1">
							<path class="fill-white" d="M735,226.3c-5.7,0.6-11.5,1.1-17.2,1.7c-66.2,6.8-134.7,13.7-192.6-16.6c-34.6-18.1-61.4-47.9-87.3-76.7 c-21.4-23.8-43.6-48.5-70.2-66.7c-53.2-36.4-121.6-44.8-175.1-48c-13.6-0.8-27.5-1.4-40.9-1.9c-46.9-1.9-95.4-3.9-141.2-16.5 C8.3,1.2,6.2,0.6,4.2,0H0c3.3,1,6.6,2,10,3c46,12.5,94.5,14.6,141.5,16.5c13.4,0.6,27.3,1.1,40.8,1.9 c53.4,3.2,121.5,11.5,174.5,47.7c26.5,18.1,48.6,42.7,70,66.5c26,28.9,52.9,58.8,87.7,76.9c58.3,30.5,127,23.5,193.3,16.7 c5.8-0.6,11.5-1.2,17.2-1.7c26.2-2.6,55-4.2,83.5-2.2v-1.2C790,222,761.2,223.7,735,226.3z"></path>
						</svg>
					</figure>
					<!-- SVG decoration -->
					<figure class="position-absolute top-50 start-0 translate-middle-y ms-5">
						<svg width="473px" height="234px">
							<path fill-rule="evenodd" opacity="0.051" fill="rgb(255, 255, 255)" d="M0.004,222.303 L364.497,-0.004 L472.998,32.563 L100.551,233.991 L0.004,222.303 Z"/>
						</svg>
					</figure>
					<!-- SVG decoration -->
					<figure class="position-absolute top-50 end-0 translate-middle-y">
						<svg  width="355.6px" height="396.1px">
							<path class="fill-danger rotate-10" d="M32.8,364.1c16.1-14.7,36-21.5,56.8-26.7c20-5.1,40.5-9.7,57.8-21.4c35.7-24.3,51.1-68.5,57.2-109.4 c6.8-45.7,4.6-93.7,21.6-137.5c8.3-21.4,22.3-41.4,43.3-51.9c17.4-8.7,36.2-7.9,54.2-1.5c10.2,3.6,19.8,8.5,29.4,13.5l2.5-4.3 c-2.7-1.4-5.4-2.8-8.2-4.2c-15.8-8-32.9-15.3-50.9-15.2C276,5.6,256.9,16,243.3,31c-16.6,18.3-25.3,42.2-30.5,66 c-5,22.9-6.8,46.3-8.8,69.6c-3.9,44.4-9.7,92.8-40.1,128c-7.1,8.2-15.4,15.4-24.9,20.8c-9.3,5.4-19.5,8.9-29.8,11.8 c-20.2,5.7-41.3,9.1-59.9,19.2c-19.3,10.4-35.1,27.2-44.2,47.1c0,0,0,0.1,0,0.1l4.4,2.6C15,384,22.9,373.1,32.8,364.1z"/>
						</svg>
					</figure>
					
					<div class="row g-3 align-items-center justify-content-lg-end position-relative py-4">
						<!-- Title -->
						<div class="col-md-6">
							<h2 class="text-white">Become an Instructor!</h2>
							<p class="text-white mb-0">Teach thousands of students and earn money with ease!</p>
						</div>
						<!-- Button -->
						<div class="col-md-6 col-lg-3 text-md-end">
							<a href="https://wa.me/+917510116655" class="btn btn-white mb-0">Register!</a>
						</div>		
					</div> <!-- Row END -->
				</div>
			</div>
		</div>
	</div>
</section>
<!-- =======================
Action box END -->


<!-- =======================
TEAM MEMBERS

-->

<section id="testimonial-area mt-3">
    <div class="container">
        <div class="row">
          
            <div class="col-md-8 offset-md-2">
                <div class="section-heading text-center">
                    <h2>Our Leading Team  </h2>
                </div>
            </div>
         
        </div>
        <div class="our-teams">
            <div class="row d-flex justify-content-center">
                {{--
                <div class="col-md-3 col-6">
                    <div class="our-team">
                        <div class="pic">
                            <img src="https://lh3.googleusercontent.com/a-/AD_cMMSn0-MifNxY0kBpEElu6fkMoVNWgtkF5nLr4jD0JudMUNw=s288-p-rw-no" alt="asif-t">
                        </div>
                        <h3 class="title">Asif Thuluvath</h3>
                        <span class="post">CEO,MD <br></span>
                        <ul class="social">
                            <li><a  target="_bew" href="https://www.facebook.com/asifthuluvatht/" class="fab fa-facebook"></a></li>
                            <li><a  target="_bew" href="https://www.instagram.com/asif_thuluvath" class="fab fa-instagram"></a></li>
                        </ul>
                    </div>
                </div>
        --}}
                <div class="col-md-3 col-8">
                    <div class="our-team">
                        <div class="pic">
                            <img src="https://lh3.googleusercontent.com/a-/ACNPEu-eo4q5aOfblx962Uq3yGcdcSApcPsnN-HbwfVEKA=s288-p-rw-no" alt="shefii">
                        </div>
                        <h3 class="title">Mohammed Shafeeque</h3>
                        <span class="post">Software Developer</span>
                        <ul class="social">
                            <li><a target="_bew1"  href="https://www.instagram.com/shefii_02/" class="fab fa-instagram"></a></li>
                            <li><a target="_bew"  href="http://www.shefii.vseller.online" class="bi bi-globe2"></a></li>
                        </ul>
                    </div>
                </div>
        
                <div class="col-md-3 col-6">
                    <div class="our-team">
                        <div class="pic">
                            <img src="https://lh3.googleusercontent.com/a-/ACNPEu_0mKL8HuDQCU5V3kcwdyMTWVW_1TJnWSjEZ9x51Q=s288-p-rw-no" alt="remil-img">
                        </div>
                        <h3 class="title">Remil</h3>
                        <span class="post">Graphic Designer</span>
                        
                    </div>
                </div>
        
                <div class="col-md-3 col-6">
                    <div class="our-team">
                        <div class="pic">
                            <img src="/images/navas.jpeg" alt="ajax-img">
                        </div>
                        <h3 class="title">Navas</h3>
                        <span class="post">Content Writer</span>
                        
                    </div>
                </div>
            </div>
        </div>
       
    </div>
</section>
  <!-- END TEAM MEMBERS-->


<!-- =======================
Contact form START -->
<section id="contact-form" class="position-relative overflow-hidden pt-0 pt-md-5 mt-3">

	<!-- SVG decoration -->
	<figure class="position-absolute top-0 start-0 ms-n7 d-none d-xl-block">
		<svg enable-background="new 0 0 253.7 138">
			<path class="fill-info" d="m96.5 118.2c0 0.2 0.1 0.4 0.1 0.5l3-0.9c0-0.1-0.1-0.3-0.1-0.4-1 0.3-2 0.5-3 0.8m112-114v-0.4c-5.4-0.3-10.7-0.5-16.1-0.8v0.5c5.4 0.2 10.8 0.5 16.1 0.7m-87.7 89.2c23.1-3.7 45.7-8.8 67.4-17.7-22 7.6-44.5 13.4-67.4 17.7m-7.5-7.7c0 0.2 0.1 0.3 0.1 0.5 5.3-1 10.6-1.9 15.8-3 13.9-2.8 27.8-5.8 41.4-9.7 5.5-1.6 11-3.4 16.4-5.2 2.8-0.9 5.5-2 8.2-3 1.1-0.4 2.1-0.7 3.2-1.1-0.1-0.2-0.1-0.4-0.2-0.6-27.5 9.9-56 16.8-84.9 22.1m74.2-0.4c-0.1-0.2-0.2-0.3-0.3-0.5-2.1 1.1-4.2 2.4-6.3 3.4-5 2.4-10 4.7-15.1 6.9-6.1 2.6-12.2 5-18.3 7.3-1 0.4-2 1.5-3.2 0.3 0 0-0.4 0.3-0.6 0.4-0.7 0.5-1.3 1.2-2 1.4-11.5 3.6-23.1 7.2-34.6 10.8l-5.1 1.5c1 0.1 1.9 0 2.8-0.3 12.3-3.7 24.6-7.4 36.9-11.2 1.2-0.4 1.8-0.3 1.6 1.2-0.1 0.5 0.3 1 0.4 1.5 0.3-0.5 0.6-0.9 0.8-1.4 0.5-1.1 0.3-2.4 1.9-3 6.2-2.3 12.4-4.7 18.5-7.2 4.1-1.7 8-3.6 12-5.5 2.6-1.2 5.2-2.4 7.7-3.7 0.9-0.4 1.9-1.2 2.9-1.9m-162.4-10.9c0.1 0.1 0.1 0.3 0.2 0.4 2.3-1.5 4.7-3.1 7-4.5 8.5-5.1 17.2-9.6 26.5-13.1 8.1-3.1 16.3-6.2 24.6-8.5 10.5-2.9 21.2-5.2 31.8-7.4 4.5-1 9.1-1.2 13.6-1.8 2.7-0.3 5.3-0.6 8-0.9s5.4-0.5 8.1-0.8c-2.1-0.2-4.2-0.4-6.2-0.2-6.2 0.7-12.3 1.6-18.5 2.5-16.1 2.4-32 5.7-47.3 11.2-9.1 3.2-18 6.9-26.9 10.7-7.5 3.1-14.5 7.2-20.9 12.4m135.8-71.8c-24.3-0.3-62.8 5.2-107 27.8-11.3 5.6-21.9 12.4-31.7 20.4 41.8-29.7 88.1-46.3 138.7-48.2m64.4 8.6c-0.3 0.3-0.6 0.8-0.8 0.8-2.4-0.3-4.7-0.7-7.1-1.1-0.4-0.1-0.9 0-1.4 0 0.3 0.3 0.5 0.7 0.9 1 2.1 1.4 4.3 2.6 6.3 4.1 6.9 4.9 11.1 11.5 12.3 19.8 0.3 2.3 0 4.6 0 7.4 5-3.7 9-7.5 11.4-12.8 1.1-2.6 1.3-5.4-0.5-7.9-3.8-5-9.4-6.7-14.9-8.8-2.1-0.8-4.7-0.4-6.2-2.5m-98.8 33.2c0.3-0.4 0.6-0.8 1-1.2-0.5-0.1-0.8-0.2-1.1-0.1-0.7 0.2-1.3 0.5-2 0.7 0.8 0.2 1.5 0.4 2.1 0.6-0.4 0.3-0.8 0.8-1.3 0.9-0.9 0.1-1.9-0.3-2.7-0.1-1.3 0.3-2.4 1-3.7 1.3-9.6 2-19.3 3.8-29 6-3.8 0.9-7.5 2.3-11.1 3.7-6.1 2.3-12.7 3.6-18.7 6.6-0.5 0.3-1.2 0.2-1.7 0.4-1.9 0.9-3.9 1.8-5.8 2.7-1.8 0.9-3.4 2-5.2 2.8-1.7 0.8-3.6 1.4-5.3 2.1-4.7 2-8.5 5.4-12.4 8.5-2.2 1.7-4 3.9-5.9 6-1 1.1-1.7 2.4-2.5 3.7-0.8 1.4-0.1 2.1 1.3 2.3 4.1 0.5 8.1 1.2 12.2 1.2 19.1 0 38-2.7 56.9-5.5 10.6-1.6 21-3.5 31.5-5.5 17.3-3.3 34.6-7.1 51.5-12.1 11.9-3.5 23.7-7.4 35.2-12.1 1.5-0.6 1.3-1.2 0.4-2.1-2.9-3.2-6.6-5.2-10.5-6.6-4.9-1.8-10.2-2.6-15.3-3.8-0.4-0.1-0.6-0.6-0.9-0.9-0.4-0.3-0.9-0.6-1.3-0.9-0.2 0.2-0.3 0.4-0.5 0.6 0.5 0.1 0.9 0.2 1.4 0.3-0.1 0.1-0.2 0.3-0.3 0.4-0.9-0.2-1.9-0.6-2.7-0.4-2.7 0.4-5.4-0.5-8.1-0.5-4.6 0-9.2-0.4-13.8-0.7l-3.6-0.3c-1.1 0-2.2 0.2-3.2 0.2-0.8 0.1-1.6 0.1-2.4 0.2s-1.7 0.5-2.2 0.2c-1.5-0.9-2.8-0.3-4.2 0-1.2 0.3-2.3 0.8-3-0.7-0.1-0.3-0.7-0.3-1-0.4 0 0-0.3 0.3-0.3 0.4 0.7 1.4-0.5 0.8-0.9 0.9-0.6 0.2-1.2 0.2-1.8 0.3-1 0.2-2 0.3-3 0.4-1.2 0.2-2.6 0-3.6 0.5-1.1 0.9-1.7 0.5-2.5 0m-77.3 0.7c-2 0.9-4.2 1.7-6.1 2.8-9 4.9-17.2 11-24.5 18.1-2.5 2.5-4.5 5.6-6.7 8.4-2.8 3.4-4.3 7.3-4.5 11.6-0.1 1.3 0.2 2.9 1 3.8 1.8 2 4.2 3.2 7.1 3.9-2-3.6-2.8-7.1-0.7-10.6 1.4-2.4 3.2-4.7 5.2-6.6 6.1-5.9 13.4-10 20.9-13.9 11.7-6.1 24-10.8 36.5-14.7 16.3-5.1 32.9-8.8 49.8-10.6 5.9-0.6 11.8-1.1 17.7-1.5 12-0.8 23.9-0.3 35.8 1.5 6.5 1 12.8 2.3 19 4.7 6.3 2.5 11.9 5.4 15 11.8 0.4 0.8 0.8 1 1.7 0.6 2-1.1 4.1-2 6.1-3.1 1.6-0.9 3.5-1.7 4.7-3 2.6-3 4.2-6.6 4.5-10.7 0.2-3.2-0.3-6.4-2.2-8.9-2.2-3-4.5-6.2-8-7.8-1.7-0.8-2.4-3.1-4.6-2.9-0.2 0-0.4-0.6-0.6-0.9-0.7-0.9-2.3-0.6-2.7-1.8-0.1 0-0.2 0.1-0.3 0.1-0.5 0.1-1 0.3-1.4 0.2-4.9-1.7-9.8-3.6-15-4.6-4.7-1-9.4-1.5-14.3-1.2-3 0.2-6.1 1.2-9.2 0.1-0.6-0.2-1.5 0.7-2.3 0.8-5.3 0.7-10.6 1.4-15.9 1.8-5.2 0.4-10.2 2.4-15.5 1.8-1.2-0.1-2.9-0.2-4 1.3-0.5 0.6-2-0.2-3.2 0.4-1.3 0.7-3.1 0.4-4.6 0.6-0.1 0-0.1 0.1-0.2 0.1-3.4 0.8-6.8 2.1-10.1 3-12.8 3.6-25.5 7.4-37.9 12.2-2.8 1.1-5.5 2.3-8.4 3.2-3.8 1.2-7.7 1.9-11.1 4.3-1.3 0.9-2.9 1.2-4.4 1.9-0.6 0.2-1 0.7-1.6 1-1.9 1-3.4 1.9-5 2.8m114 46c-0.5 0.3-0.9 0.7-1.4 0.9-8.8 2.1-17.5 4.5-26.4 6.2-11.3 2.2-22.7 3.9-34 5.7-2.9 0.5-5.8 0.5-8.7 0.8-1.8 0.2-3.6 0.7-5.5 0.9-4.8 0.5-9.6 0.9-14.3 1.3-0.8 0.1-1.7-0.3-2.5-0.3-0.5 0-1 0.6-1.5 0.6-4.5 0.1-9 0.3-13.5 0.3-6.1-0.1-12.2 0-18.3-0.6-9.3-0.8-18.5-2.3-26.3-7.8-5.3-3.6-9.8-7.7-10.6-14.6-0.6-4.9 0.7-9.4 3.3-13.4 2-3 4.4-5.8 6.5-8.8 0.6-0.8 0.8-1.9 1.4-2.7 0.9-1.3 1.7-2.7 2.8-3.8 6.3-6 13.3-11.2 20.6-15.8 5.4-3.4 11-6.8 16.7-9.8 18.2-9.6 37.1-17.4 57.1-22.4 13.4-3.3 26.9-5.8 40.7-6.6 8.3-0.5 16.7-0.1 25-0.1 6.7 0 13.5 0.2 20.2 0.2 12.4 0.1 24.6 1.5 36.5 5.1 5.9 1.8 11.5 4.4 15.9 9 5.1 5.3 6.7 14.6 3.4 21.2-2.7 5.3-6.5 9.7-11.4 13.2-2.6 1.8-5.3 3.5-7.7 5.5-2 1.7-3.6 4-5.7 5.7-3.2 2.7-6.6 5.2-9.7 7.9-1 0.9-1.6 2.2-2.5 3.2-2.5 2.8-4.8 5.8-7.6 8.1-4.4 3.5-9.1 6.6-13.8 9.8-2.5 1.7-5.2 3.1-7.8 4.6-1.9 0.1-3.5 0.8-4.6 2.4-3 1.4-6.1 2.8-9.1 4.1-0.7 0.3-1.4 0.5-2 0.7-2.4 1-4.9 2-7.4 2.9-0.8 0.5-1.6 1.3-2.4 1.6-17.9 6.8-36.1 12.5-54.8 17-11.7 2.8-23.4 5.8-35.3 8.2-10.4 2.1-20.9 3.3-31.4 4.9-1.7 0.3-3.4 0.1-5.1 0.2-2 0.1-3.6-0.5-5.1-1.8-0.2-0.2-0.4-0.3-0.6-0.4-0.5-0.3-1.1-0.5-1.6-0.8 0.5-0.3 0.9-0.7 1.5-1.1-0.1-0.1-0.2-0.2-0.4-0.3-0.5-0.3-1.1-0.5-1.6-0.8 0.6-0.4 1.2-1 1.8-1 21-1.4 41.2-6.9 61.4-12 23.7-6 47-13 69.8-21.9 5.2-2 10.4-4.3 15.6-6.6 3.7-1.7 7.4-3.7 10.9-5.9-1.8 0.6-3.7 1.3-5.5 1.9-3.9 1.2-7.8 2.4-11.7 3.7-0.5 0.2-1 0.6-1.4 0.9-0.8 0.3-1.3 0.5-1.9 0.6"/>
		</svg>
	</figure>

	<div class="container">
		<div class="row g-4 g-sm-5 align-items-center">
			<div class="col-lg-6">
				<h2 class="position-relative">Let Us Help You!!</h2>
				

				<div class="row mt-5">
					<!-- Email box -->
					<div class="col-sm-6 col-lg-12 col-xl-6 mb-5">
						<div class="card card-body shadow">
							<div class="icon-lg bg-warning text-white rounded-circle position-absolute top-0 start-100 translate-middle ms-n6">
								<i class="fas fa-envelope"></i>
							</div>
							<h6>Email</h6>
							<p class="mb-0"><a target="_new" href="mailto:pachavellamedu@gmail.com" class="h6 mb-0 fw-light stretched-link">pachavellamedu@gmail.com</a></p>
						</div>
					</div>

					<!-- Live support box -->
					<div class="col-sm-6 col-lg-12 col-xl-6 mb-5">
						<div class="card card-body shadow">
							<div class="icon-lg bg-success text-white rounded-circle position-absolute top-0 start-100 translate-middle ms-n6">
								<i class="bi bi-whatsapp"></i>
							</div>
							<h6>WhatsApp Number</h6>
							<p class="mb-0"><a target="_new" href="https://wa.me/+917510116655" class="h6 mb-0 fw-light stretched-link">+91 75101 16655</a></p>
						</div>
					</div>

					<!-- Telephone box -->
					<div class="col-sm-6 col-lg-12 col-xl-6  mb-5">
						<div class="card card-body shadow">
							<div class="icon-lg bg-danger text-white rounded-circle position-absolute top-0 start-100 translate-middle ms-n6">
								<i class="bi bi-youtube"></i>
							</div>
							<h6>Youtube</h6>
							<p class="mb-0"><a target="_new" href="https://www.youtube.com/channel/UC4zHCVXsUF-Z1IKtENE4Hxw" class="h6 mb-0 fw-light stretched-link">Follow Now</a></p>
						</div>
					</div>

                    
					<!-- Facebook box -->
					<div class="col-sm-6 col-lg-12 col-xl-6 mb-5 mb-xl-0">
						<div class="card card-body shadow">
							<div class="icon-lg bg-info text-white rounded-circle position-absolute top-0 start-100 translate-middle ms-n6">
								<i class="bi bi-facebook"></i>
							</div>
							<h6>Facebook</h6>
							<p class="mb-0"><a target="_new" href="https://www.facebook.com/pachavellamedu" class="h6 mb-0 fw-light stretched-link">Follow Now</a></p>
						</div>
					</div>
                    
					<!-- Instagram box -->
					<div class="col-sm-6 col-lg-12 col-xl-6 mb-5 mb-xl-0">
						<div class="card card-body shadow">
							<div class="icon-lg bg-danger text-white rounded-circle position-absolute top-0 start-100 translate-middle ms-n6">
								<i class="bi bi-instagram"></i>
							</div>
							<h6>Instagram</h6>
							<p class="mb-0"><a target="_new" href="https://www.instagram.com/pachavellam_/" class="h6 mb-0 fw-light stretched-link">Follow Now</a></p>
						</div>
					</div>
					
					<!-- Website box -->
					<div class="col-sm-6 col-lg-12 col-xl-6 ">
						<div class="card card-body shadow">
							<div class="icon-lg bg-purple text-white rounded-circle position-absolute top-0 start-100 translate-middle ms-n6">
								<i class="fas fa-map-marker"></i>
							</div>
							<h6>Location</h6>
							<p class="mb-0"><a target="_new" href="https://goo.gl/maps/bJVmKdVqq7zts4c58" class="h6 mb-0 fw-light stretched-link">1st Floor, Ms Tower, Jubilee Road, near Passport Office, Moonampadi, Malappuram, Kerala 676505</a></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Contact form START -->
			<div class="col-lg-6">
				<div class="card card-body shadow p-4 p-sm-5 position-relative">
					<!-- SVG decoration -->
					<figure class="position-absolute top-0 start-100 mt-n5 ms-n7">
						<svg enable-background="new 0 0 167 107">
							<path class="fill-danger" d="m87.1 1c-0.4 0.5-0.8 1-1.3 1.5l-3 2.7c-2.6 2.3-5.1 4.7-7.8 6.8-13.4 10.5-26.8 21-40.1 31.5l-25.8 20.4c-0.4 0.3-0.8 0.6-1.1 0.9-0.7 0.6-1.5 1-2.4 0.2-0.8-0.7-0.6-1.7-0.1-2.4 0.6-1 1.4-2 2.2-2.8 0.5-0.4 1-0.9 1.5-1.3 2.8-2.6 5.7-5.2 8.6-7.5 21.6-16.6 43.3-33.1 65.8-48.5 1.2-0.9 2.5-1.7 3.8-2.5 0 0 0.1 0.1 0.4 0.2-0.2 0.3-0.5 0.6-0.7 0.8zm78.9 20.9c-0.4 0.2-0.7 0.4-1.1 0.6-0.3 0.2-0.7 0.5-1.1 0.7-14.6 8.6-29 17.5-43.1 27-21.6 14.4-43 29.2-63.4 45.2-3.8 3-7.5 6-11.2 9-0.6 0.5-1.1 0.9-1.7 1.3-0.8 0.6-1.6 0.9-2.4 0.2s-0.6-1.7-0.1-2.4c0.6-1 1.3-2 2.2-2.8l0.1-0.1c2.5-2.3 5-4.6 7.7-6.6 30.4-23 61.6-44.5 94.9-63 3.8-2.1 7.7-4.1 11.6-6 1.9-1 3.9-2 5.8-3 0.5-0.2 1-0.4 1.4-0.6 0.2 0.1 0.3 0.3 0.4 0.5zm-66.1-13.4c0.7-0.5 1.3-1.1 1.9-1.7-0.1-0.1-0.2-0.2-0.5-0.3-0.7 0.5-1.4 1-2.1 1.6-0.7 0.5-1.4 1.1-2.1 1.6-4 2.9-8.1 5.8-12.1 8.7-19.3 13.8-38.6 27.7-57.8 41.8-5.4 3.9-10.5 8.1-15.6 12.3-2.1 1.7-4.2 3.5-6.3 5.2-1.5 1.2-2.8 2.6-4.1 4-0.5 0.5-1 1.1-1.2 1.8-0.1 0.5 0.1 1.2 0.4 1.5s1.1 0.4 1.5 0.2c0.8-0.4 1.5-0.9 2.2-1.5l7.2-6c4.2-3.6 8.5-7.1 12.8-10.5 10.6-8.2 21.3-16.4 31.9-24.5l23.4-18c6.9-5.4 13.7-10.8 20.5-16.2zm0.5 13.5c-1.1 1-2.2 2-3.4 2.9-3.3 2.6-6.7 5.2-10 7.8-11 8.5-22 17-32.9 25.6-6.4 5.1-12.8 10.3-19.1 15.4-3.5 2.8-7 5.7-10.5 8.5-0.8 0.7-1.6 1.4-2.5 1.9-0.5 0.3-1.6 0.3-1.9 0-0.4-0.4-0.5-1.4-0.2-1.9 0.4-0.8 1-1.6 1.7-2.3 0.7-0.6 1.4-1.3 2.1-1.9 1.7-1.6 3.4-3.2 5.2-4.7 20-15.8 40.2-31.3 61.3-45.6 2.3-1.6 4.7-3.1 7.1-4.6 0.5-0.3 1-0.7 1.5-1 0.4-0.2 0.8-0.4 1.2-0.7 0.1 0.1 0.1 0.2 0.2 0.3s0.2 0.2 0.2 0.3zm7 13.4 0.6-0.6c-0.3-0.2-0.4-0.3-0.4-0.3-1.5 1.1-3 2.2-4.5 3.2-16.7 11.1-32.8 23-48.7 35.1-4.7 3.5-9.3 7.1-13.9 10.7-0.9 0.7-1.7 1.5-2.4 2.3-0.6 0.7-0.9 1.6-0.2 2.4 0.7 0.9 1.6 0.8 2.4 0.3 1.1-0.6 2.2-1.3 3.2-2.1 1.8-1.4 3.5-2.8 5.2-4.3 1.7-1.4 3.5-2.8 5.2-4.3 12.1-9.5 24.3-19 36.5-28.4l15-12c0.6-0.4 1.3-1.2 2-2z"/>
						</svg>
					</figure>

					<!-- Form START -->
					<form class="row g-3 position-relative">
						<!-- Name -->
						<div class="col-md-6 col-lg-12 col-xl-6">
							<label class="form-label">First name *</label>
							<input type="text" class="form-control" aria-label="First name">
						</div>

						<!-- Last name -->
						<div class="col-md-6 col-lg-12 col-xl-6">
							<label class="form-label">Last name *</label>
							<input type="text" class="form-control" aria-label="Last name">
						</div>
						<!-- Email -->
						<div class="col-md-6 col-lg-12 col-xl-6">
							<label class="form-label">Email *</label>
							<input type="email" class="form-control">
						</div>
						<!-- Phone number -->
						<div class="col-md-6 col-lg-12 col-xl-6">
							<label class="form-label">Phone number *</label>
							<input type="text" class="form-control">
						</div>
						
						<!-- Comment -->
						<div class="col-12">
							<label class="form-label">Request Message *</label>
							<textarea class="form-control" rows="3"></textarea>
						</div>
						<!-- Button -->
						<div class="col-12">
							<button type="submit" class="btn btn-primary mb-0">Send message</button>
						</div>
					</form>
					<!-- Form END -->
				</div>
			</div>
			<!-- Contact form END -->
		</div>
	</div>
</section>
<!-- =======================
Contact form END -->

</main>

@endsection