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

// ロードバランサーのヘルスチェックからアクセスされる
// ミドルウェアの実行は不要なので空の配列を指定
Route::middleware([])->get('/', function () {
    return response()->json(['status' => 'OK']);
});
