<?php

namespace App\Http\Controllers\teaching;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\Teaching\Instructor;
use Illuminate\Support\Str;
use Auth;
use App\Helper;
use Storage;

class InstructorController extends Controller
{
    //
    public function instructors(){
        dd(Instructor::get());
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
        $subject_list  = Subject::where('name_slug',$single_qstn->subjecmuhut_name)->get();
     
        return view('admin.ug-pg.materials-edit',compact('list','single_qstn','level_list','course_list','year_admission','semester_list','subject_list'));

    }
    public function delete_materials($id){
        
        Material::where('id',$id)->delete();

        return redirect(ug_pg_cms('materials-view'))->with('message','Data deleted Successfully');
        
    }


    

}
