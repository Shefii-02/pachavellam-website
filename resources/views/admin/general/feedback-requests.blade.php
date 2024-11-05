@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Student Feedback Data</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <a href="/admin/general/feedback" class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-plus me-1"></i>Back to</a>
                 
                    <span class="js-choice ">
                        
                    </span>
                </div>
            </div>
            
        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body">

            <!-- Search and select START -->
            <div class="row g-3 align-items-center justify-content-between mb-4">
                
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">#</th>
                            <th scope="col" class="border-0">Name</th>
                            <th scope="col" class="border-0">Mobile</th>
                            <th scope="col" class="border-0">Feedback</th>
                            <th scope="col" class="border-0">Star Rating</th>
                            <th scope="col" class="border-0">Created at</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        
                 @foreach ($list as $key => $list)
                            
                        <!-- Table item -->
                            <tr>
                                <td>{{$key+1}}</td>
                                <!-- Table data -->
                                <td>{{ucwords($list->name)}}</td>
                                <td>{{$list->mobile}}</td>
                                <td>{{$list->message}}</td>
                                <td>{{$list->star_rating}}</td>
                                <td>{{date('Y-m-d',strtotime($list->created_at))}}</td>
                                
                            </tr>
                        
                    @endforeach
                 
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->
            </div>
        </div>
    </div>
@endsection