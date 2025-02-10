<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Notification;
use App\Models\WhatsNew;
use App\Models\SpecialTab;
use App\Models\Ourvideos;
use App\Models\CategoryPaid;
use App\Models\CategoryFree;
use App\Models\SpecialVideo;
use App\Models\Bulletin;
use App\Models\SubcategoryFree;
use App\Models\ClassFree;
use App\Models\ClassPaid;
use App\Models\SubcategoryPaid;
use App\Models\NewmodalQa;
use App\Models\Syllabus;
use App\Models\Material;
use App\Models\PrevQuestion;
use App\Models\Pscresults;
use App\Models\CurrentAffairs;
use App\Models\Pscnews;
use App\Models\Capsule;
use App\Models\CapsuleComment;
use App\Models\CapsuleLike;
use App\Models\Timetable;
use App\Models\DailyBucket;
use App\Models\DailyExam;
use App\Models\DailyExamdetails;
use App\Models\User;
use App\Models\ExamDiscussion;
use App\Models\KpscSubject;
use Storage;
use Hash;
use Illuminate\Support\Str;
use App\Models\DailyExamattempt;
use Validator;
use App\Models\KpscSubjectActivities;
use App\Models\KpscActivities;
use App\Models\ModelExamAttempt;
use Carbon\Carbon;


class HomeController extends Controller
{
   
    public function homepage(){
    
        $banner         = Banner::where('status',1)->orderby('position')->get();
        $noftify        = Notification::orderby('created_at','desc')->get();
        $whatsnew       = WhatsNew::where('status',1)->orderby('position')->get();
        $specialtab     = SpecialTab::where('status',1)->orderby('position')->get();
        $ourvideos      = Ourvideos::where('status',1)->orderby('position')->get();
        $category_paid  = CategoryPaid::where('status',1)->orderby('position')->get();
        // $category_free  = CategoryFree::where('status',1)->orderby('position')->get();
        $category_free  = KpscSubject::where('status','show')->where('type','parent')->orderby('position')->get();
        $specialvideos  = SpecialVideo::where('status',1)->whereNull('section')->orderby('position')->get();
        $psc_news       = Pscnews::where('status',1)->orderby('id','desc')->limit(10)->get();
        
        date_default_timezone_set('Asia/Kolkata');
        
        $next_exam = DailyExam::where('section','Daily Exam')->where('ended_at','>',date('Y-m-d H:i:s'))->orderby('started_at')->first();
        
        return view('kpsc.index',compact('banner', 'noftify', 'whatsnew', 'specialtab', 'ourvideos', 'category_free', 'category_paid','specialvideos','psc_news','next_exam'));
    }
    
    public function homepage_new(){
        $banner         = Banner::where('status',1)->orderby('position')->get();
        $noftify        = Notification::orderby('created_at','desc')->get();
        $whatsnew       = WhatsNew::where('status',1)->orderby('position')->get();
        $specialtab     = SpecialTab::where('status',1)->orderby('position')->get();
        $ourvideos      = Ourvideos::where('status',1)->orderby('position')->get();
        $category_paid  = CategoryPaid::where('status',1)->orderby('position')->get();
        $category_free  = KpscSubject::where('status','show')->orderby('position')->get();
        $specialvideos  = SpecialVideo::where('status',1)->whereNull('section')->orderby('position')->get();
        $psc_news       = Pscnews::where('status',1)->orderby('id','desc')->limit(10)->get();
        
        date_default_timezone_set('Asia/Kolkata');
        
        $next_exam = DailyExam::where('section','Daily Exam')->where('ended_at','>',date('Y-m-d H:i:s'))->orderby('started_at')->first();
        
        return view('kpsc.new-index',compact('banner', 'noftify', 'whatsnew', 'specialtab', 'ourvideos', 'category_free', 'category_paid','specialvideos','psc_news','next_exam'));
    }
    public function subject_category_related($category_name){
     
        $title = "Kerala PSC ".$category_name ;
        $redirection = url('kpsc');
        
        $subject_dtls      = KpscSubject::where('slug_name',$category_name)->first();
        $subcategory_check  = KpscSubject::where('parent_id',$subject_dtls->id)->orderby('position')->get();
        // $category_name      = $subject_dtls->category_name;
        if(count($subcategory_check)>0){
            return view('kpsc.sub-subject',compact('subcategory_check','category_name','title','redirection'));
        }
        else{
            // $free_classlist = ClassFree::where('category_id',$subject_dtls->id)->orderby('position')->get();
// free_classlist
            $kpsc_subject_activity = KpscSubjectActivities::leftJoin('kpsc_activities','kpsc_activities.id','kpsc_subject_activities.activity_id')
                                ->where('kpsc_subject_activities.subject_id',$subject_dtls->id)
                                ->where('kpsc_activities.status','show')
                                ->select('kpsc_activities.*')->get();
                             
            $kpsc_activity = KpscActivities::where('kpsc_activities.status','show')->get();
            
                                
            return view('kpsc.subject',compact('category_name','title','redirection','kpsc_subject_activity','kpsc_activity'));
           
        }

    }
    public function sub_subject_category_related($category_name,$sub_category){
   
        $title = "Kerala PSC ".$sub_category ;
        $redirection = url('kpsc/subject/'.$category_name);
        $kpsc_activity = KpscActivities::where('kpsc_activities.status','show')->get();
        
        $subject_dtls      = KpscSubject::where('slug_name',$category_name)->where('type','parent')->first();
     
        $sub_subject_dtls    = KpscSubject::where('parent_id',$subject_dtls->id)->where('slug_name',$sub_category)->orderby('position')->first();
           
        $kpsc_subject_activity = KpscSubjectActivities::leftJoin('kpsc_activities','kpsc_activities.id','kpsc_subject_activities.activity_id')
                                ->where('kpsc_subject_activities.subject_id',$sub_subject_dtls->id)
                                ->where('kpsc_activities.status','show')
                                ->select('kpsc_activities.*')
                                ->where('kpsc_activities.status','show')->get();

                                
        return view('kpsc.subject',compact('title','redirection','sub_category','category_name','kpsc_subject_activity','kpsc_activity')); 
    }
    
 
 
 
    

