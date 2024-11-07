<?php

namespace App\Http\Controllers\cms;

use App\Models\DailyBucket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;

use Illuminate\Support\Str;

class TodayBucketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dailyBucket_list = DailyBucket::orderBy('id','DESC')->get();
        $day_title = DailyBucket::groupBy('day_title')->get('day_title');
        return view('cms.daily-bucket', compact('dailyBucket_list','day_title'));
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
       
        if($request->type == 'Pdf'){
            $file = $request->file('pdf_file');
            $name1 = Str::random(40).'.'.$file->getClientOriginalExtension();
            $image1 = file_get_contents($file);
            Storage::put('/public/filesdaily-buckets/'.$name1, $image1);
        }
        else if($request->type == 'Voice Msg'){
            $file = $request->file('voice_file');
            $name1 = Str::random(40).'.'.$file->getClientOriginalExtension();
            $image1 = file_get_contents($file);
            Storage::put('/public/filesdaily-buckets/'.$name1, $image1);
        }
        else if($request->type == "Link"){
           $name1 =  $request->link;
        }
        
        else if($request->type == 'Text Msg'){
            $name1 = $request->content;
        }
        else
        {
            $name1="";
        }
       
        
        
        $caps = new DailyBucket;
        $caps->section = "Daily Buckets";
        $caps->type = $request->type;
        $caps->title = $request->title;
        $caps->content = $name1;
        $caps->class_date = $request->date;
        $caps->day_title = $request->day_title;
        $caps->save();
        
        
        return redirect()->back()->with('message','Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DailyBucket  $DailyBucket
     * @return \Illuminate\Http\Response
     */
    public function show(DailyBucket $DailyBucket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DailyBucket  $DailyBucket
     * @return \Illuminate\Http\Response
     */
    public function edit(DailyBucket $DailyBucket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DailyBucket  $DailyBucket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DailyBucket $DailyBucket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DailyBucket  $DailyBucket
     * @return \Illuminate\Http\Response
     */
    public function delete(DailyBucket $DailyBucket,$id)
    {
        //
        $bnr = $DailyBucket->where('id',$id)->first();
            if(($bnr->type == 'Pdf') || ($bnr->type == 'Voice Msg')) {
                Storage::delete('/public/daily-buckets/'.$bnr->content);
            }
           
        $bnr->delete();
      
        return redirect()->route('adminkpsc.daily-buckets.index')->with('message','Data Deleted Successfully');
    }
}
