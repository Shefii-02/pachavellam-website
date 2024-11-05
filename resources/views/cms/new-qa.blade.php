@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">New Model Q A</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-6 text-end">
                    @can('New Q A add csv')
                    <!-- Short by filter -->
                    <!--<button class="btn btn-sm btn-primary me-1"  data-bs-toggle="modal" data-bs-target="#csv_modal"><i class="bi bi-plus me-1"></i>Add Csv</button> -->
                    @endcan
                    @can('New Q A add')
                    <button class="btn btn-sm btn-primary me-1"  data-bs-toggle="modal" data-bs-target="#new_modal"><i class="bi bi-plus me-1"></i>Add New</button>
                    @endcan
                    
                    <span class="js-choice "></span>
                </div>
            </div>
            
        </div>
        <!-- Card header END -->

        @can('New Q A list')
            <!-- Card body START -->
        <div class="card-body row mt-5">
            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <!--<th scope="col" class="border-0 rounded-start">No</th>-->
                            <th scope="col" class="border-0">Question</th>
                            <th scope="col" class="border-0">Answer</th>
                            <th scope="col" class="border-0">Options</th>
                            <th scope="col" class="border-0">Created Date</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @php $i=count($newqa_list) ?? 1; @endphp
                        @foreach ($newqa_list->reverse() as $list)
                        <!-- Table item -->
                        <tr>
                            <!-- Table data -->
                                {{--<td>
                             $i-- 
                            </td>--}}
                            <!-- Table data -->
                            <td style=" white-space: pre-line;">
                                {!! $list->question !!}
                            </td>
                            <!-- Table data -->
                            <td style=" white-space: pre-line;">
                                {{$list->currect_ans}}
                            </td>
                            <!-- Table data -->
                            <td style=" white-space: pre-line;">
                                {{$list->options}}
                            </td>
                            <td>
                                {{$list->created_at}}
                            </td>
                            @php 
                                $exp = explode(',,',$list['options']);
                                $option1 = str_replace(array('{', '}'), '',$exp[0]);
                                $option2 = str_replace(array('{', '}'), '',$exp[1]);
                                $option3 = str_replace(array('{', '}'), '',$exp[2]);
                                $option4 = str_replace(array('{', '}'), '',$exp[3]);

                            @endphp

                            <!-- Table data -->
                            <td>
                                @can('New Q A edit')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset" aria-label="Reset"  href="#" data-id="{{$list->id}}" data-question="{{$list->question}}" data-answer="{{$list->currect_ans}}" data-option1="{{$option1}}" data-option2="{{$option2}}" data-option3="{{$option3}}" data-option4="{{$option4}}" data-subject="{{$list->subject}}" class="btn btn-sm btn-info me-1 edit_qa btn-circle"><i class="bi bi-pencil "></i></a>
                                @endcan
                                @can('New Q A delete')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  href="{{route('adminkpsc.new-qa.delete', $list->id)}}" data-id="{{$list->id}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
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
        @endcan
        
        <!-- Card body START -->
    </div>
