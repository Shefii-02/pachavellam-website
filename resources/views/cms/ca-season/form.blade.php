@extends('cms.section')
@section('content2')
@php
    $editing = (bool) $season;
@endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'New' }} Season</h3>
        <a href="{{ $editing ? kpsc_cms('ca-daily-exam/seasons/' . $season->id) : kpsc_cms('ca-daily-exam') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body">
        <form action="{{ $editing ? kpsc_cms('ca-daily-exam/seasons/' . $season->id) : kpsc_cms('ca-daily-exam/seasons') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Season name <span class="text-danger">*</span></label>
                    <input class="form-control" required name="name" value="{{ old('name', $season->name ?? '') }}" placeholder="e.g. Season 2 · Jan–Mar 2027">
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        @foreach (\App\Models\CaSeason::STATUSES as $k => [$label])
                            <option value="{{ $k }}" @selected(old('status', $season->status ?? 'draft') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Draft is hidden from the app.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Schedule</label>
                    <select class="form-control" name="schedule_type">
                        @foreach (\App\Models\CaSeason::SCHEDULES as $k => $label)
                            <option value="{{ $k }}" @selected(old('schedule_type', $season->schedule_type ?? 'daily') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Starts on</label>
                    <input class="form-control" type="date" name="starts_on" value="{{ old('starts_on', optional($season->starts_on ?? null)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ends on</label>
                    <input class="form-control" type="date" name="ends_on" value="{{ old('ends_on', optional($season->ends_on ?? null)->format('Y-m-d')) }}">
                    <div class="form-text">Leave empty if the season keeps running.</div>
                    @error('ends_on')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tags</label>
                    <input class="form-control" name="tags" value="{{ old('tags', implode(', ', $season->tags ?? [])) }}" placeholder="daily, current affairs, kpsc 2027">
                    <div class="form-text">Separate with commas.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3" name="description">{{ old('description', $season->description ?? '') }}</textarea>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Cover image</label>
                    <input class="form-control" type="file" name="cover_image" accept="image/*">
                    @error('cover_image')<div class="text-danger small">{{ $message }}</div>@enderror
                    @if ($editing && $season->cover_image)
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img src="{{ asset('storage/' . $season->cover_image) }}" alt="" style="height:48px;border-radius:6px">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="remove_cover" value="1" id="rc"><label class="form-check-label" for="rc">Remove</label></div>
                        </div>
                    @endif
                </div>
                <div class="col-md-2">
                    <label class="form-label">Position</label>
                    <input class="form-control" type="number" min="0" name="position" value="{{ old('position', $season->position ?? 0) }}">
                </div>
                <div class="col-md-5 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="feat" {{ old('is_featured', $season->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="feat">Show this season in the app by default <span class="text-muted small">(only one season can be the default)</span></label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
            </div>
        </form>
        @if ($editing)
            <form action="{{ kpsc_cms('ca-daily-exam/seasons/' . $season->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this season? This only works when it has no exams.')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash me-1"></i>Delete season</button>
            </form>
        @endif
    </div>
</div>
@endsection
