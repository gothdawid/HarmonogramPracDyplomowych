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


            $list_of_commission = array_unique($list_of_commission);

            $days = [];
            $today = Carbon::now();

            $hours = [540, 570, 600, 630, 660, 690, 720, 750, 780, 810, 840, 870, 900, 930, 960];

            //1 - lesson
            //2 - window

            //****** TO JEST OK ******//
            function check_hours_range($lesson_start, $lesson_end, $window_start, $window_end) {
                if (($lesson_start <= $window_start && $window_start <= $lesson_end) || 
                    ($lesson_start <= $window_end && $window_end <= $lesson_end) || 
                    ($window_start <= $lesson_start && $lesson_start <= $window_end) || 
                    ($window_start <= $lesson_end && $lesson_end <= $window_end)){
                    return true; // the hours are not available
                } else {
                    return false; // the hours are available
                }
            }

           //dd(check_hours_range(660, 750, 645, 675));

            function minutesToTime($minutes) {
                $hours = floor($minutes / 60);
                $minutes %= 60;
                return sprintf('%02d:%02d', $hours, $minutes);
            }

            function generateDatesFromTime() {
                $dates = [];
                $today = Carbon::now();
                for ($i = 0; $i < 14; $i++) {
                    $dates[] = $today->format('Y-m-d');
                    $today->addDay();
                }
                return $dates;
            }

            //ID   |   Godziny  | 111 | 222 | 333 |
                // |------------|-----|-----|-----|
                // 9:00 - 9:30  |  0  |  1  |  0  |
                // 9:30 - 10:00 |  0  |  1  |  0  |
                // 10:00 - 10:30|  0  |  0  |  1  |
                // 10:30 - 11:00|  0  |  1  |  0  |
                // 11:00 - 11:30|  0  |  1  |  0  |
                // 11:30 - 12:00|  1  |  0  |  1  |
                // 12:00 - 12:30|  0  |  0  |  0  |
                // 12:30 - 13:00|  1  |  0  |  1  |
                // 13:00 - 13:30|  1  |  0  |  1  |
                // 13:30 - 14:00|  0  |  1  |  0  |
                // 14:00 - 14:30|  1  |  1  |  1  |
                // 14:30 - 15:00|  0  |  0  |  0  |
                // 15:00 - 15:30|  1  |  1  |  1  |
                // 15:30 - 16:00|  1  |  1  |  0  |

                $i = 0;

            foreach ($list_of_commission as $teacher) {
                $i++;
                $teacher = Teacher::where('Teacher-Name', $teacher)->first();
                if($teacher == null)
                    continue;

                $lessons = $teacher->lessons()->get();
                $datesArray = generateDates();

                foreach($datesArray as $date){
                    foreach($hours as $hour){
                        $day = Carbon::parse($date)->format('l');
                        $days[$hour . " - " . minutesToTime($hour)][$date . $day][$teacher['Teacher-ID']] = 0;

                        foreach ($lessons as $lesson) {
                            $datesTermins = explode(";", $lesson['TERMIN_DT']);
                            if(check_hours_range($lesson['OD_GODZ'], $lesson['DO_GODZ'], $hour, $hour + 30) && in_array($date, $datesTermins)){
                                $days[$hour . " - " . minutesToTime($hour)][$date . $day][$teacher['Teacher-ID']] = 1;
                            }
                        }
                    }
                }
            }

            //1 - teacher is not available
            //0 - teacher is available
            dd($days);

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