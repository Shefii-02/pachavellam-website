@extends('admin.main-layout')
@section('main')
@php $newId = session('new_link'); $badge = ['active' => 'primary', 'paid' => 'success', 'expired' => 'secondary', 'cancelled' => 'dark']; @endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div><h3 class="mb-0">Admissions &amp; Revenue</h3><div class="small text-muted">Payment links: the student pays on a web page; the order is made when Pay is pressed, and the course is added once the payment is confirmed.</div></div>
        <a href="{{ url('admin/payment-links/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-link-45deg me-1"></i>New payment link</a>
    </div>
    @include('admin.finance._tabs')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Student name or mobile..."></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="channel" class="form-select form-select-sm"><option value="">Any channel</option>@foreach (['whatsapp' => 'WhatsApp', 'web' => 'Website', 'android' => 'Android', 'ios' => 'iOS', 'admin' => 'Staff'] as $k => $l)<option value="{{ $k }}" @selected(request('channel') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>@foreach (['active' => 'Waiting for payment', 'paid' => 'Paid', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ url('admin/payment-links') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0 table-hover">
        <thead><tr><th>Created</th><th>Student</th><th>Course</th><th>Channel</th><th>Opened</th><th>Expires</th><th>Status</th><th>Link</th></tr></thead>
        <tbody>
        @forelse ($list as $l)
            @php $st = $l->displayStatus(); @endphp
            <tr class="{{ $newId === $l->id ? 'table-active' : '' }}">
                <td class="small text-nowrap">{{ $l->created_at->format('d M Y, h:i A') }}</td>
                <td><div class="fw-semibold">{{ $l->user?->name ?: '#' . $l->user_id }}</div><div class="small text-muted">{{ $l->user?->mobile }}</div></td>
                <td class="small">{{ $l->course?->title }}<div class="text-muted">{{ $l->exam?->short_name ?: $l->exam?->name }}{{ $l->coupon_code ? ' · coupon ' . $l->coupon_code : '' }}</div></td>
                <td class="small">{{ \App\Services\Payments\AdmissionService::CHANNELS[$l->channel] ?? $l->channel }}</td>
                <td class="small">{{ $l->opens }}×{{ $l->last_opened_at ? ' · ' . $l->last_opened_at->diffForHumans() : '' }}</td>
                <td class="small text-nowrap">{{ $l->expires_at?->format('d M, h:i A') ?? '—' }}</td>
                <td><span class="badge bg-{{ $badge[$st] ?? 'secondary' }}">{{ ['active' => 'Waiting', 'paid' => 'Paid', 'expired' => 'Expired', 'cancelled' => 'Cancelled'][$st] }}</span>@if ($l->admission_id)<div class="small"><a href="{{ url('admin/admissions/' . $l->admission_id) }}">order #{{ $l->admission_id }}</a></div>@endif</td>
                <td class="text-nowrap">
                    @if ($st === 'active')
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light mb-0" onclick="copyText(this, @js($l->url()))" title="Copy the link"><i class="bi bi-clipboard"></i> Link</button>
                            <button type="button" class="btn btn-sm btn-light mb-0" onclick="copyText(this, @js($l->text()))" title="Copy the message with the link"><i class="bi bi-chat-text"></i> Message</button>
                            <a href="{{ $l->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-success mb-0" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            <a href="{{ $l->smsUrl() }}" class="btn btn-sm btn-info text-white mb-0" title="SMS"><i class="bi bi-chat-dots"></i></a>
                            <a href="{{ $l->emailUrl() }}" class="btn btn-sm btn-secondary mb-0" title="Email"><i class="bi bi-envelope"></i></a>
                            <button type="button" class="btn btn-sm btn-primary mb-0 d-none share-native" onclick="shareNative(@js($l->text()), @js($l->url()))" title="Share from this device"><i class="bi bi-share"></i></button>
                        </div>
                        <form action="{{ url('admin/payment-links/' . $l->id . '/cancel') }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this link?')">@csrf<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-x-lg"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No payment links yet.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $list->links() }}</div>
</div>
<script>
function copyText(btn, text) {
    var done = function () { var o = btn.innerHTML; btn.innerHTML = '<i class="bi bi-check2"></i> Copied'; setTimeout(function () { btn.innerHTML = o; }, 1500); };
    if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done); } else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done(); }
}
function shareNative(text, url) { navigator.share({ text: text, url: url }).catch(function () {}); }
if (navigator.share) { document.querySelectorAll('.share-native').forEach(function (b) { b.classList.remove('d-none'); }); }
</script>
@endsection
