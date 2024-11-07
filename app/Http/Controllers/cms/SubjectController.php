<?php

namespace App\Http\Controllers\cms;

use App\Models\KpscSubject;
use App\Models\KpscSubjectActivities;
use App\Models\KpscActivities;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;


class SubjectController extends Controller
{
    public function subjects_all(){
        $all_subjects = KpscSubject::where('kpsc_subjects.type','parent')->get();
        $activities = KpscActivities::get();
        return view('cms.subjects_all',compact('all_subjects','activities'));
    }
  
    public function subject_store(Request $request){
        if($request->has('image')){
            $image = file_get_contents($request->image);
            $name = Str::random(40).'.png';
            Storage::put('/public/files/category/'.$name, $image);
        }	
        
        
        $subject = new KpscSubject();
        $subject->subject_title = $request->subject_name;
        $subject->bg_color      = $request->bg_color;
        $subject->text_color    = $request->text_color;
        $subject->image         = 'category/'.$name;
        $subject->slug_name     = Str::slug($request->subject_name);
        $subject->type          = 'parent';
        $subject->status        = 1;
    
        try{
            $subject->save();
            $subject->position      = $subject->id;
            $subject->save();
            if($request->has('activity')){
                foreach($request->activity as $list){
                $activity = new KpscSubjectActivities();
                    $activity->activity_id = $list;	
                    $activity->subject_id  = $subject->id;
                    $activity->save();
                }
            }
            
            
        }
        catch(Exception $e){
            dd($e);
            die;
        }
                return redirect()->route('adminkpsc.subjects.index')->with('message','Data added Successfully');
    }
    public function subject_edit($id){
        
        $subject = KpscSubject::where('id',$id)->first();
        $activites = KpscActivities::get();
        $subject_activites = KpscSubjectActivities::where('subject_id',$id)->get();
        
        return view('cms.subjects_edit',compact('subject','activites','subject_activites'));

    }
    public function subject_update($id,Request $request){
        
        
        
        $subject = KpscSubject::where('id',$id)->first();
        $subject->subject_title = $request->subject_name;
        $subject->bg_color      = $request->bg_color;
        $subject->text_color    = $request->text_color;
        $subject->slug_name     = Str::slug($request->subject_name);
        $subject->status        = $request->status;
        $subject->position      = $request->position;
    
        try{
            $subject->save();
            
            KpscSubjectActivities::where('subject_id',$subject->id)->delete();
            if($request->has('activity')){

                foreach($request->activity as $list){
                $activity = new KpscSubjectActivities();
                    $activity->activity_id = $list;	
                    $activity->subject_id  = $subject->id;
                    $activity->save();
                }
            }
            
            
            if(strlen($request->image) > 0){
                $image  = file_get_contents($request->image);
                $name   = Str::random(40).'.png';
                
                Storage::put('/public/files/category/'.$name, $image);
                
                Storage::delete('/public/files/'.$subject->image);
                $subject->image = 'category/'.$name;
                $subject->save();
            }
            
        }
        catch(Exception $e){
            dd($e);
            die;
        }
                return redirect()->route('adminkpsc.subjects.index')->with('message','Data Updated Successfully');
    
    }
    public function subject_delete($id){
        

        try{
    
            KpscSubject::where('id',$id)->delete();
            KpscSubjectActivities::where('subject_id',$id)->delete();
        }
        catch(Exception $e){
            dd($e);
            die;
        }
        
        return redirect()->route('adminkpsc.subjects.index')->with('message','Data delted Successfully');
    
    }
    
    public function sub_subjects_all(){
        
        $all_subjects = KpscSubject::leftJoin('kpsc_subjects as parent','parent.id','kpsc_subjects.parent_id')->where('kpsc_subjects.type','child')
                                    ->select('kpsc_subjects.*','parent.subject_title as parent_title')->get();
     
        $parent_subjects = KpscSubject::where('kpsc_subjects.type','parent')->leftJoin('kpsc_subjects as parent','parent.id','kpsc_subjects.parent_id')
                                        ->select('kpsc_subjects.*','parent.subject_title as parent_title')->get();
        $activities = KpscActivities::get();
        return view('cms.sub_subjects_all',compact('all_subjects','activities','parent_subjects'));
    }
    
    public function sub_subject_store(Request $request){
        if($request->has('image')){
            $image = file_get_contents($request->image);
            $name = Str::random(40).'.png';
            Storage::put('/public/files/category/'.$name, $image);
        }	
        
        
        $subject = new KpscSubject();
        $subject->subject_title = $request->subject_name;
        $subject->bg_color      = $request->bg_color;
        $subject->text_color    = $request->text_color;
        $subject->image         = 'category/'.$name;
        $subject->slug_name     = Str::slug($request->subject_name);
        $subject->parent_id     = $request->parent;
        $subject->type          = 'child';
        $subject->status        = 1;
    
        try{
            $subject->save();
            $subject->position      = $subject->id;
            $subject->save();
            if($request->has('activity')){
                foreach($request->activity as $list){
                $activity = new KpscSubjectActivities();
                    $activity->activity_id = $list;	
                    $activity->subject_id  = $subject->id;
                    $activity->save();
                }
            }
            
            
        }
        catch(Exception $e){
            dd($e);
            die;
        }
        return redirect()->route('adminkpsc.sub-subjects.index')->with('message','Data added Successfully');
    }
    
    public function sub_subject_edit($id){
        $subject = KpscSubject::where('id',$id)->first();
        $activities = KpscActivities::get();
        $parent_subjects = KpscSubject::where('kpsc_subjects.type','parent')
                                        ->leftJoin('kpsc_subjects as parent','parent.id','kpsc_subjects.parent_id')
                                        ->select('kpsc_subjects.*','parent.subject_title as parent_title')
                                        ->get();
                                        
        $subject_activites = KpscSubjectActivities::where('subject_id',$id)->get();
        
        return view('cms.sub_subjects_edit',compact('subject','activities','subject_activites','parent_subjects'));

    }
    
    public function sub_subject_update($id,Request $request){
        $subject = KpscSubject::where('id',$id)->first();
        $subject->subject_title = $request->subject_name;
        $subject->bg_color      = $request->bg_color;
        $subject->text_color    = $request->text_color;
        $subject->slug_name     = Str::slug($request->subject_name);
        $subject->status        = $request->status;
        $subject->position      = $request->position;
        $subject->parent_id     = $request->parent;
        try{
            $subject->save();
            
            KpscSubjectActivities::where('subject_id',$subject->id)->delete();
            if($request->has('activity')){

                foreach($request->activity as $list){
                $activity = new KpscSubjectActivities();
                    $activity->activity_id = $list;	
                    $activity->subject_id  = $subject->id;
                    $activity->save();
                }
            }
            
            
            if(strlen($request->image) > 0){
                $image  = file_get_contents($request->image);
                $name   = Str::random(40).'.png';
                
                Storage::put('/public/files/category/'.$name, $image);
                
                Storage::delete('/public/files/'.$subject->image);
                $subject->image = 'category/'.$name;
                $subject->save();
            }
            
        }
        catch(Exception $e){
            dd($e);
            die;
        }
                return redirect()->route('adminkpsc.sub-subjects.index')->with('message','Data Updated Successfully');
    
    }
    
    public function sub_subject_delete($id){
       
        try{
    
            KpscSubject::where('id',$id)->delete();
            KpscSubjectActivities::where('subject_id',$id)->delete();
        }
        catch(Exception $e){
            dd($e);
            die;
        }
        
        return redirect()->route('adminkpsc.sub-subjects.index')->with('message','Data delted Successfully');
     
    }
    
    
}