<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_shop_pass_id
 * @property int $daily_reward_received_count
 * @property string $daily_latest_received_at
 * @property string $start_at
 * @property string $end_at
 */
class UsrShopPass extends UsrEloquentModel implements UsrShopPassInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_shop_pass_id',
        'daily_reward_received_count',
        'daily_latest_received_at',
        'start_at',
        'end_at',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_shop_pass_id;
    }

    public function getMstShopPassId(): string
    {
        return $this->mst_shop_pass_id;
    }

    public function getDailyRewardReceivedCount(): int
    {
        return $this->daily_reward_received_count;
    }

    public function getDailyLatestReceivedAt(): string
    {
        return $this->daily_latest_received_at;
    }

    public function getStartAt(): string
    {
        return $this->start_at;
    }

    public function getEndAt(): string
    {
        return $this->end_at;
    }

    public function reset(CarbonImmutable $now, CarbonImmutable $startAt, CarbonImmutable $endAt): void
    {
        $this->daily_reward_received_count = 0;
        $this->daily_latest_received_at = $now->subDay()->format('Y-m-d H:i:s');
        $this->start_at = $startAt->format('Y-m-d H:i:s');
        $this->end_at = $endAt->format('Y-m-d H:i:s');
    }

    /**
     * 期限内で購入されたパスの残り時間を考慮した再購入のためのリセット処理
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable $endAt
     * @return void
     */
    public function resetByRemainingTime(CarbonImmutable $startAt, CarbonImmutable $endAt): void
    {
        $this->daily_reward_received_count = 0;
        $this->start_at = $startAt->format('Y-m-d H:i:s');
        $this->end_at = $endAt->format('Y-m-d H:i:s');
    }

    /**
     * パスリワード受け取りによるカウントアップと日付更新の処理
     * @param CarbonImmutable $now
     * @return void
     */
    public function rewardReceived(CarbonImmutable $now): void
    {
        // 受け取り回数をインクリメント
        $this->daily_reward_received_count++;
        $this->daily_latest_received_at = $now->format('Y-m-d H:i:s');
    }
}