<!-- Main content END -->

 <!-- Modal -->
 <div class="modal fade" id="new_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">New Model Q A Form </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="new-qa" method="POST"/>
        @csrf()
        <div class="modal-body row ">
            <div class="col-md-12 mt-2 mb-3">
                <label class="form-label">Subject</label>
                <select class="form-control subject_name" required  name="subject_name" >
                    <option value=""></option>
                    @foreach($subjects as $category)
                        <option value="{{$category->id}}" @if(app('request')->input('subject_name') == $category->id) selected @endif>{{$category->subject_title}}</option>
                    @endforeach
                </select>
            </div>
            <div class="section-div">
                @php $rand = rand(100000,99999999999); @endphp
                <div class="{{$rand}} border border-bottom mb-2 p-2">
                    <div class="row">
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Question</label>
                            <input class="form-control" required  type="text" name="question[]" placeholder="Enter Question" >
                            
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Answer</label>
                            <i class="float-end bi bi-trash  remove-div" data-val="{{$rand}}"></i>
                            <input class="form-control" required  type="text" name="answer[]" placeholder="Enter Answer" >
                            
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Option1</label>
                            <input class="form-control" required  type="text" name="option1[]" placeholder="Enter Option1" >
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Option2</label>
                            <input class="form-control" required  type="text" name="option2[]" placeholder="Enter Option2" >
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Option3</label>
                            <input class="form-control" required  type="text" name="option3[]" placeholder="Enter Option3" >
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Option4</label>
                            <input class="form-control" required  type="text" name="option4[]" placeholder="Enter Option4" >
                        </div>
                    </div>
                </div>
            </div>
            
            <div classs="col-md-12 ">
                
                <span class="btn btn-success btn-sm  mb-0 mt-3 float-end add_new"><i class="bi bi-plus me-1"></i> Add</span>
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
    <div class="modal-dialog ">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">New Model Q A Csv Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="new-qa/upload-csvtype" method="POST"  enctype="multipart/form-data">
            @csrf()
            <div class="modal-body row">
                <div class="col-md-12 ">
                    
                    <a href="{{url('sample-file/new-modal-qa.csv')}}" download class="btn btn-success float-end btn-sm"><span class="bi bi-download"></span> Sample Csv format</a>
                </div>
                <div class="section-div2">
                    @php $rand = rand(100000,99999999999); @endphp
                    <div class="{{$rand}} ">
                        <div class="">
                            
                            <div class="col-md-12 mt-2">
                                <label class="form-label">File</label>
                                <input class="form-control" required accept=".csv" type="file" name="file"  >
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
<div class="modal fade" id="edit_qa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edit New Model QA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="new-qa/update-qa" method="POST">
            @csrf()
            <div class="modal-body row">
                <div class="col-md-12 mt-2 mb-3">
                <label class="form-label">Subject</label>
                <select class="form-control subject_name" required  name="subject_name" >
                    <option value=""></option>
                    @foreach($subjects as $category)
                        <option value="{{$category->id}}" @if(app('request')->input('subject_name') == $category->id) selected @endif>{{$category->subject_title}}</option>
                    @endforeach
                </select>
            </div>
                <div class="section-div">
                    @php $rand = rand(100000,99999999999); @endphp
                    <div class="{{$rand}} border border-bottom mb-2">
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Question</label>
                                <input class="form-control" required id="question" type="text" name="question" placeholder="Enter Question" >
                                <input type="hidden" class="id"  name="id">
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Answer</label>
                                <input class="form-control" required id="answer" type="text" name="answer" placeholder="Enter Answer" >
                                
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Option1</label>
                                <input class="form-control" required id="option1" type="text" name="option1" placeholder="Enter Option1" >
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Option2</label>
                                <input class="form-control" required id="option2" type="text" name="option2" placeholder="Enter Option2" >
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Option3</label>
                                <input class="form-control" required id="option3" type="text" name="option3" placeholder="Enter Option3" >
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Option4</label>
                                <input class="form-control" required id="option4" type="text" name="option4" placeholder="Enter Option4" >
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
            var div_val =   '<div class="'+rand+' border border-bottom mb-2 p-2">'+
                                '<div class="row">'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Question</label>'+
                                        '<input class="form-control" required  type="text" name="question[]" placeholder="Enter Question" >'+
                                        
                                    '</div>'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Answer</label>'+
                                        '<i class="float-end bi bi-trash  remove-div" data-val="{{$rand}}"></i>'+
                                        '<input class="form-control" required  type="text" name="answer[]" placeholder="Enter Answer" >'+
                                        
                                    '</div>'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Option1</label>'+
                                        '<input class="form-control" required  type="text" name="option1[]" placeholder="Enter Option1" >'+
                                    '</div>'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Option2</label>'+
                                        '<input class="form-control" required  type="text" name="option2[]" placeholder="Enter Option2" >'+
                                    '</div>'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Option3</label>'+
                                        '<input class="form-control" required  type="text" name="option3[]" placeholder="Enter Option3" >'+
                                    '</div>'+
                                    '<div class="col-md-6 mt-2">'+
                                        '<label class="form-label">Option4</label>'+
                                        '<input class="form-control" required  type="text" name="option4[]" placeholder="Enter Option4" >'+
                                    '</div>'+
                                '</div>'+
                            '</div>';
                            
            $('#new_modal .section-div').append(div_val);
        });

        
        $('body').on('click', '.remove-div', function(){
            var class_val = $(this).data('val');
            $('.'+class_val).remove();
        });
       
        $('.edit_qa').click(function(event) {
            
            var id = $(this).data('id');
            var question =  $(this).data('question');
            var answer =  $(this).data('answer');
            var option1 = $(this).data('option1');
            var option2 =  $(this).data('option2');
            var option3 =  $(this).data('option3');
            var option4 = $(this).data('option4');
            var subject = $(this).data('subject');

            $('#question').val(question);
            $('.id').val(id);
            $('#answer').val(answer);
            $('#option1').val(option1);
            $('#option2').val(option2);
            $('#option3').val(option3);
            $('#option4').val(option4);
            $('#subject').val(subject);

            $('#edit_qa').modal('show');
        });

    });

    
</script>


@endsection