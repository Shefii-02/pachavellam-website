<?php

namespace App\Http\Controllers\cms;

use App\Models\Pscnews;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class PscnewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $psc_news = Pscnews::get();
      
        return view('cms.psc-news', compact('psc_news'));
       
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

        
        if ($request->hasFile('file') && $request->type == 'Pdf') {
            $file = $request->file('file');

            // Generate a unique file name
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

            // Move the file to the storage directory
            $file->storeAs('public/news/', $fileName);


        }
        
        
        $image = file_get_contents($request->image);
        $name = Str::random(40).'.png';
        
        Storage::put('/public/'.$name, $image);
        //
        $psc_news               = new Pscnews;
        $psc_news->title        = $request->title;
        $psc_news->slug         = Str::slug($request->title);
        $psc_news->type         = $request->type;
        $psc_news->post_date    = $request->date;
        $psc_news->image        = $name;
        $psc_news->status       = 1;
        $psc_news->description  = $request->type == 'Pdf' ? $fileName : $request->content;
        $psc_news->save();
        $psc_news->position     = $psc_news->id;
        $psc_news->save();

       
        return redirect()->route('adminkpsc.psc-news.index')->with('message','Data added Successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pscnews  $pscnews
     * @return \Illuminate\Http\Response
     */
    public function show(Pscnews $pscnews)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pscnews  $pscnews
     * @return \Illuminate\Http\Response
     */
    public function edit(Pscnews $pscnews)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pscnews  $pscnews
     * @return \Illuminate\Http\Response
     */
    public function update_pscnews(Request $request, Pscnews $pscnews)
    {
        //
        $psc_news = $pscnews->where('id',$request->id)->first();

 
            // $image  = file_get_contents($request->image);
            // $name   = Str::random(40).'.png';
           
            // Storage::put('/public/'.$name, $image);
            
            // Storage::delete('/public/'.$psc_news->image);
            // $psc_news->image = $name;
            // $psc_news->save();
        
            
             
        if ($request->hasFile('file') && $request->type == 'Pdf') {
            $file = $request->file('file');

            // Generate a unique file name
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

            // Move the file to the storage directory
            $file->storeAs('public/news/', $fileName);


        }
        
        $psc_news->slug         = Str::slug($request->title);
        $psc_news->type         = $request->type;
        $psc_news->title        = $request->title;
        $psc_news->post_date    = $request->date;
        $psc_news->status       = 1;
        $psc_news->description  = $request->type == 'Pdf' ? $fileName : $request->content;
        $psc_news->save();

        return redirect()->route('adminkpsc.psc-news.index')->with('message','Data Updated Successfully');



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pscnews  $pscnews
     * @return \Illuminate\Http\Response
     */
    public function delete(Pscnews $pscnews,$id)
    {
        //
        $del_news = $pscnews->where('id',$id)->first();
        
        Storage::delete('/public/'.$del_news->image);
        $del_news->delete();

        return redirect()->route('adminkpsc.psc-news.index')->with('message','Data Deleted Successfully');

    }
}
