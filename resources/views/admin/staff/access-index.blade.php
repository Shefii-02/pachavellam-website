@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div><h3 class="mb-0">Staff access</h3><div class="small text-muted">What each staff member may do, and in which exam types. Admins can do everything everywhere. Role permissions are edited in the Roles page.</div></div>
        <form method="GET" class="d-flex gap-2"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search staff..."><button class="btn btn-sm btn-light mb-0">Search</button></form>
    </div>
    <div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0 table-hover">
        <thead><tr><th>Staff</th><th>Roles</th><th>Exam types</th><th>Last sign-in</th><th></th></tr></thead>
        <tbody>
        @foreach ($admins as $a)
            <tr class="table-light"><td><div class="fw-semibold">{{ $a->name }} <span class="badge bg-dark">Admin</span></div><div class="small text-muted">{{ $a->email }}</div></td><td colspan="3" class="small text-muted">Everything, in every exam type.</td><td></td></tr>
        @endforeach
        @forelse ($staff as $u)
            @php $mine = collect($access[$u->id] ?? [])->pluck('exam_id')->map(fn ($v) => (int) $v)->all(); @endphp
            <tr>
                <td><div class="fw-semibold">{{ $u->name }}</div><div class="small text-muted">{{ $u->email }}</div></td>
                <td>@forelse ($u->roles as $r)<span class="badge bg-light text-dark border">{{ $r->name }}</span> @empty<span class="text-muted small">no role</span>@endforelse</td>
                <td>
                    @if (count($mine) === 0)<span class="badge bg-danger">none</span>
                    @elseif (count($mine) === $exams->count())<span class="badge bg-success">all</span>
                    @else @foreach ($exams->whereIn('id', $mine) as $e)<span class="badge bg-info">{{ $e->short_name ?: $e->name }}</span> @endforeach @endif
                </td>
                <td class="small text-nowrap">{{ isset($lastLogin[$u->id]) ? \Illuminate\Support\Carbon::parse($lastLogin[$u->id])->diffForHumans() : '—' }}</td>
                <td class="text-end text-nowrap"><a href="{{ url('admin/staff-access/' . $u->id . '/edit') }}" class="btn btn-sm btn-primary mb-0">Edit access</a>
                    @if (\App\Support\StaffAccess::isAdmin(auth()->user()) && !session('impersonator_id'))
                        <form action="{{ url('admin/impersonate/' . $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Open the panel as {{ addslashes($u->name) }}? You will see what they see; signing out brings you back here.')">@csrf
                            <button class="btn btn-sm btn-warning mb-0" title="Open the panel as this staff member"><i class="bi bi-box-arrow-in-right"></i> Login as</button></form>
                    @endif
                    <a href="{{ url('admin/activity-logs?staff=' . $u->id) }}" class="btn btn-sm btn-light mb-0" title="Their activity"><i class="bi bi-clock-history"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No staff found.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
