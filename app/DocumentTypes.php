<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentTypes
 * @package App
 * @var string 'description'
 */
class DocumentTypes extends BaseModel
{
    protected $table = 'document_types';

    protected $attributes = [
        'description' => null,
    ];

    protected $fillable = [
        'description'
    ];

}
