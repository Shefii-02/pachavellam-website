@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">Current Affairs Daily Exam Show</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-6">
                    <a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('ca-daily-exam') }}"><i
                            class="bi bi-plus me-1"></i>Add New Exam</a>
                    <span class="js-choice "></span>
                </div>
            </div>

        </div>

        <!-- Card body START -->
        <div class="card-body row">

            <div class="form-group  mb-2">
                <div class="row">
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Exam Date</label>
                        <select class="form-control" id="exam_date" name="exam_date">
                            <option value="">Select Date</option>
                            @foreach ($level_list as $list)
                                <option value="{{ $list->level }}">
                                   Day {{ $list->level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-2">
                        <button class="mt-4 btn btn-info search_date_based"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mt-4 date_based_list">

                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0 rounded-start">Exam date</th>
                            <th scope="col" class="border-0">Title</th>
                            <th scope="col" class="border-0">Total Qstn</th>
                            <th scope="col" class="border-0">Attended</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @foreach ($existingExams ?? [] as $itemExam)
                            <tr>
                                <td>
                                    <span class="small">{{ DateTimeFormat($itemExam->started_at) }}</span>
                                </td>
                                <td>
                                    <span class="small"> <strong class="fw-bold">Day {{ $itemExam->level }}</strong></span>
                                </td>
                                <td>
                                    <span class="small">
                                        {{ $itemExam->exam_details ? $itemExam->exam_details->count() : 0 }}</span>
                                </td>
                                <td>
                                    <span class="small">
                                        {{ $itemExam->ca_exam_attened ? $itemExam->ca_exam_attened->groupBy('user_id')->count() : 0 }}</span>
                                </td>
                                <td>
                                    @if ($itemExam->status == 1)
                                        <span class="text-success fw-bold small">Published</span>
                                    @else
                                        <span class="text-primary fw-bold small">Drafted</span>
                                    @endif
                                </td>
                                <td align="center">
                                    <a title="Edit" href="{{ kpsc_cms('ca-daily-exam/edit/' . $itemExam->id) }}"
                                        class="btn text-info mb-2 p-0 m-0 btn-sm float-left">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="exam_details" tabindex="-1" role="dialog" aria-labelledby="exam_detailsLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exam_detailsLabel">Exam Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ kpsc_cms('ca-daily-exam/edit-details/' . $list->id) }}" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="row">
                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Exam Title</label>
                                    <input list="examtitle" name="examtitle" id="examtitle_edit" class="form-control"
                                        required autocomplete="off">
                                    <datalist id="examtitle">
                                        @foreach ($exam_titles as $list)
                                            <option value="{{ $list->examtitle }}">{{ $list->examtitle }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Exam Started At</label>
                                    <input class="form-control" required id="exam_started_edit" type="datetime-local"
                                        name="exam_started" placeholder="">
                                </div>
                              

                            
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="edit_question" tabindex="-1" role="dialog" aria-labelledby="edit_questionLabel"
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
    <!-- Modal -->
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            $('body').on('click', '.search_date_based', function() {

                var date = $('#exam_date').val();
                // var subject = $('#subject_sec').val();
                $(".date_based_list").html('');
                $.ajax({
                    url: "{{ kpsc_cms('ca-daily-exam/date_based') }}",
                    cache: false,
                    data: {
                        date: date,
                        // subject: subject
                    },
                    success: function(html) {
                        $(".date_based_list").html(html);
                    }
                });
            });

            $('body').on('click', '.exam_details_edit', function(e) {
                e.preventDefault();

                var id = $(this).data('id')
                var date = $(this).data('date')
                var started = $(this).data('started')
                var ended = $(this).data('ended')
                var title = $(this).data('title')
                var subject = $(this).data('subject')

                $('#exam_date_edit').val(date);
                $('#exam_started_edit').val(started);
                $('#exam_ended_edit').val(ended);
                $('#examtitle_edit').val(title);
                $('#subject_edit').val(subject);
                $('#exam_id').val(id);

                $('#exam_details').modal('show')

            });


            $('body').on('click', '.exam_question_edit', function(e) {
                e.preventDefault();

                var id = $(this).data('id');
                var question = $(this).data('question');
                var answer = $(this).data('answer');
                var option1 = $(this).data('option1');
                var option2 = $(this).data('option2');
                var option3 = $(this).data('option3');
                var option4 = $(this).data('option4');



                $('.question').val(question);
                $('#answer').val(answer);
                $('#option1').val(option1);
                $('#option2').val(option2);
                $('#option3').val(option3);
                $('#option4').val(option4);

                var url = "{{ kpsc_cms('ca-daily-exam/update/edit__id') }}";
                url = url.replace("edit__id", id);

                $('#question_edit_form').attr('action', url)


                $('#edit_question').modal('show')


            });

            // $('body').on('click', '.remove-div', function() {
            //     var class_val = $(this).data('val');

            //     $('.' + class_val).remove();
            // });




        });
    </script>
@endsection
