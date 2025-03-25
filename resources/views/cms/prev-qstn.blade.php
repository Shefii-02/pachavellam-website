@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Prev Question Paper</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3 text-end">
                    @can('Prev Qstn add')
                    <!-- Short by filter -->
                    <button class="btn btn-sm btn-primary me-1 add_new_banner"><i class="bi bi-plus me-1"></i>Add New</button>
                    @endcan
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
                    @can('Prev Qstn add')
                    <form action="{{url(kpsc_cms('prev-qstn'))}}" method="POST" enctype="multipart/form-data">
                        @csrf()
                        <div class="row">
                            

                            
                            <div class="col-md-6 mb-2">
                                  <label class="form-label">Category</label>
                                <input list="categories" name="category" autocomplete="off" class="form-control" required >
                                <datalist id="categories">
                                    @foreach ($category as $item)
                                        <option value="{{ $item->category }}">
                                    @endforeach
                                </datalist>
                              
                               
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Sub Category</label>
                                
                                <input list="subcategory"  autocomplete="off" name="subcategory"   class="form-control" required >
                                <datalist id="subcategory">
                                    @foreach ($sub_category as $item)
                                        <option value="{{ $item->subcategory }}">
                                    @endforeach
                                </datalist>
                           
                               
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Title</label>
                                <input class="form-control" required   autocomplete="off" type="text" name="title"  >
                               
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Question Pdf</label>
                                <input class="form-control"    type="file" name="qstn_file" >
                                <div classs="col-md-2 ">
                                    
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Answer Pdf</label>
                                <input class="form-control"    type="file" name="ans_file" >
                                <div classs="col-md-2 ">
                                    <button type="submit" class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                                </div>
                            </div>
                        </div>
                    
                    </form>
                    @endcan
                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->
            @can('Prev Qstn list')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">Category</th>
                            <th scope="col" class="border-0">Sucategory</th>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Question Paper</th>
                            <th scope="col" class="border-0">Answer Key</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>
                    
                    
                    <!-- Table body START -->
                    <tbody>
                        @foreach ($prev_qstn as $list)
                            
                        <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>{{$list->category}}</td>
                                <td>{{$list->subcategory}}</td>
                                <td>{{$list->title}}</td>
                                <td><a href="{{ Storage::url('files/'.$list->qstn_paper) }}" target="_new">Open</a></td>
                                <td><a href="{{ Storage::url('files/'.$list->ans_key) }}" target="_new">Open</a></td>
                                <!-- Table data -->
                                <td>
                                    @can('Prev Qstn edit')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit" aria-label="Edit"  data-id="{{$list->id}}" href="{{route('adminkpsc.prev-qstn.edit', $list->id)}}" class="btn btn-sm btn-info btn-circle reset-banner"><i class="bi bi-pencil me-1 "></i></a>
                                    @endcan
                                    @can('Prev Qstn delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  data-id="{{$list->id}}" href="{{route('adminkpsc.prev-qstn.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
                                    @endcan
                                </td>
                            </tr>
                        
                        @endforeach
                        
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->
            @endcan
            <!-- Pagination START -->
            
            <!-- Pagination END -->
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

<!-- Modal -->

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
