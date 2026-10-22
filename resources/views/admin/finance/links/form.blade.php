@extends('admin.main-layout')
@section('main')
@php $selCourse = old('course_id', $selectedCourse); @endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <a href="{{ url('admin/payment-links') }}" class="small"><i class="bi bi-arrow-left"></i> Payment links</a>
    <h3 class="mb-3 mt-1">New payment link</h3>
    <form action="{{ url('admin/payment-links') }}" method="POST">
        @csrf
        <input type="hidden" name="user_id" id="userId" value="{{ old('user_id') }}">
        <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body"><div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Student</label>
                <input type="text" id="studentQ" class="form-control" placeholder="Search by name, mobile or email..." autocomplete="off">
                <div id="studentResults" class="list-group position-absolute shadow-sm" style="z-index:5;max-width:460px"></div>
                <div class="small text-success mt-1" id="studentPicked">{{ old('user_id') ? 'Student chosen (id ' . old('user_id') . ')' : '' }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Course <span class="text-muted">(paid, published)</span></label>
                <select name="course_id" class="form-select" required>
                    <option value="">Choose course...</option>
                    @foreach ($exams as $e)
                        @php $cs = $courses->where('exam_id', $e->id); @endphp
                        @if ($cs->isNotEmpty())
                            <optgroup label="{{ $e->short_name ?: $e->name }}">
                                @foreach ($cs as $c)@php $p = ($c->discount_price !== null && $c->discount_price < $c->price) ? $c->discount_price : $c->price; @endphp<option value="{{ $c->id }}" @selected($selCourse == $c->id)>{{ $c->title }} — ₹{{ number_format($p) }}</option>@endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Sent through</label>
                <select name="channel" class="form-select">@foreach (['whatsapp' => 'WhatsApp', 'web' => 'Website', 'android' => 'Android app', 'ios' => 'iOS app', 'admin' => 'Other (staff)'] as $k => $l)<option value="{{ $k }}" @selected(old('channel', 'whatsapp') === $k)>{{ $l }}</option>@endforeach</select>
                <div class="form-text">Used to split the revenue by channel.</div></div>
            <div class="col-md-3"><label class="form-label">Coupon code <span class="text-muted">(optional)</span></label><input type="text" name="coupon_code" class="form-control" value="{{ old('coupon_code') }}"></div>
            <div class="col-md-3"><label class="form-label">Link valid for (hours)</label><input type="number" name="hours" min="1" max="720" class="form-control" value="{{ old('hours', 48) }}"></div>
            <div class="col-md-3"><label class="form-label">Internal note <span class="text-muted">(not sent)</span></label><input type="text" name="note" class="form-control" maxlength="290" value="{{ old('note') }}"></div>
            <div class="col-12">
                <label class="form-label">Message to send with the link <span class="text-muted">(optional)</span></label>
                <textarea name="message" rows="4" class="form-control" maxlength="1500" placeholder="Hello {name}, your seat for {course} is reserved. Fee {amount}. Pay here: {link}">{{ old('message') }}</textarea>
                <div class="form-text">You can use <code>{name}</code> <code>{course}</code> <code>{amount}</code> <code>{link}</code>. Empty = a standard message. The link is always added. After creating, send it by WhatsApp, SMS or email, or copy it.</div>
            </div>
        </div></div></div>
        <div class="d-flex gap-2"><button class="btn btn-primary mb-0">Create link</button><a href="{{ url('admin/payment-links') }}" class="btn btn-light mb-0">Cancel</a></div>
    </form>
</div>
<script>
(function () {
    var q = document.getElementById('studentQ'), box = document.getElementById('studentResults'), hid = document.getElementById('userId'), picked = document.getElementById('studentPicked'), timer;
    q.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch(@json(url('admin/admissions/students')) + '?q=' + encodeURIComponent(q.value), { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }).then(function (rows) {
                box.innerHTML = '';
                rows.forEach(function (s) {
                    var a = document.createElement('a'); a.href = '#'; a.className = 'list-group-item list-group-item-action py-1 small'; a.textContent = s.label;
                    a.addEventListener('click', function (e) { e.preventDefault(); hid.value = s.id; picked.textContent = 'Chosen: ' + s.label; box.innerHTML = ''; q.value = ''; });
                    box.appendChild(a);
                });
                if (!rows.length) box.innerHTML = '<div class="list-group-item small text-muted">No student found.</div>';
            });
        }, 250);
    });
})();
</script>
@endsection
