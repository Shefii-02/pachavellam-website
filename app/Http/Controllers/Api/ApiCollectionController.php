<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Models\{Bulletin,Capsule,DailyExam,DailyExamdetails,DailyExamattempt,ModelExamDetails,ModelExamAttempt,CurrentAffairs};
use App\Models\{Banner,SpecialTab,Notification,KpscSubject,Syllabus,PrevQuestion,Pscresults,Pscnews,Timetable,Material};
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Storage;
use Hash;
use DB;
use Carbon\Carbon;
use App\Http\Resources\UserResources;

use Illuminate\Support\Str;
use App\Http\Resources\{BulletinResources,FeedResources,DailyExamResources,DailyExamDetailsResources,DailyExamLeaderboardResources};
use App\Http\Resources\{ModelExamResources,ModelExamDetailsResources,ModelExamLeaderboardResources,BannerResources,PrevQyestionPapperResources};
use App\Http\Resources\{ResultResources,SyllabusResources,SubjectCategoryResources};

class ApiCollectionController extends Controller

{
    public function BulletIn(Request $request)
    {
        $bulletin = Bulletin::orderBy('month_year','DESC')->orderBy('issue','DESC')->get();
        return response()->json(['data'=> BulletinResources::collection($bulletin),'status' => 200]);
    }
    
    public function FeedList(Request $request){
        $user_id = $request->user_id;
         $capsule_list = Capsule::with('author','comments','comments.author','likes')->orderby('created_at','desc')->get();
         return response()->json([
        'data' => FeedResources::collection($capsule_list)->additional(['user_id' => $user_id]), 
        'status' => 200
    ]);
    
    }
    // public function FeedList(Request $request)
    // {
    //     // Retrieve the page number and limit from the request
    //     $page = $request->input('page', 1); // Default to page 1 if not provided
    //     $limit = $request->input('limit', 20); // Default to 20 items per page if not provided
    
    //     // Fetch paginated results
    //     $capsule_list = Capsule::with('author', 'comments', 'comments.author', 'likes')
    //                           ->orderby('created_at', 'desc')
    //                           ->paginate($limit, ['*'], 'page', $page);
    
    //     // Return paginated results
    //     return response()->json([
    //         'data' => FeedResources::collection($capsule_list->items()),
    //         'current_page' => $capsule_list->currentPage(),
    //         'total_pages' => $capsule_list->lastPage(),
    //         'status' => 200
    //     ]);
    // }

    
    public function DailyExams(Request $request){
        $date_list = DailyExam::orderBy('exam_date','DESC')->where('section','Daily Exam')->get();
        return response()->json(['data'=> DailyExamResources::collection($date_list),'status' => 200]);
    }
    public function DailyExamSingle(Request $request){
        $date_details = DailyExamdetails::where('exam_id',$request->exam_id)->get();
        $exam = DailyExam::where('id',$request->exam_id)->first();
        
        return response()->json(['data'=> DailyExamDetailsResources::collection($date_details),'exam_ended' => $exam->ended_at,'status' => 200]);
    }
    public function DailyExamLeaderboard(Request $request){
        $exam_attended = DailyExamattempt::with('user')->where('exam_id',$request->exam_id)->get();
        return response()->json(['data'=> DailyExamLeaderboardResources::collection($exam_attended),'status' => 200]);
    }
   
    public function ModelExams(Request $request){
        $date_list = DailyExam::with('model_exam_details')->orderBy('exam_date','DESC')->where('section','Model Exam')->get();
        // return $date_list;
        return response()->json(['data'=> ModelExamResources::collection($date_list),'status' => 200]);
    }
    public function ModelExamSingle(Request $request){
        $date_details = ModelExamDetails::where('exam_id',$request->exam_id)->get();
        return response()->json(['data'=> ModelExamDetailsResources::collection($date_details),'status' => 200]);
    }
    public function ModelExamLeaderboard(Request $request){
         date_default_timezone_set('Asia/Kolkata');
         $exm_details = DailyExam::where('id',$request->exam_id)->first();
        if($exm_details){

          if($exm_details->section == 'Daily Exam'){
               $exam_attended = DailyExamattempt::with('user')->where('exam_id',$request->exam_id)->where('attend_ended_at','<=',$exm_details->ended_at)->orderBy('total_mark','DESC')->orderBy('attend_ended_at','ASC')->get();
          }
          else{
           $exam_attended = ModelExamAttempt::with('user')->where('exam_id',$request->exam_id)->where('answer_uploaded','<=',$exm_details->ended_at)->orderBy('total_mark','DESC')->orderBy('answer_uploaded','ASC')->get();
        
          }

            $leaderboardResources = $exam_attended->map(function($item, $index) {
                    return new ModelExamLeaderboardResources($item, $index + 1);
                });
        
        return response()->json(['data'=> $leaderboardResources,'status' => 200]);
        }
    }
    
    
    public function DailyCADates(Request $request){

        $monthMapping = "CASE 
                            WHEN month = 'January' THEN 1
                            WHEN month = 'February' THEN 2
                            WHEN month = 'March' THEN 3
                            WHEN month = 'April' THEN 4
                            WHEN month = 'May' THEN 5
                            WHEN month = 'June' THEN 6
                            WHEN month = 'July' THEN 7
                            WHEN month = 'August' THEN 8
                            WHEN month = 'September' THEN 9
                            WHEN month = 'October' THEN 10
                            WHEN month = 'November' THEN 11
                            WHEN month = 'December' THEN 12
                            ELSE 13
                        END";

        $currentAffairs = CurrentAffairs::select('year', 'day', 'month')
            ->orderBy('year', 'DESC')
            ->orderBy(DB::raw($monthMapping))
            ->orderBy('day', 'ASC')
            ->get();

        $groupedData = $currentAffairs->groupBy(['year', 'month', 'day']);

        $response = [];
        foreach ($groupedData as $year => $months) {
            $yearData = ['year' => $year, 'months' => []];
            foreach ($months as $month => $days) {
                $monthData = ['month' => $month, 'days' => []];
                foreach ($days as $day => $records) {
               //'records' => $records
                    $dayData = ['day' => $day,'month' => $month, 'year' => $year ];
                    $monthData['days'][] = $dayData;
                }
                $yearData['months'][] = $monthData;
            }
            $response[] = $yearData;
        }


        return response()->json(['data'=> $response,'status' => 200]);
    }

