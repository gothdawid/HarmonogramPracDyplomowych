<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;
use App\Models\Teacher;

class Controller extends BaseController {
    use AuthorizesRequests, ValidatesRequests;


    //public constructor 
    public function __construct() {
        date_default_timezone_set('Europe/Warsaw');
    }


    public $hours = [540, 570, 600, 630, 660, 690, 720, 750, 780, 810, 840, 870, 900, 930, 960];

    function generateDatesFromTime($ignoreDays = [], $today) {
        $dates = [];
        
        if ($today == null) {
            $today = Carbon::now();
        } else {
            $today = new Carbon($today);
        }

        $year = Carbon::now()->year;
        $easter_date = Carbon::createFromDate($year, 1, 1)->addDays(easter_days($year));
        $easter_day = $easter_date->format('d');
        $easter_month = $easter_date->format('m');

        $holidays = [
            Carbon::createFromDate($year, 1, 1), // Nowy Rok
            Carbon::createFromDate($year, 1, 6), // Trzech Króli
            Carbon::createFromDate($year, $easter_month, $easter_day), // Wielkanoc
            Carbon::createFromDate($year, $easter_month, $easter_day)->addDays(1), // Poniedziałek Wielkanocny
            Carbon::createFromDate($year, 5, 1), // Święto Pracy
            Carbon::createFromDate($year, 5, 3), // Święto Konstytucji 3 Maja
            Carbon::createFromDate($year, $easter_month, $easter_day)->addDays(49), // Zielone Świątki
            Carbon::createFromDate($year, $easter_month, $easter_day)->addDays(60), // Boże Ciało
            Carbon::createFromDate($year, 8, 15), // Wniebowzięcie Najświętszej Maryi Panny
            Carbon::createFromDate($year, 11, 1), // Wszystkich Świętych
            Carbon::createFromDate($year, 11, 11), // Narodowe Święto Niepodległości
            Carbon::createFromDate($year, 12, 25), // Boże Narodzenie (pierwszy dzień)
            Carbon::createFromDate($year, 12, 26) // Boże Narodzenie (drugi dzień)
        ];

        foreach ($ignoreDays as $day) {
            $day = new Carbon($day);
            array_push($holidays, $day->format('m-d'));
        }

        for ($i = 0; count($dates) < 14; $i++) {
            $date = $today->format('Y-m-d');
            if (!in_array($today->format('m-d'), $holidays) && $today->isWeekday()) {
                $dates[] = $date;
            }
            $today->addDay();
        }
        return $dates;
    }

    function checkHoursRange($lesson_start, $lesson_end, $window_start, $window_end) {
        if (
            ($lesson_start <= $window_start && $window_start <= $lesson_end) ||
            ($lesson_start <= $window_end && $window_end <= $lesson_end) ||
            ($window_start <= $lesson_start && $lesson_start <= $window_end) ||
            ($window_start <= $lesson_end && $lesson_end <= $window_end)
        ) {
            return true; // the hours are not available
        } else {
            return false; // the hours are available
        }
    }

    function minutesToTime($minutes) {
        $hours = floor($minutes / 60);
        $minutes %= 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    function generateDatesWithAvailibiltyWindows($list_of_commission, $ignoreDays = [], $today = null) {
        $availibilityArray = [];

        $i = 0;

        foreach ($list_of_commission as $teacher) {
            $i++;
            $teacher = Teacher::where('Teacher-Name', $teacher)->first(); //get teacher from database

            //if teacher is not in database - skip
            if ($teacher == null)
                continue;

            //get lessons and generate dates to check with ignored days (holidays) of uni
            $lessons = $teacher->lessons()->get();
            $datesArray = $this->generateDatesFromTime($ignoreDays, $today);

            //1 - teacher is not available
            //0 - teacher is available

            //for each date
            foreach ($datesArray as $date) {
                //for each hour
                foreach ($this->hours as $hour) {
                    $day = Carbon::parse($date)->format('l');
                    //set default value to 0 (available)
                    $availibilityArray[$date][$this->minutesToTime($hour)][$teacher['Teacher-ID']] = 0;

                    //for each lesson of teacher
                    foreach ($lessons as $lesson) {
                        //get date of current lesson
                        $datesTermins = explode(";", $lesson['TERMIN_DT']);

                        //check if teacher has lesson in this hour and on this day
                        if ($this->checkHoursRange($lesson['OD_GODZ'], $lesson['DO_GODZ'], $hour, $hour + 30) && in_array($date, $datesTermins)) {
                            $availibilityArray[$date][$this->minutesToTime($hour)][$teacher['Teacher-ID']] = 1;
                        }
                    }
                }
            }
        }

        return $availibilityArray;
    }

    function findWindowWithKeys(array &$data, int $key1, int $key2, int $key3): ?string {
        //for each date
        foreach ($data as $date => $dates) {
            //for each 30 minute window
            foreach ($dates as $window => $values) {
                if (
                    isset($values[$key1]) && $values[$key1] === 0 &&
                    isset($values[$key2]) && $values[$key2] === 0 &&
                    isset($values[$key3]) && $values[$key3] === 0
                ) {

                    //set keys to -1 to avoid double booking defense
                    $data[$date][$window][$key1] = -1;
                    $data[$date][$window][$key2] = -1;
                    $data[$date][$window][$key3] = -1;

                    //return date with time of defense
                    $dateOfDefense = Carbon::parse($date)->format('Y-m-d') . " " . $window;
                    return Carbon::parse($dateOfDefense)->format('Y-m-d H:i:s');
                }
            }
        }

        return null;
    }
}
