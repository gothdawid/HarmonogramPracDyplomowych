<?php

namespace App\Imports;

use App\Models\Defense;
use Illuminate\Support\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class DefenseImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $rows)
    {
        return new Defense([
            'student' => $rows[0],
            'promoter' => $rows[1],
            'examiner' => $rows[2],
        ]);
    }
}