<?php

namespace App\Http\Controllers\ug_pg;

use App\Http\Controllers\Controller;
use App\Models\UG_PG\Notification;
use App\Models\UG_PG\Banner;
use App\Models\UG_PG\SpecialVideo;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\UG_PG\University;
use App\Models\UG_PG\Level;
use App\Models\UG_PG\Course;
use App\Models\UG_PG\Semester;
use App\Models\UG_PG\Subject;
use App\Models\UG_PG\Question_Paper;
use App\Models\UG_PG\Syllabus;
use App\Models\UG_PG\Material;
use App\Models\UG_PG\Year_Admission;
use App\Models\ContactDb;
use App\Models\UG_PG\RequestClass;
use App\Models\UG_PG\News;


use Illuminate\Support\Str;

class FrontviewController extends Controller
{
    public function index(){
        $banner  = Banner::where('status',1)->orderby('position')->get();
        $noftify = Notification::orderby('created_at','desc')->get();
        $specialvideos  = SpecialVideo::where('status',1)->orderby('position')->get();
        $news_list      = News::where('status',1)->orderby('id','desc')->limit(10)->get();
        return view('ug-pg.index',compact('noftify','banner','specialvideos','news_list'));
    }
   public function notify(){
        $title = "Notification";
        $redirection = url('ug-pg');
        $noftify    = Notification::orderby('created_at','desc')->get();
        return view('ug-pg.notify',compact('noftify','title','redirection')); 
    }
    public function  university($section = null){  
        $list = University::orderby('position')->get();
        return view('ug-pg.university',compact('list','section'));
    }
    public function  category($section = null,$university_name = null){
  
        if($university_name != null){
            $list = Level::orderby('position')->where('university_name',$university_name)->get();
        }
        else{
            $list = Level::orderby('position')->get();
        }
        return view('ug-pg.category',compact('list','section'));
    }
    public function  course($section = null,$university_name = null,$category_name = null){
        
            if($university_name == null && $category_name == null){
                $list = Course::orderby('position')->get();
            }
            else{
                $list = Course::orderby('position')->where('university_name',$university_name)->where('level_name',$category_name)->get();
            }
            return view('ug-pg.course',compact('list','section'));

        
    }
    public function  admission_year($section = null,$university_name = null,$category_name = null,$course_name = null
    ){
        

            if($university_name == null && $category_name == null){
                $list = Year_Admission::orderby('position')->get();
            }
            else{
                $list = Year_Admission::orderby('position')->where('university_name',$university_name)->where('level_name',$category_name)
                 ->where('course_name',$course_name)
                ->get();
            }

            return view('ug-pg.admission_year',compact('list','section'));

        
    }


    public function  semester($section = null,$university_name = null,$category_name = null,$course_name = null){
       
        if($section === 'syllabus'){
            $list = syllabus::orderby('position')->where('course_name',$course_name)->where('level_name',$category_name)->where('university_name',$university_name)->get();
            return view('ug-pg.syllabus',compact('list','section'));
        }
        else{
            if($university_name == null && $category_name == null){
                $list = Semester::orderby('position')->get();
            }
            else{
                $list = Semester::orderby('position')->where('course_name',$course_name)->where('university_name',$university_name)->where('level_name',$category_name)->get();
            }
            return view('ug-pg.semester',compact('list','section'));
        }
    }
    public function  subjects($section = null,$university_name = null,$category_name = null,$course_name = null,$semester_name = null){
        if($university_name == null && $category_name == null){
            $list = Subject::orderby('position')->get();
        }
        else{
            
            $list = Subject::orderby('position')->where('semester_name',$semester_name)->where('course_name',$course_name)->where('university_name',$university_name)->where('level_name',$category_name)->get();
           
        }
        return view('ug-pg.subjects',compact('list','section'));
    }
    
    public function  question_papers($section = null,$university_name = null,$category_name = null,$course_name = null,$semester_name = null,$subject_name = null){
      
            if($university_name == null && $category_name == null){
                $list = Question_Paper::orderby('position')->get();
            }
            else{
                $list = Question_Paper::orderby('position')->where('semester_name',$semester_name)->where('course_name',$course_name)->where('subject_name',$subject_name)->where('university_name',$university_name)->where('level_name',$category_name)->get();
            }

            return view('ug-pg.question_papers',compact('list','section'));
        
 
    }
    
