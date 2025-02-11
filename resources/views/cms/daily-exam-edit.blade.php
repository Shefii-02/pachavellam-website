@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">Daily Exam Edit</h3>
                </div>
                <!-- Select option -->
                <div class="col-md-6">
                    <a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('daily-exam/view') }}"><i
                            class="bi bi-plus me-1"></i>Back</a>
                    <span class="js-choice "></span>
                </div>
            </div>

        </div>

        <!-- Card body START -->
        <div class="card-body row">

            <!-- Search and select START -->
            <div class="row g-3 align-items-center justify-content-between mb-4">

                <div class="col-lg-12 new-banner-section ">
                    <form action="{{ kpsc_cms('daily-exam/update/' . $exam_detail->id) }}" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Exam Date</label>
                                    <input class="form-control" value="{{ $exam_detail->exam_date }}" required
                                        type="date" name="exam_date" placeholder="">

                                </div>
                                <div class="col-md-3 mt-2">
                                    <label class="form-label">Exam Started At</label>
                                    <input class="form-control" value="{{ $exam_detail->started_at }}" required
                                        type="datetime-local" name="exam_started" placeholder="">
                                </div>

                                <div class="col-md-3 mt-2">
                                    <label class="form-label">Exam Ended</label>
                                    <input class="form-control" value="{{ $exam_detail->ended_at }}" required
                                        type="datetime-local" name="exam_ended" placeholder="">

                                </div>
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Exam Title</label>
                                    <input list="examtitle" value="{{ $exam_detail->examtitle }}" name="examtitle"
                                        class="form-control" required autocomplete="off">
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
                                            <option {{ $exam_detail->subject == $list2->id ? 'selected' : '' }}
                                                value="{{ $list2->id }}">{{ $list2->subject_title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" name="status"
                                            {{ $exam_detail->status == 1 ? 'checked' : '' }} type="checkbox" role="switch"
                                            id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">Publish</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="mt-4">
                            @foreach ($exam_detail->exam_details ?? [] as $examDetailItem)
                                <div class="row border m-2 p-2 bg-light class-{{ $examDetailItem->id }}">
                                    <div class="col-lg-12 text-end">
                                        <span class="editQuestion" role="button" data-id="{{ $examDetailItem->id }}">
                                            <i class="bi bi-pencil-fill me-2"></i>
                                        </span>
                                        <span role="button"><i data-id="{{ $examDetailItem->id }}"
                                                class="bi bi-trash text-danger remove-div"></i></span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h5>Question</h5>
                                        <span>{!! $examDetailItem->question !!}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h6>Answer</h6>
                                        <span>{!! $examDetailItem->currect_ans !!}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h6>Option 1</h6>
                                        <span>{!! $examDetailItem->option_1 !!}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h6>Option 2</h6>
                                        <span>{!! $examDetailItem->option_2 !!}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h6>Option 3</h6>
                                        <span>{!! $examDetailItem->option_3 !!}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h6>Option 4</h6>
                                        <span>{!! $examDetailItem->option_4 !!}</span>
                                    </div>
                                    <div class="col-lg-12">
                                        <h6>Notes:</h6>
                                        <span>{!! $examDetailItem->notes !!}</span>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                        <div class="text-end mt-4">
                            {{-- <span class="btn btn-dark btn-sm   float-left "><i class="fa fa-upload me-1"></i> Import
                                Questions</span> --}}
                            <span class="btn btn-info btn-sm float-left" data-bs-toggle="modal"
                                data-bs-target="#addQuestion"><i class="fa fa-plus me-1"></i> Add
                                Question</span>
                            <button type="submit" class="btn btn-success btn-sm"><i
                                    class="fa fa-bookmark me-1"></i>Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card body START -->
    </div>
    <!-- Main content END -->

    <!-- Modal -->
    <div class="modal fade" id="addQuestion" tabindex="-1" role="dialog" aria-labelledby="exam_detailsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exam_detailsLabel">Create Exam Question</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ kpsc_cms('daily-exam/save-question/' . $exam_detail->id) }}" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="">
                                <div class="">
                                    <div class="row">
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Question</label>
                                            <textarea class="form-control my-editor" id="desc-editor-0" required name="question" placeholder="Enter Question"
                                                rows="3"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Answer</label>
                                            <textarea class="form-control my-editor" id="desc-ans-0" required autocomplete="off" type="text" name="answer"
                                                placeholder="Enter Answer"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Option1</label>
                                            <textarea class="form-control my-editor" id="desc-op1-0" autocomplete="off" type="text" name="option1"
                                                placeholder="Enter Option1"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Option2</label>
                                            <textarea class="form-control my-editor" id="desc-op2-0" autocomplete="off" type="text" name="option2"
                                                placeholder="Enter Option2"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Option3</label>
                                            <textarea class="form-control my-editor" id="desc-op3-0" autocomplete="off" type="text" name="option3"
                                                placeholder="Enter Option3"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Option4</label>
                                            <textarea class="form-control  my-editor" id="desc-op4-0" autocomplete="off" type="text" name="option4"
                                                placeholder="Enter Option4"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Explanation</label>
                                            <textarea class="form-control" id="explanation" autocomplete="off" type="text" name="explanation"
                                                placeholder="Enter Explanation"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="editQuestion" tabindex="-1" role="dialog" aria-labelledby="exam_detailsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exam_detailsLabel">Edit Exam Question </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body editQuestionDetails">

                </div>

            </div>
        </div>
    </div>
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


            // $('body').on('click', '.remove-div', function() {
            //     var class_val = $(this).data('val');
            //     $('.class-' + class_val).remove();
            // });

            $('body').on('click', '.remove-div', function() {

                var id = $(this).data('id'); // Retrieve the question ID

                if (!id) {
                    alert("Error: No question ID found!"); // Debugging check
                    return;
                }

                $.ajax({
                    url: "{{ kpsc_cms('daily-exam/question-delete') }}",
                    type: "GET", // Use POST instead of GET
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}" // Include CSRF token for Laravel
                    },
                    success: function(response) {
                        $('.class-' + id).remove(); // Remove the question from UI
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error:", error); // Log error for debugging
                    }
                });
            });


            $('body').on('click', '.editQuestion', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ kpsc_cms('daily-exam/question-edit') }}",
                    cache: false,
                    data: {
                        'id': id
                    },
                    success: function(html) {
                        $(".editQuestionDetails").append(html);
                        $('#editQuestion').modal('show');
                        $('.my-editor').each(function() {
                            CKEDITOR.replace($(this).attr('id'), {
                                filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
                                filebrowserUploadMethod: 'form',
                                height: 100,
                                filebrowserBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=2&editor=ckeditor&fldr=",
                                filebrowserImageBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=1&editor=ckeditor&fldr=",
                                toolbar: [{
                                        name: 'basicstyles',
                                        items: ['Bold', 'Italic',
                                            'Underline', 'Strike', '-',
                                            'RemoveFormat'
                                        ]
                                    },
                                    {
                                        name: 'paragraph',
                                        items: ['NumberedList',
                                            'BulletedList', '-',
                                            'Blockquote'
                                        ]
                                    },
                                    {
                                        name: 'insert',
                                        items: ['Image', 'Table',
                                            'HorizontalRule',
                                            'SpecialChar'
                                        ]
                                    }, // ✅ Enable image insertion

                                ]
                            });
                        });
                    }
                });
            });


        });
    </script>
@endsection
