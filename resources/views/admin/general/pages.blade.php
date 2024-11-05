@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Pages</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    @can('Pages add')
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
                    @can('Pages add')
                    <form action="{{admin_general('pages')}}" method="POST">
                        @csrf()
                        <div class="row">
                            
                            <div class="col-md-12">
                                <div class="row">
                                        
                                    <div class="col-md-6">
                                        <label class="form-label">Title</label>
                                        <input class="form-control" type="text" name="title" placeholder="Enter Title" > 
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Update Date</label>
                                        <input class="form-control" type="date" value="{{date('Y-m-d')}}" name="date"  >
                                    </div>
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
                @endcan
                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->
            @can('Pages list')
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Posted Date</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach($pages as $list)
                            <!-- Table item -->
                            <tr>
                               
                                <!-- Table data -->
                                <td>{{$list->title}}</td>
                                
                                <td>{{$list->position}}</td>
                                <td>{{$list->post_date}}</td>
                                <td>
                                    @if($list->status == 1)
                                        <span class="badge bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                               

                                <!-- Table data -->
                                <td>
                                    @can('Pages Edit')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-title="{{$list->title}}" data-date="{{$list->post_date}}" data-position="{{$list->position}}" data-content="{{$list->content}}" data-id="{{$list->id}}" class="btn btn-sm btn-info  reset-banner btn-circle"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('Pages Delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"   data-id="{{$list->id}}" href="{{admin_general('pages-delete/'.$list->id)}}" class="btn btn-sm btn-danger  btn-circle"><i class="bi bi-trash "></i></a>
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
          <h5 class="modal-title" id="edit_banner_modal">Edit News</h5>
          <button type="button" class="close"  data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{admin_general('update-pages')}}" method="POST">
            <div class="modal-body">
              
                    @csrf()
                <div class="editbanner">
                    <div class="row">
                         
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input class="form-control title" type="text"  name="title" placeholder="Enter Title" > 
                                    <input type="hidden" name="id" class="id">
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label">Update Date</label>
                                    <input class="form-control date" type="date"  name="date"  >
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label">Position</label>
                                    <input class="form-control position" type="text"  name="position" >
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label">Status</label>
                                    <select class="form-control status"  name="status" >
                                        <option value="1">Show</option>
                                        <option value="0" >Hide</option>
                                    </select>
                                </div>
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


$('.reset-banner').click(function(event) {

    var id =  $(this).data('id');
    var title =  $(this).data('title');
    var date =  $(this).data('date');
    var position =  $(this).data('position');
    var content =  $(this).data('content');
    
    $('.position').val(position);
    $('.title').val(title);
    $('.date').val(date);
    $('.id').val(id);
    
    $('#edit_banner').modal('show');
    CKEDITOR.instances['editor1'].setData(content);
  
});
    

</script>
@endsection
 