@extends('admin.general.layout')
@section('general')
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="mb-0">Course Features</h3>
            <div class="small text-muted">The things a course can include, common to every exam type. A feature can be limited to some exam types.</div>
        </div>
        <a href="{{ admin_general('features/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add Feature</a>
    </div>
    <div class="card-body">
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle mb-0 table-hover">
                <thead>
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Feature</th>
                        <th class="border-0">Type</th>
                        <th class="border-0">Available for</th>
                        <th class="border-0">Used by</th>
                        <th class="border-0">Position</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($features as $f)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @include('admin.partials.feature-icon', ['feature' => $f, 'size' => 28])<strong>{{ $f->name }}</strong>
                                @if ($f->is_system)<span class="badge bg-light text-dark border">built-in</span>@endif
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($f->description, 110) }}</div>
                            </td>
                            <td>
                                {{ \App\Models\ExamFeature::KINDS[$f->content_kind] ?? $f->content_kind }}
                                                            </td>
                            <td>{{ $f->exam_scope === 'all' ? 'All exam types' : $f->exams->map(fn ($e) => $e->short_name ?: $e->name)->implode(', ') }}</td>
                            <td>{{ $usage[$f->id] ?? 0 }} course(s)</td>
                            <td>{{ $f->position }}</td>
                            <td>
                                @if ($f->status)<span class="badge rounded-pill bg-orange text-white">Show</span>
                                @else<span class="badge rounded-pill bg-secondary text-white">Hide</span>@endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ admin_general('features/' . $f->id . '/edit') }}" class="btn btn-sm btn-circle btn-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                @unless ($f->is_system)
                                    <form action="{{ admin_general('features/' . $f->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this feature?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No features yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
