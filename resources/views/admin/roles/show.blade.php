@extends('admin.general.layout')

@section('general')
    <div class="row mb-4">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Show Role</h1>
            <a class="btn btn-primary" href="{{ route('admingeneralrole.index') }}">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow-lg border-0 rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h5 class="text-primary"><i class="fas fa-user-tag"></i> Role Name</h5>
                    <div class="alert alert-light border p-2">{{ $role->name }}</div>
                </div>

                <div class="col-md-12">
                    <h5 class="text-primary"><i class="fas fa-shield-alt"></i> Permissions</h5>
                    <div class="border p-3 rounded bg-light">
                        @if(!empty($rolePermissions))
                            @foreach($rolePermissions as $v)
                                <span class="badge bg-success px-3 py-2 me-1 mb-1">{{ $v->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No permissions assigned</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
