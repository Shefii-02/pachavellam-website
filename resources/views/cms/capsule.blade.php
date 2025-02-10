@extends('cms.section')


<style>
    .content_feed img{
        max-width:100px;
        max-height:100px;
    }
</style>
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Capsule</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    @can('Capsule add')
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
                    @can('Capsule add')
                    <form action="{{url(kpsc_cms('capsule'))}}" method="POST">
                        @csrf
                        <div class="row">
                            
                            <div class="col-md-12">
                                <div class="col-md-6 mb-3">
                                    <label>Type</label>
                                    <select class="form-control" id="type" name="type">
                                        <option>Image</option>
                                        <option>Content</option>
                                    </select>
                                </div>
                                <div  id="image" class="col-md-6 p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                    <!-- Image -->
                                    <img src="/assets/images/element/gallery.svg" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                    <div>
                                        <span class="text-danger">Image size : 570*570</span><br>
                                        <label style="cursor:pointer;">
                                            <span> 
                                                <input class="form-control stretched-link" type="file"  accept="image/*" id="pic1" name="my-image" accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                                <div class="col-lg-12" id="content" style="display:none">
                                    <label>Content</label>
                                   <textarea class="form-control" name="content" id="editor"></textarea>
                                </div>
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
            <div class="table-responsive border-0">
                @can('Capsule list')
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th style="width:200px" class="border-0 rounded-start">Image/Content</th>
                            <th  class="border-0">Type</th>
                            <th  class="border-0">Like</th>
                            <th  class="border-0">Comment</th>
                            <th  class="border-0">Created at</th>
                            <th  class="border-0">Status</th>
                            <th  class="border-0">Added By</th>
                            <th  class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach ($capsule_list->sortByDesc('id') as $list)
                            
                        <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>
                                    @if($list->type == 'Image')
                                    <div class="d-flex align-items-center">
                                        <!-- Image -->
                                        <div class="w-100px">
                                            <img src="{{ Storage::url('files/'.$list->image) }}" class="rounded" alt="">
                                        </div>
                                    </div>
                                    @else
                                    <div class="content_feed">
                                        {!! Str::limit($list->content, 300) !!}
                                    </div>
                                     
                                    @endif
                                </td>
                                <td>
                                     {{$list->type }}
                                </td>
                                <!-- Table data -->
                                <td>{{count(App\Models\CapsuleLike::where('cap_id',$list->id)->get())}}</td>
                                <td>{{count(App\Models\CapsuleComment::where('cap_id',$list->id)->get())}}</td>
                                <td>
                                    {{ date('d-m-Y',strtotime($list->updated_at)) }}
                                </td>
                                <td>
                                    @if($list->status == 1)
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                                <td class="text-capitalize">
                                    {{App\Models\User::where('id',$list->author_id)->pluck('name')->first()}}
                                </td>

                                <!-- Table data -->
                                <td>
                                    @can('Capsule edit')
                                    <!--<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  data-id="{{$list->id}}" class="btn btn-sm btn-info  reset-banner btn-circle"><i class="bi bi-pencil "></i></a>-->
                                    @endcan
                                    @can('Capsule delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  data-id="{{$list->id}}" href="{{route('adminkpsc.capsule.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
                                    @endcan
                                </td>
                            </tr>
                        
                        @endforeach
                        
                    </tbody>
                    <!-- Table body END -->
                </table>
                @endcan
            </div>
            
            {!! $capsule_list->links() !!}
           
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

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
        <form action="/kpsc/banner-slider/update-slider" method="POST">
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
  <div class="modal uploadimageModal" tabindex="-1" role="dialog" id="uploadimageModal">
    <div class="modal-dialog" role="document" style="min-width: 700px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Image</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
        width: 570,
        height: 570,
        type: 'square'
    },
    boundary: {
        width: 570,
        height: 570
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

$('#type').change(function(event) {
   var typ = $(this).val();
   if(typ == 'Content'){
       $('#image').hide();
       $('#content').show();
   }
   else{
       
       $('#content').hide();
       $('#image').show();
   }
   
});


</script>
@endsection
