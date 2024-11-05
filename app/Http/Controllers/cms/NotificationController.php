<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Helper;
use Storage;
  
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $notification = Notification::orderBy('id','desc')->get();
      
        return view('cms.notifications', compact('notification'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $notify = new Notification;
        $notify->title = $request->title;
        $notify->redirection =$request->redirection;
        $notify->save();

        return redirect()->route('adminkpsc.notifications.index')->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $notify = Notification::where('id',$id)->first();
     
        $edit_model = '
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Title</label>
                    <input class="form-control" type="text" name="title" value="'.$notify->title.'" placeholder="Enter Title" >
                   <input type="hidden" name="id" value="'.$notify->id.'">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Redirection</label>
                    <input class="form-control" type="text" name="redirection"  value="'.$notify->redirection.'" placeholder="Enter Redirection link" >
                    
                </div>
            </div>';

        return $edit_model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update_notify(Request $request, Notification $notification)
    {
        //
        $notifications = $notification->where('id',$request->id)->first();
        $notifications->title = $request->title;
        $notifications->redirection =  $request->redirection;
        $notifications->save();

        return redirect()->route('adminkpsc.notifications.index')->with('message','Data Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function delete(Notification $notification,$id)
    {
        //
        $notification->where('id',$id)->delete();
        return redirect()->route('adminkpsc.notifications.index')->with('message','Data Deleted Successfully');
    }
}
