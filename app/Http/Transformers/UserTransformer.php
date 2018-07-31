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
use App\User;
use League\Fractal;

class UserTransformer extends Fractal\TransformerAbstract
{
protected $defaultIncludes = ['docs', 'family'];

    public function transform(User $user)
    {
        try {
            return [
                'id' => $user->reference,
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
        public function includeFamily(User $user) {
                $family_id_array = [];
                $family = Family::where('user_id', $user->id)->get();
                foreach ($family as $item) {
                        array_push($family_id_array, $item->relates_to);
                }
                $relations = User::whereIn('id', $family_id_array)->get();
                return $this->collection($relations, new UserTransformer);
        }



}