<?php

namespace App;

use Faker\Provider\Uuid;

class BankLogin extends BaseModel
{
    protected $table = 'bank_logins';

    protected $fillable = [
        'bank', 'notes', 'login username', 'password part 1', 'password part 2'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->reference = (string) Uuid::uuid();
        });
    }

}
