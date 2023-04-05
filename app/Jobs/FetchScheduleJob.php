<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class FetchScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    public function fetchXmlData(string $xmlUrl): array
    {
        $cacheKey = 'xml_' . md5($xmlUrl);
        
        // Check if data is cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Fetch XML data
        $xmlData = file_get_contents($xmlUrl);
        
        // Parse XML data
        $parsedXml = simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($parsedXml);
        $array = json_decode($json, true);
        
        // Cache the data for 1 hour
        Cache::put($cacheKey, $array, 60*60);
        
        return $array;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        $formated_schedule = collect();
        $xmlErrorCount = 0;
        $xmlDepartmentsObject = $this->fetchXmlData('http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialow.xml');
        //dd($xmlDepartmentsObject);
        foreach ($xmlDepartmentsObject['PL']['ITEMS']['ITEM'] as $Department){
            $xmlTeachersObject = $this->fetchXmlData('http://www.plan.uz.zgora.pl/static_files/nauczyciel_lista_wydzialu.ID='.$Department['ID'].'.xml');
            //dd($xmlTeachersObject);

            if(array_key_exists('ITEM', $xmlTeachersObject['ITEMS'])){    
                foreach ($xmlTeachersObject['ITEMS']['ITEM'] as $teacher ) {
                    if(!is_string($teacher)) {
                        //dd($teacher);
                        $xmlSchedulesObject = $this->fetchXmlData('http://www.plan.uz.zgora.pl/static_files/nauczyciel_plan.ID='.$teacher['ID'].'.xml');
                        //dd($xmlSchedulesObject);
                        foreach ($xmlSchedulesObject['ITEMS'] as $schedule ) {
                            foreach ($schedule as $lesson) {
                                //dd($lesson);
                                if(is_string($lesson) || empty($lesson) || count($lesson) < 4) {
                                    Log::info($lesson);
                                    $lesson = $schedule;
                                }
                                try {
                                    $data = [];
                                    $data['Departament-ID'] = $Department['ID'];
                                    $data['Departament-Name'] = $Department['NAME'];
                                    $data['Teacher-ID'] = $teacher['ID'];
                                    $data['Teacher-Name'] = $teacher['NAME'];
                                    $data['Jednostka'] = $teacher['JEDN'];
                                    $data['Jednostka-en'] = $teacher['JEDN_EN'];
                                    $data['Plan-ID'] = $lesson['ID_POZYCJA'];
                                    $data['DAY'] = $lesson['DAY'];
                                    $data['OD_GODZ'] = $lesson['OD_GODZ'];
                                    $data['DO_GODZ'] = $lesson['DO_GODZ'];
                                    $data['G_OD'] =   $lesson['G_OD'];
                                    $data['G_DO'] =   $lesson['G_DO'];
                                    $data['NAME'] =   $lesson['NAME'];
                                    $data['NAME_EN'] =   $lesson['NAME_EN'];
                                    $data['ID_KALENDARZ'] = $lesson['ID_KALENDARZ'];
                                    $data['TERMIN_K'] = $lesson['TERMIN_K'];
                                    //$data['TERMIN_DT'] = $lesson['TERMIN_DT'];

                                    $formated_schedule->push($data);
                                } catch (\Throwable $th) {
                                    Log::debug($lesson);
                                    Log::debug($schedule);
                                    throw($th);
                                    $xmlErrorCount++;
                                }
                            }
                        }
                    }                        
                }
            }
        }
        //print($xmlErrorCount);
        Cache::put('schedule-data', $formated_schedule);
        //dd($formated_schedule[20]);
    }
}
