@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-6">
                    <h3 class="mb-0">Daily CA</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-6">
                    @can('Daily Ca link add')
                        <!-- Short by filter -->
                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#link_modal"><i
                                class="bi bi-plus me-1"></i>Add Link</button>
                    @endcan
                    @can('Daily Ca pdf add')
                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#pdf_modal"><i
                                class="bi bi-plus me-1"></i>Add Pdf</button>
                    @endcan
                    @can('Daily Ca csv add')
                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#csv_modal"><i
                                class="bi bi-plus me-1"></i>Add Csv</button>
                    @endcan
                    @can('Daily Ca add')
                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#add_modal"><i
                                class="bi bi-plus me-1"></i>Add New</button>
                    @endcan
                    <span class="js-choice "></span>
                </div>
            </div>

        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body row">
            @can('Daily Ca list')
                <form method="GET" action="{{ kpsc_cms('daily-ca/daily-ca-show') }}">
                    <div class="row">
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Year</label>
                            <select class="form-control year_select" required name="year">
                                <option></option>
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Month</label>
                            <select class="form-control month_select" required name="month">
                                <option>Please first select year</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Title/Day/Subject</label>
                            <select class="form-control monthly_based" name="day">
                                <option>Please first select year</option>
                            </select>
                        </div>
                        <div classs="col-md-2  mt-2">
                            <button type="submit" class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-search me-1"></i>
                                Submit</button>
                        </div>
                    </div>

                </form>
            @endcan

            @can('Daily Ca list')
                @if (isset($daily_ca_list))
                    <div class="mt-5">
                        <!-- Course list table START -->
                        <div class="table-responsive border-0">
                            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                                <!-- Table head -->
                                <thead>
                                    <tr>
                                        <th scope="col" class="border-0 rounded-start">Date</th>
                                        <th scope="col" class="border-0">Title</th>
                                        <th scope="col" class="border-0">Link/File</th>
                                        <th scope="col" class="border-0">Question</th>
                                        <th scope="col" class="border-0">Answer</th>
                                        <th scope="col" class="border-0">Note</th>
                                        <th scope="col" class="border-0 rounded-end">Action</th>
                                    </tr>
                                </thead>

                                <!-- Table body START -->
                                <tbody>
                                    @foreach ($daily_ca_list as $list)
                                        <!-- Table item -->
                                        <tr>
                                            <!-- Table data -->
                                            <td>
                                                {{ $list->year ?? '---' }}
                                                <br>
                                                {{ $list->month ?? '---' }}
                                                <br>
                                                {{ $list->day ?? '---' }}
                                            </td>

                                            <!-- Table data -->
                                            <td>
                                                {{ $list->title ?? '---' }}
                                            </td>
                                            <!-- Table data -->
                                            <td>
                                                @if ($list->file_name != null)
                                                    <a target="_new"
                                                        href="{{ Storage::url('files/' . $list->file_name) }}">Open</a>
                                                @else
                                                    {{ '---' }}
                                                @endif
                                            </td>
                                            <!-- Table data -->
                                            <td>
                                                {{ $list->question ?? '---' }}
                                            </td>
                                            <!-- Table data -->
                                            <td>
                                                {{ $list->answer ?? '---' }}
                                            </td>
                                            <!-- Table data -->
                                            <td>
                                                {{ $list->note ?? '---' }}
                                            </td>

                                            <!-- Table data -->
                                            <td>
                                                @can('Daily Ca edit')
                                                    @if ($list->type == 'Link')
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-original-title="Reset" aria-label="Reset"
                                                            data-id="{{ $list->id }}" data-year="{{ $list->year }}"
                                                            data-month="{{ $list->month }}" data-title="{{ $list->title }}"
                                                            data-link="{{ $list->file_name }}"
                                                            class="btn btn-sm btn-info  edit_linktype btn-circle"><i
                                                                class="bi bi-pencil "></i></a>
                                                    @elseif ($list->type == 'Pdf')
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-original-title="Reset" aria-label="Reset"
                                                            data-id="{{ $list->id }}" data-year="{{ $list->year }}"
                                                            data-month="{{ $list->month }}" data-title="{{ $list->title }}"
                                                            class="btn btn-sm btn-info  edit_pdftype btn-circle"><i
                                                                class="bi bi-pencil "></i></a>
                                                    @elseif ($list->type == 'Day')
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-original-title="Reset" aria-label="Reset"
                                                            data-id="{{ $list->id }}" data-year="{{ $list->year }}"
                                                            data-month="{{ $list->month }}" data-day="{{ $list->day }}"
                                                            data-question="{{ $list->question }}"
                                                            data-answer="{{ $list->answer }}" data-note="{{ $list->note }}"
                                                            class="btn btn-sm btn-info me-1 edit_daytype btn-circle"><i
                                                                class="bi bi-pencil "></i></a>
                                                    @else
                                                    @endif
                                                @endcan

                                                @can('Daily Ca delete')
                                                    <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-original-title="Delete" aria-label="Delete"
                                                        data-id="{{ $list->id }}"
                                                        href="{{ route('adminkpsc.daily-ca.delete', $list->id) }}"
                                                        class="btn btn-sm btn-danger  btn-circle"><i class="bi bi-trash"></i></a>
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
                @endif
            @endcan
        </div>
        <!-- Card body START -->
    </div>
    <!-- Main content END -->

    <!-- Modal -->
    <div class="modal fade" id="link_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Daily CA Link type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ kpsc_cms('daily-ca/daily-ca-linktype') }}" method="POST">
                    @csrf()
                    <div class="modal-body row">
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control" required type="text"
                                name="year" placeholder="Enter Year">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control" required type="text"
                                name="month" placeholder="Enter Month">
                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-div">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Title</label>
                                        <input class="form-control" required type="text" name="title[]"
                                            placeholder="Enter Redirection link">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Link</label>
                                        <i class="float-end bi bi-trash  remove-div" data-val="{{ $rand }}"></i>
                                        <input class="form-control" required type="text" name="link[]"
                                            placeholder="Enter Redirection link">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div classs="col-md-12 ">

                            <span class="btn btn-success btn-sm  mb-0 mt-3 float-end add_new"><i
                                    class="bi bi-plus me-1"></i> Add</span>
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
    <div class="modal fade" id="pdf_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Daily CA Pdf Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ kpsc_cms('daily-ca/daily-ca-pdftype') }}" method="POST" enctype="multipart/form-data">
                    @csrf()
                    <div class="modal-body row">
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control" required type="text"
                                name="year" placeholder="Enter Year">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control" required type="text"
                                name="month" placeholder="Enter Month">

                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-div2">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Title</label>
                                        <input class="form-control" required type="text" name="title"
                                            placeholder="Enter Title">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">File</label>
                                        <input class="form-control" required accept=".pdf" type="file"
                                            name="file">
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
    <div class="modal fade" id="csv_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Daily CA Csv Type</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ kpsc_cms('daily-ca/daily-ca-csvtype') }}" method="POST" enctype="multipart/form-data">
                    @csrf()
                    <div class="modal-body row">
                        <div classs="col-md-12 ">

                            <a href="{{ url('sample-file/daily-ca.csv') }}" download
                                class="btn btn-success float-end btn-sm"><span class="bi bi-download"></span> Sample Csv
                                format</a>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control" required type="text"
                                name="year" placeholder="Enter Year">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control" required type="text"
                                name="month" placeholder="Enter Month">

                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-di">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Day/Subject</label>
                                        <input list="day" autocomplete="off" class="form-control" required
                                            type="text" name="day" placeholder="Enter Day">

                                        <datalist id="day">
                                            @foreach ($daily_ca_day as $day_list)
                                                <option>{{ $day_list->day }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">File</label>
                                        <input class="form-control" required accept=".csv" type="file"
                                            name="file">
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
    <div class="modal fade" id="add_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Daily CA Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ kpsc_cms('daily-ca') }}" method="POST">
                    @csrf()
                    <div class="modal-body row">
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control" required type="text"
                                name="year" placeholder="Enter Redirection link">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control" required type="text"
                                name="month" placeholder="Enter Redirection link">

                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Day/Subject</label>
                            <input list="day" autocomplete="off" class="form-control" required type="text"
                                name="day" placeholder="Enter Redirection link">

                            <datalist id="day">
                                @foreach ($daily_ca_day as $day_list)
                                    <option>{{ $day_list->day }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-div3">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Question</label>
                                        <textarea class="form-control" required type="text" name="question[]" placeholder="Enter Question"></textarea>

                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Answer</label>
                                        <textarea class="form-control" required type="text" name="answer[]" placeholder="Enter Answer"></textarea>

                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Note</label>
                                        <i class="float-end bi bi-trash  remove-div3" data-val="{{ $rand }}"></i>
                                        <textarea class="form-control" type="text" name="note[]" placeholder="Enter Note"></textarea>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <div classs="col-md-12 ">
                            <span class="btn btn-success btn-sm  mb-0 mt-3 float-end add_new3"><i
                                    class="bi bi-plus me-1"></i> Add</span>
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


    <div class="modal fade" id="edit_daytype" tabindex="-1" aria-labelledby="edit_day" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_day">Daily CA Edit Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ kpsc_cms('daily-ca/edit-ca-day-update') }}" method="POST">
                    @csrf()
                    <div class="modal-body row">
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control year" required type="text"
                                name="year" placeholder="Enter Year">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="id" class="id">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control month" required type="text"
                                name="month" placeholder="Enter  Month">

                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Day/Subject</label>
                            <input list="day" autocomplete="off" class="form-control day" required type="text"
                                name="day" placeholder="Enter  Day">

                            <datalist id="day">
                                @foreach ($daily_ca_day as $day_list)
                                    <option>{{ $day_list->day }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-div3">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Question</label>
                                        <textarea class="form-control question my-editor1" id="qstn" required type="text" name="question"
                                            placeholder="Enter Question"></textarea>

                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Answer</label>
                                        <textarea class="form-control answer my-editor1" id="ans" required type="text" name="answer"
                                            placeholder="Enter Answer"></textarea>

                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Note</label>
                                        <textarea class="form-control note" type="text" name="note" placeholder="Enter Note"></textarea>
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
    <div class="modal fade" id="edit_linktype" tabindex="-1" aria-labelledby="edit_link" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_link">Daily CA Edit Link type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ kpsc_cms('daily-ca/daily-ca-linktype-update') }}" method="POST">
                    @csrf()
                    <div class="modal-body row">
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control year" required type="text"
                                name="year" placeholder="Enter Year">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="id" class="id">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control month" required type="text"
                                name="month" placeholder="Enter Month">

                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-div">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Title</label>
                                        <input class="form-control title" required type="text" name="title"
                                            placeholder="Enter Redirection link">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Link</label>
                                        <input class="form-control link" required type="text" name="link"
                                            placeholder="Enter Redirection link">
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
    <div class="modal fade" id="edit_pdftype" tabindex="-1" aria-labelledby="edit_pdf" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_pdf">Daily CA Edit Pdf Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ kpsc_cms('daily-ca/daily-ca-pdftype-update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf()
                    <div class="modal-body row">
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Year</label>
                            <input list="year" autocomplete="off" class="form-control year" required type="text"
                                name="year" placeholder="Enter Year">
                            <datalist id="year">
                                @foreach ($daily_ca as $year_list)
                                    <option>{{ $year_list->year }}</option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="id" class="id">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Month</label>
                            <input list="month" autocomplete="off" class="form-control month" required type="text"
                                name="month" placeholder="Enter Month">

                            <datalist id="month">
                                @foreach ($daily_ca_month as $month_list)
                                    <option>{{ $month_list->month }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="section-div2">
                            @php $rand = rand(100000,99999999999); @endphp
                            <div class="{{ $rand }}">
                                <div class="row">
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Title</label>
                                        <input class="form-control title" required type="text" name="title"
                                            placeholder="Enter Title">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">File</label>
                                        <input class="form-control" accept=".pdf" type="file" name="file">
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
        $(document).ajaxStart(function() {
            $("#wait").css("display", "block");
        });

        $(document).ajaxComplete(function() {
            $("#wait").css("display", "none");
        });
        $(document).ready(function() {
            $(".add_new").click(function(e) {
                var rand = Math.random().toString(36).slice(2);
                var div_val = '<div class="' + rand + '">' +
                    '<div class="row">' +
                    '<div class="col-md-6 mt-2">' +
                    '<label class="form-label">Title</label>' +
                    '<input class="form-control" required type="text" name="title[]" placeholder="Enter Redirection link" >' +
                    '</div>' +
                    '<div class="col-md-6 mt-2">' +
                    '<label class="form-label">Link</label>' +
                    '<i class="float-end bi bi-trash remove-div" data-val="' + rand + '"></i>' +
                    '<input class="form-control" required  type="text" name="link[]" placeholder="Enter Redirection link" >' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $('.section-div').append(div_val);
            });


            $(".add_new3").click(function(e) {
                var rand = Math.random().toString(36).slice(2);
                var div_val = '<div class="' + rand + '">' +
                    '<div class="row">' +
                    '<div class="col-md-12 mt-2">' +
                    '<label class="form-label">Question</label>' +
                    '<textarea class="form-control" required  type="question" name="question[]" placeholder="Enter Question" ></textarea>' +
                    '</div>' +
                    '<div class="col-md-12 mt-2">' +
                    '<label class="form-label">Answer</label>' +
                    '<textarea class="form-control" required type="text" name="answer[]" placeholder="Enter Answer" ></textarea>' +
                    '</div>' +
                    '<div class="col-md-12 mt-2">' +
                    '<label class="form-label">Note</label>' +
                    '<i class="float-end bi bi-trash remove-div" data-val="' + rand + '"></i>' +
                    '<textarea class="form-control"  type="text" name="note[]" placeholder="Enter Note" ></textarea>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $('.section-div3').append(div_val);
            });
            $('body').on('click', '.remove-div', function() {
                var class_val = $(this).data('val');
                $('.' + class_val).remove();
            });

            $('body').on('click', '.remove-div3', function() {
                var class_val = $(this).data('val');
                $('.' + class_val).remove();
            });


            $('body').on('change', '.year_select', function() {
                var year = $(this).val();
                $.ajax({
                    url: "{{ kpsc_cms('daily-ca/fetch-months') }}",
                    cache: false,
                    data: {
                        year: year
                    },
                    success: function(html) {
                        $(".month_select").html(html);
                    }
                });
            });

            $('body').on('change', '.month_select', function() {
                var month = $(this).val();
                var year = $('.year_select').val();
                $.ajax({
                    url: "{{ kpsc_cms('daily-ca/fetch-months-based') }}",
                    cache: false,
                    data: {
                        year: year,
                        month: month
                    },
                    success: function(html) {
                        $(".monthly_based").html(html);
                    }
                });
            });

            $('body').on('click', '.edit_linktype', function() {
                var id = $(this).data('id');
                var year = $(this).data('year');
                var month = $(this).data('month');
                var title = $(this).data('title');
                var link = $(this).data('link');
                $('#edit_linktype .id').val(id);
                $('#edit_linktype .year').val(year);
                $('#edit_linktype .month').val(month);
                $('#edit_linktype .title').val(title);
                $('#edit_linktype .link').val(link);

                $('#edit_linktype').modal('show');
            });
            $('body').on('click', '.edit_pdftype', function() {
                var id = $(this).data('id');
                var year = $(this).data('year');
                var month = $(this).data('month');
                var title = $(this).data('title');

                $('#edit_pdftype .id').val(id);
                $('#edit_pdftype .year').val(year);
                $('#edit_pdftype .month').val(month);
                $('#edit_pdftype .title').val(title);

                $('#edit_pdftype').modal('show');

            });
            $('body').on('click', '.edit_daytype', function() {
                var id = $(this).data('id');
                var year = $(this).data('year');
                var month = $(this).data('month');
                var day = $(this).data('day');
                var question = $(this).data('question');
                var answer = $(this).data('answer');
                var note = $(this).data('note');

                $('#edit_daytype .id').val(id);
                $('#edit_daytype .year').val(year);
                $('#edit_daytype .month').val(month);
                $('#edit_daytype .day').val(day);
                $('#edit_daytype .question').val(question);
                $('#edit_daytype .answer').val(answer);
                $('#edit_daytype .note').val(note);

                $('#edit_daytype').modal('show');
            });
        });
    </script>
@endsection
