@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Psc Free Class</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    @can('Psc Free Class Category list')
                    <!-- Short by filter -->
                    <!--<a class="btn btn-sm btn-info me-1" href="free_category_class" ><i class="bi bi-plus me-1"></i>Add|View-Category</a> -->
                    @endcan
                    @can('Psc Free Class Sub Category list')
                    <!--<a class="btn btn-sm btn-info me-1" href="free_subcategory_class" ><i class="bi bi-plus me-1"></i>Add|View-Sub Category</a>-->
                    @endcan
                    <br>
                    @can('Psc Free Class Add')
                    <button class="btn btn-sm btn-primary me-1 " data-bs-toggle="modal" data-bs-target="#add_modal" ><i class="bi bi-plus me-1"></i>Add Class</button>
                    @endcan
                    @can('Psc Free Class add csv')
                    <!--<button class="btn btn-sm btn-primary me-1 " data-bs-toggle="modal" data-bs-target="#csv_modal" ><i class="bi bi-plus me-1"></i>Add Csv</button>-->
                    @endcan
                    
                    <span class="js-choice "></span>
                </div>
            </div>
            
        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body row">
            @can('Psc Free Class list')
            <form method="GET" action="free-class-list">
                <div class="row">
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Subject</label>
                        <select class="form-control category_name1" required  name="category_name" >
                            <option value=""></option>
                            @foreach($subjects as $category)
                                <option value="{{$category->id}}" @if(app('request')->input('category_name') == $category->id) selected @endif>{{$category->subject_title}}</option>
                            @endforeach
                        </select>
                    </div>
                   
                    <div class="col-md-4 mt-2">
                        <button type="submit" class="btn btn-success prev-btn mb-0 mt-4"><i class="bi bi-search me-1"></i> Submit</button>
                    </div>
                </div>
                
            </form>
            @endcan
            @can('Psc Free Class list')
                @if(isset($class_list))
                    <!-- Course list table START -->
                    <div class="table-responsive border-0 mt-5">
                        <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                            <!-- Table head -->
                            <thead>
                                <tr>
                                    <th scope="col" class="border-0 rounded-start">Subject</th>
                                    <th scope="col" class="border-0">Link</th>
                                    <th scope="col" class="border-0">Position</th>
                                    <th scope="col" class="border-0">Status</th>
                                    <th scope="col" class="border-0 rounded-end">Action</th>
                                </tr>
                            </thead>
    
                            <!-- Table body START -->
                            <tbody>
                                @if($class_list->count() > 0)
                                @foreach ($class_list as $list)
                                    
                                <!-- Table item -->
                                    <tr>
                                        <!-- Table data -->
                                        <td>
                                           {{$list->subject_title}}
                                        </td>
                                        <!-- Table data -->
                               
                                        <td><a href="{{$list->link}}" target="_new" title="{{$list->subject_title}}">open</a></td>
                                        <td>{{$list->position}}</td>
                                        <td>
                                            @if($list->status == 1)
                                                <span class="badge bg-orange text-white">Show</span>
                                            @else
                                                
                                            <span class="badge bg-secondary text-white">Hide</span>
                                            @endif
                                        </td>
                                    
    
                                        <!-- Table data -->
                                        <td>
                                            @can('Psc Free Class edit')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-position="{{$list->position}}" data-link="{{$list->link}}" data-title="{{$list->title}}" data-id="{{$list->id}}" data-category="{{$list->category_id}}" data-subcategory="{{$list->subcategory_id}}" class="btn btn-sm btn-info me-1 reset-banner btn-circle"><i class="bi bi-pencil me-1"></i></a>
                                            @endcan
                                            @can('Psc Free Class delete')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  data-id="{{$list->id}}" href="{{route('adminkpsc.free-class.delete', $list->id)}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">
                                        NO Data Found
                                            
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            <!-- Table body END -->
                        </table>
                    </div>
                    
                    <!-- Course list table END -->
                @endif
            @endcan

        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

  <!-- Modal -->
 <div class="modal fade" id="add_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Free Class Adding Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{url(kpsc_cms('free-class'))}}" method="POST">
            @csrf()
            <div class="modal-body row">
                <div class="col-md-6 mt-2">
                    <label class="form-label">Category</label>
                    <select class="form-control category_name" required  name="category_name" >
                        <option value=""></option>
                        @foreach($subjects as $category)
                            <option value="{{$category->id}}">{{$category->subject_title}}</option>
                        @endforeach
                    </select>
                </div>
          
            
                <div class="section-div2">
                    @php $rand = rand(100000,99999999999); @endphp
                    <div class="{{$rand}}">
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Title</label>
                                <input class="form-control"  autocomplete="off" required type="text" name="title[]" placeholder="Enter Video Title" >
                                
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Link</label>
                                <i class="float-end bi bi-trash  remove-div3" data-val="{{$rand}}"></i>
                                <input class="form-control"  autocomplete="off" required  type="url" name="link[]" placeholder="Enter Video Link" >
                            </div>
                            
                        </div> 
                    </div>
                </div>
                
                <div classs="col-md-12 ">
                    <span class="btn btn-success btn-sm  mb-0 mt-3 float-end add_new2"><i class="bi bi-plus me-1"></i> Add</span>
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save </button>
            </div>
        </form>
      </div>
    </div>
  </div>

    <!-- Modal -->
 <div class="modal fade" id="csv_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Free Class Csv Type</h5>

          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <form action="free-class/free-class-csvtype" method="POST" enctype="multipart/form-data">
            @csrf()
            <div class="modal-body row">
                <div classs="col-md-12 ">
                
                    <a href="/sample-file/class-link.csv" download class="btn btn-success float-end btn-sm"><span class="bi bi-download"></span> Sample Csv format</a>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">Category</label>
                    <select class="form-control category_name3" required  name="category_name" >
                        <option value=""></option>
                        @foreach($subjects as $category)
                            <option value="{{$category->id}}">{{$category->subject_title}}</option>
                        @endforeach
                    </select>
                </div>
               
                <div class="section-di">
                    @php $rand = rand(100000,99999999999); @endphp
                    <div class="{{$rand}}">
                        <div class="row">
                            <div class="col-md-12 mt-2">
                                <label class="form-label">File</label>
                                <input class="form-control" required accept=".csv"  type="file" name="file"  >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save </button>
            </div>
        </form>
      </div>
    </div>
  </div>

