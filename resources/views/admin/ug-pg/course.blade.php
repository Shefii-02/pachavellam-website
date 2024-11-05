@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Add  Course</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <button class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-plus me-1"></i>Add New</button>
                 
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
                    <form action="{{ug_pg_cms('course')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-md-5 mb-2">
                                <label class="form-label">Select University Name</label>
                                <select class="form-control university" required  name="university"  >
                                    <option value=""> -- Select University -- </option>
                                    @foreach ($list as $list2)
                                        <option value="{{$list2->name_slug}}">{{$list2->university_name}}</option>
                                    @endforeach
                                </select>
                               
                            </div>
                            <div class="col-lg-4 col-md-5 mb-2">
                                <label class="form-label">Select Level Name</label>
                                <select class="form-control lavel_name" required  name="level_name"  >
                                    <option value=""> -- Select First University -- </option>
                                </select>
                               
                            </div>
                            <div class="col-lg-4 col-md-5 mb-2">
                                <label class="form-label">Course Name</label>
                                <input class="form-control" required type="text" name="course_name" placeholder="Enter Name" >
                               
                            </div>
                             <div classs="col-lg-5 col-md-2 align-self-end">
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
                            <th scope="col" class="border-0">University Name</th>
                            <th scope="col" class="border-0">Level Name</th>
                            <th scope="col" class="border-0">Course Name</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                            @can('course edit')
                            <th scope="col" class="border-0 rounded-end">Action</th>
                            @endcan
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                     @foreach ($course_list as $list2)
                            
                        <!-- Table item -->
                            <tr>
                                <!-- Table data -->
                                <td>{{$list2->id}}</td>
                                <td>{{remove_slug($list2->university_name)}}</td>
                                <td>{{remove_slug($list2->level_name)}}</td>
                                <td>{{ucwords($list2->course_name)}}</td>
                                <td>{{$list2->position}}</td>
                                <td>
                                    @if($list2->status == 1)
                                        <span class="badge rounded-pill bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge rounded-pill bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                            
                                <!-- Table data -->
                                <td>
                                       @can('course edit')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" href="#" data-position="{{$list2->position}}" data-university="{{$list2->university_name}}" data-lavel_name="{{$list2->level_name}}"  data-course_name="{{$list2->course_name}}"    data-id="{{$list2->id}}" class="btn btn-sm btn-info  btn-circle reset-banner"><i class="bi bi-pencil "></i></a>
                                    @endcan
                                       @can('course delete')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" data-id="{{$list2->id}}" href="{{ug_pg_cms('course/delete/'.$list2->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
                                    @endcan
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
          <h5 class="modal-title" id="edit_banner_modal">Edit Course</h5>
          <a href="#"  class="close"  data-bs-dismiss="modal" aria-label="Close">
            <span class="fa fa-times" aria-hidden="true"></span>
          </a>
        </div>
        <form action="{{ug_pg_cms('course/updating')}}" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner row">
                    <div class="col-lg-5 col-md-5">
                        <label class="form-label">Select University Name</label>
                        <select class="form-control university" required  name="university"  >
                            <option value=""> -- Select University -- </option>
                            @foreach ($list as $list2)
                                <option value="{{$list2->name_slug}}">{{$list2->university_name}}</option>
                            @endforeach
                        </select>
                       
                    </div>
                    <div class="col-lg-5 col-md-5">
                        <label class="form-label">Select Level Name</label>
                        <select class="form-control lavel_name" required  name="level_name"  >
                            <option value=""> -- Select First University -- </option>
                        </select>
                       
                    </div>
                    <div class="col-lg-5 col-md-5">
                        <label class="form-label">Course Name</label>
                        <input class="form-control course_name" required type="text" name="course_name" placeholder="Enter Name" >
                       <input type="hidden" class="id" name="id">
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label"> Position</label>
                        <input class="form-control position" name="position" type="text"  placeholder="Showing Position" >
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" >
                            <option value="1">Show</option>
                            <option value="0">Hide</option>
                        </select>
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
            var id          = $(this).data('id');
            var university  =  $(this).data('university');
            var lavel_name  = $(this).data('lavel_name');
            var course_name = $(this).data('course_name');
            var position    = $(this).data('position');
            

           
            $('.university').val(university);
            change_level_option(university,lavel_name);
            // $('.lavel_name option[value='+lavel_name+']').attr('selected','selected');
            $('.id').val(id);
            $('.position').val(position);
            $('.course_name').val(course_name);
            
            $('#edit_banner').modal('show');
        });

        $('.university').change(function(event) {
            var university = $(this).val();
       
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_level')}}",
                data: {'university' : university},
                cache: false,
                success: function(data){
                    $('.lavel_name').html(data)
                }
            });
        });
        function change_level_option(option,level_name){
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_level')}}",
                data: {'university' : option},
                cache: false,
                success: function(data){
                    $('.lavel_name').html(data)
                    $('.lavel_name').val(level_name);

                }
            });
        }
        

    });
</script>
@endsection
