<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brodown extends Model
{
    protected $table = 'brodowns';

    protected $fillable = [
        'name', 'description'
    ];
}
