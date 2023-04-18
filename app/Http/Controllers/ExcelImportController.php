<?php

namespace App\Http\Controllers;

use App\Models\Defense;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DefenseImport;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExcelImportController extends Controller
{
    public function form()
    {
        $user = Auth::user();
        return view('calendar')->with('user_usage', $user->usage_count);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if ($user->usage_count < -10000000) {
            session()->flash('error', 'You have reached your maximum number of imports');
            return redirect()->back();
        }

        if (
            $request->validate([
                'file' => 'required|mimes:csv,xlx,xls,xlsx|max:2048'
            ])
        ) {
            $file = $request->file('file');

            $calendar = $user->calendars()->create([
                'Calendar_Name' => $request->calendar_name,
            ]);

            $defenses_list = Excel::toArray(new DefenseImport, $file);
            $list_of_commission = [];

            foreach ($defenses_list as $elem) {
                foreach ($elem as $item) {
                    $list_of_commission[] = $item['examiner1'];
                    $list_of_commission[] = $item['examiner2'];
                    $list_of_commission[] = $item['promoter'];

                    $defense = new Defense([
                        'student' => $item['student'],
                        'promoter_name' => $item['promoter'],
                        'egzaminer_name' => $item['examiner1'],
                        'egzaminer2_name' => $item['examiner2'],
                    ]);
                    try {
                        $defense->examiner()->associate(Teacher::where('Teacher-Name', $item['examiner1'])->firstOrFail());
                    } catch (\Throwable $th) {
                        session()->flash('error', 'Examiner ' . $item['examiner1'] . ' does not exist in database');
                    }
                    try {
                        $defense->examiner2()->associate(Teacher::where('Teacher-Name', $item['examiner2'])->firstOrFail());
                    } catch (\Throwable $th) {
                        session()->flash('error', 'Examiner ' . $item['examiner2'] . ' does not exist in database');
                    }
                    try {
                        $defense->promoter()->associate(Teacher::where('Teacher-Name', $item['promoter'])->firstOrFail());
                    } catch (\Throwable $th) {
                        session()->flash('error', 'Promoter ' . $item['promoter'] . ' does not exist in database');
                    }

                    //dd($defense);
                    $calendar->defenses()->save($defense);
                }
            }

            $availibilityArray = $this->generateDatesWithAvailibiltyWindows(array_unique($list_of_commission));
            dd($availibilityArray);

            $user->usage_count -= 1;
            $user->save();

            return view('calendar')->with('user_usage', $user->usage_count)->with('collection', $defenses_list);
        } else {
            session()->flash('error', 'Please upload a valid file');
            return redirect()->back();
        }
        //$collection = Excel::toArray(new DefenseImport, 'E:\Downloads\defenses.xlsx');
    }
}