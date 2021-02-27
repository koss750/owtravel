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

    public function requestForUser($user_id)
    {
        $user = User::where('id', $user_id)->firstOrFail();
        $cards = $user->cards;
        $code = rand(100, 1000);

        $this->hookController = new LinkHookController;
        $this->hookController->lineOne = "Here is you verification code";
        $this->hookController->lineTwo = $code;
        $this->hookController->sendTextMessage("K");

        $expiresAt = now()->addMinutes(4);
        Cache::put("pay", $code, $expiresAt);
    }

    public function showForUser($user_id, $code)
    {
        try {
            $savedCode = Cache::pull("pay");
        } catch (\Exception $e) {
            abort (500,"Code not found");
        }
        if ($savedCode == $code) {
            $user = User::where('id', $user_id)->firstOrFail();
            $cards = $user->cards;
            $cards['code'] = rand(100, 1000);
            return view('card')
                ->with(
                    'cards',
                    $this->respond($this->showCollection($user->cards,new BankCardTransformer))
                )
                ->with(
                    'code',
                    $cards['code']
                );
        }
        else {
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
