@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">OMR Exam Show</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-6">
                    <a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('model-exam') }}"><i
                            class="bi bi-plus me-1"></i>Add New</a>
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
                            @foreach ($date_list as $list)
                                <option value="{{ $list->exam_date }}">
                                    {{ date('d-m-Y', strtotime($list->exam_date)) }}
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
                {{-- existingExams --}}
                @foreach ($existingExams as $date_list)
                    @php
                        $date_based = $date_list->model_exam_details_one;
                    @endphp
                    <div class="table-responsive border-0">


                        <div class="card bg-light rounded">
                            <div class="card-body">
                                <table class="table table-dark-gray table-bordered align-middle p-4 mb-0 table-hover">
                                    <tr>
                                        <th scope="col" class="border">Exam Title</th>
                                        <th scope="col" class="border">{{ $date_list->examtitle }}</th>
                                        <th scope="col" class="border"> Total Participants</th>
                                        <th scope="col" class="border">
                                            {{ isset($date_list->model_exam_attened) ? $date_list->model_exam_attened->count() : 0 }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="border">Exam Started</th>
                                        <th scope="col" class="border">
                                            {{ date('d-M-Y => h:i a', strtotime($date_list->started_at)) }}</th>
                                        <th scope="col" class="border">Exam Ended</th>
                                        <th scope="col" class="border">
                                            {{ date('d-M-Y =>  h:i a', strtotime($date_list->ended_at)) }}
                                        </th>
                                    </tr>

                                    <tr>
                                        <th colspan="4">
                                            <a href="#"
                                                data-questionpaper="{{ url(Storage::url('model-exam/' . $date_based->qp_file)) }}"
                                                data-answerkey="{{ url(Storage::url('model-exam/' . $date_based->answer_file)) }}"
                                                data-date="{{ $date_list->exam_date }}" data-id="{{ $date_list->id }}"
                                                data-started="{{ date('Y-m-d\TH:i', strtotime($date_list->started_at)) }}"
                                                data-ended="{{ date('Y-m-d\TH:i', strtotime($date_list->ended_at)) }}"
                                                data-title="{{ $date_list->examtitle }}"
                                                data-subject="{{ $date_list->subject }}"
                                                data-category = "{{ $date_list->category }}"
                                                class="btn btn-info mb-2 exam_details_edit btn-sm float-left">
                                                <i class="bi bi-pencil"></i>
                                                Edit Exam Details
                                            </a>
                                            <a href="{{ url('admin/kpsc/model-exam/delete/' . $date_list->id) }}"
                                                class="btn btn-sm btn-danger mb-2 float-left">
                                                <i class="bi bi-x"></i>
                                                Delete
                                            </a>
                                            <a target="_new"
                                                href="{{ url('kpsc/model-exam/' . $date_list->exam_date . '/' . $date_list->id . '/leaderboard') }}"
                                                class="btn btn-sm btn-dark mb-2 float-left">
                                                <i class="bi bi-bar-chart"></i>
                                                View Leaderboard
                                            </a>

                                            <a target="_new2"
                                                href="{{ url('kpsc/model-exam/' . $date_list->exam_date . '/' . $date_list->id) }}"
                                                class="btn btn-sm btn-warning mb-2 float-left">
                                                <i class="bi bi-eye"></i>
                                                View Exam Page
                                            </a>



                                        </th>

                                    </tr>
                                </table>
                            </div>
                        </div>

                    </div>
                    <!-- Course list table END -->

                    <!-- Button trigger modal -->
                @endforeach

            </div>

        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="exam_details" tabindex="-1" role="dialog" aria-labelledby="exam_detailsLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exam_detailsLabel">Exam Details title</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ kpsc_cms('model-exam/edit-details') }}" enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="row">
                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Exam Date</label>
                                    <input class="form-control" required id="exam_date_edit" type="date" name="exam_date"
                                        placeholder="">
                                    <input class="form-control" type="hidden" id="exam_id" name="exam_id"
                                        placeholder="">
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Exam Started At</label>
                                    <input class="form-control" required id="exam_started_edit" type="datetime-local"
                                        name="exam_started" placeholder="">
                                </div>

                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Exam Ended</label>
                                    <input class="form-control" required id="exam_ended_edit" type="datetime-local"
                                        name="exam_ended" placeholder="">
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Exam Title</label>
                                    <input type="text" name="examtitle" id="examtitle_edit" class="form-control"
                                        required autocomplete="off">
                                </div>

                                <div class="col-md-12 mt-2">
                                    <label class="form-label">Category</label>
                                    <select name="category" id="category_edit" class="form-control" required
                                        autocomplete="off">
                                        <option class=""></option>
                                        <option class="10th level">10th level</option>
                                        <option class="plus two level">Plus two level</option>
                                        <option class="degree level">Degree level</option>
                                        <option class="programmer">Programmer</option>
                                        <option class="typest">Typest</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mb-2">
                                    <label class="form-label">Question Papper</label>
                                    <input class="form-control" accept=".pdf" type="file" name="qstn_file">

                                    <a href="" id="questionpaper">view Question papper</a>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="form-label">Answer Key</label>
                                    <input class="form-control" accept=".pdf" type="file" name="ans_file">
                                    <a href="" id="answerkey">view Answer Key</a>
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
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            $('body').on('click', '.search_date_based', function() {

                var date = $('#exam_date').val();
                var subject = $('#subject_sec').val();
                $(".date_based_list").html('');
                $.ajax({
                    url: "{{ kpsc_cms('model-exam/date_based') }}",
                    cache: false,
                    data: {
                        date: date,
                        subject: subject
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
                var questionpaper = $(this).data('questionpaper')
                var answerkey = $(this).data('answerkey')
                var category = $(this).data('category')

                console.log(questionpaper, answerkey)

                $('#exam_date_edit').val(date);
                $('#exam_started_edit').val(started);
                $('#exam_ended_edit').val(ended);
                $('#examtitle_edit').val(title);
                $('#subject_edit').val(subject);
                $('#exam_id').val(id);
                $('#questionpaper').attr('href', questionpaper)
                $('#answerkey').attr('href', answerkey)
                $('#category_edit').val(category);


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

                var url = "{{ kpsc_cms('daily-exam/update/edit__id') }}";
                url = url.replace("edit__id", id);

                $('#question_edit_form').attr('action', url)


                $('#edit_question').modal('show')


            });






        });
    </script>
@endsection
