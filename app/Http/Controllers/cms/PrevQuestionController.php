<?php

namespace App\Http\Controllers\cms;

use App\Models\PrevQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrevQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $prev_qstn     = PrevQuestion::Orderby('id', 'desc')->get();
        $category      = PrevQuestion::distinct('category')->select('category')->get();
        $sub_category  = PrevQuestion::distinct('subcategory')->select('subcategory')->get();
        return view('cms.prev-qstn', compact('prev_qstn', 'category', 'sub_category'));
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
        $validated = $request->validate([
            'qstn_file' => 'required',
            'category' => 'required',
            'subcategory'  => 'required',
            'title'  => 'required',
        ]);

        //

        $name1 = null;
        $name2 = null;

        $prevqstn = new PrevQuestion;
        if ($request->hasFile('qstn_file')) {
            $name1 = Str::random(40) . '.pdf';
            $image1 = file_get_contents($request->file('qstn_file'));
            Storage::put('/public/files/question-papers/' . $name1, $image1);
            $prevqstn->qstn_paper = 'question-papers/' . $name1;
        } else {
            $prevqstn->qstn_paper = 'question-papers/' . $name1;
        }

        if ($request->hasFile('ans_file')) {
            $name2 = Str::random(40) . '.pdf';
            $image2 = file_get_contents($request->file('ans_file'));
            Storage::put('/public/files/question-papers/' . $name2, $image2);
            $prevqstn->ans_key = 'question-papers/' . $name2;
        } else {
            $prevqstn->ans_key = 'question-papers/' . $name2;
        }

        $prevqstn->category = $request->category;
        $prevqstn->subcategory = $request->subcategory;
        $prevqstn->title = $request->title;
        $prevqstn->save();
     

        return redirect()->route('adminkpsc.prev-qstn.index')->with('message', 'Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PrevQuestion  $prevQuestion
     * @return \Illuminate\Http\Response
     */
    public function show(PrevQuestion $prevQuestion, $id)
    {


        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PrevQuestion  $prevQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(PrevQuestion $prevQuestion, $id)
    {
        //
        $prev_qstn     = PrevQuestion::where('id', $id)->first() ?? abort(404);
        $category      = PrevQuestion::distinct('category')->select('category')->get();
        $sub_category  = PrevQuestion::distinct('subcategory')->select('subcategory')->get();
        return view('cms.prev-qstn-edit', compact('prev_qstn', 'category', 'sub_category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PrevQuestion  $prevQuestion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //
        $validated = $request->validate([
            'category' => 'required',
            'subcategory'  => 'required',
            'title'  => 'required',
        ]);

        $prevqstn = PrevQuestion::where('id', $id)->first() ?? abort(404);
        $prevqstn->category = $request->category;
        $prevqstn->subcategory = $request->subcategory;
        $prevqstn->title = $request->title;

        if ($request->hasFile('qstn_file')) {
            Storage::delete('/public/files/' . $prevqstn->qstn_paper);
            $name1 = Str::random(40) . '.pdf';
            $image1 = file_get_contents($request->file('qstn_file'));
            Storage::put('/public/files/question-papers/' . $name1, $image1);
            $prevqstn->qstn_paper = 'question-papers/' . $name1;
        }
 
        if ($request->hasFile('ans_file')) {
            $name2 = Str::random(40) . '.pdf';
            Storage::delete('/public/files/' . $prevqstn->ans_key);
            $image2 = file_get_contents($request->file('ans_file'));
            Storage::put('/public/files/question-papers/' . $name2, $image2);
            $prevqstn->ans_key = 'question-papers/' . $name2;
        }


        $prevqstn->save();
 
        return redirect()->route('adminkpsc.prev-qstn.index')->with('message', 'Data updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PrevQuestion  $prevQuestion
     * @return \Illuminate\Http\Response
     */
    public function delete(PrevQuestion $prevQuestion, $id)
    {
        //
        //
        $bnr = $prevQuestion->where('id', $id)->first();
        Storage::delete('/public/files/question-papers/' . $bnr->qstn_paper);
        Storage::delete('/public/files/question-papers/' . $bnr->ans_key);

        $bnr->delete();


        return redirect()->route('adminkpsc.prev-qstn.index')->with('message', 'Data Deleted Successfully');
    }
}
