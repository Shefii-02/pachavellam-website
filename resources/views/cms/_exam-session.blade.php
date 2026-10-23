{{-- Season and free access of an old KPSC exam. $kind = daily | omr | ca ; $examRow = the exam being edited, or null. --}}
@php
    $kpscId = \App\Models\CompetitiveExam::where('slug', 'kpsc')->value('id');
    $sessions = $kpscId ? \App\Models\ExamSeason::where('exam_id', $kpscId)->where('kind', $kind)->where('status', '!=', 'archived')->orderByDesc('id')->get() : collect();
    $curSession = old('series_season_id', optional($examRow ?? null)->series_season_id ?? request('session'));
    $acc = app(\App\Services\SeasonAccess::class);
@endphp
<div class="col-md-6 mt-2">
    <label class="form-label">Season</label>
    <select name="series_season_id" class="form-control">
        <option value="">No season</option>
        @foreach ($sessions as $se)
            <option value="{{ $se->id }}" {{ (string) $curSession === (string) $se->id ? 'selected' : '' }}>{{ $se->title }} - {{ $acc->isPaid($se) ? 'Paid ₹' . rtrim(rtrim(number_format($acc->effectivePrice($se), 2), '0'), '.') : 'Free' }}@if ($se->basis_name) ({{ $se->basis_name }})@endif</option>
        @endforeach
    </select>
    <div class="form-text">Seasons group the exams and decide free or paid. <a href="{{ url('admin/exam/kpsc/series/' . $kind) }}" target="_blank">Create / manage seasons</a></div>
</div>
<div class="col-md-6 mt-2 d-flex align-items-center">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" name="free_access" id="fa{{ $kind }}" value="1" {{ old('free_access', optional($examRow ?? null)->free_access) ? 'checked' : '' }}>
        <label class="form-check-label" for="fa{{ $kind }}">Free access - open for everyone even when its season is paid</label>
    </div>
</div>
