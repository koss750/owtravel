<?php

namespace App;


class BankCard extends BaseModel
{
    protected $table = 'bank_cards';

    protected $fillable = [
        'ln', 'CVC', 'expiry_month', 'expiry_year', 'bank'
    ];
}
