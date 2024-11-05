<?php

namespace App\Http\Controllers\ug_pg;

use App\Models\UG_PG\SpecialVideo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SpecialVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $spcl_videos = SpecialVideo::get();
        $category =  SpecialVideo::select('category')->distinct('category')->get();
      
        return view('admin.ug-pg.special-videos', compact('spcl_videos','category'));
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
        $spcl_videos = new SpecialVideo;
        $spcl_videos->title = $request->title;
        $spcl_videos->category = $request->category;
        $spcl_videos->redirection = $request->redirection;
        $spcl_videos->status = 1;
        $spcl_videos->save();
        $spcl_videos->position = $spcl_videos->id;
        $spcl_videos->save();

        return redirect()->back()->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function show(SpecialVideo $specialVideo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $ourvideos = SpecialVideo::where('id',$id)->first();
        $category =  SpecialVideo::select('category')->distinct('category')->get();
        $edit_modal = ' 
                        <div class="col-md-12">
                            <label class="form-label">Category</label>
                            <input autocomplete="off" value="'.$ourvideos->category.'" class="form-control" list="category" type="text" name="category" placeholder="Enter Video Category" >
                                <datalist id="category">';
                                foreach($category as $cat){
                                    $edit_modal .="<option value='".$cat->category."'></option>";
                                }
                               
        $edit_modal .=  ' </datalist>
                            <input type="hidden" value="'.$ourvideos->id.'" name="id">
                        </div>
                        <div class="col-md-12">
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
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function update_spclvideos(Request $request, SpecialVideo $specialVideo)
    {
        //
        $our_videos =SpecialVideo::where('id',$request->id)->first();
        $our_videos->title = $request->title;
        $our_videos->category = $request->category;
        $our_videos->redirection = $request->redirection;
        $our_videos->status = $request->status;
        $our_videos->position = $request->position;
        $our_videos->save();
        
        return redirect()->back()->with('message','Data Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
        SpecialVideo::where('id',$id)->delete();

        return redirect()->back()->with('message','Data Deleted Successfully');
    }
}
