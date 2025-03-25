<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class CkeditorController extends Controller
// {
//     //
//     public function upload(Request $request)
//     {
//         if($request->hasFile('upload')) {
//             $originName = $request->file('upload')->getClientOriginalName();
//             $fileName = pathinfo($originName, PATHINFO_FILENAME);
//             $extension = $request->file('upload')->getClientOriginalExtension();
//             $fileName = $fileName.'_'.time().'.'.$extension;
        
//             $request->file('upload')->move(public_path('images'), $fileName);
   
//             $CKEditorFuncNum = $request->input('CKEditorFuncNum');
//             $url = asset('images/'.$fileName); 
//             $msg = 'Image uploaded successfully'; 
//             $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
               
//             @header('Content-type: text/html; charset=utf-8'); 
//             echo $response;
//         }
//     }
// }


// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class CkeditorController extends Controller
// {
    //
    // public function upload(Request $request)
    // {
    //     if($request->hasFile('upload')) {
    //         $originName = $request->file('upload')->getClientOriginalName();
    //         $fileName = pathinfo($originName, PATHINFO_FILENAME);
    //         $extension = $request->file('upload')->getClientOriginalExtension();
    //         $fileName = $fileName.'_'.time().'.'.$extension;
        
    //         $request->file('upload')->move(public_path('images'), $fileName);
   
    //         $CKEditorFuncNum = $request->input('CKEditorFuncNum');
    //         $url = asset('images/'.$fileName); 
    //         $msg = 'Image uploaded successfully'; 
    //         $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
               
    //         @header('Content-type: text/html; charset=utf-8'); 
    //         echo $response;
    //     }
    // }
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
 
class CkeditorController extends Controller {
     
    public function index(){
        return view('editor');
    }
     
    public function upload( Request $request ){
        if($request->hasFile('upload')) {
         
            //get filename with extension
            $fileNameWithExtension = $request->file('upload')->getClientOriginalName();
       
            //get filename without extension
            $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
       
            //get file extension
            $extension = $request->file('upload')->getClientOriginalExtension();
       
            //filename to store
            $fileNameToStore = $fileName.'_'.time().'.'.$extension;
       
            //Upload File
            $request->file('upload')->storeAs('public/uploads', $fileNameToStore);
  
            /* $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = asset('storage/uploads/'.$fileNameToStore); 
            $msg = 'Image successfully uploaded'; 
            $renderHtml = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
             
            // Render HTML output 
            @header('Content-type: text/html; charset=utf-8'); 
            echo $renderHtml; */
             
            $CKEditorFuncNum = $request->input('CKEditorFuncNum') ? $request->input('CKEditorFuncNum') : 0;
             
            if($CKEditorFuncNum > 0){
             
                $url = asset('storage/uploads/'.$fileNameToStore); 
                $msg = 'Image successfully uploaded'; 
                $renderHtml = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
                 
                // Render HTML output 
                @header('Content-type: text/html; charset=utf-8'); 
                echo $renderHtml;
                 
            } else {
             
                $url = asset('storage/uploads/'.$fileNameToStore); 
                $msg = 'Image successfully uploaded'; 
                $renderHtml = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
                return response()->json([
                    'uploaded' => '1',
                    'fileName' => $fileNameToStore,
                    'url' => $url
                ]);
            }
             
        }
    }
     
    public function browse( Request $request ){
         
        $files = Storage::files('public/uploads');
        //dd($files);
        $CKEditorFuncNum = $request->input('CKEditorFuncNum');
         
        $msg = 'Image successfully uploaded'; 
        foreach( $files as $file){
            $url = asset('storage/uploads/'.str_replace('public/uploads/', '', $file));
            $renderHtml = "<script>
            function returnFileUrl() {
                window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url');
                window.close();
            }
            </script>";
            $renderHtml .='<button onclick="returnFileUrl()">Select File</button>';
        }
         
        // Render HTML output 
        @header('Content-type: text/html; charset=utf-8'); 
        echo $renderHtml;
         
    }
}

// }
