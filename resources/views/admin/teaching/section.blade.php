@extends('layouts.web-layout')
@section('content')


<!-- **************** MAIN CONTENT START **************** -->

@php
    if (auth()->check() && auth()->user()->type == 'Student') {
        // If the user is a student, perform the redirect
        header('Location: /');
        exit();
    }
@endphp
	
<!-- Sidebar START -->
<nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

	<!-- Navbar brand for xl START -->
	<div class="d-flex align-items-center">
		<a class="navbar-brand" href="{{url('admin')}}">
			<img class="navbar-brand-item" src="https://www.pachavellam.com/images/logo-2.png" alt="">
		</a>
	</div>
	<!-- Navbar brand for xl END -->
	
	<div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" style="    overflow-y: auto;" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
		<div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

			<!-- Sidebar menu START -->
			<ul class="navbar-nav flex-column" id="navbar-sidebar">
				
				<!-- Menu item 1 -->
				<li class="nav-item" >
					<a href="{{url('admin')}}" class="nav-link  {!! active_menu("home") !!}">
						<i class="bi bi-house fa-fw me-2"></i>Home
					</a>
				</li>

				<!-- Menu item 1 -->
				<li class="nav-item" >
					<a href="{{teaching_cms('/')}}" class="nav-link  {!! active_menu("dashboard") !!}">
						<i class="bi bi-house fa-fw me-2"></i>Dashboard
					</a>
				</li>
				<!-- menu item 2 -->
				<li class="nav-item">
					<a class="nav-link {!! active_menu("our-videos") !!}"  href="{{teaching_cms('instructors')}}">
						<i class="bi bi-basket fa-fw me-2"></i>Instructors
					</a>
				</li>
				<!-- menu item 3 -->
				<li class="nav-item">
					<a class="nav-link {!! active_menu("our-videos") !!}"  href="{{teaching_cms('course')}}">
						<i class="bi bi-basket fa-fw me-2"></i>Course
					</a>
				</li>
				<!-- menu item 4 -->
				<li class="nav-item">
					<a class="nav-link {!! active_menu("class") !!}"  href="{{teaching_cms('class')}}">
						<i class="bi bi-basket fa-fw me-2"></i>Class
					</a>
				</li>
			
                <!-- menu item 2 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu("payments") !!}"  href="{{ug_pg_cms('payments')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Payments
                    </a>
                </li>
				
            </ul>
			<!-- Sidebar menu end -->

			<!-- Sidebar footer START -->
			<div class="px-3 mt-auto pt-3">
				<div class="d-flex align-items-center justify-content-between text-primary-hover">
						<a class="h5 mb-0 text-body" href="{{url('admin/dashboard')}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
							<i class="bi bi-gear-fill"></i>
						</a>
						<a class="h5 mb-0 text-body" href="{{url('teaching')}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Home">
							<i class="bi bi-globe"></i>
						</a>
						<a class="h5 mb-0 text-body" href="{{url('sign-out')}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign out">
							<i class="bi bi-power"></i>
						</a>
				</div>
			</div>
			<!-- Sidebar footer END -->
			
		</div>
	</div>
</nav>
<!-- Sidebar END -->

<!-- Page content START -->
<div class="page-content">

	<!-- Top bar START -->
	<nav class="top-bar navbar-light border-bottom py-0 py-xl-3">
		<div class="container-fluid p-0">
			<div class="d-flex align-items-center w-100">

				<!-- Logo START -->
				<div class="d-flex align-items-center d-xl-none">
					<a class="navbar-brand" href="index-2.html">
						<img class="light-mode-item navbar-brand-item h-60px" src="https://www.pachavellam.com/assets/images/home/logo.png" alt="">
						<img class="dark-mode-item navbar-brand-item h-60px" src="https://www.pachavellam.com/assets/images/home/logo-2.png" alt="">
					</a>
				</div>
				<!-- Logo END -->

				<!-- Toggler for sidebar START -->
				<div class="navbar-expand-xl sidebar-offcanvas-menu">
					<button class="navbar-toggler me-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false" aria-label="Toggle navigation" data-bs-auto-close="outside">
						<i class="bi bi-text-right fa-fw h2 lh-0 mb-0 rtl-flip" data-bs-target="#offcanvasMenu"> </i>
					</button>
				</div>
				<!-- Toggler for sidebar END -->
				
				<!-- Top bar left -->
				<div class="navbar-expand-lg ms-auto ms-xl-0  d-none d-md-block">
					
					<!-- Toggler for menubar START -->
					<button class="navbar-toggler ms-auto " type="button" data-bs-toggle="collapse" data-bs-target="#navbarTopContent" aria-controls="navbarTopContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-animation">
							<span></span>
							<span></span>
							<span></span>
						</span>
					</button>
					<!-- Toggler for menubar END -->

					<!-- Topbar menu START -->
					<div class="collapse navbar-collapse w-100" id="navbarTopContent">
						<!-- Top search START -->
						<div class="nav my-3 my-xl-0 flex-nowrap align-items-center">
							<div class="nav-item w-100">
								<form class="position-relative">
									<input class="form-control pe-5 bg-secondary bg-opacity-10 border-0" type="search" placeholder="Search" aria-label="Search">
									<button class="btn bg-transparent px-2 py-0 position-absolute top-50 end-0 translate-middle-y" type="submit"><i class="fas fa-search fs-6 text-primary"></i></button>
								</form>
							</div>
						</div>
						<!-- Top search END -->
					</div>
					<!-- Topbar menu END -->
				</div>
				<!-- Top bar left END -->
				
				<!-- Top bar right START -->
				<div class="ms-xl-auto">
					<ul class="navbar-nav flex-row align-items-center">

						<!-- Notification dropdown START -->
						<li class="nav-item ms-2 ms-md-3 dropdown">
							<!-- Notification button -->
							<a class="btn btn-light btn-round mb-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
								<i class="bi bi-bell fa-fw"></i>
							</a>
							<!-- Notification dote -->
							<span class="notif-badge animation-blink"></span>

							<!-- Notification dropdown menu START -->
							{{-- <div class="dropdown-menu dropdown-animation dropdown-menu-end dropdown-menu-size-md p-0 shadow-lg border-0">
								<div class="card bg-transparent">
									<div class="card-header bg-transparent border-bottom py-4 d-flex justify-content-between align-items-center">
										<h6 class="m-0">Notifications <span class="badge bg-danger bg-opacity-10 text-danger ms-2">2 new</span></h6>
										<a class="small" href="#">Clear all</a>
									</div>
									<div class="card-body p-0">
										<ul class="list-group list-unstyled list-group-flush">
											<!-- Notif item -->
											<li>
												<a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
													<div class="me-3">
														<div class="avatar avatar-md">
															<img class="avatar-img rounded-circle" src="https://www.pachavellam.com/assets/images/favicon.png" alt="avatar">
														</div>
													</div>
													<div>
														<p class="text-body small m-0">Congratulate <b>Joan Wallace</b> for graduating from <b>Microverse university</b></p>
														<u class="small">Say congrats</u>
													</div>
												</a>
											</li>

											
										</ul>
									</div>
									<!-- Button -->
									<div class="card-footer bg-transparent border-0 py-3 text-center position-relative">
										<a href="#" class="stretched-link">See all incoming activity</a>
									</div>
								</div>
							</div> --}}
							<!-- Notification dropdown menu END -->
						</li>
						<!-- Notification dropdown END -->

						<!-- Profile dropdown START -->
						<li class="nav-item ms-2 ms-md-3 dropdown">
							<!-- Avatar -->
							<a class="avatar avatar-sm p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
								<img class="avatar-img rounded-circle" src="{{asset('storage/users/'.Auth::user()->image)}}" alt="avatar">
							</a>

							<!-- Profile dropdown START -->
							<ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3" aria-labelledby="profileDropdown">
								<!-- Profile info -->
								<li class="px-3">
									<div class="d-flex align-items-center">
										<!-- Avatar -->
										<div class="avatar me-3">
											<img class="avatar-img rounded-circle shadow" src="{{asset('storage/users/'.Auth::user()->image)}}" alt="avatar">
										</div>
										<div>
											<a class="h6 mt-2 mt-sm-0" href="#">{{Auth::user()->name}}</a>
											<p class="small m-0">{{Auth::user()->email}}</p>
										</div>
									</div>
									<hr>
								</li>

								<!-- Links -->
								<li><a class="dropdown-item" href="{{url('profile')}}"><i class="bi bi-person fa-fw me-2"></i>Edit Profile</a></li>
								<li><a class="dropdown-item" href="#"><i class="bi bi-info-circle fa-fw me-2"></i>Help</a></li>
								<li><a class="dropdown-item bg-danger-soft-hover" href="{{url('sign-out')}}"><i class="bi bi-power fa-fw me-2"></i>Sign Out</a></li>
								<li> <hr class="dropdown-divider"></li>

								<!-- Dark mode switch START -->
								<li>
									<div class="modeswitch-wrap" id="darkModeSwitch">
										<div class="modeswitch-item">
											<div class="modeswitch-icon"></div>
										</div>
										<span>Dark mode</span>
									</div>
								</li> 
								<!-- Dark mode switch END -->
							</ul>
							<!-- Profile dropdown END -->
						</li>
						<!-- Profile dropdown END -->
					</ul>
				</div>
				<!-- Top bar right END -->
			</div>
		</div>
	</nav>
	<!-- Top bar END -->

	<!-- Page main content START -->
	<div class="page-content-wrapper border">
        @yield('content2')
    </div>
	<!-- Page main content END -->
</div>
<!-- Page content END -->
<!-- **************** MAIN CONTENT END **************** -->


@endsection


@section('scripts')
<script>
	$(document).ready(function() {
       $('.ckeditor').ckeditor();
       
	});

</script>

<script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script>
    var editor = CKEDITOR.replace('editor', {
        filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token() ])}}",
        filebrowserUploadMethod: 'form',
		height: 400,
		
    });

</script>

	

@endsection