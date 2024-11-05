<?php

namespace App\Http\Controllers\cms;

use App\Models\Ourvideos;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OurvideosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $our_videos = Ourvideos::get();
      
        return view('cms.our-videos', compact('our_videos'));
       
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
        $our_videos = new Ourvideos;
        $our_videos->title = $request->title;
        $our_videos->redirection = $request->redirection;
        $our_videos->save();
        $our_videos->status = 1;
        $our_videos->position = $our_videos->id;
        $our_videos->save();

        return redirect()->route('adminkpsc.our-videos.index')->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ourvideos  $ourvideos
     * @return \Illuminate\Http\Response
     */
    public function show(Ourvideos $ourvideos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ourvideos  $ourvideos
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ourvideos =Ourvideos::where('id',$id)->first();

        $edit_modal = ' <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input class="form-control"  value="'.$ourvideos->title.'" type="text" name="title" placeholder="Enter Youtube Title" >
                            <input type="hidden" value="'.$ourvideos->id.'" name="id">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Redirection</label>
                            <input class="form-control" value="'.$ourvideos->redirection.'" type="text" name="redirection" placeholder="Enter Youtube link" >
                        
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label"> Position</label>
                            <input class="form-control" name="position" type="text" value="'.$ourvideos->position.'" placeholder="Showing Position" >
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" >
                                <option value="1">Show</option>
                                <option value="0">Hide</option>
                            </select>
                        </div>
                       ';

            return  $edit_modal;

        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ourvideos  $ourvideos
     * @return \Illuminate\Http\Response
     */
    public function update_ourvideos (Request $request, Ourvideos $ourvideos)
    {
        //
        $our_videos =Ourvideos::where('id',$request->id)->first();
        $our_videos->title = $request->title;
        $our_videos->redirection = $request->redirection;
        $our_videos->status = $request->status;
        $our_videos->position = $request->position;
        $our_videos->save();
        
        return redirect()->route('adminkpsc.our-videos.index')->with('message','Data Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ourvideos  $ourvideos
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
         Ourvideos::where('id',$id)->delete();

        return redirect()->route('adminkpsc.our-videos.index')->with('message','Data Deleted Successfully');

    }
}
