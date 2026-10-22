@extends('cms.section')
@section('content2')
@php
    $kinds = \App\Models\CaSeason::KINDS;
    $isDaily = $kind === 'daily';
    $base = kpsc_cms('ca-daily-exam/seasons/' . $season->id);
@endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">Add {{ $kinds[$kind] }} exam</h3>
            <div class="small text-muted">{{ $season->name }}</div>
        </div>
        <a href="{{ $base }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body">
        <form action="{{ $base }}/exams" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="exam_kind" class="form-control" onchange="location='{{ $base }}/exams/create?kind='+this.value">
                        @foreach ($kinds as $k => $label)
                            <option value="{{ $k }}" @selected($kind === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ $isDaily ? 'Day' : 'Number' }} <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="slot_no" required class="form-control" value="{{ old('slot_no', $nextSlot) }}">
                    @error('slot_no')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-7">
                    <label class="form-label">Title (optional)</label>
                    <input name="exam_title" class="form-control" value="{{ old('exam_title') }}" placeholder="{{ $isDaily ? 'Day ' . $nextSlot : $kinds[$kind] . ' ' . $nextSlot }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Starts at <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="started_at" required class="form-control" value="{{ old('started_at', $suggestedStart) }}">
                    @error('started_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ends at (optional)</label>
                    <input type="datetime-local" name="ended_at" class="form-control" value="{{ old('ended_at') }}">
                    @error('ended_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                @unless ($isDaily)
                    <div class="col-md-2">
                        <label class="form-label">Covers from</label>
                        <input type="date" name="period_start" class="form-control" value="{{ old('period_start') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Covers until</label>
                        <input type="date" name="period_end" class="form-control" value="{{ old('period_end') }}">
                        @error('period_end')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                @endunless
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="status" id="st" value="1" {{ old('status') ? 'checked' : '' }}>
                        <label class="form-check-label" for="st">Publish now <span class="text-muted small">(leave off until the questions are added)</span></label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Create &amp; add questions</button>
                @unless ($isDaily)
                    <a href="{{ $base }}/build?kind={{ $kind }}" class="btn btn-outline-success mb-0"><i class="bi bi-magic me-1"></i>Build from daily exams instead</a>
                @endunless
            </div>
        </form>
    </div>
</div>
@endsection
