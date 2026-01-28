<?php

use Illuminate\Support\Facades\Route;

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

Route::get('login/{provider}', 'LoginController@redirectToProvider')->name('Login');
Route::get('login/{provider}/callback', 'LoginController@handleProviderCallback')->name('LoginCallback');
Route::post('logout', 'LoginController@logout')->name('logout');
