@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0"> Log Files</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <a href="{{ route('admingenerallogs.delete',str_replace('.html', '', $file_name)) }}" class="btn btn-sm btn-danger me-1 ">
                        <i class="bi bi-trash me-1"></i>Delete File 
                    </a>

                    <span class="js-choice ">

                    </span>
                </div>
            </div>

        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body">

            {!! $file !!}

        </div>
        <!-- Card body START -->
    </div>
    <!-- Main content END -->
@endsection
