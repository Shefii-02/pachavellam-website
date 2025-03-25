<?php

namespace App\Imports;

use App\Models\CurrentAffairs;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class DailyCA implements ToModel, WithHeadingRow, WithCustomCsvSettings
{
    /**
     * Define CSV settings to ensure correct encoding.
     */
    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',  // Set to UTF-8; adjust if your CSV uses a different encoding
        ];
    }

    /**
     * Map each row to a CurrentAffairs model.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Access additional fields from the request if necessary
        $request = request()->all();

        return new CurrentAffairs([
            'year'      => $request['year'] ?? null,
            'month'     => $request['month'] ?? null,
            'type'      => 'Day',
            'title'     => null,
            'day'       => $request['day'] ?? null,
            'file_name' => null,
            'question'  => $row['question'] ?? null,
            'answer'    => $row['answer'] ?? null,
            'note'      => $row['note'] ?? null,
            'status'    => 1,
        ]);
    }
}
