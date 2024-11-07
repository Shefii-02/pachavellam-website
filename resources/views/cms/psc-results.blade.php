@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Psc Results</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    @can('Psc Results add')
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
                    @can('Psc Results add')
                    <form action="{{url(kpsc_cms('psc-results'))}}" method="POST" enctype="multipart/form-data">
                        @csrf()
                        <div class="row">
                            
                            
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <input class="form-control" required  type="text" name="type"  >
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input class="form-control" required  type="text" name="title"  >
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Pdf</label>
                                <input class="form-control"  required  type="file" name="file" >
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

            <!-- Course list table START -->
            @can('Psc Results list')
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">Type</th>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">File</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach ($result_list as $list)
                            
                        <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>{{$list->type}}</td>
                                <td>{{$list->title}}</td>
                                <td><a href="{{ Storage::url('files/'.$list->file_name) }}" target="_new">Open</a></td>
                                
                                <!-- Table data -->
                                <td>
                                    @can('Psc Results edit')
                                    {{-- <a href="#" data-id="{{$list->id}}" class="btn btn-sm btn-info me-1 reset-banner"><i class="bi bi-pencil me-1"></i>Reset</a> --}}
                                    @endcan
                                    @can('Psc Results delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  data-id="{{$list->id}}" href="{{route('adminkpsc.psc-results.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
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
            {{-- <div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3">
                <!-- Content -->
                <p class="mb-0 text-center text-sm-start">Showing 1 to 8 of 20 entries</p>
                <!-- Pagination -->
                <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
                    <ul class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0">
                        <li class="page-item mb-0"><a class="page-link" href="#" tabindex="-1"><i class="fas fa-angle-left"></i></a></li>
                        <li class="page-item mb-0"><a class="page-link" href="#">1</a></li>
                        <li class="page-item mb-0 active"><a class="page-link" href="#">2</a></li>
                        <li class="page-item mb-0"><a class="page-link" href="#">3</a></li>
                        <li class="page-item mb-0"><a class="page-link" href="#"><i class="fas fa-angle-right"></i></a></li>
                    </ul>
                </nav>
            </div> --}}
            <!-- Pagination END -->
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->


   
@endsection

@section('scripts')

<script>

    $(document).ready(function(){
        
        $(".add_new_banner").click(function(){
            
            $(".new-banner-section").fadeToggle(1000); 
        });
    });
    
$('.reset-banner').click(function(event) {

    var id = $(this).data('id');
    var url = "{{route('adminkpsc.banner-slider.edit', 'id')}}";
    var url = url.replace("/id/",'/'+id+'/');

    $.ajax({
        url: url,
        cache: false,
        success: function(html){
            $('.editbanner').html(html);
            $('#edit_banner').modal('show');
        }
    });
   
    
});
</script>
@endsection
 