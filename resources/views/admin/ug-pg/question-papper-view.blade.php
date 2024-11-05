@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Question Paper List</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <a href="{{ug_pg_cms('question-paper')}}" class="btn btn-sm btn-primary me-1 add_new">
                    
                        <i class="bi bi-plus me-1"></i>Add Question Paper</a>
                 
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
                
                <div class="col-lg-12 ">
                     <!-- Course list table START -->
                    <div class="table-responsive border-0">
                        <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                            <!-- Table head -->
                            <thead>
                                <tr>
                                    <th scope="col" class="border-0">#</th>
                                    <th scope="col" class="border-0">Course Details</th>
                                    <th scope="col" class="border-0">Semester</th>
                                    <th scope="col" class="border-0">Title</th>
                                    <th scope="col" class="border-0">Status</th>
                                    <th scope="col" class="border-0 rounded-end">Action</th>
                                </tr>
                            </thead>

                            <!-- Table body START -->
                            <tbody>
                                @php
                                    $c = 1;
                                @endphp
                            @foreach ($list as $list2)
                                    
                                <!-- Table item -->
                                    <tr>
                                    
                                        <!-- Table data -->
                                        <td>{{$c++}}</td>
                                        <td>
                                            {{remove_slug($list2->university_name)}}<br>
                                            {{remove_slug($list2->level_name)}}<br>
                                            {{remove_slug($list2->course_name)}}
                                        </td>
                                        <td>
                                            {{remove_slug($list2->semester_name)}}<br>
                                            {{remove_slug($list2->subject_name)}}
                                        </td>
                                        <td>
                                            {{remove_slug($list2->title)}}<br>
                                            {{$list2->year}}
                                        </td>
                                        <td>
                                            @if($list2->status == 1)
                                                <span class="badge  rounded-pill  bg-orange text-white">Show</span>
                                            @else
                                                
                                            <span class="badge  rounded-pill  bg-secondary text-white">Hide</span>
                                            @endif
                                        </td>
                                    
                                        <!-- Table data -->
                                        <td>
                                            <a  data-bs-toggle="tooltip" data-bs-placement="top" title="View" target="_new" href="{{ug_pg_cms('question-paper-single/'.$list2->id)}}" class="btn btn-sm btn-primary btn-circle"><i class="bi bi-eye "></i></a>
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" href="{{ug_pg_cms('question-paper-edit/'.$list2->id)}}"  data-id="{{$list2->id}}" class="btn btn-sm btn-info btn-circle reset-banner"><i class="bi bi-pencil "></i></a>
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"  data-id="{{$list2->id}}" href="{{ug_pg_cms('question-paper/delete/'.$list2->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
                                        </td>
                                    </tr>
                                
                                @endforeach
                                
                            </tbody>
                            <!-- Table body END -->
                        </table>
                    </div>
                    <!-- Course list table END -->

                            
                    
                    
                </div>
            </div>
            <!-- Search and select END -->


        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->


@endsection


