@extends('admin.main-layout')
@section('main')
@php $selCourse = old('course_id', $selectedCourse); @endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <a href="{{ url('admin/admissions') }}" class="small"><i class="bi bi-arrow-left"></i> Admissions</a>
    <h3 class="mb-3 mt-1">Add a student to a course</h3>
    <form action="{{ url('admin/admissions') }}" method="POST">
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
                <label class="form-label">Course</label>
                <select name="course_id" id="course" class="form-select" required>
                    <option value="">Choose course...</option>
                    @foreach ($exams as $e)
                        @php $cs = $courses->where('exam_id', $e->id); @endphp
                        @if ($cs->isNotEmpty())
                            <optgroup label="{{ $e->short_name ?: $e->name }}">
                                @foreach ($cs as $c)<option value="{{ $c->id }}" data-price="{{ $c->course_type === 'free' ? 0 : ($c->discount_price !== null && $c->discount_price < $c->price ? $c->discount_price : $c->price) }}" @selected($selCourse == $c->id)>{{ $c->title }} — {{ $c->course_type === 'free' ? 'free' : '₹' . number_format(($c->discount_price !== null && $c->discount_price < $c->price) ? $c->discount_price : $c->price) }}</option>@endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <div class="form-text">Only published courses are listed.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">How was the fee received?</label>
                <select name="method" id="method" class="form-select">@foreach ($methods as $k => $l)<option value="{{ $k }}" @selected(old('method', 'cash') === $k)>{{ $l }}</option>@endforeach</select>
            </div>
            <div class="col-md-4" id="amountBox">
                <label class="form-label">Amount received (₹)</label>
                <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" placeholder="course price">
                <div class="form-text">Empty = the full fee. Less than the fee = the rest stays as balance due. The commission is worked out on the full fee when the student is admitted.</div>
            </div>
            <div class="col-md-4"><label class="form-label">Date received</label><input type="datetime-local" name="paid_at" class="form-control" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"></div>

            <div class="col-md-4"><label class="form-label">Extra discount (₹) <span class="text-muted">(optional)</span></label><input type="number" step="0.01" min="0" name="discount" class="form-control" value="{{ old('discount') }}"></div>
            <div class="col-md-4"><label class="form-label">Coupon code <span class="text-muted">(optional)</span></label><input type="text" name="coupon_code" class="form-control" value="{{ old('coupon_code') }}"></div>
            <div class="col-md-4"><label class="form-label">Course access until <span class="text-muted">(optional)</span></label><input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}"><div class="form-text">Empty = the course's own validity.</div></div>
            <div class="col-12" id="unpaidNote" style="display:none"><div class="alert alert-info py-2 small mb-0">The student gets the course now with <strong>nothing received</strong>. The fee is kept as <strong>balance due</strong> (you can record the money later on the admission page), and the commission is calculated on the fee from today.</div></div>
            <div class="col-12"><label class="form-label">Note <span class="text-muted">(receipt number, who paid ...)</span></label><textarea name="notes" rows="2" class="form-control" maxlength="1000">{{ old('notes') }}</textarea></div>
        </div></div></div>
        <div class="d-flex gap-2"><button class="btn btn-primary mb-0">Add to the course</button><a href="{{ url('admin/admissions') }}" class="btn btn-light mb-0">Cancel</a></div>
    </form>
</div>
<script>
(function () {
    var q = document.getElementById('studentQ'), box = document.getElementById('studentResults'), hid = document.getElementById('userId'), picked = document.getElementById('studentPicked'), timer;
    q.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch(@json(url('admin/admissions/students')) + '?q=' + encodeURIComponent(q.value), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); }).then(function (rows) {
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
    var method = document.getElementById('method'), amountBox = document.getElementById('amountBox');
    function m() { amountBox.style.display = (method.value === 'free' || method.value === 'unpaid') ? 'none' : ''; }
    function n() { document.getElementById('unpaidNote').style.display = (method.value === 'free' || method.value === 'unpaid') ? '' : 'none'; }
    method.addEventListener('change', function () { m(); n(); }); m(); n();
})();
</script>
@endsection
