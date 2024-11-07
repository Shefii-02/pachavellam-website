@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">
                    ഇന്നറിവ്
                    </h3>    
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
                 
                    <form action="" method="POST"  enctype="multipart/form-data">
                        @csrf()
                        <div class="row">
                            
                                <div class="col-md-6 mt-3">
                                    <label class="form-label">Day Title</label>
                                    <input list="day_title" name="day_title" autocomplete="off" class="form-control" placeholder="Day Title" required>

                                      <datalist id="day_title">
                                          @foreach($day_title as $title)
                                            <option value="{{$title->day_title}}">{{$title->day_title}}</opion>
                                          @endforeach
                                      </datalist>
                                </div>
                               
                                 <div class="col-md-6 mt-3">
                                    <label class="form-label">Type</label>
                                    <select class="form-control file_type" name="type">
                                        <option>Pdf</option>
                                        <option>Link</option>
                                        <option>Voice Msg</option>
                                        <option>Text Msg</option>
                                        
                                    </select>
                                </div>
                                 <div class="col-md-6 mt-3">
                                    <label class="form-label">File Title</label>
                                    <input class="form-control" required type="text" name="title" placeholder="Enter Title" > 
                                </div>
                                 <div class="col-md-6 mt-3">
                                    <label class="form-label">Class Date</label>
                                    <input class="form-control" required type="date" name="date" >
                                </div>
                                <div class="col-md-6 mt-3 pdf_file">
                                    <label class="form-label">Pdf File</label>
                                    <input class="form-control" accept=".pdf" type="file" name="pdf_file" >
                                </div>
                            
                                <div class="col-md-6 mt-3  link_input">
                                    <label class="form-label"> Link </label>
                                    <input class="form-control " type="url" name="link" >
                                </div>
                                 <div class="col-md-6 mt-3  voice_file">
                                    <label class="form-label">Voice Message</label>
                                    <input class="form-control " accept="audio/*,.mp3"  type="file" name="voice_file" >
                                </div>
                                <div class="col-md-12 mt-3  editor_input">
                                    <div class="text-editor">
                                        <textarea name="content" id="editor" rows="10">
                                          
                                        </textarea>
                                    </div>
                                   
                                </div>
                                <div classs="col-md-2 ">
                                    <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
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
                            
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Type</th>
                            <th scope="col" class="border-0 text-center">Content</th>
                            <th scope="col" class="border-0">Class Date</th>
                            <th scope="col" class="border-0">Last Update</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                    @foreach($dailyBucket_list as $list)
                            <!-- Table item -->
                            <tr>
                               

                                <!-- Table data -->
                                <td>{{$list->title}}</td>
                                <td>{{$list->type}}</td>
                                <td>
                                    <div class="text-center">
                                    @if($list->type == 'Pdf')
                                        <a href="{{ Storage::url('daily-buckets/'.$list->content) }}" target="_new">Open</a>
                                    @elseif($list->type == 'Voice Msg')
                                    
                                        <audio controls>
                                            
                                          <source src="{{ Storage::url('daily-buckets/'.$list->content) }}" type="audio/ogg">
                                          <source src="{{ Storage::url('daily-buckets/'.$list->content) }}" type="audio/mpeg">
                                        
                                        </audio>
                                    
                                    @elseif($list->type == 'Text Msg')
                                        {!! Str::limit($list->content, 50) !!}
                                    @elseif($list->type == 'Link')
                                    
                                        <a href="{{$list->content}}" target="_new">Open</a>
                                    @else
                                        -------
                                    @endif
                                    
                                        
                                    </div>
                                    
                                </td>
                                <td>{{$list->class_date}}</td>
                                <td>{{$list->updated_at}}</td>
                               
                               

                                <!-- Table data -->
                                <td>
                                  
                                 {{--   <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-title="{{$list->title}}" data-date="{{$list->post_date}}" data-image="{{ url(Storage::url('files/'.$list->image)) }}" data-content="{{$list->description}}" data-id="{{$list->id}}" class="btn btn-sm btn-info  reset-banner btn-circle"><i class="bi bi-pencil"></i></a>
                                 --}}
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"   data-id="{{$list->id}}" href="{{route('adminkpsc.daily-buckets.delete', $list->id)}}" class="btn btn-sm btn-danger  btn-circle"><i class="bi bi-trash "></i></a>
                                 
                                </td>
                            </tr>
                        @endforeach
                      
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->
       
          
            <!-- Pagination END -->
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

<!-- Modal -->
<div class="modal fade" id="edit_banner" tabindex="-1" role="dialog" aria-labelledby="edit_banner_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_banner_modal">Edit News</h5>
          <button type="button" class="close" onclick="$('#edit_banner').modal('hide');" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="/kpsc/cms/psc-news/update-pscnews" method="POST">
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
  
@endsection

@section('scripts')
<script>

    $(document).ready(function(){
        
        $(".add_new_banner").click(function(){
            
            $(".new-banner-section").fadeToggle(1000); 
        });
    });
    
        $('.pdf_file').show();
        $('.link_input').hide();
        $('.voice_file').hide();
        $('.editor_input').hide();
       

$('body').on('change','.file_type',function(event) {
   var type= $(this).val();
   if(type == 'Text Msg'){
        $('.pdf_file').hide();
        $('.link_input').hide();
        $('.voice_file').hide();
        $('.editor_input').show();
   }
   else if(type == 'Link'){
         $('.pdf_file').hide();
        $('.link_input').show();
        $('.voice_file').hide();
        $('.editor_input').hide();
   }
   else if(type == 'Voice Msg'){
        $('.pdf_file').hide();
        $('.link_input').hide();
        $('.voice_file').show();
        $('.editor_input').hide();
   }
   else{
        $('.pdf_file').show();
        $('.link_input').hide();
        $('.voice_file').hide();
        $('.editor_input').hide();
       
       
   }
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
 
