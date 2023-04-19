<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Defense;
use App\Models\Calendar;
use Carbon\Carbon;

class ViewCalendarController extends Controller
{
    public function index($id) {
        $user_calendars = Auth::user()->calendars()->orderBy('created_at', 'desc')->limit(5)->get();
        $calendar_defenses = Auth::user()->calendars()->find($id)->defenses()->get();

        $calendar_data = [];

        foreach($calendar_defenses as $defense) {
            $calendar_data[] = [
                'title' => $defense['student'],
                'start' => Carbon::parse($defense['EgzamDate'])->toIso8601String(),
                'end' => Carbon::parse($defense['EgzamDate'])->addMinutes(30)->toIso8601String(),
            ];
        }

        return view('singlecalendar', ['user_calendars' => $user_calendars, 'calendar_data' => $calendar_data]);
    }
}