     public function question_papers_single($section = null,$university_name = null,$category_name = null,$course_name = null,$semester_name = null,$subject_name = null,$title = null){
        // if($section === 'question-paper'){
        //     if($university_name == null && $category_name == null){
        //         $list = Question_Paper::orderby('position')->where('name_slug',$title)->first();
        //     }
        //     else{
        //         $list = Question_Paper::orderby('position')->where('name_slug',$title)->where('semester_name',$semester_name)->where('course_name',$course_name)->where('subject_name',$subject_name)->where('university_name',$university_name)->where('level_name',$category_name)->first();

        //     }
        //     return view('ug-pg.question_paper_single',compact('list','section'));
        // }
        if($section === 'question-paper'){
            if($university_name == null && $category_name == null){
                $list = Question_Paper::orderby('position')->where('name_slug',$title)->first();
            }
            else{
                $list = Question_Paper::orderby('position')->where('name_slug',$title)->where('semester_name',$semester_name)->where('course_name',$course_name)->where('subject_name',$subject_name)->where('university_name',$university_name)->where('level_name',$category_name)->first();

            }
            return view('ug-pg.question_paper_single_view',compact('list','section'));
        }
     }

   
    public function  request_class(){
        return view('ug-pg.request_class');
    }

    public function request_class_store(Request $request){
        $name = $request->name;
        $email = $request->email;
        $mobile = $request->mobile;
        $college = $request->college;
        $course = $request->course;
        $subjects = $request->subjects;
        $class_method = $request->class_method;
        $comments = $request->comments;
       
        $check_exist = RequestClass::where('email',$email)->where('subject',$subjects)->where('course',$course)->where('mobile',$mobile)->first();
        if(!$check_exist){

            $add_new_class = new RequestClass;
            $add_new_class->name = $name;
            $add_new_class->email = $email;
            $add_new_class->mobile = $mobile;
            $add_new_class->college = $college;
            $add_new_class->university = 0;
            $add_new_class->course = $course;
            $add_new_class->subject = $subjects;
            $add_new_class->comments = $comments;
            $add_new_class->type = $class_method;
            $add_new_class->semester = 0;
            $add_new_class->save();
    
            $result = '<h2>Registration completed successfully  </h2>
            <h6 class="mt-4">our executive will contact you shortly</h6>';

            
            $contact = ContactDb::where('email',$email)->where('mobile',$mobile)->first();

            if(!$contact){
                $add_new_contact = new ContactDb;
                $add_new_contact->name	    = $name;
                $add_new_contact->email	    = $email;
                $add_new_contact->mobile	= $mobile;
                $add_new_contact->college	= $college;
                $add_new_contact->university= 0;
                $add_new_contact->course	= $course;
                $add_new_contact->save();
            }
        }
        else{
            $result = '<h2>You are  already registered</h2>
            <h6 class="mt-4">our executive will contact you shortly</h6>';
        }
        return view('ug-pg.register_success',compact('result'));
    }
    
    
    public function  news_all(){
        $title = 'All News';
        $redirection = url('ug-pg');
        
        $news_list       = News::where('status',1)->orderby('id','desc')->get();
        
        return view('ug-pg.news-all',compact('news_list','title','redirection'));
    }
    
    public function  news_view($id){
     
        $news_list       = News::where('id',$id)->first();
        $title = 'Latest News';
        $redirection = url('ug-pg/news-all');
        
        return view('ug-pg.news-single',compact('news_list','title','redirection'));
    }


    public function  syllabus(){
        $redirection = url('ug-pg');
        $list = Syllabus::groupby('university_name')->get('university_name');
        return view('ug-pg.syllabus',compact('list','redirection'));
    }
    
    public function  univrsity_syllabus($university){
        $redirection = url('ug-pg/syllabus');
        $list = Syllabus::where('university_name',$university)->groupby('level_name')->get('level_name');
        return view('ug-pg.syllabus_level',compact('list','university','redirection'));
    }
    
    public function  univrsity_level_syllabus($university,$level_name){
        $title = remove_slug($university).'/'.remove_slug($level_name);
        $list = Syllabus::where('university_name',$university)->where('level_name',$level_name)->get();
        $redirection = url('ug-pg/syllabus/'.$university);
        return view('ug-pg.syllabus_list',compact('list','university','level_name','title','redirection'));
    }
    
