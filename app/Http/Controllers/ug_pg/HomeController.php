<?php

namespace App\Http\Controllers\ug_pg;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\UG_PG\University;
use App\Models\UG_PG\Level;
use App\Models\UG_PG\Course;
use App\Models\UG_PG\Semester;
use App\Models\UG_PG\Subject;
use App\Models\UG_PG\Question_Paper;
use App\Models\UG_PG\Material;
use App\Models\UG_PG\Syllabus;
use App\Models\UG_PG\Year_Admission;
use Illuminate\Support\Str;
use Auth;
use App\Helper;
use Storage;

class HomeController extends Controller
{
    //
    public function dashboard(){

        return view('admin.ug-pg.dashboard');
    }
    public function university(){
        $list = University::orderby('position')->get();
        return view('admin.ug-pg.university',compact('list'));
    }
    public function university_store(Request $request){
        $add_new = new University;
        $add_new->university_name = $request->university_name;
        $add_new->name_slug  = Str::slug($request->university_name);
        $add_new->status  = 1;
        $add_new->author_id = Auth::user()->id;
        $add_new->save();
       
        $add_new->position  = $add_new->id;
        $add_new->save();

        return redirect()->back()->with('message','Data added Successfully');
    
    }

    public function delete($id){
        $del_val = University::where('id',$id)->first();
        $del_val->delete();
        $this->update_university_related($del_val->name_slug);

        return redirect()->back()->with('message','Data deleted Successfully');
    }
    public function updating(Request $request){
        $add_new =  University::where('id',$request->id)->first();
        $slug_name = $add_new->name_slug;
        $add_new->university_name = $request->university_name;
        $add_new->name_slug  = Str::slug($request->university_name);
        $add_new->status  = $request->status;
        $add_new->position  = $request->position;
        $add_new->save();
        $this->update_university_related($slug_name,$add_new->name_slug);
        return redirect()->back()->with('message','Data updated Successfully');
    }

    public function level(){
        $level_list = Level::orderby('position')->get();
        $list = University::orderby('position')->get();
        return view('admin.ug-pg.level',compact('level_list','list'));
    }
    
    public function level_store(Request $request){
        $add_new = new Level;
        $add_new->university_name = $request->university;	
        $add_new->level_name = $request->level_name;	
        $add_new->name_slug = Str::slug($request->level_name);
        $add_new->status = 1;
        $add_new->author_id = Auth::user()->id;
        $add_new->save();
        
        $add_new->position  = $add_new->id;
        $add_new->save();
        return redirect()->back()->with('message','Data added Successfully');
       
    }
    public function delete_level($id){
        $del_val = Level::where('id',$id)->first();
        $del_val->delete();
        $this->delete_level_related($del_val->name_slug);
         return redirect()->back()->with('message','Data deleted Successfully');
    }
    public function updating_level(Request $request){
        $add_new = Level::where('id',$request->id)->first();

        $slug_name = $add_new->name_slug;

        $add_new->university_name = $request->university;	
        $add_new->level_name = $request->level_name;	
        $add_new->name_slug = Str::slug($request->level_name);
        $add_new->position = $request->position;
        $add_new->status = $request->status;
        $add_new->save();
        
        $this->update_level_related($slug_name,$add_new->name_slug);
        return redirect()->back()->with('message','Data updated Successfully');
    }
    public function course(){
        $list = University::orderby('position')->get();
        $course_list = Course::orderby('position')->get();
        return view('admin.ug-pg.course',compact('list','course_list'));
    }
    
    public function course_store(Request $request){
        			
        $add_new = new Course;
        $add_new->university_name  =$request->university;
        $add_new->level_name  =$request->level_name;
        $add_new->course_name  =$request->course_name;
        $add_new->name_slug = Str::slug($request->course_name);
        $add_new->position = 1;
        $add_new->status = 1;
        $add_new->author_id = Auth::user()->id;
        $add_new->save();
        return redirect()->back()->with('message','Data added Successfully');
    }
    public function delete_course($id){
        $del_val =Course::where('id',$id)->first();
        $del_val->delete();
        $this->delete_course_related($del_val->name_slug);

        return redirect()->back()->with('message','Data deleted Successfully');
    }
    public function updating_course(Request $request){
    
        $add_new = Course::where('id',$request->id)->first();
        $slug_name = $add_new->name_slug;

        $add_new->university_name  =$request->university;
        $add_new->level_name  =$request->level_name;
        $add_new->course_name  =$request->course_name;
        $add_new->name_slug = Str::slug($request->course_name);
        $add_new->position = $request->position;
        $add_new->status = $request->status;
        $add_new->save();
        
        $this->update_course_related($slug_name,$add_new->name_slug);
        return redirect()->back()->with('message','Data updated Successfully');
    }

