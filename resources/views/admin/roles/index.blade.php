@extends('admin.general.layout')
    @section('general')
    <div class="row">
        <div class="col-lg-12 row">
            <div class="col-md-6 text-left">
                <div class="col-12 mb-3">
                    <h1 class="h3 mb-2 mb-sm-0"> Role Management</h1>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a class="btn btn-primary" href="{{ route('admingeneralrole.create') }}"><i class="fas fa-plus"></i> Create</a>
            </div>
        </div>
    </div>



    @if ($message = Session::get('success'))

        <div class="alert alert-success">

            <p>{{ $message }}</p>

        </div>

    @endif



    <table class="table table-bordered">

        <tr>

            <th>No</th>

            <th>Name</th>

            <th width="280px">Action</th>

        </tr>

        @foreach ($roles as $key => $role)

        <tr>

            <td>{{ ++$i }}</td>

            <td>{{ $role->name }}</td>

            <td>

                <a class="btn btn-info" href="{{ route('admingeneralrole.show',$role->id) }}">Show</a>

                {{-- @can('role-edit') --}}

                    <a class="btn btn-primary" href="{{ route('admingeneralrole.edit',$role->id) }}">Edit</a>

                {{-- @endcan --}}

                @can('role-delete')

                    {!! Form::open(['method' => 'DELETE','route' => ['admingeneralrole.destroy', $role->id],'style'=>'display:inline']) !!}

                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}

                    {!! Form::close() !!}

                @endcan

            </td>

        </tr>

        @endforeach

    </table>



{!! $roles->render() !!}




@endsection