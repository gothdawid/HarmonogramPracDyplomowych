<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DefenseImport;
use Illuminate\Support\Facades\Auth;

class ExcelImportController extends Controller
{
    public function form() {
        $user = Auth::user();
        return view('calendar')->with('user_usage', $user->usage_count);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if($user->usage_count < -10000000) {
            session()->flash('error', 'You have reached your maximum number of imports');
            return redirect()->back();
        }

        if($request->validate([
            'file' => 'required|mimes:csv,xlx,xls,xlsx|max:2048'
        ])) {
            $list_of_commission = [];

            $file = $request->file('file');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $fileName, 'public');
            $file->move(public_path('uploads'), $fileName);
            $collection = Excel::toArray(new DefenseImport, $filePath);

            foreach($collection[0] as $row) {
                $list_of_commission[] = $row['promoter'];
                $list_of_commission[] = $row['examiner1'];
                $list_of_commission[] = $row['examiner2'];
            }

            $list_of_commission = array_unique($list_of_commission);

            dd($list_of_commission);
            $user->usage_count -= 1;
            $user->save();

            return view('calendar')->with('user_usage', $user->usage_count)->with('collection', $collection);
        } else {
            session()->flash('error', 'Please upload a valid file');
            return redirect()->back();
        }
        //$collection = Excel::toArray(new DefenseImport, 'E:\Downloads\defenses.xlsx');
    }
}
