<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\FetchScheduleJob;

class ScheduleController extends Controller
{
    public function import_deps()
    {

        $job = new FetchScheduleJob(0);
        $xmlDepartmentsObject = $job->fetchXmlData('http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialow.xml');
        $data = [];


        for ($i = 0; $i < count($xmlDepartmentsObject['PL']['ITEMS']['ITEM']); $i++) {
            $dep = Department::where('Departament-ID', $xmlDepartmentsObject['PL']['ITEMS']['ITEM'][$i]['ID'])->first();
            $data[$i] = $xmlDepartmentsObject['PL']['ITEMS']['ITEM'][$i];
            if ($dep) {
                $data[$i]['Dep_id'] = $dep['Departament-ID'];
                $data[$i]['UPDATED_AT'] = $dep->updated_at;
                if ($dep->updated_at->diffInSeconds(now()) > 60 * 60 * 6)
                    $data[$i]['Status'] = 1;
                else
                    $data[$i]['Status'] = 2;
            } else {
                $data[$i]['Status'] = 0;
                $data[$i]['UPDATED_AT'] = "";
            }
        }

        return view('importdeps', [
            'data' => $data
        ]);
    }

    public function download_dep(Request $request, $id)
    {
        $department = Department::where('Departament-ID', $id)->first();
        if ($department) {
            $lastUpdated = $department->updated_at;
            if ($lastUpdated->diffInSeconds(now()) > 60 * 60 * 6) {
                Lesson::where('Departament-ID', $id)->delete();

                $job = new FetchScheduleJob($id);
                $job::dispatch($id);
                $department->update();
                $department->touch();
                print($id);
            } else
                print("no");

        } else {
            $department = new Department();
            $department['Departament-ID'] = $id;
            $department['Departament-Name'] = $request->query('name');

            //TODO - data size
            $department['size'] = 0;
            $department->save();

            $job = new FetchScheduleJob($id);
            $job::dispatch($id);
            print($id);
        }
    }
}