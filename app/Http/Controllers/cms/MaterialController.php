<?php

namespace App\Http\Controllers\cms;

use App\Models\Material;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\KpscSubject;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $material_list = Material::leftJoin('kpsc_subjects', 'kpsc_subjects.id', 'material.type')->select('material.*', 'kpsc_subjects.subject_title')->orderBy('created_at', 'desc')->get();
        $exam_subjects = KpscSubject::where('status', 'show')->orderby('position')->get();

        return view('cms.material', compact('material_list', 'exam_subjects'));
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

        $validated = $request->validate([
            'file' => 'required',
            'category' => 'required',
            'title'  => 'required',
            'post_date'  => 'required',
        ]);

        $name = Str::random(40) . '.pdf';

        $image = file_get_contents($request->file('file'));
        Storage::put('/public/files/material/' . $name, $image);

        $material_new = new Material;
        $material_new->type = $request->category;
        $material_new->title = $request->title;
        $material_new->date = $request->post_date;
        $material_new->file_name = 'material/' . $name;
        $material_new->save();
        return redirect()->route('adminkpsc.material.index')->with('message', 'Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $material)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function edit(Material $material)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Material $material)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function delete(Material $material, $id)
    {
        //
        $bnr = $material->where('id', $id)->first();
        Storage::delete('/public/files/' . $bnr->file_name);
        $bnr->delete();


        return redirect()->route('adminkpsc.material.index')->with('message', 'Data Deleted Successfully');
    }
}
