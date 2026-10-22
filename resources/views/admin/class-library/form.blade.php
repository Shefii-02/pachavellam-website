@extends($layout)
@section('content2')
@php
    $editing = (bool) $class;
    $v = fn ($k, $d = null) => old($k, $editing ? ($class->{$k} ?? $d) : $d);
    $b = fn ($k, $d) => (bool) old($k, $editing ? $class->{$k} : $d);
    $duration = old('duration', $editing && $class->duration_seconds ? \App\Services\ClassSource::formatDuration($class->duration_seconds) : '');
    $tags = old('tags', $editing ? implode(', ', $class->tags ?? []) : '');
    $details = old('details', $editing ? $class->details : '');
    $viaStorage = old('storage_path', $editing ? $class->storage_path : '') !== '';
@endphp
@include('admin.question-library._alerts')

<form action="{{ $editing ? $base . '/' . $class->id : $base }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom"><h4 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} class</h4></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Class title</label><input type="text" name="title" class="form-control" required maxlength="250" value="{{ $v('title') }}"></div>
                <div class="col-md-4">
                    <label class="form-label">Library folder</label>
                    <select name="folder_id" class="form-select" required><option value="">Choose folder...</option>
                        @foreach ($tree as $f)<option value="{{ $f['id'] }}" @selected(old('folder_id', $folderId) == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach</select>
                </div>

                <div class="col-12">
                    <div class="border rounded p-3">
                        <div class="fw-semibold mb-2">Where is the class video?</div>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Link <span class="text-muted">(YouTube unlisted, CDN or any video link)</span></label>
                                <input type="text" name="url" id="url" class="form-control" placeholder="https://youtu.be/…  or  https://cdn.example.com/class1.mp4" value="{{ $v('url') }}">
                                <div class="form-text" id="urlKind"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">…or file on cloud storage</label>
                                <input type="text" name="storage_path" id="storagePath" class="form-control" placeholder="{{ $storage->configured() ? 'polity/class-01.mp4' : 'cloud storage is not set up' }}" value="{{ $v('storage_path') }}" @disabled(!$storage->configured())>
                                <div class="form-text">
                                    @if ($storage->configured())
                                        Disk <code>{{ $storage->disk() }}</code>. <a href="{{ $base }}/storage">Browse the storage</a> to pick files. If a file is given here, it is used instead of the link.
                                    @else
                                        Set <code>CLASS_STORAGE_DISK</code> in the .env file (e.g. <code>s3</code>) to use files from a bucket.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3"><label class="form-label">Class length</label><input type="text" name="duration" class="form-control" placeholder="1:25:30" value="{{ $duration }}"><div class="form-text">hours:minutes:seconds, or minutes</div></div>
                <div class="col-md-3"><label class="form-label">Teacher</label><input type="text" name="instructor" class="form-control" maxlength="150" value="{{ $v('instructor') }}"></div>
                <div class="col-md-3"><label class="form-label">Language</label>
                    <select name="language" class="form-select"><option value="">—</option>
                        @foreach ($languages as $code => $l)<option value="{{ $code }}" @selected($v('language') === $code)>{{ $l['name'] }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Class date</label><input type="date" name="class_date" class="form-control" value="{{ old('class_date', $editing && $class->class_date ? $class->class_date->format('Y-m-d') : '') }}"></div>

                <div class="col-12"><label class="form-label">Short description</label><textarea name="description" rows="2" class="form-control" maxlength="1000">{{ $v('description') }}</textarea></div>
                <div class="col-12"><label class="form-label">Details <span class="text-muted">(topics covered, notes)</span></label><textarea name="details" id="details" rows="5" class="form-control">{{ $details }}</textarea></div>

                <div class="col-md-6"><label class="form-label">Tags <span class="text-muted">(comma separated)</span></label><input type="text" name="tags" class="form-control" value="{{ $tags }}"></div>
                <div class="col-md-6"><label class="form-label">Notes link <span class="text-muted">(PDF / drive link, optional)</span></label><input type="text" name="notes_url" class="form-control" value="{{ $v('notes_url') }}"></div>
                <div class="col-md-6">
                    <label class="form-label">Thumbnail <span class="text-muted">(YouTube links get theirs automatically)</span></label>
                    <input type="file" name="thumbnail" accept="image/*" class="form-control">
                    @if ($editing && $class->thumbnail)<div class="mt-1"><img src="{{ $class->thumbnailUrl() }}" alt="" style="height:50px" class="border rounded"> <label class="small ms-2"><input type="checkbox" name="remove_thumbnail" value="1"> remove</label></div>@endif
                </div>
                <div class="col-md-3"><label class="form-label">Status</label>
                    <select name="status" class="form-select">@foreach (config('class_library.statuses') as $k => [$l])<option value="{{ $k }}" @selected($v('status', 'published') === $k)>{{ $l }}</option>@endforeach</select>
                    <div class="form-text">Only published classes are shown to students.</div></div>
            </div>
        </div>
    </div>

    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom"><strong>Watch rules</strong> <span class="small text-muted">applied by the app / website player, per student</span></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Max watch time per user (min)</label><input type="number" min="1" name="max_watch_minutes" class="form-control" value="{{ $v('max_watch_minutes') }}" placeholder="no limit"><div class="form-text">Total minutes one student may watch.</div></div>
                <div class="col-md-3"><label class="form-label">Max views per user</label><input type="number" min="1" name="max_views" class="form-control" value="{{ $v('max_views') }}" placeholder="no limit"><div class="form-text">Times the class can be started.</div></div>
                <div class="col-md-3"><label class="form-label">Access days after first view</label><input type="number" min="1" name="access_days" class="form-control" value="{{ $v('access_days') }}" placeholder="no limit"></div>
                <div class="col-md-3"><label class="form-label">Completed at (% watched)</label><input type="number" min="1" max="100" name="complete_percent" class="form-control" value="{{ $v('complete_percent', 90) }}"></div>
                @foreach ([['allow_speed', 'Allow speed change', true], ['allow_seek', 'Allow skipping forward', true], ['resume_playback', 'Resume where left off', true], ['allow_download', 'Allow download', false], ['watermark_user', 'Show student name / number over the video', false]] as [$k, $l, $d])
                    <div class="col-md-4"><div class="form-check"><input type="hidden" name="{{ $k }}" value="0"><input class="form-check-input" type="checkbox" name="{{ $k }}" value="1" id="{{ $k }}" @checked($b($k, $d))><label class="form-check-label" for="{{ $k }}">{{ $l }}</label></div></div>
                @endforeach
            </div>
        </div>
    </div>

    @if ($editing && $stats)
        <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body small">
            <strong>Usage:</strong> {{ $class->view_count }} view(s) · {{ $stats['learners'] }} student(s) watched · {{ round($stats['seconds'] / 60) }} min watched in total · {{ $stats['completed'] }} completed · in {{ $class->course_links_count }} course(s)
        </div></div>
    @endif

    <div class="d-flex gap-2 mb-3">
        <button class="btn btn-primary mb-0">{{ $editing ? 'Save class' : 'Add class' }}</button>
        <a href="{{ $base }}{{ ($folderId ?? null) ? '?folder=' . $folderId : '' }}" class="btn btn-light mb-0">Cancel</a>
    </div>
</form>

@if ($editing)
    <form action="{{ $base }}/{{ $class->id }}" method="POST" onsubmit="return confirm('Delete this class? It is also removed from {{ $class->course_links_count }} course(s).')">@csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger mb-4"><i class="bi bi-trash me-1"></i>Delete class</button></form>
@endif

<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
(function () {
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('details', { height: 160, removePlugins: 'elementspath', toolbar: [['Bold', 'Italic', 'Underline'], ['NumberedList', 'BulletedList'], ['RemoveFormat', 'Source']] });
    }
    var url = document.getElementById('url'), kind = document.getElementById('urlKind');
    function show() {
        var v = url.value.trim(), t = '';
        if (v) {
            if (/(youtube\.com|youtu\.be|youtube-nocookie\.com)/i.test(v)) t = 'YouTube link';
            else if (/\.(mp4|m4v|mov|webm|mkv|m3u8|mpd|mp3|m4a)(\?|$)/i.test(v) || /(cloudfront\.net|amazonaws\.com|b-cdn\.net|r2\.dev|digitaloceanspaces\.com)/i.test(v)) t = 'CDN / video file link';
            else t = 'Other link (opens as it is)';
        }
        kind.textContent = t ? 'Detected: ' + t : '';
    }
    url.addEventListener('input', show); show();
})();
</script>
@endsection
