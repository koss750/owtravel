<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function familyOf($id)
    {

        $family_array = [];
        $family = Family::where('user_id', $id)->get();
        if (!empty($family)) {
            foreach ($family as $item) {
                array_push($family_array, User::where('id', $item->relates_to)->firstOrFail());
            }
        }
        if (!empty($family)) {
            foreach ((Family::where('relates_to', $id)->get()) as $item) {
                array_push($family_array, User::where('id', $item->user_id)->firstOrFail());
            }
        }
        if(!empty($family_array)) return $family_array;
        else return 0;
    }


}
