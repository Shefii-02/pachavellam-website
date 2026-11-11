@extends('admin.general.layout')
@section('general')
@php $editing = (bool) $exam; @endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom"><h3 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} Exam Type</h3></div>
    <div class="card-body">
        <form action="{{ $editing ? admin_general('exam-types/' . $exam->id) : admin_general('exam-types') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Exam name <span class="text-danger">*</span></label>
                    <input class="form-control" required name="name" value="{{ old('name', $exam->name ?? '') }}" placeholder="e.g. Kerala Police Constable">
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Short name</label>
                    <input class="form-control" name="short_name" maxlength="30" value="{{ old('short_name', $exam->short_name ?? '') }}" placeholder="e.g. KP">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Badge colour</label>
                    <input class="form-control form-control-color w-100" type="color" name="color" value="{{ old('color', $exam->color ?? '#0d6efd') }}">
                </div>
                @unless ($editing)
                    <div class="col-md-6">
                        <label class="form-label">Slug (optional)</label>
                        <input class="form-control" name="slug" value="{{ old('slug') }}" placeholder="auto from the name, e.g. kerala-police">
                        <div class="form-text">Lower case letters, numbers and dashes. Cannot be changed later.</div>
                        @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                @else
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input class="form-control" value="{{ $exam->slug }}" disabled>
                    </div>
                @endunless
                <div class="col-md-6">
                    <label class="form-label">Performance data source</label>
                    <select class="form-control" name="performance_source">
                        <option value="">— None (no graded exams yet) —</option>
                        @foreach ($sources as $key => $label)
                            <option value="{{ $key }}" @selected(old('performance_source', $exam->performance_source ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Where this exam's graded attempts are read from for ranking and performance.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3" name="description">{{ old('description', $exam->description ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label mb-0">Features of this exam type</label>
                    <div class="form-text mb-2">Choose what this exam type has in the admin sidebar. Staff see only the ticked ones, and only if they also have the View permission (Roles / Staff Access). {{ $editing ? '' : 'You can change this later.' }}</div>
                    <div class="row g-3">
                        @foreach ($catalog as $group => $items)
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-semibold">{{ $group }}</div>
                                        <label class="small mb-0"><input type="checkbox" class="form-check-input feature-group"> all</label>
                                    </div>
                                    @foreach ($items as $key => $item)
                                        <div class="form-check">
                                            <input class="form-check-input feature-check" type="checkbox" name="features[]" value="{{ $key }}" id="f{{ md5($key) }}" @checked($item['locked'] || in_array($key, old('features', $chosen))) @disabled($item['locked'])>
                                            @if ($item['locked'])<input type="hidden" name="features[]" value="{{ $key }}">@endif
                                            <label class="form-check-label" for="f{{ md5($key) }}">{{ $item['label'] }}@if ($item['permission']) <span class="text-muted small">· {{ $item['permission'] }}</span>@endif</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image <span class="text-muted">(home page tab, "What are you preparing for?")</span></label>
                    <input class="form-control" type="file" name="image" accept="image/*">
                    @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                    @if ($editing && $exam->image)
                        <div class="d-flex align-items-center gap-3 mt-2"><img src="{{ Storage::url($exam->image) }}" class="rounded border" style="height:64px" alt="">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="remove_image" value="1" id="rmImg"><label class="form-check-label" for="rmImg">Remove image</label></div></div>
                    @endif
                    <div class="form-text">A square picture (PNG / JPG / WebP) with a plain or transparent background looks best.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Position</label>
                    <input class="form-control" type="number" min="0" name="position" value="{{ old('position', $exam->position ?? 0) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status" {{ old('status', $editing ? $exam->status : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Show to students</label>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
                <a href="{{ admin_general('exam-types') }}" class="btn btn-light mb-0">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('.feature-group').forEach(function (g) {
    var boxes = g.closest('.border').querySelectorAll('.feature-check:not(:disabled)');
    g.checked = Array.prototype.every.call(boxes, function (b) { return b.checked; });
    g.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = g.checked; }); });
});
</script>
@endsection
