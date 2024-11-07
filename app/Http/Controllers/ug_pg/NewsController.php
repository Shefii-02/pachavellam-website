<?php

namespace App\Http\Controllers\ug_pg;

use App\Models\UG_PG\News;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $news = News::get();
      
        return view('admin.ug-pg.news', compact('news'));
       
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

        
        $image = file_get_contents($request->image);
        $name = Str::random(40).'.png';
        
        Storage::put('/public/files'.$name, $image);
        //
        $psc_news               = new News;
        $psc_news->title        = $request->title;
        $psc_news->post_date    = $request->date;
        $psc_news->image        = $name;
        $psc_news->status       = 1;
        $psc_news->description  = $request->content;
        $psc_news->save();
        $psc_news->position     = $psc_news->id;
        $psc_news->save();

       
        return redirect()->back()->with('message','Data added Successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function show(News $news)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function edit(News $news)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\news  $news
     * @return \Illuminate\Http\Response
     */
    public function update_news(Request $request, news $news)
    {
        //
        $psc_news = $news->where('id',$request->id)->first();

 
            // $image  = file_get_contents($request->image);
            // $name   = Str::random(40).'.png';
           
            // Storage::put('/public/files'.$name, $image);
            
            // Storage::delete('/public/'.$psc_news->image);
            // $psc_news->image = $name;
            // $psc_news->save();
        
            
        
        
        $psc_news->title        = $request->title;
        $psc_news->post_date    = $request->date;
        $psc_news->status       = 1;
        $psc_news->description  = $request->content;
        $psc_news->save();

        return redirect()->back()->with('message','Data Updated Successfully');



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\news  $news
     * @return \Illuminate\Http\Response
     */
    public function delete(News $news,$id)
    {
        //
        $del_news = $news->where('id',$id)->first();
        
        Storage::delete('/public/'.$del_news->image);
        $del_news->delete();

        return redirect()->back()->with('message','Data Deleted Successfully');

    }
}
