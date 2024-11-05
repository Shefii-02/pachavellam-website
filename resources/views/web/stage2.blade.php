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
<section style="background-color: #b5bcb86b;">
	<div class="container">
		<div class="row g-5 justify-content-between">
			<!-- Admission form START -->
			<div class="col-md-8 mx-auto">
                @if(\Auth::user()->hasRole('Teacher'))
                    <div class="card">
                        <div class="card-body">		
                            <form action="/stage2" method="POST">
                               @csrf()
                                <!-- Teachers -->
                                <h5 class="mt-3">Teaching  Details</h5>
                                <!-- Subjects  -->
                                <div class="col-12 mt-4">
                                    <div class="row g-xl-0 align-items-center">
                                        <div class="col-lg-4">
                                            <h6 class="mb-lg-0">Subjects <span class="text-danger">*</span> <br><small>
                                                (Use a comma if there is more than one)</small></h6>
                                        </div>
                                        <div class="col-lg-8">
                                            <textarea class="form-control" id="subjects" name="subjects">

                                            </textarea>

                                        </div>
                                    </div>
                                </div>

                                <!-- Type -->
                                <div class="col-12 mt-4">
                                    <div class="row g-xl-0 align-items-center">
                                        <div class="col-lg-4">
                                            <h6 class="mb-lg-0">Preferable Working <span class="text-danger">*</span></h6>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="d-flex">
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" value="online" type="radio" name="Preferable_working" id="preferableonline" checked>
                                                    <label class="form-check-label" for="preferableonline">
                                                        Online
                                                    </label>
                                                </div>
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" value="offline" type="radio" name="Preferable_working" id="preferableoffline">
                                                    <label class="form-check-label" for="preferableoffline">
                                                        Offline
                                                    </label>
                                                </div>
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" value="both" type="radio" name="Preferable_working" id="preferableboth">
                                                    <label class="form-check-label" for="preferableboth">
                                                        Both
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4"></div>
                                        <div class="col-lg-8">
                                            <div id="preferable_desc" class="preferable_desc " style="display: block;">
                                                <div class="col-12">
                                                    <p class="mb-lg-0">(Do you have working facility?)</p>
                                                            
                                                </div>
                                                <div class="d-flex mt-3">
                                                    
                                                    <div class="form-check me-4">
                                                        <input class="form-check-input" type="checkbox" id="facility" name="facility" value="laptop" >
                                                        <label class="form-check-label">Laptop</label>
                                                    </div>
                                                    <div class="form-check me-4">
                                                        <input class="form-check-input" type="checkbox" id="facility" name="facility" value="highspeed-internet" >
                                                        <label class="form-check-label">Highspeed Internet</label>
                                                    </div>
                                                    <div class="form-check me-4">
                                                        <input class="form-check-input" type="checkbox" id="facility" name="facility" value="mic-camera" >
                                                        <label class="form-check-label">Quality Mic & Camera</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Board of university -->
                                <div class="col-12 mt-4">
                                    <div class="row g-xl-0 align-items-center">
                                        <div class="col-lg-4">
                                            <h6 class="mb-lg-0">How many years of experience Online </h6>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="exp_online" name="exp_online">
                                        </div>
                                    </div>
                                </div>
                                <!-- Board of university -->
                                <div class="col-12 mt-4">
                                    <div class="row g-xl-0 align-items-center">
                                        <div class="col-lg-4">
                                            <h6 class="mb-lg-0">How many years of experience Offline </h6>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="exp_offline" name="exp_offline">
                                        </div>
                                    </div>
                                </div>
                                <!-- Board of university -->
                                <div class="col-12 mt-4">
                                    <div class="row g-xl-0 align-items-center">
                                        <div class="col-lg-4">
                                            <h6 class="mb-lg-0">Are you ready to work without payment for poor student <span class="text-danger">*</span></h6>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="d-flex">
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" type="radio" value="yes" name="without_payment" id="without_payment_yes" >
                                                    <label class="form-check-label" for="without_payment_yes">
                                                        Yes
                                                    </label>
                                                </div>
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" type="radio" value="no" name="without_payment" id="without_payment_no" checked>
                                                    <label class="form-check-label" for="without_payment_no">
                                                        No
                                                    </label>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Type -->
                                <div class="col-12 mt-4">
                                    <div class="row g-xl-0 align-items-center">
                                        <div class="col-lg-4">
                                            <h6 class="mb-lg-0">Ready for home tution <span class="text-danger">*</span></h6>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="d-flex">
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" value="yes" type="radio" name="home_tution" id="hometutionyes" checked>
                                                    <label class="form-check-label" for="hometutionyes">
                                                        Yes
                                                    </label>
                                                </div>
                                                <div class="form-check radio-bg-light me-4">
                                                    <input class="form-check-input" value="no" type="radio" name="home_tution" id="hometutionno">
                                                    <label class="form-check-label" for="hometutionno">
                                                        No
                                                    </label>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Button -->
                                <div class="col-12 text-sm-end mt-4">
                                    <button type="submit" class="btn btn-primary mb-0">Submit</button>
                                </div> 
                            </form>
                        </div>
                    </div>
                @endif     
                @if(\Auth::user()->hasRole('Student'))
					<div class="card">
                        <div class="card-body">		
                            <form action="/stage2" method="POST">
                                @csrf()
                                    <!-- Student/Proffesional  -->
                                <h5 class="mt-3">Education  Details</h5>
                                    <!-- Grade  -->
                                    <div class="col-12 mt-4">
                                        <div class="row g-xl-0 align-items-center">
                                            <div class="col-lg-4">
                                                <h6 class="mb-lg-0">Grade <span class="text-danger">*</span> </h6>
                                            </div>
                                            <div class="col-lg-8">
                                                <input type="text" class="form-control" id="grade" name="grade" placeholder="eg 10th std,UG,...">


                                            </div>
                                        </div>
                                    </div>
                                    <!-- college  -->
                                    <div class="col-12 mt-4">
                                        <div class="row g-xl-0 align-items-center">
                                            <div class="col-lg-4">
                                                <h6 class="mb-lg-0">School/College/Institute <span class="text-danger">*</span> </h6>
                                            </div>
                                            <div class="col-lg-8">
                                                <input type="text" class="form-control" id="college" name="college" placeholder="">


                                            </div>
                                        </div>
                                    </div>
                                    <!-- college  -->
                                    <div class="col-12 mt-4">
                                        <div class="row g-xl-0 align-items-center">
                                            <div class="col-lg-4">
                                                <h6 class="mb-lg-0">Board/University <span class="text-danger">*</span> </h6>
                                            </div>
                                            <div class="col-lg-8">
                                                <input type="text" class="form-control" id="unversity" name="university" placeholder="">


                                            </div>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div class="col-12 text-sm-end mt-4">
                                        <button class="btn btn-primary mb-0" type="submit">Submit</button>
                                    </div> 
                            </form>
                        </div>
                    </div>
                @endif
                @if(\Auth::user()->hasRole('Institution'))
                    <div class="card">
                        <div class="card-body">		
                            <form action="/stage2" method="POST">
                                @csrf()
                                <!-- Student/Proffesional  -->
                                <h5 class="mt-0">Company Institution Details</h5>
					            <!-- Grade  -->
                    
                                    <div class="col-12 mt-4">
                                        <div class="row g-xl-0 align-items-center">
                                            <div class="col-lg-4">
                                                <h6 class="mb-lg-0">Type of Services you are providing <span class="text-danger">*</span>
                                                    <br><small>
                                                        (Use a comma if there is more than one)</small>
                                                </h6>
                                            </div>
                                            <div class="col-lg-8">
                                                <textarea class="form-control" id="service" name="service" >

                                                </textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- college  -->
                                    <div class="col-12 mt-4">
                                        <div class="row g-xl-0 align-items-center">
                                            <div class="col-lg-4">
                                                <h6 class="mb-lg-0">Do you have own website?  </h6>
                                            </div>
                                            <div class="col-lg-8">
                                                <div class="d-flex">
                                                    <div class="form-check radio-bg-light me-4">
                                                        <input class="form-check-input" type="radio" value="yes" name="webiste" id="webisteyes" checked>
                                                        <label class="form-check-label" for="webisteyes">
                                                            Yes
                                                        </label>
                                                    </div>
                                                    <div class="form-check radio-bg-light me-4">
                                                        <input class="form-check-input" type="radio" value="no" name="webiste" id="webisteno">
                                                        <label class="form-check-label" for="webisteno">
                                                            No
                                                        </label>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- college  -->
                                    <div class="col-12 mt-4">
                                        <div class="row g-xl-0 align-items-center">
                                            <div class="col-lg-4">
                                                <h6 class="mb-lg-0">Do you have own mobile app? </h6>
                                            </div>
                                            <div class="col-lg-8">
                                                <div class="d-flex">
                                                    <div class="form-check radio-bg-light me-4">
                                                        <input class="form-check-input" value="yes" type="radio" name="mobile_app" id="mobile_appyes" checked>
                                                        <label class="form-check-label" for="mobile_appyes">
                                                            Yes
                                                        </label>
                                                    </div>
                                                    <div class="form-check radio-bg-light me-4">
                                                        <input class="form-check-input" value="no" type="radio" name="mobile_app" id="mobile_appno">
                                                        <label class="form-check-label" for="mobile_appno">
                                                            No
                                                        </label>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div class="col-12 text-sm-end mt-4">
                                        <button class="btn btn-primary mb-0" type="submit">Submit</button>
                                    </div> 
                            </form>
                        </div>
                    </div>
                @endif
				
			</div>
			<!--  form END -->	
		</div>
	</div>
</section>
<!-- =======================
Main part END -->

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
    $("input[name$='Preferable_working']").click(function() {
        var pref = $(this).val();
        if(pref == 'offline'){
            $("div.preferable_desc").hide();
        }
        else{
            $("div.preferable_desc").show();
        }
        

       
        $("div.preferable_desc").html(test);
    });
});
</script>


@endsection



