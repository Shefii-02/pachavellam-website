<?php

namespace App\Http\Controllers\cms;

use App\Models\ClassFree;
use App\Models\CategoryFree;
use App\Models\SubcategoryFree;
use App\Imports\FreeclassImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use App\Models\KpscSubject;
use Storage;
use Illuminate\Support\Str;

class ClassFreeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $free_class = ClassFree::get();
        $subjects = KpscSubject::where('status','show')->orderby('position')->get();

        return view('cms.free-class', compact('free_class','subjects'));
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
       

        if(count($request->title)>0){
            for($i=0;$i<count($request->title);$i++){
                $class_free = new ClassFree;
                $class_free->category_id = $request->category_name; 
                // $class_free->subcategory_id = $request->subcategory_name;
                $class_free->title = $request->title[$i]; 
                $class_free->link = $request->link[$i]; 
                $class_free->status = 1;
                $class_free->save();
                $class_free->position = $class_free->id;
                $class_free->save();
            }
        }

        return redirect()->route('adminkpsc.free-class.index')->with('message','Data added Successfully');

       
    }


    public function freeclass_csv(Request $request){
        
        Excel::import(new FreeclassImport,request()->file('file'));
            
        return redirect()->route('adminkpsc.free-class.index')->with('message','File uploaded Successfully');
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClassFree  $classFree
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request,ClassFree $classFree)
    {
        //
        $category_name = $request->category_name;
        $subcategory_name = $request->subcategory_name;

       
       $class_list = ClassFree::leftJoin('kpsc_subjects','kpsc_subjects.id','class_link_free.category_id')
                                // ->leftJoin('class_subcategory_free','class_subcategory_free.id','class_link_free.subcategory_id')
                                // ->where('class_link_free.category_id',$category_name)
                                // ->where(function($query2) use ($subcategory_name){
                                //     $query2->where('class_link_free.subcategory_id', '=', $subcategory_name)
                                //     ->orWhere('class_link_free.subcategory_id', '=', null);
                                // })
                                ->select('kpsc_subjects.subject_title','class_link_free.*');
        if($request->category_name != ''){
            $class_list = $class_list->where('class_link_free.category_id',$category_name);
        }
        $class_list = $class_list->get();
 
        $free_class = ClassFree::get();
        $subjects = KpscSubject::where('status','show')->orderby('position')->get();

        return view('cms.free-class', compact('free_class','subjects','class_list'));
                
                  

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClassFree  $classFree
     * @return \Illuminate\Http\Response
     */
    public function edit(ClassFree $classFree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ClassFree  $classFree
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClassFree $classFree)
    {
        //
                $class_free = $classFree->where('id',$request->id)->first();
                $class_free->category_id = $request->category_name; 
                // $class_free->subcategory_id = $request->subcategory_name;
                $class_free->title = $request->title; 
                $class_free->link = $request->link; 
                $class_free->status = $request->status;
                $class_free->position =  $request->position;
                $class_free->save();
        
                return redirect()->back()->with('message','Data Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClassFree  $classFree
     * @return \Illuminate\Http\Response
     */
    public function delete(ClassFree $classFree,$id)
    {
        //
        $classFree->where('id',$id)->delete();

        return redirect()->route('adminkpsc.free-class.index')->with('message','Data Deleted Successfully');

    }
}
