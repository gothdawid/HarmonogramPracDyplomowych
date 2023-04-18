<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;
use App\Models\Teacher;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public $hours = [540, 570, 600, 630, 660, 690, 720, 750, 780, 810, 840, 870, 900, 930, 960];

    function generateDatesFromTime($ignoreDays = []) {
        $dates = [];
        $today = Carbon::now()->addDays(11);
        $holidays = [
            Carbon::createFromDate($today->year, 1, 1)->format('m-d'), // Nowy Rok
            Carbon::createFromDate($today->year, 1, 6)->format('m-d'), // Trzech Króli
            Carbon::createFromDate($today->year, 5, 1)->format('m-d'), // Święto Pracy
            Carbon::createFromDate($today->year, 5, 3)->format('m-d'), // Święto Konstytucji 3 Maja
            Carbon::createFromDate($today->year, 8, 15)->format('m-d'), // Wniebowzięcie Najświętszej Maryi Panny
            Carbon::createFromDate($today->year, 11, 1)->format('m-d'), // Wszystkich Świętych
            Carbon::createFromDate($today->year, 11, 11)->format('m-d'), // Narodowe Święto Niepodległości
            Carbon::createFromDate($today->year, 12, 25)->format('m-d'), // Boże Narodzenie (pierwszy dzień)
            Carbon::createFromDate($today->year, 12, 26)->format('m-d'), // Boże Narodzenie (drugi dzień)
            Carbon::createFromDate($today->year, $today->month, $today->day)->subDays(2)->next(Carbon::THURSDAY)->format('m-d'), // Boże Ciało
        ];

        foreach ($ignoreDays as $day) {
            $day = new Carbon($day);
            array_push($holidays, $day->format('m-d'));
        }
    
        $easter_date = Carbon::createFromDate($today->year, 3, 21)
                                ->addDays(easter_days($today->year))
                                ->format('m-d');
        array_push($holidays, $easter_date);
    
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
        if (($lesson_start <= $window_start && $window_start <= $lesson_end) || 
            ($lesson_start <= $window_end && $window_end <= $lesson_end) || 
            ($window_start <= $lesson_start && $lesson_start <= $window_end) || 
            ($window_start <= $lesson_end && $lesson_end <= $window_end)){
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

    function generateDatesWithAvailibiltyWindows($list_of_commission, $ignoreDays = []) {
        $availibilityArray = [];

        $i = 0;

        foreach($list_of_commission as $teacher) {
            $i++;
            $teacher = Teacher::where('Teacher-Name', $teacher)->first();
            if($teacher == null)
                continue;

            $lessons = $teacher->lessons()->get();
            $datesArray = $this->generateDatesFromTime($ignoreDays);

            //1 - teacher is not available
            //0 - teacher is available
            foreach($datesArray as $date){
                foreach($this->hours as $hour){
                    $day = Carbon::parse($date)->format('l');
                    $availibilityArray[$date . $day][/*$hour . " - " . */$this->minutesToTime($hour)][$teacher['Teacher-ID']] = 0;

                    foreach($lessons as $lesson) {
                        $datesTermins = explode(";", $lesson['TERMIN_DT']);
                        if($this->checkHoursRange($lesson['OD_GODZ'], $lesson['DO_GODZ'], $hour, $hour + 30) && in_array($date, $datesTermins)){
                            $availibilityArray[$date . $day][/*$hour . " - " . */$this->minutesToTime($hour)][$teacher['Teacher-ID']] = 1;
                        }
                    }
                }
            }
        }

        return $availibilityArray;
    }

}
