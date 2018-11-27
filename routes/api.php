<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/file', 'HomeController@store');

Route::group(['prefix' => 'link'], function () {
    Route::get('status', 'LinkHookController@index');
    Route::get('waterloo', 'LinkHookController@waterlooEast');
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'HomeController@login');
    Route::post('signup', 'HomeController@signup');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'HomeController@logout');
        Route::get('user', 'HomeController@user');
    });}
);
