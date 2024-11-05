<?php

namespace App\Http\Controllers\cms;

use App\Models\CurrentAffairsView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use Storage;
use Illuminate\Support\Str;

class CurrentAffairsViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CurrentAffairsView  $currentAffairsView
     * @return \Illuminate\Http\Response
     */
    public function show(CurrentAffairsView $currentAffairsView)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CurrentAffairsView  $currentAffairsView
     * @return \Illuminate\Http\Response
     */
    public function edit(CurrentAffairsView $currentAffairsView)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CurrentAffairsView  $currentAffairsView
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CurrentAffairsView $currentAffairsView)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CurrentAffairsView  $currentAffairsView
     * @return \Illuminate\Http\Response
     */
    public function destroy(CurrentAffairsView $currentAffairsView)
    {
        //
    }
}
