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

}
