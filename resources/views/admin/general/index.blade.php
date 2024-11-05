@extends('admin.general.layout')
@section('general')
	
	<div class="row">
		<div class="col-12 mb-3">
			<h1 class="h3 mb-2 mb-sm-0">Dashboard</h1>
		</div>
	</div>


<!-- Chart and Ticket START -->
	<div class="row g-4 mb-4">

		<!-- Chart START -->
		<div class="col-xxl-12">
			<div class="row g-4 mb-4">
			
				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<a href="{{url('kpsc/cms')}}">
						<div class="card card-body bg-warning bg-opacity-15 p-4 h-100">
							<div class="d-flex justify-content-between align-items-center">
								<!-- Digit -->
								<div>
									<h2 class=" mb-0 fw-bold"></h2>
									<span class="mb-0 h6 fw-light">Kerala Psc</span>
								</div>
								<!-- Icon -->
								<div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-lightbulb fa-fw"></i></div>
							</div>
						</div>
					</a>
				</div>

				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<a href="{{url('ug-pg/cms')}}">
						<div class="card card-body bg-purple bg-opacity-10 p-4 h-100">
							<div class="d-flex justify-content-between align-items-center">
								<!-- Digit -->
								<div>
									<h2 class=" mb-0 fw-bold" ></h2>
									<span class="mb-0 h6 fw-light">UG|PG Section</span>
								</div>
								<!-- Icon -->
								<div class="icon-lg rounded-circle bg-purple text-white mb-0"><i class="fas  fa-user-graduate  fa-fw"></i></div>
							</div>
						</div>
					</a>
				</div>

				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
						<div class="d-flex justify-content-between align-items-center">
							<!-- Digit -->
							<div>
								<h2 class=" mb-0 fw-bold" ></h2>
								<span class="mb-0 h6 fw-light">5 - 12 Class Section</span>
							</div>
							<!-- Icon -->
							<div class="icon-lg rounded-circle bg-grad text-white mb-0"><i class="fas fa-fw fa-user-tie"></i></div>
						</div>
					</div>
				</div>
				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<a href="{{url('admin/general/cms/')}}">
						<div class="card card-body bg-success bg-opacity-10 p-4 h-100">
							<div class="d-flex justify-content-between align-items-center">
								<!-- Digit -->
								<div>
									<div class="d-flex">
										<h2 class=" mb-0 fw-bold" ></h2>
										<span class="mb-0 h2 ms-1"></span>
									</div>
									<span class="mb-0 h6 fw-light">General Settings</span>
								</div>
								<!-- Icon -->
								<div class="icon-lg rounded-circle bg-danger text-white mb-0"><i class="bi bi-gear fa-fw"></i></div>
							</div>
						</div>
					</a>
				</div>
				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<div class="card card-body bg-success bg-opacity-10 p-4 h-100">
						<div class="d-flex justify-content-between align-items-center">
							<!-- Digit -->
							<div>
								<div class="d-flex">
									<h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="845"	data-purecounter-delay="200">0</h2>
								
								</div>
								<span class="mb-0 h6 fw-light">Institution</span>
							</div>
							<!-- Icon -->
							<div class="icon-lg rounded-circle bg-grad-pink text-white mb-0"><i class=" bi bi-bank fa-fw"></i></div>
						</div>
					</div>
				</div>
				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
						<div class="d-flex justify-content-between align-items-center">
							<!-- Digit -->
							<div>
								<h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="1235"	data-purecounter-delay="200">0</h2>
								<span class="mb-0 h6 fw-light">Teachers</span>
							</div>
							<!-- Icon -->
							<div class="icon-lg rounded-circle bg-primary text-white mb-0"><i class="bi bi-person-check fa-fw"></i></div>
						</div>
					</div>
				</div>

				<!-- Counter item -->
				<div class="col-md-3 col-xxl-3">
					<div class="card card-body bg-success bg-opacity-10 p-4 h-100">
						<div class="d-flex justify-content-between align-items-center">
							<!-- Digit -->
							<div>
								<div class="d-flex">
									<h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="845"	data-purecounter-delay="200">0</h2>
									<span class="mb-0 h2 ms-1"></span>
								</div>
								<span class="mb-0 h6 fw-light">Students</span>
							</div>
							<!-- Icon -->
							<div class="icon-lg rounded-circle bg-skype text-white mb-0"><i class="bi bi-people fa-fw"></i></div>
						</div>
					</div>
				</div>
			</div>
			<!-- Counter boxes END -->

					<div id="ChartPayout"></div>
			
		</div>
	</div>
<!-- Chart END -->

@endsection