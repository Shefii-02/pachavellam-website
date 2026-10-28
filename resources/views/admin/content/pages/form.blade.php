@extends('admin.main-layout')
@section('main')
@php $editing = (bool) $page; $isCustom = $kind === 'custom'; $v = fn ($f, $d = '') => old($f, $page->{$f} ?? $d); @endphp
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'New' }} {{ $isCustom ? 'custom' : 'prebuilt' }} page</h3>
        <a href="{{ url('admin/pages') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card bg-transparent border rounded-3"><div class="card-body">
        <form method="POST" action="{{ $editing ? url('admin/pages/' . $page->id) : url('admin/pages') }}">
            @csrf @if ($editing) @method('PUT') @endif
            <input type="hidden" name="kind" value="{{ $kind }}">
            <div class="row g-3">
                <div class="col-md-7"><label class="form-label">Title <span class="text-danger">*</span></label><input class="form-control" name="title" maxlength="150" required value="{{ $v('title') }}">@error('title')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-5"><label class="form-label">Key / slug</label><input class="form-control" name="slug" maxlength="80" value="{{ $v('slug') }}" placeholder="made from the title">@error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
                    <div class="form-text">{{ $isCustom ? 'The page opens at /p/<slug>.' : 'Short name used in the page pickers.' }}</div></div>
                @if ($isCustom)
                    <div class="col-12"><label class="form-label">Content <span class="text-danger">*</span></label><textarea class="form-control" name="body" rows="14" required>{{ $v('body') }}</textarea>@error('body')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="form-text">HTML is allowed (headings, lists, links, images, tables, YouTube embeds). Scripts are removed.</div></div>
                    <div class="col-md-6"><label class="form-label">SEO title</label><input class="form-control" name="meta_title" maxlength="150" value="{{ $v('meta_title') }}"></div>
                    <div class="col-md-6"><label class="form-label">SEO description</label><input class="form-control" name="meta_description" maxlength="300" value="{{ $v('meta_description') }}"></div>
                @else
                    <div class="col-md-8"><label class="form-label">Website address <span class="text-danger">*</span></label><input class="form-control" name="path" maxlength="200" required value="{{ $v('path') }}" placeholder="/free-classes">@error('path')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="form-text">An address of this website that already exists. The mobile app opens it in a web view.</div></div>
                @endif
                <div class="col-md-3"><label class="form-label">Order</label><input type="number" min="0" class="form-control" name="position" value="{{ $v('position', 0) }}"></div>
                <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" value="1" id="pgst" {{ old('status', $page->status ?? true) ? 'checked' : '' }}><label class="form-check-label" for="pgst">Shown</label></div></div>
            </div>
            <div class="mt-3"><button class="btn btn-success mb-0">Save</button></div>
        </form>
    </div></div>
</div>
@endsection
