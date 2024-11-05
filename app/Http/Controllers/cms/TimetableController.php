<?php

namespace App\Http\Controllers\cms;

use App\Models\Timetable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

        
        


class TimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $time_table = Timetable::get();
      
        return view('cms.time-table', compact('time_table'));
       
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
        $name = Str::random(40).'.pdf';
        $image = file_get_contents($request->file('file'));
        Storage::put('/public/time-table/'.$name, $image);
        
        $new_one = new Timetable;
        $new_one->title = $request->title;
        $new_one->file = $name;
        $new_one->status = 1;
        $new_one->save();
        $new_one->position = $new_one->id;
        $new_one->save();

        return redirect()->route('adminkpsc.time-table.index')->with('message','Data added Successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Timetable  $ourvideos
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
        
        $tble = Timetable::where('id',$id)->first();
        Storage::delete('/public/time-table'.$tble->file);
        $tble->delete();
        

        return redirect()->route('adminkpsc.time-table.index')->with('message','Data Deleted Successfully');

    }
}
