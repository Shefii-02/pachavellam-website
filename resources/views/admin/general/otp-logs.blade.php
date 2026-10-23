@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom">
            <div class="row align-items-center">
                <div class="col-md-4"><h3 class="mb-0">OTP Logs</h3></div>
                <div class="col-md-8">
                    <form method="GET" class="row g-2 justify-content-md-end">
                        <div class="col-auto">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Phone number">
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All status</option>
                                @foreach (['sent', 'verified', 'expired', 'failed'] as $st)
                                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
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
                            <th>#</th>
                            <th>Phone number</th>
                            <th>OTP</th>
                            <th>User</th>
                            <th>SMS driver</th>
                            <th>Created at</th>
                            <th>Expires at</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $status = $log->display_status;
                                $badge = ['verified' => 'success', 'sent' => 'primary', 'expired' => 'secondary', 'failed' => 'danger'][$status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>{{ $logs->firstItem() + $loop->index }}</td>
                                <td>{{ $log->phone }}</td>
                                <td>{{ $log->otp ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->is_new_user ? 'warning text-dark' : 'info' }}">
                                        {{ $log->is_new_user ? 'New user' : 'Existing user' }}
                                    </span>
                                </td>
                                <td>{{ $log->driver }}</td>
                                <td>{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                                <td>{{ $log->expires_at?->format('d M Y, h:i A') }}</td>
                                <td>
                                    <span class="badge bg-{{ $badge }}" @if($log->error) title="{{ $log->error }}" @endif>{{ ucfirst($status) }}</span>
                                </td>
                                <td class="text-end">
                                    <a title="Delete" href="{{ admin_general('otp-logs/delete/' . $log->id) }}"
                                       onclick="return confirm('Delete this OTP log?')" class="btn btn-sm btn-danger btn-circle">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">@include('admin.partials.per-page', ['p' => $logs])
{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
