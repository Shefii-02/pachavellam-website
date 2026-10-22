@extends('admin.general.layout')
@section('general')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Daily Analytics</h3>
        <form method="GET">
            <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach ([7, 14, 30, 90] as $d)
                    <option value="{{ $d }}" @selected($days == $d)>Last {{ $d }} days</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- today --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['App users today', $totals['app_users'], 'bi-phone'],
            ['API requests today', number_format($totals['api_requests']), 'bi-hdd-network'],
            ['Website visitors today', $totals['web_visitors'], 'bi-people'],
            ['Website requests today', number_format($totals['web_requests']), 'bi-globe'],
            ['Bandwidth today', format_bytes($totals['bytes']), 'bi-cloud-arrow-down'],
        ] as [$label, $value, $icon])
            <div class="col-6 col-lg">
                <div class="card bg-transparent border rounded-3 h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</div>
                        <div class="h3 mb-0">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- per day --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Day by day</h5></div>
        <div class="card-body">
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle mb-0 table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2">Date</th>
                            <th colspan="5" class="text-center">App (API)</th>
                            <th colspan="3" class="text-center">Website</th>
                            <th rowspan="2">Total bandwidth</th>
                        </tr>
                        <tr>
                            <th>Users</th><th>Requests</th><th>4xx</th><th>5xx</th><th>Avg ms</th>
                            <th>Visitors</th><th>Requests</th><th>Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daily as $row)
                            <tr>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($row->day)->format('D, d M Y') }}</td>
                                <td>{{ number_format($row->app_users) }}</td>
                                <td>{{ number_format($row->api_requests) }}</td>
                                <td>{{ number_format($row->api_4xx) }}</td>
                                <td class="{{ $row->api_5xx > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($row->api_5xx) }}</td>
                                <td>{{ $row->api_avg_ms ?? '—' }}</td>
                                <td>{{ number_format($row->web_visitors) }}</td>
                                <td>{{ number_format($row->web_requests) }}</td>
                                <td>{{ format_bytes($row->web_bytes_out) }}</td>
                                <td><strong>{{ format_bytes($row->total_bytes) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center">No data yet. Requests are logged from now on.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2 mb-0">Users = distinct logged-in app users calling the API. Visitors = distinct IP addresses on the website. Bandwidth = request + response size (body only, headers not counted).</p>
        </div>
    </div>

    {{-- today details --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card bg-transparent border rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Top API endpoints today</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Endpoint</th><th>Hits</th><th>Avg ms</th><th>Errors</th><th>Data</th></tr></thead>
                        <tbody>
                            @forelse ($topApi as $r)
                                <tr>
                                    <td style="word-break:break-all"><span class="badge bg-secondary">{{ $r->method }}</span> {{ $r->path }}</td>
                                    <td>{{ number_format($r->hits) }}</td><td>{{ $r->avg_ms }}</td>
                                    <td>{{ $r->errors }}</td><td>{{ format_bytes($r->bytes) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No API traffic today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card bg-transparent border rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Top website pages today</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Page</th><th>Hits</th><th>Visitors</th><th>Data sent</th></tr></thead>
                        <tbody>
                            @forelse ($topWeb as $r)
                                <tr>
                                    <td style="word-break:break-all">{{ $r->path }}</td>
                                    <td>{{ number_format($r->hits) }}</td><td>{{ $r->visitors }}</td>
                                    <td>{{ format_bytes($r->bytes) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No website traffic today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card bg-transparent border rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Most active app users today</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>User ID</th><th>Requests</th><th>Data</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($topUsers as $r)
                                <tr>
                                    <td>#{{ $r->user_id }}</td><td>{{ number_format($r->hits) }}</td>
                                    <td>{{ format_bytes($r->bytes) }}</td>
                                    <td><a href="{{ admin_general('dev-tools/api-logs') }}?user={{ $r->user_id }}">view requests</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No logged-in app users today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
