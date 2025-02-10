 
 <!-- Course list table START -->
            <div class="table-responsive border-0">
                
              
                <div class="card bg-light rounded">
                  <div class="card-body">
                        <table class="table table-dark-gray table-bordered align-middle p-4 mb-0 table-hover">
                            <tr>
                                <th scope="col" class="border">Exam Title</th>
                                <th scope="col" class="border">{{$date_list->examtitle}}</th>
                                <th scope="col" class="border"> Total Participants</th>
                                <th scope="col" class="border">{{$attened}}</th>
                            </tr>
                            <tr>
                                <th scope="col" class="border">Exam Started</th>
                                <th scope="col" class="border">{{date('d-M-Y => h:i a',strtotime($date_list->started_at))}}</th>
                                <th scope="col" class="border">Exam Ended</th>
                                <th scope="col" class="border">{{date('d-M-Y =>  h:i a',strtotime($date_list->ended_at))}}</th>
                            </tr>
                          
                            <tr>
                                <th colspan="4">
                                    
                                    
                                    
                                    <a  href="#" data-date="{{$date_list->exam_date}}"  data-started="{{ date('Y-m-d\TH:i', strtotime($date_list->started_at)) }}" 
                                        data-ended="{{ date('Y-m-d\TH:i', strtotime($date_list->ended_at)) }}"  data-id="{{$date_list->id}}"  data-title="{{$date_list->examtitle}}" data-subject="{{$date_list->subject}}" class="btn btn-info mb-2 exam_details_edit btn-sm float-left">
                                         <i class="bi bi-pencil"></i> 
                                        Edit Exam Details
                                    </a>
                                    
                                    <a target="_new" href="{{url('kpsc/daily-exam/'.$date_list->exam_date.'/'.$date_list->id.'/leaderboard')}}" class="btn btn-sm btn-primary mb-2 float-left">
                                         <i class="bi bi-bar-chart"></i> 
                                        View Leaderboard
                                    </a>
                                    
                                    <a target="_new2" href="{{url('kpsc/daily-exam/'.$date_list->exam_date.'/'.$date_list->id)}}" class="btn btn-sm btn-warning mb-2 float-left">
                                         <i class="bi bi-eye"></i> 
                                        View Exam Page
                                    </a>
                                    
                                    <a  onclick="return confirm('Are you sure can`t retrive data?')" href="{{kpsc_cms('daily-exam/delete-all/'.$date_based[0]->exam_id)}}" class="btn btn-danger btn-sm mb-2 float-left"><i class="fa fa-trash"></i> Delete Exam With All Questions</a> 
                                    
                                   <a  onclick="return confirm('Are you sure can`t retrive data?')" href="{{kpsc_cms('daily-exam/clear-leaderboard/'.$date_based[0]->exam_id)}}" class="btn btn-danger btn-sm mb-2 float-left"><i class="fa fa-trash"></i> Clear Leaderboard</a> 
                                  
                                   
                                   
                                </th>
                               
                            </tr>
                        </table>
                  </div>
                </div>
                
                
                @if($date_based->count() > 0)
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0 rounded-start">No</th>
                            <th scope="col" class="border-0">Question</th>
                            <th scope="col" class="border-0">Answer</th>
                            <th scope="col" class="border-0">Options</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                        
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                        @php $i=1; @endphp
                        @foreach ($date_based as $list)
                        <!-- Table item -->
                        <tr>
                            <!-- Table data -->
                            <td>
                                {{$i++}}
                            </td>
                            <!-- Table data -->
                            <td style="white-space: pre-wrap;">
                                {!! $list->question !!}
                            </td>
                            <!-- Table data -->
                            <td>
                                {{$list->currect_ans}}
                            </td>
                            <!-- Table data -->
                            <td>
                                {{$list->option_1}}<br>
                                {{$list->option_2}}<br>
                                {{$list->option_3}}<br>
                                {{$list->option_4}}
                            </td>
                            
                            <!-- Table data -->
                            <td>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit" aria-label="Edit"   href="#" data-question="{!! $list->question !!}" data-answer="{{$list->currect_ans}}" data-option1="{{$list->option_1}}" data-option2="{{$list->option_2}}" data-option3="{{$list->option_3}}" data-option4="{{$list->option_4}}"  data-id="{{$list->id}}"  class="btn btn-sm btn-info btn-circle exam_question_edit"><i class="bi bi-pencil "></i>
                                </a>
                                
                                <a data-bs-toggle="tooltip"   onclick="return confirm('Are you sure can`t retrive data?')" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"  href="{{kpsc_cms('daily-exam/delete/'.$list->id)}}" data-id="{{$list->id}}" class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash "></i></a>
                            </td
                            
                        </tr>
                        @endforeach
                        
                    </tbody>
                    <!-- Table body END -->
                </table>
                @else
                    <div class="text-center">
                        <h3>Data Not Found</h3>
                    </div>
                @endif
            </div>
            <!-- Course list table END -->
            
            <!-- Button trigger modal -->


            
            