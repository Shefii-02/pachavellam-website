@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">What's New</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                   
                  <a href="#" class="btn btn-sm btn-primary me-1 add_new_banner">
                      <i class="bi bi-plus me-1"></i>Add New</a>
                    <!-- Short by filter -->
                    
                     <!--@can('Whtas New add')--> <!--@endcan-->
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
                    @can('Whtas New add')
                    @endcan
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
                                                <input class="form-control stretched-link" type="file"  accept="image/*" id="pic" name="my-image" id="image" accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="col-md-12">
                                    <label class="form-label">Title</label>
                                    <input class="form-control" type="text" name="title" required placeholder="Enter Title" >
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Redirection</label>
                                    <input class="form-control" type="text" name="redirection" required placeholder="Enter Redirection link" >
                                </div>
                            </div>
                            <div class="col-md-5"></div>
                            <div class="col-md-5 row">
                                <div class="col-md-6">
                                    <label class="form-label">Bacground Color</label>
                                    <input class="form-control" type="color" name="bgcolor" required placeholder="Section background color" >
                                
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Text Color</label>
                                    <input class="form-control" type="color" name="textcolor" required placeholder="Title text color" >
                                    
                                </div>
                                <div classs="col-md-12 ">
                                    <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                                </div>
                            </div>
                        </div>
                    
                    </form>
                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->
            @can('Whtas New list')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0 rounded-start">Image</th>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Redirect</th>
                            <th scope="col" class="border-0">Background color</th>
                            <th scope="col" class="border-0">Text Color</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach($whatsnew as $whatsnew_list)
                            <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!-- Image -->
                                        <div class="w-100px">
                                            <img src="{{ Storage::url('files/'.$whatsnew_list->image) }}" class="rounded" alt="">
                                        </div>
                                        
                                    </div>
                                </td>
                                <!-- Table data -->
                                <td>
                                    {{$whatsnew_list->title}}
                                </td>
                                <!-- Table data -->
                                <td>
                                    <a href="{{$whatsnew_list->redirection}}">{{$whatsnew_list->redirection}}</a>
                                </td>
                                <!-- Table data -->
                                <td>
                                    <div style="background: {{$whatsnew_list->bg_color}};width:20px;height:20px"></div>
                                </td>
                                <!-- Table data -->
                                <td>
                                    <div style="background: {{$whatsnew_list->text_color}};width:20px;height:20px"></div>
                                </td>
                                <!-- Table data -->
                                <td>
                                    {{$whatsnew_list->position}}
                                </td>
                                <!-- Table data -->
                                <td>
                                    @if($whatsnew_list->status == 1)
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                            

                                <!-- Table data -->
                                <td>
                                    @can('Whtas New edit')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-id="{{$whatsnew_list->id}}" class="btn btn-sm btn-info  reset-banner btn-circle"><i class="bi bi-pencil "></i></a>
                                    @endcan
                                    @can('Whtas New delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  data-id="{{$whatsnew_list->id}}" href="{{route('adminkpsc.whats-new.delete', $whatsnew_list->id)}}" class="btn btn-sm btn-danger  btn-circle"><i class="bi bi-trash "></i></a>
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
<div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="edit__modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit__modal">Edit Whatsnew</h5>
          <button type="button" class="close" onclick="$('#edit_modal').modal('hide');" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="/admin/kpsc/whats-new/update-whatsnew" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner">

                </div>

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
    var image_crop = $('.image_demo').croppie({
    viewport: {
        width: 150,
        height: 150,
        type: 'square'
    },
    boundary: {
        width: 150,
        height: 150
    }
});
/// catching up the cover_image change event and binding the image into my croppie. Then show the modal.
$('#pic').on('change', function() {
    var reader = new FileReader();
    reader.onload = function(event) {
        image_crop.croppie('bind', {
            url: event.target.result,
        });
    }
    reader.readAsDataURL(this.files[0]);
    $('#uploadimageModal').modal('show');
});


$('.crop_image').click(function(event) {
    image_crop.croppie('result', {
        type: 'canvas',
        format: 'png'
    }).then(function(response) {
        $("#uploaded-image").attr("src", response);
        $("#picture").val(response);
    });
    $("#pic").val("");
    $('#uploadimageModal').modal('hide');
});
$('.reset-banner').click(function(event) {

    var id = $(this).data('id');
    var url = "{{route('adminkpsc.whats-new.edit', 'id')}}";
    var url = url.replace("/id/",'/'+id+'/');
   
    $.ajax({
        url: url,
        cache: false,
        success: function(html){
            $('.editbanner').html(html);
            $('#edit_modal').modal('show');
        }
    });


});

</script>
@endsection
