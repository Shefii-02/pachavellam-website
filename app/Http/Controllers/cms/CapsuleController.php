<?php

namespace App\Http\Controllers\cms;

use App\Models\Capsule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use App\Models\CapsuleComment;
use App\Models\CapsuleLike;

use Illuminate\Support\Str;

class CapsuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $capsule_list = Capsule::orderBy('id','desc')->paginate(10);
      
        return view('cms.capsule', compact('capsule_list'));
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
        
        $caps = new Capsule;
        $caps->type  = $request->type;
        
        $caps->author_id = auth()->user()->id;
        $caps->status = 1;
        $caps->save();
       
           
        if($request->type == 'Image'){
            $image = file_get_contents($request->image);
            $name = Str::random(40).'.png';
            Storage::put('/public/files'.$name, $image);
            $caps->image = $name; 
            $caps->position = $caps->id;

        }
        else{
            $caps->content  = $request->content;
            
        }
                   $caps->save();
        return redirect()->route('adminkpsc.capsule.index')->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Capsule  $capsule
     * @return \Illuminate\Http\Response
     */
    public function show(Capsule $capsule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Capsule  $capsule
     * @return \Illuminate\Http\Response
     */
    public function edit(Capsule $capsule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Capsule  $capsule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Capsule $capsule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Capsule  $capsule
     * @return \Illuminate\Http\Response
     */
    public function delete(Capsule $capsule,$id)
    {
        //
        $bnr = $capsule->where('id',$id)->first();
        Storage::delete('/public/'.$bnr->image);
        $bnr->delete();
        CapsuleComment::where('cap_id',$id)->delete();
        CapsuleLike::where('cap_id',$id)->delete();

        
        return redirect()->route('adminkpsc.capsule.index')->with('message','Data Deleted Successfully');
    }
}
