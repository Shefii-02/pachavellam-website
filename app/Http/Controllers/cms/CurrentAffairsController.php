<?php

namespace App\Http\Controllers\cms;

use App\Models\CurrentAffairs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\DailyCA;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class CurrentAffairsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $daily_ca = CurrentAffairs::select('year')->groupBy('year')->get();

        $daily_ca_month = CurrentAffairs::select('month')->groupBy('month')->get();
      
        $daily_ca_day = CurrentAffairs::select('day')->groupBy('day')->get();
      
        return view('cms.daily-ca', compact('daily_ca','daily_ca_month','daily_ca_day'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetch_months(Request $request)
    {
        //
        $daily_ca = CurrentAffairs::where('year',$request->year)->select('month')->groupBy('month')->get();
   
        $optn = "<option></option>";
        foreach($daily_ca as $key=> $monthlist){
            $optn .= "<option>".$monthlist->month."</option>";
        }

        return $optn;
    }

    public function fetch_month_basedlist(Request $request){
     
        $daily_ca = CurrentAffairs::where('year',$request->year)
                                    ->where('month',$request->month)
                                    ->select('day')
                                    ->groupBy('day')
                                    ->get();
   
        $optn = "";
        foreach($daily_ca as $key=> $monthlist){
            $optn .= "<option >".$monthlist->day."</option>";
        }

        return $optn; 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function linktype_store(Request $request)
    {
        //    
       
        if(count($request->title)>0){
            for($i=0;$i<count($request->title);$i++){
                $daily_ca = new CurrentAffairs;
                $daily_ca->year = $request->year; 
                $daily_ca->month = $request->month;
                $daily_ca->day   = null;
                $daily_ca->type =  "Link";
                $daily_ca->title = $request->title[$i];
                $daily_ca->file_name = $request->link[$i];
                $daily_ca->question = null;
                $daily_ca->answer = null;
                $daily_ca->note = null;
                $daily_ca->status = 1;
                $daily_ca->save();

                $daily_ca->position = $daily_ca->id;
                $daily_ca->save();
            }
        }

        return redirect()->route('adminkpsc.daily-ca.index')->with('message','Data added Successfully');


    }

    public function pdftype_store(Request $request){

        $name1 = Str::random(40).'.pdf';
        
        $image1 = file_get_contents($request->file('file'));
        Storage::put('/public/files/current-affairs/'.$name1, $image1);

        $daily_ca = new CurrentAffairs;
        $daily_ca->year = $request->year; 
        $daily_ca->month = $request->month;
        $daily_ca->day   = null;
        $daily_ca->type =  "Pdf";
        $daily_ca->title = $request->title;
        $daily_ca->file_name = 'current-affairs/'.$name1;
        $daily_ca->question = null;
        $daily_ca->answer = null;
        $daily_ca->note = null;
        $daily_ca->status = 1;
        $daily_ca->save();

        $daily_ca->position = $daily_ca->id;
        $daily_ca->save();

        return redirect()->route('adminkpsc.daily-ca.index')->with('message','File uploaded Successfully');

    }

    public function csvtype_store(Request $request){
        $request->validate([
            'file' => 'required|mimes:csv,txt,application/csv,application/vnd.ms-excel|max:2048',
        ]);
        
        Excel::import(new DailyCA,request()->file('file'));
            
        return redirect()->route('adminkpsc.daily-ca.index')->with('message','File uploaded Successfully');
    }

    public function store(Request $request){
      
    // ,
    

        if(count($request->question)>0){
            for($i=0;$i<count($request->question);$i++){
                $daily_ca = new CurrentAffairs;
                $daily_ca->year  = $request->year; 
                $daily_ca->month = $request->month;
                $daily_ca->day   = $request->day;
                $daily_ca->type  =  "Day";
                $daily_ca->title = null;
                $daily_ca->file_name = null;
                $daily_ca->question = $request->question[$i];;
                $daily_ca->answer = $request->answer[$i];;
                $daily_ca->note   = $request->note[$i];;
                $daily_ca->status = 1;
                $daily_ca->save();

                $daily_ca->position = $daily_ca->id;
                $daily_ca->save();
            }
        }

        return redirect()->route('adminkpsc.daily-ca.index')->with('message','Data added Successfully');


    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CurrentAffairs  $currentAffairs
     * @return \Illuminate\Http\Response
     */
    public function ca_showlist(Request $request,CurrentAffairs $currentAffairs)
    {
        //
        $daily_ca_list = CurrentAffairs::where('year',$request->year)
                                        ->where('month',$request->month)
                                        ->where('day',$request->day)
                                        ->get();

        $daily_ca = CurrentAffairs::select('year')->groupBy('year')->get();
        $daily_ca_month = CurrentAffairs::select('month')->groupBy('month')->get();
      
        $daily_ca_day = CurrentAffairs::select('day')->groupBy('day')->get();


        return view('cms.daily-ca', compact('daily_ca','daily_ca_list','daily_ca_month','daily_ca_day'));                
   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CurrentAffairs  $currentAffairs
     * @return \Illuminate\Http\Response
     */
    public function edit(CurrentAffairs $currentAffairs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CurrentAffairs  $currentAffairs
     * @return \Illuminate\Http\Response
     */
    public function update_ca_link(Request $request){
        $daily_ca = CurrentAffairs::where('id',$request->id)->first();
        $daily_ca->year = $request->year; 
        $daily_ca->month = $request->month;
        $daily_ca->title = $request->title;
        $daily_ca->file_name = $request->link;
        $daily_ca->status = 1;
        $daily_ca->save();
             
        return redirect()->back()->with('message','Data Updated Successfully');
    }
    public function update_ca_pdf(Request $request){

          
            $daily_ca = CurrentAffairs::where('id',$request->id)->first();
            $daily_ca->year = $request->year; 
            $daily_ca->month = $request->month;
            $daily_ca->title = $request->title;
            $daily_ca->status = 1;
            $daily_ca->save();

            if($request->has('file')){

                $name1 = Str::random(40).'.pdf';
                $image1 = file_get_contents($request->file('file'));
                Storage::put('/public/files/current-affairs/'.$name1, $image1);
                Storage::delete('/public/files/'.$daily_ca->file_name);
                $daily_ca->file_name = 'current-affairs/'.$name1;
                $daily_ca->save();
            }

            return redirect()->back()->with('message','Data Updated Successfully');
    }
    public function update_ca_day(Request $request){
       
            $daily_ca = CurrentAffairs::where('id',$request->id)->first();
            $daily_ca->year  = $request->year; 
            $daily_ca->month = $request->month;
            $daily_ca->day   = $request->day;
            $daily_ca->question = $request->question;
            $daily_ca->answer = $request->answer;
            $daily_ca->note   = $request->note;
            $daily_ca->status = 1;
            $daily_ca->save();
            return redirect()->back()->with('message','Data Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CurrentAffairs  $currentAffairs
     * @return \Illuminate\Http\Response
     */
    public function delete(CurrentAffairs $currentAffairs,$id)
    {
        //
        $bnr = $currentAffairs->where('id',$id)->first();
        Storage::delete('/public/files/'.$bnr->file_name);
        $bnr->delete();

        
        return redirect()->back()->with('message','Data Deleted Successfully');

    }


   
}
