<?php

namespace App\Imports;

use App\Models\NewmodalQa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;

class NewModalqaimport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
       
         return new NewmodalQa([
        
            'question'    => $row['question'],
            'currect_ans' => $row['answer'],
            'options'     => "{{".$row['option1']."}},,{{".$row['option2']."}},,{{".$row['option3']."}},,{{".$row['option4']."}}",
            'status'      => 1,
            
                    
        ]);
   
    }
}
