<?php

namespace App\Http\Controllers\cms;


use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;


class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $banner = Banner::get();
      
        return view('cms.banner-slider', compact('banner'));

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

       

        $image = file_get_contents($request->image);
        $name = Str::random(40).'.png';
        
        
        			
        $banner = new Banner;
        $banner->image = $name;
        $banner->redirection = $request->redirection;
        $banner->save();

        $banner->position = $banner->id;
        $banner->save();

       
        return redirect()->route('adminkpsc.banner-slider.index')->with('message','Data added Successfully');
        //
      
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function show(Banner $banner)
    {
        //
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function edit(Banner $banner,$id)
    {
        //
        $banner_list = $banner->where('id',$id)->first();
     
        $edit_model = '
       
        <div class="row">
                        
        <div class="col-md-12">
            <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                <!-- Image -->
                <img src="'.Storage::url('files/'.$banner_list->image).'" id="uploaded-image2" class="uploaded-image2 h-50px mb-2" alt="">
                <div>
                    <label style="cursor:pointer;">
                        <span> 
                            <input class="form-control stretched-link" type="file"  accept="image/*" id="pic2" name="my-image"  accept="image/gif, image/jpeg, image/png" />
                            <input type="hidden" name="image" id="picture2" />
                        </span>
                    </label>
                </div>	
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-lg-12">
                    <label class="form-label">Redirection</label>
                    <input type="hidden" value="'.$banner_list->id.'" name="id">
                    <input class="form-control" type="text" name="redirection" value="'.$banner_list->redirection.'" placeholder="Enter Redirection link" >
                </div>
                <div class="col-lg-12">
                    <label class="form-label"> Position</label>
                    <input class="form-control" name="position" type="text" value="'.$banner_list->position.'" placeholder="Showing Position" >
                </div>
                <div class="col-lg-12">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" >
                        <option value="1">Show</option>
                        <option value="0">Hide</option>
                    </select>
                </div>
            </div>
        </div> 
    </div>';
        return $edit_model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function update_slider(Request $request, Banner $banner)
    {
        //
        $banners = $banner->where('id',$request->id)->first();

        if(strlen($request->image) > 0){
            $image  = file_get_contents($request->image);
            $name   = Str::random(40).'.png';
            
 
            
            Storage::delete('/public/files/'.$banners->image);
            $banners->image = $name;
            $banners->save();
        }
        
        
        $banners->redirection = $request->redirection;
        $banners->position   = $request->position;
        $banners->status     =  $request->status;
        $banners->save();

        return redirect()->route('adminkpsc.banner-slider.index')->with('message','Data Updated Successfully');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function delete(Banner $banner,$id)
    {
        //
        $bnr = $banner->where('id',$id)->first();
        
        $bnr->delete();

        Storage::delete('/public/files/'.$bnr->image);
        return redirect()->route('adminkpsc.banner-slider.index')->with('message','Data Deleted Successfully');

    }
}
