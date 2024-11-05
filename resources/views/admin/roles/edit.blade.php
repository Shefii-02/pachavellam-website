@extends('admin.general.layout')
    @section('general')
        <div class="row">
            <div class="col-lg-12 row">
                <div class="col-md-6 text-left">
                    <div class="col-12 mb-3">
                        <h1 class="h3 mb-2 mb-sm-0">Edit Role</h1>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a class="btn btn-primary" href="{{ route('admingeneralrole.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>


    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif


{!! Form::model($role, ['method' => 'PATCH','route' => ['admingeneralrole.update', $role->id]]) !!}
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group mb-3">
            <strong>Name:</strong>
            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group mb-3">
            <strong class="mb-3">Permission:</strong>
            <br/><input type="checkbox" id="checkAll">Check All
            <div class="row">

                @foreach($permission as $value)
                <div class="col-6">
                    <label>{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                    {{ $value->name }}</label>

                </div>
            
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 text-left">
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Update</button>
    </div>
</div>
{!! Form::close() !!}


@endsection

@section('scripts')
<script>
     $("#checkAll").click(function () {
         $('input:checkbox').not(this).prop('checked', this.checked);
     });
</script>

@endsection
