<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * WARNING: api.phpに定義したルートは、/apiというURIプレフィックスが自動的に適用される
 * https://readouble.com/laravel/9.x/ja/routing.html#the-default-route-files
 */
Route::controller(Controllers\AdminController::class)->group(function () {
    Route::post('/register-asset', 'registerAsset');
    Route::get('/get-asset-release-data/{platform}', 'getEffectiveAssetReleases');
    Route::get('/get-asset-release-data/{platform}/{release_key}', 'getAssetReleaseData');
    Route::get('/get-master-release-data', 'getMasterReleaseData');

    Route::get('/get-promotion-tag', 'getPromotionTag');
    Route::get('/get-tag-promotion-data/{adm_promotion_tag_id}', 'getTagPromotionData');
    Route::get('/get-information-promotion-data/{adm_promotion_tag_id}', 'getInformationPromotionData');
    Route::get('/get-ign-promotion-data/{adm_promotion_tag_id}', 'getIgnPromotionData');
    Route::get('/get-jumpplusreward-promotion-data/{adm_promotion_tag_id}', 'getJumpPlusRewardPromotionData');
    Route::get('/get-gacha-caution-promotion-data/{adm_promotion_tag_id}', 'getGachaCautionPromotionData');
    Route::get('/get-s3object-promotion-data/{adm_promotion_tag_id}', 'getS3ObjectPromotionData');
    Route::get('/get-message-distribution-promotion-data/{adm_promotion_tag_id}', 'getMessageDistributionPromotionData');
});
