<?php

namespace App\Imports;

use App\Models\Bulletin;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;

class Bulletin_upload implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $request)
    {
        //
        
       
         return new Bulletin([
            						
            'file_name' => $request['file_name'],
            'download'  => $request['down_count'],
            'month_year'=> date("Y-m-d",strtotime($request['year_month'])),
            'issue'     => $request['issue'],
            'position'  => 1,
            'status'    => 1,
                    
        ]);
   
    }
}
