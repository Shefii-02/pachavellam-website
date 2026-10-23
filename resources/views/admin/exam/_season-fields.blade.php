{{-- The fields of a season: used to add and to edit. $s = the season or null, $p = unique id prefix, $special = special exams are always paid. --}}
@php
    $v = fn ($f, $d = '') => old($f, $s->{$f} ?? $d);
    $paid = $special || old('is_paid', $s->is_paid ?? false);
@endphp
<div class="col-md-6"><label class="form-label small mb-0">Season title <span class="text-danger">*</span></label><input class="form-control" name="title" required maxlength="150" placeholder="e.g. History - Season 1" value="{{ $v('title') }}"></div>
<div class="col-md-6"><label class="form-label small mb-0">Tagline</label><input class="form-control" name="tagline" maxlength="200" value="{{ $v('tagline') }}"></div>
<div class="col-md-3"><label class="form-label small mb-0">Built around</label>
    <select name="basis" class="form-select"><option value="">General</option>@foreach (\App\Models\ExamSeason::BASES as $bk => $bl)<option value="{{ $bk }}" {{ $v('basis') === $bk ? 'selected' : '' }}>{{ $bl }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label small mb-0">Which subject / category / exam</label><input class="form-control" name="basis_name" maxlength="120" placeholder="e.g. Indian History" value="{{ $v('basis_name') }}"></div>
<div class="col-md-2"><label class="form-label small mb-0">Starts on</label><input type="date" class="form-control" name="starts_on" value="{{ old('starts_on', optional($s->starts_on ?? null)->format('Y-m-d')) }}"></div>
<div class="col-md-2"><label class="form-label small mb-0">Ends on</label><input type="date" class="form-control" name="ends_on" value="{{ old('ends_on', optional($s->ends_on ?? null)->format('Y-m-d')) }}"></div>
<div class="col-md-2"><label class="form-label small mb-0">Status</label><select name="status" class="form-select">@foreach (\App\Models\ExamSeason::STATUSES as $k => [$l])<option value="{{ $k }}" {{ $v('status', 'published') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label small mb-0">Cover</label><input type="file" class="form-control" name="cover_image" accept="image/*"></div>
<div class="col-md-6"><label class="form-label small mb-0">About this season</label><textarea class="form-control" name="description" rows="1" maxlength="3000">{{ $v('description') }}</textarea></div>

{{-- free or paid --}}
<div class="col-12"><div class="border rounded-3 p-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <strong>Access</strong>
        @if ($special)
            <span class="badge bg-warning text-dark">Paid - special exams are always paid</span><input type="hidden" name="is_paid" value="1">
        @else
            <div class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="is_paid" id="{{ $p }}f" value="0" {{ $paid ? '' : 'checked' }} onchange="pvSeasonPaid('{{ $p }}')"><label class="form-check-label" for="{{ $p }}f">Free - every exam is free</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="is_paid" id="{{ $p }}p" value="1" {{ $paid ? 'checked' : '' }} onchange="pvSeasonPaid('{{ $p }}')"><label class="form-check-label" for="{{ $p }}p">Paid - bought like a course</label></div>
        @endif
    </div>
    <div id="{{ $p }}box" class="row g-2 mt-1" style="{{ $paid ? '' : 'display:none' }}">
        <div class="col-md-3"><label class="form-label small mb-0">Price (₹) <span class="text-danger">*</span></label><input type="number" min="1" step="0.01" class="form-control" name="price" value="{{ $v('price') ?: '' }}" placeholder="e.g. 299"></div>
        <div class="col-md-3"><label class="form-label small mb-0">Offer price (₹)</label><input type="number" min="0" step="0.01" class="form-control" name="discount_price" value="{{ $v('discount_price') ?: '' }}" placeholder="optional"></div>
        <div class="col-md-3"><label class="form-label small mb-0">Valid for</label>
            <select name="validity_type" class="form-select" onchange="pvSeasonValidity('{{ $p }}')">@foreach (['lifetime' => 'Lifetime', 'days' => 'Days after buying', 'date' => 'Until a date'] as $vk => $vl)<option value="{{ $vk }}" {{ $v('validity_type', 'lifetime') === $vk ? 'selected' : '' }}>{{ $vl }}</option>@endforeach</select></div>
        <div class="col-md-3" id="{{ $p }}vd" style="display:none"><label class="form-label small mb-0">Days</label><input type="number" min="1" max="3650" class="form-control" name="validity_days" value="{{ $v('validity_days') }}"></div>
        <div class="col-md-3" id="{{ $p }}vt" style="display:none"><label class="form-label small mb-0">Until</label><input type="date" class="form-control" name="valid_until" value="{{ old('valid_until', optional($s->valid_until ?? null)->format('Y-m-d')) }}"></div>
        <div class="col-md-12"><label class="form-label small mb-0">First exams that stay free</label>
            <div class="input-group" style="max-width:420px"><input type="number" min="0" max="500" class="form-control" name="free_exams" value="{{ $v('free_exams', 0) }}"><span class="input-group-text">exam(s)</span></div>
            <div class="form-text">The first 1, 2, 3 ... exams (by date) open for everyone without buying. You can also tick <b>Free access</b> on any single exam when you create it - those are free too. Everything else opens after the purchase, for the validity chosen.</div></div>
    </div>
</div></div>