    public function year_admission(){
        $list = University::orderby('position')->get();
        $year_list = Year_Admission::orderby('course_name')->get();
        return view('admin.ug-pg.year_admission',compact('list','year_list'));
    }
    public function year_admission_store(Request $request){
        $add_new = new Year_Admission;
        $add_new->university_name = $request->university;
        $add_new->level_name = $request->level_name;
        $add_new->course_name = $request->course;
        $add_new->year_admission = $request->year_admission;
        $add_new->name_slug = Str::slug($request->year_admission);
        $add_new->status = 1;
        $add_new->author_id = Auth::user()->id;
        $add_new->save();
        
        $add_new->position = $add_new->id;
        $add_new->save();
        
        return redirect()->back()->with('message','Data added Successfully');
    }
    public function delete_year_admission($id){

        $del_val =Year_Admission::where('id',$id)->first();
        $del_val->delete();
        $this->delete_admission_related($del_val->name_slug);

        return redirect()->back()->with('message','Data deleted Successfully');
    }
    public function updating_year_admission(Request $request){
        $add_new = Year_Admission::where('id',$request->id)->first();
        $slug_name = $add_new->name_slug;
        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->year_admission     = $request->year_admission;
        $add_new->name_slug         = Str::slug($request->year_admission);
        $add_new->position	        = $request->position;
        $add_new->status            = $request->status;
        $add_new->save();
        
        $this->update_admission_related($slug_name,$add_new->name_slug);
        return redirect()->back()->with('message','Data added Successfully');
    }

    public function semester(){
        $list = University::orderby('position')->get();
        $semester_list = Semester::orderby('course_name')->get();
        return view('admin.ug-pg.semester',compact('list','semester_list'));
    }
    public function semester_store(Request $request){
        $add_new                    = new Semester;
        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->semester_name     = $request->semester;
        $add_new->year_admission    = $request->year_admission;
        $add_new->name_slug         = Str::slug($request->semester);
        $add_new->status            = 1;
        $add_new->author_id = Auth::user()->id;
        $add_new->save();
        $add_new->position	        = $add_new->id;
        $add_new->save();  
        return redirect()->back()->with('message','Data added Successfully');
    }
    public function delete_semester($id){
        $del_val = Semester::where('id',$id)->first();
        $del_val->delete();
        $this->delete_semester_related($del_val->name_slug);

        return redirect()->back()->with('message','Data deleted Successfully');
    }
    public function updating_semester(Request $request){
        $add_new = Semester::where('id',$request->id)->first();
        $slug_name = $add_new->name_slug;

        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->year_admission    = $request->year_admission;
        $add_new->semester_name     = $request->semester;
        $add_new->name_slug         = Str::slug($request->semester);
        $add_new->position	        = $request->position;
        $add_new->status            = $request->status;
        $add_new->save();
        
       
        $this->update_semester_related($slug_name,$add_new->name_slug);
        return redirect()->back()->with('message','Data added Successfully');
    }

    public function subjects(){
        $list = University::orderby('position')->get();
        $subject_list = Subject::orderby('course_name')->get();
        return view('admin.ug-pg.subjects',compact('list','subject_list'));
    }
    
    public function subjects_store(Request $request){
            foreach($request->subjects as $subject_name){
                if($subject_name != null){
                    $add_new = new Subject;
                    $add_new->university_name   = $request->university;
                    $add_new->level_name        = $request->level_name;
                    $add_new->course_name       = $request->course;
                    $add_new->semester_name     = $request->semester;
                    $add_new->subject_name      = $subject_name;
                    $add_new->author_id = Auth::user()->id;
                    $add_new->name_slug         = Str::slug($subject_name);
                    $add_new->year_admission    = $request->year_admission;
                    $add_new->status            = 1;
                    $add_new->save();

                    $add_new->position            = $add_new->id;
                    $add_new->save();
                    
                }
            }
        return redirect()->back()->with('message','Data added Successfully');
    }
    	
    public function delete_subjects($id){
        
        $del_val = Subject::where('id',$id)->first();
        $del_val->delete();
        $this->delete_subject_related($del_val->name_slug);
        return redirect()->back()->with('message','Data deleted Successfully');
    }
   
