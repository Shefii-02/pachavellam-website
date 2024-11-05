@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Admin  Staff</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <button class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-plus me-1"></i>Create New</button>
                 
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
                    <form action="{{admin_general('users')}}" method="POST">
                        @csrf
                        <div class="row">
                            
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Full Name</label>
                                <input class="form-control" required type="text"  name="full_name" placeholder="Enter Full Name" >
                               
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Email Id</label>
                                <input class="form-control" required type="text" name="email_id" placeholder="Enter Email Id" >
                               
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Mobile No</label>
                                <input class="form-control" required type="text" name="mobile_no" placeholder="Enter Mobile Number" >
                               
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Password</label>
                                <input class="form-control" required type="text" name="Password" placeholder="******" >
                               
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Job Title</label>
                                <select class="form-control" required type="text" name="role" >
                                    <option>Office Staff</option>
                                    <option>Data Entry</option>
                                    <option>Developer</option>
                                </select>
                               
                            </div>
                             <div class="col-lg-12  text-center col-md-2 ">
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
                            <th scope="col" class="border-0">#</th>
                            <th scope="col" class="border-0">Name</th>
                            <th scope="col" class="border-0">Email</th>
                            <th scope="col" class="border-0">Mobile No</th>
                            <th scope="col" class="border-0">Designation</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                 @foreach ($list as $list)
                            
                        <!-- Table item -->
                            <tr>
                               
                                <td>{{$list->id}}</td>
                                <!-- Table data -->
                                <td>{{ucwords($list->name)}}</td>
                                <td>{{ucwords($list->email)}}</td>
                                <td>{{ucwords($list->mobile)}}</td>
                                <td>{{ucwords($list->role_name)}}</td>
                                <td>
                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" data-name="{{$list->name}}" data-email="{{$list->email}}"  data-mobile="{{$list->mobile}}" data-role_name="{{$list->role_name}}"  data-id="{{$list->id}}" class="btn btn-sm btn-info text-center btn-circle  reset-banner"><i class="bi bi-pencil "></i></a>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" data-id="{{$list->id}}" href="{{admin_general('users/delete/'. $list->id)}}" class="btn btn-sm text-center btn-circle btn-danger "><i class="bi bi-trash "></i></a>
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
    <div class="modal-dialog " role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit_banner_modal">Edit Unversity</h5>
          <a href="#"  class="close"  data-bs-dismiss="modal" aria-label="Close">
            <span class="fa fa-times" aria-hidden="true"></span>
          </a>
        </div>
        <form action="{{admin_general('users/updating')}}" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner">
                    
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Full Name</label>
                        <input class="form-control full_name" required type="text"  name="full_name" placeholder="Enter Full Name" >
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Email Id</label>
                        <input class="form-control email_id" required type="text" name="email_id" placeholder="Enter Email Id" >
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Mobile No</label>
                        <input class="form-control mobile_no" required type="text" name="mobile_no" placeholder="Enter Mobile Number" >
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="text" name="Password" placeholder="******" >
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Job Title</label>
                        <select class="form-control job_title" required type="text" name="role" >
                            <option>Office Staff</option>
                            <option>Data Entry</option>
                            <option>Developer</option>
                        </select>
                       <input type="hidden" class="id" name="id">
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
        $(".add_new").click(function(){         
            $(".new-section").fadeToggle(1000); 
        });
        $('.reset-banner').click(function(event) {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var email = $(this).data('email');
            var mobile = $(this).data('mobile');
            var role_name = $(this).data('role_name');
            $('.full_name').val(name);
            $('.id').val(id);
            $('.email_id').val(email);
            $('.mobile_no').val(mobile);
            $('.job_title').val(role_name);
            

            $('#edit_banner').modal('show');
        });
    });
</script>
@endsection
