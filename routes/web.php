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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/mydocuments/{id}', 'DocumentController@my_docs');
Route::get('/familydocuments/{id}', 'DocumentController@family_docs');
Route::get('/documents', 'DocumentController@index')->middleware('auth:api');
Route::get('/users', 'HomeController@users');
Route::get('/user/{id}', 'HomeController@my_user');
Route::post('/file', 'FileController@store');


Route::middleware('auth:api')->get('/tests', function (Request $request) {
    return $request->user();
});