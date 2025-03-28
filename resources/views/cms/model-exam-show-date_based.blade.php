 <!-- Course list table START -->
 @foreach ($dateList as $date_list)
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
                             <a href="{{ kpsc_cms('daily-exam/edit/' . $date_list->id) }}"
                                 class="btn btn-info mb-2  btn-sm float-left">
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
