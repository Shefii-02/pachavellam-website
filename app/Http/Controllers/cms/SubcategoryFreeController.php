<?php

namespace App\Http\Controllers\cms;

use App\Models\SubcategoryFree;
use App\Models\CategoryFree;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class SubcategoryFreeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $subcategory_free = SubcategoryFree::leftJoin('class_category_free','class_category_free.id','class_subcategory_free.category_id')
                                            ->select('class_category_free.category_name as category_name','class_subcategory_free.*')
                                            ->get();
        $category_free = CategoryFree::get();
      
        return view('cms.free_subcategory_class', compact('subcategory_free','category_free'));
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
        	
        	

        $subcategory = new SubcategoryFree;
        $subcategory->image = $name;
        $subcategory->category_id = $request->category_name;
        $subcategory->subcategory_name = $request->subcategory_name;
        $subcategory->name_slug = Str::slug($request->subcategory_name);
        $subcategory->status = 1;
        $subcategory->save();
        
        
        $subcategory->position = $subcategory->id;
        $subcategory->save();

       
        return redirect()->route('adminkpsc.free_subcategory_class.index')->with('message','Data added Successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubcategoryFree  $subcategoryFree
     * @return \Illuminate\Http\Response
     */
    public function show(SubcategoryFree $subcategoryFree)
    {
        //
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SubcategoryFree  $subcategoryFree
     * @return \Illuminate\Http\Response
     */
    public function edit(SubcategoryFree $subcategoryFree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubcategoryFree  $subcategoryFree
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubcategoryFree $subcategoryFree)
    {
        //
        $subcategory = $subcategoryFree->where('id',$request->id)->first();

        // if(strlen($request->image) > 0){
        //     $image  = file_get_contents($request->image);
        //     $name   = Str::random(40).'.png';
            
        //     Storage::put('/public/files'.$name, $image);
            
        //     Storage::delete('/public/'.$category->image);
        //     $category->image = $name;
        //     $category->save();
        // }
        
        $subcategory->category_id = $request->category_name;
        $subcategory->subcategory_name = $request->subcategory_name;
        $subcategory->name_slug  = Str::slug($request->subcategory_name);
        $subcategory->position   = $request->position;
        $subcategory->status     =  $request->status;
        $subcategory->save();

        return redirect()->route('adminkpsc.free_subcategory_class.index')->with('message','Data Updated Successfully');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubcategoryFree  $subcategoryFree
     * @return \Illuminate\Http\Response
     */
    public function delete(SubcategoryFree $subcategoryFree,$id)
    {
        //
        $bnr = $subcategoryFree->where('id',$id)->first();
        
        Storage::delete('/public/'.$bnr->image);
        $bnr->delete();

        return redirect()->route('adminkpsc.free_subcategory_class.index')->with('message','Data Deleted Successfully');

    }
}
