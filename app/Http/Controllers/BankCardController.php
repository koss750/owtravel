<?php

namespace App\Http\Controllers;

use App\BankCard;
use App\Http\Transformers\BankCardTransformer;
use App\User;
use http\Client\Request;
use Illuminate\Support\Facades\Cache;

class BankCardController extends Controller
{
    /**
     * @var BankCard $card
     */
    public $card;
    /**
     * @var User $user
     */
    public $user;

    /**
     * @var LinkHookController $controller
     */
    private $hookController;

    public function _construct($card){
        $this->card = $card;
    }

    public function requestForUser($user_id, $query = "all")
    {
        $cache_key = "PAY_$user_id";
        $code = rand(10000, 1000000);


        switch ($query) {
            case "all":
                $cardsQ = BankCard::where('user_id', $user_id);
                break;
            default:
                $cardsQ = BankCard::where([
                    ["user_id", $user_id],
                    ['bank', 'LIKE', "%$query%"]]);
                break;
        }
        $expiresAt = now()->addMinutes(4);
        $this->hookController = new LinkHookController;
        $this->hookController->lineOne = "Here is your code: $code";
        $this->hookController->lineTwo = "Or follow this link: https://liks.uk/pay/$user_id/$code";
        if ($query!="l") {
            $this->hookController->sendTextMessage("K");
            $params['phone'] = '447394444404';
        }
        else {
            $this->hookController->sendTextMessage("L");
            $params['phone'] = '447482428982';
        }
        $cards = Cache::remember($cache_key.'_cards', $expiresAt, function() use ($cardsQ) {
            return $cardsQ->get();
        });

        $params['count'] = $cards->count();
        Cache::put($cache_key, $code, $expiresAt);

        return view('old_card')
            ->with(
                'params',
                $params
            );
    }

    public function showForUser2(Request $request)
    {

        dd($request);
        $cache_key = "PAY_$user_id";

        try {
            $savedCode = Cache::pull($cache_key);
            Cache::forget($cache_key);
        } catch (\Exception $e) {
            abort (500,"Code not found");
        }
        if ($savedCode == $code) {
            return view('card')
                ->with(
                    'cards',
                    $this->respond($this->showCollection(Cache::pull($cache_key.'_cards'),new BankCardTransformer))
                )
                ->with(
                    'code',
                    $code
                );
        } else {
            abort(500, "Wrong  Code");
        }
    }

    public function showForUser($user_id, $code)
    {
        $cache_key = "PAY_$user_id";

        try {
            $savedCode = Cache::pull($cache_key);
            Cache::forget($cache_key);
        } catch (\Exception $e) {
            abort (500,"Code not found");
        }
        if ($savedCode == $code) {
            return view('card')
                ->with(
                    'cards',
                    $this->respond($this->showCollection(Cache::pull($cache_key.'_cards'),new BankCardTransformer))
                )
                ->with(
                    'code',
                    $code
                );
        } else {
            abort(500, "Wrong  Code");
        }
    }

//    public function index($card){
//        $docs = BankCard::where();
//        return $this->respond($this->showCollection($docs,$documentTransformer));
//    }
//
//    public function my_docs($id, DocumentTransformer $documentTransformer){
//
//
//        try {
//            $user = User::where('id', $id)->firstOrFail();
//        } catch (\Exception $e) {
//            abort (500,"User not found");
//        }
//
//        try {
//            $docs = Document::where('user_id', $user->id)->get();
//
//            return $this->respond($this->showCollection($docs,$documentTransformer));
//        } catch (\Exception $e) {
//                abort (500,"No documents found for $user->name");
//            }
//
//
//    }
}
