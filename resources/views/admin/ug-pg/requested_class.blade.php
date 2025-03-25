@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Class Requested</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3">
                   
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
                
                <!-- Course list table START -->
                <div class="table-responsive border-0">
                    <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                        <!-- Table head -->
                        <thead>
                            <tr>
                                <th scope="col" class="border-0">#</th>
                                <th scope="col" class="border-0"> Name</th>
                                <th scope="col" class="border-0">College</th>
                                <th scope="col" class="border-0">Course</th>
                                <th scope="col" class="border-0">Subject</th>
                                <th scope="col" class="border-0">Type</th>
                                <th scope="col" class="border-0">Requested at</th>
                                <th scope="col" class="border-0">Status</th>
                                <th scope="col" class="border-0 rounded-end">Action</th>
                            </tr>
                        </thead>
    
                        <!-- Table body START -->
                        <tbody>
                            @php 
                                $i = 0;
                            @endphp
                           @foreach ($requested_class as $list3)
                                
                            <!-- Table item -->
                                <tr>
                                 
                                    <!-- Table data -->
                                    <td>{{$i=$i+1}}</td>
                                    <td>
                                        {{$list3->name}}<br>
                                        <a href="tel://{{$list3->mobile}}">{{$list3->mobile}}</a>
                                    </td>
                                    <td>{{remove_slug($list3->college)}}</td>
                                    <td>{{remove_slug($list3->course)}}</td>
                                    <td>{{remove_slug($list3->subject)}}</td>
                                    <td>{{remove_slug($list3->type)}}</td>
                                    <td>{{$list3->created_at}}</td>
                                    <td>
                                        @if($list3->status == 1)
                                            <span class="badge rounded-pill  bg-success text-white">Confirm</span>
                                        @else
                                            
                                        <span class="badge rounded-pill  bg-secondary text-white">Pending</span>
                                        @endif
                                    </td>
                                
                                    <!-- Table data -->
                                    <td>
                                        
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" href="{{ug_pg_cms('class-request/edit/'.$list3->id)}}"   data-id="{{$list3->id}}" class="btn btn-sm btn-info btn-circle reset-request"><i class="bi bi-pencil "></i></a>
                                          
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" data-id="{{$list3->id}}" href="{{ug_pg_cms('class-request/delete/'.$list3->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>

                                    </td>
                                </tr>
                            
                            @endforeach
                            
                        </tbody>
                        <!-- Table body END -->
                    </table>
                </div>
                <!-- Course list table END -->
            </div>
        </div>
    </div>
    
<!-- Modal -->
<div class="modal fade" id="editeModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Update Information</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body edit_body">
    
        
      </div>
    </div>
  </div>
</div>
        @endsection
        
        
@section('scripts')
    <script>
        $('body').on('click', '.reset-request', function(e) {
            e.preventDefault()
            var id = $(this).data('id');
            var url = $(this).attr('href');
         
            $.ajax({
              url: url,
              cache: false,
              data: {id : id},
              success: function(data){
                $(".edit_body").html(data);
                $('#editeModal').modal('show');
              }
            });

        });
    </script>

@endsection
        