    public function updating_subjects(Request $request){
        $add_new = Subject::where('id',$request->id)->first();
        $slug_name = $add_new->name_slug;
        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->semester_name     = $request->semester;
        $add_new->subject_name      = $request->subjects;
        $add_new->name_slug         = Str::slug($request->subjects);
        $add_new->year_admission    = $request->year_admission;
        $add_new->position	        = $request->position;
        $add_new->status            = $request->status;
        $add_new->save();

        $this->update_subject_related($slug_name,$add_new->name_slug);

        return redirect()->back()->with('message','Data updated Successfully');
        
    }
    
    public function question_papper(){
        $list = University::orderby('position')->get();
        $semester_list = Semester::orderby('course_name')->get();
        return view('admin.ug-pg.question-papper',compact('list','semester_list'));
    }

    public function question_papper_view(){
        $list = Question_Paper::orderby('created_at')->get();
        return view('admin.ug-pg.question-papper-view',compact('list'));
    }

    public function question_papper_single($id){
        $list = Question_Paper::where('id',$id)->first();
        return view('admin.ug-pg.question-papper-single',compact('list'));
    }

    public function question_papper_edit($id){
        $single_qstn = Question_Paper::where('id',$id)->first();
        $list = University::orderby('position')->get();
        $level_list = Level::where('name_slug',$single_qstn->level_name)->get();
        $course_list = Course::where('name_slug',$single_qstn->course_name)->get();
        $year_admission = Year_Admission::where('name_slug',$single_qstn->year_admission)->get();
        $semester_list = Semester::where('name_slug',$single_qstn->semester_name)->get();
        $subject_list  = Subject::where('name_slug',$single_qstn->subject_name)->get();
     
        return view('admin.ug-pg.question-papper-edit',compact('list','single_qstn','level_list','course_list','year_admission','semester_list','subject_list'));
    }

    public function store_question_papper(Request $request){
       
        $add_new = new Question_Paper;
        											
        $add_new->university_name = $request->university;
        $add_new->level_name = $request->level_name;
        $add_new->course_name = $request->course;
        $add_new->semester_name = $request->semester;
        $add_new->title = $request->title;
        $add_new->year = $request->year;
        // $add_new->content = $request->question_paper_content;
        $add_new->name_slug = Str::slug($request->title);
        $add_new->subject_name = $request->subject;
        $add_new->year_admission    = $request->year_admission;
        $add_new->author_id = Auth::user()->id;
        $add_new->type = $request->type;
        $add_new->notes = null;
        
         
        if($request->type == 'Text Type'){
            $add_new->content           = $request->question_paper_content;
        }
        else if(($request->type == 'Pdf Link')){
            $add_new->content           = $request->link;
             
        }     
        else if($request->type == 'Pdf File'){
             
            $file = $request->file('file');
            $name1 = Str::random(40).'.'.$file->getClientOriginalExtension();
            $image1 = file_get_contents($file);
            Storage::put('/public/ug-pg/question-paper/'.$name1, $image1);
            $add_new->content           = $name1;
        }
       
        $add_new->save();
        $add_new->status = 1;
        $add_new->position =$add_new->id;
        
        $add_new->save();

        return redirect()->back()->with('message','Data added Successfully');
    }

    public function updating_question_papper(Request $request){
        
        $add_new = Question_Paper::where('id',$request->id)->first();
        											
        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->semester_name     = $request->semester;
        $add_new->title             = $request->title;
        $add_new->year              = $request->year;
        $add_new->content           = $request->question_paper_content;
        $add_new->name_slug         = Str::slug($request->title);
        $add_new->subject_name      = $request->subject;
        $add_new->year_admission    = $request->year_admission;
        $add_new->status            = $request->status;
        $add_new->type              = $request->type;
        $add_new->position          = $request->position;
        
        if($request->type == 'Text Type'){
            $add_new->content           = $request->material_content;
        }
        else if($request->type == 'Pdf Link'){
            $add_new->content           = $request->link;
        }     
        else if($request->type == 'Pdf File'){
            if($request->hasfile('file'))
            {
                $file = $request->file('file');
                $name1 = Str::random(40).'.'.$file->getClientOriginalExtension();
                $image1 = file_get_contents($file);
                Storage::put('/public/ug-pg/question-paper/'.$name1, $image1);
                $add_new->content           = $name1;
            }
        }
        
        $add_new->save();

        return redirect(ug_pg_cms('question-paper-view'))->with('message','Data added Successfully');
    }

