@extends('admin.exam.course.workspace')
@section('course_body')
@php
    $icons = \App\Models\ExamCourseNode::KIND_ICONS;
    $q = $parent ? '&parent=' . $parent->id : '';
    $courseUrl = $courseUrl;
@endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <a href="{{ $courseUrl }}" class="small"><i class="bi bi-arrow-left"></i> {{ $course->title }}</a>
                <h3 class="mb-1">@include('admin.partials.feature-icon', ['feature' => $feature, 'size' => 32]){{ $feature->name }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ $base }}">Top</a></li>
                        @foreach ($breadcrumb as $crumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                @if ($loop->last) {{ $crumb->title }}
                                @else <a href="{{ $base }}?parent={{ $crumb->id }}">{{ $crumb->title }}</a> @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
            @if ($canEdit)
                <div class="d-flex gap-2 flex-wrap">
                    @if ($parent)
                        <a href="{{ $parent->parent_id ? $base . '?parent=' . $parent->parent_id : $base }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Up</a>
                    @endif
                    @if (in_array('folder', $allowed))
                        <a href="{{ $base }}/create?type=folder{{ $q }}" class="btn btn-sm btn-outline-primary mb-0"><i class="bi bi-folder-plus me-1"></i>Folder</a>
                    @endif
                    @if (in_array('file', $allowed))
                        <a href="{{ $base }}/create?type=file{{ $q }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-file-earmark-plus me-1"></i>File</a>
                    @endif
                    @if (in_array('article', $allowed))
                        <a href="{{ $base }}/create?type=article{{ $q }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-newspaper me-1"></i>Article</a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if ($canStarter && $canEdit)
            <div class="alert alert-info d-flex justify-content-between align-items-center py-2">
                <span>Start with the usual folders: Study Materials, Syllabus, Exam Oriented Special Notes, Quick Learn Notes.</span>
                <form action="{{ $base }}/starter" method="POST">@csrf<button class="btn btn-sm btn-info mb-0 text-white">Create them</button></form>
            </div>
        @endif

        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle mb-0 table-hover">
                <thead>
                    <tr><th class="border-0">Name</th><th class="border-0">Details</th><th class="border-0">Position</th><th class="border-0">Status</th>@if ($canEdit)<th class="border-0 text-end">Action</th>@endif</tr>
                </thead>
                <tbody>
                    @forelse ($nodes as $n)
                        <tr>
                            <td>
                                @if ($n->node_type === 'folder')
                                    <i class="bi bi-folder-fill text-warning me-2 fs-5"></i>
                                    <a href="{{ $base }}?parent={{ $n->id }}"><strong>{{ $n->title }}</strong></a>
                                @elseif ($n->node_type === 'article')
                                    <i class="bi bi-newspaper text-primary me-2 fs-5"></i>{{ $n->title }}
                                    @if ($n->audio_path)<span class="badge bg-success ms-1"><i class="bi bi-headphones"></i> voice</span>@endif
                                @else
                                    <i class="bi {{ $icons[$n->file_kind] ?? $icons['other'] }} me-2 fs-5"></i>{{ $n->title }}
                                @endif
                                @if ($n->is_free_preview)<span class="badge bg-light text-dark border ms-1">free preview</span>@endif
                                @if ($n->description)<div class="small text-muted ms-4">{{ \Illuminate\Support\Str::limit($n->description, 90) }}</div>@endif
                            </td>
                            <td class="small">
                                @if ($n->node_type === 'folder')
                                    {{ $n->children_count }} item(s)
                                @elseif ($n->node_type === 'article')
                                    {{ \Illuminate\Support\Str::of(strip_tags((string) $n->body))->wordCount() }} words
                                    @if ($n->audio_path) · <a href="{{ asset('storage/' . $n->audio_path) }}" target="_blank">listen</a>@endif
                                @else
                                    @if ($n->file_path)
                                        <a href="{{ asset('storage/' . $n->file_path) }}" target="_blank">{{ \Illuminate\Support\Str::limit($n->file_name ?: 'open file', 30) }}</a>
                                        <span class="text-muted">· {{ $n->file_size ? number_format($n->file_size / 1048576, 1) . ' MB' : '' }}</span>
                                    @endif
                                    @if ($n->external_url) <a href="{{ $n->external_url }}" target="_blank" rel="noopener">{{ $n->file_path ? '+ link' : 'open link' }}</a>@endif
                                @endif
                                @if (($linkCounts[$n->id] ?? 0) > 0)<div class="text-muted"><i class="bi bi-link-45deg"></i> {{ $linkCounts[$n->id] }} linked note(s)</div>@endif
                            </td>
                            <td>{{ $n->position }}</td>
                            <td>@if ($n->status)<span class="badge rounded-pill bg-orange text-white">Show</span>@else<span class="badge rounded-pill bg-secondary text-white">Hide</span>@endif</td>
                            @if ($canEdit)
                                <td class="text-end text-nowrap">
                                    @if ($n->node_type === 'folder')
                                        <a href="{{ $base }}?parent={{ $n->id }}" class="btn btn-sm btn-circle btn-primary" title="Open"><i class="bi bi-folder2-open"></i></a>
                                    @endif
                                    <a href="{{ $base }}/{{ $n->id }}/edit" class="btn btn-sm btn-circle btn-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ $base }}/{{ $n->id }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete {{ $n->node_type === 'folder' ? 'this folder and everything inside it' : 'this item' }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ $parent ? 'This folder is empty.' : 'Nothing here yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
