@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div><h3 class="mb-0">Staff activity</h3><div class="small text-muted">Every create, change, delete, restore and sign-in by staff. {{ number_format($today) }} events today.</div></div>
        <a href="{{ url('admin/activity-logs/export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-6 col-md-2"><select name="staff" class="form-select form-select-sm"><option value="">All staff</option>@foreach ($staff as $s)<option value="{{ $s->id }}" @selected(request('staff') == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="action" class="form-select form-select-sm"><option value="">Any action</option>@foreach ($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst(str_replace('_', ' ', $a)) }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="type" class="form-select form-select-sm"><option value="">Any item</option>@foreach ($types as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->id }}" @selected(request('exam') == $e->id)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
        <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search the item or details..."></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-primary mb-0">Filter</button><a href="{{ url('admin/activity-logs') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>
    <div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>When</th><th>Staff</th><th>Action</th><th>Item</th><th>Exam type</th><th>Details</th><th>From</th></tr></thead>
        <tbody>
        @forelse ($list as $r)
            <tr>
                <td class="small text-nowrap">{{ $r->created_at->format('d M Y, h:i:s A') }}</td>
                <td class="small">{{ $r->user_name ?: 'System' }}</td>
                <td><span class="badge bg-{{ \App\Models\ActivityLog::COLORS[$r->action] ?? 'secondary' }}">{{ str_replace('_', ' ', $r->action) }}</span></td>
                <td class="small" style="max-width:280px"><div class="text-muted">{{ $r->subject_type }}</div><div class="text-truncate">{{ $r->subject_label }}</div></td>
                <td class="small">{{ $r->exam?->short_name ?: $r->exam?->name ?: '—' }}</td>
                <td class="small" style="max-width:360px">
                    {{ $r->description }}
                    @if ($r->changes)
                        <details><summary class="text-primary" style="cursor:pointer">{{ count($r->changes) }} change(s)</summary>
                            @foreach ($r->changes as $field => $c)<div><code>{{ $field }}</code>: <span class="text-danger">{{ \Illuminate\Support\Str::limit((string) ($c[0] ?? ''), 60) }}</span> → <span class="text-success">{{ \Illuminate\Support\Str::limit((string) ($c[1] ?? ''), 60) }}</span></div>@endforeach
                        </details>
                    @endif
                </td>
                <td class="small text-muted">{{ $r->ip }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No activity matches.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
</div>
@endsection
