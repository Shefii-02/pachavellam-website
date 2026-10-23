@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div><h3 class="mb-0">Notifications</h3><div class="small text-muted">New joins, exam type interests, admissions, cancellations and failed payments from the last {{ config('staff_access.notification_days') }} days. {{ $unread }} unread.</div></div>
        <form action="{{ url('admin/notifications/read') }}" method="POST">@csrf<button class="btn btn-sm btn-light mb-0"><i class="bi bi-check2-all me-1"></i>Mark all read</button></form>
    </div>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-6 col-md-3"><select name="type" class="form-select form-select-sm"><option value="">All kinds</option>@foreach (['new_join' => 'New joins', 'interest' => 'Exam type interest', 'admission' => 'Admissions', 'cancellation' => 'Cancellations', 'payment_failed' => 'Failed payments'] as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="state" class="form-select form-select-sm"><option value="">Read + unread</option><option value="unread" @selected(request('state') === 'unread')>Unread only</option></select></div>
        <div class="col-auto"><button class="btn btn-sm btn-light mb-0">Filter</button></div>
    </form>
    <div class="card bg-transparent border rounded-3"><div class="list-group list-group-flush">
        @forelse ($list as $n)
            @php [$icon, $color] = \App\Models\StaffNotification::STYLE[$n->type] ?? ['bi-bell', 'secondary']; $isRead = isset($read[$n->id]); @endphp
            <a href="{{ url('admin/notifications/' . $n->id . '/open') }}" class="list-group-item list-group-item-action bg-transparent d-flex gap-3 align-items-start {{ $isRead ? '' : 'bg-primary bg-opacity-10' }}">
                <span class="text-{{ $color }} fs-4"><i class="bi {{ $icon }}"></i></span>
                <span class="flex-grow-1"><span class="fw-semibold d-block">{{ $n->title }} @unless ($isRead)<span class="badge bg-danger">new</span>@endunless</span>
                    @if ($n->body)<span class="small text-muted d-block">{{ $n->body }}</span>@endif
                    <span class="small text-muted">{{ $n->created_at->format('d M Y, h:i A') }} · {{ $n->created_at->diffForHumans() }}{{ $n->exam ? ' · ' . ($n->exam->short_name ?: $n->exam->name) : '' }}</span></span>
            </a>
        @empty
            <div class="list-group-item bg-transparent text-center text-muted py-4">Nothing to show.</div>
        @endforelse
    </div></div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
</div>
@endsection