    public function explore(){
        $title = "Explore Subjects";
        $redirection = url('kpsc');
        $category_paid  = CategoryPaid::where('status',1)->orderby('position')->get();
        $category_free  = CategoryFree::where('status',1)->orderby('position')->get();
        return view('kpsc.explore',compact('category_free', 'category_paid','title','redirection'));
    }

    public function bulletin(Request $request){
         $title = "Psc Bulletin";
        $redirection = url('kpsc');
        $pscbulletin  = Bulletin::where('status',1)
                                ->orderby('month_year','desc')
                                ->orderBy('issue', 'desc')
                                ->paginate(8);
                                
        if ($request->ajax()) {
           
            return view('kpsc.psc-bulletin-load-more',compact('pscbulletin'));
        }
        
        return view('kpsc.psc-bulletin',compact('pscbulletin','title','redirection')); 


    }

    public function notify(){
        $title = "Notification";
        $redirection = url('kpsc');
        $noftify    = Notification::orderby('created_at','desc')->get();
        return view('kpsc.notify',compact('noftify','title','redirection')); 
    }

    public function freecategory_related($category_name){

        $title = ucfirst($category_name);
        $redirection = url('kpsc/explore');
        $category_free      = CategoryFree::where('name_slug',$category_name)->first();
        $subcategory_check  = SubcategoryFree::where('category_id',$category_free->id)->orderby('position')->get();
        $category_name      = $category_free->category_name;
        if(count($subcategory_check)>0){
            return view('kpsc.free_subcategory',compact('subcategory_check','category_name','title','redirection'));
        }
        else{
            $free_classlist = ClassFree::where('category_id',$category_free->id)->orderby('position')->get();

             return view('kpsc.class-list',compact('free_classlist','category_name','title','redirection'));
           
        }

    }

    public function freesubcategory_related($subcategory_name){
        $title = ucfirst($subcategory_name);
        $redirection = url('kpsc/explore');
        $subcategory_check  = SubcategoryFree::where('name_slug',$subcategory_name)->first();
        $category_free      = CategoryPaid::where('id',$subcategory_check->id)->first();
        $category_name      = $category_free->category_name;
        $free_classlist = ClassFree::where('category_id',$subcategory_check->category_id)
                                    ->where('subcategory_id',$subcategory_check->id)
                                    ->orderby('position')
                                    ->get();
        
        return view('kpsc.class-list',compact('free_classlist','category_name','title','redirection'));

    }

    public function paidcategory_related($category_name){
        $title = ucfirst($category_name);
        $redirection = url('kpsc/explore');
        $category_free      = CategoryPaid::where('name_slug',$category_name)->first();
        $subcategory_check  = SubcategoryPaid::where('category_id',$category_free->id)->orderby('position')->get();
        $category_name      = $category_free->category_name;
        if(count($subcategory_check)>0){
            return view('kpsc.paid_subcategory',compact('subcategory_check','category_name','title','redirection'));
        }
        else{
            $free_classlist = ClassPaid::where('category_id',$category_free->id)->orderby('position')->get();

             return view('kpsc.class-list',compact('free_classlist','category_name','title','redirection'));
           
        }
    }
    public function paidsubcategory_related($subcategory_name){
        $title = ucfirst($subcategory_name);
        $redirection = url('kpsc/explore');
        $subcategory_check  = SubcategoryPaid::where('name_slug',$subcategory_name)->first();
        $category_free      = CategoryPaid::where('id',$subcategory_check->id)->first();
        $category_name      = $category_free->category_name;
        $free_classlist = ClassPaid::where('category_id',$subcategory_check->category_id)
                                    ->where('subcategory_id',$subcategory_check->id)
                                    ->orderby('position')
                                    ->get();
        
        return view('kpsc.class-list',compact('free_classlist','category_name','title','redirection'));
    }

    public function question_answer(){
        $title = 'Psc New Pattern';
        $redirection = url('kpsc');
        $first  = NewmodalQa::orderby('created_at','asc')->first();
        $first = $first->id;
        $latest_date = NewmodalQa::orderby('created_at','desc')->first();
        $latest = NewmodalQa::where('created_at',$latest_date->created_at)->orderby('created_at','asc')->first();
         $latest = $latest->id;
        return view('kpsc.psc-pattern',compact('first','latest','title','redirection'));
    }

    public function question_answer_id($id){
         $title = 'Psc New Pattern';
         
        $redirection = url('kpsc/psc-new-pattern');
        
        if($id == 'all'){
            $question_list  = NewmodalQa::orderby('created_at','asc')->get();
            return view('kpsc.new-modal-qa-all',compact('title','redirection','question_list'));
        }
        else{
            $list_details  = NewmodalQa::where('id',$id)->first();
            return view('kpsc.new-modal-qa-single',compact('title','redirection','list_details','id'));
        }
        
        
        
    }
    
