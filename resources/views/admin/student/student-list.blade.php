
@extends('admin.student.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Students List</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    
                    <span class="js-choice ">
                        
                    </span>
                </div>
            </div>
            
        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body">
            
            @php
                $exportUrl = student_cms('student-list/export') . '?' . http_build_query(array_filter($filters, fn ($v) => $v !== ''));
                $hasFilter = collect($filters)->except('type')->filter(fn ($v) => $v !== '')->isNotEmpty() || $filters['type'] !== 'Student';
            @endphp

            <div class="row g-3 mb-3">
                @foreach ([
                    ['Students', $stats['total'], ''],
                    ['With mobile', $stats['with_mobile'], 'mobile=with'],
                    ['Without mobile', $stats['without_mobile'], 'mobile=without'],
                    ['Active (30 days)', $stats['active_30'], 'activity=active_30'],
                    ['Inactive (90+ days)', $stats['inactive_90'], 'activity=inactive_90'],
                ] as [$label, $value, $qs])
                    <div class="col-6 col-lg">
                        <a href="{{ student_cms('student-list') }}{{ $qs ? '?' . $qs : '' }}" class="text-reset">
                            <div class="border rounded p-2 px-3">
                                <div class="small text-muted">{{ $label }}</div>
                                <div class="h5 mb-0">{{ number_format($value) }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <form method="GET" class="row g-2 mb-3">
                <div class="col-lg-3">
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="Name, email or mobile...">
                </div>
                <div class="col-6 col-lg-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="all" @selected($filters['type'] === 'all')>All user types</option>
                        @foreach ($types as $t)
                            <option value="{{ $t }}" @selected($filters['type'] === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="mobile" class="form-select form-select-sm">
                        <option value="">Mobile: any</option>
                        <option value="with" @selected($filters['mobile'] === 'with')>Mobile number filled</option>
                        <option value="without" @selected($filters['mobile'] === 'without')>No mobile number</option>
                        <option value="verified" @selected($filters['mobile'] === 'verified')>Mobile verified</option>
                        <option value="unverified" @selected($filters['mobile'] === 'unverified')>Mobile not verified</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="activity" class="form-select form-select-sm">
                        <option value="">Activity: any</option>
                        <option value="active_30" @selected($filters['activity'] === 'active_30')>Active in last month</option>
                        <option value="inactive_30" @selected($filters['activity'] === 'inactive_30')>Not active in last month</option>
                        <option value="inactive_90" @selected($filters['activity'] === 'inactive_90')>Not active in past 3 months</option>
                        <option value="never" @selected($filters['activity'] === 'never')>Never active</option>
                    </select>
                </div>
                <div class="col-6 col-lg-3">
                    <select name="module" class="form-select form-select-sm">
                        <option value="">Used: anything</option>
                        @foreach (\App\Services\StudentFilter::MODULE_LABELS as $key => $label)
                            <option value="{{ $key }}" @selected($filters['module'] === $key)>Used {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="signup" class="form-select form-select-sm">
                        <option value="">Signup: any</option>
                        @foreach (\App\Services\StudentFilter::SIGNUP_LABELS as $key => $label)
                            <option value="{{ $key }}" @selected($filters['signup'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="platform" class="form-select form-select-sm">
                        <option value="">Platform: any</option>
                        @foreach (\App\Services\ClientInfo::PLATFORM_LABELS as $k => $label)
                            <option value="{{ $k }}" @selected($filters['platform'] === $k)>Used {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="interest" class="form-select form-select-sm">
                        <option value="">Interest: any</option>
                        <option value="none" @selected($filters['interest'] === 'none')>No interest chosen</option>
                        @foreach (\App\Models\CompetitiveExam::orderBy('position')->get() as $ex)
                            <option value="{{ $ex->slug }}" @selected($filters['interest'] === $ex->slug)>Interested in {{ $ex->short_name ?: $ex->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2"><input type="date" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm" title="Joined from"></div>
                <div class="col-6 col-lg-2"><input type="date" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm" title="Joined until"></div>
                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button class="btn btn-sm btn-primary mb-0" type="submit">Filter</button>
                    @if ($hasFilter)<a href="{{ student_cms('student-list') }}" class="btn btn-sm btn-light mb-0">Reset</a>@endif
                    <a href="{{ $exportUrl }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-download me-1"></i>Export CSV ({{ number_format($list->total()) }})</a>
                </div>
            </form>
            <p class="text-muted small">Showing {{ $list->firstItem() ?? 0 }}-{{ $list->lastItem() ?? 0 }} of {{ number_format($list->total()) }}. The export includes every matching user, not just this page.</p>

            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover books">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">#</th>
                            <th scope="col" class="border-0">Name</th>
                            <th scope="col" class="border-0">Email</th>
                            <th scope="col" class="border-0">Mobile No</th>
                            <th scope="col" class="border-0">Signup</th>
                            <th scope="col" class="border-0">Joined</th>
                            <th scope="col" class="border-0">Last activity</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                 @foreach ($list as $row)
                            
                        <!-- Table item -->
                            <tr>
                               
                                <td>{{$row->id}}</td>
                                <!-- Table data -->
                                <td><a href="{{ student_cms('student-list/' . $row->id) }}">{{ ucwords($row->name) ?: '(no name)' }}</a></td>
                                <td>{{$row->email}}</td>
                                <td>{{$row->mobile ?: '—'}}</td>
                                @php [$stLabel, $stColor] = \App\Services\StudentFilter::activityStatus($row->last_activity); @endphp
                                <td>{{ \App\Services\StudentFilter::signupMethod($row) }}</td>
                                <td class="text-nowrap">{{ $row->created_at?->format('d M Y') }}</td>
                                <td class="text-nowrap">{{ $row->last_activity ? \Carbon\Carbon::parse($row->last_activity)->diffForHumans() : '—' }}</td>
                                <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                                <td>
                                    <a href="{{student_cms('student-list/'. $row->id.'/delete')}}" onclick="return confirm('Delete this user?')" class="btn btn-sm text-center btn-circle btn-danger "><i class="bi bi-trash "></i></a>
                                </td>
                            </tr>
                        
                    @endforeach
                 
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->
            <div class="mt-3">{{ $list->links('pagination::bootstrap-5') }}</div>

        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->


@endsection
