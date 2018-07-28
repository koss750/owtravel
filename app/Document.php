<?php

namespace App;


use BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Document extends Model
{
    use Notifiable;

    protected $table = 'documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
      
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
