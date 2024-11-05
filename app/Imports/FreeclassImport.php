<?php

namespace App\Imports;

use App\Models\ClassFree;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;

class FreeclassImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {

        // dd($row);
        if(($row['title'] == null) && ($row['link'] == null)){

        }
        else{

            $request = request()->all();

            $last = ClassFree::where('category_id',$request['category_name'])->orderBy('id','desc')->count();
            $vali = ClassFree::where('category_id',$request['category_name'])->where('link',$row['link'])->count();
            if($vali<1){
                return new ClassFree([
                    'category_id'       => $request['category_name'],
                    'subcategory_id'    => $request['subcategory_name'],
                    'title'             => $row['title'],
                    'link'              => $row['link'],
                    'status'            => 1,      
                    'position'          => $last + 1,
                ]);
            }
        }
    }
}
