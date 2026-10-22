@extends($layout)
@section('content2')
@php $label = $exam->short_name ?: $exam->name; @endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h3 class="mb-0">{{ $label }} - Subjects</h3><div class="small text-muted">Groups / folders for the free YouTube classes of this exam type.</div></div>
    <a href="{{ url('admin/content/free-classes?exam=' . $exam->slug) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-play-btn me-1"></i>YouTube classes</a>
</div>
@if (session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
    <form action="{{ $base }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">@csrf
        <div class="col-md-4"><label class="form-label small mb-0">Subject / folder name</label><input class="form-control" name="title" required maxlength="150"></div>
        <div class="col-md-3"><label class="form-label small mb-0">Inside (optional)</label><select name="parent_id" class="form-select"><option value="">Top level</option>@foreach ($subjects->whereNull('parent_id') as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small mb-0">Colour</label><input type="color" class="form-control form-control-color" name="color" value="#1b2a41"></div>
        <div class="col-md-3"><label class="form-label small mb-0">Picture (optional)</label><input type="file" class="form-control" name="image" accept="image/*"></div>
        <div class="col-12"><button class="btn btn-success mb-0"><i class="bi bi-plus me-1"></i>Add subject</button></div>
    </form>
</div></div>

<div class="table-responsive"><table class="table table-dark-gray align-middle mb-0">
    <thead><tr><th>#</th><th>Subject</th><th>Classes</th><th>Shown</th><th class="text-end">Action</th></tr></thead>
    <tbody>
    @php $roots = $subjects->whereNull('parent_id'); @endphp
    @forelse ($roots as $s)
        @foreach ([$s] + $subjects->where('parent_id', $s->id)->all() as $row)
            <tr>
                <td>{{ $row->position }}</td>
                <td>@if ($row->parent_id)<span class="text-muted ms-3">↳</span> @endif<span class="badge" style="background:{{ $row->color ?: '#6c757d' }}">&nbsp;</span> <b>{{ $row->title }}</b></td>
                <td>{{ $classCounts[$row->id] ?? 0 }}</td>
                <td>{!! $row->status ? '<span class="badge bg-success">Shown</span>' : '<span class="badge bg-secondary">Hidden</span>' !!}</td>
                <td class="text-end text-nowrap">
                    <button class="btn btn-sm btn-circle btn-info mb-0" type="button" onclick="document.getElementById('ed{{ $row->id }}').classList.toggle('d-none')"><i class="bi bi-pencil"></i></button>
                    <form action="{{ $base . '/' . $row->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subject? Its classes are kept without a folder.')">@csrf @method('DELETE')<button class="btn btn-sm btn-circle btn-danger mb-0"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
            <tr id="ed{{ $row->id }}" class="d-none"><td colspan="5">
                <form action="{{ $base . '/' . $row->id }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">@csrf @method('PUT')
                    <div class="col-md-3"><input class="form-control" name="title" value="{{ $row->title }}" required maxlength="150"></div>
                    <div class="col-md-3"><select name="parent_id" class="form-select"><option value="">Top level</option>@foreach ($subjects->whereNull('parent_id')->where('id', '!=', $row->id) as $p)<option value="{{ $p->id }}" {{ $row->parent_id == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>@endforeach</select></div>
                    <div class="col-md-1"><input type="number" min="0" class="form-control" name="position" value="{{ $row->position }}"></div>
                    <div class="col-md-1"><input type="color" class="form-control form-control-color" name="color" value="{{ $row->color ?: '#1b2a41' }}"></div>
                    <div class="col-md-2"><input type="file" class="form-control" name="image" accept="image/*"></div>
                    <div class="col-md-1"><div class="form-check"><input class="form-check-input" type="checkbox" name="status" value="1" id="sh{{ $row->id }}" {{ $row->status ? 'checked' : '' }}><label class="form-check-label small" for="sh{{ $row->id }}">Shown</label></div></div>
                    <div class="col-md-1"><button class="btn btn-sm btn-success mb-0">Save</button></div>
                </form>
            </td></tr>
        @endforeach
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No subjects yet. Add the first one above.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
