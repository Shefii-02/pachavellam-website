@extends($rsLayout ?? 'admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0">Roaring Study</h3>
            <div class="small text-muted">Timed study packs of notes, PDFs, voice notes, links and recorded classes. Each pack opens at its own time, for the exam types you tick, free for everyone or only for chosen courses.</div>
        </div>
        <a href="{{ ($rsBase . '/create') }}{{ request('exam') ? '?exam=' . request('exam') : '' }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New Roaring Study</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title..."></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>
            @foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="access" class="form-select form-select-sm"><option value="">Free + course</option><option value="global" @selected(request('access') === 'global')>Free for everyone</option><option value="course" @selected(request('access') === 'course')>Course based</option></select></div>
        <div class="col-6 col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>
            @foreach (\App\Models\RoaringStudy::STATUSES as $k => [$l])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ ($rsBase) }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <div class="card bg-transparent border rounded-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead><tr><th style="width:34px"></th><th>Roaring Study</th><th>Exam types</th><th>Opens</th><th>Access</th><th class="text-end">Sections</th><th class="text-end">Content</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($list as $s)
                    @php [$stLabel, $stColor] = \App\Models\RoaringStudy::STATUSES[$s->status]; @endphp
                    <tr>
                        <td><button type="button" class="btn btn-sm btn-light mb-0 rs-toggle" data-target="rs{{ $s->id }}" title="Show sections and content"><i class="bi bi-chevron-right"></i></button></td>
                        <td><a href="{{ ($rsBase . '/' . $s->id) }}" class="fw-semibold">{{ $s->title }}</a></td>
                        <td>@foreach ($s->exams as $e)<span class="badge bg-light text-dark border">{{ $e->short_name ?: $e->name }}</span> @endforeach</td>
                        <td class="small text-nowrap">{{ $s->publish_at?->format('d M Y, h:i A') ?? 'when published' }}@if ($s->end_at)<div class="text-muted">until {{ $s->end_at->format('d M Y, h:i A') }}</div>@endif</td>
                        <td>@if ($s->isGlobal())<span class="badge bg-success">Free · everyone</span>@else<span class="badge bg-info text-white">{{ $s->courses_count }} course(s)</span>@endif</td>
                        <td class="text-end">{{ $s->sections_count }}</td>
                        <td class="text-end">{{ $itemCounts[$s->id] ?? 0 }}</td>
                        <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ ($rsBase . '/' . $s->id) }}" class="btn btn-sm btn-light mb-0">Content</a>
                            <a href="{{ ($rsBase . '/' . $s->id . '/edit') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                    <tr id="rs{{ $s->id }}" class="d-none">
                        <td></td>
                        <td colspan="8" class="bg-body-tertiary">
                            @forelse ($s->sections as $sec)
                                <div class="mb-3">
                                    <div class="fw-semibold"><i class="bi bi-folder2-open me-1"></i>{{ $sec->title }} <span class="badge bg-light text-dark border">{{ $sec->items->count() }}</span>@if ($sec->release_at)<span class="small text-muted ms-2">opens {{ \Illuminate\Support\Carbon::parse($sec->release_at)->format('d M Y, h:i A') }}</span>@endif</div>
                                    <ul class="list-group list-group-flush ms-3">
                                        @forelse ($sec->items as $it)
                                            <li class="list-group-item bg-transparent py-1 d-flex gap-2 align-items-baseline">
                                                <span class="badge bg-light text-dark border" style="min-width:96px">{{ \App\Models\RoaringStudy::TYPES[$it->type] ?? $it->type }}</span>
                                                <span class="flex-grow-1"><b>{{ $it->title ?: ($it->type === 'class' ? ($it->libraryClass->title ?? 'Class') : \App\Models\RoaringStudy::TYPES[$it->type]) }}</b>
                                                    <span class="small text-muted d-block">
                                                        @switch($it->type)
                                                            @case('text'){{ \Illuminate\Support\Str::limit(strip_tags((string) $it->content), 140) }}@break
                                                            @case('link'){{ \Illuminate\Support\Str::limit((string) $it->content, 100) }}@break
                                                            @case('class'){{ $it->libraryClass->title ?? '' }}@break
                                                            @default{{ basename((string) $it->file_path) }}
                                                        @endswitch
                                                    </span></span>
                                                @if (!$it->status)<span class="badge bg-secondary">Hidden</span>@endif
                                            </li>
                                        @empty
                                            <li class="list-group-item bg-transparent py-1 text-muted small">No content in this section yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            @empty
                                <div class="text-muted small">No sections yet.</div>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No roaring studies yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $list->links() }}</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.rs-toggle').forEach(function (b) {
            b.addEventListener('click', function () {
                var row = document.getElementById(b.dataset.target); row.classList.toggle('d-none');
                b.querySelector('i').className = 'bi ' + (row.classList.contains('d-none') ? 'bi-chevron-right' : 'bi-chevron-down');
            });
        });
    });
</script>
@endsection
@section('content2')@yield('main')@endsection
