@extends('admin.main-layout')
@section('main')
@php $chosenRoles = old('roles', $userRoles); $chosenPerms = old('permissions', $direct); $chosenExams = array_map('intval', old('exam_ids', $examIds)); @endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <a href="{{ url('admin/staff-access') }}" class="small"><i class="bi bi-arrow-left"></i> Staff access</a>
    <h3 class="mb-1 mt-1">{{ $user->name }}</h3>
    <div class="small text-muted mb-3">{{ $user->email }}</div>

    <form action="{{ url('admin/staff-access/' . $user->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3 mb-3">
            <div class="col-lg-4"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
                <h6>Roles</h6>
                @foreach ($roles as $r)
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->name }}" id="r{{ $r->id }}" @checked(in_array($r->name, $chosenRoles))><label class="form-check-label" for="r{{ $r->id }}">{{ $r->name }}</label></div>
                @endforeach
                <div class="form-text mt-2">A role brings its permissions. Edit what a role includes in the Roles page.</div>
            </div></div></div>
            <div class="col-lg-8"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
                <div class="d-flex justify-content-between"><h6>Exam types</h6><label class="small mb-0"><input type="checkbox" class="form-check-input" id="allExams"> all</label></div>
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($exams as $e)
                        <div class="form-check"><input class="form-check-input exam-check" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="e{{ $e->id }}" @checked(in_array($e->id, $chosenExams))><label class="form-check-label" for="e{{ $e->id }}">{{ $e->short_name ?: $e->name }}</label></div>
                    @endforeach
                </div>
                <div class="form-text mt-2">The exam types this person can open, see students of, and work in. With none ticked they cannot open any exam type. New exam types are not added automatically.</div>
            </div></div></div>
        </div>

        <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
            <h6>Permissions <span class="small text-muted fw-normal">tick to give directly; a tick with a lock comes from a role</span></h6>
            <div class="row g-3">
                @foreach ($groups as $group => $perms)
                    <div class="col-md-4">
                        <div class="fw-semibold mb-1">{{ $group }}</div>
                        @foreach ($perms as $name => $cfg)
                            @php $fromRole = in_array($name, $viaRole); @endphp
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $name }}" id="p{{ md5($name) }}" @checked(in_array($name, $chosenPerms) || $fromRole)>
                                <label class="form-check-label" for="p{{ md5($name) }}">{{ $cfg['label'] }} @if ($fromRole)<i class="bi bi-lock-fill text-muted" title="from a role"></i>@endif @if ($cfg['exam_scoped'])<span class="badge bg-light text-dark border">exam types</span>@endif</label>
                                <div class="form-text mt-0">{{ $cfg['hint'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="alert alert-info py-2 small mt-3 mb-0">"exam types" permissions only work inside the exam types ticked above. Money screens (admissions, revenue, payment links) are for people you trust: leave them unticked for most staff.</div>
        </div></div>
        <div class="d-flex gap-2"><button class="btn btn-primary mb-0">Save access</button><a href="{{ url('admin/staff-access') }}" class="btn btn-light mb-0">Cancel</a></div>
    </form>
</div>
<script>
var all = document.getElementById('allExams');
all.addEventListener('change', function () { document.querySelectorAll('.exam-check').forEach(function (c) { c.checked = all.checked; }); });
</script>
@endsection
