<?php

namespace App\Http\Controllers\General;;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FeedbackPost;
use App\Models\FeedbackRequests;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends Controller
{
    
    public function feedback(){
         $list = FeedbackPost::with('requests')->get();
        return view('admin.general.feedback',compact('list'));
    }
    public function feedback_store(Request $request){
        $newone = new FeedbackPost();
        $newone->title = $request->title;
        $newone->description = $request->description;
        $newone->expdate = Carbon::createFromFormat('Y-m-d', $request->expdate)->setTime(23, 59, 0);
        $newone->section = $request->section;
        $randomNumber = mt_rand(1000000, 99999999);

        $newone->author  = $randomNumber;
        $newone->save();
        
        return redirect()->back()->with('message','Data added Successfully');
    }
    public function feedback_delete($id){
        
        FeedbackPost::with('requests')->where('id',$id)->delete();
        FeedbackRequests::where('feedback_id',$id)->delete();
         return redirect()->back()->with('message','Data Deleted  Successfully');
    }
    public function feedback_updating(Request $request){
        $newone         = FeedbackPost::whereId($request->id)->first();
        $newone->title  = $request->title;
        $newone->description = $request->description;
        $newone->expdate=  Carbon::createFromFormat('Y-m-d', $request->expdate)->setTime(23, 59, 0);
        $newone->section= $request->section;
        $newone->save();
        return redirect()->back()->with('message','Data updated Successfully');
    }
    
    public function feedback_requests($author){
        
        $feedback         = FeedbackPost::whereAuthor($author)->first();
        $list = FeedbackRequests::where('feedback_id',$feedback->id)->get();
        
        return view('admin.general.feedback-requests',compact('list'));
    }
    
    
}





