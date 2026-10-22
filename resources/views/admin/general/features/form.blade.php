@extends('admin.general.layout')
@section('general')
@php $editing = (bool) $feature; @endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} Feature</h3>
        <a href="{{ admin_general('features') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body">
        <form action="{{ $editing ? admin_general('features/' . $feature->id) : admin_general('features') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input class="form-control" required name="name" value="{{ old('name', $feature->name ?? '') }}" placeholder="e.g. Live doubt sessions">
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Picture</label>
                    <input class="form-control" type="file" name="image" accept="image/*">
                    <div class="form-text">JPG, PNG, WEBP or SVG, max 1 MB. A square picture works best.</div>
                    @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                    @if ($editing && $feature->image)
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img src="{{ asset('storage/' . $feature->image) }}" alt="" style="height:44px;width:44px;object-fit:contain;border-radius:6px" class="border">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="remove_image" value="1" id="ri"><label class="form-check-label" for="ri">Remove</label></div>
                        </div>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label">Position</label>
                    <input class="form-control" type="number" min="0" name="position" value="{{ old('position', $feature->position ?? 0) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-control" name="content_kind" id="kind">
                        @foreach (\App\Models\ExamFeature::KINDS as $k => $label)
                            <option value="{{ $k }}" @selected(old('content_kind', $feature->content_kind ?? 'flag') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        <strong>Content</strong>: the course gets its own folders, files and articles.
                        <strong>Exams</strong>: exams common to all categories.
                        <strong>Section access</strong>: the course simply includes that section.
                    </div>
                    @error('content_kind')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                @unless ($editing)
                    <div class="col-md-6">
                        <label class="form-label">Slug (optional)</label>
                        <input class="form-control" name="slug" value="{{ old('slug') }}" placeholder="auto from the name">
                        <div class="form-text">Cannot be changed later.</div>
                        @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                @endunless
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3" name="description">{{ old('description', $feature->description ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Available for</label>
                    <div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="exam_scope" id="es1" value="all" {{ old('exam_scope', $feature->exam_scope ?? 'all') === 'all' ? 'checked' : '' }}><label class="form-check-label" for="es1">All exam types</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="exam_scope" id="es2" value="selected" {{ old('exam_scope', $feature->exam_scope ?? 'all') === 'selected' ? 'checked' : '' }}><label class="form-check-label" for="es2">Only these exam types</label></div>
                    </div>
                    <div id="examPick" class="mt-2" style="display:none">
                        @foreach ($exams as $e)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="fe{{ $e->id }}" {{ in_array($e->id, (array) old('exam_ids', $selectedExams)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="fe{{ $e->id }}"><span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span></label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">The feature is only offered on the course form of these exam types.</div>
                    @error('exam_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="st" {{ old('status', $editing ? $feature->status : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="st">Show on the course form</label>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
                <a href="{{ admin_general('features') }}" class="btn btn-light mb-0">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        function scope() { $('#examPick').toggle($('input[name=exam_scope]:checked').val() === 'selected'); }
        $('input[name=exam_scope]').on('change', scope); scope();
    })();
</script>
@endsection
