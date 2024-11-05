@extends('layouts.web-layout')
@section('content')

@section('styles')

@php
    if (auth()->check() && auth()->user()->type == 'Student') {
        // If the user is a student, perform the redirect
        header('Location: /');
        exit();
    }
@endphp

<!-- **************** MAIN CONTENT START **************** -->

	
<!-- Sidebar START -->
<nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

	<!-- Navbar brand for xl START -->
	<div class="d-flex align-items-center">
		<a class="navbar-brand" href="{{url('admin/kpsc')}}">
			<img class="navbar-brand-item" src="/images/logo-2.png" alt="">
		</a>
	</div>
	<!-- Navbar brand for xl END -->
	
	<div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" style="    overflow-y: auto;" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
		<div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

			<!-- Sidebar menu START -->
			<ul class="navbar-nav flex-column" id="navbar-sidebar">
				<!-- Menu item 1 -->
				<li class="nav-item" >
					<a href="{{url('admin')}}" class="nav-link  {!! active_menu('') !!}">
						<i class="bi bi-house fa-fw me-2"></i>Home
					</a>
				</li>
				<!-- Menu item 1 -->
				<li class="nav-item" >
					<a href="{{url('admin/kpsc')}}" class="nav-link  {!! active_menu('') !!}">
						<i class="bi bi-house fa-fw me-2"></i>Dashboard
					</a>
				</li>	<!-- menu item 2 -->
			    <li class="nav-item" >
					<a class="nav-link {!! active_menu('model-exam') !!} "  href="{{url('admin/kpsc/model-exam')}}">
						<i class="bi bi-basket fa-fw me-2"></i>Model Exam
					</a>
				</li>
				<li class="nav-item" >
					<a class="nav-link {!! active_menu('daily-exam') !!} "  href="{{url('admin/kpsc/daily-exam')}}">
						<i class="bi bi-basket fa-fw me-2"></i>Daily Exam
					</a>
				</li>
				
				 <!-- menu item 3 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('material') !!}"  href="{{url('admin/kpsc/material')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Material
                    </a>
                </li>
                <!-- menu item 4 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('capsule') !!}"  href="{{url('admin/kpsc/capsule')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Capsule
                    </a>
                </li>
                
                <!-- menu item 5 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('notifications') !!}"  href="{{url('admin/kpsc/notifications')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('free-class') !!}"  href="{{url('admin/kpsc/free-class')}}">
                        <i class="bi bi-basket fa-fw me-2"></i> Free Class
                    </a>
                </li>
				 <!-- menu item 19 -->
				 <li class="nav-item">
                    <a class="nav-link {!! active_menu('premium-class') !!}"  href="{{url('admin/kpsc/paid-class')}}">
                        <i class="bi bi-basket fa-fw me-2"></i> Premium Class
                    </a>
                </li>
                <!-- menu item 6 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('our-videos') !!}"  href="{{url('admin/kpsc/our-videos')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Our Videos
                    </a>
                </li>
                <!-- menu item 7 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('special-videos') !!}"  href="{{url('admin/kpsc/special-videos')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Youtube Class
                    </a>
                </li>
                
                <!-- menu item 9 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('daily-ca') !!}"  href="{{url('admin/kpsc/daily-ca')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Daily CA
                    </a>
                </li>
                <!-- menu item 10 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('psc-bulletin') !!}"  href="{{url('admin/kpsc/psc-bulletin')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Psc Bulletin
                    </a>
                </li>
                <!-- menu item 11 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('prev-qstn') !!}"  href="{{url('admin/kpsc/prev-qstn')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Prev Qstn
                    </a>
                </li>
                
                 <!-- menu item 13 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('new-qa') !!}"  href="{{url('admin/kpsc/new-qa')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>New Q & A
                    </a>
                </li>
               
                <!-- menu item 14 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('psc-results') !!}"  href="{{url('admin/kpsc/psc-results')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Psc Results
                    </a>
                </li>
                
                  <!-- menu item 15 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('time-table') !!}"  href="{{url('admin/kpsc/time-table')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Time Table
                    </a>
                </li>
                 <!-- menu item 12 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('syllabus') !!}"  href="{{url('admin/kpsc/syllabus')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Syllabus
                    </a>
                </li>
                   <!-- menu item 16 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('subjects') !!}"  href="{{url('admin/kpsc/subjects')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Subjects
                    </a>
                </li>
                <!-- menu item 8 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('psc-news') !!}"  href="{{url('admin/kpsc/psc-news')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Psc News
                    </a>
                </li>
                <!-- menu item 17 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('today-bucket') !!}"  href="{{url('admin/kpsc/today-bucket')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>
                        Roaring 40`s
                    </a>
                </li>
                 <!-- menu item 18 -->
				 <!--<li class="nav-item">-->
     <!--               <a class="nav-link {!! active_menu('free-class') !!}"  href="{{url('admin/kpsc/free-class')}}">-->
     <!--                   <i class="bi bi-basket fa-fw me-2"></i>Psc Free Class-->
     <!--               </a>-->
     <!--           </li>-->
				 <!-- menu item 15 -->
				 <!--<li class="nav-item">-->
     <!--               <a class="nav-link {!! active_menu('paid-class') !!}"  href="{{url('admin/kpsc/paid-class')}}">-->
     <!--                   <i class="bi bi-basket fa-fw me-2"></i>Psc Paid Class-->
     <!--               </a>-->
                    
     <!--           </li>-->
                 
                
				<!-- menu item 20 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('whats-new') !!}"  href="{{url('admin/kpsc/whats-new')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>What's New
                    </a>
                </li>
                <!-- menu item 21 -->
                <li class="nav-item">
                    <a class="nav-link {!! active_menu('special-tab') !!}"  href="{{url('admin/kpsc/special-tab')}}">
                        <i class="bi bi-basket fa-fw me-2"></i>Special Tab
                    </a>
                </li>
                <!-- menu item 22 -->
				<li class="nav-item" >
					<a class="nav-link {!! active_menu('banner-slider') !!} "  href="{{url('admin/kpsc/banner-slider')}}">
						<i class="bi bi-basket fa-fw me-2"></i>Stories 
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
						<a class="h5 mb-0 text-body" href="{{url('kpsc')}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Home">
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
								<!--<form class="position-relative">-->
								<!--	<input class="form-control pe-5 bg-secondary bg-opacity-10 border-0" type="search" placeholder="Search" aria-label="Search">-->
								<!--	<button class="btn bg-transparent px-2 py-0 position-absolute top-50 end-0 translate-middle-y" type="submit"><i class="fas fa-search fs-6 text-primary"></i></button>-->
								<!--</form>-->
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
							{{-- <div class="dropdown-menu dropdown-animation dropdown-menu-end dropdown-menu-size-md p-0 shadow-lg border-0"> --}}
								{{-- <div class="card bg-transparent">
									<div class="card-header bg-transparent border-bottom py-4 d-flex justify-content-between align-items-center">
										<h6 class="m-0">Notifications <span class="badge bg-danger bg-opacity-10 text-danger ms-2">2 new</span></h6>
										<a class="small" href="#">Clear all</a>
									</div> --}}
									{{-- <div class="card-body p-0">
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

											<!-- Notif item -->
											<li>
												<a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
													<div class="me-3">
														<div class="avatar avatar-md">
															<img class="avatar-img rounded-circle" src="assets/images/avatar/02.jpg" alt="avatar">
														</div>
													</div>
													<div>
														<h6 class="mb-1">Larry Lawson Added a new course</h6>
														<p class="small text-body m-0">What's new! Find out about new features</p>
														<u class="small">View detail</u>
													</div>
												</a>
											</li>

											<!-- Notif item -->
											<li>
												<a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
													<div class="me-3">
														<div class="avatar avatar-md">
															<img class="avatar-img rounded-circle" src="assets/images/avatar/05.jpg" alt="avatar">
														</div>
													</div>
													<div>
														<h6 class="mb-1">New request to apply for Instructor</h6>
														<u class="small">View detail</u>
													</div>
												</a>
											</li>

											<!-- Notif item -->
											<li>
												<a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
													<div class="me-3">
														<div class="avatar avatar-md">
															<img class="avatar-img rounded-circle" src="assets/images/avatar/03.jpg" alt="avatar">
														</div>
													</div>
													<div>
														<h6 class="mb-1">Update v2.3 completed successfully</h6>
														<p class="small text-body m-0">What's new! Find out about new features</p>
														<small class="text-body">5 min ago</small>
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

@endsection

