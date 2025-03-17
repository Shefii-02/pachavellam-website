@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">Current Affairs Daily Exam</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-6">
                    <a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('ca-daily-exam/view') }}"><i
                            class="bi bi-plus me-1"></i>View CA Daily Exam's</a>
                    <span class="js-choice "></span>
                </div>
            </div>
        </div>

        <!-- Card body START -->
        <div class="card-body row">

            <!-- Search and select START -->
            <div class="row g-3 align-items-center justify-content-between mb-4">

                <div class="col-lg-12 new-banner-section ">
                    <form action="{{ kpsc_cms('ca-daily-exam/store') }}" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="row">
                                <div class="col-md-4 mt-2">
                                    <label class="form-label">Exam Day</label>
                                    <select name="level" class="form-control" required
                                        autocomplete="off">
                                        @for ($i = 1; $i <= 100; $i++) 
                                            @if(!$examDays->contains($i))
                                                <option value="{{ $i }}">Day {{$i}}</option>
                                            @endif
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label class="form-label">Exam Started At</label>
                                    <input class="form-control" required type="datetime-local" name="exam_started"
                                        placeholder="">
                                </div>


                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <input type="submit" class="btn btn-success btn-sm" value="Save">
                        </div>
                    </form>



                </div>
            </div>
        </div>

        <!-- Card body START -->
    </div>
    <!-- Main content END -->


    <!-- Modal -->
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $(".add_new_banner").click(function() {
                $(".new-banner-section").fadeToggle(1000);
            });

            // $(".add_new").click(function(e) {
            //     var rand = Math.random().toString(36).slice(2);
            //     var div_val = '<div class="' + rand + ' border border-bottom mb-2 p-3 bg-light">' +
            //         '<div class="row">' +
            //         '<div class="col-md-12 mt-2">' +
            //         '<label class="form-label">Question</label>' +
            //         '<i class="float-end bi bi-trash  remove-div" data-val="' + rand + '"></i>' +
            //         '<textarea class="form-control my-editor" id="desc-editor-' + rand +
            //         '" required rows="3" name="question[]" placeholder="Enter Question"  autocomplete="off"  ></textarea>' +

            //         '</div>' +
            //         '<div class="col-md-12 mt-2">' +
            //         '<label class="form-label">Answer</label>' +
            //         '<textarea class="form-control" required  id="desc-ans-' + rand +
            //         '" type="text" name="answer[]" placeholder="Enter Answer"  autocomplete="off" ></textarea>' +

            //         '</div>' +
            //         '<div class="col-md-6 mt-2">' +
            //         '<label class="form-label">Option1</label>' +
            //         '<textarea class="form-control my-editor"  id="desc-op1-' + rand +
            //         '" autocomplete="off"   type="text" name="option1[]" placeholder="Enter Option1" ></textarea>' +
            //         '</div>' +
            //         '<div class="col-md-6 mt-2">' +
            //         '<label class="form-label">Option2</label>' +
            //         '<textarea class="form-control my-editor" id="desc-op2-' + rand +
            //         '"  autocomplete="off"  type="text" name="option2[]" placeholder="Enter Option2" ></textarea>' +
            //         '</div>' +
            //         '<div class="col-md-6 mt-2">' +
            //         '<label class="form-label">Option3</label>' +
            //         '<textarea class="form-control my-editor" id="desc-op3-' + rand +
            //         '" autocomplete="off"   type="text" name="option3[]" placeholder="Enter Option3" ></textarea>' +
            //         '</div>' +
            //         '<div class="col-md-6 mt-2">' +
            //         '<label class="form-label">Option4</label>' +
            //         '<textarea class="form-control my-editor" id="desc-op4-' + rand +
            //         '" autocomplete="off"   type="text" name="option4[]" placeholder="Enter Option4" ></textarea>' +
            //         '</div>' +
            //         '</div>' +
            //         '</div>';

            //     $('.section-div').append(div_val);

            //     // CKEditorChange('desc-editor-' + rand);
            //     // $('#desc-editor-' + rand).ckeditor();
            //     // ✅ Reinitialize CKEditor for the newly added textarea

            //     CKEDITOR.replace('desc-editor-' + rand, {
            //         filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            //         filebrowserUploadMethod: 'form',
            //         height: 100,
            //         filebrowserBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=2&editor=ckeditor&fldr=",
            //         filebrowserImageBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=1&editor=ckeditor&fldr="
            //     });
            // });






        });
    </script>
@endsection
