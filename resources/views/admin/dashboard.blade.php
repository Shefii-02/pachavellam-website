@extends('admin.main-layout')
@section('main')
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
                                                        data-purecounter-end="{{ $stats['students'] }}" data-purecounter-delay="200">0</h2>
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
                        <!-- Live overview START -->
                        @can('General Setting Tab')
                            @php $maxReq = max(1, $days->max(fn ($d) => $d['api'] + $d['web'])); @endphp
                            <div class="row g-3 mb-4">
                                @foreach ([
                                    ['App users today', number_format($stats['app_users']), 'bi-phone', 'admin/general/dev-tools/analytics'],
                                    ['API requests today', number_format($stats['api_requests']), 'bi-hdd-network', 'admin/general/dev-tools/api-logs'],
                                    ['API errors today', number_format($stats['api_errors']), 'bi-exclamation-triangle', 'admin/general/dev-tools/api-logs?status=5xx'],
                                    ['Website visitors today', number_format($stats['web_visitors']), 'bi-globe', 'admin/general/dev-tools/analytics'],
                                    ['Bandwidth today', format_bytes($stats['bytes']), 'bi-cloud-arrow-down', 'admin/general/dev-tools/analytics'],
                                    ['New sign ups today', number_format($stats['new_users_today']), 'bi-person-plus', 'admin/students/student-list'],
                                ] as [$label, $value, $icon, $link])
                                    <div class="col-6 col-lg-4 col-xl-2">
                                        <a href="{{ url($link) }}" class="text-reset">
                                            <div class="card card-body border h-100 p-3">
                                                <div class="text-muted small"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</div>
                                                <div class="h4 mb-0 {{ $label === 'API errors today' && $stats['api_errors'] > 0 ? 'text-danger' : '' }}">{{ $value }}</div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-lg-7">
                                    <div class="card border h-100">
                                        <div class="card-header border-bottom"><h5 class="mb-0">Requests, last 7 days</h5></div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-end justify-content-between" style="height:160px;gap:8px">
                                                @foreach ($days as $d)
                                                    @php $total = $d['api'] + $d['web']; @endphp
                                                    <div class="text-center flex-fill" title="App {{ $d['api'] }} · Web {{ $d['web'] }} · {{ format_bytes($d['bytes']) }}">
                                                        <div class="small text-muted">{{ $total }}</div>
                                                        <div class="d-flex flex-column justify-content-end" style="height:120px">
                                                            <div class="bg-primary" style="height:{{ round($d['api'] / $maxReq * 100) }}%;min-height:{{ $d['api'] ? 2 : 0 }}px"></div>
                                                            <div class="bg-info" style="height:{{ round($d['web'] / $maxReq * 100) }}%;min-height:{{ $d['web'] ? 2 : 0 }}px"></div>
                                                        </div>
                                                        <div class="small">{{ $d['label'] }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="small text-muted mt-2">
                                                <span class="badge bg-primary">&nbsp;</span> App (API)
                                                <span class="badge bg-info ms-2">&nbsp;</span> Website
                                                <a class="float-end" href="{{ url('admin/general/dev-tools/analytics') }}">Full analytics →</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="card border h-100">
                                        <div class="card-header border-bottom d-flex justify-content-between">
                                            <h5 class="mb-0">Latest server errors</h5>
                                            <a href="{{ url('admin/general/logs') }}" class="small">Error logs →</a>
                                        </div>
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                @forelse ($recentErrors as $e)
                                                    <li class="list-group-item small">
                                                        <span class="badge bg-danger">{{ $e->status }}</span>
                                                        <span class="badge bg-secondary">{{ $e->method }}</span>
                                                        <span style="word-break:break-all">{{ $e->path }}</span>
                                                        <div class="text-muted">{{ $e->created_at->diffForHumans() }}@if ($e->error) · {{ \Illuminate\Support\Str::limit($e->error, 60) }}@endif</div>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-muted small">No server errors recorded.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endcan

                        <div class="row g-3 mb-4">
                            <div class="col-lg-5">
                                <div class="card border h-100">
                                    <div class="card-header border-bottom"><h5 class="mb-0">Exam categories</h5></div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            @foreach ($categoryCounts as $label => [$count, $link])
                                                <div class="col-6">
                                                    <a href="{{ $link }}" class="text-reset">
                                                        <div class="border rounded p-3">
                                                            <div class="text-muted small">{{ $label }}</div>
                                                            <div class="h4 mb-0">{{ $count }}</div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="card border h-100">
                                    <div class="card-header border-bottom d-flex justify-content-between">
                                        <h5 class="mb-0">Newest users</h5>
                                        <a href="{{ url('admin/students/student-list') }}" class="small">All users →</a>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-sm align-middle mb-0">
                                            <tbody>
                                                @foreach ($recentUsers as $u)
                                                    <tr>
                                                        <td>{{ $u->name ?: '—' }}<div class="small text-muted">{{ $u->email ?: $u->mobile }}</div></td>
                                                        <td><span class="badge bg-secondary">{{ $u->type }}</span></td>
                                                        <td class="small text-muted text-nowrap">{{ $u->created_at?->diffForHumans() }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Live overview END -->

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
