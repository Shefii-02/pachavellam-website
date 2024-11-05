@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Paid Category Class</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7">
                    <!-- Short by filter -->
                    @can('Psc Paid Class category add')
                    <button class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-plus me-1"></i>Add New</button>
                    @endcan
                    @can('Psc Paid Class Sub Category list')
                    <a class="btn btn-sm btn-primary me-1" href="paid_subcategory_class" ><i class="bi bi-plus me-1"></i>Add|View-Sub Category</a>
                     @endcan
                    @can('Psc Paid Class list')
                    <a class="btn btn-sm btn-primary me-1" href="paid-class" ><i class="bi bi-plus me-1"></i>Add|View-PaidClass</a> 
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
                
                <div class="col-lg-12 new-section " style="display: none;">
                     @can('Psc Paid Class category add')
                    <form action="{{url(kpsc_cms('paid_category_class'))}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                    <!-- Image -->
                                    <img src="/assets/images/element/gallery.svg" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                    <div>
                                        <label style="cursor:pointer;">
                                            <span> 
                                                <input class="form-control stretched-link" type="file"  accept="image/*" id="pic1" name="my-image" accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Category Name</label>
                                <input class="form-control" required type="text" name="category_name" placeholder="Enter Category" >
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
            @can('Psc Paid Class Category list')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0 rounded-start">Image</th>
                            <th scope="col" class="border-0">Category</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach ($category_paid as $list)
                            
                        <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!-- Image -->
                                        <div class="w-100px">
                                            <img src="{{ Storage::url($list->image) }}" class="rounded" alt="">
                                        </div>
                                        
                                    </div>
                                </td>

                                <!-- Table data -->
                                <td>{{$list->category_name}}</td>
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
                                     @can('Psc Paid Class Category edit')
                                    <a href="#" data-position="{{$list->position}}" data-category="{{$list->category_name}}"  data-image="{{ url(Storage::url($list->image)) }}"  data-id="{{$list->id}}" class="btn btn-sm btn-info me-1 reset-banner"><i class="bi bi-pencil me-1"></i>Reset</a>
                                    @endcan
                                     @can('Psc Paid Class Category delete')
                                    <a  data-id="{{$list->id}}" href="{{route('adminkpsc.paid_category.delete', $list->id)}}" class="btn btn-sm btn-danger me-1"><i class="bi bi-trash me-1"></i>Delete</a>
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
<!-- Main content END -->

<!-- Modal -->
<div class="modal fade" id="edit_banner" tabindex="-1" role="dialog" aria-labelledby="edit_banner_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_banner_modal">Edit Free Category</h5>
          <button type="button" class="close" onclick="$('#edit_banner').modal('hide');" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="/kpsc/cms/paid_category_class/update-freecategory" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner">
                    <div class="col-md-12">
                        <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                            <!-- Image -->
                            <img src="/assets/images/element/gallery.svg" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                            <div>
                                <label style="cursor:pointer;">
                                    <span> 
                                        <input class="form-control stretched-link" type="file"  accept="image/*" id="pic1" name="my-image" accept="image/gif, image/jpeg, image/png" />
                                        <input type="hidden" name="image" class="picture" id="picture" />
                                    </span>
                                </label>
                            </div>	
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Category Name</label>
                        <input class="form-control category_name" required type="text" name="category_name" placeholder="Enter Category" >
                        <input class="form-control id" required type="hidden" name="id" >

                    </div>
                    <div class="col-lg-12">
                        <label class="form-label"> Position</label>
                        <input class="form-control position" name="position" type="text"  placeholder="Showing Position" >
                    </div>
                    <div class="col-lg-12">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" >
                            <option value="1">Show</option>
                            <option value="0">Hide</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
            
                <button type="submit" class="btn btn-success prev-btn mb-0 "><i class="bi bi-check me-1"></i> Submit</button>

            </div>
        </form>
      </div>
    </div>
  </div>
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
        
        $(".add_new").click(function(){
            
            $(".new-section").fadeToggle(1000); 
        });
    });
    var image_crop = $('.image_demo').croppie({
    viewport: {
        width: 252,
        height: 152
        // type: 'square'
    },
    boundary: {
        width: 252,
        height: 152
    }
});
/// catching up the cover_image change event and binding the image into my croppie. Then show the modal.
$('#pic1,#pic2').on('change', function() {
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
        $("#uploaded-image2,#uploaded-image").attr("src", response);
        $("#picture").val(response);
    });
    $("#pic1,#pic2").val("");
    $('#uploadimageModal').modal('hide');
});
$('.reset-banner').click(function(event) {

    var id = $(this).data('id');
    var category =  $(this).data('category');
    var image =  $(this).data('image');
    var position = $(this).data('position');
    
    $('.picture').val(image);
    $(".uploaded-image").attr("src",image);

  
    $('.category_name').val(category);
    $('.id').val(id);
    $('.position').val(position);
    $('#edit_banner').modal('show');
});
</script>
@endsection
