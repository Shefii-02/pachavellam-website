<?php

namespace App\Http\Controllers\cms;

use App\Models\WhatsNew;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helper\Reply;
use App\Helper;
use Storage;
  
use Illuminate\Support\Str;

class WhatsNewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $whatsnew = WhatsNew::get();
    
        return view('cms.whats-new', compact('whatsnew'));

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
   
        $image = file_get_contents($request->image);
        $name = Str::random(40).'.png';
        
        Storage::put('/public/files'.$name, $image);
        			

        $whatsnews = new WhatsNew;
        $whatsnews->image = $name;
        $whatsnews->title = $request->title;
        $whatsnews->redirection = $request->redirection;
        $whatsnews->bg_color = $request->bgcolor;
        $whatsnews->text_color = $request->textcolor;
        $whatsnews->save();

        $whatsnews->position = $whatsnews->id;
        $whatsnews->save();
        return redirect()->route('adminkpsc.whats-new.index')->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WhatsNew  $whatsNew
     * @return \Illuminate\Http\Response
     */
    public function show(WhatsNew $whatsNew)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WhatsNew  $whatsNew
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $whats_new = WhatsNew::where('id',$id)->first();
        $edit_modal = '<div class="row">
                        <div class="col-md-12">
                            <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                <!-- Image -->
                                <img src="'.Storage::url('files/'.$whats_new->image).'" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                <div>
                                    <label style="cursor:pointer;">
                                        <span> 
                                            <input class="form-control stretched-link" type="file"  accept="image/*" id="pic2" name="my-image" accept="image/gif, image/jpeg, image/png" />
                                            <input type="hidden" name="image" id="picture2" />
                                        </span>
                                    </label>
                                </div>	
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <label class="form-label">Title</label>
                                <input type="hidden" value="'.$whats_new->id.'" name="id">
                                <input class="form-control" value="'.$whats_new->title.'" type="text"  name="title" required placeholder="Enter Title" >
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Redirection</label>
                                <input class="form-control" value="'.$whats_new->redirection.'" type="text"  name="redirection" required placeholder="Enter Redirection link" >
                            </div>
                        </div>
                        <div class="col-md-12 row">
                            <div class="col-lg-6">
                                <label class="form-label"> Position</label>
                                <input class="form-control" name="position" type="text" value="'.$whats_new->position.'" placeholder="Showing Position" >
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" >
                                    <option value="1">Show</option>
                                    <option value="0">Hide</option>
                                </select>
                            </div>
                        
                        </div>
                        <div class="col-md-12 row">
                            <div class="col-md-6">
                                <label class="form-label">Bacground Color</label>
                                <input class="form-control" value="'.$whats_new->bg_color.'" type="color" name="bgcolor" required placeholder="Section background color" >
                            
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Text Color</label>
                                <input class="form-control" value="'.$whats_new->text_color.'" type="color" name="textcolor" required placeholder="Title text color" >
                                
                            </div>
                            <div classs="col-md-12 ">
                                <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Submit</button>
                            </div>
                        </div>
                    </div>';

            return $edit_modal;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WhatsNew  $whatsNew
     * @return \Illuminate\Http\Response
     */
    public function update_whatsnew(Request $request, WhatsNew $whatsNew)
    {
        //
   
        $whatsnews = $whatsNew->where('id',$request->id)->first();

        if(strlen($request->image) > 0){
            $image  = file_get_contents($request->image);
            $name   = Str::random(40).'.png';
            
            Storage::put('/public/files'.$name, $image);
            
            Storage::delete('/public/'.$whatsnews->image);

            $whatsnews->image = $name;
            $whatsnews->save();
        }

        $whatsnews->title = $request->title;
        $whatsnews->redirection = $request->redirection;
        $whatsnews->bg_color = $request->bgcolor;
        $whatsnews->text_color = $request->textcolor;
        $whatsnews->position = $request->position;
        $whatsnews->status = $request->status;
        $whatsnews->save();
        return redirect()->route('adminkpsc.whats-new.index')->with('message','Data Updated Successfully');



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WhatsNew  $whatsNew
     * @return \Illuminate\Http\Response
     */
    public function delete($id,WhatsNew $whatsNew)
    {
        //
        $wtsnew = $whatsNew->where('id',$id)->first();
        Storage::delete('/public/'.$wtsnew->image);
        $wtsnew->delete();

     
        return redirect()->route('adminkpsc.whats-new.index')->with('message','Data Deleted Successfully');

    }
}
