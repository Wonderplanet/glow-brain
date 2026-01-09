<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionLimitedTermInterface;
use App\Domain\Resource\Usr\Models\UsrModel;
use Carbon\CarbonImmutable;

class UsrMissionLimitedTerm extends UsrModel implements UsrMissionLimitedTermInterface, IUsrMission
{
    protected static string $tableName = 'usr_mission_limited_terms';
    protected array $modelKeyColumns = ['usr_user_id', 'mst_mission_limited_term_id'];

    public static function create(
        string $usrUserId,
        string $mstMissionLimitedTermId,
        CarbonImmutable $now,
    ): UsrMissionLimitedTermInterface {
        return new self([
            'usr_user_id' => $usrUserId,
            'mst_mission_limited_term_id' => $mstMissionLimitedTermId,
            'status' => MissionStatus::UNCLEAR->value,
            'is_open' => MissionUnlockStatus::LOCK->value,
            'progress' => MissionConstant::PROGRESS_INITIAL_VALUE,
            'latest_reset_at' => $now->toDateTimeString(),
            'cleared_at' => null,
            'received_reward_at' => null,
        ]);
    }

    public function getMissionType(): int
    {
        return MissionType::LIMITED_TERM->getIntValue();
    }

    public function getMstMissionId(): string
    {
        return $this->attributes['mst_mission_limited_term_id'];
    }

    public function getStatus(): int
    {
        return $this->attributes['status'];
    }

    public function getIsOpen(): int
    {
        return $this->attributes['is_open'];
    }

    public function getProgress(): int
    {
        return $this->attributes['progress'];
    }

    public function getUnlockProgress(): int
    {
        // 期間限定ミッションの開放条件はなく、開放進捗値を持たないため、常に0を返す
        return 0;
    }

    public function getLatestResetAt(): string
    {
        return $this->attributes['latest_reset_at'];
    }

    public function getClearedAt(): ?string
    {
        return $this->attributes['cleared_at'];
    }

    public function getReceivedRewardAt(): ?string
    {
        return $this->attributes['received_reward_at'];
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->attributes['status'] = MissionStatus::UNCLEAR->value;
        $this->attributes['is_open'] = MissionUnlockStatus::LOCK->value;
        $this->attributes['progress'] = MissionConstant::PROGRESS_INITIAL_VALUE;
        $this->attributes['latest_reset_at'] = $now->toDateTimeString();
        $this->attributes['cleared_at'] = null;
        $this->attributes['received_reward_at'] = null;
    }

    public function open(): void
    {
        $this->attributes['is_open'] = MissionUnlockStatus::OPEN->value;
    }

    public function updateProgress(int $progress): void
    {
        $this->attributes['progress'] = $progress;
    }

    public function updateUnlockProgress(int $unlockProgress): void
    {
        // 期間限定ミッションの開放条件はなく、開放進捗値を持たないため、何もしない
        return;
    }

    public function clear(CarbonImmutable $now): void
    {
        $this->attributes['status'] = MissionStatus::CLEAR->value;
        $this->attributes['cleared_at'] = $now->toDateTimeString();
    }

    public function receiveReward(CarbonImmutable $now): void
    {
        $this->attributes['status'] = MissionStatus::RECEIVED_REWARD->value;
        $this->attributes['received_reward_at'] = $now->toDateTimeString();
    }

    public function isClear(): bool
    {
        return $this->attributes['status'] >= MissionStatus::CLEAR->value;
    }

    public function isReceivedReward(): bool
    {
        return $this->attributes['status'] === MissionStatus::RECEIVED_REWARD->value;
    }

    public function canReceiveReward(): bool
    {
        return $this->attributes['status'] === MissionStatus::CLEAR->value
            && $this->attributes['is_open'] === MissionUnlockStatus::OPEN->value;
    }

    public function isOpen(): bool
    {
        return $this->attributes['is_open'] === MissionUnlockStatus::OPEN->value;
    }
}
