<?php

namespace App\Http\Controllers\General;;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LogController extends Controller
{
    const FILES_PER_PAGE = 10;
    public function index(){
        $files = File::files(storage_path('logs/exceptions'));

        // Get the filenames only
        $fileNames = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        // Create a LengthAwarePaginator
        $currentPage = request()->get('page', 1);
        $perPage = self::FILES_PER_PAGE; // Reference the constant correctly
        $currentPageItems = array_slice($fileNames, ($currentPage - 1) * $perPage, $perPage);
        $filelists = new LengthAwarePaginator($currentPageItems, count($fileNames), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        $files = File::files(storage_path('logs/exceptions'));

        // Get the filenames and their modification times
        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = [
                'name' => $file->getFilename(),
                'modified' => $file->getMTime(), // Get the modification time
            ];
        }

        // Sort files by modification time in descending order
        usort($fileNames, function ($a, $b) {
            return $b['modified'] <=> $a['modified']; // Descending order
        });

        // Extract only the file names for pagination
        $fileNamesOnly = array_column($fileNames, 'name');

        // Create a LengthAwarePaginator
        $currentPage = request()->get('page', 1);
        $perPage = self::FILES_PER_PAGE;
        $currentPageItems = array_slice($fileNamesOnly, ($currentPage - 1) * $perPage, $perPage);
        $filelists = new LengthAwarePaginator($currentPageItems, count($fileNamesOnly), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

     

        return view('admin.general.logs',compact('filelists'));
    }



    public function show($id){
        $filePath = storage_path('logs/exceptions/' . $id);
  
        // Check if the file exists
        if (!File::exists($filePath)) {
            abort(404);
        }

        // Read the content of the file
        $content = File::get($filePath);
            $file_name = $id;
            $file = $content;

            return view('admin.general.log-single',compact('file','file_name'));
    }


    public function destroy(string $id){
   
        $filename = $id.'.html';
       
        $filePath = storage_path('logs/exceptions/' . $filename);
            
        // Check if the file exists
        if (File::exists($filePath)) {
     
            File::delete($filePath);
        }

        return redirect(route('admingenerallogs.index'));

    }

    public function destroy_all(){
 
        $files = File::files(storage_path('logs/exceptions'));
        
        foreach ($files as $file) {
            File::delete($file->getPathname()); // Delete each file
        }

        return redirect(route('admingenerallogs.index'));
    }
}