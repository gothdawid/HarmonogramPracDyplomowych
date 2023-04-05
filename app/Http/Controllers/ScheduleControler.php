<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\FetchScheduleJob;

class ScheduleControler extends Controller
{
    public function index() {
        FetchScheduleJob::dispatch();
    }
}
