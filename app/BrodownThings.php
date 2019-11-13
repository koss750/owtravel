<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrodownThings extends Model
{
    protected $table = 'brodown_things';

    protected $fillable = [
        'brodown_id', 'type', 'value'
    ];
}
