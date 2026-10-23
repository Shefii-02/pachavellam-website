@extends('admin.main-layout')
@section('main')
@php $money = fn ($v) => '₹' . number_format((float) $v, 2); @endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div><h3 class="mb-0">Admissions &amp; Revenue</h3><div class="small text-muted">Who joined which course, payments, cancellations and refunds.</div></div>
        <div class="d-flex gap-2">
            <a href="{{ url('admin/admissions/export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-download me-1"></i>Export CSV</a>
            <a href="{{ url('admin/admissions/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-person-plus me-1"></i>Add student to a course</a>
        </div>
    </div>
    @include('admin.finance._tabs')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Student name, mobile or email..."></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-3"><select name="course" class="form-select form-select-sm"><option value="">All courses</option>@foreach ($courses as $c)<option value="{{ $c->id }}" @selected(request('course') == $c->id)>{{ $c->title }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>@foreach (['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled', 'pending' => 'Payment pending'] as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="source" class="form-select form-select-sm"><option value="">Any channel</option>@foreach (\App\Services\Payments\AdmissionService::CHANNELS as $k => $l)<option value="{{ $k }}" @selected(request('source') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="gateway" class="form-select form-select-sm"><option value="">Any payment</option>@foreach (\App\Models\PaymentTransaction::GATEWAYS as $k => $l)<option value="{{ $k }}" @selected(request('gateway') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" title="From"></div>
        <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm" title="To"></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ url('admin/admissions') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <div class="row g-2 mb-3 text-center">
        @foreach ([['Admissions', number_format($totals->c)], ['Fee value', $money($totals->value)], ['Paid', $money($totals->paid)], ['Due', $money($totals->due)], ['Refunded', $money($totals->refunded)], ['Commission', $money($totals->commission)], ['Collected − refunds − commission', $money($totals->paid - $totals->refunded - $totals->commission)]] as [$k, $v])
            <div class="col-6 col-md"><div class="border rounded py-2"><div class="small text-muted">{{ $k }}</div><div class="fw-semibold">{{ $v }}</div></div></div>
        @endforeach
    </div>

    <div class="card bg-transparent border rounded-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead><tr><th>#</th><th>Student</th><th>Course</th><th>Paid</th><th>Via</th><th>Admitted</th><th>Valid until</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($list as $a)
                    @php [$stLabel, $stColor] = \App\Models\CourseAdmission::STATUSES[$a->displayStatus()]; @endphp
                    <tr>
                        <td>{{ $list->firstItem() + $loop->index }}</td>
                        <td><div class="fw-semibold">{{ $a->user?->name ?: 'Student #' . $a->user_id }}</div><div class="small text-muted">{{ $a->user?->mobile ?: $a->user?->email }}</div></td>
                        <td><div>{{ $a->course?->title }}</div><div class="small text-muted">{{ $a->exam?->short_name ?: $a->exam?->name }}</div></td>
                        <td>{{ $money($a->amount_paid) }}@if ($a->refunded_amount > 0)<div class="small text-danger">− {{ $money($a->refunded_amount) }} refunded</div>@endif @if ($a->discount_amount > 0)<div class="small text-muted">coupon/discount {{ $money($a->discount_amount) }}</div>@endif @if ($a->status === 'active' && $a->amount - $a->amount_paid > 0.009)<div class="small text-warning">due {{ $money($a->amount - $a->amount_paid) }}</div>@endif</td>
                        <td class="small">{{ \App\Models\PaymentTransaction::GATEWAYS[$a->gateway] ?? '—' }}<div class="text-muted">{{ \App\Services\Payments\AdmissionService::CHANNELS[$a->source] ?? $a->source }}</div></td>
                        <td class="small text-nowrap">{{ $a->admitted_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        <td class="small text-nowrap">{{ $a->admitted_at ? ($a->valid_until?->format('d M Y') ?? 'lifetime') : '—' }}</td>
                        <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                        <td class="text-end"><a href="{{ url('admin/admissions/' . $a->id) }}" class="btn btn-sm btn-light mb-0">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No admissions match.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
</div>
@endsection
