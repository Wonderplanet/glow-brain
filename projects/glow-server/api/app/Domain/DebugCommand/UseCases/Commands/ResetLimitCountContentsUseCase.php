<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\User\Models\UsrUserBuyCount;

class ResetLimitCountContentsUseCase extends BaseCommands
{
    protected string $name = '回数制限リセット';
    protected string $description = '回数制限のあるコンテンツのリセットをします。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 回数制限のあるコンテンツのリセットをします
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $countResetValue = 0;
        $msResetValue = null;
        $datetimeResetNotNullValue = '2000-01-01 00:00:00';
        $usrId = $user->id;
        // ガチャの状況をリセット
        UsrGacha::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'ad_count' => $countResetValue,
                'ad_daily_count' => $countResetValue,
                'count' => $countResetValue,
                'daily_count' => $countResetValue,
                'played_at' => $datetimeResetNotNullValue,
                'ad_played_at' => $datetimeResetNotNullValue,
            ]);
        // ガチャの天井判定をリセット
        UsrGachaUpper::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'count' => $countResetValue,
            ]);
        // 降臨バトルの状況をリセット
        UsrAdventBattle::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'reset_challenge_count' => $countResetValue,
                'reset_ad_challenge_count' => $countResetValue,
            ]);
        // 探索の状況をリセット
        UsrIdleIncentive::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'diamond_quick_receive_count' => $countResetValue,
                'ad_quick_receive_count' => $countResetValue,
                'idle_started_at' => $datetimeResetNotNullValue,
                'diamond_quick_receive_at' => $datetimeResetNotNullValue,
                'ad_quick_receive_at' => $datetimeResetNotNullValue,
            ]);

        ///イベントステージ
        UsrStageEvent::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'reset_clear_count' => $countResetValue,
                'reset_ad_challenge_count' => $countResetValue,
                'reset_clear_time_ms' => $msResetValue,
                'clear_time_ms' => $msResetValue,
                'latest_reset_at' => $datetimeResetNotNullValue,
            ]);

        // 強化クエストステージ
        UsrStageEnhance::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'clear_count' => $countResetValue,
                'reset_challenge_count' => $countResetValue,
                'reset_ad_challenge_count' => $countResetValue,
                'latest_reset_at' => $datetimeResetNotNullValue,
            ]);

        UsrStageSession::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'daily_continue_ad_count' => $countResetValue,
                'latest_reset_at' => $datetimeResetNotNullValue,
            ]);

        UsrUserBuyCount::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'daily_buy_stamina_ad_count' => $countResetValue,
                'daily_buy_stamina_ad_at' => $datetimeResetNotNullValue,
            ]);

        UsrShopItem::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'trade_count' => $countResetValue,
                'trade_total_count' => $countResetValue,
                'last_reset_at' => $datetimeResetNotNullValue,
            ]);

        UsrStoreProduct::query()
            ->where('usr_user_id', $usrId)
            ->update([
                'purchase_count' => $countResetValue,
                'purchase_total_count' => $countResetValue,
                'last_reset_at' => $datetimeResetNotNullValue,
            ]);
    }
}
