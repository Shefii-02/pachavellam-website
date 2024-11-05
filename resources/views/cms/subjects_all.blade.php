@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Subjects </h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    {{-- @can('Psc Subjects Add')--}}
                    <!-- Short by filter -->
                    <button class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-plus me-1"></i>Add New</button>
                    {{-- @endcan--}}
                    {{-- @can('Psc Subjects Sub  Add')--}}
                    <a class="btn btn-sm btn-primary me-1" href="{{route('adminkpsc.sub-subjects.index')}}" ><i class="bi bi-plus me-1"></i>Add|View-Sub Subject</a>
                    {{-- @endcan--}}
                    
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
                    {{-- @can('Psc Subjects Add') --}}
                    <form action="{{url(kpsc_cms('subjects/create'))}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                    <!-- Image -->
                                    <img src="/assets/images/element/gallery.svg" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                    <div>
                                        <label style="cursor:pointer;">
                                            <span> 
                                                <input class="form-control stretched-link" type="file" required  accept="image/*" id="pic1" name="my-image" accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="form-label">Subject Name</label>
                                    <input class="form-control" required type="text" name="subject_name" placeholder="Subject Name" >
                                </div>
                                <div class="form-group mb-2">
                                    <label class="form-label">BgColor</label>
                                    <input class="form-control" required type="color" name="bg_color" >
                                </div>
                                <div class="form-group mb-2">
                                    <label class="form-label">Text Color</label>
                                    <input class="form-control" required type="color" value="#ffffff" name="text_color"  >
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Subject Activies</label>
                                    <div class="row">
                                         @foreach($activities as $item)
                                            <div class="col-6 mb-2">
                                                <div class="form-check-inline">
                                                  <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" name="activity[]" value="{{$item->id}}"> {{$item->activity}}
                                                  </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                   
                                </div>
                                
                            </div>
                            <div classs="col-md-12 text-center">
                                    <button type="submit" class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                            </div>
                        </div>
                    
                    </form>
                    {{-- @endcan --}}
                </div>
            </div>
            <!-- Search and select END -->
           {{-- @can('Psc Subject list') --}}
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
                        @foreach ($all_subjects as $list)
                            
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
                                <td>{{$list->subject_title}}</td>
                                <td>{{$list->position}}</td>
                                <td>
                                    @if($list->status == 'show')
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                            
                                <!-- Table data -->
                                <td>
                                   {{--  @can('Psc Subject edit') --}}
                                    <a href="{{route('adminkpsc.subjects.edit', $list->id)}}" class="btn btn-sm btn-info me-1 reset-banner"><i class="bi bi-pencil me-1"></i>Reset</a>
                                    {{-- @endcan --}}
                                    {{-- @can('Psc Subject delete') --}}
                                    <a   href="{{route('adminkpsc.subjects.delete', $list->id)}}" class="btn btn-sm btn-danger me-1"><i class="bi bi-trash me-1"></i>Delete</a>
                                   {{-- @endcan --}}
                                </td>
                            </tr>
                        
                    @endforeach 
                        
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->
            {{--    @endcan--}}
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

<!-- Modal -->
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
    $('#uploadimageModal').modal('hide');
});
</script>
@endsection
