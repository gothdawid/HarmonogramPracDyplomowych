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

        $deps = Department::all(['Departament-ID', 'UPDATED_AT']);
        foreach ($xmlDepartmentsObject['PL']['ITEMS']['ITEM'] as $xmlDep) {
            $found = false;
            foreach ($deps as $dep) {
                $status = 0;
                if ($dep['Departament-ID'] == $xmlDep['ID']) {
                    $found = true;

                    $time = strtotime($dep['UPDATED_AT']) + 60 * 60 * 6;
                    if ($time < time())
                        $status = 1;
                    else
                        $status = 2;

                    array_push($data, [
                        'ID' => $xmlDep['ID'],
                        'NAME' => $xmlDep['NAME'],
                        'Dep_id' => $xmlDep['ID'],
                        'UPDATED_AT' => date('d.m.Y H:i:s', strtotime($dep['UPDATED_AT'])),
                        'Status' => $status
                    ]);
                    break;
                }
            }
            if (!$found) {
                array_push($data, [
                    'ID' => $xmlDep['ID'],
                    'NAME' => $xmlDep['NAME'],
                    'Dep_id' => $xmlDep['ID'],
                    'UPDATED_AT' => 0,
                    'Status' => 0
                ]);
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
            if ($lastUpdated->diffInSeconds(now()) > 60 * 60 * 6 || $department->lessons()->count() < 1) {
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