    public function new_modal_qa_check_answer(Request $request){
    
        $answer = $request->click_ans;
        $qstn = $request->question_no;
        
        $check = NewmodalQa::where('id',$qstn)->where('currect_ans',$answer)->first();
        
        if($check){
            return 1;
        }
        else{
            return 0;
        }
    }

    public function daily_ca(){
        $title = 'Daily Current Affairs';
        $redirection = url('kpsc');
        $daily_ca_year = CurrentAffairs::groupBy('year')->orderBy('year','desc')->get('year');
        return view('kpsc.psc-daily-ca',compact('daily_ca_year','title','redirection'));
    }

    public function daily_ca_sub($year){
        $title = 'Daily Current Affairs-'.$year;
        $redirection = url('kpsc/psc-daily-current-affairs');
        $daily_ca_month = CurrentAffairs::where('year',$year)->groupBy('month')->get('month');
        $daily_ca_month = $daily_ca_month->map(function ($item) {
            $monthNumber = date('m', strtotime($item['month']));
            $item['month_number'] = $monthNumber;
            return $item;
        });


        return view('kpsc.psc-daily-ca-month',compact('daily_ca_month','year','title','redirection'));
    }

    public function daily_ca_day($year,$month){
        $month = remove_slug($month);
        $title = 'Daily Current Affairs-'.$year.'-'.$month;
        $redirection = url('kpsc/psc-daily-current-affairs/'.$year);
        // ->groupBy('day')
        $daily_ca_day = CurrentAffairs::where('year',$year)->where('month',$month)->orderBy('created_at')->select('day','type','title','file_name')->get();
    
        $daily_ca_day = $daily_ca_day->unique(function ($item) { return $item['day'] . $item['type'] . $item['title'] . $item['file_name'];});
        
        if($daily_ca_day->where('day','<>',null)->count()==0){
            $daily_ca_day = CurrentAffairs::where('year',$year)->where('month',$month)->orderBy('created_at')->get();
        }  
        
        return view('kpsc.psc-daily-ca-day',compact('daily_ca_day','year','month','title','redirection'));
    }
    
    public function daily_ca_single_day($year,$month,$day){
        $title = 'Daily Current Affairs-'.$year.'-'.$month;
        if($month == 'march'){
            
        }
        else
        {
        $day = remove_slug($day);
        }
        $redirection = url('kpsc/psc-daily-current-affairs/'.$year.'/'.$month);
        $daily_ca_day_single = CurrentAffairs::where('year',$year)->where('month',$month)->where('day',$day)->get();
        
        return view('kpsc.psc-daily-ca-day-single',compact('daily_ca_day_single','year','month','day','title','redirection'));
    }
    
    public function psc_material(){
        $title = 'Study Material';
        $redirection = url('kpsc');
        $material_type = Material::groupBy('type')->get('type');
        return view('kpsc.psc-material',compact('material_type','title','redirection'));
    }

    public function psc_material_sub_category($sub){
        $title = 'Study Material';
        $redirection = url('kpsc/psc-material');
        $sub_category = str_replace('-',' ',$sub);
        $material_list = Material::where('type',$sub_category)->get(); 
        return view('kpsc.psc-sub-material',compact('material_list','title','redirection'));
    }

    public function psc_syllabus(){
        $title = 'Psc Syllabus';
        $redirection = url('kpsc');
        $syllabus_type = Syllabus::groupBy('type')->get('type'); 
        return view('kpsc.psc-syllabus',compact('syllabus_type','title','redirection'));
    }

    public function psc_syllabus_sub_category($sub){
         $title = 'Psc Syllabus';
        $redirection = url('kpsc/psc-syllabus');
        $sub_category = str_replace('-',' ',$sub);
        $syllabus_list = Syllabus::where('type',$sub_category)->orderBy('date','desc')->get(); 
        return view('kpsc.psc-sub-syllabus',compact('syllabus_list','title','redirection'));
    }

    public function prev_qstn_ans(){
         $title = 'Psc Prev Qstn Paper & Ans Key';
        $redirection = url('kpsc');
        $prev_type = PrevQuestion::groupBy('category')->get('category'); 
        return view('kpsc.psc-question-answer',compact('prev_type','title','redirection'));
    }

    public function prev_qstn_ans_sub_category($sub){
         $title = 'Psc Prev Qstn Paper & Ans Key';
        $redirection = url('kpsc');
        $category = str_replace('-',' ',$sub);
        $prev_type = PrevQuestion::where('category',$category)->groupBy('subcategory')->get('subcategory'); 
        return view('kpsc.psc-question-answer-sub_category',compact('prev_type','category','title','redirection'));
    }

    public function prev_qstn_ans_list($category,$sub_category){
       
         $title = 'Psc Prev Qstn Paper & Ans Key';
        $redirection = url('kpsc');
         $category = remove_slug($category);
        $sub_category = remove_slug($sub_category);
        $qstn_answer_key = PrevQuestion::where('category',$category)->where('subcategory',$sub_category)->orderBy('id','desc')->get(); 
       
        return view('kpsc.psc-prev-qstn-anskey',compact('qstn_answer_key','title','redirection'));
        
    }

