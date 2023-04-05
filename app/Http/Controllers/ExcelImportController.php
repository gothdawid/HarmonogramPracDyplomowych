<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DefenseImport;

class ExcelImportController extends Controller
{
    public function form() {
        return view('calendar');
    }

    public function import(Request $request)
    {
        if($request->validate([
            'file' => 'required|mimes:csv,xlx,xls,xlsx|max:2048'
        ])) {
            $file = $request->file('file');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $fileName, 'public');
            $file->move(public_path('uploads'), $fileName);
            $collection = Excel::toArray(new DefenseImport, $filePath);
            dd($collection);
        } else {
            return back()->with('error', 'Please upload a valid file');
        }
        //$collection = Excel::toArray(new DefenseImport, 'E:\Downloads\defenses.xlsx');
    }
}
