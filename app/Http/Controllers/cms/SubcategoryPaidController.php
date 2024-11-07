<?php

namespace App\Http\Controllers\cms;

use App\Models\SubcategoryPaid;
use App\Models\CategoryPaid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class SubcategoryPaidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $subcategory_paid = SubcategoryPaid::leftJoin('class_category_paid','class_category_paid.id','class_subcategory_paid.category_id')
                                            ->select('class_category_paid.category_name as category_name','class_subcategory_paid.*')
                                            ->get();
        $category_paid = CategoryPaid::get();

        return view('cms.paid_subcategory_class', compact('subcategory_paid','category_paid'));
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
        
        Storage::put('/public/files/category/'.$name, $image);
        	
        	

        $subcategory = new SubcategoryPaid;
        $subcategory->image = 'category/'.$name;
        $subcategory->category_id = $request->category_name;
        $subcategory->subcategory_name = $request->subcategory_name;
        $subcategory->name_slug = Str::slug($request->subcategory_name);
        $subcategory->status = 1;
        $subcategory->save();
        
        
        $subcategory->position = $subcategory->id;
        $subcategory->save();

       
        return redirect()->route('kpsc/cms.paid_subcategory_class.index')->with('message','Data added Successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubcategoryPaid  $subcategoryPaid
     * @return \Illuminate\Http\Response
     */
    public function show(SubcategoryPaid $subcategoryPaid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SubcategoryPaid  $subcategoryPaid
     * @return \Illuminate\Http\Response
     */
    public function edit(SubcategoryPaid $subcategoryPaid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubcategoryPaid  $subcategoryPaid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubcategoryPaid $subcategoryPaid)
    {
        //
         //
         $subcategory = $subcategoryPaid->where('id',$request->id)->first();

         // if(strlen($request->image) > 0){
         //     $image  = file_get_contents($request->image);
         //     $name   = Str::random(40).'.png';
             
         //     Storage::put('/public/files/'.$name, $image);
             
         //     Storage::delete('/public/files/'.$category->image);
         //     $category->image = $name;
         //     $category->save();
         // }
         
         $subcategory->category_id = $request->category_name;
         $subcategory->subcategory_name = $request->subcategory_name;
         $subcategory->name_slug  = Str::slug($request->subcategory_name);
         $subcategory->position   = $request->position;
         $subcategory->status     =  $request->status;
         $subcategory->save();
 
         return redirect()->route('kpsc/cms.paid_subcategory_class.index')->with('message','Data Updated Successfully');
 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubcategoryPaid  $subcategoryPaid
     * @return \Illuminate\Http\Response
     */
    public function delete(SubcategoryPaid $subcategoryPaid,$id)
    {
        //
        $bnr = $subcategoryPaid->where('id',$id)->first();
        
        Storage::delete('/public/files/'.$bnr->image);
        $bnr->delete();

        return redirect()->route('kpsc/cms.paid_subcategory_class.index')->with('message','Data Deleted Successfully');

    }
}
