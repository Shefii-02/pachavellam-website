@extends('admin.general.layout')
@section('general')
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-0">Exam Types</h3>
                <div class="small text-muted">KPSC, RRB, SSC, KTET, UGC NET and any exam you add. Each gets a tab in the main sidebar, its own categories, courses and coupons, and students can pick it as an interest.</div>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ admin_general('exam-types/create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Add Exam Type</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                <thead>
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Exam type</th>
                        <th class="border-0">Slug</th>
                        <th class="border-0">Categories</th>
                        <th class="border-0">Courses</th>
                        <th class="border-0">Interested students</th>
                        <th class="border-0">Position</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 rounded-end text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $e)
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>
                                <span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>
                                <span class="ms-1">{{ $e->name }}</span>
                                @if (isset(\App\Models\CompetitiveExam::MODULES[$e->slug]))<span class="badge bg-light text-dark border" title="Has its own admin module with banners, notifications and more">module</span>@endif
                            </td>
                            <td><code>{{ $e->slug }}</code></td>
                            <td><a href="{{ $e->examUrl('category') }}">{{ number_format($categories[$e->id] ?? 0) }}</a></td>
                            <td><a href="{{ $e->examUrl('courses') }}">{{ number_format($courses[$e->id] ?? 0) }}</a></td>
                            <td><a href="{{ $e->interestedUrl() }}">{{ number_format($students[$e->id] ?? 0) }}</a></td>
                            <td>{{ $e->position }}</td>
                            <td>
                                @if ($e->status)<span class="badge rounded-pill bg-orange text-white">Show</span>
                                @else<span class="badge rounded-pill bg-secondary text-white">Hide</span>@endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a title="Open" href="{{ $e->homeUrl() }}" class="btn btn-sm btn-circle btn-primary"><i class="bi bi-box-arrow-up-right"></i></a>
                                <a title="Edit" href="{{ admin_general('exam-types/' . $e->id . '/edit') }}" class="btn btn-sm btn-circle btn-info"><i class="bi bi-pencil"></i></a>
                                <form action="{{ admin_general('exam-types/' . $e->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam type?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No exam types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
