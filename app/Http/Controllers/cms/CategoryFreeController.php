<?php

namespace App\Http\Controllers\cms;
use App\Models\KpscSubject;
use App\Models\CategoryFree;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class CategoryFreeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        
        $category_free = KpscSubject::get();
      
        return view('cms.free_category_class', compact('category_free'));
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
        
        $image = file_get_contents($request->image);
        $name = Str::random(40).'.png';
        
        Storage::put('/public/files'.$name, $image);
        			
        $category = new CategoryFree;
        $category->image = $name;
        $category->category_name = $request->category_name;
        $category->name_slug = Str::slug($request->category_name);
        $category->status = 1;
        $category->save();

        $category->position = $category->id;
        $category->save();

       
        return redirect()->route('adminkpsc.free_category_class.index')->with('message','Data added Successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CategoryFree  $categoryFree
     * @return \Illuminate\Http\Response
     */
    public function show(CategoryFree $categoryFree)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CategoryFree  $categoryFree
     * @return \Illuminate\Http\Response
     */
    public function edit(CategoryFree $categoryFree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CategoryFree  $categoryFree
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CategoryFree $categoryFree)
    {
        //
        $category = $categoryFree->where('id',$request->id)->first();

        // if(strlen($request->image) > 0){
        //     $image  = file_get_contents($request->image);
        //     $name   = Str::random(40).'.png';
            
        //     Storage::put('/public/files'.$name, $image);
            
        //     Storage::delete('/public/'.$category->image);
        //     $category->image = $name;
        //     $category->save();
        // }
        
        $category->category_name = $request->category_name;
        $category->name_slug  = Str::slug($request->category_name);
        $category->position   = $request->position;
        $category->status     =  $request->status;
        $category->save();

        return redirect()->route('adminkpsc.free_category_class.index')->with('message','Data Updated Successfully');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CategoryFree  $categoryFree
     * @return \Illuminate\Http\Response
     */
    public function delete(CategoryFree $categoryFree,$id)
    {
        //
        $bnr = $categoryFree->where('id',$id)->first();
        
        Storage::delete('/public/'.$bnr->image);
        $bnr->delete();

        return redirect()->route('adminkpsc.free_category_class.index')->with('message','Data Deleted Successfully');

    }
}
