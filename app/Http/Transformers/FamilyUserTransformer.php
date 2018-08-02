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
use App\Family;
use App\FamilyMember;
use App\User;
use League\Fractal;

class FamilyUserTransformer extends Fractal\TransformerAbstract
{

    public $family_member;
    protected $defaultIncludes = ['docs'];

    public function transform(User $user)
    {
            $family_item = Family::where('user_id', $user->id)->where('relates_to', $this->family_member->id)->first();
        if (empty($family_item)) {
            $family_item = Family::where('user_id', $this->family_member->id)->where('relates_to', $user->id)->firstOrFail();
        }
        //else $family_item = $family_item[0];
        $relation = FamilyMember::where('id', $family_item->member_id)->firstOrFail();
        $description_string = $relation->description;
        try {
            return [
                'relation' => $description_string,
                'name' => $user->name
            ];
        } catch (\Exception $e) {
            abort(500, "failed to transform user $user->id");
        }
    }

        public function includeDocs(User $user) {
                $docs = Document::where('user_id', $user->id)->get();
                return $this->collection($docs, new DocumentTransformer);
    }



}