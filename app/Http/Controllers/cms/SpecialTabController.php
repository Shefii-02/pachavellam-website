<?php

namespace App\Http\Controllers\cms;

use App\Models\SpecialTab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;


class SpecialTabController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $special_tb = SpecialTab::get();
      
        return view('cms.special-tab', compact('special_tb'));
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
        
   

        $special_tab = new SpecialTab;
        $special_tab->image =  $name;
        $special_tab->redirection =   $request->redirection;
        $special_tab->save();

        $special_tab->position = $special_tab->id;
        $special_tab->save();

        return redirect()->route('adminkpsc.special-tab.index')->with('message','Data added Successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SpecialTab  $specialTab
     * @return \Illuminate\Http\Response
     */
    public function show(SpecialTab $specialTab)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SpecialTab  $specialTab
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $special_tab = SpecialTab::where('id',$id)->first();

        $edit_modal =   '<div class="row">
                            <div class="col-md-12">
                                <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                    <!-- Image -->
                                    <img src="'.Storage::url('files/'.$special_tab->image).'" id="uploaded-image" class="uploaded-image h-50px mb-2" alt="">
                                    <div>
                                        <label style="cursor:pointer;">
                                            <span> 
                                                <input class="form-control stretched-link" type="file"   accept="image/*" id="pic1" name="my-image"  accept="image/gif, image/jpeg, image/png" />
                                                <input type="hidden" name="image" id="picture1" />
                                            </span>
                                        </label>
                                    </div>	
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Redirection</label>
                                <input class="form-control" value="'.$special_tab->redirection.'" type="text" name="redirection" placeholder="Enter Redirection link" >
                                <input type="hidden" name="id" value="'.$special_tab->id.'">
                            </div>
                            <div class="col-lg-12">
                                <label class="form-label"> Position</label>
                                <input class="form-control" name="position" type="text" value="'.$special_tab->position.'" placeholder="Showing Position" >
                            </div>
                            <div class="col-lg-12">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" >
                                    <option value="1">Show</option>
                                    <option value="0">Hide</option>
                                </select>
                            </div>
                            
                        </div>';

            return $edit_modal;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SpecialTab  $specialTab
     * @return \Illuminate\Http\Response
     */
    public function update_specialtab(Request $request, SpecialTab $specialTab)
    {
        //
        $special_tab = $specialTab->where('id',$request->id)->first();

        if(strlen($request->image) > 0){
            $image  = file_get_contents($request->image);
            $name   = Str::random(40).'.png';
            
        
            
            Storage::delete('/public/files/'.$special_tab->image);
            $special_tab->image = $name;
            $special_tab->save();
        }
        
        
        $special_tab->redirection = $request->redirection;
        $special_tab->position   = $request->position;
        $special_tab->status     =  $request->status;
        $special_tab->save();

        return redirect()->route('adminkpsc.special-tab.index')->with('message','Data Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SpecialTab  $specialTab
     * @return \Illuminate\Http\Response
     */
    public function delete(SpecialTab $specialTab,$id)
    {
        //
        $bnr = $specialTab->where('id',$id)->first();
        
        $bnr->delete();

        Storage::delete('/public/files/'.$bnr->image);
        return redirect()->route('adminkpsc.special-tab.index')->with('message','Data Deleted Successfully');
    }
}
