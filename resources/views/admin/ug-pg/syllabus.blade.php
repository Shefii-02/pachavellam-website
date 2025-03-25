@extends('admin.ug-pg.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Add Syllabus</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <a href="{{ug_pg_cms('syllabus-view')}}" class="btn btn-sm btn-primary me-1 add_new"><i class="bi bi-eye me-1"></i>View syllabus </a>
                 
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
                    <form action="{{ug_pg_cms('syllabus')}}" method="POST"  enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label class="form-label">Select University Name</label>
                                <select class="form-control university" required  name="university"  >
                                    <option value=""> -- Select University -- </option>
                                    @foreach ($list as $list2)
                                        <option value="{{$list2->name_slug}}">{{$list2->university_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label class="form-label">Select Level Name</label>
                                <select class="form-control level_name text-captilize" required  name="level_name"  >
                                    <option value=""> -- Select First University -- </option>
                                </select>
                               
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label class="form-label">Course With Admission</label>
                                <input class="form-control title" required  name="course"  >
                                   
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                
                                <label class="form-label"> File</label>
                                <input type="file" class="form-control" name="file">
                            </div>
                        </div>
                       <div classs="col-lg-5 text-center col-md-2 ">
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
      
      

    

       
        
    });
</script>
@endsection