    public function psc_results(){
         $title = 'Psc Results';
        $redirection = url('kpsc');
        $results_type = Pscresults::groupBy('type')->get('type'); 
        
        
        
        return view('kpsc.psc-results',compact('results_type','title','redirection'));
        
        
    }

    public function  psc_results_sub_category($sub){
        $title = 'Psc Results';
        $redirection = url('kpsc');
        $sub_category = str_replace('-',' ',$sub);
        $results_list = Pscresults::where('type',$sub_category)->get(); 
        
        
        
        return view('kpsc.psc-results-list',compact('results_list','title','redirection'));
    }

    public function psc_special_videos(){
         $title = 'Special Videos';
        $redirection = url('kpsc');
        $spcl_video = SpecialVideo::whereNull('section')->get();
        return view('kpsc.psc-special-video-list',compact('spcl_video','title','redirection'));
    }

    public function psc_our_videos(){
        $title = 'Our Videos';
        $redirection = url('kpsc');
        $our_video = Ourvideos::get();
        return view('kpsc.psc-our-video-list',compact('our_video','title','redirection'));
    }
    
    public function  news_all(){
        $title = 'All News';
        $redirection = url('kpsc');
        
        $psc_news       = Pscnews::where('status',1)->orderby('id','desc')->get();
        
        return view('kpsc.news-all',compact('psc_news','title','redirection'));
    }
    
    public function  news_view($id){
     
        $psc_news       = Pscnews::where('id',$id)->first();
        $title = 'Latest News';
        $redirection = url('kpsc/news-all');
        
        return view('kpsc.news-single',compact('psc_news','title','redirection'));
    }
    
    public function feed_show(){
        $title = 'Pachavellam Feed';
        $redirection = url('kpsc');
  
   $noftify        = Notification::orderby('created_at','desc')->get();

        $capsule_list = Capsule::orderby('created_at','desc')->get();
        return view('kpsc.feed-show',compact('title','redirection','capsule_list','noftify'));
    }
    
    public function feed_comments_show(Request $request){
        $comments = CapsuleComment::where('cap_id',$request->id)->orderby('created_at','desc')->get();
        
        $data = "";
                    // <!--<img src="https://i.imgur.com/yTFUilP.jpg" alt="" class="rounded-circle" width="40" height="40">-->
                    // <!--<h4 class="text-light">Jhon Doe</h4>-->
                    
        foreach($comments as $list){
            $data  .=    '<div class="comment col-12 mt-1 text-justify bg-dark float-left">
                            <span  class="text-light  float-right">- '.date('d-M, Y',strtotime($list->created_at)).'</span>
                            <br>
                            <p class="text-light">'.$list->comment.'</p>
                          </div>';
        }

        return $data;
    }
    
    public function feed_like(Request $request){
        
        $check_liked_or_not = CapsuleLike::where('cap_id',$request->id)->where('ip_adress',$request->ip())->first();
        if(!$check_liked_or_not){
            $feed_like = new CapsuleLike;
            $feed_like->cap_id = $request->id;
            $feed_like->ip_adress = $request->ip();
            $feed_like->save();
        }
        
        return count(CapsuleLike::where('cap_id',$request->id)->get());
    }
    public function feed_comments_store(Request $request){
        $comments_store = new CapsuleComment;
        $comments_store->comment  =  $request->comments;
        $comments_store->cap_id = $request->post_id;
        $comments_store->ip_adress = $request->ip();
        $comments_store->save();
        
        $comments = CapsuleComment::where('cap_id',$request->post_id)->orderby('created_at','desc')->get();
       
         $data = array();
        $data[0] = "";
        
        foreach($comments as $list){
            $data[0]  .=    '<div class="col-12 comment mt-1 text-justify bg-dark float-left">
                            <span  class="text-light float-right">- '.date('d-M, Y',strtotime($list->created_at)).'</span>
                            <br>
                            <p class="text-light">'.$list->comment.'</p>
                        </div>';
        }
        $data[1] = "";
        $data[1] = count(CapsuleComment::where('cap_id',$request->post_id)->get());
        
        return $data;
        
    }
    
    public function time_tables(){
        $title = 'Pachavellam Education :: Time Table';
        $redirection = url('kpsc');
        $time_table = Timetable::orderby('position')->where('status',1)->get();
        return view('kpsc.time_table',compact('title','redirection','time_table'));
    }
    
    
    public function live_class(){
        return view('kpsc.live-class');
    }

    
    
    public function daily_bucket(){
        $title = 'Daily Fact Bucket :: Roaring 40`s';
        $redirection = url('kpsc');
        $today = date('Y-m-d');
        $dailyBucket_list = DailyBucket::orderby('class_date','desc')->orderby('created_at','desc')->get();
        
        return view('kpsc.daily_bucket',compact('title','redirection','dailyBucket_list'));
        
    }
    
        
    public function daily_bucket_single($id){
        
        $title = 'Daily Fact Bucket :: Roaring 40`s';
        $redirection = url('kpsc');
        $dailyBucket = DailyBucket::where('id',$id)->first();
       
        $dailyBucket_list = DailyBucket::where('day_title',$dailyBucket->day_title)->orderBy('id','desc')->get(); 
      
        return view('kpsc.daily_bucket_show',compact('title','redirection','dailyBucket_list'));
    }
    
        
    public function daily_bucket_show_pdf($date,$pdf_name){
        $title       = 'Daily Fact Bucket :: Roaring 40`s';
        $redirection = url('kpsc/daily-bucket');
        $url         = $pdf_name;
        
         $dailyBucket_list = DailyBucket::where('class_date',$date)->get();
         
        return view('kpsc.daily_bucket_show_pdf',compact('title','redirection','url','dailyBucket_list'));
        
    }
    
    
    
