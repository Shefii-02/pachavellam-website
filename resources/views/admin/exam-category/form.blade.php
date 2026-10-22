@extends($layout)
@section('content2')
@php $editing = (bool) $category; @endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} {{ $moduleLabel }} Category</h3>
    </div>
    <div class="card-body">
        <form action="{{ $editing ? $baseUrl . '/' . $category->id : $baseUrl }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input class="form-control" required type="text" name="name" placeholder="Enter name"
                        value="{{ old('name', $category->name ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Parent Category</label>
                    <select class="form-control" name="parent_id">
                        <option value="">— None (top level) —</option>
                        @foreach ($parentOptions as $id => $label)
                            <option value="{{ $id }}" {{ old('parent_id', $selectedParent) == $id ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Image / Icon</label>
                    <input class="form-control" type="file" name="image" accept="image/*">
                    <div class="form-text">JPG, PNG, WEBP or SVG, max 2 MB.</div>
                    @if ($editing && $category->image)
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="" style="height:48px;width:48px;object-fit:cover;border-radius:6px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label" for="remove_image">Remove</label>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Position</label>
                    <input class="form-control" type="number" min="0" name="position"
                        value="{{ old('position', $category->position ?? 0) }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            {{ old('status', $editing ? $category->status : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Show</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                            {{ old('is_featured', $category->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Title</label>
                    <input class="form-control" type="text" name="meta_title"
                        value="{{ old('meta_title', $category->meta_title ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <input class="form-control" type="text" name="meta_description"
                        value="{{ old('meta_description', $category->meta_description ?? '') }}">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success mb-0"><i class="bi bi-check me-1"></i> Save</button>
                <a href="{{ $baseUrl }}{{ $selectedParent ? '?parent=' . $selectedParent : '' }}"
                    class="btn btn-light mb-0">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
