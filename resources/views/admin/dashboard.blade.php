@extends('layouts.web-layout')
@section('content')
    <!-- **************** MAIN CONTENT START **************** -->


    <!-- Sidebar START -->
    <nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

        <!-- Navbar brand for xl START -->
        <div class="d-flex align-items-center">
            <a class="navbar-brand" href="{{ url('admin/dashboard') }}">
                <img class="navbar-brand-item" src="{{ url('images/logo-2.png') }}" alt="">
            </a>
        </div>
        <!-- Navbar brand for xl END -->

        <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" style="    overflow-y: auto;"
            data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
            <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

                <!-- Sidebar menu START -->
                <ul class="navbar-nav flex-column" id="navbar-sidebar">

                    <!-- Menu item 1 -->
                    <li class="nav-item">
                        <a href="{{ url('admin/dashboard') }}" class="nav-link  {!! active_menu('dashboard') !!}">
                            <i class="bi bi-house-door-fill fa-fw me-2"></i>Dashboard
                        </a>
                    </li>

                    @can('kpsc Tab')
                        <!-- menu item 2 -->
                        <li class="nav-item">
                            <a class="nav-link {!! active_menu('') !!} " href="{{ url('admin/kpsc/') }}">
                                <i class="bi bi-1-circle-fill fa-fw me-2"></i>Kpsc
                            </a>
                        </li>
                    @endcan
                    @can('ug-pg Tab')
                        <!-- menu item 2 -->
                        {{-- <li class="nav-item">
                    <a class="nav-link {!! active_menu("notifications") !!}"  href="{{ug_pg_cms('/')}}">
                        <i class="bi bi-2-circle-fill fa-fw me-2"></i>UG - PG
                    </a>
                </li> --}}
                    @endcan
                    @can('1-12 Tab')
                        <!-- menu item 2 -->
                        <!--<li class="nav-item">-->
                        <!--    <a class="nav-link {!! active_menu('notifications') !!}"  href="{{ url('primary/') }}">-->
                        <!--        <i class="bi bi-basket fa-fw me-2"></i>1 - 12 Class-->
                        <!--    </a>-->
                        <!--</li>-->
                    @endcan
                    @can('Institution Tab')
                        <!-- menu item 3 -->
                        <!--<li class="nav-item">-->
                        <!--    <a class="nav-link {!! active_menu('whats-new') !!}"  href="{{ url('institution') }}">-->
                        <!--        <i class="bi bi-basket fa-fw me-2"></i>Institution-->
                        <!--    </a>-->
                        <!--</li>-->
                    @endcan
                    @can('Teachers Tab')
                        <!-- menu item 4 -->
                        {{-- <li class="nav-item">
                    <a class="nav-link {!! active_menu("teaching-tab") !!}"  href="{{teaching_cms('/')}}">
                        <i class="bi bi-3-circle-fill fa-fw me-2"></i>Teaching
                    </a>
                </li> --}}
                    @endcan
                    @can('Students Tab')
                        <!-- menu item 5 -->
                        <li class="nav-item">
                            <a class="nav-link {!! active_menu('students-tab') !!}" href="{{ url('admin/students') }}">
                                <i class="bi bi-4-circle-fill fa-fw me-2"></i>Students
                            </a>
                        </li>
                    @endcan
                    @can('General Setting Tab')
                        <!-- menu item 6 -->
                        <li class="nav-item">
                            <a class="nav-link {!! active_menu('special-videos') !!}" href="{{ url('admin/general') }}">
                                <i class="bi bi-5-circle-fill fa-fw me-2"></i>General
                            </a>
                        </li>
                    @endcan

                </ul>
                <!-- Sidebar menu end -->

                <!-- Sidebar footer START -->
                <div class="px-3 mt-auto pt-3">
                    <div class="d-flex align-items-center justify-content-between text-primary-hover">

                        <a class="h5 mb-0 text-body" href="{{ url('admin') }}" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Home">
                            <i class="bi bi-house"></i>
                        </a>

                        @can('General Setting Tab')
                            <a class="h5 mb-0 text-body" href="{{ url('admin/general') }}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Settings">
                                <i class="bi bi-gear-fill"></i>
                            </a>
                        @endcan
                        <a class="h5 mb-0 text-body" href="{{ url('sign-out') }}" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Sign out">
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
                            <img class="light-mode-item navbar-brand-item h-60px" src="{{ url('images/logo.png') }}"
                                alt="">
                            <img class="dark-mode-item navbar-brand-item h-60px" src="{{ url('images/logo-2.png') }}"
                                alt="">
                        </a>
                    </div>
                    <!-- Logo END -->

                    <!-- Toggler for sidebar START -->
                    <div class="navbar-expand-xl sidebar-offcanvas-menu">
                        <button class="navbar-toggler me-auto" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false"
                            aria-label="Toggle navigation" data-bs-auto-close="outside">
                            <i class="bi bi-text-right fa-fw h2 lh-0 mb-0 rtl-flip" data-bs-target="#offcanvasMenu"> </i>
                        </button>
                    </div>
                    <!-- Toggler for sidebar END -->

                    <!-- Top bar left -->
                    <div class="navbar-expand-lg ms-auto ms-xl-0  d-none d-md-block">

                        <!-- Toggler for menubar START -->
                        <button class="navbar-toggler ms-auto " type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarTopContent" aria-controls="navbarTopContent" aria-expanded="false"
                            aria-label="Toggle navigation">
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
                                @can('Notification Section')
                                    <!-- Notification button -->
                                    <a class="btn btn-light btn-round mb-0" href="#" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                        <i class="bi bi-bell fa-fw"></i>
                                    </a>
                                    <!-- Notification dote -->
                                    <span class="notif-badge animation-blink"></span>
                                @endcan
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
                                <a class="avatar avatar-sm p-0" href="#" id="profileDropdown" role="button"
                                    data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <img class="avatar-img rounded-circle"
                                        src="{{ asset('storage/users/' . Auth::user()->image) }}" alt="avatar">
                                </a>

                                <!-- Profile dropdown START -->
                                <ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3"
                                    aria-labelledby="profileDropdown">
                                    <!-- Profile info -->
                                    <li class="px-3">
                                        <div class="d-flex align-items-center">
                                            <!-- Avatar -->
                                            <div class="avatar me-3">
                                                <img class="avatar-img rounded-circle shadow"
                                                    src="{{ asset('storage/users/' . Auth::user()->image) }}"
                                                    alt="avatar">
                                            </div>
                                            <div>
                                                <a class="h6 mt-2 mt-sm-0" href="#">{{ Auth::user()->name }}</a>
                                                <p class="small m-0">{{ Auth::user()->email }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                    </li>

                                    <!-- Links -->
                                    <li><a class="dropdown-item" href="{{ url('profile') }}"><i
                                                class="bi bi-person fa-fw me-2"></i>Edit Profile</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-info-circle fa-fw me-2"></i>Help</a></li>
                                    <li><a class="dropdown-item bg-danger-soft-hover" href="{{ url('sign-out') }}"><i
                                                class="bi bi-power fa-fw me-2"></i>Sign Out</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

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
        @can('Dashboard View')
            <!-- Page main content START -->
            <div class="page-content-wrapper border">
                {{-- ///////////////////////////////start --}}
                <!-- Title -->
                <div class="row">
                    <div class="col-12 mb-3">
                        <h1 class="h3 mb-2 mb-sm-0">Dashboard</h1>
                    </div>
                </div>


                <!-- Chart and Ticket START -->
                <div class="row g-4 mb-4">

                    <!-- Chart START -->
                    <div class="col-xxl-12">
                        <div class="row g-4 mb-4 justify-content-center">
                            <!-- Counter item -->
                            <div class="col-md-3 col-xxl-3">
                                <a href="{{ url('admin/kpsc') }}">
                                    <div class="card card-body bg-warning bg-opacity-75 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Digit -->
                                            <div>
                                                <h2 class=" mb-0 fw-bold"></h2>
                                                <span class="mb-0 h6 fw-light">Kerala Psc</span>
                                            </div>
                                            <!-- Icon -->
                                            <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i
                                                    class="fas fa-lightbulb fa-fw"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Counter item -->
                            <div class="col-md-3 col-xxl-3">
                                <a href="{{ ug_pg_cms('/') }}">
                                    <div class="card card-body bg-purple bg-opacity-75 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Digit -->
                                            <div>
                                                <h2 class=" mb-0 fw-bold"></h2>
                                                <span class="mb-0 h6 fw-light">UG|PG Section</span>
                                            </div>
                                            <!-- Icon -->
                                            <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i
                                                    class="fas  fa-user-graduate  fa-fw"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Counter item -->
                            <!--<div class="col-md-3 col-xxl-3">-->
                            <!--    <div class="card card-body bg-primary bg-opacity-75 p-4 h-100">-->
                            <!--        <div class="d-flex justify-content-between align-items-center">-->
                            <!-- Digit -->
                            <!--            <div>-->
                            <!--                <h2 class=" mb-0 fw-bold" ></h2>-->
                            <!--                <span class="mb-0 h6 fw-light">5 - 12 Class Section</span>-->
                            <!--            </div>-->
                            <!-- Icon -->
                            <!--            <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i class="fas fa-fw fa-user-tie"></i></div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!-- Counter item -->
                            <div class="col-md-3 col-xxl-3">
                                <a href="{{ url('admin/teaching') }}">
                                    <div class="card card-body bg-primary bg-opacity-75 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Digit -->
                                            <div>
                                                <h2 class="mb-0 fw-bold"></h2>
                                                <span class="mb-0 h6 fw-light">Teachers</span>
                                            </div>
                                            <!-- Icon -->
                                            <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i
                                                    class="bi bi-person-check fa-fw"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <!-- Counter item -->
                            <div class="col-md-3 col-xxl-3">
                                <a href="{{ url('admin/general/cms/') }}">
                                    <div class="card card-body bg-info bg-opacity-75 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Digit -->
                                            <div>
                                                <div class="d-flex">
                                                    <h2 class=" mb-0 fw-bold"></h2>
                                                    <span class="mb-0 h2 ms-1"></span>
                                                </div>
                                                <span class="mb-0 h6 fw-light">General Settings</span>
                                            </div>
                                            <!-- Icon -->
                                            <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i
                                                    class="bi bi-gear fa-fw"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <!-- Counter item -->
                            <div class="col-md-3 col-xxl-3">
                                <div class="card card-body bg-success bg-opacity-75 p-4 h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Digit -->
                                        <div>
                                            <div class="d-flex">
                                                <h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0"
                                                    data-purecounter-end="9" data-purecounter-delay="200">0</h2>

                                            </div>
                                            <span class="mb-0 h6 fw-light">Institution</span>
                                        </div>
                                        <!-- Icon -->
                                        <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i
                                                class=" bi bi-bank fa-fw"></i></div>
                                    </div>
                                </div>
                            </div>


                            <!-- Counter item -->
                            <div class="col-md-3 col-xxl-3">
                                <a href="{{ url('admin/students') }}">
                                    <div class="card card-body bg-dark bg-opacity-50 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Digit -->
                                            <div>
                                                <div class="d-flex">
                                                    <h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0"
                                                        data-purecounter-end="1845" data-purecounter-delay="200">0</h2>
                                                    <span class="mb-0 h2 ms-1"></span>
                                                </div>
                                                <span class="mb-0 h6 fw-light">Students</span>
                                            </div>
                                            <!-- Icon -->
                                            <div class="icon-lg rounded-circle bg-dark text-white mb-0"><i
                                                    class="bi bi-people fa-fw"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <!-- Counter boxes END -->

                        {{-- <div id="ChartPayout"></div> --}}
                        <!--<div class="row">-->
                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="donut-chart-2" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->

                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="donut-chart-3" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->

                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="pie_chart" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->

                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="donut-chart" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->
                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="responsive-chart" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->

                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="spline" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->

                        <!--     <div class="col-md-6 mb-2">-->
                        <!--        <div id="timeseries" class="bg-blue bg-opacity-75 rounded p-3"></div>-->
                        <!--     </div>-->


                        <!--</div>-->

                    </div>
                </div>
                <!-- Chart END -->




            </div>
            <!-- Chart and Ticket END -->
        @endcan
        <!-- Top listed Cards START -->


        {{-- //////////end --}}
    </div>
    <!-- Page main content END -->
    </div>
@endsection


@section('scripts')
    {{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var options = {
            chart: {
                width: "100%",
                height: 300,
                type: "bar"
            },
            plotOptions: {
                bar: {
                    horizontal: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 1,
                colors: ["#fff"]
            },
            series: [{
                    data: [44, 55, 41, 64, ]
                },
                {
                    data: [30, 85, 11, 78]
                }, {
                    data: [53, 32, 33, 52]
                }

            ],
            xaxis: {
                categories: [
                    "Kpsc",
                    "Ug-Pg",
                    "Edu Counseling",
                    "1 to 12",
                ],
            },
            legend: {
                position: "right",
                verticalAlign: "top",
                containerMargin: {
                    left: 35,
                    right: 60
                }
            },
            responsive: [{
                breakpoint: 1000,
                options: {
                    plotOptions: {
                        bar: {
                            horizontal: false
                        }
                    },
                    legend: {
                        position: "bottom"
                    }
                }
            }]
        };

        var chart = new ApexCharts(
            document.querySelector("#responsive-chart"),
            options
        );



        chart.render();

        var options = {
            series: [44, 55, 13, 43, 22],
            chart: {
                width: "100%",
                height: 300,
                type: 'pie',
                toolbar: {
                    show: true
                },
            },
            labels: ['Team A', 'Team B', 'Team C', 'Team D', 'Team E'],
            color: '#fff',
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]


        };

        var chart = new ApexCharts(document.querySelector("#pie_chart"), options);
        chart.render();

        var options = {
            series: [44, 55, 41, 17, 15],
            chart: {
                width: "100%",
                height: 300,
                type: 'donut',
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#donut-chart"), options);
        chart.render();



        var options = {
            series: [44, 55, 41, 17, 15],
            chart: {
                width: "100%",
                height: 300,
                type: 'donut',
            },
            plotOptions: {
                pie: {
                    startAngle: -90,
                    endAngle: 270
                }
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'gradient',
            },
            legend: {
                formatter: function(val, opts) {
                    return val + " - " + opts.w.globals.series[opts.seriesIndex]
                }
            },
            // title: {
            //   text: 'Gradient Donut with custom Start-angle'
            // },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#donut-chart-2"), options);
        chart.render();



        var options = {
            series: [44, 55, 41, 17, 15],
            chart: {
                width: "100%",
                height: 300,
                type: 'donut',
                dropShadow: {
                    enabled: true,
                    color: '#111',
                    top: -1,
                    left: 3,
                    blur: 3,
                    opacity: 0.2
                }
            },
            stroke: {
                width: 0,
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                showAlways: true,
                                show: true
                            }
                        }
                    }
                }
            },
            labels: ["Comedy", "Action", "SciFi", "Drama", "Horror"],
            dataLabels: {
                dropShadow: {
                    blur: 3,
                    opacity: 0.8
                }
            },
            fill: {
                type: 'pattern',
                opacity: 1,
                pattern: {
                    enabled: true,
                    style: ['verticalLines', 'squares', 'horizontalLines', 'circles', 'slantedLines'],
                },
            },
            states: {
                hover: {
                    filter: 'none'
                }
            },
            theme: {
                palette: 'palette2'
            },
            // title: {
            //   text: "Favourite Movie Type"
            // },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#donut-chart-3"), options);
        chart.render();



        var options = {
            series: [{
                name: 'series1',
                data: [31, 40, 28, 51, 42, 109, 100]
            }, {
                name: 'series2',
                data: [11, 32, 45, 32, 34, 52, 41]
            }],
            chart: {
                width: "100%",
                height: 300,
                type: 'area'
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                type: 'datetime',
                categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z",
                    "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z",
                    "2018-09-19T06:30:00.000Z"
                ]
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };

        var chart = new ApexCharts(document.querySelector("#spline"), options);
        chart.render();

        var options = {
            series: [{
                name: 'PRODUCT A',
                data: dataSet[0]
            }, {
                name: 'PRODUCT B',
                data: dataSet[1]
            }, {
                name: 'PRODUCT C',
                data: dataSet[2]
            }],
            chart: {
                type: 'area',
                stacked: false,
                height: 350,
                zoom: {
                    enabled: false
                },
            },
            dataLabels: {
                enabled: false
            },
            markers: {
                size: 0,
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#8e8da4',
                    },
                    offsetX: 0,
                    formatter: function(val) {
                        return (val / 1000000).toFixed(2);
                    },
                },
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                }
            },
            xaxis: {
                type: 'datetime',
                tickAmount: 8,
                min: new Date("01/01/2014").getTime(),
                max: new Date("01/20/2014").getTime(),
                labels: {
                    rotate: -15,
                    rotateAlways: true,
                    formatter: function(val, timestamp) {
                        return moment(new Date(timestamp)).format("DD MMM YYYY")
                    }
                }
            },
            title: {
                text: 'Irregular Data in Time Series',
                align: 'left',
                offsetX: 14
            },
            tooltip: {
                shared: true
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                offsetX: -10
            }
        };

        var chart = new ApexCharts(document.querySelector("#timeseries"), options);
        chart.render();
    </script> --}}
@endsection