    public function psc_download_count(Request $request){
        Bulletin::where('id',$request->dwn_id)->increment('download', 1);
        
    }
    
    public function get_model_exam_dates(){
        $date_list = DailyExam::leftJoin('kpsc_subjects','kpsc_subjects.id','kpsc_daily_exams.subject')
                                ->select('kpsc_daily_exams.*','kpsc_subjects.subject_title')
                                ->where('section','Model Exam')
                                ->orderby('exam_date')->get();
         
         return view('kpsc.model-exam',compact('date_list'));
    }
    
    
    public function get_model_date_based_questions($date,$id){
         
        $examdetails = DailyExam::leftJoin('kpsc_model_exams_details','kpsc_model_exams_details.exam_id','kpsc_daily_exams.id')
                                ->where('kpsc_daily_exams.id',$id)
                                ->select('kpsc_daily_exams.*','kpsc_model_exams_details.qp_file','kpsc_model_exams_details.answer_file')
                                ->first();
        
        return view("kpsc.model-exam-single",compact('examdetails'));

    }
     
   
    
    public function model_exam_answerkey($date,$id,Request $request){
        
        $examdetails = DailyExam::leftJoin('kpsc_model_exams_details','kpsc_model_exams_details.exam_id','kpsc_daily_exams.id')
                                ->where('kpsc_daily_exams.id',$id)
                                ->select('kpsc_daily_exams.*','kpsc_model_exams_details.qp_file','kpsc_model_exams_details.answer_file')
                                ->first();
        
        return view("kpsc.model-exam-answerkey",compact('examdetails'));
    }
    
    public function store_model_exam_results($id,Request $request){
        date_default_timezone_set('Asia/Kolkata');
        $new_one = ModelExamAttempt::where('exam_id',$id)->where('ip_address',$request->ip())->first();
        if(!$new_one){
            $new_one = new ModelExamAttempt();
            $new_one->exam_id      = $id;
             
            $new_one->full_name     = $request->fullname;
            $new_one->mobile_no     = $request->mobile_no;
            $new_one->ip_address    = $request->ip();
            $new_one->right_answer  = $request->correct_answer ?? 0;
            $new_one->wrong_answer  = $request->wrong_answer ?? 0;
            $new_one->total_mark    = $new_one->right_answer - ($new_one->wrong_answer / 3);
            $new_one->answer_uploaded = Carbon::now()->format('Y-m-d H:i:s');
            $new_one->save();
               return redirect('/kpsc/model-exam')->with('success', 'Mark uploaded successfully!' );
        }
        
      return redirect('/kpsc/model-exam')->with('success', 'Your Mark already uploaded!' );
         
    }
     
    public function model_exam_leaderboard($date,$id,Request $request){
             
        date_default_timezone_set('Asia/Kolkata');
        $examlist = DailyExam::where('exam_date',$date)
                                ->where ('id',$id)
                                ->where('ended_at', '<', Carbon::now()->format('Y-m-d H:i:s'))
                                ->first() ?? abort(404);
         
        $list_attempt = ModelExamAttempt::where('exam_id',$examlist->id)
                        ->leftJoin('kpsc_daily_exams','kpsc_daily_exams.id','kpsc_model_exam_attempt.exam_id')
                        ->where('kpsc_model_exam_attempt.answer_uploaded','<=',$examlist->ended_at)
                        ->select('kpsc_model_exam_attempt.*')
                        ->orderBy('kpsc_model_exam_attempt.total_mark','DESC')
                        ->orderBy('kpsc_model_exam_attempt.created_at','ASC')
                        ->limit(100)->get();
                        
          if (Auth::check() && (auth()->user()->id == 1)){
              
                   $list_attempt = ModelExamAttempt::where('exam_id',$examlist->id)
                        ->leftJoin('kpsc_daily_exams','kpsc_daily_exams.id','kpsc_model_exam_attempt.exam_id')
                        ->where('kpsc_model_exam_attempt.answer_uploaded','<=',$examlist->ended_at)
                        ->select('kpsc_model_exam_attempt.*')
                        ->orderBy('kpsc_model_exam_attempt.total_mark','DESC')
                        ->orderBy('kpsc_model_exam_attempt.created_at','ASC')
                        ->get();         
               
          }
            
                return view('kpsc.model-exam-leaderboard',compact('list_attempt'));    
    }
    
    public function get_daily_exam_dates(){
         $date_list = DailyExam::leftJoin('kpsc_subjects','kpsc_subjects.id','kpsc_daily_exams.subject')
                                ->select('kpsc_daily_exams.*','kpsc_subjects.subject_title')
                                ->where('section','Daily Exam')
                                ->orderby('exam_date')->get();
         
         return view('kpsc.daily-exam',compact('date_list'));
    } 
    
           
    public function daily_exam_user_login(Request $request){
  
           $request->validate(['email'	=> 'bail|required|email', 'password' => 'bail|required']);
		
            $credentials = [
                'email' => $request['email'],
                'password' => $request['password'],
            ];
        
            $remember_me = $request->has('remember_me') ? true : false; 
 
        
            if (Auth::attempt($credentials,$remember_me)) {
              
            //   $val = redirect()->intended()->getTargetUrl();
            //   if($val == "https://www.pachavellam.com"){
                  return redirect()->intended(); 
            //   }
            //   else{
            //     return redirect()->intended();
            //   }
            }
            else{
                return redirect()->route('sign-in')->with('message', 'Wrong password or this account not approved yet.');


            }
    }
    
