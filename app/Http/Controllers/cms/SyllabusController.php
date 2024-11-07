<?php

namespace App\Http\Controllers\cms;

use App\Models\Syllabus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class SyllabusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $syllabus_list = Syllabus::get();
      
        return view('cms.syllabus', compact('syllabus_list'));
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
        $name = Str::random(40).'.pdf';
        
        $image = file_get_contents($request->file('file'));
       

        $syllabus_new  =  new Syllabus;
        $syllabus_new->type= $request->type;
        $syllabus_new->title = $request->title;
        $syllabus_new->category_no = $request->category_no;
        $syllabus_new->date = $request->post_date;
        $syllabus_new->file_name = $name;
        $syllabus_new->save();

        return redirect()->route('adminkpsc.syllabus.index')->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Syllabus  $syllabus
     * @return \Illuminate\Http\Response
     */
    public function show(Syllabus $syllabus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Syllabus  $syllabus
     * @return \Illuminate\Http\Response
     */
    public function edit(Syllabus $syllabus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Syllabus  $syllabus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Syllabus $syllabus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Syllabus  $syllabus
     * @return \Illuminate\Http\Response
     */
    public function delete(Syllabus $syllabus,$id)
    {
        //
        $bnr = $syllabus->where('id',$id)->first();
        Storage::delete('/public/files/'.$bnr->file_name);
        $bnr->delete();

        
        return redirect()->route('adminkpsc.syllabus.index')->with('message','Data Deleted Successfully');
    }
}
