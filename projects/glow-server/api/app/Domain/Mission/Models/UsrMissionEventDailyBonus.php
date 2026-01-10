<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $mst_mission_event_daily_bonus_id
 * @property int $status
 * @property ?string $cleared_at
 * @property ?string $received_reward_at
 */
class UsrMissionEventDailyBonus extends UsrEloquentModel implements UsrMissionEventDailyBonusInterface
{
    use HasFactory;

    protected $guarded = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_mission_event_daily_bonus_id;
    }

    public function getMstMissionId(): string
    {
        return $this->mst_mission_event_daily_bonus_id;
    }

    public function getMstMissionEventDailyBonusId(): string
    {
        return $this->mst_mission_event_daily_bonus_id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getClearedAt(): ?string
    {
        return $this->cleared_at;
    }

    public function getReceivedRewardAt(): ?string
    {
        return $this->received_reward_at;
    }

    public function getLatestUpdateAt(): string
    {
        // デイリーボーナスではlatest_update_atを使ったリセット判定は行わない。
        // 空文字はCarbonImmutable::parseで現在時刻になり、リセット不要判定になるので、問題ない
        return '';
    }

    public function clear(CarbonImmutable $now): void
    {
        $this->status = MissionStatus::CLEAR->value;
        $this->cleared_at = $now->toDateTimeString();
    }
    public function receiveReward(CarbonImmutable $now): void
    {
        $this->status = MissionStatus::RECEIVED_REWARD->value;
        $this->received_reward_at = $now->toDateTimeString();
    }

    public function resetStatus(
        CarbonImmutable $now,
    ): void {
        $this->status = MissionStatus::UNCLEAR->value;
        $this->cleared_at = null;
        $this->received_reward_at = null;
    }

    public function isClear(): bool
    {
        return $this->status >= MissionStatus::CLEAR->value;
    }

    public function isReceivedReward(): bool
    {
        return $this->status === MissionStatus::RECEIVED_REWARD->value;
    }

    public function canReceiveReward(): bool
    {
        return $this->status === MissionStatus::CLEAR->value;
    }
}
