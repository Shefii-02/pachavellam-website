@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">Model Exam Show</h3>
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
                console.log(1)
                var id = $(this).data('id')
                var date = $(this).data('date')
                var started = $(this).data('started')
                var ended = $(this).data('ended')
                var title = $(this).data('title')
                var subject = $(this).data('subject')
                var questionpaper = $(this).data('questionpaper')
                var answerkey = $(this).data('answerkey')

                console.log(questionpaper, answerkey)

                $('#exam_date_edit').val(date);
                $('#exam_started_edit').val(started);
                $('#exam_ended_edit').val(ended);
                $('#examtitle_edit').val(title);
                $('#subject_edit').val(subject);
                $('#exam_id').val(id);
                $('#questionpaper').attr('href', questionpaper)
                $('#answerkey').attr('href', answerkey)

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
