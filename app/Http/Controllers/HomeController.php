<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrentAffairs;
use App\Imports\Bulletin_upload;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Pages;
use App\Models\FeedbackPost;
use App\Models\FeedbackRequests;
use Carbon\Carbon;


class HomeController extends Controller
{
    //
    public function index(){

        if($_SERVER['HTTP_USER_AGENT'] == 'Android' || $_SERVER['HTTP_USER_AGENT'] == "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"){
                return view("test.new-home");
            // return '<h1 style="margin:0 auto; top:50%">we`are introduce new version please hold on..</h1>';
        }
        else{
            return view('web.home');
        }
    }

    public function create(){
        return  view('web.sign-up');
    }

    //daily ca get

    public function daily_ca_get(){
        return  view('web.daily-ca-get');
    }
    

    //daily ca post
    public function daily_ca_store(Request $request){
        
        
       
        $apiURL = $request->url;
        $html   = file_get_contents($apiURL, FILE_IGNORE_NEW_LINES);
        $result = explode('<div class="col-lg-9 contact-main  rounded">',$html);
        $result2 = explode('<div class="col-lg-3">',$result[1]);
        $result3 = explode('<div class="showhim">',$result2[0]);
        

        // first array val remove last 3 div remove
    
        //loop
        for($j=1;$j<count($result3);$j++){

            $result4 = explode('<button class="answerbtn btn-info btn btn-sm  w-100 mt-2 mb-3" onclick="revealAnswersFunction(event)">Show Answer</button>',$result3[$j]);
        
        

            $qstn = strip_tags($result4[0]);
            $result5 = explode('</strong>',$result4[1]);
            $ans = strip_tags($result5[0]);
            if($result5[1]){
                $note = strip_tags($result5[1]);
            }
            
                $daily_ca = new CurrentAffairs;
                $daily_ca->year = $request->year; 
                $daily_ca->month = $request->month;
                $daily_ca->day   = $request->day;
                $daily_ca->type =  "Day";
                $daily_ca->title = null;
                $daily_ca->file_name = null;
                $daily_ca->question = trim($qstn);
                $daily_ca->answer = trim(str_replace('Correct Answer: &nbsp;','',$ans));
                $daily_ca->note = trim(str_replace('Notes:','',($note ?? null)));
                $daily_ca->status = 1;
                $daily_ca->save();

           

        }

        return redirect()->back();
    }


    public function upload_pscbullet_in(){
        return  view('web.upload_pscbullet_in');
    }


    public function upload_pscbullet_in_store(Request $request){
        
        Excel::import(new Bulletin_upload,request()->file('file'));
        dd(12345);              
    }

    public function info($title){
        $title = remove_slug($title);
   
        $pages_details = Pages::where('title',$title)->first();
        if($pages_details){
            return  view('web.info',compact('pages_details'));
        }
        else{
            abort(404);
        }
    }
    
    public function feedback_form($author){
        $datetime = Carbon::now()->format('Y-m-d H:i:s');
        $feed = FeedbackPost::whereAuthor($author)->where('expdate','>=',$datetime)->first();
        if(!$feed){
            $message = "Feedback Form Expired";
            
            return  view('web.feedback_form',compact('author','message'));
        }
        else
        {
            $message = "0";
            
            return  view('web.feedback_form',compact('author','message','feed'));
        }
        
        
        
    }
    
    public function feedback_submit($author,Request $request){
        
        $datetime = Carbon::now()->format('Y-m-d H:i:s');
        $feed = FeedbackPost::whereAuthor($author)->where('expdate','>=',$datetime)->first();
     
        if($feed){
            
            $repe = FeedbackRequests::where('ip_address',$request->ip())->where('feedback_id',$feed->id)->first();
            if(!$repe){
                $newone  = new FeedbackRequests();
                $newone->feedback_id = $feed->id;	
                $newone->name	 = $request->name;
                $newone->mobile	 = $request->mobile;
                $newone->message = $request->message;	
                $newone->ip_address = $request->ip();	
                $newone->star_rating = $request->has('rating') ? $request->rating : 1;
                $newone->save();
            
                return redirect()->back()->with('success','Successfully Submited');
            }
            else
            {
                
                return redirect()->back()->with('error','You are already submited');
            }
            
        }
        else
        {
            
            return redirect()->back()->with('error','Feedback Form Expired');
        }
    }
    

}
