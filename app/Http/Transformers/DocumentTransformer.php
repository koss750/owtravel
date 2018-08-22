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
use App\User;
use League\Fractal;

class DocumentTransformer extends Fractal\TransformerAbstract
{

    public function transform(Document $doc)
    {
        try {
            $type = DocumentTypes::where('id', $doc->document_type_id)->firstOrFail();
            $typeName = $type->description;
            $country = Country::where('iso_3', $doc->issue_country)->firstOrFail();
            $country_code = $country->iso_3;
            $country = $country->iso_3 . " ($country->title)";


            $transformedObject = [
                'id' => $doc->reference,
                'type' => $typeName,
                'number' => $doc->number,
                'issued_by' => $country,
                'issued_by_code' => $country_code,
                'link' => $doc->link
            ];

            return $transformedObject;
        } catch (\Exception $e) {
            abort(500, "failed to transform document $doc->id");
        }
    }

}