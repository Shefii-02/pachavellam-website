<?php

namespace App\Imports;

use App\Models\ClassPaid;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;

class PaidclassImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    
    public function model(array $row)
    {
        $request = request()->all();

        $last = ClassPaid::orderBy('id','desc')->first();
        return new ClassPaid([
            'category_id'       => $request['category_name'],
            'subcategory_id'    => $request['subcategory_name'],
            'title'             => $row['title'],
            'link'              => $row['link'],
            'status'            => 1,      
            'position'          => $last->id + 1,
        ]);
   
    }
}