<!-- Modal -->
<div class="modal fade" id="edit_freeclass" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edit Free Class</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{url(kpsc_cms('free-class/update-freeclass'))}}" method="POST">
            @csrf()
            <div class="modal-body row">
                <div class="col-md-6 mt-2">
                    <label class="form-label">Category</label>
                    <select class="form-control category_name3" required  name="category_name" >
                        <option value=""></option>
                         @foreach($subjects as $category)
                            <option value="{{$category->id}}">{{$category->subject_title}}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="section-div2">
                    @php $rand = rand(100000,99999999999); @endphp
                    <div class="{{$rand}}">
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Title</label>
                                <input class="form-control title"  autocomplete="off" required type="text" name="title" placeholder="Enter Video Title" >
                                <input class="form-control id" required type="hidden" name="id" placeholder="Enter Video Title" >

                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Link</label>
                                <input class="form-control link"  autocomplete="off" required  type="url" name="link" placeholder="Enter Video Link" >
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Position</label>
                                <input class="form-control position" required  type="text" name="position" placeholder="Enter position" >
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" >
                                    <option value="1">Show</option>
                                    <option value="0">Hide</option>
                                </select>
                            </div>
                        </div> 
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save </button>
            </div>
        </form>
        
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $(".add_new").click(function(e){
            var rand  = Math.random().toString(36).slice(2);
     
        });

        $('body').on('click', '.add_new2', function(){
            var rand  = Math.random().toString(36).slice(2);
            var div_val =   '<div class="'+rand+'">'+
                                '<div class="row">'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Title</label>'+
                                        '<input class="form-control" required type="text" name="title[]" placeholder="Enter Video Title" >'+
                                        
                                    '</div>'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Link</label>'+
                                        '<i class="float-end bi bi-trash  remove-div3" data-val="'+rand+'"></i>'+
                                        '<input class="form-control" required  type="url" name="link[]" placeholder="Enter Video Link" >'+
                                    '</div>'+
                                '</div>'+
                            '</div>';
                            
            $('.section-div2').append(div_val);
        });

        $(".subcategory option").hide();
        $(".subcategory1 option").hide();
        
        $(".category_name").change(function(e){
            $(".subcategory option").hide();
            $(".subcategory option[data-val=" + $(this).val() + "]").show();
        });
        
        $(".category_name1").change(function(e){
            $(".subcategory1 option").hide();
            $(".subcategory1 option[data-val=" + $(this).val() + "]").show();
        });
        $('body').on('click', '.remove-div3', function(){
            var class_val = $(this).data('val');
            $('.'+class_val).remove();
         
        });

        $('.reset-banner').click(function(event) {
            
            var id = $(this).data('id');
            var category =  $(this).data('category');
            var subcategory =  $(this).data('subcategory');
            var link =  $(this).data('link');
            var title =  $(this).data('title');
            var position = $(this).data('position');
            $('.category_name3 option[value="'+category+'"]').attr("selected", "selected");
            $('.subcategory3 option[value="'+subcategory+'"]').attr("selected", "selected");
            $('.title').val(title);
            $('.id').val(id);
            $('.link').val(link);
            $('.position').val(position);
            $('#edit_freeclass').modal('show');
        });
      
    });

    
</script>


@endsection