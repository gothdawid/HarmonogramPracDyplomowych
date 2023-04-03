<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DefenseImport;

class ExcelImportController extends Controller
{
    public function import(Request $request)
    {
        $collection = Excel::toArray(new DefenseImport, 'E:\Downloads\defenses.xlsx');
    }
}
