<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28/07/2018
 * Time: 21:45
 */
namespace App\Http\Transformers;

use App\BankCard;
use App\Country;
use App\DocumentTypes;
use App\User;
use League\Fractal;

class BankCardTransformer extends Fractal\TransformerAbstract
{

    public function transform(BankCard $card)
    {
        try {
            $transformedObject = [
                'holder' => $card->cardholder($card),
                'bank' => $card->bank,
                'account' => $card->account,
                'number' => decrypt($card->ln),
                'expiry' => "$card->expiry_month / $card->expiry_year",
                'cvc' => decrypt($card->CVC)
            ];

            return $transformedObject;
        } catch (\Exception $e) {
            abort(500, "failed to transform document $card->id");
        }
    }

}
