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
            <div class="row g-3 mt-1">
                @php
                    $pg = isset($category) ? \App\Support\CategorySections::for($category) : \App\Support\CategorySections::DEFAULTS;
                    $own = isset($category) && $category->settings && isset($category->settings['page_sections']);
                    $orderOf = array_flip($pg['sections']);
                @endphp
                <div class="col-12"><hr class="my-2"><h6 class="mb-1"><i class="bi bi-layout-text-window me-1"></i>Public page: sections</h6>
                    <div class="small text-muted mb-2">What the website's category page (/c/{{ isset($category) ? $category->slug : 'slug' }}) shows. Empty sections are hidden automatically. Sub categories use the parent's setting unless they have their own.</div>
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="inherit_page" value="1" id="inhPg" {{ !$own ? 'checked' : '' }}><label class="form-check-label" for="inhPg">Use the parent's / default setting</label></div>
                    <div id="pgBox" style="{{ !$own ? 'opacity:.5' : '' }}">
                        <div class="row g-2">
                            @foreach (\App\Support\CategorySections::ALL as $k => $label)
                                <div class="col-md-4"><div class="border rounded p-2 d-flex align-items-center gap-2">
                                    <input class="form-check-input mt-0" type="checkbox" name="ps[{{ $k }}][on]" value="1" id="ps{{ $k }}" {{ isset($orderOf[$k]) ? 'checked' : '' }}>
                                    <label class="form-check-label flex-grow-1" for="ps{{ $k }}">{{ $label }}</label>
                                    <input type="number" min="0" class="form-control form-control-sm" style="width:64px" name="ps[{{ $k }}][order]" value="{{ $orderOf[$k] ?? array_search($k, array_keys(\App\Support\CategorySections::ALL)) + 10 }}" title="Order (small number first)">
                                </div></div>
                            @endforeach
                        </div>
                        <div class="row g-2 mt-1 align-items-center">
                            <div class="col-md-3"><select name="page_layout" class="form-select form-select-sm"><option value="tabs" {{ $pg['layout'] === 'tabs' ? 'selected' : '' }}>Show as tabs</option><option value="stacked" {{ $pg['layout'] === 'stacked' ? 'selected' : '' }}>Show one below another</option></select></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="page_new_tab" value="1" id="pgNt" {{ $pg['new_tab'] ? 'checked' : '' }}><label class="form-check-label" for="pgNt">Items open in a new tab</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="page_copy_link" value="1" id="pgCl" {{ $pg['copy_link'] ? 'checked' : '' }}><label class="form-check-label" for="pgCl">Show a "copy link" button</label></div></div>
                        </div>
                    </div>
                    <script>document.addEventListener('DOMContentLoaded', function () { var c = document.getElementById('inhPg'), b = document.getElementById('pgBox'); c.addEventListener('change', function () { b.style.opacity = c.checked ? .5 : 1; }); });</script>
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
