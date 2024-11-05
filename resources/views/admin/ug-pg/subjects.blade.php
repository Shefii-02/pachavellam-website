@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Add Subjects</h3>    
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
                    <form action="{{ug_pg_cms('subjects')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-5 col-md-5 mb-3">
                                <label class="form-label">Select University Name</label>
                                <select class="form-control university" required  name="university"  >
                                    <option value=""> -- Select University -- </option>
                                    @foreach ($list as $list2)
                                        <option value="{{$list2->name_slug}}">{{$list2->university_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-5 col-md-5 mb-3">
                                <label class="form-label">Select Level Name</label>
                                <select class="form-control level_name" required  name="level_name"  >
                                    <option value=""> -- Select First University -- </option>
                                </select>
                               
                            </div>
                            <div class="col-lg-5 col-md-5 mb-3">
                                <label class="form-label">Select Course</label>
                                <select class="form-control course" required  name="course"  >
                                    <option value=""> -- Select First Level -- </option>
                                </select>
                            </div>
                            
                            <div class="col-lg-5 col-md-5 mb-3">
                                <label class="form-label">Select Semester</label>
                                <select class="form-control semester" required  name="semester"  >
                                    <option value=""> -- Select First Course -- </option>
                                </select>
                            </div>
                            <div class="col-lg-5 col-md-5 ">
                                <label class="form-label"> Subject Name</label>
                                <input type="text" class="form-control" required  name="subjects[]">
                            </div>
                             <div classs="col-lg-5 col-md-2 ">
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
                            <th scope="col" class="border-0">Semester</th>
                            <th scope="col" class="border-0">Subject</th>
                            <th scope="col" class="border-0">Position</th>
                            <th scope="col" class="border-0">Status</th>
                                @can('semester edit')
                            <th scope="col" class="border-0 rounded-end">Action</th>
                            @endcan
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                       @foreach ($subject_list as $list3)
                            
                        <!-- Table item -->
                            <tr>
                                
                                <!-- Table data -->
                                <td>{{$list3->id}}</td>
                                <td>{{remove_slug($list3->university_name)}}</td>
                                <td>{{remove_slug($list3->level_name)}}</td>
                                <td>{{remove_slug($list3->course_name)}}</td>
                                <td>{{remove_slug($list3->semester_name)}}</td>
                                <td>{{ucwords($list3->subject_name)}}</td>
                                <td>{{$list3->position}}</td>
                                <td>
                                    @if($list3->status == 1)
                                        <span class="badge rounded-pill  bg-orange text-white">Show</span>
                                    @else
                                        
                                    <span class="badge rounded-pill  bg-secondary text-white">Hide</span>
                                    @endif
                                </td>
                            
                                <!-- Table data -->
                                <td>
                                        @can('semester edit')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" href="#" data-subject_name="{{$list3->subject_name}}" data-position="{{$list3->position}}"  data-university_name="{{$list3->university_name}}"   data-level_name="{{$list3->level_name}}"  data-course_name="{{$list3->course_name}}"  data-semester_name="{{$list3->semester_name}}"  data-id="{{$list3->id}}" class="btn btn-sm btn-info btn-circle reset-banner"><i class="bi bi-pencil "></i></a>
                                         @endcan
                                        @can('semester edit')
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" data-id="{{$list3->id}}" href="{{ug_pg_cms('subjects/delete/'.$list3->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
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
          <h5 class="modal-title" id="edit_banner_modal">Edit Subject</h5>
          <a href="#"  class="close"  data-bs-dismiss="modal" aria-label="Close">
            <span class="fa fa-times" aria-hidden="true"></span>
          </a>
        </div>
        <form action="{{ug_pg_cms('subjects/updating')}}" method="POST">
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
                        <select class="form-control level_name" required  name="level_name"  >
                            <option value=""> -- Select First University -- </option>
                        </select>
                       
                    </div>
                    <div class="col-lg-5 col-md-5">
                        <label class="form-label">Select Course</label>
                        <select class="form-control course" required  name="course"  >
                            <option value=""> -- Select First Level -- </option>
                        </select>
                    </div>
                    
                    <div class="col-lg-5 col-md-5">
                        <label class="form-label">Select Semester</label>
                        <select class="form-control semester" required  name="semester"  >
                            <option value=""> -- Select First Course -- </option>
                            
                        </select>
                    </div>
                    <div class="col-lg-5 col-md-5 ">
                        <label class="form-label"> Subject Name</label>
                        <input type="text" class="form-control subjects" required  name="subjects">
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label"> Position</label>
                        <input class="form-control position" name="position" type="text"  placeholder="Showing Position" >
                        <input type="hidden" name="id" class="id">
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
            var id = $(this).data('id');
            var university= $(this).data('university_name')
            var level_name = $(this).data('level_name')
            var course_name = $(this).data('course_name')
            var semester_name = $(this).data('semester_name')
            var position = $(this).data('position');
            var subject_name = $(this).data('subject_name');

            $('.university').val(university);

            change_level_option(university,level_name);
            change_course_option(university,level_name,course_name);
            change_semester_option(university,level_name,course_name,semester_name);
            $('.id').val(id);
            $('.position').val(position);
            $('.subjects').val(subject_name);
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
                    $('.level_name').html(data)
                }
            });
        });
        $('.level_name').change(function(event) {
            var level_name = $(this).val();
            var university = $('.university').val();
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_course')}}",
                data: {'university' : university,'level_name' : level_name},
                cache: false,
                success: function(data){
                    $('.course').html(data)
                }
            });
        });
      
        $('.course').change(function(event) {
            var course_name = $(this).val();
            var university = $('.university').val();
            var level_name = $('.level_name').val();
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_semesters')}}",
                data: {'university' : university,'level_name' : level_name,'course_name' : course_name},
                cache: false,
                success: function(data){
                    $('.semester').html(data)
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
                    $('.level_name').html(data)
                    $('.level_name').val(level_name)
                }
            });
        }

        function change_course_option(option,level_name,course_name){
          
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_course')}}",
                data: {'university' : option,'level_name' : level_name},
                cache: false,
                success: function(data){
                    $('.course').html(data)
                    $('.course').val(course_name)
                }
            });
        }

       

        function change_semester_option(option,level_name,course_name,semester_name){
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_semesters')}}",
                data: {'university' : option,'level_name' : level_name,'course_name' : course_name},
                cache: false,
                success: function(data){
                    $('.semester').html(data)
                    $('.semester').val(semester_name)
                }
            });
        }
        
    });
</script>
@endsection