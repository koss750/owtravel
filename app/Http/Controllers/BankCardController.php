<?php

namespace App\Http\Controllers;

use App\BankCard;
use App\User;
use Illuminate\Http\Request;

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

    public function _construct($card){
        $this->card = $card;
    }

    public function showForUser($user_id)
    {
        $user = User::where('id', $user_id)->firstOrFail();
        return view('card')
            ->with('cards', $user->cards);
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
