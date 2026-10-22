@extends('admin.main-layout')
@section('main')
@php
    $editing = (bool) $capsule;
    $type = old('type', $capsule->type ?? 'Image');
@endphp
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'New' }} Capsule</h3>
        <a href="{{ url('admin/capsule') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card bg-transparent border rounded-3">
        <div class="card-body">
            <form action="{{ $editing ? url('admin/capsule/' . $capsule->id) : url('admin/capsule') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        @if ($editing)
                            <input class="form-control" value="{{ $capsule->type === 'Content' ? 'Text' : 'Image' }}" disabled>
                        @else
                            <select class="form-control" name="type" id="ctype">
                                <option value="Image" @selected($type === 'Image')>Image</option>
                                <option value="Content" @selected($type === 'Content')>Text</option>
                            </select>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tag</label>
                        <input class="form-control" name="tag" value="{{ old('tag', $capsule->tag ?? '') }}" maxlength="255" placeholder="optional">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Position</label>
                        <input class="form-control" type="number" min="0" name="position" value="{{ old('position', $capsule->position ?? '') }}" placeholder="auto">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="status" id="st" value="1" {{ old('status', $editing ? $capsule->status : 1) ? 'checked' : '' }}><label class="form-check-label" for="st">Shown</label></div>
                    </div>

                    <div class="col-md-6" id="imageBox">
                        <label class="form-label">Image <span class="text-muted small">(square, 570 × 570 looks best)</span></label>
                        <input class="form-control" type="file" name="image" accept="image/*">
                        @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                        @if ($editing && $capsule->type === 'Image' && $capsule->image)
                            <img src="{{ asset('storage/files/' . $capsule->image) }}" alt="" class="rounded mt-2" style="max-height:120px">
                            <div class="form-text">Choose a file only if you want to replace it.</div>
                        @endif
                    </div>
                    <div class="col-12" id="contentBox">
                        <label class="form-label">Text</label>
                        <textarea class="form-control my-editor" id="capsule-content" name="content" rows="6">{{ old('content', $capsule->content ?? '') }}</textarea>
                        @error('content')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        @include('admin.content._exam-picker', ['exams' => $exams, 'chosen' => $chosen, 'what' => 'capsule'])
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
                    <a href="{{ url('admin/capsule') }}" class="btn btn-light mb-0">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var fixed = @json($editing ? $capsule->type : null);
        function sync() {
            var t = fixed || (document.getElementById('ctype') ? document.getElementById('ctype').value : 'Image');
            document.getElementById('imageBox').style.display = t === 'Image' ? '' : 'none';
            document.getElementById('contentBox').style.display = t === 'Content' ? '' : 'none';
        }
        var sel = document.getElementById('ctype');
        if (sel) { sel.addEventListener('change', sync); }
        sync();
    })();
</script>
@endsection
