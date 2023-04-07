<?php

namespace App\Http\Controllers;

use App\Models\Department;
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

        return view('importdeps', [
            'data' => $xmlDepartmentsObject['PL']['ITEMS']['ITEM']
        ]);
    }

    public function download_dep(Request $request, $id)
    {
        $department = Department::where('Departament-ID', $id)->first();
        if ($department) {
            $lastUpdated = $department->updated_at;
            if ($lastUpdated->diffInSeconds(now()) > 60) {
                $job = new FetchScheduleJob($id);
                $job::dispatch($id);
                $department->update();
                $department->touch();
                print("UPDATED");
            }
            print("UPDATE NOT REQUIRED");

        } else {
            $department = new Department();
            $department['Departament-ID'] = $id;
            $department['Departament-Name'] = $request->query('name');

            //TODO - data size
            $department['size'] = 0;
            $department->save();

            $job = new FetchScheduleJob($id);
            $job::dispatch($id);
            print("CREATED");
        }
    }
}