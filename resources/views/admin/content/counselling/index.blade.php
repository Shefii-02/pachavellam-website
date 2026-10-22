@extends('admin.main-layout')
@section('main')
@php $S = \App\Models\CounsellingRequest::STATUSES; @endphp
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-headset me-1"></i>Counselling requests</h3>
            <div class="small text-muted">Sent from the "Talk to a counsellor" form on the home page. Call the student, then mark the request.</div>
        </div>
    </div>

    <ul class="nav nav-pills mb-3 flex-wrap gap-1">
        <li class="nav-item"><a class="nav-link py-1 {{ !request('status') ? 'active' : '' }}" href="{{ url('admin/counselling') }}">All <span class="badge bg-secondary">{{ $counts->sum() }}</span></a></li>
        @foreach ($S as $k => [$label, $color])
            <li class="nav-item"><a class="nav-link py-1 {{ request('status') === $k ? 'active' : '' }}" href="{{ url('admin/counselling?status=' . $k) }}">{{ $label }} <span class="badge bg-{{ $color }}">{{ $counts[$k] ?? 0 }}</span></a></li>
        @endforeach
    </ul>

    <form class="row g-2 mb-3" method="GET">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div class="col-md-4"><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Search name or phone"></div>
        <div class="col-md-3"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->id }}" @selected((string) request('exam') === (string) $e->id)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-auto"><button class="btn btn-sm btn-primary mb-0">Filter</button></div>
    </form>

    <div class="table-responsive border rounded-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Student</th><th>Preparing for</th><th>Best time</th><th>Message</th><th>Received</th><th style="min-width:260px">Follow up</th></tr></thead>
            <tbody>
                @forelse ($list as $r)
                    <tr>
                        <td><div class="fw-semibold">{{ $r->name }}</div>
                            <a href="tel:{{ $r->phone }}" class="small"><i class="bi bi-telephone"></i> {{ $r->phone }}</a>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', strlen(preg_replace('/\D/', '', $r->phone)) === 10 ? '91' . $r->phone : $r->phone) }}" target="_blank" rel="noopener" class="small ms-2 text-success"><i class="bi bi-whatsapp"></i></a>
                            @if ($r->email)<div class="small text-muted">{{ $r->email }}</div>@endif</td>
                        <td>@if ($r->exam)<span class="badge text-white" style="background:{{ $r->exam->color ?: '#2563eb' }}">{{ $r->exam->short_name ?: $r->exam->name }}</span>@else<span class="text-muted small">-</span>@endif</td>
                        <td class="small">{{ $r->preferred_time ?: '-' }}</td>
                        <td class="small" style="max-width:260px;white-space:pre-wrap">{{ $r->message }}</td>
                        <td class="small text-nowrap">{{ $r->created_at->format('d M Y, h:i a') }}</td>
                        <td>
                            <form method="POST" action="{{ url('admin/counselling/' . $r->id) }}" class="d-flex flex-column gap-1">
                                @csrf @method('PUT')
                                <div class="d-flex gap-1">
                                    <select name="status" class="form-select form-select-sm">@foreach ($S as $k => [$label])<option value="{{ $k }}" @selected($r->status === $k)>{{ $label }}</option>@endforeach</select>
                                    <button class="btn btn-sm btn-success mb-0"><i class="bi bi-check"></i></button>
                                </div>
                                <input name="staff_note" class="form-control form-control-sm" maxlength="2000" value="{{ $r->staff_note }}" placeholder="Note (optional)">
                                @if ($r->handler)<div class="small text-muted">by {{ $r->handler->name }}, {{ $r->updated_at->diffForHumans() }}</div>@endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No counselling requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $list->links() }}</div>
</div>
@endsection
