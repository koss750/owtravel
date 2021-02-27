<?php

namespace App\Http\Controllers;

use App\BankCard;
use App\Http\Transformers\BankCardTransformer;
use App\User;
use http\Client\Request;

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
        $this->hookController = new LinkHookController;
    }

    public function requestForUser($user_id)
    {
        $user = User::where('id', $user_id)->firstOrFail();
        $cards = $user->cards;
        $code = rand(100, 1000);

        $this->hookController->lineOne = "Here is you verification code";
        $this->hookController->lineTwo = $cards['code'];
        $this->hookController->sendTextMessage("K");

        $response = new Illuminate\Http\Response('Request received');
        $response->withCookie(cookie('code', $code, 5));
    }

    public function showForUser($user_id, $code)
    {
        try {
            $cookie = Request::cookie('name');
        } catch (\Exception $e) {
            abort (500,"Cookie not found");
        }
        if ($cookie == $code) {
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
