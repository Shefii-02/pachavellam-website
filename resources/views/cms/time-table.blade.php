@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Time Table</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    {{-- @can('Time Table Add') --}}
                    <!-- Short by filter -->
                    <a href="#" class="btn btn-sm btn-primary me-1 add_new_banner"><i class="bi bi-plus me-1"></i>Add New</a>
                    {{-- @endcan --}}
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
                
                <div class="col-lg-12 new-banner-section " style="display: none;">
                    {{-- @can('Time Table Add') --}}
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            
                            <div class="col-md-5 mb-2">
                                <label class="form-label">Title</label>
                                <input class="form-control" type="text" name="title" placeholder="Enter Title" >
                            </div>
                            <div class="col-md-5 mb-2">
                                <label class="form-label">File</label>
                                <input class="form-control" type="file" name="file" accept=".pdf"  >
                            
                            </div>
                            <div classs="col-md-2 mb-2">
                                    <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                            </div>
                        </div>
                        @csrf()
                    </form>
                   {{-- @endcan --}}

                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->
            {{-- @can('Time Table List') --}}
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Redirect</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach($time_table as $list)
                        <!-- Table item -->
                        <tr>
                            <!-- Table data -->
                           
                            <!-- Table data -->
                            <td>{{$list->title}}</td>
                            
                            <td><a href="{{ Storage::url('time-table/'.$list->file) }}" target="_new">Open</a></td>
                            
                            <td>{{$list->position}}</td>
                                <td>
                                    @if($list->status == 1)
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                            <!-- Table data -->
                            <td>
                                
                                {{-- @can('Time Table Delete') --}}
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"   data-id="{{$list->id}}" href="{{route('adminkpsc.time-table.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle "><i class="bi bi-trash "></i></a>
                                {{-- @endcan --}}
                            </td>
                        </tr>

                        @endforeach

                        
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->
            {{-- @endcan --}}
            
        </div>
        <!-- Card body START -->
    </div>

@endsection

@section('scripts')
<script>

    $(document).ready(function(){
        
        $(".add_new_banner").click(function(){
            
            $(".new-banner-section").fadeToggle(1000); 
        });
    });

</script>
@endsection
