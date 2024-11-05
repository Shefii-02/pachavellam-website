<?php

namespace App\Imports;

use App\Models\CurrentAffairs;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;

class DailyCA implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        $request = request()->all();
        //
        
    
       
         return new CurrentAffairs([
        
            'year'      => $request['year'],
            'month'     => $request['month'],
            'type'      => 'Day',
            'title'     => null,
            'day'       => $request['day'],
            'file_name' => null,
            'question'  => $row['question'],
            'answer'    => $row['answer'],
            'note'      => $row['note'],
            'status'    => 1,
                    
        ]);
   
    }
}
