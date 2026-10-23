@extends('admin.main-layout')
@section('main')
@php $money = fn ($v) => '₹' . number_format((float) $v, 2); @endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div><h3 class="mb-0">Admissions &amp; Revenue</h3><div class="small text-muted">Every payment and refund, online and entered by staff.</div></div>
        <a href="{{ url('admin/transactions/export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
    @include('admin.finance._tabs')
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Order no., gateway id, student..."></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="gateway" class="form-select form-select-sm"><option value="">Any payment</option>@foreach (\App\Models\PaymentTransaction::GATEWAYS as $k => $l)<option value="{{ $k }}" @selected(request('gateway') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="channel" class="form-select form-select-sm"><option value="">Any channel</option>@foreach (\App\Services\Payments\AdmissionService::CHANNELS as $k => $l)<option value="{{ $k }}" @selected(request('channel') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-1"><select name="type" class="form-select form-select-sm"><option value="">All</option><option value="payment" @selected(request('type') === 'payment')>Payments</option><option value="refund" @selected(request('type') === 'refund')>Refunds</option></select></div>
        <div class="col-6 col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>@foreach (array_keys(\App\Models\PaymentTransaction::STATUSES) as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ url('admin/transactions') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>
    <div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0 table-hover">
        <thead><tr><th>When</th><th>Type</th><th>Via</th><th>Channel</th><th>Order no.</th><th>Student</th><th>Course</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($list as $t)
            <tr>
                <td class="small text-nowrap">{{ ($t->paid_at ?? $t->created_at)->format('d M Y, h:i A') }}</td>
                <td>{{ $t->type === 'refund' ? 'Refund' : 'Payment' }}</td>
                <td class="small">{{ $t->gatewayLabel() }}{{ $t->method ? ' · ' . $t->method : '' }}</td>
                <td class="small">{{ \App\Services\Payments\AdmissionService::CHANNELS[$t->channel] ?? ($t->channel ?: '—') }}</td>
                <td class="small"><a href="{{ url('admin/admissions/' . $t->admission_id) }}"><code>{{ $t->merchant_ref }}</code></a>@if ($t->gateway_payment_id)<div class="text-muted">{{ $t->gateway_payment_id }}</div>@endif</td>
                <td class="small">{{ $t->user?->name ?: '#' . $t->user_id }}<div class="text-muted">{{ $t->user?->mobile }}</div></td>
                <td class="small">{{ $t->course?->title }}</td>
                <td class="text-end">{{ $t->type === 'refund' ? '− ' : '' }}{{ $money($t->amount) }}</td>
                <td><span class="badge bg-{{ \App\Models\PaymentTransaction::STATUSES[$t->status] ?? 'secondary' }}">{{ $t->status }}</span>@if ($t->failure_reason)<div class="small text-muted" style="max-width:200px">{{ $t->failure_reason }}</div>@endif</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No transactions.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
</div>
@endsection
