<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\FetchScheduleJob;

class ScheduleController extends Controller
{
    public function index()
    {
        FetchScheduleJob::dispatch();
    }


    public function import_deps()
    {
        // $xmlDepartmentsObject = $this->fetchXmlData('http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialow.xml');
        // dd($xmlDepartmentsObject);

        $job = new FetchScheduleJob(0);

        $xmlDepartmentsObject = $job->fetchXmlData('http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialow.xml');

        //dd($xmlDepartmentsObject['PL']['ITEMS']['ITEM']);
        return view('importdeps', [
            'data' => $xmlDepartmentsObject['PL']['ITEMS']['ITEM']
        ]);
    }

    public function download_dep($id)
    {
        $job = new FetchScheduleJob($id);
        $job::dispatch($id);
    }
}