    public function daily_exam_user_register(Request $request){


        $validator = Validator::make($request->all(), 
                        [   'name' => 'required|max:255',
                            'email' => 'required|email|max:255|unique:users', 
                            'password' => 'required|min:6',  
                            'mobile_no' => 'required|min:10',
                        ]);

        if ($validator->fails()) {  return redirect()->intended('/kpsc/daily-exam/login')->withErrors($validator); }
        
        if($request->has('image') && $request->image != ''){
            $image = file_get_contents($request->image);
            $name = Str::random(40).'.png';
            Storage::put("/public/users/".$name, $image);
        }
        else
        {
          $name = NULL;  
        }

        $newUser = new User;
        $newUser->email = $request->email;
        $newUser->name = $request->name;
        $newUser->mobile = $request->mobile_no;
        $newUser->type = "Student";
        $newUser->password = Hash::make($request->password);
        $newUser->image = $name;
        $newUser->save();

        Auth::login($newUser);
        
        return redirect()->back(); 
    }
    
    
    public function get_date_based_questions($date,$id){
           
        date_default_timezone_set('Asia/Kolkata');
        
        if (Auth::check()){
            
            $user_val =  User::where('id',auth()->user()->id)->where('mobile',NULL)->first();
            
            if($user_val){
                return redirect()->intended('/kpsc/daily-exam/register');
            }
            
        }
            
        $date_list = DailyExam::where('exam_date',$date)
                            ->where('id',$id);
        if (Auth::check()){
               if(auth()->user()->id == 1){
                    $date_list = $date_list->where ('started_at','<=',date('Y-m-d H:i:s'));   
               }
        }
        
        $date_list = $date_list->first() ?? abort(404);
        $date_based = DailyExamdetails::where('exam_id',$date_list->id)->get() ?? abort(404);
        
 
        
        $new_one = DailyExamattempt::where('user_id',auth()->user()->id)
                    ->where('exam_id',$date_list->id)
                    ->first();
                    
        if(!$new_one){
            $new_one = new DailyExamattempt();
            $new_one->user_id = auth()->user()->id;
            $new_one->exam_id = $date_list->id;
            $new_one->attend_started_at = date('Y-m-d H:i:s');
            $new_one->save();
        }
       
      
        
        $ab = array();
        $jo = 1;
        
        foreach($date_based as $list){
            $ab[] = ['id' => $jo,
                    'question' => $list->question,
                    'options' => array(array('a'=>$list->option_1,'b'=>$list->option_2,'c'=>$list->option_3,'d'=>$list->option_4)),
                    'answer' => $list->currect_ans,
                    'score' => 0,
                    'status' => ""];
            $jo = $jo +1;
        }
        
        $date_based = array('JS'=>$ab);
   
        $attempt_id = $new_one->id;
        $exam_date = $date_list->exam_date;
        $exam_id = $id;
        
        return view("kpsc.daily-exam-single",compact('date_based','exam_date','attempt_id','exam_id'));
        
    }
    
