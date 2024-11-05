<?php

namespace App\Http\Controllers\cms;

use App\Models\NewmodalQa;
use App\Models\KpscSubject;
use App\Imports\NewModalqaimport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class NewmodalQaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $newqa_list = NewmodalQa::get();
        $subjects = KpscSubject::where('status','show')->orderby('position')->get();

        return view('cms.new-qa', compact('newqa_list','subjects'));
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
        if(count($request->question)>0){
            for($i=0;$i<count($request->question);$i++){
                $new_qa = new NewmodalQa;
                $new_qa->question=$request->question[$i];
                $new_qa->currect_ans=$request->answer[$i];
                $new_qa->options="{{".$request->option1[$i]."}},,{{".$request->option2[$i]."}},,{{".$request->option3[$i]."}},,{{".$request->option4[$i]."}}";
                $new_qa->status=0;
                $new_qa->save();
                $new_qa->position=$new_qa->id;
                $new_qa->subject = $request->subject_name ?? null;
                $new_qa->save();
            }
        }

        return redirect()->route('adminkpsc..new-qa.index')->with('message','Data added Successfully');

    }

    public function csvtype_store(Request $request){
      
// ,
        Excel::import(new NewModalqaimport,request()->file('file'));
            
        return redirect()->route('adminkpsc..new-qa.index')->with('message','File uploaded Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NewmodalQa  $newmodalQa
     * @return \Illuminate\Http\Response
     */
    public function show(NewmodalQa $newmodalQa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NewmodalQa  $newmodalQa
     * @return \Illuminate\Http\Response
     */
    public function edit(NewmodalQa $newmodalQa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NewmodalQa  $newmodalQa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewmodalQa $newmodalQa)
    {
        //
        $newmodal = NewmodalQa::where('id',$request->id)->first();
        $newmodal->subject = $request->subject_name ?? null;
        $newmodal->question = $request->question;
        $newmodal->currect_ans = $request->answer;
        $newmodal->options = "{{".$request->option1."}},,{{".$request->option2."}},,{{".$request->option3."}},,{{".$request->option4."}}";
        $newmodal->save();
        return redirect()->back()->with('message','Data Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewmodalQa  $newmodalQa
     * @return \Illuminate\Http\Response
     */
    public function delete(NewmodalQa $newmodalQa, $id)
    {
        //
        $newmodalQa->where('id',$id)->delete();
        return redirect()->back()->with('message','Data Deleted Successfully');
    }
}
