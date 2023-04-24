<?php

namespace App\Http\Controllers;

use App\Models\Defense;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DefenseImport;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExcelImportController extends Controller {
    public function form() {
        $user = Auth::user();
        return view('calendar')->with('user_usage', $user->usage_count);
    }

    public function import(Request $request) {
        $user = Auth::user();
        if ($user->usage_count < -10000000) {
            session()->flash('error', 'You have reached your maximum number of imports');
            return redirect()->back();
        }

        if (
            $request->validate([
                'date' => 'required|date',
                'file' => 'required|mimes:csv,xlx,xls,xlsx|max:2048'
            ])
        ) {
            $file = $request->file('file');
            $date = $request->date;

            $calendar = $user->calendars()->create([
                'Calendar_Name' => $request->calendar_name,
            ]);

            $defenses_list = Excel::toArray(new DefenseImport, $file);

            $list_of_commission = [];

            $ignoreDays = [];
            try {
                usort($defenses_list[0], function ($a, $b) {
                    return strcmp($b['przewodniczacy'], $a['przewodniczacy']);
                });
            } catch (\Throwable $e) {
                session()->flash('error', 'Wrong file');
                $calendar->delete();
                return redirect()->back();
            }

            foreach ($defenses_list as $elem) {
                foreach ($elem as $item) {
                    if ($item['swieta'] != null) {
                        $ignoreDays[] = Carbon::now()->year . "-" . $item['swieta'];
                    }
                }

                foreach ($elem as $item) {

                    if ($item['student'] == null || $item['promotor'] == null || $item['recenzent'] == null || $item['przewodniczacy'] == null)
                        continue;

                    $list_of_commission[] = $item['recenzent'];
                    $list_of_commission[] = $item['przewodniczacy'];
                    $list_of_commission[] = $item['promotor'];

                    $defense = new Defense([
                        'student' => $item['student'],
                        'promoter_name' => $item['promotor'],
                        'egzaminer_name' => $item['recenzent'],
                        'egzaminer2_name' => $item['przewodniczacy'],
                    ]);

                    try {
                        $defense->examiner()->associate(Teacher::where('Teacher-Name', $item['recenzent'])->firstOrFail());
                    } catch (\Throwable $th) {
                        session()->flash('error', 'Examiner ' . $item['recenzent'] . ' does not exist in database');
                    }
                    try {
                        $defense->examiner2()->associate(Teacher::where('Teacher-Name', $item['przewodniczacy'])->firstOrFail());
                    } catch (\Throwable $th) {
                        session()->flash('error', 'Examiner ' . $item['przewodniczacy'] . ' does not exist in database');
                    }
                    try {
                        $defense->promoter()->associate(Teacher::where('Teacher-Name', $item['promotor'])->firstOrFail());
                    } catch (\Throwable $th) {
                        session()->flash('error', 'Promoter ' . $item['promotor'] . ' does not exist in database');
                    }
                    try {
                        $calendar->defenses()->save($defense);
                    } catch (\Throwable $e) {
                        session()->flash('error', 'Unexpected error');

                        $calendar->delete();
                        return redirect()->back();
                    }
                }
            }

            $availibilityArray = $this->generateDatesWithAvailibiltyWindows(array_unique($list_of_commission), $ignoreDays, $date);

            $obrony = $calendar->defenses()->get();
            foreach ($obrony as $obrona) {
                try {
                    $obrona['EgzamDate'] = $this->findWindowWithKeys($availibilityArray, $obrona->examinerID, $obrona->examiner2ID, $obrona->promoterID);
                    $obrona->save();
                } catch (\Throwable $e) {
                    session()->flash('error', 'Unexpected error');
                    $calendar->delete();
                    return redirect()->back();
                }
            }

            $user->usage_count -= 1;
            $user->save();

            return view('calendar')->with('user_usage', $user->usage_count)->with('collection', $defenses_list);
        } else {
            session()->flash('error', 'Please upload a valid file');
            return redirect()->back();
        }
    }
}
