@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Notifications</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                    <!-- Short by filter -->
                    <a href="#" class="btn btn-sm btn-primary me-1 add_new_notify"><i class="bi bi-plus me-1"></i>Add New</a>
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
                
                <div class="col-lg-12 new-banner-notify " style="display: none;">
                    <form action="" method="POST">
                        @csrf()
                        <div class="row">
                            
                            <div class="col-md-5">
                                <label class="form-label">Title</label>
                                <input class="form-control" required name="title" type="text" placeholder="Enter Title" >
                            
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Redirection</label>
                                <input class="form-control" required name="redirection" type="text" placeholder="Enter Redirection link" >
                                
                            </div>
                            <div classs="col-md-2 ">
                                <button type="submit" class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
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
                            <th scope="col" class="border-0 rounded-start">Title</th>
                            <th scope="col" class="border-0">Redirect</th>
                            <th scope="col" class="border-0">Created date</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach ($notification as $notification_list)
                        <!-- Table item -->
                        <tr>
                            <!-- Table data -->
                            <td>
                                {{$notification_list->title}}
                            </td>

                            <!-- Table data -->
                            <td>
                                {{$notification_list->redirection}}
                            </td>
                            <td>
                                {{$notification_list->updated_at}}
                            </td>
                           

                            <!-- Table data -->
                            <td>
                                <a href="#" data-id="{{$notification_list->id}}" class="btn btn-sm btn-info me-1 reset-notify"><i class="bi bi-pencil me-1"></i>Reset</a>
                                <a href="{{ug_pg_cms('notifications/delete/'.$notification_list->id)}}" data-id="{{$notification_list->id}}" class="btn btn-sm btn-danger me-1"><i class="bi bi-trash me-1"></i>Delete</a>
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
<div class="modal fade" id="edit_notify" tabindex="-1" role="dialog" aria-labelledby="edit_notify_modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_notify_modal">Edit Notify</h5>
          <button type="button" class="close"  data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="{{ug_pg_cms('notifications/update-notify')}}" method="POST">
                <div class="modal-body">
                    
                        @csrf()
                    <div class="editnotify">
    
                    </div>
    
                </div>
                <div class="modal-footer">
                
                    <button type="submit" class="btn btn-success prev-btn mb-0 "><i class="bi bi-check me-1"></i> Submit</button>
    
                </div>
            </form>
        </div>
        
      </div>
    </div>
  </div>

@endsection

@section('scripts')

<script>

    $(document).ready(function(){
        
        $(".add_new_notify").click(function(){
            
            $(".new-banner-notify").fadeToggle(1000); 
        });
        $('.reset-notify').click(function(event) {
            var id = $(this).data('id');
            var url = "{{ug_pg_cms('notifications/id/edit')}}";
            var url = url.replace("/id/",'/'+id+'/');
            
            $.ajax({
                url: url,
                cache: false,
                success: function(html){
                    $('.editnotify').html(html);
                    $('#edit_notify').modal('show');
                }
            });
          
        });
    });
    

</script>
@endsection
