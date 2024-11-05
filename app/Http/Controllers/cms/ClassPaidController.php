<?php

namespace App\Http\Controllers\cms;

use App\Models\ClassPaid;
use App\Models\CategoryPaid;
use App\Models\SubcategoryPaid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\PaidclassImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\KpscSubject;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class ClassPaidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $paid_class = ClassPaid::get();
        $category_paid = CategoryPaid::get();
        $subcategory_paid = SubcategoryPaid::get();
        
        $subjects = KpscSubject::where('status','show')->orderby('position')->get();
        return view('cms.paid-class', compact('paid_class','category_paid','subcategory_paid','subjects'));
    
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
                $class_paid = new ClassPaid;
                $class_paid->category_id = $request->category_name; 
                // $class_paid->subcategory_id = $request->subcategory_name;
                $class_paid->title = $request->title[$i]; 
                $class_paid->link = $request->link[$i]; 
                $class_paid->status = 1;
                $class_paid->save();
                $class_paid->position = $class_paid->id;
                $class_paid->save();
            }
        }

        return redirect()->route('adminkpsc.paid-class.index')->with('message','Data added Successfully');

    }

    
    public function paidclass_csv(Request $request){
        
        Excel::import(new PaidclassImport,request()->file('file'));
            
        return redirect()->route('adminkpsc.paid-class.index')->with('message','File uploaded Successfully');
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClassPaid  $classPaid
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request,ClassPaid $classPaid)
    {
        //
       
              
        $category_name = $request->category_name;
        $subcategory_name = $request->subcategory_name;

       $class_list = ClassPaid::leftJoin('kpsc_subjects','kpsc_subjects.id','class_link_paid.category_id')
                                // ->leftJoin('class_subcategory_paid','class_subcategory_paid.id','class_link_paid.subcategory_id')
                                // ->where('class_link_paid.category_id',$category_name)
                                // ->where(function($query2) use ($subcategory_name){
                                //     $query2->where('class_link_paid.subcategory_id', '=', $subcategory_name)
                                //     ->orWhere('class_link_paid.subcategory_id', '=', null);
                                // })
                                ->select('class_link_paid.*','kpsc_subjects.subject_title');
        if($request->category_name != ''){
            $class_list = $class_list->where('class_link_paid.category_id',$category_name);
        }
        $class_list = $class_list->get();
 
        $subjects = KpscSubject::where('status','show')->orderby('position')->get();

        $paid_class = ClassPaid::get();
        $category_paid = CategoryPaid::get();
        $subcategory_paid = SubcategoryPaid::get();
        return view('cms.paid-class', compact('class_list','paid_class','category_paid','subcategory_paid','subjects'));
                            
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClassPaid  $classPaid
     * @return \Illuminate\Http\Response
     */
    public function edit(ClassPaid $classPaid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ClassPaid  $classPaid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClassPaid $classPaid)
    {
        //
        $class_paid = $classPaid->where('id',$request->id)->first();
        $class_paid->category_id = $request->category_name; 
        // $class_paid->subcategory_id = $request->subcategory_name;
        $class_paid->title = $request->title; 
        $class_paid->link = $request->link; 
        $class_paid->status = $request->status;
        $class_paid->position =  $request->position;
        $class_paid->save();

        return redirect()->back()->with('message','Data Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClassPaid  $classPaid
     * @return \Illuminate\Http\Response
     */
    public function delete(ClassPaid $classPaid,$id)
    {
        //d
        $classPaid->where('id',$id)->delete();

        return redirect()->route('adminkpsc.paid-class.index')->with('message','Data Deleted Successfully');

    }
}
