@extends('cms.section')
@section('content2')
@php
    $kinds = \App\Models\CaSeason::KINDS;
    $base = kpsc_cms('ca-daily-exam/seasons/' . $season->id);
    $adding = (bool) $target;
@endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0"><i class="bi bi-magic me-1"></i>Build from daily exams</h3>
            <div class="small text-muted">{{ $season->name }} · questions are <strong>copied</strong> from the daily exams, so the daily exams stay as they are.</div>
        </div>
        <a href="{{ $base }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger py-2">@foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>
        @endif

        <form action="{{ $base }}/build" method="POST" id="buildForm">
            @csrf

            {{-- 1. which exam --}}
            <h6>1. Which exam gets the questions?</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Add to</label>
                    <select class="form-control" id="targetSelect" onchange="location='{{ $base }}/build?kind={{ $kind }}' + (this.value ? '&exam_id=' + this.value : '')">
                        <option value="">➕ A new exam</option>
                        @foreach ($existing as $ex)
                            <option value="{{ $ex->id }}" @selected($target && $target->id === $ex->id)>{{ $ex->exam_title ?: $kinds[$ex->exam_kind] . ' ' . $ex->slot_no }} ({{ $kinds[$ex->exam_kind] }})</option>
                        @endforeach
                    </select>
                </div>
                @if ($adding)
                    <input type="hidden" name="exam_id" value="{{ $target->id }}">
                    <div class="col-md-6 d-flex align-items-end"><div class="text-muted small">Questions already copied into this exam are skipped, so nothing is added twice.</div></div>
                @else
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="exam_kind" class="form-control" onchange="location='{{ $base }}/build?kind='+this.value">
                            @foreach (['weekend', 'weekly', 'monthly', 'other'] as $k)
                                <option value="{{ $k }}" @selected($kind === $k)>{{ $kinds[$k] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Number</label>
                        <input type="number" min="1" name="slot_no" class="form-control" value="{{ old('slot_no', $nextSlot) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Title (optional)</label>
                        <input name="exam_title" class="form-control" value="{{ old('exam_title') }}" placeholder="{{ $kinds[$kind] }} {{ $nextSlot }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Starts at</label>
                        <input type="datetime-local" name="started_at" class="form-control" value="{{ old('started_at', $suggestedStart) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ends at (optional)</label>
                        <input type="datetime-local" name="ended_at" class="form-control" value="{{ old('ended_at') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="status" id="st" value="1"><label class="form-check-label" for="st">Publish now</label></div>
                    </div>
                @endif
            </div>

            {{-- 2. period + daily exams --}}
            <h6>2. Which days does it cover?</h6>
            <div class="row g-3 mb-2">
                <div class="col-md-3"><label class="form-label">From</label><input type="date" name="period_start" id="from" class="form-control" value="{{ old('period_start', $from) }}"></div>
                <div class="col-md-3"><label class="form-label">Until</label><input type="date" name="period_end" id="to" class="form-control" value="{{ old('period_end', $to) }}"></div>
                <div class="col-md-6 d-flex align-items-end gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-light mb-0 preset" data-days="7">Last 7 days</button>
                    <button type="button" class="btn btn-sm btn-light mb-0 preset" data-days="14">Last 14 days</button>
                    <button type="button" class="btn btn-sm btn-light mb-0 preset" data-days="30">Last 30 days</button>
                    <button type="button" class="btn btn-sm btn-light mb-0 preset" data-weekend="1">Last weekend (Sat–Sun)</button>
                </div>
            </div>
            <div class="border rounded p-2 mb-4" id="dailyList">
                <div class="text-muted small p-2">Loading daily exams…</div>
            </div>

            {{-- 3. how many questions --}}
            <h6>3. How many questions?</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="form-check"><input class="form-check-input" type="radio" name="mode" id="m1" value="all" {{ old('mode', 'all') === 'all' ? 'checked' : '' }}><label class="form-check-label" for="m1">All questions of the ticked daily exams</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="mode" id="m2" value="per_exam" {{ old('mode') === 'per_exam' ? 'checked' : '' }}><label class="form-check-label" for="m2">A random number from <em>each</em> ticked daily exam</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="mode" id="m3" value="total" {{ old('mode') === 'total' ? 'checked' : '' }}><label class="form-check-label" for="m3">A random number in total, across all ticked daily exams</label></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Number of questions</label>
                    <input type="number" min="1" max="1000" name="n" id="n" class="form-control" value="{{ old('n', 5) }}">
                    <div class="form-text" id="estimate"></div>
                </div>
            </div>

            <button class="btn btn-success mb-0"><i class="bi bi-magic me-1"></i>{{ $adding ? 'Add questions' : 'Create exam with these questions' }}</button>
            <span class="text-muted small ms-2">You can review, edit or remove any question afterwards.</span>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var previewUrl = "{{ $base }}/build/preview";
    var preselected = @json(array_map('intval', (array) old('source_ids', [])));
    var $list = $('#dailyList');

    function load() {
        var from = $('#from').val(), to = $('#to').val();
        if (!from || !to) { return; }
        $list.html('<div class="text-muted small p-2">Loading daily exams…</div>');
        $.getJSON(previewUrl, { from: from, to: to }).done(function (res) {
            if (!res.data.length) {
                $list.html('<div class="text-muted small p-2">No daily exams in this period.</div>');
                estimate();
                return;
            }
            var html = '<div class="form-check mb-1"><input class="form-check-input" type="checkbox" id="all"><label class="form-check-label fw-bold" for="all">Select all</label></div>';
            res.data.forEach(function (e) {
                var checked = preselected.length ? preselected.indexOf(e.id) > -1 : e.questions > 0;
                html += '<div class="form-check"><input class="form-check-input src" type="checkbox" name="source_ids[]" value="' + e.id + '" data-q="' + e.questions + '" id="s' + e.id + '"' + (checked ? ' checked' : '') + (e.questions ? '' : ' disabled') + '>' +
                    '<label class="form-check-label" for="s' + e.id + '">' + e.title + ' <span class="text-muted">· ' + e.date + ' · ' + e.questions + ' questions' + (e.published ? '' : ' · draft') + '</span></label></div>';
            });
            $list.html(html);
            $('#all').on('change', function () { $('.src:not(:disabled)').prop('checked', this.checked); estimate(); });
            $('.src').on('change', estimate);
            estimate();
        }).fail(function () { $list.html('<div class="text-danger small p-2">Could not load the daily exams.</div>'); });
    }

    function estimate() {
        var total = 0, count = 0;
        $('.src:checked').each(function () { total += parseInt($(this).data('q')) || 0; count++; });
        var mode = $('input[name=mode]:checked').val(), n = parseInt($('#n').val()) || 0, out = total;
        if (mode === 'per_exam') { $('.src:checked').each(function () { out += 0; }); out = 0; $('.src:checked').each(function () { out += Math.min(n, parseInt($(this).data('q')) || 0); }); }
        if (mode === 'total') { out = Math.min(n, total); }
        $('#estimate').text(count + ' daily exam(s) ticked · about ' + out + ' question(s) will be added');
        $('#n').prop('disabled', mode === 'all');
    }

    function setRange(from, to) { $('#from').val(from); $('#to').val(to); load(); }
    function iso(d) { return d.toISOString().slice(0, 10); }
    $('.preset').on('click', function () {
        var t = new Date(); t.setHours(12);
        if ($(this).data('weekend')) {
            // most recent Saturday–Sunday that is already over (or today's, if it is Sunday)
            var sun = new Date(t); while (sun.getDay() !== 0) { sun.setDate(sun.getDate() - 1); }
            var sat = new Date(sun); sat.setDate(sat.getDate() - 1);
            setRange(iso(sat), iso(sun));
        } else {
            var f = new Date(t); f.setDate(f.getDate() - (parseInt($(this).data('days')) - 1));
            setRange(iso(f), iso(t));
        }
    });
    $('#from, #to').on('change', load);
    $('input[name=mode], #n').on('change input', estimate);
    load();
})();
</script>
@endsection
