@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">Daily Exam</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-6">
                    <!--<a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('daily-exam/add-weekly-monthly-exam') }}"><i class="bi bi-plus me-1"></i>Add Weekly/Monthly Exam</a>-->

                    <a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('daily-exam/view') }}"><i
                            class="bi bi-plus me-1"></i>View Daily Exam</a>
                    <span class="js-choice "></span>
                </div>
            </div>

        </div>

        <!-- Card body START -->
        <div class="card-body row">

            <!-- Search and select START -->
            <div class="row g-3 align-items-center justify-content-between mb-4">

                <div class="col-lg-12 new-banner-section ">
                    <form action="{{ kpsc_cms('daily-exam/store') }}" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Exam Date</label>
                                    <input class="form-control" required type="date" name="exam_date" placeholder="">

                                </div>
                                <div class="col-md-3 mt-2">
                                    <label class="form-label">Exam Started At</label>
                                    <input class="form-control" required type="datetime-local" name="exam_started"
                                        placeholder="">
                                </div>

                                <div class="col-md-3 mt-2">
                                    <label class="form-label">Exam Ended</label>
                                    <input class="form-control" required type="datetime-local" name="exam_ended"
                                        placeholder="">

                                </div>
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Exam Title</label>
                                    <input list="examtitle" name="examtitle" class="form-control" required
                                        autocomplete="off">
                                    {{-- <datalist id="examtitle">
                                        @foreach ($exam_titles as $list)
                                            <option value="{{ $list->examtitle }}">{{ $list->examtitle }}</option>
                                        @endforeach
                                    </datalist> --}}
                                </div>

                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Subject</label>
                                    <select id="subject" name="subject" class="form-control" required autocomplete="off">
                                        @foreach ($exam_subjects as $list2)
                                            <option value="{{ $list2->id }}">{{ $list2->subject_title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <input type="submit" class="btn btn-success btn-sm" value="Save">
                        </div>
                    </form>
                    {{-- <div class="section-div">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}  border-bottom mb-2 p-3 bg-light">
                                <div class="row">
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Question</label>
                                        <i class="float-end bi bi-trash  remove-div" data-val="{{ $rand }}"></i>
                                        <textarea class="form-control my-editor" id="desc-editor-0" required name="question[]" placeholder="Enter Question"
                                            rows="3"></textarea>

                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Answer</label>
                                        <textarea class="form-control my-editor" id="desc-ans-0" required autocomplete="off" type="text" name="answer[]"
                                            placeholder="Enter Answer"></textarea>

                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Option1</label>
                                        <textarea class="form-control my-editor" id="desc-op1-0" autocomplete="off" type="text" name="option1[]"
                                            placeholder="Enter Option1"></textarea>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Option2</label>
                                        <textarea class="form-control my-editor" id="desc-op2-0" autocomplete="off" type="text" name="option2[]"
                                            placeholder="Enter Option2"></textarea>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Option3</label>
                                        <textarea class="form-control my-editor" id="desc-op3-0" autocomplete="off" type="text" name="option3[]"
                                            placeholder="Enter Option3"></textarea>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Option4</label>
                                        <textarea class="form-control  my-editor" id="desc-op4-0" autocomplete="off" type="text" name="option4[]"
                                            placeholder="Enter Option4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div classs="col-md-12 ">

                            <span class="btn btn-info btn-sm  mb-0 mt-3 float-left add_new"><i
                                    class="fa fa-plus me-1"></i> Add More Question</span>
                        </div>
                        <br>

                        <br>
                        <br>

                        <div classs="col-md-12 ">

                            <input type="submit" name="submit_form" value="Save" class="btn btn-success btn-block">
                        </div> --}}


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
