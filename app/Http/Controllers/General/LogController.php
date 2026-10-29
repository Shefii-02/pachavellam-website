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
        $dir = storage_path('logs/exceptions');
        // plain filenames only (no per-file objects or stat calls): the folder can hold tens of thousands of files
        $names = is_dir($dir) ? array_values(preg_grep('/\.html$/', scandir($dir, SCANDIR_SORT_NONE))) : [];
        // names are d_m_Y_H_i_s.html, so re-key them as Y_m_d_H_i_s to sort newest first
        usort($names, fn ($a, $b) => strcmp($this->sortKey($b), $this->sortKey($a)));

        $currentPage = max(1, (int) request()->get('page', 1));
        $perPage = self::FILES_PER_PAGE;
        $filelists = new LengthAwarePaginator(array_slice($names, ($currentPage - 1) * $perPage, $perPage), count($names), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('admin.general.logs',compact('filelists'));
    }

    private function sortKey(string $name): string
    {
        return preg_match('/^(\d{2})_(\d{2})_(\d{4})_(\d{2})_(\d{2})_(\d{2})/', $name, $m) ? "$m[3]$m[2]$m[1]$m[4]$m[5]$m[6]" : '0' . $name;
    }

    public function show($id){
        $filePath = storage_path('logs/exceptions/' . basename($id));
  
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
 
        foreach (glob(storage_path('logs/exceptions/*.html')) ?: [] as $path) {
            @unlink($path);
        }

        return redirect(route('admingenerallogs.index'));
    }
}