    public function store_exam_results($date,$id,Request $request){
            
            // 
        date_default_timezone_set('Asia/Kolkata');
        $update_one = DailyExamattempt::where('user_id',auth()->user()->id)
                                        ->where('exam_id',$id)
                                        ->where('status','<>',2)
                                        ->first();
                
        if($update_one){
            $update_one->right           = $request->right;
            $update_one->wrong           = $request->wrong;
            $update_one->skipped         = $request->skipped;
            $update_one->attend_ended_at = date('Y-m-d H:i:s');
            $update_one->attempt_time    =	strtotime($update_one->attend_ended_at)-strtotime($update_one->attend_started_at);
            $update_one->total_mark	     = ($request->right - ($request->wrong * 0.333));
            $update_one->summary         = $request->summary;
            $update_one->status          = "2";
            $update_one->save();
            
            return 1;
        }
        else{
             return 0;
        }
        
    }
    
    
    public function exam_discussion($date,$id,Request $request){
        $title = 'Exam Discussion :: '.$date;
        $redirection = url('kpsc/daily-exam');
        
        $date_list = DailyExam::where('exam_date',$date)
        ->where('id',$id)
        ->first() ?? abort(404);
        
        $list = ExamDiscussion::leftjoin('users as sender','sender.id','daily_exam_discussion.sender_id')
                                ->leftjoin('users as reply','reply.id','daily_exam_discussion.reply_id')
                                ->where('exam_id',$date_list->id)
                                ->orderBy('daily_exam_discussion.created_at','desc')
                                ->select('daily_exam_discussion.*','sender.image as sender_img','reply.image as reply_img','sender.name as sender_name','reply.name as reply_name')
                                ->get();
   
        
        return view('kpsc.daily-exam-discussion',compact('title','redirection','list'));
    }
    public function exam_discussion_store($date,$id,Request $request){
        
        $date_list = DailyExam::where('exam_date',$date)
        ->where ('id',$id)
        ->first() ?? abort(404);
        
        $add_one = new ExamDiscussion();
        $add_one->exam_id =   $date_list->id;
        $add_one->sender_id  = auth()->user()->id;
        $add_one->comment = $request->comment;
        $add_one->sender_ip = $request->ip();
        $add_one->save();
        return redirect()->back();
        
    }
    public function exam_leaderboard($date,$id,Request $request){
        
        $examlist = DailyExam::where('exam_date',$date)
                                ->where ('id',$id)
                                ->first() ?? abort(404);
        $list_attempt = DailyExamattempt::leftJoin('users','users.id','kpsc_daily_exams_attempt.user_id')
                        ->where('exam_id',$examlist->id)
                        ->where('kpsc_daily_exams_attempt.attend_ended_at','<=',$examlist->ended_at)
                        ->where('kpsc_daily_exams_attempt.status','2')
                        ->select('kpsc_daily_exams_attempt.user_id','kpsc_daily_exams_attempt.exam_id','kpsc_daily_exams_attempt.attempt_time','kpsc_daily_exams_attempt.total_mark','kpsc_daily_exams_attempt.updated_at','users.name','users.image')
                        ->orderBy('kpsc_daily_exams_attempt.total_mark','DESC')
                        ->orderBy('kpsc_daily_exams_attempt.attempt_time','ASC')
                        ->orderBy('kpsc_daily_exams_attempt.updated_at','ASC')
                        ->limit(100)->get();
                        
          if (Auth::check() && (auth()->user()->id == 1)){
              
                    $list_attempt = DailyExamattempt::leftJoin('users','users.id','kpsc_daily_exams_attempt.user_id')
                                                    ->where('exam_id',$examlist->id)
                                                    ->select('kpsc_daily_exams_attempt.user_id','kpsc_daily_exams_attempt.exam_id','kpsc_daily_exams_attempt.attempt_time','kpsc_daily_exams_attempt.total_mark','kpsc_daily_exams_attempt.updated_at','users.name','users.image')
                                                    ->orderBy('kpsc_daily_exams_attempt.total_mark','DESC')
                                                    ->orderBy('kpsc_daily_exams_attempt.attempt_time','ASC')
                                                    ->orderBy('kpsc_daily_exams_attempt.updated_at','ASC')
                                                    ->get();                
               
          }
            
                return view('kpsc.daily-exam-leaderboard',compact('list_attempt'));              
 
    }

    public function daily_exam_forget_password(Request $request){
        
        $user_check = User::where('email',$request->email_id)->where('mobile',$request->mobile)->first();
        if($user_check){
           $user_check->password =  Hash::make($request->password);
           $user_check->save();
            return 1;
        }
        else{
            
            return 0;
        }
        
    }
    
    
    
    public function get_date_based_questions_new($date,$id){
           
        date_default_timezone_set('Asia/Kolkata');
            
        $date_list = DailyExam::where('exam_date',$date)
                            ->where('id',$id);
        if (Auth::check()){
               if(auth()->user()->id == 1){
                $date_list = $date_list->where ('started_at','<=',date('Y-m-d H:i:s'));   
               }
        }
        
        $date_list = $date_list->first() ?? abort(404);
        $date_based = DailyExamdetails::where('exam_id',$date_list->id)->get() ?? abort(404);
        
 
        
        $new_one = DailyExamattempt::where('user_id',auth()->user()->id)
                    ->where('exam_id',$date_list->id)
                    ->first();
                    
        if(!$new_one){
            $new_one = new DailyExamattempt();
            $new_one->user_id = auth()->user()->id;
            $new_one->exam_id = $date_list->id;
            $new_one->attend_started_at = date('Y-m-d H:i:s');
            $new_one->save();
        }
       
      
        
      
        $jo = 0;
        $questions=array();
        
      

        foreach($date_based as $list){

               $questions[$jo]['text'] = $list->question;

                $questions[$jo]['responses'][0]['text']= $list->option_1;
                if($list->option_1 == $list->currect_ans){
                     $questions[$jo]['responses'][0]['correct']="true";
                }
                $questions[$jo]['responses'][1]['text']= $list->option_2;
                if($list->option_2 == $list->currect_ans){
                     $questions[$jo]['responses'][1]['correct']="true";
                }
                $questions[$jo]['responses'][2]['text']= $list->option_3;
                if($list->option_3 == $list->currect_ans){
                     $questions[$jo]['responses'][2]['correct']="true";
                }
                $questions[$jo]['responses'][3]['text']= $list->option_4;
                if($list->option_4 == $list->currect_ans){
                     $questions[$jo]['responses'][3]['correct']="true";
                }
               
        
                $questions[$jo]['optional']= $list->currect_ans;
               
                $jo = $jo +1;
        }
        
     
        
        $QStn = array("user"=>"me","questions"=>$questions);
 
        $attempt_id = $new_one->id;
        $exam_date = $date_list->exam_date;
        $exam_id = $id;
        
        return view("kpsc.daily-exam-single-new",compact('QStn','exam_date','attempt_id','exam_id'));
        
    }

    
    ////////////////////////////******************************************/////////////////////////////////
    public function subject_related_free_class($category_name){
            
        $title = "Kerala PSC ".$category_name ;
        $redirection = url('kpsc');
        $subject_dtls      = KpscSubject::where('slug_name',$category_name)->first();
        $free_classlist = ClassFree::where('category_id',$subject_dtls->id)->orderby('position')->get();
        return view('kpsc.class-list',compact('title','redirection','free_classlist')); 

    }

