@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom">
            <div class="row align-items-center g-2">
                <div class="col-md-3"><h3 class="mb-0">API Request Logs</h3></div>
                <div class="col-md-9">
                    <form method="GET" class="row g-2 justify-content-md-end">
                        <div class="col-auto"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Path contains..."></div>
                        <div class="col-auto">
                            <select name="method" class="form-select form-select-sm">
                                <option value="">Method</option>
                                @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $m)
                                    <option value="{{ $m }}" @selected(request('method') === $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Status</option>
                                @foreach (['2xx', '3xx', '4xx', '5xx'] as $st)
                                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto"><input type="number" name="user" value="{{ request('user') }}" class="form-control form-control-sm" placeholder="User ID" style="width:100px"></div>
                        <div class="col-auto"><input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm"></div>
                        <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
                        <div class="col-auto"><a href="{{ admin_general('dev-tools/api-logs') }}" class="btn btn-sm btn-light">Reset</a></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if (session('message'))
                <div class="alert alert-success py-2">{{ session('message') }}</div>
            @endif
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Method</th>
                            <th>Path</th>
                            <th>Status</th>
                            <th>Time (ms)</th>
                            <th>In / Out</th>
                            <th>User</th>
                            <th>IP</th>
                            <th>Device</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $badge = $log->status >= 500 ? 'danger' : ($log->status >= 400 ? 'warning text-dark' : ($log->status >= 300 ? 'info' : 'success'));
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $log->created_at->format('d M, h:i:s A') }}</td>
                                <td><span class="badge bg-secondary">{{ $log->method }}</span></td>
                                <td style="word-break:break-all">{{ $log->path }}
                                    @if ($log->error)<div class="small text-danger">{{ $log->error }}</div>@endif
                                </td>
                                <td><span class="badge bg-{{ $badge }}">{{ $log->status }}</span></td>
                                <td class="{{ $log->duration_ms > 1000 ? 'text-danger fw-bold' : '' }}">{{ $log->duration_ms }}</td>
                                <td class="text-nowrap">{{ format_bytes($log->bytes_in) }} / {{ format_bytes($log->bytes_out) }}</td>
                                <td>{!! $log->user_id ? '<a href="?user=' . $log->user_id . '">#' . $log->user_id . '</a>' : '—' !!}</td>
                                <td>{{ $log->ip }}</td>
                                <td class="small text-nowrap" title="{{ $log->user_agent }}">{{ $log->platform ? \App\Services\ClientInfo::label($log->platform) : \Illuminate\Support\Str::limit($log->user_agent, 24) }}{{ $log->app_version ? ' v' . $log->app_version : '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No requests logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>{{ $logs->links('pagination::bootstrap-5') }}</div>
                <a href="{{ admin_general('dev-tools/prune?days=30') }}" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Delete all request logs older than 30 days?')">Delete logs older than 30 days</a>
            </div>
        </div>
    </div>
@endsection
