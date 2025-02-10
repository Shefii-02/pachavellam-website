@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Special Tab</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    @can('special Tab Add')
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
                     @can('special Tab Add')
                    <form action="" method="POST">
                      @csrf()
                        <div class="row">
                            
                            <div class="col-md-5">
                                <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                    <!-- Image -->
                                    <img src="/assets/images/element/gallery.svg" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                    <div>
                                        <label style="cursor:pointer;">
                                            <span> 
                                                <input class="form-control stretched-link" type="file"  accept="image/*" id="pic1" name="my-image"  accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture1" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Redirection</label>
                                <input class="form-control" type="text" name="redirection" placeholder="Enter Redirection link" >
                                <div classs="col-md-2 ">
                                    <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                                </div>
                            </div>
                            
                        </div>
                      
                    </form>

                    @endcan
                    
                    
                </div>
            </div>
            <!-- Search and select END -->
             @can('special Tab list')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0 rounded-start">Image</th>
                            <th scope="col" class="border-0">Redirect</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach($special_tb as $list)
                        <!-- Table item -->
                        <tr>
                            <!-- Table data -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Image -->
                                    <div class="w-100px">
                                        <img src="{{ Storage::url('files/'.$list->image) }}" class="rounded" alt="">
                                    </div>
                                    
                                </div>
                            </td>

                            <!-- Table data -->
                            <td>
                                <a href="{{$list->redirection}}">{{$list->redirection}}</a>
                            </td>
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
                                @can('special Tab edit')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-id="{{$list->id}}" class="btn btn-sm btn-info  reset-banner btn-circle"><i class="bi bi-pencil "></i></a>
                                @endcan
                                @can('special Tab delete')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"   data-id="{{$list->id}}" href="{{route('adminkpsc.special-tab.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>    
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
            <!--<div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3">-->
                <!-- Content -->
            <!--    <p class="mb-0 text-center text-sm-start">Showing 1 to 8 of 20 entries</p>-->
                <!-- Pagination -->
            <!--    <nav class="d-flex justify-content-center mb-0" aria-label="navigation">-->
            <!--        <ul class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0">-->
            <!--            <li class="page-item mb-0"><a class="page-link" href="#" tabindex="-1"><i class="fas fa-angle-left"></i></a></li>-->
            <!--            <li class="page-item mb-0"><a class="page-link" href="#">1</a></li>-->
            <!--            <li class="page-item mb-0 active"><a class="page-link" href="#">2</a></li>-->
            <!--            <li class="page-item mb-0"><a class="page-link" href="#">3</a></li>-->
            <!--            <li class="page-item mb-0"><a class="page-link" href="#"><i class="fas fa-angle-right"></i></a></li>-->
            <!--        </ul>-->
            <!--    </nav>-->
            <!--</div>-->
            <!-- Pagination END -->
        </div>
        <!-- Card body START -->
    </div>
    <!-- Modal -->
<div class="modal fade" id="edit_banner" tabindex="-1" role="dialog" aria-labelledby="edit_banner_modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_banner_modal">Edit Banner</h5>
          <button type="button" class="close" onclick="$('#edit_banner').modal('hide');" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="/admin/kpsc/special-tab/update-specialtab" method="POST">
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
<!-- Main content END -->
<div class="modal uploadimageModal" tabindex="-1" role="dialog" id="uploadimageModal">
    <div class="modal-dialog" role="document" style="min-width: 700px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="image_demo"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="preview_target" value="" />
                <input type="hidden" id="input_target" value="" />
                <button type="button" class="btn btn-primary crop_image">Crop and Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>   

@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.js"></script>
<script>

    $(document).ready(function(){
        
        $(".add_new_banner").click(function(){
            
            $(".new-banner-section").fadeToggle(1000); 
        });
    });
    
    $('#pic1').on('change', function() {
     const file = this.files[0];
    if (file) {
        let reader = new FileReader();
        reader.onload = function (event) {
            $("#uploaded-image2,#uploaded-image").attr("src", event.target.result);
            $("#picture1").val(event.target.result);
        };
        reader.readAsDataURL(file);
    }
});
    
    



$('.reset-banner').click(function(event) {

    var id = $(this).data('id');
    var url = "{{route('adminkpsc.special-tab.edit', 'id')}}";
    var url = url.replace("/id/",'/'+id+'/');
    alert(url)
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
