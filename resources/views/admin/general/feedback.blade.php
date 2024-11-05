@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Student Feedback</h3>    
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
                    <form action="{{admin_general('feedback')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Title</label>
                                <input class="form-control" required type="text"  name="title" placeholder="Title" >
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" required name="description" ></textarea>
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Expdated Date</label>
                                <input class="form-control" required type="date" min="{{date('Y-m-d')}}" name="expdate" >
                            </div>
                            <div class="col-lg-5 col-md-5 mb-2">
                                <label class="form-label">Section</label>
                                <select class="form-control" required  name="section">
                                    <option value="Psc">Psc</option>
                                    <option value="Ug Pg">Ug Pg</option>
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
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Description</th>
                            <th scope="col" class="border-0">Section</th>
                            <th scope="col" class="border-0">Exp:date</th>
                            <th scope="col" class="border-0">Count Submition</th>
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
                                <td>{{ucwords($list->title)}}</td>
                                <td>{{ Str::limit($list->description, 20) }}</td>
                                <td>{{ucwords($list->section)}}</td>
                                <td>{{date('Y-m-d',strtotime($list->expdate))}}</td>
                                <td>{{count($list->requests)}}</td>
                                <td>
                                     <a data-bs-toggle="tooltip" data-bs-placement="top" title="Link Copy" onclick="navigator.clipboard.writeText('{{url('feedback/'. $list->author)}}')" href="#"class="btn btn-sm btn-success text-center btn-circle"><i class="bi bi-clipboard "></i></a>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Request View" href="{{admin_general('feedback/view/'. $list->author)}}"class="btn btn-sm btn-primary text-center btn-circle"><i class="bi bi-eye "></i></a>
                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" data-title="{{$list->title}}" data-expdate="{{date('Y-m-d',strtotime($list->expdate))}}"  data-description="{{$list->description}}" data-section="{{$list->section}}"  data-id="{{$list->id}}" class="btn btn-sm btn-info text-center btn-circle  reset-banner"><i class="bi bi-pencil "></i></a>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" data-id="{{$list->id}}" href="{{admin_general('feedback/delete/'. $list->id)}}" class="btn btn-sm text-center btn-circle btn-danger "><i class="bi bi-trash "></i></a>
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
          <h5 class="modal-title" id="edit_banner_modal">Edit Data</h5>
          <a href="#"  class="close"  data-bs-dismiss="modal" aria-label="Close">
            <span class="fa fa-times" aria-hidden="true"></span>
          </a>
        </div>
        <form action="{{admin_general('feedback/updating')}}" method="POST">
            <div class="modal-body">
                
                    @csrf()
                <div class="editbanner">
                    
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Title</label>
                        <input class="form-control title" required type="text"  name="title" placeholder="Title" >
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Description</label>
                        <textarea class="form-control description" required name="description" ></textarea>
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Expdated Date</label>
                        <input class="form-control expdate" required type="date" min="{{date('Y-m-d')}}" name="expdate" >
                       
                    </div>
                    <div class="col-lg-12 col-md-12 mb-2">
                        <label class="form-label">Section</label>
                        <select class="form-control section" required  name="section">
                            <option value="Psc">Psc</option>
                            <option value="Ug Pg">Ug Pg</option>
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
            var title = $(this).data('title');
            var description = $(this).data('description');
            var expdate = $(this).data('expdate');
            var section = $(this).data('section'); 
            $('.title').val(title);
            $('.id').val(id);
            $('.description').val(description);
            $('.expdate').val(expdate);
            $('.section').val(section);
            

            $('#edit_banner').modal('show');
        });
    });
   

</script>
@endsection
