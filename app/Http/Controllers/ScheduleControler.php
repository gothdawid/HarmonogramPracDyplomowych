<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScheduleControler extends Controller
{
    //
    public function index() {
        //Download xml from url
        $urlDepartments = 'http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialow.xml';
        $xmlDepartmentsObject = simplexml_load_file($urlDepartments);
        
        
        //dd($xmlObject->ITEMS);


        foreach ($xmlDepartmentsObject->PL->ITEMS->ITEM as $item){
            
            print("<H2>".$item->ID." ");

            print($item->NAME."</H2><br>");

            $urlTeachers = 'http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialu.ID='.$item->ID.'.xml';
            $xmlTeachersObject = simplexml_load_file($urlTeachers);

            foreach ($xmlTeachersObject->ITEMS->ITEM as $teacher ) {
                print("<br><p><b>".$teacher->ID."</b> ");

                print($teacher->NAME."</p>");

                $urlSchedule = 'http://www.plan.uz.zgora.pl/static_files/nauczyciel_plan.ID='.$teacher->ID.'.xml';
                $xmlScheduleObject = simplexml_load_file($urlSchedule);

                foreach ($xmlScheduleObject->ITEMS->ITEM as $schedule ) {
                    print($schedule->ID_KALENDARZ." ");
                    print("<b>".$schedule->DAY."</b> ");
                    print($schedule->OD_GODZ."-".$schedule->DO_GODZ." ");

                    print($schedule->NAME."<br>");
                }
            }

            //dd($xmlTeachersObject);


        }




        //dd($xmlObject);
    }
}
