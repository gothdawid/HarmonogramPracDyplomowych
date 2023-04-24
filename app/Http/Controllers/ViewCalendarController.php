<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Defense;
use App\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ViewCalendarController extends Controller {
    public function index($id) {
        $user_calendars = Auth::user()->calendars()->orderBy('created_at', 'desc')->limit(5)->get();
        $calendar_defenses = Auth::user()->calendars()->find($id)->defenses()->get();

        $calendar_data = [];
        $calendar_start_date = $calendar_defenses->min('EgzamDate');

        $hours = 60 * 60 * 8;

        /* @TODO: 
            Add global visibility of other users calendar (display accepted calendar on every calendar) 
        */

        foreach ($calendar_defenses as $defense) {
            //Cache for 8 hours data about availibilty of teachers to speed up loading page
            $list_of_hours_with_lessons = Cache::remember($defense['egzaminer2_name'] . $defense['promoter_name'] . $defense['egzaminer_name'], $hours, function () use ($defense) {
                return $this->generateDatesWithAvailibiltyWindows([$defense['egzaminer2_name'], $defense['promoter_name'], $defense['egzaminer_name']]);
            });

            $calendar_data[] = [
                //those are needed for calendar defense event
                'id' => $defense['id'],
                'title' => $defense['student'] . ' (Przewodniczący: ' . $defense['egzaminer2_name'] . ', Recenzent: ' . $defense['egzaminer_name'] . ', Promotor: ' . $defense['promoter_name'] . ')',
                'start' => Carbon::parse($defense['EgzamDate'])->toIso8601String(),
                'end' => Carbon::parse($defense['EgzamDate'])->addMinutes(30)->toIso8601String(),
                //those are needed for modals of defenses
                'extendedProps' => [
                    'student' => $defense['student'],
                    'leader' => $defense['egzaminer2_name'],
                    'promoter' => $defense['promoter_name'],
                    'reviewer' => $defense['egzaminer_name'],
                    'timeStart' => Carbon::parse($defense['EgzamDate'])->format('Y-m-d H:i'),
                    'timeEnd' => Carbon::parse($defense['EgzamDate'])->addMinutes(30)->format('Y-m-d H:i'),
                    'promoter_id' => $defense['promoterID'],
                    'reviewer_id' => $defense['examinerID'],
                    'leader_id' => $defense['examiner2ID'],
                    //this is needed to show user which hours are available for this defense
                    'hours_with_lessons' => $list_of_hours_with_lessons,
                ]
            ];
        }

        return view('singlecalendar', [
            'user_calendars' => $user_calendars,
            'calendar_data' => $calendar_data,
            'calendar_start_date' => $calendar_start_date,
            'calendar_id' => $id,
            'calendar_name' => $user_calendars->find($id)->Calendar_Name
        ]);
    }

    public function save(Request $request) {
        $id = $request->defense_id;
        $defense_start = Carbon::parse(strstr($request->defense_start, '(', true))->subHours(2); /* TEMPORARY FIX FOR TIMEZONE PROBLEM (EVENT DATE DOES NOT MATCH DATA IN THIS SCRIPT) */
        $defense_end = Carbon::parse(strstr($request->defense_end, '(', true))->subHours(2); /* TEMPORARY FIX FOR TIMEZONE PROBLEM (EVENT DATE DOES NOT MATCH DATA IN THIS SCRIPT) */

        $defense = Defense::find($id);
        $defense->EgzamDate = $defense_start;

        if (!$defense->save()) {
            return response("Error while saving to database", 500)->header('Content-Type', 'text/plain');
        }

        return response("Saved", 200)->header('Content-Type', 'text/plain');
    }

    public function delete($id) {
        $calendar = Auth::user()->calendars()->find($id);
        $calendar->delete();

        return redirect()->route('calendar');
    }
}
