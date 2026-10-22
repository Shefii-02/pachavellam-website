@extends('admin.exam.section')
@section('content2')
@if ($isAdmin)
    <div class="mb-2 text-end"><a href="{{ admin_general('exam-types/' . $exam->id . '/edit') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil me-1"></i>Edit exam type</a></div>
@endif
@include('admin.partials.exam-dashboard', ['dash' => $dash])
@endsection
