@extends('admin.general.layout')
    @section('general')
        <div class="row">
            <div class="col-lg-12 row">
                <div class="col-md-6 text-left">
                    <div class="col-12 mb-3">
                        <h1 class="h3 mb-2 mb-sm-0">Create New Role</h1>
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

        <form action="{{ url('admin/general/role') }}" method="POST">
            @csrf()
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group mb-3">
                        <label class="mb-2">Name:</label>
                        <input type="text" name="name" placeholder="Role Name" class="form-control">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group mb-3">
                        <label class="mb-2">Permission:</label>
                        <div class="form-group mt-2 row ">
                        @foreach($permission as $value)

                            <div class="col-md-4 mb-3">
                                <label>
                                <input type="checkbox" name="permission[]" value="{{$value->id}}"  class=" name">

                                {{ $value->name }}</label>
                            </div>
                            
                        <br/>
                        @endforeach
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 text-left">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit</button>
                </div>
            </div>
        </form>
        


@endsection