@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">News</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    <!-- Short by filter -->
                    <button class="btn btn-sm btn-primary me-1 add_new_banner"><i class="bi bi-plus me-1"></i>Add New</button>
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
                                                <input class="form-control stretched-link" type="file"  accept="image/*" id="pic" name="my-image"  accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="col-md-12">
                                    <label class="form-label">Title</label>
                                    <input class="form-control" type="text" name="title" placeholder="Enter Title" > 
                                </div>
                                <div class="col-md-12 mt-3">
                                    <label class="form-label">Post Date</label>
                                    <input class="form-control" type="date" name="date" placeholder="Enter Title" >
                                </div>
                            </div>
                        
                            <div class="col-md-12 mt-3">
                                <div class="text-editor">
                                    <textarea name="content" id="editor" rows="10">
                                      
                                    </textarea>
                                </div>
                                <div classs="col-md-2 ">
                                    <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->

            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0 rounded-start">Image</th>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Posted Date</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach($news as $news_list)
                            <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!-- Image -->
                                        <div class="w-100px">
                                            <img src="{{ Storage::url('files/'.$news_list->image) }}" class="rounded" alt="">
                                        </div>
                                        
                                    </div>
                                </td>

                                <!-- Table data -->
                                <td>{{$news_list->title}}</td>
                                <td>{{$news_list->post_date}}</td>
                                <td>
                                    @if($news_list->status == 1)
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                               

                                <!-- Table data -->
                                <td>
                                    <a href="#" data-title="{{$news_list->title}}" data-date="{{$news_list->post_date}}" data-image="{{ url(Storage::url('files/'.$news_list->image)) }}" data-content="{{$news_list->description}}" data-id="{{$news_list->id}}" class="btn btn-sm btn-info me-1 reset-banner"><i class="bi bi-pencil me-1"></i>Reset</a>
                                    <a  data-id="{{$news_list->id}}" href="{{ug_pg_cms('news/delete/'.$news_list->id)}}" class="btn btn-sm btn-danger me-1"><i class="bi bi-trash me-1"></i>Delete</a>
                                </td>
                            </tr>
                        @endforeach
                        
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->

           
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

<!-- Modal -->
<div class="modal fade" id="edit_banner" tabindex="-1" role="dialog" aria-labelledby="edit_banner_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_banner_modal"> News</h5>
          <button type="button" class="close" onclick="$('#edit_banner').modal('hide');" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ug_pg_cms('news/update-news')}}" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner">
                    <div class="row">
                            
                        <div class="col-md-5">
                            <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                <!-- Image -->
                                <img src="/assets/images/element/gallery.svg" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                <div>
                                    <label style="cursor:pointer;">
                                        <span> 
                                            <input class="form-control stretched-link" type="file"  accept="image/*" id="pic2" name="my-image"  accept="image/gif, image/jpeg, image/png" />
                                            <input type="hidden" name="image" id="picture" class="picture" />
                                        </span>
                                    </label>
                                </div>	
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="col-md-12">
                                <label class="form-label">Title</label>
                                <input class="form-control title" type="text"  name="title" placeholder="Enter Title" > 
                                <input type="hidden" name="id" class="id">
                            </div>
                            <div class="col-md-12 mt-3">
                                <label class="form-label">Post Date</label>
                                <input class="form-control date" type="date"  name="date" placeholder="Enter Title" >
                            </div>
                        </div>
                    
                        <div class="col-md-12 mt-3">
                            <div class="text-editor">
                                <textarea name="content" class="ckeditor" id="editor1"  rows="10">
                                  
                                </textarea>
                            </div>
                           
                        </div>
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
        
        $(".add_new_banner").click(function(){
            
            $(".new-banner-section").fadeToggle(1000); 
        });
    });
    var image_crop = $('.image_demo').croppie({
    viewport: {
        width: 514,
        height: 206
        // type: 'square'
    },
    boundary: {
        width: 514,
        height: 206
    }
});
/// catching up the cover_image change event and binding the image into my croppie. Then show the modal.
$('#pic,#pic2').on('change', function() {
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
    $("#pic,#pic2").val("");
    $('#uploadimageModal').modal('hide');
});
$('.reset-banner').click(function(event) {

    var id =  $(this).data('id');
    var title =  $(this).data('title');
    var date =  $(this).data('date');
    var image =  $(this).data('image');
    var content =  $(this).data('content');

    

    $('.title').val(title);
    $('.date').val(date);

 
    $('.id').val(id);
    $('.picture').val(image);
    $(".uploaded-image").attr("src",image);
    $('#edit_banner').modal('show');
    CKEDITOR.instances['editor1'].setData(content);
  
});
    

</script>
@endsection
 
