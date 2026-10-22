@extends('admin.exam.course.workspace')
@section('course_body')
@php
    $editing = (bool) $node;
    $labels = ['folder' => 'Folder', 'file' => 'File', 'article' => 'Article'];
    $val = fn ($key, $default = '') => old($key, $node->{$key} ?? $default);
    $backUrl = $base . ($parentId ? '?parent=' . $parentId : '');
@endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} {{ strtolower($labels[$type]) }}</h3>
            <div class="small text-muted">{{ $course->title }} · {{ $feature->name }}</div>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body">
        <form action="{{ $editing ? $base . '/' . $node->id : $base }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @else <input type="hidden" name="node_type" value="{{ $type }}"> @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">{{ $type === 'article' ? 'Article title' : 'Name' }} <span class="text-danger">*</span></label>
                    <input class="form-control" required name="title" value="{{ $val('title') }}"
                        placeholder="{{ ['folder' => 'e.g. 2025', 'file' => 'e.g. Prelims question paper', 'article' => 'e.g. Kerala Renaissance'][$type] }}">
                    @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Inside folder</label>
                    <select class="form-control" name="parent_id">
                        <option value="">— Top level —</option>
                        @foreach ($folders as $id => $path)
                            <option value="{{ $id }}" @selected(old('parent_id', $parentId) == $id)>{{ $path }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="2" name="description">{{ $val('description') }}</textarea>
                </div>

                @if ($type === 'file')
                    <div class="col-md-6">
                        <label class="form-label">Upload file</label>
                        <input class="form-control" type="file" name="file">
                        <div class="form-text">PDF, Word / Excel / PowerPoint, ZIP, audio (mp3, m4a, wav...), video (mp4...) or image. Up to 100 MB.</div>
                        @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
                        @if ($editing && $node->file_path)
                            <div class="mt-2 d-flex align-items-center gap-3">
                                <a href="{{ asset('storage/' . $node->file_path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $node->file_name ?: 'Current file' }}</a>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="remove_file" value="1" id="rf"><label class="form-check-label" for="rf">Remove</label></div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">…or a link</label>
                        <input class="form-control" type="url" name="external_url" value="{{ $val('external_url') }}" placeholder="https://">
                        <div class="form-text">Google Drive, YouTube, or any web link.</div>
                        @error('external_url')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                @endif

                @if ($type === 'article')
                    <div class="col-12">
                        <label class="form-label">Article text</label>
                        <textarea class="form-control" rows="12" name="body">{{ $val('body') }}</textarea>
                        @error('body')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-headphones me-1"></i>Voice version (audio)</label>
                        <input class="form-control" type="file" name="audio" accept="audio/*">
                        <div class="form-text">mp3, m4a, aac, wav or ogg, up to 100 MB. Students can listen instead of reading.</div>
                        @error('audio')<div class="text-danger small">{{ $message }}</div>@enderror
                        @if ($editing && $node->audio_path)
                            <div class="mt-2">
                                <audio controls preload="none" src="{{ asset('storage/' . $node->audio_path) }}" style="max-width:100%"></audio>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="remove_audio" value="1" id="ra"><label class="form-check-label" for="ra">Remove the voice recording</label></div>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($type !== 'folder')
                    <div class="col-12">
                        <label class="form-label"><i class="bi bi-link-45deg me-1"></i>Linked notes <span class="text-muted small">(e.g. the quick learn notes for this question paper)</span></label>
                        <select class="form-control" name="related_ids[]" multiple size="6">
                            @foreach ($linkable as $id => $path)
                                <option value="{{ $id }}" @selected(in_array($id, (array) old('related_ids', $linkedIds)))>{{ $path }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Pick files or articles from anywhere in this course. Hold Ctrl / Cmd to pick several.</div>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label">Position</label>
                    <input class="form-control" type="number" min="0" name="position" value="{{ $val('position', 0) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="st" {{ old('status', $editing ? $node->status : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="st">Show to students</label>
                    </div>
                </div>
                @if ($type !== 'folder')
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_free_preview" value="1" id="fp" {{ old('is_free_preview', $node->is_free_preview ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fp">Free preview <span class="text-muted small">(visible without joining)</span></label>
                        </div>
                    </div>
                @endif
            </div>

            @if (!$node && $otherCourses->isNotEmpty())
                <div class="row mt-3">@include('admin.exam.partials.also-courses', ['otherCourses' => $otherCourses, 'what' => $type, 'note' => 'A separate copy (with its file) is made at the top level of this section in each ticked course.'])</div>
            @endif

            <div class="mt-4">
                <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
                <a href="{{ $backUrl }}" class="btn btn-light mb-0">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
