@extends('cms.section')
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
                    @can('Notification add')
                    <!-- Short by filter -->
                    <a href="#" class="btn btn-sm btn-primary me-1 add_new_notify"><i class="bi bi-plus me-1"></i>Add New</a>
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
                
                <div class="col-lg-12 new-banner-notify " style="display: none;">
                    @can('Notification add')
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
                    @endcan
                </div>
            </div>
            <!-- Search and select END -->
            @can('Notification list')
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
                                <a href="{{$notification_list->redirection}}" >{{$notification_list->redirection}}</a>
                            </td>
                            <td>
                                {{$notification_list->updated_at}}
                            </td>
                           

                            <!-- Table data -->
                            <td>
                                @can('Notification edit')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-id="{{$notification_list->id}}" class="btn btn-sm btn-info  reset-notify btn-circle"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('Notification Delete')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  href="{{route('adminkpsc.notifications.delete', $notification_list->id)}}" data-id="{{$notification_list->id}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash"></i></a>
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
            <form action="/admin/kpsc/notifications/update-notify" method="POST">
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
            var url = "{{route('adminkpsc.notifications.edit', 'id')}}";
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
