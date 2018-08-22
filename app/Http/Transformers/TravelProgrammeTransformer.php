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
use League\Fractal;

class TravelProgrammeTransformer extends Fractal\TransformerAbstract
{

    public function transform(TravelProgramme $travelProgramme)
    {
        try {



            $transformedObject = [
                'id' => $travelProgramme->reference,
                'reference' => $travelProgramme->human_reference,
                'name' => $travelProgramme->name,
                'start' => $travelProgramme->start_date,
                'end' => $travelProgramme->end_date,
                'destination' => $travelProgramme->main_destination,
                'score' => $travelProgramme->coolness_factor
            ];

            return $transformedObject;
        } catch (\Exception $e) {
            abort(500, "failed to transform programme $travelProgramme->id");
        }
    }

}