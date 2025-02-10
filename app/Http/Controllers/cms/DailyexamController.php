<?php

namespace App\Http\Controllers\cms;

use App\Models\DailyExam;
use App\Models\DailyExamattempt;
use App\Models\SampleLogin;
use App\Models\DailyExamdetails;
use App\Models\KpscSubject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Illuminate\Support\Str;

class DailyexamController extends Controller
{
    public function daily_exams(){
        $exam_titles = DailyExam::where('section','Daily Exam')->groupBy('examtitle')->get('examtitle');
        // $exam_subjects = DailyExam::groupBy('subject')->get('subject');
        $exam_subjects = KpscSubject::where('status','show')->orderby('position')->get();

        return view('cms.daily-exam',compact('exam_titles','exam_subjects'));
    }
    
    public function daily_exams_store(Request $request){
        
        if(count($request->question) >0){
            
            $new_one = DailyExam::where('exam_date',$request->exam_date)
                                ->where('subject',$request->subject)
                                ->where('examtitle',$request->examtitle)
                                ->where('section','Daily Exam')
                                ->first();
            if(!$new_one){
            $new_one  = new DailyExam();
            }
            $new_one->section = "Daily Exam";
            $new_one->exam_date = date('Y-m-d',strtotime($request->exam_date));
            $new_one->started_at = date('Y-m-d H:i:s',strtotime($request->exam_started));
            $new_one->ended_at = date('Y-m-d H:i:s',strtotime($request->exam_ended));
            $new_one->subject = $request->subject;
            $new_one->examtitle = $request->examtitle;
            
            try{
                 $new_one->save();
                 
                 for($i=0;$i<count($request->question);$i++){
                     $new_two = new DailyExamdetails();
                     $new_two->exam_id = $new_one->id;
                     $new_two->question = $request->question[$i];
                     $new_two->option_1 = $request->option1[$i];
                     $new_two->option_2 = $request->option2[$i];
                     $new_two->option_3 = $request->option3[$i];
                     $new_two->option_4 = $request->option4[$i];
                     $new_two->currect_ans = $request->answer[$i];
                     $new_two->save();
                 }
                 
                return redirect()->back()->with('message','Data added Successfully');
            }
            catch(Exception $e){
                dd($e);
            }
        }
        else{
            return redirect()->back()->with('message','Error!');
        }
       
    }
    public function daily_exams_show(){
        $date_list = DailyExam::where('section','Daily Exam')->orderBy('exam_date','desc')->get();
        $exam_titles = DailyExam::where('section','Daily Exam')->groupBy('examtitle')->get('examtitle');
        // $exam_subjects = DailyExam::groupBy('subject')->get('subject');
        $exam_subjects = KpscSubject::where('status','show')->orderby('position')->get();

        return view('cms.daily-exam-show',compact('date_list','exam_titles','exam_subjects'));
         
    }
    
    
    public function exam_details_update(Request $request){
        
        $exam_date    = $request->exam_date;
        $exam_started = $request->exam_started;
        $exam_ended   = $request->exam_ended;
        $examtitle    = $request->examtitle;
        $subject      = $request->subject;
        
        $new_one = DailyExam::where('id',$request->exam_id)->first() ?? abort(404);
            
            
            $new_one->section = "Daily Exam";
            $new_one->exam_date = date('Y-m-d',strtotime($exam_date));
            $new_one->started_at = date('Y-m-d H:i:s',strtotime($request->exam_started));
            $new_one->ended_at = date('Y-m-d H:i:s',strtotime($request->exam_ended));
            $new_one->subject = $subject;
            $new_one->examtitle = $examtitle;
            
            try{
                 $new_one->save();
            }
            catch(Exception $e){
                dd($e);
            }
            
            return redirect()->back()->with('message','Data Updated Successfully');
    }

    
    public function daily_exams_date_based(Request $request){
       
        $date_list  = DailyExam::where('section','Daily Exam')->where('exam_date',$request->date)->where('subject',$request->subject)->first() ?? abort(404);
        
        $date_based = DailyExamdetails::where('exam_id',$date_list->id)->get() ?? abort(404);
        $attened    = DailyExamattempt :: where('exam_id',$date_list->id)->count() ?? abort(404);
        return view('cms.daily-exam-show-date_based',compact('date_based','date_list','attened'));
    }
    
    public function daily_exams_edit(){
        
    }
    
    public function date_depend_subjects(Request $request){
        $exam_subjects  = DailyExam::leftJoin('kpsc_subjects','kpsc_subjects.id','kpsc_daily_exams.subject')
                                ->where('section','Daily Exam')
                                ->where('exam_date',$request->date)
                                ->select('kpsc_subjects.*')
                                ->get();
                                

         $listing="";                       
        foreach ($exam_subjects as $list)
        {
            $listing .= '<option value="'.$list->id.'">'.
                        $list->subject_title
                    .'</option>';
        }
               
        return   $listing;              
        
    }
    
    public function daily_exams_update($id,Request $request){
      
        $new_two = DailyExamdetails::where('id',$id)->first() ?? abort(404);
        $new_two->question = $request->question;
        $new_two->option_1 = $request->option1;
        $new_two->option_2 = $request->option2;
        $new_two->option_3 = $request->option3;
        $new_two->option_4 = $request->option4;
        $new_two->currect_ans = $request->answer;
        $new_two->save();

        return redirect()->back()->with('message','Data Updated Successfully');
        
    }
    
    public function daily_exams_delete($id){
        
        $date_based = DailyExamdetails::where('id',$id)->first();
        $date_based->delete();
        
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    
    public function daily_exams_delete_all($id){
        DailyExam::leftJoin('kpsc_daily_exams_details','kpsc_daily_exams_details.exam_id','kpsc_daily_exams.id')->where('kpsc_daily_exams_details.exam_id',$id)->where('kpsc_daily_exams.id',$id)->delete();
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    
    
    public function clear_leaderboard($id){
        
        $date_based = DailyExamattempt::where('exam_id',$id)->delete();
        
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    
    
}