    public function delete_question_papper($id){
        
        Question_Paper::where('id',$id)->delete();

        return redirect(ug_pg_cms('question-paper-view'))->with('message','Data deleted Successfully');
        
    }

    public function material(){
        $list = University::orderby('position')->get();
        $semester_list = Semester::orderby('course_name')->get();
        return view('admin.ug-pg.materials',compact('list','semester_list'));
    }

    public function material_store(Request $request){
        $add_new = new Material;
        											
        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->semester_name     = $request->semester;
        $add_new->title             = $request->title;
        $add_new->year              = $request->year ?? 0;
        $add_new->name_slug         = Str::slug($request->title);
        $add_new->subject_name      = $request->subject;
        $add_new->year_admission    = $request->year_admission  ?? '0';
        $add_new->author_id         = Auth::user()->id;
        $add_new->type              = $request->type;
        $add_new->notes             = null;
        $add_new->status            =    1;
        
        if($request->type == 'Text Type'){
            $add_new->content           = $request->material_content;
        }
        else if(($request->type == 'Pdf Link') || ($request->type == 'Youtube Link') || ($request->type == 'Other Page Link')){
            $add_new->content           = $request->link;
        }     
        else if($request->type == 'Pdf File'){
            $file = $request->file('file');
            $name1 = Str::random(40).'.'.$file->getClientOriginalExtension();
            $image1 = file_get_contents($file);
            Storage::put('/public/ug-pg/material/'.$name1, $image1);
            $add_new->content           = $name1;
        }
        
        $add_new->save();

        $add_new->position =$add_new->id;
        $add_new->save();

        return redirect()->back()->with('message','Data added Successfully');
    }

    public function updating_materials(Request $request){
        $add_new = Material::where('id',$request->id)->first();
        $old_content = $add_new->content;										
        $add_new->university_name   = $request->university;
        $add_new->level_name        = $request->level_name;
        $add_new->course_name       = $request->course;
        $add_new->semester_name     = $request->semester;
        $add_new->title             = $request->title;
        $add_new->year              = $request->year;
        $add_new->name_slug         = Str::slug($request->title);
        $add_new->subject_name      = $request->subject;
        $add_new->year_admission    = $request->year_admission ?? '0';
        $add_new->status            = $request->status;
        $add_new->type              = $request->type;
        $add_new->position          = $request->position;
         
        if($request->type == 'Text Type'){
            $add_new->content           = $request->material_content;
        }
        else if(($request->type == 'Pdf Link') || ($request->type == 'Youtube Link') || ($request->type == 'Other Page Link')){
            $add_new->content           = $request->link;
        }     
        else if($request->type == 'Pdf File'){
            if($request->hasfile('file'))
            {
                $file = $request->file('file');
                $name1 = Str::random(40).'.'.$file->getClientOriginalExtension();
                $image1 = file_get_contents($file);
                Storage::put('/public/ug-pg/material/'.$name1, $image1);
                $add_new->content           = $name1;
            }
        }
        
        $add_new->save();
        return redirect(ug_pg_cms('materials-view'))->with('message','Data updated Successfully');

        
    }

    public function material_view(){
          $list = Material::orderby('created_at')->get();
        return view('admin.ug-pg.materials-view',compact('list'));

    }

    public function material_single($id){
         $list = Material::where('id',$id)->first();
        return view('admin.ug-pg.materials-single',compact('list'));

    }

    public function material_edit($id){
        $single_qstn = Material::where('id',$id)->first();
        $list = University::orderby('position')->get();
        $level_list = Level::where('name_slug',$single_qstn->level_name)->get();
        $course_list = Course::where('name_slug',$single_qstn->course_name)->get();
        $year_admission = Year_Admission::where('name_slug',$single_qstn->year_admission)->get();
        $semester_list = Semester::where('name_slug',$single_qstn->semester_name)->get();
        $subject_list  = Subject::where('name_slug',$single_qstn->subject_name)->get();
     
        return view('admin.ug-pg.materials-edit',compact('list','single_qstn','level_list','course_list','year_admission','semester_list','subject_list'));

    }
    public function delete_materials($id){
        
        Material::where('id',$id)->delete();

        return redirect(ug_pg_cms('materials-view'))->with('message','Data deleted Successfully');
        
    }

    public function get_level(Request $request){

        $list = Level::where('university_name',$request->university)->get();
        $options ="<option value=''></option>";
        if(count($list)>0){
            foreach($list as $option){
                $options .="<option value='".$option->name_slug."'>".$option->level_name."</option>";
            }
        }
        else{
            $options .="<option value=''>No data found</option>";
        }
    
        return $options;
    }

