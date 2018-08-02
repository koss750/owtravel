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
                'name' => $user->name,
                'email' => $user->email
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
                $transformer = new FamilyUserTransformer;
                $transformer->family_member=$user;
                $family = User::familyOf($user->id);
                if($family!=0) return $this->collection($family, $transformer);
        }



}