    public function sub_subject_related_free_class($category_name,$sub_category){

        $title = "Kerala PSC ".$sub_category ;
        $redirection = url('kpsc/subject/'.$category_name.'/'.$sub_category);
        
        $subject_dtls      = KpscSubject::where('slug_name',$category_name)->first();
        $sub_subject_dtls      = KpscSubject::where('slug_name',$sub_category)->where('parent_id',$subject_dtls->id)->first();
        
        
        $free_classlist = ClassFree::where('category_id',$sub_subject_dtls->id)->orderby('position')->get();


        return view('kpsc.class-list',compact('title','redirection','free_classlist')); 
    }
    
    public function subject_related_premium_class($category_name){
        $title          = "Kerala PSC ".$category_name ;
        $redirection    = url('kpsc');
        $subject_dtls   = KpscSubject::where('slug_name',$category_name)->first();
        $free_classlist = ClassPaid::where('category_id',$subject_dtls->id)->orderby('position')->get();
        return view('kpsc.class-list',compact('title','redirection','free_classlist')); 

    }
    public function sub_subject_related_premium_class($category_name,$sub_category){
        $title              = "Kerala PSC ".$sub_category ;
        $redirection        = url('kpsc/subject/'.$category_name.'/'.$sub_category);
        
        $subject_dtls       = KpscSubject::where('slug_name',$category_name)->first();
        $sub_subject_dtls   = KpscSubject::where('slug_name',$sub_category)->where('parent_id',$subject_dtls->id)->first();
        
        
        $free_classlist     = ClassPaid::where('category_id',$sub_subject_dtls->id)->orderby('position')->get();


        return view('kpsc.class-list',compact('title','redirection','free_classlist')); 
    }
    
    
    public function  subject_related_youtube_class($category_name){
         $title          = "Kerala PSC ".$category_name ;
        $redirection    = url('kpsc');
        $subject_dtls   = KpscSubject::where('slug_name',$category_name)->first();
       
        $spcl_video = SpecialVideo::where('category',$subject_dtls->id)->whereNull('section')->get(); 
        return view('kpsc.psc-special-video-list',compact('spcl_video','title','redirection'));
        
    }
    public function  sub_subject_related_youtube_class($category_name,$sub_category){
           $title          = "Kerala PSC ".$category_name ;
        $redirection    = url('kpsc');
        
        $subject_dtls       = KpscSubject::where('slug_name',$category_name)->first();
        $sub_subject_dtls   = KpscSubject::where('slug_name',$sub_category)->where('parent_id',$subject_dtls->id)->first();
        
        $spcl_video = SpecialVideo::where('category',$sub_subject_dtls->id)->whereNull('section')->get(); 
        return view('kpsc.psc-special-video-list',compact('spcl_video','title','redirection'));
    }
    public function  subject_related_study_notes($category_name){
        $title          = "Kerala PSC ".$category_name ;
        $redirection    = url('kpsc');
        $subject_dtls   = KpscSubject::where('slug_name',$category_name)->first();
       
        $material_list = Material::where('type',$subject_dtls->id)->get(); 
        return view('kpsc.psc-sub-material',compact('material_list','title','redirection'));
        
    }
    public function  sub_subject_related_study_notes($category_name,$sub_category){
        $title              = "Kerala PSC ".$sub_category ;
        $redirection        = url('kpsc/subject/'.$category_name.'/'.$sub_category);
        
        $subject_dtls       = KpscSubject::where('slug_name',$category_name)->first();
        $sub_subject_dtls   = KpscSubject::where('slug_name',$sub_category)->where('parent_id',$subject_dtls->id)->first();
        
        $material_list = Material::where('type',$sub_subject_dtls->id)->get(); 
        return view('kpsc.psc-sub-material',compact('material_list','title','redirection'));
    }
    public function  subject_related_mock_test($category_name){
         $title          = "Kerala PSC ".$category_name ;
        $redirection    = url('kpsc');
        $subject_dtls   = KpscSubject::where('slug_name',$category_name)->first();
       
        $date_list = DailyExam::leftJoin('kpsc_subjects','kpsc_subjects.id','kpsc_daily_exams.subject')
                                ->select('kpsc_daily_exams.*','kpsc_subjects.subject_title')
                                ->where('section','Daily Exam')
                                ->where('subject',$subject_dtls->id)
                                ->orderby('exam_date')->get();
         
         return view('kpsc.daily-exam',compact('date_list'));
    }
    public function  sub_subject_related_mock_test($category_name,$sub_category){
        $title              = "Kerala PSC ".$sub_category ;
        $redirection        = url('kpsc/subject/'.$category_name.'/'.$sub_category);
        
        $subject_dtls       = KpscSubject::where('slug_name',$category_name)->first();
        $sub_subject_dtls   = KpscSubject::where('slug_name',$sub_category)->where('parent_id',$subject_dtls->id)->first();
        
        $date_list = DailyExam::leftJoin('kpsc_subjects','kpsc_subjects.id','kpsc_daily_exams.subject')
                                ->select('kpsc_daily_exams.*','kpsc_subjects.subject_title')
                                 ->where('subject',$sub_subject_dtls->id)
                                 ->where('section','Daily Exam')
                                ->orderby('exam_date')->get();
         
         return view('kpsc.daily-exam',compact('date_list'));
    }
    public function  subject_related_text_books($category_name){
        
    }
    public function  sub_subject_related_text_books($category_name,$sub_category){
        
    }
    
    
    
}
