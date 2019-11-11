<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkLog extends Model
{
    protected $table = 'link_logs';

    protected $fillable = ['type', 'subtype', 'value'];


}
