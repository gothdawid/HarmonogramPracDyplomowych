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

            //create from defenses list that will generate array with ignore days
            $ignoreDays = [];

            usort($defenses_list[0], function ($a, $b) {
                return strcmp($b['przewodniczacy'], $a['przewodniczacy']);
            });

            //dd($this->generateDatesFromTime($ignoreDays));
            // dd($defenses_list);

            foreach ($defenses_list as $elem) {
                foreach($elem as $item) {
                    if($item['swieta'] != null) {
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

                    //dd($defense);
                    $calendar->defenses()->save($defense);
                }
            }

            $availibilityArray = $this->generateDatesWithAvailibiltyWindows(array_unique($list_of_commission), $ignoreDays);

            function findWindowWithKeys(array &$data, int $key1, int $key2, int $key3): ?string
            {
                foreach ($data as $date => $dates) {
                    foreach ($dates as $window => $values) {
                        if (
                            isset($values[$key1]) && $values[$key1] === 0 &&
                            isset($values[$key2]) && $values[$key2] === 0 &&
                            isset($values[$key3]) && $values[$key3] === 0
                        ) {
                            $data[$date][$window][$key1] = -1;
                            $data[$date][$window][$key2] = -1;
                            $data[$date][$window][$key3] = -1;
                            $dateOfDefense = Carbon::parse($date)->format('Y-m-d') . " " . $window ;
                            return Carbon::parse($dateOfDefense)->format('Y-m-d H:i:s');
                        }
                    }
                }
                
                return null;
            }
            // dd($availibilityArray);
            
            $obrony = $calendar->defenses()->get();

            foreach($obrony as $obrona) {
                $obrona['EgzamDate'] = findWindowWithKeys($availibilityArray, $obrona->examinerID, $obrona->examiner2ID, $obrona->promoterID);
                // $def[] = findWindowWithKeys($availibilityArray, $obrona->examinerID, $obrona->examiner2ID, $obrona->promoterID) . " " . $obrona->student;
                //dd($obrona['EXAM_DATE']);
                $obrona->save();
            }



            //dd($availibilityArray);

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