    public function syllabus(){
        $list = University::orderby('position')->get();
        $semester_list = Semester::orderby('course_name')->get();
        return view('admin.ug-pg.syllabus',compact('list','semester_list'));
    }
    public function syllabus_store(Request $request){

        $name = Str::random(40).'.pdf';
        $image = file_get_contents($request->file('file'));
        Storage::put('/public/syllabus/'.$name, $image);
        
        $add_new                    =    new Syllabus;
        $add_new->university_name   =    $request->university;
        $add_new->level_name        =    $request->level_name;
        $add_new->title             =    $request->course;
        $add_new->name_slug         =    Str::slug($request->course);
        $add_new->file           =       $name;
        $add_new->author_id         =    Auth::user()->id;
        $add_new->status            =    1;
        $add_new->save();
        $add_new->position          =    $add_new->id;
        $add_new->save();

        return redirect()->back()->with('message','Data added Successfully');
    }
    
    public function syllabus_view(){
        $list = Syllabus::orderby('position')->get();
      return view('admin.ug-pg.syllabus-view',compact('list'));

    }
    public function delete_syllabus($id){
        
        try{
        Syllabus::find($id)->delete();
        }
        catch(\Exception $e){
            
        }
        $list = Syllabus::orderby('position')->get();
      return redirect()->back()->with('message','Data deleted Successfully');;

    }
    
    

    public function get_course(Request $request){
        		// 			
        $list = Course::where('university_name',$request->university)
                        ->where('level_name',$request->level_name)
                        ->get();
        
        $options ="<option value=''></option>";
        if(count($list)>0){
            foreach($list as $option){
                $options .="<option value='".$option->name_slug."'>".$option->course_name."</option>";
            }
        }
        else{
            $options .="<option value=''>No data found</option>";
        }
    
        return $options;
    }

    public function get_semesters(Request $request){
        $list = Semester::where('university_name',$request->university)
                        ->where('course_name',$request->course_name)
                        ->get();
        
        $options ="<option value=''></option>";
        if(count($list)>0){
            foreach($list as $option){
                $options .="<option value='".$option->name_slug."'>".$option->semester_name."</option>";
            }
        }
        else{
            $options .="<option value=''>No data found</option>";
        }
    
        return $options;
    }
    
    public function get_subjects(Request $request){
        $list = Subject::where('university_name',$request->university)
                        ->where('course_name',$request->course_name)
                        ->where('semester_name',$request->semester_name)
                        ->get();
        
        $options ="<option value=''></option>";
        if(count($list)>0){
            foreach($list as $option){
                $options .="<option value='".$option->name_slug."'>".$option->subject_name."</option>";
            }
        }
        else{
            $options .="<option value=''>No data found</option>";
        }
    
        return $options;
    }

    public function get_admission_year(Request $request){
        $list = Year_Admission::where('university_name',$request->university)
        ->where('course_name',$request->course_name)
        ->get();

        $options ="<option value=''></option>";
        if(count($list)>0){
            foreach($list as $option){
                $options .="<option value='".$option->name_slug."'>".$option->year_admission."</option>";
            }
        }
        else{
            $options .="<option value=''>No data found</option>";
        }

        return $options;
    }



    //deteled related slug_name
        public function delete_university_related($slug_name = null)
        {
            try{
                Level::where('university_name',$slug_name)->delete();
                Course::where('university_name',$slug_name)->delete();
                Year_Admission::where('university_name',$slug_name)->delete();
                Semester::where('university_name',$slug_name)->delete();
                Subject::where('university_name',$slug_name)->delete();
                Question_Paper::where('university_name',$slug_name)->delete();
                Material::where('university_name',$slug_name)->delete();
                Syllabus::where('university_name',$slug_name)->delete();
                return 1;
            }
            catch(\Exception $e){
                dd($e);
            }
        

        }

        public function delete_level_related($slug_name = null)
        {
            try{
                Course::where('level_name',$slug_name)->delete();
                Year_Admission::where('level_name',$slug_name)->delete();
                Semester::where('level_name',$slug_name)->delete();
                Subject::where('level_name',$slug_name)->delete();
                Question_Paper::where('level_name',$slug_name)->delete();
                Material::where('level_name',$slug_name)->delete();
                Syllabus::where('level_name',$slug_name)->delete();
                return 1;
            }
            catch(\Exception $e){
                dd($e);
            }
        }

