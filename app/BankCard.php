<?php

namespace App;

use Faker\Provider\Uuid;

class BankCard extends BaseModel
{
    protected $table = 'bank_cards';

    protected $fillable = [
        'ln', 'CVC', 'expiry_month', 'expiry_year', 'bank'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->reference = (string) Uuid::uuid();
        });
    }

    public function formatLongNumber($cc) {
        // Clean out extra data that might be in the cc
        $cc = str_replace(array('-',' '),'',$cc);
        // Get the CC Length
        $cc_length = strlen($cc);
        // Initialize the new credit card to contain the last four digits
        $newCreditCard = substr($cc,-4);
        // Walk backwards through the credit card number and add a dash after every fourth digit
        for($i=$cc_length-5;$i>=0;$i--){
            // If on the fourth character add a dash
            if((($i+1)-$cc_length)%4 == 0){
                $newCreditCard = ' '.$newCreditCard;
            }
            // Add the current character to the new credit card
            $newCreditCard = $cc[$i].$newCreditCard;
        }
        // Return the formatted credit card number
        return $newCreditCard;
    }

    public function cardholder(BankCard $bankCard) {
        $user = User::where('id', $bankCard->user_id);
        return $user->name;
    }

    public function type($id) {
        $cardTypeId = $id[0];
        switch ($cardTypeId)  {
            case 3:
                return "AMEX";
                break;
            case 4:
                return "VISA";
                break;
            case 5:
                return "MasterCard";
                break;
        }
    }

}
