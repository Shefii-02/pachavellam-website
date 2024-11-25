<?php

namespace App\Http\Controllers\cms;

use App\Models\Bulletin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class BulletinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $bulletin = Bulletin::orderBy('id','desc')->get();
      
        return view('cms.psc-bulletin', compact('bulletin'));
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
     
        $name = 'Psc-bullet-'.date("Y-M", strtotime($request->month)).'-Issued-'.$request->issue.'.pdf';
        dd($request->all());
        $image = file_get_contents($request->file('file'));
        Storage::put('/public/bullet-in/'.$name, $image);

        //
        $bullet_in = new Bulletin;
        $bullet_in->month_year = date('Y-m-d',strtotime($request->month));
        $bullet_in->issue = $request->issue;
        $bullet_in->file_name = $name;
        $bullet_in->status = 1;
        $bullet_in->save();
        $bullet_in->position = $bullet_in->id;
        $bullet_in->save();

        return redirect()->route('adminkpsc.psc-bulletin.index')->with('message','Data added Successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bulletin  $bulletin
     * @return \Illuminate\Http\Response
     */
    public function show(Bulletin $bulletin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bulletin  $bulletin
     * @return \Illuminate\Http\Response
     */
    public function edit(Bulletin $bulletin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bulletin  $bulletin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bulletin $bulletin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bulletin  $bulletin
     * @return \Illuminate\Http\Response
     */
    public function delete(Bulletin $bulletin,$id)
    {
        //
        $bnr = $bulletin->where('id',$id)->first();
        Storage::delete('/public/bullet-in/'.$bnr->file_name);
        $bnr->delete();

        
        return redirect()->route('adminkpsc.psc-bulletin.index')->with('message','Data Deleted Successfully');

    }
}
