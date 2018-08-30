<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28/07/2018
 * Time: 21:45
 */
namespace App\Http\Transformers;

use App\Country;
use App\Document;
use App\DocumentTypes;
use App\TravelProgramme;
use App\User;
use Carbon\Carbon;
use League\Fractal;

class TravelProgrammeTransformer extends Fractal\TransformerAbstract
{

    public function transform(TravelProgramme $travelProgramme)
    {
        try {

            $start = new Carbon($travelProgramme->start_date);
            $end = new Carbon($travelProgramme->end_date);
            $length = $end->diffInDays($start,true);
            $main_iso_2 = Country::where('iso_3', $travelProgramme->countries)->firstOrFail()->iso_2;


            $transformedObject = [
                'id' => $travelProgramme->reference,
                'reference' => $travelProgramme->human_reference,
                'name' => $travelProgramme->name,
                'start' => $travelProgramme->start_date,
                'end' => $travelProgramme->end_date,
                'destination' => $travelProgramme->main_destination,
                'main_flag_code' => $main_iso_2,
                'score' => $travelProgramme->coolness_factor,
                'length' => $length
            ];

            return $transformedObject;
        } catch (\Exception $e) {
            abort(500, "failed to transform programme $travelProgramme->id");
        }
    }

}