        function delete_course_related($slug_name = null)
        {
            try{
                Year_Admission::where('course_name',$slug_name)->delete();
                Semester::where('course_name',$slug_name)->delete();
                Subject::where('course_name',$slug_name)->delete();
                Question_Paper::where('course_name',$slug_name)->delete();
                Material::where('course_name',$slug_name)->delete();
                Syllabus::where('course_name',$slug_name)->delete();
                return 1;
            }
            catch(\Exception $e){
                dd($e);
            }
        }

        function delete_admission_related($slug_name = null)
        {
            try{
                Semester::where('year_admission',$slug_name)->delete();
                Subject::where('year_admission',$slug_name)->delete();
                Question_Paper::where('year_admission',$slug_name)->delete();
                Material::where('year_admission',$slug_name)->delete();
                Syllabus::where('year_admission',$slug_name)->delete();
                return 1;
            }
            catch(\Exception $e){
                dd($e);
            }
        }
        
        function delete_semester_related($slug_name = null)
        {
            try{
                Subject::where('semester_name',$slug_name)->delete();
                Question_Paper::where('semester_name',$slug_name)->delete();
                Material::where('semester_name',$slug_name)->delete();
                return 1;
            }
            catch(\Exception $e){
                dd($e);
            }
        }
        
        function delete_subject_related($slug_name = null)
        {
            try{
                Question_Paper::where('subject_name',$slug_name)->delete();
                Material::where('subject_name',$slug_name)->delete();
                return 1;
            }
            catch(\Exception $e){
                dd($e);
            }
        }
        
    ///update slugname


    public function update_university_related($slug_name = null,$new_slug_name = null)
    {
        try{
            
            Level::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Course::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Year_Admission::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Semester::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Subject::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Question_Paper::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Material::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            Syllabus::where('university_name', $slug_name)->update(['university_name' => $new_slug_name]);
            return 1;
        }
        catch(\Exception $e){
            dd($e);
        }
      

    }

    public function update_level_related($slug_name = null,$new_slug_name = null)
    {
        try{
            
            Course::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            Year_Admission::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            Semester::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            Subject::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            Question_Paper::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            Material::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            Syllabus::where('level_name', $slug_name)->update(['level_name' => $new_slug_name]);
            return 1;
        }
        catch(\Exception $e){
            dd($e);
        }
    }
    

    function update_course_related($slug_name = null,$new_slug_name = null)
    {
        try{
            Year_Admission::where('course_name', $slug_name)->update(['course_name' => $new_slug_name]);
            Semester::where('course_name', $slug_name)->update(['course_name' => $new_slug_name]);
            Subject::where('course_name', $slug_name)->update(['course_name' => $new_slug_name]);
            Question_Paper::where('course_name', $slug_name)->update(['course_name' => $new_slug_name]);
            Material::where('course_name', $slug_name)->update(['course_name' => $new_slug_name]);
            // Syllabus::where('course_name', $slug_name)->update(['course_name' => $new_slug_name]);
            return 1;
        }
        catch(\Exception $e){
            dd($e);
        }
    }

    function update_admission_related($slug_name = null,$new_slug_name = null)
    {
        try{
            Semester::where('year_admission', $slug_name)->update(['year_admission' => $new_slug_name]);
            Subject::where('year_admission', $slug_name)->update(['year_admission' => $new_slug_name]);
            Question_Paper::where('year_admission', $slug_name)->update(['year_admission' => $new_slug_name]);
            Material::where('year_admission', $slug_name)->update(['year_admission' => $new_slug_name]);
            // Syllabus::where('year_admission', $slug_name)->update(['year_admission' => $new_slug_name]);
            return 1;
        }
        catch(\Exception $e){
            dd($e);
        }
    }
    
    function update_semester_related($slug_name = null,$new_slug_name = null)
    {
        try{
            Subject::where('semester_name', $slug_name)->update(['semester_name' => $new_slug_name]);
            Question_Paper::where('semester_name', $slug_name)->update(['semester_name' => $new_slug_name]);
            Material::where('semester_name', $slug_name)->update(['semester_name' => $new_slug_name]);
            return 1;
        }
        catch(\Exception $e){
            dd($e);
        }
    }
    
    function update_subject_related($slug_name = null,$new_slug_name = null)
    {
        try{
            Question_Paper::where('subject_name', $slug_name)->update(['subject_name' => $new_slug_name]);
            Material::where('subject_name', $slug_name)->update(['subject_name' => $new_slug_name]);
            return 1;
        }
        catch(\Exception $e){
            dd($e);
        }
    }

    

}
