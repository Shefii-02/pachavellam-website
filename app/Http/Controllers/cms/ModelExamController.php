<?php

namespace App\Http\Controllers\cms;

use App\Models\DailyExam;
use App\Models\DailyExamattempt;
use App\Models\SampleLogin;
use App\Models\ModelExamDetails; 
use App\Models\ModelExamAttempt;
use App\Models\KpscSubject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Illuminate\Support\Str;
use Storage;



class ModelExamController extends Controller
{
    public function model_exams(){
        $exam_titles = DailyExam::where('section','Model Exam')->groupBy('examtitle')->get('examtitle');
        // $exam_subjects = DailyExam::groupBy('subject')->get('subject');
        $exam_subjects = KpscSubject::where('status','show')->orderby('position')->get();

        return view('cms.model-exam',compact('exam_titles','exam_subjects'));
    }
    
    public function model_exams_store(Request $request){
        
     
        if($request->hasFile('qstn_file')){   
            $name1 = Str::random(40).'.pdf';
            $image1 = file_get_contents($request->file('qstn_file'));
         
                    Storage::put('/public/model-exam/'.$name1, $image1);
           
           
        }
        if($request->hasFile('ans_file')){
            $name2 = Str::random(40).'.pdf';
            $image2 = file_get_contents($request->file('ans_file'));

            Storage::put('/public/model-exam/'.$name2, $image2);

        }
      
            $new_one  = new DailyExam();
            $new_one->section    = "Model Exam";
            $new_one->exam_date  =  date('Y-m-d',strtotime($request->exam_date));
            $new_one->started_at =  date('Y-m-d H:i:s',strtotime($request->exam_started));
            $new_one->ended_at   =  date('Y-m-d H:i:s',strtotime($request->exam_ended));
            $new_one->subject    = $request->subject ?? null;
            $new_one->examtitle  = $request->examtitle;
            try{
                $new_one->save();
                 
                $new_two                = new ModelExamDetails();
                $new_two->exam_id	= $new_one->id;
                $new_two->qp_file	    = $name1 ?? null;
                $new_two->answer_file  = $name2 ?? null;
                $new_two->save();
                
                 
                return redirect()->back()->with('message','Data added Successfully');
            }
            catch(Exception $e){
                dd($e);
            }
        
       
    }
    public function model_exams_show(){
        $date_list = DailyExam::where('section','Model Exam')->select('exam_date')->groupBy('exam_date')->orderBy('exam_date','desc')->get();
        $exam_titles = DailyExam::where('section','Model Exam')->groupBy('examtitle')->get('examtitle');

        return view('cms.model-exam-show',compact('date_list','exam_titles'));
         
    }
    
    
    public function exam_details_update(Request $request){
 
        $exam_date    = $request->exam_date;
        $exam_started = $request->exam_started;
        $exam_ended   = $request->exam_ended;
        $examtitle    = $request->examtitle;
        $subject      = $request->subject;
        dd($request->all());
        $new_one = DailyExam::where('id',$request->exam_id)->where('section','Model Exam')->first() ?? abort(404);
            
            $new_one->exam_date  = date('Y-m-d',strtotime($exam_date));
            $new_one->started_at = date('Y-m-d H:i:s',strtotime($request->exam_started));
            $new_one->ended_at   = date('Y-m-d H:i:s',strtotime($request->exam_ended));
            $new_one->examtitle  = $examtitle;
            
            try{
                 $new_one->save();
                 
                  $new_two = ModelExamDetails::where('exam_id', $new_one->id)->first();

                    if ($request->hasFile('qstn_file')) {   
                        // Generate a random name for the new file
                        $name1 = Str::random(40) . '.pdf'; 
                    
                        // Remove the old file if it exists
                        if (Storage::exists('/public/model-exam/' . $new_two->qp_file)) {
                            Storage::delete('/public/model-exam/' . $new_two->qp_file);
                        }
                    
                        // Get the content of the new file
                        $image1 = file_get_contents($request->file('qstn_file'));
                    
                        // Store the new file
                        Storage::put('/public/model-exam/' . $name1, $image1);
                    
                        // Update the model with the new file name
                        $new_two->qp_file = $name1;
                    }
                    
                    if ($request->hasFile('ans_file')) {
                        // Generate a random name for the new file
                        $name2 = Str::random(40) . '.pdf';
                    
                        // Remove the old file if it exists
                        if (Storage::exists('/public/model-exam/' . $new_two->answer_file)) {
                            Storage::delete('/public/model-exam/' . $new_two->answer_file);
                        }
                    
                        // Get the content of the new file
                        $image2 = file_get_contents($request->file('ans_file'));
                    
                        // Store the new file
                        Storage::put('/public/model-exam/' . $name2, $image2);
                    
                        // Update the model with the new file name
                        $new_two->answer_file = $name2;
                    }
                    
                    // Save the updated model
                    $new_two->save();
        
                   
            }
            catch(Exception $e){
                dd($e);
            }
            
            return redirect()->back()->with('message','Data Updated Successfully');
    }

    
    public function model_exams_date_based(Request $request){
    
        $dateList  = DailyExam::with('model_exam_details_one','model_exam_attened')->where('section','Model Exam')->where('exam_date',$request->date)->get() ?? abort(404);
       
        return view('cms.model-exam-show-date_based',compact('dateList'));
    }
    
    public function model_exams_edit(){
        
    }
    
    public function date_depend_subjects(Request $request){
        $exam_subjects  = DailyExam::leftJoin('kpsc_subjects','kpsc_subjects.id','kpsc_daily_exams.subject')
                                ->where('section','Model Exam')
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
    
    public function model_exams_update($id,Request $request){
      
        $new_two = ModelExamDetails::where('id',$id)->first() ?? abort(404);
        $new_two->question = $request->question;
        $new_two->option_1 = $request->option1;
        $new_two->option_2 = $request->option2;
        $new_two->option_3 = $request->option3;
        $new_two->option_4 = $request->option4;
        $new_two->currect_ans = $request->answer;
        $new_two->save();

        return redirect()->back()->with('message','Data Updated Successfully');
        
    }
    
    public function model_exams_delete($id){
        $date_based = DailyExam::where('id',$id)->first();
        $new_two = ModelExamDetails::where('exam_id',$date_based->id)->first();
        if($new_two){
        
            if (Storage::exists('/public/model-exam/' . $new_two->qp_file)) {
                Storage::delete('/public/model-exam/' . $new_two->qp_file);
            }

            // Remove the old file if it exists
            if (Storage::exists('/public/model-exam/' . $new_two->answer_file)) {
                Storage::delete('/public/model-exam/' . $new_two->answer_file);
            }
            ModelExamDetails::where('exam_id',$date_based->id)->delete();
        }
        
        ModelExamAttempt::where('exam_id',$date_based->id)->delete();
        DailyExam::where('id',$id)->delete();
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    
    public function model_exams_delete_all($id){
        DailyExam::leftJoin('kpsc_daily_exams_details','kpsc_daily_exams_details.exam_id','kpsc_daily_exams.id')->where('kpsc_daily_exams_details.exam_id',$id)->where('kpsc_daily_exams.id',$id)->delete();
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    
    
    public function clear_leaderboard($id){
        
        $date_based = DailyExamattempt::where('exam_id',$id)->delete();
        
        return redirect()->back()->with('message','Data deleted Successfully');
    }
    
    
}