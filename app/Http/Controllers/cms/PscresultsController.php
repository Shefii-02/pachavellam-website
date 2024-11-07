<?php

namespace App\Http\Controllers\cms;

use App\Models\Pscresults;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class PscresultsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $result_list = Pscresults::get();
      
        return view('cms.psc-results', compact('result_list'));
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
        Storage::put('/public/files/'.$name, $image);

        $result_new  =  new Pscresults;
        $result_new->type= str_replace('-',' ',$request->type);
        $result_new->title = $request->title;
        $result_new->file_name = $name;
        $result_new->save();

        return redirect()->route('adminkpsc.psc-results.index')->with('message','Data added Successfully');


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pscresults  $pscresults
     * @return \Illuminate\Http\Response
     */
    public function show(Pscresults $pscresults)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pscresults  $pscresults
     * @return \Illuminate\Http\Response
     */
    public function edit(Pscresults $pscresults)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pscresults  $pscresults
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pscresults $pscresults)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pscresults  $pscresults
     * @return \Illuminate\Http\Response
     */
    public function delete(Pscresults $pscresults,$id)
    {
        //
        $bnr = $pscresults->where('id',$id)->first();
        Storage::delete('/public/files/'.$bnr->file_name);
        $bnr->delete();

        
        return redirect()->route('adminkpsc.psc-results.index')->with('message','Data Deleted Successfully');
   
    }
}
