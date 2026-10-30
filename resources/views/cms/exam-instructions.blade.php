@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Exam instructions</h3>
            <a class="btn btn-sm btn-outline-primary" href="{{ url('admin/kpsc/exam-reports') }}">Reported errors</a>
        </div>
        <div class="card-body">
            @if (session('ok'))<div class="alert alert-success py-2">{{ session('ok') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif
            <p class="text-muted small">Write an instruction once, then pick it in the OMR / MCQ exam form. An exam that picks none shows the default of its kind.</p>

            <form method="POST" action="{{ url('admin/kpsc/exam-instructions/save') }}" class="border rounded-3 p-3 mb-4">
                @csrf
                <input type="hidden" name="id" value="{{ $edit?->id }}">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required maxlength="120" value="{{ old('name', $edit?->name) }}"></div>
                    <div class="col-md-3"><label class="form-label">For</label>
                        <select class="form-select" name="kind">
                            <option value="omr" @selected(old('kind', $edit?->kind) === 'omr')>OMR exam</option>
                            <option value="mcq" @selected(old('kind', $edit?->kind) === 'mcq')>MCQ exam</option>
                        </select></div>
                    <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="isdef" @checked(old('is_default', $edit?->is_default))><label class="form-check-label" for="isdef">Default for this kind</label></div></div>
                    <div class="col-12"><label class="form-label">Instructions (HTML: p, ul, ol, li, b, i, u, br, h5, h6)</label><textarea class="form-control" name="body" rows="9" required>{{ old('body', $edit?->body) }}</textarea></div>
                    <div class="col-12"><button class="btn btn-success">{{ $edit ? 'Update' : 'Add' }} instruction</button> @if ($edit)<a class="btn btn-light" href="{{ url('admin/kpsc/exam-instructions') }}">Cancel</a>@endif</div>
                </div>
            </form>

            <table class="table align-middle">
                <thead><tr><th>Name</th><th>For</th><th>Exams using it</th><th></th></tr></thead>
                <tbody>
                @forelse ($list as $i)
                    <tr>
                        <td>{{ $i->name }} @if ($i->is_default)<span class="badge bg-success">Default</span>@endif</td>
                        <td class="text-uppercase">{{ $i->kind }}</td>
                        <td>{{ $used[$i->id] ?? 0 }}</td>
                        <td class="text-end"><a class="btn btn-sm btn-info" href="{{ url('admin/kpsc/exam-instructions?edit=' . $i->id) }}">Edit</a>
                            <a class="btn btn-sm btn-danger" onclick="return confirm('Delete? Exams using it fall back to the default.')" href="{{ url('admin/kpsc/exam-instructions/delete/' . $i->id) }}">Delete</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No instructions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
