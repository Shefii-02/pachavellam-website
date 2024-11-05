@extends('admin.general.layout')
    @section('general')
    <div class="row">
        <div class="col-lg-12 row">
            <div class="col-md-6 text-left">
                <div class="col-12 mb-3">
                    <h1 class="h3 mb-2 mb-sm-0">Show  Role</h1>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a class="btn btn-primary" href="{{ route('admingeneralrole.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </div>


<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group mb-3">
            <strong>Name:</strong><br>
            {{ $role->name }}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group mb-3">
            <strong class="mb-3">Permissions:</strong><br>
            @if(!empty($rolePermissions))
                @foreach($rolePermissions as $v)
                    <label class="label label-success">{{ $v->name }},</label>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection