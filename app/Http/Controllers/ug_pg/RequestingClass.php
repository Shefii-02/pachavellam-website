<?php

namespace App\Http\Controllers\ug_pg;

use App\Models\UG_PG\SpecialVideo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UG_PG\RequestClass;

class RequestingClass extends Controller
{
    public function index(){
        $requested_class = RequestClass::where('status','<>',2)->orderby('id','desc')->get();
        
        return view('admin.ug-pg.requested_class',compact('requested_class'));
    }
    

    public function delete($id,Request $request){
        $del = RequestClass::where('id',$id)->first();
        $del->status = 2;
        $del->save();
        
        return redirect()->back()->with('message','Data Deleted Successfully');
    }
    public function edit($id,Request $request){
        $show  = RequestClass::where('id',$id)->first();
        
        return view('admin.ug-pg.requested_class_edit',compact('show'));
    }
    public function update($id,Request $request){
        $update  = RequestClass::where('id',$id)->first();
        $update->name = $request->name;
        $update->email = $request->email;
        $update->mobile = $request->mobile_no;
        $update->college = $request->college;
        $update->course = $request->course;
        $update->subject = $request->subject;
        $update->type = $request->type;
        $update->status = $request->status;
        $update->comments = $request->comments;
        $update->save();
        
        return redirect()->back()->with('message','Data Updated Successfully');
    }
}
?>