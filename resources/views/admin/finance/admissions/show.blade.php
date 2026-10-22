@extends('admin.main-layout')
@section('main')
@php
    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    [$stLabel, $stColor] = \App\Models\CourseAdmission::STATUSES[$a->displayStatus()];
@endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <a href="{{ url('admin/admissions') }}" class="small"><i class="bi bi-arrow-left"></i> Admissions</a>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mt-1 mb-3">
        <div>
            <h3 class="mb-1">Admission #{{ $a->id }} <span class="badge bg-{{ $stColor }} fs-6">{{ $stLabel }}</span>@if ($a->trashed()) <span class="badge bg-dark fs-6">removed</span>@endif</h3>
            <div class="small text-muted">{{ $a->user?->name ?: 'Student #' . $a->user_id }} · {{ $a->user?->mobile }} {{ $a->user?->email }} → <strong>{{ $a->course?->title }}</strong> ({{ $a->exam?->short_name ?: $a->exam?->name }})</div>
        </div>
        @unless ($a->trashed())
        <div class="d-flex gap-2">
            @if ($a->status !== 'cancelled')<button class="btn btn-sm btn-outline-danger mb-0" data-bs-toggle="collapse" data-bs-target="#cancelBox"><i class="bi bi-x-circle me-1"></i>Cancel admission</button>@endif
            @if ($a->status !== 'active')
                <form action="{{ url('admin/admissions/' . $a->id) }}" method="POST" onsubmit="return confirm('Remove this admission record? Its payment entries stay in the accounts.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-dark mb-0"><i class="bi bi-trash me-1"></i>Remove record</button></form>
            @endif
        </div>
        @endunless
    </div>

    @if ($a->status === 'active' && !$a->trashed() && $a->amount - $a->amount_paid > 0.009)
        <form action="{{ url('admin/admissions/' . $a->id . '/payment') }}" method="POST" class="card bg-transparent border border-success rounded-3 mb-3"><div class="card-body">
            @csrf
            <h6 class="text-success">Record money received · balance due {{ $money($a->amount - $a->amount_paid) }}</h6>
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small mb-0">Received through</label><select name="method" class="form-select form-select-sm">@foreach (['cash' => 'Cash', 'upi' => 'UPI / transfer', 'bank' => 'Bank deposit'] as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label small mb-0">Amount (₹)</label><input type="number" step="0.01" min="0.01" max="{{ $a->amount - $a->amount_paid }}" name="amount" class="form-control form-control-sm" value="{{ old('amount', $a->amount - $a->amount_paid) }}" required></div>
                <div class="col-md-3"><label class="form-label small mb-0">Date</label><input type="datetime-local" name="paid_at" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}"></div>
                <div class="col-md-3"><button class="btn btn-sm btn-success mb-0">Record payment</button></div>
            </div>
        </div></form>
    @endif

    @if ($a->status === 'active' && !$a->trashed())
    <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="cancelBox">
        <form action="{{ url('admin/admissions/' . $a->id . '/cancel') }}" method="POST" class="card bg-transparent border border-danger rounded-3 mb-3"><div class="card-body">
            @csrf
            <h6 class="text-danger">Cancel this admission</h6>
            <p class="small text-muted mb-2">The student loses the course at once. The coupon (if any) is given back. Paid so far {{ $money($a->amount_paid) }}, refundable {{ $money($refundable) }}.</p>
            <div class="row g-2">
                <div class="col-md-5"><label class="form-label small mb-0">Reason</label><input type="text" name="reason" class="form-control form-control-sm" required maxlength="290" value="{{ old('reason') }}"></div>
                <div class="col-md-4"><label class="form-label small mb-0">Refund</label>
                    <select name="refund_mode" id="refundMode" class="form-select form-select-sm">
                        <option value="none" @selected(old('refund_mode', 'none') === 'none')>No refund</option>
                        <option value="manual" @selected(old('refund_mode') === 'manual')>Record a refund (money returned outside)</option>
                        @if ($hasOnlinePayment)<option value="gateway" @selected(old('refund_mode') === 'gateway')>Refund through the payment gateway</option>@endif
                    </select></div>
                <div class="col-md-3" id="refundAmt" style="display:none"><label class="form-label small mb-0">Refund amount (₹)</label><input type="number" step="0.01" min="0" max="{{ $refundable }}" name="refund_amount" class="form-control form-control-sm" value="{{ old('refund_amount', $refundable) }}"></div>
                <div class="col-12"><button class="btn btn-sm btn-danger mb-0" onclick="return confirm('Cancel this admission?')">Cancel admission</button></div>
            </div>
        </div></form>
    </div>
    @elseif ($a->status === 'pending')
        <div class="alert alert-warning py-2 small">Order waiting for payment{{ $a->created_by ? ' (made by staff)' : '' }}. When the student pays, the course is added and the commission is fixed. You can remove this record.</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body small">
            <h6>Money</h6>
            @foreach ([['Course price', $a->original_amount], ['Coupon / discount', -$a->discount_amount], ['To pay', $a->amount], ['Paid', $a->amount_paid], ['Balance due', max(0, $a->amount - $a->amount_paid)], ['Refunded', -$a->refunded_amount], ['Kept', $a->retained()], ['Commission (' . rtrim(rtrim(number_format($a->commission_percent, 2), '0'), '.') . '%)', -$a->commission_amount], ['After commission', $a->net()]] as [$k, $v])
                <div class="d-flex justify-content-between border-bottom py-1"><span>{{ $k }}</span><strong>{{ $v < 0 ? '− ' . $money(-$v) : $money($v) }}</strong></div>
            @endforeach
            @if ($a->coupon_code)<div class="mt-2">Coupon <code>{{ $a->coupon_code }}</code></div>@endif
        </div></div></div>
        <div class="col-md-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body small">
            <h6>Details</h6>
            <div class="d-flex justify-content-between border-bottom py-1"><span>Source</span><strong>{{ \App\Services\Payments\AdmissionService::CHANNELS[$a->source] ?? $a->source }}{{ $creator ? ' (' . $creator . ')' : '' }}</strong></div>
            <div class="d-flex justify-content-between border-bottom py-1"><span>Payment</span><strong>{{ \App\Models\PaymentTransaction::GATEWAYS[$a->gateway] ?? '—' }}</strong></div>
            <div class="d-flex justify-content-between border-bottom py-1"><span>Admitted</span><strong>{{ $a->admitted_at?->format('d M Y, h:i A') ?? '—' }}</strong></div>
            <div class="d-flex justify-content-between border-bottom py-1"><span>Access until</span><strong>{{ $a->admitted_at ? ($a->valid_until?->format('d M Y') ?? 'lifetime') : '—' }}</strong></div>
            @if ($a->cancelled_at)<div class="d-flex justify-content-between border-bottom py-1"><span>Cancelled</span><strong>{{ $a->cancelled_at->format('d M Y, h:i A') }}</strong></div><div class="py-1"><span class="text-muted">Reason:</span> {{ $a->cancel_reason }}</div>@endif
            @if ($a->notes)<div class="py-1"><span class="text-muted">Note:</span> {{ $a->notes }}</div>@endif
        </div></div></div>
    </div>

    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>Payments and refunds</strong></div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>When</th><th>Type</th><th>Via</th><th>Order no.</th><th>Gateway payment id</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($a->transactions as $t)
                <tr>
                    <td class="small text-nowrap">{{ ($t->paid_at ?? $t->created_at)->format('d M Y, h:i A') }}</td>
                    <td>{{ $t->type === 'refund' ? 'Refund' : 'Payment' }}</td>
                    <td class="small">{{ $t->gatewayLabel() }}{{ $t->method ? ' · ' . $t->method : '' }}</td>
                    <td class="small"><code>{{ $t->merchant_ref }}</code></td>
                    <td class="small">{{ $t->gateway_payment_id ?: '—' }}</td>
                    <td class="text-end">{{ $t->type === 'refund' ? '− ' : '' }}{{ $money($t->amount) }}</td>
                    <td><span class="badge bg-{{ \App\Models\PaymentTransaction::STATUSES[$t->status] ?? 'secondary' }}">{{ $t->status }}</span>@if ($t->failure_reason)<div class="small text-muted">{{ $t->failure_reason }}</div>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No payments.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
<script>
(function () {
    var m = document.getElementById('refundMode'); if (!m) return;
    function s() { document.getElementById('refundAmt').style.display = m.value === 'none' ? 'none' : ''; }
    m.addEventListener('change', s); s();
})();
</script>
@endsection
