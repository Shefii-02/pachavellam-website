<?php

namespace App\Http\Controllers\cms;

use App\Models\SpecialVideo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\KpscSubject;

class SpecialVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $spcl_videos = SpecialVideo::leftJoin('kpsc_subjects', 'kpsc_subjects.id', 'special_videos.category')->whereNull('section')->select('special_videos.*', 'kpsc_subjects.subject_title')->orderby('position')->get();
        // $category =  SpecialVideo::select('category')->distinct('category')->get();
        $category = KpscSubject::where('status', 'show')->orderby('position')->get();

        return view('cms.special-videos', compact('spcl_videos', 'category'));
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
            'title' => 'required',
            'redirection' => 'required',
            'category'  => 'required',
        ]);

        // 
        $spcl_videos = new SpecialVideo;
        $spcl_videos->title = $request->title;
        $spcl_videos->category = $request->category;
        $spcl_videos->redirection = $this->getYouTubeVideoId($request->input('redirection'));
        $spcl_videos->status = 1;
        $spcl_videos->save();
        $spcl_videos->position = $spcl_videos->id;
        $spcl_videos->save();

        return redirect()->route('adminkpsc.special-videos.index')->with('message', 'Data added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function show(SpecialVideo $specialVideo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        //
        $ourvideos = SpecialVideo::where('id', $id)->first();
        $category =  KpscSubject::where('status', 'show')->orderby('position')->get();

        $edit_modal = ' 
                        <div class="col-md-12">
                            <label class="form-label">Category</label>
                                <select name="category" class="form-control" >';
        foreach ($category as $catgy) {
            $edit_modal .= '<option value="' . $catgy->id . '"';
            if ($ourvideos->category == $catgy->id) {
                $edit_modal .= "selected";
            }
            $edit_modal .= ">" . $catgy->subject_title . '</option>';
        }
        '</select>';


        $edit_modal .=  ' 
                            <input type="hidden" value="' . $ourvideos->id . '" name="id">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input class="form-control"  value="' . $ourvideos->title . '" type="text" name="title" placeholder="Enter Youtube Title" >
                            <input type="hidden" value="' . $ourvideos->id . '" name="id">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Redirection</label>
                            <input class="form-control" value="https://youtu.be/' . $ourvideos->redirection . '" type="url" name="redirection" placeholder="Enter Youtube link" >
                        
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label"> Position</label>
                            <input class="form-control" name="position" type="text" value="' . $ourvideos->position . '" placeholder="Showing Position" >
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" >
                                <option value="1">Show</option>
                                <option value="0">Hide</option>
                            </select>
                        </div>
                       ';

        return  $edit_modal;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function update_spclvideos(Request $request, SpecialVideo $specialVideo)
    {
        //
        $validated = $request->validate([
            'title' => 'required',
            'redirection' => 'required',
            'category'  => 'required',
            'position'  => 'required',
        ]);

        $our_videos = SpecialVideo::where('id', $request->id)->first();
        $our_videos->title = $request->title;
        $our_videos->category = $request->category;
        $our_videos->redirection = $this->getYouTubeVideoId($request->input('redirection'));
        $our_videos->status = $request->status;
        $our_videos->position = $request->position;
        $our_videos->save();

        return redirect()->route('adminkpsc.special-videos.index')->with('message', 'Data Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SpecialVideo  $specialVideo
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
        SpecialVideo::where('id', $id)->delete();

        return redirect()->route('adminkpsc.special-videos.index')->with('message', 'Data Deleted Successfully');
    }

    protected function getYouTubeVideoId($url)
    {
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Validate the host
        $validHosts = ['www.youtube.com', 'youtube.com', 'm.youtube.com', 'youtu.be'];
        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $validHosts)) {
            return false; // Not a valid YouTube URL
        }

        // Handle short URLs (youtu.be)
        if ($parsedUrl['host'] === 'youtu.be') {
            return isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : false;
        }

        // Handle YouTube Shorts URLs
        if (isset($parsedUrl['path']) && str_starts_with($parsedUrl['path'], '/shorts/')) {
            return str_replace('/shorts/', '', $parsedUrl['path']);
        }

        // Handle regular YouTube video URLs (youtube.com)
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            return $queryParams['v'] ?? false;
        }

        return false; // No valid video ID found
    }
}
