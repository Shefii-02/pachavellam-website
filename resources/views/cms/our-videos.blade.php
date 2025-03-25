@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Our Videos</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    @can('Our Videos Add')
                    <!-- Short by filter -->
                    <a href="#" class="btn btn-sm btn-primary me-1 add_new_banner"><i class="bi bi-plus me-1"></i>Add New</a>
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
                    @can('Our Videos Add')
                    <form action="" method="POST">
                        <div class="row">
                            
                            <div class="col-md-5 mb-2">
                                <label class="form-label">Title</label>
                                <input class="form-control" type="text" name="title" placeholder="Enter Youtube Title" >
                                
                            </div>
                            <div class="col-md-5 mb-2">
                                <label class="form-label">Redirection<small><strike>https://youtu.be/</strike>HT6h0OsVrso</small></label>
                                <input class="form-control" type="text" name="redirection" placeholder="Enter Youtube link" >
                            
                            </div>
                            <div classs="col-md-2 mb-2">
                                    <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                            </div>
                        </div>
                        @csrf()
                    </form>
                   @endcan

                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->
            @can('Our Videos List')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">Image</th>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Redirect</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach($our_videos as $videos)
                        <!-- Table item -->
                        <tr>
                            <!-- Table data -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Image -->
                                    <div class="w-100px">
                                        <img src="https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg" class="rounded" alt="">
                                    </div>
                                    
                                </div>
                            </td>

                            <!-- Table data -->
                            <td>{{$videos->title}}</td>
                            <td><a href="https://youtu.be/{{$videos->redirection}}">https://youtu.be/{{$videos->redirection}}</a></td>
                            <td>{{$videos->position}}</td>
                                <td>
                                    @if($videos->status == 1)
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                            <!-- Table data -->
                            <td>
                                @can('Our Videos Edit')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-id="{{$videos->id}}" class="btn btn-sm btn-info  reset-banner btn-circle"><i class="bi bi-pencil "></i></a>
                                @endcan
                                @can('Our Videos delete')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"   data-id="{{$videos->id}}" href="{{route('adminkpsc.our-videos.delete', $videos->id)}}" class="btn btn-sm btn-danger btn-circle "><i class="bi bi-trash "></i></a>
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
            
        </div>
        <!-- Card body START -->
    </div>

<!-- Modal -->
<div class="modal fade" id="edit_banner" tabindex="-1" role="dialog" aria-labelledby="edit_banner_modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_banner_modal">Edit Oue videos</h5>
          <button type="button" class="close" onclick="$('#edit_banner').modal('hide');" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="/admin/kpsc/our-videos/update-ourvideos" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner">

                </div>

            </div>
            <div class="modal-footer">
            
                <button type="submit" class="btn btn-success prev-btn mb-0 "><i class="bi bi-check me-1"></i> Submit</button>

            </div>
        </form>
      </div>
    </div>
  </div>
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
        var url = "{{route('adminkpsc.our-videos.edit', 'id')}}";
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
