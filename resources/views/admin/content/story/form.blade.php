@extends('admin.main-layout')
@section('main')
@php $editing = (bool) $story; @endphp
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'New' }} Story</h3>
        <a href="{{ url('admin/stories') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card bg-transparent border rounded-3">
        <div class="card-body">
            <form action="{{ $editing ? url('admin/stories/' . $story->id) : url('admin/stories') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Image @unless ($editing)<span class="text-danger">*</span>@endunless</label>
                        <input class="form-control" type="file" name="image" accept="image/*" {{ $editing ? '' : 'required' }}>
                        @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                        @if ($editing && $story->image)
                            <img src="{{ asset('storage/files/' . $story->image) }}" alt="" class="rounded mt-2" style="max-height:120px">
                            <div class="form-text">Choose a file only if you want to replace it.</div>
                        @endif
                    </div>
                    <div class="col-12">@include('admin.content._landing-picker', ['type' => $story->target_type ?? 'link', 'value' => $story->target_value ?? ''])</div>
                    <div class="col-md-6" data-landing-link>
                        <label class="form-label">Custom link</label>
                        <input class="form-control" id="landingLink" name="redirection" maxlength="500" value="{{ old('redirection', $story->redirection ?? '') }}" placeholder="https://... or # for none">
                        @error('redirection')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Position</label>
                        <input class="form-control" type="number" min="0" name="position" value="{{ old('position', $story->position ?? '') }}" placeholder="auto">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="status" id="st" value="1" {{ old('status', $editing ? $story->status : 1) ? 'checked' : '' }}><label class="form-check-label" for="st">Shown</label></div>
                    </div>
                    <div class="col-12">
                        @include('admin.content._exam-picker', ['exams' => $exams, 'chosen' => $chosen, 'what' => 'story'])
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
                    <a href="{{ url('admin/stories') }}" class="btn btn-light mb-0">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
