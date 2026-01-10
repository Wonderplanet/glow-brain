<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

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


Route::middleware([
    'encrypt',
    // 'client_version_check',
    // 'asset_version_check',
    // 'master_version_check',
])->group(function () {
    Route::controller(Controllers\AuthController::class)->group(function () {
        Route::post('/sign_up', 'signUp');
        Route::post('/sign_in', 'signIn');
    });
});


Route::middleware([
    'encrypt',
    'auth:api',
    'block_multiple_access',
    'user_status_check',
    'client_version_check',
    'asset_version_check',
    'master_version_check',
    'cross_day_check',
])->group(function () {
    // メンテナンスチェック不要なAPI群
    Route::controller(Controllers\UserController::class)->group(function () {
        Route::get('/user/info', 'info');
        Route::post('/user/change_name', 'changeName');
        Route::post('/user/change_avatar', 'changeAvatar');
        Route::post('/user/change_emblem', 'changeEmblem');
        Route::post('/user/buy_stamina_ad', 'buyStaminaAd');
        Route::post('/user/buy_stamina_diamond', 'buyStaminaDiamond');
        Route::post('/user/link_bnid_confirm', 'linkBnidConfirm');
        Route::post('/user/link_bnid', 'linkBnid');
        Route::post('/user/unlink_bnid', 'unlinkBnid');
        Route::post('/user/agree', 'agree');
    });

    Route::controller(Controllers\GameController::class)->group(function () {
        Route::get('/game/version', 'version');
        Route::post('/game/update_and_fetch', 'updateAndFetch');
        Route::get('/game/fetch', 'fetch');
        Route::get('/game/server_time', 'serverTime');
        Route::get('/game/badge', 'badge');
    });

    Route::controller(Controllers\UnitController::class)->group(function () {
        Route::post('/unit/grade_up', 'gradeUp');
        Route::post('/unit/level_up', 'levelUp');
        Route::post('/unit/rank_up', 'rankUp');
    });

    Route::controller(Controllers\ItemController::class)->group(function () {
        Route::post('/item/consume', 'consume');
        Route::post('/item/exchange_select_item', 'exchangeSelectItem');
    });

    Route::controller(Controllers\IdleIncentiveController::class)->group(function () {
        Route::post('/idle_incentive/receive', 'receive');
        Route::post('/idle_incentive/quick_receive_by_diamond', 'quickReceiveByDiamond');
        Route::post('/idle_incentive/quick_receive_by_ad', 'quickReceiveByAd');
    });

    Route::controller(Controllers\PartyController::class)->group(function () {
        Route::post('/party/save', 'save');
    });

    Route::controller(Controllers\OutpostController::class)->group(function () {
        Route::post('/outpost/enhance', 'enhance');
        Route::post('/outpost/change_artwork', 'changeArtwork');
    });

    Route::controller(Controllers\MissionController::class)->group(function () {
        Route::post('/mission/update_and_fetch', 'updateAndFetch');
        Route::post('/mission/bulk_receive_reward', 'bulkReceiveReward');
        Route::post('/mission/clear_on_call', 'clearOnCall');
        Route::post('/mission/event_daily_bonus_update', 'eventDailyBonusUpdate');
        Route::post('/mission/event_update_and_fetch', 'eventUpdateAndFetch');
        Route::post('/mission/advent_battle_fetch', 'adventBattleFetch');
    });

    Route::controller(Controllers\MessageController::class)->group(function () {
        Route::post('/message/update_and_fetch', 'updateAndFetch');
        Route::post('/message/open', 'open');
        Route::post('/message/receive', 'receive');
    });

    Route::controller(Controllers\EncyclopediaController::class)->group(function () {
        Route::post('/encyclopedia/receive_reward', 'receiveReward');
        Route::post('/encyclopedia/receive_first_collection_reward', 'receiveFirstCollectionReward');
    });

    Route::controller(Controllers\TutorialController::class)->group(function () {
        Route::post('/tutorial/update_status', 'updateStatus');
        Route::post('/tutorial/gacha_draw', 'gachaDraw');
        Route::post('/tutorial/gacha_confirm', 'gachaConfirm');
        Route::post('/tutorial/stage_start', 'stageStart');
        Route::post('/tutorial/stage_end', 'stageEnd');
        Route::post('/tutorial/unit_level_up', 'unitLevelUp');
    });

    // メンテナンスチェック対象のドメイン群
    Route::middleware(['content_maintenance_check'])->group(function () {
        // ステージ関連（cleanupのみ除外）
        Route::controller(Controllers\StageController::class)->group(function () {
            Route::post('/stage/start', 'start');
            Route::post('/stage/end', 'end');
            Route::post('/stage/continue_diamond', 'continueDiamond');
            Route::post('/stage/continue_ad', 'continueAd');
            Route::post('/stage/abort', 'abort');
            Route::post('/stage/cleanup', 'cleanup');
        });

        // 降臨バトル関連（cleanupのみ除外）
        Route::controller(Controllers\AdventBattleController::class)->group(function () {
            Route::post('/advent_battle/top', 'top');
            Route::post('/advent_battle/start', 'start');
            Route::post('/advent_battle/end', 'end');
            Route::post('/advent_battle/abort', 'abort');
            Route::get('/advent_battle/ranking', 'ranking');
            Route::get('/advent_battle/info', 'info');
            Route::post('/advent_battle/cleanup', 'cleanup');
        });

        // ガシャ関連
        Route::controller(Controllers\GachaController::class)->group(function () {
            Route::get('/gacha/prize', 'prize');
            Route::get('/gacha/history', 'history');
            Route::post('/gacha/draw/ad', 'drawAd');
            Route::post('/gacha/draw/diamond', 'drawDiamond');
            Route::post('/gacha/draw/paid_diamond', 'drawPaidDiamond');
            Route::post('/gacha/draw/item', 'drawItem');
            Route::post('/gacha/draw/free', 'drawFree');
        });

        // PVP関連（cleanupのみ除外）
        Route::controller(Controllers\PvpController::class)->group(function () {
            Route::post('/pvp/top', 'top');
            Route::post('/pvp/change_opponent', 'changeOpponent');
            Route::post('/pvp/start', 'start');
            Route::post('/pvp/end', 'end');
            Route::get('/pvp/ranking', 'ranking');
            Route::post('/pvp/resume', 'resume');
            Route::post('/pvp/abort', 'abort');
            Route::post('/pvp/cleanup', 'cleanup');
        });

        // ショップ関連
        Route::controller(Controllers\ShopController::class)->group(function () {
            Route::post('/shop/allowance', 'allowance');
            Route::get('/shop/get_store_info', 'getStoreInfo');
            Route::post('/shop/set_store_info', 'setStoreInfo');
            Route::post('/shop/trade_shop_item', 'tradeShopItem');
            Route::post('/shop/trade_pack', 'branchTradePack');
            Route::post('/shop/purchase_pass', 'purchase');
            Route::get('/shop/purchase_history', 'purchaseHistory');
        });

        // 交換所関連
        Route::controller(Controllers\ExchangeController::class)->group(function () {
            Route::post('/exchange/trade', 'trade');
        });

        // BOXガチャ関連
        Route::controller(Controllers\BoxGachaController::class)->group(function () {
            Route::get('/box_gacha/info', 'info');
            Route::post('/box_gacha/draw', 'draw');
            Route::post('/box_gacha/reset', 'reset');
        });
    });

    if (config('app.debug')) {
        Route::controller(Controllers\DebugCommandController::class)->group(function () {
            Route::get('/debug_command/list', 'list');
            Route::post('/debug_command/execute', 'execute');
        });
    }
});
