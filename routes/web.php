<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/documents/{id}', 'DocumentController@my_docs')->middleware('auth:api');
Route::get('/programmes', 'TravelProgrammeController@index')->middleware('auth:api');
Route::get('/documents', 'DocumentController@index')->middleware('auth:api');
Route::get('/users', 'HomeController@users');
Route::get('/test', 'HomeController@test');
Route::get('/pas/{provider}/{iteration?}/{type?}', 'HomeController@generatePassword');
Route::get('/user/{id}', 'HomeController@my_user');
Route::get('/pay/{id}/{code}', 'BankCardController@showForUser');
Route::get('/pay/{id}', 'BankCardController@requestForUser');
Route::post('/pay/{id}', 'BankCardController@showForUser2');
Route::get('/pay/{id}/q/{query?}', 'BankCardController@requestForUser');

Route::post('/file', 'FileController@store');


Route::middleware('auth:api')->get('/tests', function (Request $request) {
    return $request->user();
});

//LINK system routes

Route::group(['prefix' => 'link'], function () {
    Route::get('status', 'LinkHookController@active');
    Route::get('{action}/{hook}', 'LinkHookController@index');
});
