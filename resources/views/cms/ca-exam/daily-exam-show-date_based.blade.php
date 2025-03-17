 @foreach ($date_list ?? [] as $item)
     <!-- Course list table START -->
     <div class="table-responsive border-0">
         <div class="card bg-light rounded">
             <div class="card-body">
                 <table class="table table-dark-gray table-bordered align-middle p-4 mb-0 table-hover">
                     <tr>
                         <th scope="col" class="border">Exam Title</th>
                         <th scope="col" class="border">Day {{ $item->level }}</th>
                         <th scope="col" class="border"> Total Participants</th>
                         <th scope="col" class="border">
                             {{ $item->exam_attend_list ? $item->exam_attend_list->count() : 0 }}</th>
                             <th scope="col" class="border">Exam Started</th>
                         <th scope="col" class="border">
                             {{ date('d-M-Y => h:i a', strtotime($item->started_at)) }}
                         </th>
                     </tr>
                     

                     <tr align="right">
                         <th colspan="6" align="right">
                             <a href="{{ kpsc_cms('ca-daily-exam/edit/' . $item->id) }}"
                                 class="btn btn-info mb-2  btn-sm float-left">
                                 <i class="bi bi-pencil"></i>
                                 Edit Exam Details
                             </a>

                           
                             <a onclick="return confirm('Are you sure can`t retrive data?')"
                                 href="{{ kpsc_cms('ca-daily-exam/delete-all/' . $item->id) }}"
                                 class="btn btn-danger btn-sm mb-2 float-left"><i class="fa fa-trash"></i> Delete Exam
                                 With
                                 All Questions</a>

                             <a onclick="return confirm('Are you sure can`t retrive data?')"
                                 href="{{ kpsc_cms('ca-daily-exam/clear-leaderboard/' . $item->id) }}"
                                 class="btn btn-danger btn-sm mb-2 float-left"><i class="fa fa-trash"></i> Clear
                                 Leaderboard</a>
                         </th>

                     </tr>
                 </table>
             </div>
         </div>
     </div>
 @endforeach
