<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 25/07/2018
 * Time: 18:59
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** * Class BaseModel * @property int id * @property string reference * @package App */


class BaseModel extends Model
{
    use SoftDeletes;
    protected $hidden = ['id'];
}