    public function DailyCA(Request $request){
        $daily_ca_day_single = CurrentAffairs::where('year',$request->year)->where('month',$request->month)->where('day',$request->day)->get();
        return response()->json(['data'=> $daily_ca_day_single,'status' => 200]);
    }
    
    public function Stories(Request $request){
        $stories         = Banner::where('status',1)->orderBy('created_at','DESC')->get();
        
        return response()->json(['data'=> BannerResources::collection($stories),'status' => 200]);
    }
    public function Banners(Request $request){
        $banner         = SpecialTab::where('status',1)->orderby('position')->get();
        
        return response()->json(['data'=> BannerResources::collection($banner),'status' => 200]);
    }
    public function Notifications(Request $request){
       $noftify        = Notification::orderby('created_at','desc')->get();
       return response()->json(['data'=> $noftify,'status' => 200]);
    }
    
    public function CategoriesList(Request $request)
    {
        $subjects = KpscSubject::with('child')
            ->where('status', 'show')
            ->where('type', 'parent')
            ->orderBy('position')
            ->get();
    
        return response()->json([
            'data' => SubjectCategoryResources::collection($subjects),
            'status' => 200,
        ]);
    }
    
    public function PreviousQuestionPappers(Request $request)
    {
        $prev_paper = PrevQuestion::all();
        $groupedData = $prev_paper->groupBy(['category', 'subcategory']);
        $response = [];
        foreach ($groupedData as $category => $subcategories) {
            $categoryData = ['category' => $category, 'subcategories' => []];
            foreach ($subcategories as $subcategory => $records) {
                $subcategoryData = [
                    'subcategory' => $subcategory,
                    'records' => PrevQyestionPapperResources::collection($records)
                ];
                $categoryData['subcategories'][] = $subcategoryData;
            }
            $response[] = $categoryData;
        }

        return response()->json(['data' => $response, 'status' => 200]);
    }
    

    public function ResultsRequesting(Request $request){

        $results = Pscresults::orderBy('created_at', 'desc')->get();
        $groupedData = $results->groupBy('type');
        $response = [];
        foreach ($groupedData as $type => $records) {
            $typeData = [
                'type' => $type,
                'records' => SyllabusResources::collection($records)
            ];
            $response[] = $typeData;
        }
        return response()->json(['data'=> $response,'status' => 200]);   
    }

    public function SyllabusRequesting(Request $request)
    {
        $syllabus = Syllabus::where('type','!=','EXAM CALENDAR')->orderBy('created_at', 'desc')->get();
        $groupedData = $syllabus->groupBy('type');
        $response = [];
        foreach ($groupedData as $type => $records) {
            $typeData = [
                'type' => $type,
                'records' => SyllabusResources::collection($records)
            ];
            $response[] = $typeData;
        }

        return response()->json(['data' => $response, 'status' => 200]);
    }
    
    public function ExamCalender(Request $request){
        
        $time_table = Syllabus::where('type','EXAM CALENDAR')->orderBy('created_at', 'desc')->get();
        $groupedData = $time_table->groupBy('category_no');
        $response = [];
        foreach ($groupedData as $type => $records) {
            $typeData = [
                'type' => $type,
                'records' => SyllabusResources::collection($records)
            ];
            $response[] = $typeData;
        }

        return response()->json(['data' => $response, 'status' => 200]);
    }
    
    public function News(Request $request){
        
    }
    
    public function GameLevels(Request $request){
        
    }
    
    public function refferalFriends(Request $request){
        
        $users = User::get();
            return response()->json(['data' => UserResources::collection($users), 'status' => 200]);
    }

    
}