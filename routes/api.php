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

Route::post('/search', 'UserController@search');
Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/profile', 'UserController@artisanProfile');
    Route::post('/phone/verify', 'UserController@verifyPhoneNumber');
});
