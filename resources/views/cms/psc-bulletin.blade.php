@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Psc Bulletin</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    @can('Psc Bulletin Add')
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
                    @can('Psc Bulletin Add')
                    <form action="{{url(kpsc_cms('psc-bulletin'))}}" method="POST" enctype="multipart/form-data">
                        @csrf()
                        <div class="row">
                            
                            
                            <div class="col-md-6">
                                <label class="form-label">Month/Year</label>
                                <input class="form-control" required value="#" type="month" name="month"  >
                               
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Issue</label>
                                <select class="form-control" required   name="issue">
                                    <option value="1">Issue 1</option>
                                    <option value="2">Issue 2</option>
                                    <option value="3">Issue 3</option>
                                    <option value="4">Issue 4</option>
                                </select>
                               
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
            @can('Psc Bulletin list')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">Month-Year</th>
                            <th scope="col" class="border-0">Issue</th>
                            <th scope="col" class="border-0">File</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach ($bulletin as $list)
                            
                        <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>{{$list->month_year}}</td>
                                <td>Issue-{{$list->issue}}</td>
                                <td><a href="{{ Storage::url('bullet-in/'.$list->file_name) }}" target="_new">Open</a></td>
                            
                                <!-- Table data -->
                                <td>
                                    @can('Psc Bulletin edit')
                                    {{-- <a href="#" data-id="{{$list->id}}" class="btn btn-sm btn-info me-1 reset-banner"><i class="bi bi-pencil me-1"></i>Reset</a> --}}
                                    @endcan
                                    @can('Psc Bulletin delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  data-id="{{$list->id}}" href="{{route('adminkpsc.psc-bulletin.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
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
        <form action="/kpsc/cms/banner-slider/update-slider" method="POST">
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
</script>
@endsection
