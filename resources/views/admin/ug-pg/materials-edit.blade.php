@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Edit Study Material</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <a href="{{ug_pg_cms('materials')}}" class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-plus me-1"></i>Add study material </a>
                 
                    <a href="{{ug_pg_cms('materials-view')}}" class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-eye me-1"></i>View study material </a>
                 
                 
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
                
                <div class="col-lg-12 ">
                    <form action="{{ug_pg_cms('materials/updating')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Select University Name</label>
                                <select class="form-control university" required  name="university"  >
                                    <option value=""> -- Select University -- </option>
                                    @foreach ($list as $list2)
                                        <option @if($single_qstn->university_name == $list2->name_slug) selected @endif value="{{$list2->name_slug}}">{{$list2->university_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Select Level Name</label>
                                <select class="form-control level_name" required  name="level_name"  >
                                    <option value=""> -- Select Level Name -- </option>
                                        @foreach ($level_list as $list2)
                                            <option @if($single_qstn->level_name == $list2->name_slug) selected @endif value="{{$list2->name_slug}}">{{$list2->level_name}}</option>
                                        @endforeach
                                </select>
                               
                            </div>
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Select Course</label>
                                <select class="form-control course" required  name="course"  >
                                    <option value=""> -- Select Course Name -- </option>
                                        @foreach ($course_list as $list2)
                                            <option @if($single_qstn->course_name == $list2->name_slug) selected @endif value="{{$list2->name_slug}}">{{$list2->course_name}}</option>
                                        @endforeach
                                </select>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Select Semester</label>
                                <select class="form-control semester" required  name="semester"  >
                                    <option value=""> -- Select Semester -- </option>
                                    @foreach ($semester_list as $list2)
                                        <option @if($single_qstn->semester_name == $list2->name_slug) selected @endif value="{{$list2->name_slug}}">{{$list2->semester_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Select Subject</label>
                                <select class="form-control subjects" required  name="subject"  >
                                    <option value=""> -- Select Subject -- </option>
                                    @foreach ($subject_list as $list2)
                                        <option @if($single_qstn->subject_name == $list2->name_slug) selected @endif value="{{$list2->name_slug}}">{{$list2->subject_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            
                            <div class="col-lg-4 col-md-4 mb-3">
                                
                                <label class="form-label">Position</label>
                                <input type="text" class="form-control" value="{{$single_qstn->position}}" name="position">
                            </div>
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" required  name="status"  >
                                  <option value="1" @if($single_qstn->status == 1) selected @endif>Show</option>
                                  <option value="0" @if($single_qstn->status == 0) selected @endif>Hidden</option>
                                </select>
                            </div>
                           
                            <div class="col-lg-4 col-md-4 mb-3">
                                <label class="form-label">Select Type</label>
                                <select class="form-control type" required  name="type"  >
                                    <option>Text Type</option>
                                    <option>Pdf Link</option>
                                    <option>Pdf File</option>
                                    <option>Youtube Link</option>
                                    <option>Other Page Link</option>
                                </select>
                            </div>
                            <div class="col-lg-8 col-md-8 mb-3">
                                <label class="form-label">Title</label>
                                <input class="form-control title" value="{{$single_qstn->title}}" required  name="title"  >
                                <input type="hidden" name="id" value="{{$single_qstn->id}}" >
                                <input type="hidden" class="form-control year"  value="{{$single_qstn->year}}"  name="year"  >
                            </div>
                            <div class="col-lg-12 col-md-12 mb-3 pdf_file">
                                <label class="form-label">Pdf File</label>
                                <input class="form-control" accept=".pdf" type="file"  name="file"  >
                            </div>
                             <div class="col-lg-12 col-md-12 mb-3 link">
                                <label class="form-label">Link</label>
                                <input class="form-control " type="url" value="{{$single_qstn->content}}"  name="link"  >
                            </div>       
                            <div class="col-lg-12 col-md-12 mb-3 material_content">
                                
                                <label class="form-label">Material Content</label>
                                <textarea class="form-control ckeditor" name="material_content" id="editor">
                                    {{$single_qstn->content}}  
                                </textarea>
                            </div>
                            
                        </div>
                       <div classs="col-lg-5 col-md-2 ">
                            <button type="submit" class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                        </div>
                    </form>

                       
                    
                    
                </div>
            </div>
            <!-- Search and select END -->


        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->


@endsection

@section('scripts')

<script>

    $(document).ready(function(){
    

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

      

        $('.semester').change(function(event) {
            var semester_name  = $(this).val();
            var university     = $('.university').val();
            var course_name    = $('.course').val();
            $.ajax({
                type: "GET",
                url: "{{ug_pg_cms('get_subjects')}}",
                data: {'university' : university,'course_name' : course_name,'semester_name' : semester_name},
                cache: false,
                success: function(data){
                    $('.subjects').html(data)
                }
            });
        });
        
            if('{{$single_qstn->type}}' == 'Text Type'){
                $('.material_content').show();
                $('.pdf_file').hide();
                $('.link').hide();
           }
           else if(('{{$single_qstn->type}}' == 'Pdf Link') || ('{{$single_qstn->type}}' == 'Youtube Link') || ('{{$single_qstn->type}}' == 'Other Page Link')){
               
                $('.material_content').hide();
                $('.pdf_file').hide();
                $('.link').show();
           }
           else if('{{$single_qstn->type}}' == 'Pdf File'){
                $('.material_content').hide();
                $('.pdf_file').show();
                $('.link').hide();
           }
        
        $('.type').val('{{$single_qstn->type}}').trigger('change');
        
        $('body').on('change','.type',function(event) {
           var type= $(this).val();

           if(type == 'Text Type'){
                $('.material_content').show();
                $('.pdf_file').hide();
                $('.link').hide();
           }
           else if((type == 'Pdf Link') || (type == 'Youtube Link') || (type == 'Other Page Link')){
               
                $('.material_content').hide();
                $('.pdf_file').hide();
                $('.link').show();
           }
           else if(type == 'Pdf File'){
                $('.material_content').hide();
                $('.pdf_file').show();
                $('.link').hide();
           }
           else{
               alert('please select one type')
           }

        });
    });
</script>
@endsection