    public function  syllabus_single_view($university,$level_name,$title_slug){
        $title = remove_slug($university).'/'.remove_slug($level_name).'/'.remove_slug($title_slug);
        $redirection = url('ug-pg/syllabus/'.$university.'/'.$level_name);
        $list = Syllabus::where('university_name',$university)
                        ->where('level_name',$level_name)
                        ->where('name_slug',$title_slug)
                        ->first();

        
        return view('ug-pg.syllabus-single-view',compact('list','university','level_name','title','redirection'));
     
        
    }
    
    public function  syllabus_single_download($university,$level_name){
        
    }

    
    public function  material(){
        $redirection = url('ug-pg');
        $title = 'Study Materials';
        
        $list = University::orderby('position')->get();
        return view('ug-pg.materials',compact('list'));
    }
    
    public function  material_university($university_name){
        $redirection = url('ug-pg/materials');
           
        $title = 'Study Materials';
        $list = Material::where('university_name',$university_name)->groupby('level_name')->get('level_name'); 
        
        return view('ug-pg.materials_levels',compact('list','university_name','redirection'));
    }
    
    public function  material_level($university_name,$level_name){
        $title = 'Study Materials';
    
        $list = Material::where('university_name',$university_name)->where('level_name',$level_name)
                            ->groupby('course_name')->get('course_name');
        $redirection = url('ug-pg/materials/'.$university_name);
        return view('ug-pg.material_course',compact('list','university_name','level_name','title','redirection'));
    }
    
    public function  material_course($university_name,$level_name,$course){
        $title = 'Study Materials';
        $list = Material::where('university_name',$university_name)->where('level_name',$level_name)
                            ->where('course_name',$course)->get('semester_name');
        $redirection = url('ug-pg/materials/'.$university_name.'/'.$level_name);
        return view('ug-pg.material_semester',compact('list','university_name','level_name','course','title','redirection'));
    }
    
    public function  material_semester($university_name,$level_name,$course,$semester){
        $title = 'Study Materials';
        $list = Material::where('university_name',$university_name)->where('level_name',$level_name)
                            ->where('course_name',$course)->where('semester_name',$semester)->orderby('subject_name')->get();
        $redirection = url('ug-pg/materials/'.$university_name.'/'.$level_name.'/'.$course);
        return view('ug-pg.material_subjects',compact('list','university_name','level_name','course','title','redirection','semester'));
    }
    
    public function  material_subject($university_name,$level_name,$course,$semester,$subject){
        
        $title = $course.'-Study Materials';
        $list = Material::where('university_name',$university_name)->where('level_name',$level_name)
                            ->where('course_name',$course)->where('semester_name',$semester)->where('subject_name',$subject)->get();
                            
        $redirection = url('ug-pg/materials/'.$university_name.'/'.$level_name.'/'.$course.'/'.$semester);
        return view('ug-pg.material_list',compact('list','university_name','level_name','course','title','redirection','semester','subject'));
    }
    
    
    public function material_single_view($university_name,$level_name,$course,$semester,$subject,$single_view)
    {
         $title = $subject.'-Study Materials';
        $list = Material::where('university_name',$university_name)->where('level_name',$level_name)
                            ->where('course_name',$course)->where('semester_name',$semester)->where('subject_name',$subject)->where('name_slug',$single_view)->first();
                            
        $redirection = url('ug-pg/materials/'.$university_name.'/'.$level_name.'/'.$course.'/'.$semester.'/'.$subject);
        return view('ug-pg.material_single-view',compact('list','university_name','level_name','course','title','redirection','semester','subject','single_view'));
    }
    
    // public function material_singleview($university_name,$level_name,$course,$semester,$subject,$single_view){
    //      $title = 'Study Materials';
    //      $list = Material::where('university_name',$university_name)->where('level_name',$level_name)
    //                         ->where('course_name',$course)->where('semester_name',$semester)->where('subject_name',$subject)->where('name_slug',$single_view)->first();
                            
    //     $redirection = url('ug-pg/materials/'.$university_name.'/'.$level_name.'/'.$course.'/'.$semester.'/'.$subject);
    //     return view('ug-pg.material_singleview',compact('list','university_name','level_name','course','title','redirection','semester','subject','single_view'));
    // }

}