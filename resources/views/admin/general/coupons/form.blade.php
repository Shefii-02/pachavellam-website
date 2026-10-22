@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
@php
    $editing = (bool) $coupon;
    $val = fn ($key, $default = '') => old($key, $coupon->{$key} ?? $default);
    $dt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('Y-m-d\TH:i') : '';
    $pickedCourses = (array) old('course_ids', $selected);
@endphp
<form action="{{ $editing ? admin_coupons('' . $coupon->id) : admin_coupons() }}" method="POST">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $editing ? 'Edit coupon ' . $coupon->code : 'New coupon' }}</h3>
        <a href="{{ $editing ? admin_coupons('' . $coupon->id) : admin_coupons() }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    @if ($errors->any())<div class="alert alert-danger py-2">Please fix the highlighted fields.</div>@endif
    @if ($editing && $used > 0)
        <div class="alert alert-info py-2 small">Used {{ $used }} time(s) so far. Changes apply to future uses only.</div>
    @endif

    {{-- code --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Code</h5></div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-4" id="singleCode">
                <label class="form-label">Coupon code <span class="text-danger">*</span></label>
                <input class="form-control text-uppercase" name="code" value="{{ $val('code') }}" maxlength="30" placeholder="ONAM25" style="letter-spacing:1px" {{ $hasUses ? 'readonly' : '' }}>
                <div class="form-text">{{ $hasUses ? 'Locked, because the coupon has been used.' : 'Letters, numbers, - and _. Not case sensitive for students.' }}</div>
                @error('code')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label class="form-label">Internal name</label>
                <input class="form-control" name="name" value="{{ $val('name') }}" placeholder="e.g. Onam offer, WhatsApp group">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="act" value="1" {{ old('is_active', $editing ? $coupon->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="act">Active</label>
                </div>
            </div>
            @unless ($editing)
                <div class="col-12">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="bulkOn" {{ old('bulk_count') ? 'checked' : '' }}><label class="form-check-label" for="bulkOn">Create many unique codes at once <span class="text-muted small">(for a campaign: one code per student, group or seller)</span></label></div>
                </div>
                <div class="col-md-3 bulk-only"><label class="form-label">How many codes</label><input class="form-control" type="number" min="2" max="200" name="bulk_count" value="{{ old('bulk_count', 10) }}">@error('bulk_count')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-3 bulk-only"><label class="form-label">Code prefix</label><input class="form-control text-uppercase" name="bulk_prefix" maxlength="12" value="{{ old('bulk_prefix') }}" placeholder="ONAM"><div class="form-text">Codes look like ONAM-K7M2QX4P. Each code gets the limits below on its own.</div></div>
            @endunless
            <div class="col-12">
                <label class="form-label">Description <span class="text-muted small">(for your team)</span></label>
                <textarea class="form-control" rows="2" name="description">{{ $val('description') }}</textarea>
            </div>
        </div></div>
    </div>

    {{-- discount --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Discount</h5></div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select class="form-control" name="discount_type" id="dtype">
                    <option value="percent" @selected($val('discount_type', 'percent') === 'percent')>Percentage (%)</option>
                    <option value="fixed" @selected($val('discount_type', 'percent') === 'fixed')>Fixed amount (₹)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Value <span class="text-danger">*</span></label>
                <input class="form-control" type="number" step="0.01" min="0.01" name="discount_value" id="dvalue" value="{{ $val('discount_value') }}" required>
                @error('discount_value')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 percent-only">
                <label class="form-label">Maximum discount (₹)</label>
                <input class="form-control" type="number" step="0.01" min="0.01" name="max_discount" id="dcap" value="{{ $val('max_discount') }}" placeholder="no cap">
                <div class="form-text">Caps a percentage coupon.</div>
                @error('max_discount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Example</label>
                <div class="form-control-plaintext" id="example">—</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Course price at least (₹)</label>
                <input class="form-control" type="number" step="0.01" min="0" name="min_purchase" value="{{ $val('min_purchase', 0) }}">
                <div class="form-text">Minimum purchase amount.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Course price at most (₹)</label>
                <input class="form-control" type="number" step="0.01" min="0" name="max_purchase" value="{{ $val('max_purchase') }}" placeholder="no limit">
                @error('max_purchase')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div></div>
    </div>

    {{-- limits + validity --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Usage limits &amp; validity</h5></div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Total uses allowed</label>
                <input class="form-control" type="number" min="1" name="total_limit" value="{{ $val('total_limit') }}" placeholder="unlimited">
                <div class="form-text">Across all students. Leave empty for no limit.</div>
                @error('total_limit')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Uses per student</label>
                <input class="form-control" type="number" min="1" name="per_user_limit" value="{{ old('per_user_limit', $editing ? $coupon->per_user_limit : 1) }}" placeholder="unlimited">
                <div class="form-text">How many times one student can use it.</div>
                @error('per_user_limit')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Starts</label>
                <input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at', $dt($coupon->starts_at ?? null)) }}">
                <div class="form-text">Empty = from now.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Expires</label>
                <input class="form-control" type="datetime-local" name="expires_at" value="{{ old('expires_at', $dt($coupon->expires_at ?? null)) }}">
                <div class="form-text">Empty = never.</div>
                @error('expires_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div></div>
    </div>

    {{-- exam types --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Exam types</h5></div>
        <div class="card-body">
            <div class="mb-2">
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="exam_scope" id="xs1" value="all" {{ $val('exam_scope', 'all') === 'all' ? 'checked' : '' }}><label class="form-check-label" for="xs1">All exam types</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="exam_scope" id="xs2" value="selected" {{ $val('exam_scope', 'all') === 'selected' ? 'checked' : '' }}><label class="form-check-label" for="xs2">Only these exam types</label></div>
            </div>
            <div id="examPick" style="display:none">
                @foreach ($exams as $e)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input exam-box" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="ex{{ $e->id }}" {{ in_array($e->id, (array) old('exam_ids', $selectedExams)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ex{{ $e->id }}"><span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span></label>
                    </div>
                @endforeach
            </div>
            <div class="form-text mt-1">For example a KPSC-only offer, or one coupon for KTET and UGC NET together.</div>
            @error('exam_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- courses --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Applicable courses</h5></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="applies_to" id="ap1" value="all" {{ $val('applies_to', 'all') === 'all' ? 'checked' : '' }}><label class="form-check-label" for="ap1">All courses</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="applies_to" id="ap2" value="selected" {{ $val('applies_to', 'all') === 'selected' ? 'checked' : '' }}><label class="form-check-label" for="ap2">Selected courses only</label></div>
            </div>
            <div id="coursePick" style="display:none">
                <div class="d-flex gap-2 mb-2">
                    <input type="text" id="courseSearch" class="form-control form-control-sm" placeholder="Search courses..." style="max-width:280px">
                    <button type="button" class="btn btn-sm btn-light mb-0" id="pickAll">Tick all shown</button>
                    <button type="button" class="btn btn-sm btn-light mb-0" id="pickNone">Clear</button>
                </div>
                <div class="border rounded p-2" style="max-height:260px;overflow:auto">
                    @forelse ($courses as $c)
                        <div class="form-check course-row" data-title="{{ strtolower($c->title) }}">
                            <input class="form-check-input course-box" type="checkbox" name="course_ids[]" value="{{ $c->id }}" id="c{{ $c->id }}" {{ in_array($c->id, $pickedCourses) ? 'checked' : '' }}>
                            <label class="form-check-label" for="c{{ $c->id }}">
                                <span class="badge text-white" style="background:{{ $c->exam->color ?: '#6c757d' }}">{{ $c->exam->short_name ?: $c->exam->name }}</span> {{ $c->title }}
                                @if ($c->course_type === 'paid')
                                    <span class="text-muted small">· ₹{{ number_format($c->discount_price && $c->discount_price < $c->price ? $c->discount_price : $c->price) }}</span>
                                @else
                                    <span class="badge bg-light text-dark border">free · coupons do not apply</span>
                                @endif
                                @if ($c->status !== 'published')<span class="badge bg-secondary">{{ $c->status }}</span>@endif
                            </label>
                        </div>
                    @empty
                        <div class="text-muted small">No courses yet.</div>
                    @endforelse
                </div>
                @error('course_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="form-text mt-2">Coupons only work on paid, published courses, whatever is ticked here.</div>
        </div>
    </div>

    <button class="btn btn-success mb-4"><i class="bi bi-check me-1"></i>{{ $editing ? 'Save changes' : 'Create coupon' }}</button>
</form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    function percent() { return $('#dtype').val() === 'percent'; }
    function example() {
        var v = parseFloat($('#dvalue').val()) || 0, cap = parseFloat($('#dcap').val()) || null, price = 1000, d = percent() ? price * v / 100 : v;
        if (percent() && cap) { d = Math.min(d, cap); }
        d = Math.max(0, Math.min(d, price));
        $('#example').text(v ? 'On a ₹1,000 course: pay ₹' + (price - d).toLocaleString('en-IN') + ' (save ₹' + d.toLocaleString('en-IN') + ')' : '—');
    }
    function sync() { $('.percent-only').toggle(percent()); $('#dvalue').attr('max', percent() ? 100 : ''); example(); }
    $('#dtype, #dvalue, #dcap').on('input change', sync); sync();

    function exams() { $('#examPick').toggle($('input[name=exam_scope]:checked').val() === 'selected'); }
    $('input[name=exam_scope]').on('change', exams); exams();

    function courses() { $('#coursePick').toggle($('input[name=applies_to]:checked').val() === 'selected'); }
    $('input[name=applies_to]').on('change', courses); courses();

    $('#courseSearch').on('input', function () { var q = this.value.toLowerCase(); $('.course-row').each(function () { $(this).toggle($(this).data('title').indexOf(q) > -1); }); });
    $('#pickAll').on('click', function () { $('.course-row:visible .course-box').prop('checked', true); });
    $('#pickNone').on('click', function () { $('.course-box').prop('checked', false); });

    function bulk() { var on = $('#bulkOn').is(':checked'); $('.bulk-only').toggle(on); $('#singleCode').toggle(!on); var f = $('input[name=bulk_count]'); if (!on) { f.val(''); } else if (!f.val()) { f.val(10); } }
    $('#bulkOn').on('change', bulk); bulk();
})();
</script>
@endsection
