@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">OMR Exam</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-6">
                    <!--<a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('model-exam/add-weekly-monthly-exam') }}"><i class="bi bi-plus me-1"></i>Add Weekly/Monthly Exam</a>-->

                    <a class="btn btn-sm btn-primary me-1 float-end" href="{{ kpsc_cms('model-exam/view') }}"><i
                            class="bi bi-plus me-1"></i>View Model Exam</a>
                    <span class="js-choice "></span>
                </div>
            </div>

        </div>

        <!-- Card body START -->
        <div class="card-body row">


            <!-- Search and select START -->
            <div class="row g-3 align-items-center justify-content-between mb-4">

                <div class="col-lg-12 new-banner-section ">
                    <form action="{{ kpsc_cms('model-exam/store') }}" method="POST" enctype="multipart/form-data">

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
                                    <input type="input" name="examtitle" class="form-control" required autocomplete="off">
                                </div>

                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-control" required autocomplete="off">
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
                                    <input class="form-control" accept=".pdf" required type="file" name="qstn_file">
                                    <div classs="col-md-2 ">

                                    </div>
                                </div>

                                <div class="col-md-12 mb-2">
                                    <label class="form-label">Answer Key</label>
                                    <input class="form-control" accept=".pdf" required type="file" name="ans_file">

                                </div>

                                <div class="col-md-6 mt-2">

                                    <input type="hidden" id="subject" name="subject" class="form-control">
                                </div>

                            </div>

                        </div>


                        <div classs="col-md-12 ">

                            <input type="submit" name="submit_form" value="Save" class="btn btn-success btn-block">
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



        });
    </script>
@endsection
