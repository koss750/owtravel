<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28/07/2018
 * Time: 21:45
 */
namespace App\Http\Transformers;

use App\Document;
use App\DocumentTypes;
use League\Fractal;

class DocumentTransformer extends Fractal\TransformerAbstract
{
    public function transform(Document $doc)
    {
        try {
            $type = DocumentTypes::where('id', $doc->document_type_id)->firstOrFail();
            $typeName = $type->description;
            return [
                'id' => $doc->reference,
                'type' => $typeName,
                'number' => $doc->description,
                'link' => $doc->link
            ];
        } catch (\Exception $e) {
            abort(500, "failed to transform document $doc->id");
        }
    }
}